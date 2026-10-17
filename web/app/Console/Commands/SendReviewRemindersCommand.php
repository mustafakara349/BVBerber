<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Notification;
use App\Services\FirebasePushService;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('appointments:send-review-reminders')]
class SendReviewRemindersCommand extends Command
{
    protected $description = 'Tamamlanmış randevulardan 2 saat sonra kullanıcılara değerlendirme hatırlatması gönderir';

    public function handle(?FirebasePushService $pushService = null): int
    {
        $this->info('Değerlendirme hatırlatma taraması başlatılıyor...');

        $now = now();
        $query = Appointment::query()
            ->where('status', AppointmentStatus::Completed)
            ->where(function ($q) use ($now) {
                $q->where(function ($sub) use ($now) {
                    $sub->whereNotNull('completed_at')
                        ->where('completed_at', '<=', $now->copy()->subHours(2))
                        ->where('completed_at', '>=', $now->copy()->subHours(24));
                })->orWhere(function ($sub) use ($now) {
                    $sub->whereNull('completed_at')
                        ->where('start_at', '<=', $now->copy()->subHours(2))
                        ->where('start_at', '>=', $now->copy()->subHours(24));
                });
            })
            ->whereNull('review_reminder_sent_at')
            ->whereDoesntHave('review')
            ->whereHas('customer', function ($q) use ($now) {
                $q->whereNull('last_google_redirect_at')
                  ->orWhere('last_google_redirect_at', '<=', $now->copy()->subDays(60));
            });

        $count = 0;

        $query->chunkById(50, function ($appointments) use (&$count, $pushService) {
            foreach ($appointments as $appointment) {
                try {
                    $title = 'Bugünkü kesimden memnun kaldınız mı? 💈';
                    $body = 'Hizmetimizi 1 dakikada değerlendirerek geri bildirimde bulunabilirsiniz.';
                    $payloadData = [
                        'action' => 'open_review',
                        'appointment_id' => (string) $appointment->id,
                    ];

                    // 1. Firebase Push bildirimi (cihaza kayıtlıysa)
                    if ($pushService) {
                        try {
                            $pushService->sendToUser(
                                $appointment->customer_id,
                                $title,
                                $body,
                                $payloadData
                            );
                        } catch (\Throwable $e) {
                            Log::warning("Review reminder push failed for appointment {$appointment->id}: " . $e->getMessage());
                        }
                    }

                    // 2. Uygulama içi bildirim kaydı
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

                    // 3. Randevuyu hatırlatıldı olarak işaretle (İdempotent kilit)
                    $appointment->update([
                        'review_reminder_sent_at' => now(),
                    ]);

                    $count++;
                } catch (\Throwable $e) {
                    Log::error("Failed to process review reminder for appointment {$appointment->id}: " . $e->getMessage());
                }
            }
        });

        $this->info("Toplam {$count} adet randevu için değerlendirme hatırlatması gönderildi.");

        return self::SUCCESS;
    }
}
