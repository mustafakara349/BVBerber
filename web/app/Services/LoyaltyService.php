<?php

namespace App\Services;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Müşteriye puan ekleme işlemi yapar
     */
    public function earnPoints(int $customerId, int $points, string $description = 'Puan kazanıldı'): LoyaltyTransaction
    {
        return DB::transaction(function () use ($customerId, $points, $description) {
            $account = LoyaltyAccount::firstOrCreate(
                ['customer_id' => $customerId],
                ['points_balance' => 0, 'total_earned' => 0, 'total_spent' => 0]
            );

            $account->increment('points_balance', $points);
            $account->increment('total_earned', $points);

            return LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'type' => 'earn',
                'points' => $points,
                'description' => $description,
            ]);
        });
    }

    /**
     * Müşterinin puan harcaması işlemi yapar
     */
    public function spendPoints(int $customerId, int $points, string $description = 'Puan harcandı'): LoyaltyTransaction
    {
        return DB::transaction(function () use ($customerId, $points, $description) {
            $account = LoyaltyAccount::where('customer_id', $customerId)->first();

            if (!$account || $account->points_balance < $points) {
                throw new \Exception('Yetersiz puan bakiyesi.');
            }

            $account->decrement('points_balance', $points);
            $account->increment('total_spent', $points);

            return LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'type' => 'spend',
                'points' => $points,
                'description' => $description,
            ]);
        });
    }
    
    /**
     * Manuel puan düzenleme işlemi
     */
    public function manualAdjustment(int $customerId, int $points, string $description = 'Manuel ayarlama'): LoyaltyTransaction
    {
        if ($points >= 0) {
            return $this->earnPoints($customerId, $points, $description);
        } else {
            return $this->spendPoints($customerId, abs($points), $description);
        }
    }
}
