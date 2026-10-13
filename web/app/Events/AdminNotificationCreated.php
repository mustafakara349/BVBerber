<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Yeni bir panel bildirimi oluşturulduğunda ilgili personelin özel kanalına anlık yayın yapar.
 *
 * Yük (payload) bilinçli olarak hafiftir: istemci bu sinyali alınca listeyi/rozeti günceller.
 * Tek bir olay, aynı bildirimi alan tüm kullanıcılara (çoklu kanal) tek seferde yayınlanır.
 */
class AdminNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @param  list<int>  $userIds
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly array $userIds,
        public readonly array $payload,
    ) {
    }

    /** @return list<PrivateChannel> */
    public function broadcastOn(): array
    {
        return array_map(
            fn (int $id) => new PrivateChannel("admin.notifications.{$id}"),
            $this->userIds
        );
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
