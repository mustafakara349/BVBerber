<?php

namespace App\Observers;

use App\Enums\AdminNotificationCategory;
use App\Enums\AdminNotificationLevel;
use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\AdminNotification;
use App\Models\Appointment;
use App\Services\AdminNotificationService;
use App\Services\AdminNotifications\AdminAlert;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * Randevu yaşam döngüsündeki kritik olayları panel personeline bildirir.
 * ShouldHandleEventsAfterCommit: geri alınan (rollback) işlemler için bildirim üretilmez.
 *
 * Not: Aynı transaction içinde model birden fazla kez kaydedildiğinde (ör. tamamla + ödeme al)
 * after-commit geri çağrıları son durumu görür. Bu nedenle ödeme kararları veritabanındaki
 * nihai kayıt üzerinden verilir ve tekrar eden bildirimler engellenir.
 */
class AppointmentObserver implements ShouldHandleEventsAfterCommit
{
    /** Aynı olay için tekrar bildirim üretilmeyecek süre (dakika). */
    private const DEDUPE_MINUTES = 10;

    public function __construct(private readonly AdminNotificationService $notifications)
    {
    }

    public function created(Appointment $appointment): void
    {
        $appointment->loadMissing(['customer', 'employee.user']);

        $source = $appointment->source?->label();
        $sourceText = $source ? " (Kaynak: {$source})" : '';

        if ($appointment->status === AppointmentStatus::Pending) {
            $body = sprintf(
                '%s, %s için %s tarihine randevu talebi oluşturdu ve onayınızı bekliyor.%s',
                $this->customerName($appointment),
                $this->employeeName($appointment),
                $this->startText($appointment),
                $sourceText
            );

            $this->notify($appointment, 'appointment.pending_approval', AdminNotificationLevel::Warning,
                'Onay Bekleyen Randevu', $body, 'İncele & Onayla');

            return;
        }

        $body = sprintf(
            '%s, %s için %s tarihine randevu oluşturdu.%s',
            $this->customerName($appointment),
            $this->employeeName($appointment),
            $this->startText($appointment),
            $sourceText
        );

        $this->notify($appointment, 'appointment.created', AdminNotificationLevel::Info, 'Yeni Randevu', $body);
    }

    public function updated(Appointment $appointment): void
    {
        $statusChanged = $appointment->wasChanged('status');
        $paymentChanged = $appointment->wasChanged('payment_status');

        if (! $statusChanged && ! $paymentChanged) {
            return;
        }

        // Commit sonrası nihai durum
        $fresh = $appointment->fresh(['customer', 'employee.user']);
        if (! $fresh) {
            return;
        }

        if ($statusChanged) {
            $this->handleStatusChange($fresh);
        }

        $this->handlePaymentState($fresh);
    }

    private function handleStatusChange(Appointment $appointment): void
    {
        [$event, $level, $title, $verb] = match ($appointment->status) {
            AppointmentStatus::Completed => ['appointment.completed', AdminNotificationLevel::Success, 'Randevu Tamamlandı', 'tamamlandı'],
            AppointmentStatus::Cancelled => ['appointment.cancelled', AdminNotificationLevel::Danger, 'Randevu İptal Edildi', 'iptal edildi'],
            AppointmentStatus::Rejected => ['appointment.rejected', AdminNotificationLevel::Danger, 'Randevu Reddedildi', 'reddedildi'],
            AppointmentStatus::NoShow => ['appointment.no_show', AdminNotificationLevel::Warning, 'Müşteri Gelmedi', 'gelinmedi olarak işaretlendi'],
            default => [null, null, null, null],
        };

        if ($event === null || $this->recentlyNotified($appointment, $event)) {
            return;
        }

        $body = sprintf(
            '#%s numaralı %s randevusu (%s, %s) %s.',
            $appointment->appointment_code,
            $this->customerName($appointment),
            $this->employeeName($appointment),
            $this->startText($appointment),
            $verb
        );

        $this->notify($appointment, $event, $level, $title, $body);
    }

    /**
     * Tamamlanmış randevunun ödeme durumuna göre "ödeme bekleniyor" veya "ödeme alındı" bildirimi.
     */
    private function handlePaymentState(Appointment $appointment): void
    {
        if ($appointment->status !== AppointmentStatus::Completed) {
            return;
        }

        if ($appointment->payment_status === PaymentStatus::Paid) {
            // Yalnızca daha önce "ödeme bekleniyor" uyarısı verilmişse kapanış bildirimi gönder
            if (! $this->hasNotification($appointment, 'appointment.payment_pending')
                || $this->recentlyNotified($appointment, 'appointment.payment_received')) {
                return;
            }

            $this->notify($appointment, 'appointment.payment_received', AdminNotificationLevel::Success,
                'Ödeme Tahsil Edildi',
                sprintf('#%s numaralı %s randevusunun ödemesi tamamlandı (%s ₺).',
                    $appointment->appointment_code,
                    $this->customerName($appointment),
                    number_format((float) $appointment->total_price, 2, ',', '.')
                ));

            return;
        }

        if (! in_array($appointment->payment_status, [PaymentStatus::Unpaid, PaymentStatus::Partial], true)
            || $this->recentlyNotified($appointment, 'appointment.payment_pending')) {
            return;
        }

        $paid = (float) $appointment->payments()->sum('amount');
        $remaining = max(0, (float) $appointment->total_price - $paid);

        $this->notify($appointment, 'appointment.payment_pending', AdminNotificationLevel::Warning,
            'Ödeme Alınması Gerekiyor',
            sprintf('#%s numaralı %s randevusu tamamlandı ancak %s ₺ tutarında ödeme bekleniyor.',
                $appointment->appointment_code,
                $this->customerName($appointment),
                number_format($remaining, 2, ',', '.')
            ),
            'Ödeme Al');
    }

    private function notify(
        Appointment $appointment,
        string $event,
        AdminNotificationLevel $level,
        string $title,
        string $body,
        string $actionLabel = 'Randevuya Git',
    ): void {
        $barberUserId = $appointment->employee?->user_id;

        $this->notifications->send(new AdminAlert(
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

    private function hasNotification(Appointment $appointment, string $event): bool
    {
        return AdminNotification::query()
            ->where('event', $event)
            ->where('subject_type', $appointment->getMorphClass())
            ->where('subject_id', $appointment->getKey())
            ->exists();
    }

    private function recentlyNotified(Appointment $appointment, string $event): bool
    {
        return AdminNotification::query()
            ->where('event', $event)
            ->where('subject_type', $appointment->getMorphClass())
            ->where('subject_id', $appointment->getKey())
            ->where('created_at', '>=', now()->subMinutes(self::DEDUPE_MINUTES))
            ->exists();
    }

    private function startText(Appointment $appointment): string
    {
        return $appointment->start_at?->format('d.m.Y H:i') ?? '-';
    }

    private function customerName(Appointment $appointment): string
    {
        return $appointment->customer?->full_name ?: 'Misafir müşteri';
    }

    private function employeeName(Appointment $appointment): string
    {
        return $appointment->employee?->full_name ?: 'Personel';
    }
}
