<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id',
        'old_values', 'new_values',
        'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'json',
            'new_values' => 'json',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            $correlationId = $request ? ($request->header('X-Correlation-ID') ?? $request->header('X-Request-ID') ?? (string) \Illuminate\Support\Str::uuid()) : (string) \Illuminate\Support\Str::uuid();

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

            return self::create([
                'user_id' => $effectiveUserId,
                'action' => substr($action, 0, 255),
                'model_type' => substr($modelType ?? 'system', 0, 255),
                'model_id' => is_numeric($modelId) ? (int)$modelId : 0,
                'old_values' => $sanitizedOldValues ? json_encode($sanitizedOldValues) : null,
                'new_values' => json_encode($meta),
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? substr($request->userAgent() ?? '', 0, 1000) : null,
            ]);
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
