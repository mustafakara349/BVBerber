<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Refund = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Gelir',
            self::Expense => 'Gider',
            self::Refund => 'İade',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Income => 'success',
            self::Expense => 'danger',
            self::Refund => 'warning',
        };
    }
}
