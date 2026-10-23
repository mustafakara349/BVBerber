<?php

namespace App\Events;

use App\Models\AuditLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Yeni bir sistem işlem hareketi veya hata logu kaydedildiğinde
 * Reverb WebSocket özel kanalına (audit-logs) anlık yayın yapar.
 */
class AuditLogCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public readonly array $payload;

    public function __construct(AuditLog $auditLog)
    {
        if ($auditLog->user_id && !$auditLog->relationLoaded('user')) {
            $auditLog->load('user');
        }

        $isError = $auditLog->status === 'error'
            || $auditLog->status === 'failed'
            || $auditLog->model_type === 'exception'
            || $auditLog->action === 'system.exception';

        $tab = match (true) {
            $isError => 'errors',
            str_starts_with($auditLog->action, 'auth.') => 'security',
            !in_array($auditLog->model_type, ['http_request', 'exception']) => 'models',
            default => 'all',
        };

        $this->payload = [
            'id' => $auditLog->id,
            'created_at' => $auditLog->created_at ? $auditLog->created_at->format('d.m.Y H:i:s') : now()->format('d.m.Y H:i:s'),
            'created_at_human' => 'Az önce',
            'action' => $auditLog->action,
            'action_label' => $auditLog->action_label_tr,
            'action_badge' => $auditLog->action_badge,
            'model_name' => $auditLog->model_name_tr,
            'model_id' => $auditLog->model_id,
            'target_url' => $auditLog->target_url,
            'human_description' => $auditLog->human_description,
            'status' => $auditLog->status,
            'is_error' => $isError,
            'ip_address' => $auditLog->ip_address ?? '127.0.0.1',
            'show_url' => $auditLog->id ? route('audit-logs.show', $auditLog->id, false) : '#',
            'tab' => $tab,
            'user' => $auditLog->user ? [
                'id' => $auditLog->user->id,
                'name' => $auditLog->user->full_name,
                'email' => $auditLog->user->email,
                'initials' => mb_substr($auditLog->user->first_name, 0, 1) . mb_substr($auditLog->user->last_name, 0, 1),
                'photo_url' => $auditLog->user->profile_photo_url,
            ] : null,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('audit-logs'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'audit.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
