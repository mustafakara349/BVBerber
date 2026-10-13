<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Panel (İç) Sistem Bildirimleri
    |--------------------------------------------------------------------------
    |
    | default_critical_stock: Ürün kartında "kritik stok" değeri girilmemişse
    | stok uyarısı için kullanılacak varsayılan alt limit.
    |
    | feed_poll_seconds: Yalnızca YEDEK moddur. Bildirimler WebSocket (Reverb)
    | ile anlık iletilir; bağlantı kurulamadığında/koptuğunda topbar bu aralıkla
    | sunucuyu kontrol eder (saniye).
    |
    */

    'default_critical_stock' => (int) env('ADMIN_NOTIFICATIONS_CRITICAL_STOCK', 5),

    'feed_poll_seconds' => (int) env('ADMIN_NOTIFICATIONS_POLL_SECONDS', 60),

    'feed_limit' => 8,

];
