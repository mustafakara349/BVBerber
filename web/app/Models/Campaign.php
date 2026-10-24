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
        'branch_id', 'title', 'description', 'terms',
        'type', 'trigger_type', 'reward_type',
        'reward_product_id', 'reward_cafe_product_id',
        'min_order_amount', 'max_discount_amount',
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

    public function rewardProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'reward_product_id');
    }

    public function rewardCafeProduct(): BelongsTo
    {
        return $this->belongsTo(CafeProduct::class, 'reward_cafe_product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today());
    }

    /**
     * Kampanyanın insan dostu dinamik durum metnini döner.
     */
    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Pasif';
        }

        $today = today();
        $startDate = $this->start_date ? \Carbon\Carbon::parse($this->start_date)->startOfDay() : null;
        $endDate = $this->end_date ? \Carbon\Carbon::parse($this->end_date)->endOfDay() : null;

        if ($startDate && $startDate->isAfter($today)) {
            return "Beklemede " . $startDate->format('d.m.Y') . "'nde yayınlanacak";
        }

        if ($endDate && $endDate->isPast()) {
            return "Süresi Doldu (" . $endDate->format('d.m.Y') . ")";
        }

        $endStr = $endDate ? $endDate->format('d.m.Y') : 'süresiz';
        return "Yayından " . $endStr . "'nde kaldırılacak";
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_active) {
            return 'bg-secondary-subtle text-secondary border';
        }

        $today = today();
        $startDate = $this->start_date ? \Carbon\Carbon::parse($this->start_date)->startOfDay() : null;
        $endDate = $this->end_date ? \Carbon\Carbon::parse($this->end_date)->endOfDay() : null;

        if ($startDate && $startDate->isAfter($today)) {
            return 'bg-warning-subtle text-warning-emphasis border border-warning';
        }

        if ($endDate && $endDate->isPast()) {
            return 'bg-danger-subtle text-danger border border-danger';
        }

        return 'bg-success-subtle text-success border border-success';
    }

    public function getStatusIconAttribute(): string
    {
        if (!$this->is_active) {
            return 'ti ti-player-pause';
        }

        $today = today();
        $startDate = $this->start_date ? \Carbon\Carbon::parse($this->start_date)->startOfDay() : null;
        $endDate = $this->end_date ? \Carbon\Carbon::parse($this->end_date)->endOfDay() : null;

        if ($startDate && $startDate->isAfter($today)) {
            return 'ti ti-clock';
        }

        if ($endDate && $endDate->isPast()) {
            return 'ti ti-alert-circle';
        }

        return 'ti ti-circle-check';
    }
}
