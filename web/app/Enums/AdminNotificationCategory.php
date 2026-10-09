<?php

namespace App\Enums;

enum AdminNotificationCategory: string
{
    case Appointment = 'appointment';
    case Stock = 'stock';
    case Review = 'review';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Appointment => 'Randevu',
            self::Stock => 'Stok',
            self::Review => 'Değerlendirme',
            self::System => 'Sistem',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Appointment => 'ti-calendar-event',
            self::Stock => 'ti-package',
            self::Review => 'ti-star',
            self::System => 'ti-settings',
        };
    }
}
