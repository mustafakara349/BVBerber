<?php

return [
    'name' => env('APP_NAME', 'B&V Barber'),
    'currency' => env('BV_CURRENCY', 'TRY'),
    'currency_symbol' => env('BV_CURRENCY_SYMBOL', '₺'),
    'timezone' => env('BV_TIMEZONE', 'Europe/Istanbul'),
    'date_format' => 'd.m.Y',
    'time_format' => 'H:i',
    'datetime_format' => 'd.m.Y H:i',
    'default_branch_id' => env('BV_DEFAULT_BRANCH_ID', 1),
    'appointment' => [
        'default_interval' => 30,
        'cancellation_limit_hours' => 2,
        'reminder_hours_before' => 2,
    ],
    'pagination' => [
        'default' => 15,
        'appointments' => 20,
        'customers' => 25,
    ],
    'cache' => [
        'dashboard_ttl' => 300,
        'analytics_ttl' => 600,
    ],
];
