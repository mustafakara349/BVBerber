<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Notification;
use App\Services\FirebasePushService;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Tamamlanan randevulardan ~2 saat sonra, henüz değerlendirme yapmamış müşterilere
 * tek seferlik "Hizmeti değerlendirin" push ve uygulama içi bildirimi gönderir.
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

    /** Google\'a yönlendirilen müşteriye tekrar hatırlatma yapılmayacak süre (gün). */
    private const GOOGLE_COOLDOWN_DAYS = 60;

    public function handle(): int
    {
        $this->info('Değerlendirme hatırlatma taraması başlatılıyor...');

        try {
            $push = app(FirebasePushService::class);
        } catch (\Throwable $e) {
            Log::error('[ReviewReminder] Push servisi başlatılamadı: ' . $e->getMessage());
            $this->error('Push servisi başlatılamadı.');

            return self::FAILURE;
        }

        $sent = 0;
        $now = now();

        Appointment::query()
            ->where('status', AppointmentStatus::Completed)
            ->where(function ($q) use ($now) {
                $q->where(function ($sub) use ($now) {
                    $sub->whereNotNull('completed_at')
                        ->where('completed_at', '<=', $now->copy()->subHours(self::DELAY_HOURS))
                        ->where('completed_at', '>=', $now->copy()->subHours(self::MAX_AGE_HOURS));
                })->orWhere(function ($sub) use ($now) {
                    $sub->whereNull('completed_at')
                        ->where('start_at', '<=', $now->copy()->subHours(self::DELAY_HOURS))
                        ->where('start_at', '>=', $now->copy()->subHours(self::MAX_AGE_HOURS));
                });
            })
            ->whereNull('review_reminder_sent_at')
            ->whereDoesntHave('review')
            ->whereHas('customer', function ($q) use ($now) {
                $q->whereNull('last_google_redirect_at')
                    ->orWhere('last_google_redirect_at', '<=', $now->copy()->subDays(self::GOOGLE_COOLDOWN_DAYS));
            })
            ->with(['branch.settings', 'customer'])
            ->chunkById(50, function ($appointments) use ($push, &$sent) {
                foreach ($appointments as $appointment) {
                    // Şubede değerlendirme modülü kapalıysa bildirim gönderme
                    if ($appointment->branch?->settings && ! $appointment->branch->settings->review_enabled) {
                        $appointment->forceFill(['review_reminder_sent_at' => now()])->saveQuietly();
                        continue;
                    }

                    $title = 'Bugünkü kesimden memnun kaldınız mı? 💈';
                    $body = 'Hizmetimizi 1 dakikada değerlendirerek geri bildirimde bulunabilirsiniz.';
                    $payloadData = [
                        'action' => 'open_review',
                        'appointment_id' => (string) $appointment->id,
                    ];

                    // 1. Firebase Push bildirimi
                    try {
                        $push->sendToUser(
                            (int) $appointment->customer_id,
                            $title,
                            $body,
                            $payloadData
                        );
                    } catch (\Throwable $e) {
                        Log::warning("[ReviewReminder] Push gönderim uyarısı (randevu: {$appointment->id}): " . $e->getMessage());
                    }

                    // 2. Uygulama içi bildirim kaydı
                    try {
                        Notification::create([
                            'user_id' => $appointment->customer_id,
                            'title' => $title,
                            'body' => $body,
                            'type' => 'review_reminder',
                            'data' => [
                                'icon' => 'appointment',
                                'action' => 'open_review',
                                'appointment_id' => (string) $appointment->id,
                            ],
                            'sent_at' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning("[ReviewReminder] Uygulama içi bildirim oluşturulamadı: " . $e->getMessage());
                    }

                    // 3. Randevuyu damgala
                    $appointment->forceFill(['review_reminder_sent_at' => now()])->saveQuietly();
                    $sent++;
                }
            });

        $this->info("Toplam {$sent} adet randevu için değerlendirme hatırlatması işlendi.");

        return self::SUCCESS;
    }
}
