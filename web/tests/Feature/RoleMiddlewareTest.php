<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

/**
 * RoleMiddlewareTest
 *
 * Sprint 1 backlog'unun test karşılığı.
 * Rota düzeyindeki yetkilendirmenin çalıştığını doğrular.
 *
 * DB: SQLite in-memory (phpunit.xml'de tanımlı)
 */
class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleSlug): User
    {
        $role = Role::create(['name' => ucfirst($roleSlug), 'slug' => $roleSlug]);

        return User::create([
            'uuid'       => (string) \Illuminate\Support\Str::uuid(),
            'role_id'    => $role->id,
            'first_name' => 'Test',
            'last_name'  => 'User',
            'email'      => $roleSlug . '@test.com',
            'password'   => bcrypt('password'),
            'status'     => 'active',
        ]);
    }

    /** @test */
    public function barber_cannot_access_finance_transactions(): void
    {
        $barber = $this->createUserWithRole('barber');

        $response = $this->actingAs($barber)->get('/finance/transactions');

        $response->assertStatus(403);
    }

    /** @test */
    public function manager_can_access_finance_transactions(): void
    {
        $manager = $this->createUserWithRole('manager');

        $response = $this->actingAs($manager)->get('/finance/transactions');

        // 200 veya redirect (no data) - 403 olmamalı
        $response->assertStatus(200);
    }

    /** @test */
    public function barber_can_access_dashboard(): void
    {
        $barber = $this->createUserWithRole('barber');

        $response = $this->actingAs($barber)->get('/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function customer_role_cannot_access_dashboard(): void
    {
        $customer = $this->createUserWithRole('customer');

        $response = $this->actingAs($customer)->get('/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function barber_cannot_access_settings(): void
    {
        $barber = $this->createUserWithRole('barber');

        $response = $this->actingAs($barber)->get('/settings');

        $response->assertStatus(403);
    }

    /** @test */
    public function receptionist_cannot_access_reports(): void
    {
        $receptionist = $this->createUserWithRole('receptionist');

        $response = $this->actingAs($receptionist)->get('/reports');

        $response->assertStatus(403);
    }
}
