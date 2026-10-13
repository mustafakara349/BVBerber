<?php

namespace App\Services;

use App\Events\AdminNotificationCreated;
use App\Models\AdminNotification;
use App\Models\User;
use App\Services\AdminNotifications\AdminAlert;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Panel personeline yönelik iç sistem bildirimlerini oluşturan tek giriş noktası.
 *
 * - Alıcıları rol bazında çözümler (yalnızca aktif personel).
 * - Tüm alıcılar için tek bir toplu INSERT sorgusu çalıştırır.
 * - Bildirim üretimi asla asıl iş akışını (randevu, satış vb.) bozmamalıdır;
 *   bu nedenle hatalar yakalanıp loglanır.
 */
class AdminNotificationService
{
    public const MANAGEMENT_ROLES = ['super_admin', 'owner', 'manager'];

    public const FRONT_DESK_ROLES = ['super_admin', 'owner', 'manager', 'receptionist'];

    public function send(AdminAlert $alert): void
    {
        try {
            $recipientIds = $this->resolveRecipients($alert);

            if ($recipientIds === []) {
                return;
            }

            $now = now();
            $rows = array_map(fn (int $userId) => [
                'user_id' => $userId,
                'category' => $alert->category->value,
                'event' => $alert->event,
                'level' => $alert->level->value,
                'title' => mb_substr($alert->title, 0, 255),
                'body' => $alert->body,
                'action_url' => $alert->actionUrl,
                'action_label' => $alert->actionLabel,
                'subject_type' => $alert->subject?->getMorphClass(),
                'subject_id' => $alert->subject?->getKey(),
                'created_at' => $now,
                'updated_at' => $now,
            ], $recipientIds);

            AdminNotification::insert($rows);
        } catch (Throwable $e) {
            Log::error('[AdminNotification] Bildirim oluşturulamadı', [
                'event' => $alert->event,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        $this->broadcast($alert, $recipientIds);
    }

    /**
     * WebSocket üzerinden hafif bir "yeni bildirim" sinyali yayınlar.
     * İstemci sinyali alınca kendi listesini/rozetini feed uç noktasından tazeler;
     * böylece okunma durumu ve yetki kontrolleri tek yerde (sunucu) kalır.
     * WebSocket sunucusu kapalıysa bildirim yine kaydedilmiş olur, yalnızca anlık itme atlanır.
     *
     * @param  list<int>  $recipientIds
     */
    private function broadcast(AdminAlert $alert, array $recipientIds): void
    {
        try {
            event(new AdminNotificationCreated($recipientIds, [
                'event' => $alert->event,
                'category' => $alert->category->value,
                'level' => $alert->level->value,
                'title' => $alert->title,
                'body' => $alert->body,
            ]));
        } catch (Throwable $e) {
            Log::warning('[AdminNotification] Anlık yayın yapılamadı', [
                'event' => $alert->event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** @return list<int> */
    private function resolveRecipients(AdminAlert $alert): array
    {
        $roleUserIds = $alert->roles === []
            ? []
            : User::query()
                ->active()
                ->whereHas('role', fn ($q) => $q->whereIn('slug', $alert->roles))
                ->pluck('id')
                ->all();

        $extraIds = $alert->extraUserIds === []
            ? []
            : User::query()
                ->active()
                ->staff()
                ->whereIn('id', $alert->extraUserIds)
                ->pluck('id')
                ->all();

        return array_values(array_unique(array_map('intval', [...$roleUserIds, ...$extraIds])));
    }
}
