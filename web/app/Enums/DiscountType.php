<?php

namespace App\Enums;

enum DiscountType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Yüzde',
            self::Fixed => 'Sabit Tutar',
        };
    }
}
