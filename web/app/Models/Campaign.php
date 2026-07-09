<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use Auditable;

    protected $fillable = [
        'branch_id', 'title', 'description',
        'type', 'min_order_amount', 'max_discount_amount',
        'target_audience', 'image_path', 'priority', 'per_customer_limit',
        'discount_type', 'discount_value',
        'start_date', 'end_date', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_type' => DiscountType::class,
            'discount_value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'priority' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function coupons(): HasMany
    {
        // Removed as coupons are no longer directly tied to campaigns
        // Keeping an empty method to not break anything if called blindly, or return null relation
        return $this->hasMany(Coupon::class); // It will fail as campaign_id doesn't exist, we should just remove this but maybe some code relies on it?
    }

    public function services(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'campaign_services');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_products');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ServiceCategory::class, 'campaign_categories', 'campaign_id', 'category_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CampaignUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today());
    }
}
