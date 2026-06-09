<?php

namespace App\Enums;

enum SalaryType: string
{
    case Fixed = 'fixed';
    case Commission = 'commission';
    case FixedPlusCommission = 'fixed_plus_commission';
    case Hourly = 'hourly';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Sabit Maaş',
            self::Commission => 'Komisyon',
            self::FixedPlusCommission => 'Sabit + Komisyon',
            self::Hourly => 'Saatlik',
        };
    }
}
