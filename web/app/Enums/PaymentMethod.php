<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case CreditCard = 'credit_card';
    case BankTransfer = 'bank_transfer';
    case Online = 'online';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Nakit',
            self::CreditCard => 'Kredi Kartı',
            self::BankTransfer => 'Banka Transferi',
            self::Online => 'Online',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cash => 'ti-cash',
            self::CreditCard => 'ti-credit-card',
            self::BankTransfer => 'ti-building-bank',
            self::Online => 'ti-device-laptop',
        };
    }
}
