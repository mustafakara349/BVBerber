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
    | feed_poll_seconds: Topbar ve bildirim sayfasının canlı akış için sunucuyu
    | kontrol etme aralığı (saniye).
    |
    */

    'default_critical_stock' => (int) env('ADMIN_NOTIFICATIONS_CRITICAL_STOCK', 5),

    'feed_poll_seconds' => (int) env('ADMIN_NOTIFICATIONS_POLL_SECONDS', 20),

    'feed_limit' => 8,

];
