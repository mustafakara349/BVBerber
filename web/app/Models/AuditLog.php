<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'status',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Safely decode new_values array whether stored as JSON string or array.
     */
    public function getParsedNewValuesAttribute(): array
    {
        $val = $this->new_values;
        if (is_array($val)) {
            return $val;
        }
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Safely decode old_values array whether stored as JSON string or array.
     */
    public function getParsedOldValuesAttribute(): ?array
    {
        $val = $this->old_values;
        if (is_array($val)) {
            return $val;
        }
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }

    /**
     * Returns a human-friendly Turkish action label.
     */
    public function getActionLabelTrAttribute(): string
    {
        $action = strtolower($this->action);

        if (str_starts_with($action, 'auth.login.success')) {
            return 'Giriş Başarılı';
        }
        if (str_starts_with($action, 'auth.login.failed')) {
            return 'Giriş Başarısız';
        }
        if (str_starts_with($action, 'auth.logout')) {
            return 'Çıkış Yapıldı';
        }
        if (str_starts_with($action, 'auth.password')) {
            return 'Şifre İşlemi';
        }
        if ($action === 'system.exception' || $this->status === 'error' || $this->model_type === 'exception') {
            return 'Sistem Hatası';
        }
        if (str_ends_with($action, '.created') || str_starts_with($action, 'post:')) {
            return 'Oluşturuldu';
        }
        if (str_ends_with($action, '.updated') || str_starts_with($action, 'put:') || str_starts_with($action, 'patch:')) {
            return 'Güncellendi';
        }
        if (str_ends_with($action, '.deleted') || str_starts_with($action, 'delete:')) {
            return 'Silindi';
        }

        return ucfirst(str_replace(['.', '_', ':'], ' ', $action));
    }

    /**
     * Returns badge color classes and tabler icon for this action.
     */
    public function getActionBadgeAttribute(): array
    {
        $action = strtolower($this->action);

        if ($this->status === 'error' || $action === 'system.exception' || $this->model_type === 'exception') {
            return [
                'class' => 'bg-danger-subtle text-danger border-0',
                'icon' => 'ti ti-alert-triangle',
                'label' => 'Hata'
            ];
        }

        if (str_starts_with($action, 'auth.login.failed')) {
            return [
                'class' => 'bg-danger-subtle text-danger border-0',
                'icon' => 'ti ti-shield-x',
                'label' => 'Güvenlik'
            ];
        }

        if (str_starts_with($action, 'auth.')) {
            return [
                'class' => 'bg-indigo-subtle text-indigo border-0',
                'icon' => 'ti ti-shield-lock',
                'label' => 'Oturum'
            ];
        }

        if (str_ends_with($action, '.created') || str_starts_with($action, 'post:')) {
            return [
                'class' => 'bg-success-subtle text-success border-0',
                'icon' => 'ti ti-plus',
                'label' => 'Ekleme'
            ];
        }

        if (str_ends_with($action, '.updated') || str_starts_with($action, 'put:') || str_starts_with($action, 'patch:')) {
            return [
                'class' => 'bg-primary-subtle text-primary border-0',
                'icon' => 'ti ti-pencil',
                'label' => 'Güncelleme'
            ];
        }

        if (str_ends_with($action, '.deleted') || str_starts_with($action, 'delete:')) {
            return [
                'class' => 'bg-danger-subtle text-danger border-0',
                'icon' => 'ti ti-trash',
                'label' => 'Silme'
            ];
        }

        return [
            'class' => 'bg-secondary-subtle text-secondary border-0',
            'icon' => 'ti ti-activity',
            'label' => 'İşlem'
        ];
    }

    /**
     * Returns a human-friendly Turkish model name.
     */
    public function getModelNameTrAttribute(): string
    {
        $type = strtolower(class_basename($this->model_type));

        return match ($type) {
            'appointment' => 'Randevu',
            'appointmentservice' => 'Randevu Hizmeti',
            'appointmenttimeblock' => 'Zaman Engeli',
            'user' => 'Kullanıcı / Müşteri',
            'employee' => 'Çalışan',
            'employeeschedule' => 'Çalışma Takvimi',
            'employeeleave' => 'Personel İzni',
            'transaction' => 'Kasa Hareketi',
            'payment' => 'Ödeme',
            'expense' => 'Gider',
            'debt' => 'Borç / Alacak',
            'loyaltyaccount' => 'Sadakat Hesabı',
            'loyaltytransaction' => 'Sadakat Puanı',
            'service' => 'Hizmet',
            'servicecategory' => 'Hizmet Kategorisi',
            'product' => 'Ürün',
            'productsale' => 'Ürün Satışı',
            'stockmovement' => 'Stok Hareketi',
            'stockcount' => 'Stok Sayımı',
            'cafeproduct' => 'Kafe Ürünü',
            'review' => 'Değerlendirme',
            'campaign' => 'Kampanya',
            'coupon' => 'Kupon',
            'setting' => 'Sistem Ayarı',
            'branchsetting' => 'Şube Ayarı',
            'exception' => 'Sistem İstisnası',
            'http_request' => 'HTTP İsteği',
            default => ucfirst($type),
        };
    }

    /**
     * Returns a clean, human-friendly Turkish description for the table view.
     */
    public function getHumanDescriptionAttribute(): string
    {
        $parsedNew = $this->parsed_new_values;
        $rawDetails = $parsedNew['details'] ?? ($parsedNew['data']['message'] ?? ($parsedNew['message'] ?? null));

        // 1. If it's an error or exception
        if ($this->status === 'error' || $this->model_type === 'exception') {
            if ($rawDetails && is_string($rawDetails)) {
                return \Illuminate\Support\Str::limit($rawDetails, 160);
            }
            return 'Sistem veya sunucu işlem hatası oluştu.';
        }

        // 2. Auth events
        if ($this->action === 'auth.login.success') {
            return 'Kullanıcı sisteme başarıyla giriş yaptı.';
        }
        if (str_starts_with($this->action, 'auth.login.failed')) {
            return 'Hatalı bilgilerle sisteme giriş denemesi yapıldı.';
        }
        if (str_starts_with($this->action, 'auth.logout')) {
            return 'Kullanıcı oturumu başarıyla sonlandırıldı.';
        }
        if (str_starts_with($this->action, 'auth.password')) {
            return 'Şifre güncelleme veya sıfırlama işlemi gerçekleştirildi.';
        }

        // 3. Technical Eloquent events ("Model created/updated/deleted event for App\Models\...")
        if ($rawDetails && is_string($rawDetails) && preg_match('/Model (created|updated|deleted) event for/i', $rawDetails, $matches)) {
            $eventType = strtolower($matches[1]);
            $modelTr = $this->model_name_tr;

            return match ($eventType) {
                'created' => "Yeni {$modelTr} kaydı başarıyla oluşturuldu.",
                'updated' => "{$modelTr} bilgileri güncellendi.",
                'deleted' => "{$modelTr} kaydı sistemden silindi.",
                default => "{$modelTr} işlemi tamamlandı."
            };
        }

        // 4. HTTP Request events ("HTTP POST appointments/... (Status: 302)")
        if ($this->model_type === 'http_request' || str_starts_with($this->action, 'post:') || str_starts_with($this->action, 'put:') || str_starts_with($this->action, 'delete:') || str_starts_with($this->action, 'patch:')) {
            if ($rawDetails && is_string($rawDetails) && preg_match('/HTTP\s+(POST|PUT|PATCH|DELETE)\s+([^\s\(]+)(?:\s+\(Status:\s*(\d+)\))?/i', $rawDetails, $m)) {
                $path = $m[2];
                $status = $m[3] ?? '200';

                $endpointTr = match (true) {
                    str_contains($path, 'complete-payment') => 'Ödeme Tamamlama İşlemi',
                    str_contains($path, 'appointments') => 'Randevu İşlemi',
                    str_contains($path, 'transactions') => 'Kasa / Finans İşlemi',
                    str_contains($path, 'customers') => 'Müşteri İşlemi',
                    str_contains($path, 'employees') => 'Personel İşlemi',
                    str_contains($path, 'reviews') => 'Değerlendirme İşlemi',
                    str_contains($path, 'stock') => 'Stok Hareketi',
                    str_contains($path, 'services') => 'Hizmet İşlemi',
                    str_contains($path, 'products') => 'Ürün İşlemi',
                    str_contains($path, 'settings') => 'Sistem Ayarları Değişikliği',
                    default => 'Web İsteği'
                };

                return "{$endpointTr} başarıyla tamamlandı (Kod: {$status}).";
            }
        }

        // 5. If rawDetails is a clean custom string (not raw PHP event string)
        if ($rawDetails && is_string($rawDetails) && !str_starts_with($rawDetails, 'Model ') && !str_starts_with($rawDetails, 'HTTP ')) {
            return \Illuminate\Support\Str::limit($rawDetails, 160);
        }

        // 6. Action-based clean fallback
        $action = strtolower($this->action);
        $modelTr = $this->model_name_tr;

        if (str_ends_with($action, '.created') || str_starts_with($action, 'post:')) {
            return "Yeni {$modelTr} kaydı başarıyla oluşturuldu.";
        }
        if (str_ends_with($action, '.updated') || str_starts_with($action, 'put:') || str_starts_with($action, 'patch:')) {
            return "{$modelTr} bilgileri güncellendi.";
        }
        if (str_ends_with($action, '.deleted') || str_starts_with($action, 'delete:')) {
            return "{$modelTr} kaydı sistemden silindi.";
        }

        return "{$modelTr} işlemi gerçekleştirildi.";
    }

    /**
     * Returns clickable target URL if the affected model has a detail route.
     */
    public function getTargetUrlAttribute(): ?string
    {
        if (!$this->model_id) {
            return null;
        }

        $type = strtolower(class_basename($this->model_type));

        try {
            return match ($type) {
                'appointment' => route('appointments.show', $this->model_id),
                'user' => $this->resolveUserUrl(),
                'employee' => route('employees.index'),
                'transaction' => route('finance.transactions.show', $this->model_id),
                'expense' => route('finance.expenses'),
                'debt' => route('finance.receivables.index'),
                'service' => route('services.index'),
                'product' => route('products.index'),
                'cafeproduct' => route('cafe.index'),
                'campaign' => route('campaigns.index'),
                'review' => route('reviews.show', $this->model_id),
                'setting', 'branchsetting' => route('settings.index'),
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolves target URL for a User model without triggering 404 for non-customers.
     */
    protected function resolveUserUrl(): ?string
    {
        static $userRoleCache = [];

        if (!array_key_exists($this->model_id, $userRoleCache)) {
            $userRoleCache[$this->model_id] = User::where('id', $this->model_id)->value('role_id');
        }

        $roleId = $userRoleCache[$this->model_id];
        if (!$roleId) {
            return null;
        }

        static $customerRoleId = null;
        if ($customerRoleId === null) {
            $customerRoleId = Role::where('slug', 'customer')->value('id') ?? 4;
        }

        if ($roleId === $customerRoleId) {
            return route('customers.show', $this->model_id);
        }

        return route('employees.index');
    }

    /**
     * Scope for filtering by tab category.
     */
    public function scopeForTab(Builder $query, ?string $tab): Builder
    {
        return match ($tab) {
            'models' => $query->whereNotIn('model_type', ['http_request', 'exception'])
                              ->whereNot('action', 'like', 'auth.%'),
            'errors' => $query->where(function ($q) {
                $q->where('status', 'error')
                  ->orWhere('status', 'failed')
                  ->orWhere('model_type', 'exception')
                  ->orWhere('action', 'system.exception');
            }),
            'security' => $query->where('action', 'like', 'auth.%'),
            default => $query,
        };
    }

    /**
     * Scope for free-text search.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $term = trim($search);

        return $query->where(function ($q) use ($term) {
            $q->where('action', 'like', "%{$term}%")
              ->orWhere('model_type', 'like', "%{$term}%")
              ->orWhere('ip_address', 'like', "%{$term}%")
              ->orWhere('new_values', 'like', "%{$term}%")
              ->orWhereHas('user', function ($uq) use ($term) {
                  $uq->where('first_name', 'like', "%{$term}%")
                     ->orWhere('last_name', 'like', "%{$term}%")
                     ->orWhere('email', 'like', "%{$term}%");
              });

            if (is_numeric($term)) {
                $q->orWhere('id', (int) $term)
                  ->orWhere('model_id', (int) $term);
            }
        });
    }

    /**
     * Scope for date range filtering.
     */
    public function scopeDateRange(Builder $query, ?string $start, ?string $end): Builder
    {
        if (!empty($start)) {
            $query->where('created_at', '>=', $start . ' 00:00:00');
        }
        if (!empty($end)) {
            $query->where('created_at', '<=', $end . ' 23:59:59');
        }
        return $query;
    }

    /**
     * Record a security, authentication, authorization, or operational audit event.
     */
    public static function record(
        string $action,
        ?string $modelType = 'system',
        int|string $modelId = 0,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
        string $status = 'success',
        string|array|null $details = null,
        ?int $actorId = null,
        ?string $outcome = null
    ): ?self {
        try {
            $effectiveUserId = $actorId ?? $userId ?? \Illuminate\Support\Facades\Auth::id();
            $effectiveStatus = $outcome ?? $status ?? 'success';

            $request = request();
            $correlationId = $request ? ($request->header('X-Correlation-ID') ?? $request->header('X-Request-ID') ?? (string) Str::uuid()) : (string) Str::uuid();

            $sanitizedNewValues = $newValues ? self::redactSensitiveData($newValues) : [];
            $sanitizedOldValues = $oldValues ? self::redactSensitiveData($oldValues) : null;

            $meta = [
                'status' => $effectiveStatus,
                'correlation_id' => $correlationId,
            ];
            if (is_array($details)) {
                $meta['details'] = self::redactSensitiveData($details);
            } elseif ($details !== null) {
                $meta['details'] = $details;
            }
            if (!empty($sanitizedNewValues)) {
                $meta['data'] = $sanitizedNewValues;
            }

            $log = self::create([
                'user_id' => $effectiveUserId,
                'action' => substr($action, 0, 255),
                'model_type' => substr($modelType ?? 'system', 0, 255),
                'model_id' => is_numeric($modelId) ? (int)$modelId : 0,
                'status' => substr($effectiveStatus, 0, 20),
                'old_values' => $sanitizedOldValues ? json_encode($sanitizedOldValues) : null,
                'new_values' => json_encode($meta),
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? substr($request->userAgent() ?? '', 0, 1000) : null,
            ]);

            // Real-time WebSocket broadcasting via Laravel Reverb (Fail-Safe isolated)
            try {
                event(new \App\Events\AuditLogCreated($log));
            } catch (\Throwable $broadcastError) {
                // Fail-safe: WebSocket or broadcast issue must never interrupt application execution
                \Illuminate\Support\Facades\Log::warning('[AuditLog Broadcast] WebSocket yayını gönderilemedi: ' . $broadcastError->getMessage());
            }

            return $log;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to write audit log: ' . $e->getMessage(), [
                'action' => $action,
                'exception' => $e,
            ]);
            return null;
        }
    }

    /**
     * Recursively redact sensitive keys from audit log payloads.
     */
    public static function redactSensitiveData(array $data): array
    {
        $sensitiveKeys = [
            'password', 'password_confirmation', 'current_password', 'currentpassword',
            'new_password', 'newpassword', 'old_password', 'oldpassword',
            'token', '_token', 'access_token', 'remember_token', 'api_token',
            'otp', 'code', 'pin', 'secret', 'authorization', 'bearer',
            'credit_card', 'card_number', 'cvv', 'cvv2', 'pan'
        ];

        $sanitized = [];
        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string)$key);
            if (in_array($lowerKey, $sensitiveKeys, true) || str_contains($lowerKey, 'password') || str_contains($lowerKey, 'token') || str_contains($lowerKey, 'secret')) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = self::redactSensitiveData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
