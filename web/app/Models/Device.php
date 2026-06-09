<?php

namespace App\Models;

use App\Enums\DeviceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $fillable = [
        'user_id', 'device_type', 'push_token',
        'app_version', 'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'device_type' => DeviceType::class,
            'last_active_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
