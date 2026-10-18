<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\FirebasePushService;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Tamamlanan randevulardan ~2 saat sonra, henüz değerlendirme yapmamış müşterilere
 * tek seferlik "Hizmeti değerlendirin" push bildirimi gönderir.
 *
 * İdempotent: gönderim sonrası appointments.review_reminder_sent_at damgalanır.
 */
#[Signature('appointments:send-review-reminders')]
class SendReviewRemindersCommand extends Command
{
    protected $description = 'Tamamlanan randevular için değerlendirme hatırlatma bildirimi gönderir';

    /** Randevu tamamlandıktan en az kaç saat sonra bildirim gönderilir. */
    private const DELAY_HOURS = 2;

    /** Bu süreden eski tamamlanmış randevular için bildirim gönderilmez. */
    private const MAX_AGE_HOURS = 24;

    /** Google'a yönlendirilen müşteriye tekrar hatırlatma yapılmayacak süre (gün). */
    private const GOOGLE_COOLDOWN_DAYS = 60;

    public function handle(): int
    {
        try {
            $push = app(FirebasePushService::class);
        } catch (\Throwable $e) {
            Log::error('[ReviewReminder] Push servisi başlatılamadı: ' . $e->getMessage());
            $this->error('Push servisi başlatılamadı.');

            return self::FAILURE;
        }

        $sent = 0;

        Appointment::query()
            ->where('status', AppointmentStatus::Completed)
            ->where('completed_at', '<=', now()->subHours(self::DELAY_HOURS))
            ->where('completed_at', '>=', now()->subHours(self::MAX_AGE_HOURS))
            ->whereNull('review_reminder_sent_at')
            ->whereDoesntHave('review')
            ->whereHas('customer', function ($q) {
                $q->where(function ($q) {
                    $q->whereNull('last_google_redirect_at')
                        ->orWhere('last_google_redirect_at', '<=', now()->subDays(self::GOOGLE_COOLDOWN_DAYS));
                });
            })
            ->with('branch.settings')
            ->chunkById(50, function ($appointments) use ($push, &$sent) {
                foreach ($appointments as $appointment) {
                    // Şubede değerlendirme modülü kapalıysa bildirim gönderme
                    if ($appointment->branch?->settings && ! $appointment->branch->settings->review_enabled) {
                        $appointment->forceFill(['review_reminder_sent_at' => now()])->saveQuietly();
                        continue;
                    }

                    try {
                        $push->sendToUser(
                            (int) $appointment->customer_id,
                            'Bugünkü hizmetten memnun kaldınız mı? 💈',
                            'Deneyiminizi 1 dakikada değerlendirerek bize geri bildirimde bulunabilirsiniz.',
                            [
                                'action' => 'open_review',
                                'appointment_id' => (string) $appointment->id,
                            ]
                        );
                    } catch (\Throwable $e) {
                        Log::error('[ReviewReminder] Gönderim hatası: ' . $e->getMessage(), [
                            'appointment_id' => $appointment->id,
                        ]);
                        continue;
                    }

                    // Cihazı olmayan kullanıcılar için de tekrar denemeyi önlemek adına damgala
                    $appointment->forceFill(['review_reminder_sent_at' => now()])->saveQuietly();
                    $sent++;
                }
            });

        $this->info("Değerlendirme hatırlatması işlenen randevu: {$sent}");

        return self::SUCCESS;
    }
}
