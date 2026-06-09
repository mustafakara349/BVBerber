<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Partial = 'partial';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Ödenmedi',
            self::Paid => 'Ödendi',
            self::Partial => 'Kısmi',
            self::Refunded => 'İade Edildi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Unpaid => 'danger',
            self::Paid => 'success',
            self::Partial => 'warning',
            self::Refunded => 'info',
        };
    }
}
