<?php

namespace Tests\Unit;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use Tests\TestCase;

class LoyaltyPointsTest extends TestCase
{
    public function test_points_to_tl_conversion()
    {
        $this->assertEquals(1.00, LoyaltyAccount::pointsToTl(1000));
        $this->assertEquals(0.50, LoyaltyAccount::pointsToTl(500));
        $this->assertEquals(0.10, LoyaltyAccount::pointsToTl(100));
        $this->assertEquals(0.01, LoyaltyAccount::pointsToTl(10));
        $this->assertEquals(0.00, LoyaltyAccount::pointsToTl(0));
        $this->assertEquals(2.07, LoyaltyAccount::pointsToTl(2070));
        $this->assertEquals(50.00, LoyaltyAccount::pointsToTl(50000));
    }

    public function test_tl_to_points_conversion()
    {
        $this->assertEquals(1000, LoyaltyAccount::tlToPoints(1.00));
        $this->assertEquals(500, LoyaltyAccount::tlToPoints(0.50));
        $this->assertEquals(2500, LoyaltyAccount::tlToPoints(2.50));
        $this->assertEquals(0, LoyaltyAccount::tlToPoints(0.00));
    }

    public function test_account_tl_balance_and_formatted_attribute()
    {
        $account = new LoyaltyAccount([
            'points_balance' => 2070,
            'total_earned' => 3500,
            'total_spent' => 1430,
        ]);

        $this->assertEquals(2.07, $account->tl_balance);
        $this->assertEquals('₺2,07', $account->formatted_tl_balance);
        $this->assertEquals('₺3,50', $account->formatted_total_earned_tl);
        $this->assertEquals('₺1,43', $account->formatted_total_spent_tl);
    }

    public function test_transaction_tl_value_and_formatted_attribute()
    {
        $txnEarn = new LoyaltyTransaction([
            'type' => 'earn',
            'points' => 450,
            'description' => 'Randevu Tamamlandı',
        ]);

        $this->assertEquals(0.45, $txnEarn->tl_value);
        $this->assertEquals('₺0,45', $txnEarn->formatted_tl_value);

        $txnSpend = new LoyaltyTransaction([
            'type' => 'spend',
            'points' => -1000,
            'description' => 'İndirim kullanıldı',
        ]);

        $this->assertEquals(-1.00, $txnSpend->tl_value);
        $this->assertEquals('₺1,00', $txnSpend->formatted_tl_value);
    }
}
