<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
    case NoShow = 'no_show';
    case InProgress = 'in_progress';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Beklemede',
            self::Confirmed => 'Onaylandı',
            self::Completed => 'Tamamlandı',
            self::Cancelled => 'İptal Edildi',
            self::Rejected => 'Reddedildi',
            self::NoShow => 'Gelmedi',
            self::InProgress => 'Devam Ediyor',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::Completed => 'success',
            self::Cancelled => 'danger',
            self::Rejected => 'danger',
            self::NoShow => 'secondary',
            self::InProgress => 'primary',
        };
    }
}
