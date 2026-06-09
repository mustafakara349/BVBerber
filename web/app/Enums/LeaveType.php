<?php

namespace App\Enums;

enum LeaveType: string
{
    case Annual = 'annual';
    case Sick = 'sick';
    case Unpaid = 'unpaid';
    case Official = 'official';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Annual => 'Yıllık İzin',
            self::Sick => 'Hastalık İzni',
            self::Unpaid => 'Ücretsiz İzin',
            self::Official => 'Resmi İzin',
            self::Other => 'Diğer',
        };
    }
}
