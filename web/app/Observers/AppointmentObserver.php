<?php

namespace App\Observers;

use App\Enums\AdminNotificationCategory;
use App\Enums\AdminNotificationLevel;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\AdminNotificationService;
use App\Services\AdminNotifications\AdminAlert;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * Randevu yaşam döngüsündeki kritik olayları panel personeline bildirir.
 * ShouldHandleEventsAfterCommit: geri alınan (rollback) işlemler için bildirim üretilmez.
 */
class AppointmentObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly AdminNotificationService $notifications)
    {
    }

    public function created(Appointment $appointment): void
    {
        $appointment->loadMissing(['customer', 'employee.user']);

        $source = $appointment->source?->label();
        $body = sprintf(
            '%s, %s için %s tarihine randevu oluşturdu.%s',
            $this->customerName($appointment),
            $this->employeeName($appointment),
            $appointment->start_at?->format('d.m.Y H:i') ?? '-',
            $source ? " (Kaynak: {$source})" : ''
        );

        $this->notify($appointment, 'appointment.created', AdminNotificationLevel::Info, 'Yeni Randevu', $body);
    }

    public function updated(Appointment $appointment): void
    {
        if (! $appointment->wasChanged('status')) {
            return;
        }

        [$event, $level, $title, $verb] = match ($appointment->status) {
            AppointmentStatus::Completed => ['appointment.completed', AdminNotificationLevel::Success, 'Randevu Tamamlandı', 'tamamlandı'],
            AppointmentStatus::Cancelled => ['appointment.cancelled', AdminNotificationLevel::Danger, 'Randevu İptal Edildi', 'iptal edildi'],
            AppointmentStatus::Rejected => ['appointment.rejected', AdminNotificationLevel::Danger, 'Randevu Reddedildi', 'reddedildi'],
            AppointmentStatus::NoShow => ['appointment.no_show', AdminNotificationLevel::Warning, 'Müşteri Gelmedi', 'gelinmedi olarak işaretlendi'],
            default => [null, null, null, null],
        };

        if ($event === null) {
            return;
        }

        $appointment->loadMissing(['customer', 'employee.user']);

        $body = sprintf(
            '#%s numaralı %s randevusu (%s, %s) %s.',
            $appointment->appointment_code,
            $this->customerName($appointment),
            $this->employeeName($appointment),
            $appointment->start_at?->format('d.m.Y H:i') ?? '-',
            $verb
        );

        $this->notify($appointment, $event, $level, $title, $body);
    }

    private function notify(Appointment $appointment, string $event, AdminNotificationLevel $level, string $title, string $body): void
    {
        $barberUserId = $appointment->employee?->user_id;

        $this->notifications->send(new AdminAlert(
            category: AdminNotificationCategory::Appointment,
            event: $event,
            level: $level,
            title: $title,
            body: $body,
            roles: AdminNotificationService::FRONT_DESK_ROLES,
            actionUrl: route('appointments.show', $appointment, false),
            actionLabel: 'Randevuya Git',
            subject: $appointment,
            extraUserIds: $barberUserId ? [$barberUserId] : [],
        ));
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
