<?php

namespace App\Enums;

enum AppointmentSource: string
{
    case MobileApp = 'mobile_app';
    case WalkIn = 'walk_in';
    case AdminPanel = 'admin_panel';
    case Phone = 'phone';
    case Instagram = 'instagram';
    case Website = 'website';

    public function label(): string
    {
        return match ($this) {
            self::MobileApp => 'Mobil Uygulama',
            self::WalkIn => 'Walk-in',
            self::AdminPanel => 'Admin Paneli',
            self::Phone => 'Telefon',
            self::Instagram => 'Instagram',
            self::Website => 'Website',
        };
    }
}
