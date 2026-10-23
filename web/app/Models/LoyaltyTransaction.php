<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    use Auditable;

    public $timestamps = false;

    protected $fillable = ['loyalty_account_id', 'type', 'points', 'description'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function loyaltyAccount(): BelongsTo
    {
        return $this->belongsTo(LoyaltyAccount::class);
    }

    /**
     * İşlemdeki puanın TL karşılığı (1.000 Puan = 1 ₺)
     */
    public function getTlValueAttribute(): float
    {
        return LoyaltyAccount::pointsToTl($this->points);
    }

    /**
     * İşlemdeki puanın formatlanmış TL karşılığı
     */
    public function getFormattedTlValueAttribute(): string
    {
        return '₺' . number_format(abs($this->tl_value), 2, ',', '.');
    }
}
