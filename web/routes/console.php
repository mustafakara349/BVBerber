<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// İşlem (onay / ödeme) bekleyen randevular için yönetici hatırlatmaları
Schedule::command('appointments:check-pending')->hourly()->withoutOverlapping();

// Tamamlanan randevulardan 2 saat sonra müşteriye değerlendirme hatırlatması
Schedule::command('appointments:send-review-reminders')->everyFifteenMinutes()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
