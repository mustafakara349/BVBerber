<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use App\Models\Campaign;
use App\Models\Coupon;

/**
 * BranchIsolationTest
 *
 * Sprint 2 backlog'unun test karşılığı.
 * Şube izolasyonunun çalıştığını doğrular.
 * Bir kullanıcı başka bir şubeye ait kaynağı silememeli.
 */
class BranchIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function setupTwoBranches(): array
    {
        $ownerRole = Role::create(['name' => 'Owner', 'slug' => 'owner']);

        $branch1 = Branch::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Şube 1',
            'slug' => 'sube-1', 'city' => 'Istanbul', 'district' => 'K',
            'address' => 'Test', 'is_active' => true,
        ]);

        $branch2 = Branch::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Şube 2',
            'slug' => 'sube-2', 'city' => 'Ankara', 'district' => 'C',
            'address' => 'Test', 'is_active' => true,
        ]);

        $user = User::create([
            'uuid'       => (string) \Illuminate\Support\Str::uuid(),
            'role_id'    => $ownerRole->id,
            'first_name' => 'Owner',
            'last_name'  => 'User',
            'email'      => 'owner@test.com',
            'password'   => bcrypt('password'),
            'status'     => 'active',
        ]);

        return compact('user', 'branch1', 'branch2');
    }

    /** @test */
    public function user_cannot_delete_campaign_from_another_branch(): void
    {
        $data = $this->setupTwoBranches();

        // Şube 2'ye ait kampanya oluştur
        $campaign = Campaign::create([
            'branch_id'      => $data['branch2']->id,
            'title'          => 'Şube 2 Kampanyası',
            'discount_type'  => 'fixed',
            'discount_value' => 10,
            'start_date'     => now()->toDateString(),
            'end_date'       => now()->addMonth()->toDateString(),
            'is_active'      => true,
        ]);

        // Kullanıcı Şube 1 ile oturum açmış
        $this->actingAs($data['user'])
             ->withSession(['active_branch_id' => $data['branch1']->id]);

        // Şube 2'nin kampanyasını silmeye çalış
        $response = $this->delete("/campaigns/{$campaign->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('campaigns', ['id' => $campaign->id]);
    }

    /** @test */
    public function user_can_delete_own_branch_campaign(): void
    {
        $data = $this->setupTwoBranches();

        $campaign = Campaign::create([
            'branch_id'      => $data['branch1']->id,
            'title'          => 'Şube 1 Kampanyası',
            'discount_type'  => 'fixed',
            'discount_value' => 10,
            'start_date'     => now()->toDateString(),
            'end_date'       => now()->addMonth()->toDateString(),
            'is_active'      => true,
        ]);

        $this->actingAs($data['user'])
             ->withSession(['active_branch_id' => $data['branch1']->id]);

        $response = $this->delete("/campaigns/{$campaign->id}");

        $response->assertRedirect(route('campaigns.index'));
        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
    }

    /** @test */
    public function user_cannot_delete_coupon_from_another_branch(): void
    {
        $data = $this->setupTwoBranches();

        $campaign = Campaign::create([
            'branch_id'      => $data['branch2']->id,
            'title'          => 'Diğer Şube Kampanya',
            'discount_type'  => 'fixed',
            'discount_value' => 5,
            'start_date'     => now()->toDateString(),
            'end_date'       => now()->addMonth()->toDateString(),
            'is_active'      => true,
        ]);

        $coupon = Coupon::create([
            'campaign_id' => $campaign->id,
            'code'        => 'TEST123',
            'usage_limit' => 10,
        ]);

        $this->actingAs($data['user'])
             ->withSession(['active_branch_id' => $data['branch1']->id]);

        $response = $this->delete("/campaigns/coupons/{$coupon->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('coupons', ['id' => $coupon->id]);
    }
}
