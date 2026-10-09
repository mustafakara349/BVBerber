<?php

namespace App\Enums;

enum AdminNotificationLevel: string
{
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';

    /** Bootstrap renk anahtarı (bg-*-subtle / text-* sınıfları için). */
    public function color(): string
    {
        return match ($this) {
            self::Info => 'primary',
            self::Success => 'success',
            self::Warning => 'warning',
            self::Danger => 'danger',
        };
    }
}
