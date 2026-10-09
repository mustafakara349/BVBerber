<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class CafeProduct extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'branch_id',
        'name',
        'cafe_category_id',
        'description',
        'ingredients',
        'price',
        'image_path',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'sort_order'  => 'integer',
    ];

    // -------------------------------------------------------------------------
    // İlişkiler
    // -------------------------------------------------------------------------

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cafeCategory(): BelongsTo
    {
        return $this->belongsTo(CafeCategory::class, 'cafe_category_id');
    }

    // -------------------------------------------------------------------------
    // Accessor'lar
    // -------------------------------------------------------------------------

    /**
     * Görselin tam URL'sini döner. Görsel yoksa null döner.
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return Storage::disk('public')->url($this->image_path);
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // Scope'lar
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('cafe_category_id', $categoryId);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
