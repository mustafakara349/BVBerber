<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'attempts',
        'reset_token',
        'expires_at',
    ];

    protected $hidden = [
        'otp',
        'reset_token',
    ];

    protected $casts = [
        'attempts'   => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the OTP is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at ? now()->isAfter($this->expires_at) : true;
    }

    /**
     * Check if max verification attempts have been exceeded.
     */
    public function hasExceededAttempts(int $max = 5): bool
    {
        return $this->attempts >= $max;
    }
}
