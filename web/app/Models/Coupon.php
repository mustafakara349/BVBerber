<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\DiscountType;

class Coupon extends Model
{
    protected $fillable = [
        'title', 'description', 'code', 
        'discount_type', 'discount_value',
        'usage_limit', 'used_count', 'expires_at', 'per_customer_limit'
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'discount_value' => 'decimal:2',
            'discount_type' => DiscountType::class,
        ];
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function usages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid(?User $user = null): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->used_count >= $this->usage_limit) {
            return false;
        }
        
        if ($user) {
            // Check if coupon is restricted to specific users
            if ($this->users()->exists()) {
                if (!$this->users()->where('users.id', $user->id)->exists()) {
                    return false; // User is not in the allowed list
                }
            }

            $usageCount = $this->usages()->where('customer_id', $user->id)->count();
            if ($usageCount >= $this->per_customer_limit) {
                return false;
            }
        }

        return true;
    }
}
