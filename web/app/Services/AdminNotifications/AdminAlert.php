<?php

namespace App\Services\AdminNotifications;

use App\Enums\AdminNotificationCategory;
use App\Enums\AdminNotificationLevel;
use Illuminate\Database\Eloquent\Model;

/**
 * Bir panel bildiriminin değişmez (immutable) tanımı.
 * Alıcı çözümleme ve kalıcılaştırma {@see \App\Services\AdminNotificationService} tarafından yapılır.
 */
final readonly class AdminAlert
{
    /**
     * @param  list<string>  $roles        Bildirimi alacak rol slug'ları
     * @param  list<int>     $extraUserIds Rol dışında ayrıca bildirilecek kullanıcılar (ör. ilgili berber)
     */
    public function __construct(
        public AdminNotificationCategory $category,
        public string $event,
        public AdminNotificationLevel $level,
        public string $title,
        public string $body,
        public array $roles,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
        public ?Model $subject = null,
        public array $extraUserIds = [],
    ) {
    }
}
