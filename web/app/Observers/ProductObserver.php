<?php

namespace App\Observers;

use App\Enums\AdminNotificationCategory;
use App\Enums\AdminNotificationLevel;
use App\Models\Product;
use App\Services\AdminNotificationService;
use App\Services\AdminNotifications\AdminAlert;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * Ürün stoğu kritik seviyeye düştüğünde / tükendiğinde yönetimi uyarır.
 *
 * Bildirim yalnızca eşik "aşağı yönde geçildiği" anda üretilir; stok zaten
 * kritik seviyedeyken yapılan her satış için tekrar tekrar bildirim gönderilmez.
 */
class ProductObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly AdminNotificationService $notifications)
    {
    }

    public function updated(Product $product): void
    {
        if (! $product->wasChanged('stock_quantity')) {
            return;
        }

        // Commit sonrası çalıştığımız için getOriginal() güncel değeri döner; önceki değer getPrevious() ile okunur.
        $previous = (int) ($product->getPrevious()['stock_quantity'] ?? $product->stock_quantity);
        $current = (int) $product->stock_quantity;
        $threshold = $this->threshold($product);

        if ($current <= 0 && $previous > 0) {
            $this->notify(
                $product,
                'stock.out',
                AdminNotificationLevel::Danger,
                'Stok Tükendi',
                "\"{$product->name}\" ürününün stoğu tükendi. Satış ve kullanım için yeniden stok girişi yapılmalı."
            );

            return;
        }

        if ($current > 0 && $current <= $threshold && $previous > $threshold) {
            $this->notify(
                $product,
                'stock.low',
                AdminNotificationLevel::Warning,
                'Kritik Stok Uyarısı',
                "\"{$product->name}\" ürününün stoğu kritik seviyeye düştü. Kalan: {$current} adet (Kritik seviye: {$threshold})."
            );
        }
    }

    private function threshold(Product $product): int
    {
        return (int) ($product->critical_stock ?? config('admin_notifications.default_critical_stock'));
    }

    private function notify(Product $product, string $event, AdminNotificationLevel $level, string $title, string $body): void
    {
        $this->notifications->send(new AdminAlert(
            category: AdminNotificationCategory::Stock,
            event: $event,
            level: $level,
            title: $title,
            body: $body,
            roles: AdminNotificationService::MANAGEMENT_ROLES,
            actionUrl: route('products.edit', $product, false),
            actionLabel: 'Ürünü Güncelle',
            subject: $product,
        ));
    }
}
