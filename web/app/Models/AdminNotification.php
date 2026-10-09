<?php

namespace App\Models;

use App\Enums\AdminNotificationCategory;
use App\Enums\AdminNotificationLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Panel personeline özel, aksiyon alınabilir sistem bildirimi.
 *
 * Kayıtlar yalnızca uygulama içi olaylar (Observer'lar) tarafından
 * {@see \App\Services\AdminNotificationService} aracılığıyla oluşturulur;
 * HTTP üzerinden oluşturma uç noktası bilinçli olarak bulunmamaktadır.
 */
class AdminNotification extends Model
{
    protected $fillable = [
        'user_id', 'category', 'event', 'level', 'title', 'body',
        'action_url', 'action_label', 'subject_type', 'subject_id', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => AdminNotificationCategory::class,
            'level' => AdminNotificationLevel::class,
            'read_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // İlişkiler
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // -------------------------------------------------------------------------
    // Scope'lar
    // -------------------------------------------------------------------------

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    // -------------------------------------------------------------------------
    // Yardımcılar
    // -------------------------------------------------------------------------

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (! $this->isRead()) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    /**
     * Yalnızca uygulama içi göreli yolları döndürür (ör. "/appointments/..").
     * Harici/protokol-göreli ("//") veya "javascript:" gibi değerler reddedilerek
     * açık yönlendirme (open redirect) ve XSS riskleri ortadan kaldırılır.
     */
    public function safeActionUrl(): ?string
    {
        $url = $this->action_url;

        if (! is_string($url) || $url === '' || ! str_starts_with($url, '/') || str_starts_with($url, '//') || str_contains($url, '\\')) {
            return null;
        }

        return $url;
    }

    /** Canlı akış (topbar) için hafif, güvenli JSON temsili. */
    public function toFeedArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'icon' => $this->category->icon(),
            'color' => $this->level->color(),
            'is_read' => $this->isRead(),
            'time' => $this->created_at?->diffForHumans(),
            'open_url' => route('notifications.open', $this),
        ];
    }
}
