<?php

namespace App\Console\Commands;

use App\Enums\AdminNotificationCategory;
use App\Enums\AdminNotificationLevel;
use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\AdminNotification;
use App\Models\Appointment;
use App\Services\AdminNotificationService;
use App\Services\AdminNotifications\AdminAlert;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * İşlem bekleyen randevuları tarar ve panel personelini uyarır:
 *  - Saati yaklaşan / geçmiş ama hâlâ onay bekleyen randevular
 *  - Süresi dolmuş ama hâlâ kapatılmamış (onaylı / devam eden) randevular
 *  - Tamamlanmış ancak ödemesi (tamamen) alınmamış randevular
 *
 * Her randevu için aynı uyarı 24 saatte en fazla bir kez gönderilir.
 */
#[Signature('appointments:check-pending {--hours=2 : Onay bekleyenler için kaç saat ileriye bakılsın}')]
class CheckPendingAppointments extends Command
{
    protected $description = 'Onay veya ödeme bekleyen randevular için yönetici bildirimi üretir';

    private const REMIND_EVERY_HOURS = 24;

    /** Bu süreden eski açık randevular için hatırlatma yapılmaz. */
    private const LOOKBACK_DAYS = 30;

    public function handle(AdminNotificationService $notifications): int
    {
        $approval = $this->checkPendingApprovals($notifications);
        $overdue = $this->checkUnclosed($notifications);
        $payment = $this->checkUnpaid($notifications);

        $this->info("Onay: {$approval}, kapatılmamış: {$overdue}, ödeme: {$payment}");

        return self::SUCCESS;
    }

    private function checkPendingApprovals(AdminNotificationService $notifications): int
    {
        $sent = 0;

        Appointment::query()
            ->with(['customer', 'employee'])
            ->where('status', AppointmentStatus::Pending)
            ->where('start_at', '<=', now()->addHours((int) $this->option('hours')))
            ->where('start_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->chunkById(100, function ($appointments) use ($notifications, &$sent) {
                foreach ($appointments as $appointment) {
                    if ($this->remindedRecently($appointment, 'appointment.pending_reminder')) {
                        continue;
                    }

                    $overdue = $appointment->start_at->isPast();

                    $this->send($notifications, $appointment, 'appointment.pending_reminder',
                        $overdue ? AdminNotificationLevel::Danger : AdminNotificationLevel::Warning,
                        $overdue ? 'Saati Geçmiş Onaysız Randevu' : 'Yaklaşan Randevu Onay Bekliyor',
                        sprintf('#%s numaralı %s randevusu (%s, %s) hâlâ onay bekliyor.',
                            $appointment->appointment_code,
                            $appointment->customer?->full_name ?: 'Misafir müşteri',
                            $appointment->employee?->full_name ?: 'Personel',
                            $appointment->start_at->format('d.m.Y H:i')),
                        'İncele & Onayla');
                    $sent++;
                }
            });

        return $sent;
    }

    private function checkUnclosed(AdminNotificationService $notifications): int
    {
        $sent = 0;

        Appointment::query()
            ->with(['customer', 'employee'])
            ->whereIn('status', [AppointmentStatus::Confirmed, AppointmentStatus::InProgress])
            ->where('end_at', '<', now())
            ->where('end_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->chunkById(100, function ($appointments) use ($notifications, &$sent) {
                foreach ($appointments as $appointment) {
                    if ($this->remindedRecently($appointment, 'appointment.action_required')) {
                        continue;
                    }

                    $unpaid = $appointment->payment_status !== PaymentStatus::Paid;

                    $this->send($notifications, $appointment, 'appointment.action_required',
                        AdminNotificationLevel::Warning,
                        $unpaid ? 'İşlem ve Ödeme Bekleyen Randevu' : 'Kapatılmamış Randevu',
                        sprintf('#%s numaralı %s randevusunun (%s, %s) süresi doldu ancak henüz tamamlanmadı%s.',
                            $appointment->appointment_code,
                            $appointment->customer?->full_name ?: 'Misafir müşteri',
                            $appointment->employee?->full_name ?: 'Personel',
                            $appointment->start_at?->format('d.m.Y H:i') ?? '-',
                            $unpaid ? sprintf(' ve %s ₺ ödemesi alınmadı', number_format((float) $appointment->total_price, 2, ',', '.')) : ''),
                        $unpaid ? 'Tamamla & Ödeme Al' : 'Randevuyu Kapat');
                    $sent++;
                }
            });

        return $sent;
    }

    private function checkUnpaid(AdminNotificationService $notifications): int
    {
        $sent = 0;

        Appointment::query()
            ->with(['customer', 'employee'])
            ->withSum('payments', 'amount')
            ->where('status', AppointmentStatus::Completed)
            ->whereIn('payment_status', [PaymentStatus::Unpaid, PaymentStatus::Partial])
            ->where('total_price', '>', 0)
            ->chunkById(100, function ($appointments) use ($notifications, &$sent) {
                foreach ($appointments as $appointment) {
                    if ($this->remindedRecently($appointment, 'appointment.payment_pending')) {
                        continue;
                    }

                    $remaining = max(0, (float) $appointment->total_price - (float) $appointment->payments_sum_amount);
                    if ($remaining <= 0) {
                        continue;
                    }

                    $this->send($notifications, $appointment, 'appointment.payment_pending',
                        AdminNotificationLevel::Warning,
                        'Ödeme Alınması Gerekiyor',
                        sprintf('#%s numaralı %s randevusu (%s) için %s ₺ tutarında ödeme bekleniyor.',
                            $appointment->appointment_code,
                            $appointment->customer?->full_name ?: 'Misafir müşteri',
                            $appointment->start_at?->format('d.m.Y H:i') ?? '-',
                            number_format($remaining, 2, ',', '.')),
                        'Ödeme Al');
                    $sent++;
                }
            });

        return $sent;
    }

    private function send(
        AdminNotificationService $notifications,
        Appointment $appointment,
        string $event,
        AdminNotificationLevel $level,
        string $title,
        string $body,
        string $actionLabel,
    ): void {
        $barberUserId = $appointment->employee?->user_id;

        $notifications->send(new AdminAlert(
            category: AdminNotificationCategory::Appointment,
            event: $event,
            level: $level,
            title: $title,
            body: $body,
            roles: AdminNotificationService::FRONT_DESK_ROLES,
            actionUrl: route('appointments.show', $appointment, false),
            actionLabel: $actionLabel,
            subject: $appointment,
            extraUserIds: $barberUserId ? [$barberUserId] : [],
        ));
    }

    private function remindedRecently(Appointment $appointment, string $event): bool
    {
        return AdminNotification::query()
            ->where('event', $event)
            ->where('subject_type', $appointment->getMorphClass())
            ->where('subject_id', $appointment->getKey())
            ->where('created_at', '>=', now()->subHours(self::REMIND_EVERY_HOURS))
            ->exists();
    }
}
