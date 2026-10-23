<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyAccount extends Model
{
    use Auditable;

    public const DEFAULT_POINTS_PER_TL = 1000;

    protected $fillable = ['customer_id', 'points_balance', 'total_earned', 'total_spent'];

    public static function getPointsPerTl(): int
    {
        return (int) config('bv.loyalty.points_per_tl', self::DEFAULT_POINTS_PER_TL);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * Puanın TL karşılığı (1.000 Puan = 1 ₺)
     */
    public function getTlBalanceAttribute(): float
    {
        $rate = self::getPointsPerTl();
        return $rate > 0 ? round($this->points_balance / $rate, 2) : 0.0;
    }

    /**
     * Formatlanmış TL bakiye karşılığı (örn: ₺2,50)
     */
    public function getFormattedTlBalanceAttribute(): string
    {
        return '₺' . number_format($this->tl_balance, 2, ',', '.');
    }

    /**
     * Toplam kazanılan puanın formatlanmış TL karşılığı
     */
    public function getFormattedTotalEarnedTlAttribute(): string
    {
        $rate = self::getPointsPerTl();
        $tl = $rate > 0 ? round($this->total_earned / $rate, 2) : 0.0;
        return '₺' . number_format($tl, 2, ',', '.');
    }

    /**
     * Toplam harcanan puanın formatlanmış TL karşılığı
     */
    public function getFormattedTotalSpentTlAttribute(): string
    {
        $rate = self::getPointsPerTl();
        $tl = $rate > 0 ? round($this->total_spent / $rate, 2) : 0.0;
        return '₺' . number_format($tl, 2, ',', '.');
    }

    /**
     * Verilen puan miktarını TL'ye çevirir
     */
    public static function pointsToTl(int $points): float
    {
        $rate = self::getPointsPerTl();
        return $rate > 0 ? round($points / $rate, 2) : 0.0;
    }

    /**
     * Verilen TL tutarını puana çevirir
     */
    public static function tlToPoints(float $tl): int
    {
        return (int) round($tl * self::getPointsPerTl());
    }
}
