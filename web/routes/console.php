<?php

use Illuminate\Support\Facades\Schedule;

// İşlem (onay / ödeme) bekleyen randevular için yönetici hatırlatmaları
Schedule::command('appointments:check-pending')->hourly()->withoutOverlapping();

// Tamamlanan randevulardan 2 saat sonra müşteriye değerlendirme hatırlatması
Schedule::command('appointments:send-review-reminders')->everyFifteenMinutes()->withoutOverlapping();

