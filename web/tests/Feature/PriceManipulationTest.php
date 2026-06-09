<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Employee;

/**
 * PriceManipulationTest
 *
 * Sprint 1 backlog'unun test karşılığı.
 * Frontend'den gelen unit_price değerinin yok sayıldığını,
 * fiyatın veritabanından çekildiğini doğrular.
 */
class PriceManipulationTest extends TestCase
{
    use RefreshDatabase;

    private function setupBasicData(): array
    {
        $managerRole  = Role::create(['name' => 'Manager', 'slug' => 'manager']);
        $customerRole = Role::create(['name' => 'Customer', 'slug' => 'customer']);
        $barberRole   = Role::create(['name' => 'Barber',   'slug' => 'barber']);

        $manager = User::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(), 'role_id' => $managerRole->id,
            'first_name' => 'Admin', 'last_name' => 'User',
            'email' => 'admin@test.com', 'password' => bcrypt('password'), 'status' => 'active',
        ]);

        $customer = User::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(), 'role_id' => $customerRole->id,
            'first_name' => 'John', 'last_name' => 'Doe',
            'email' => 'customer@test.com', 'password' => bcrypt('password'), 'status' => 'active',
        ]);

        $branch = Branch::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Test Branch',
            'slug' => 'test-branch', 'city' => 'Istanbul', 'district' => 'Kadıköy',
            'address' => 'Test Address', 'is_active' => true,
        ]);

        $barberUser = User::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(), 'role_id' => $barberRole->id,
            'first_name' => 'Barber', 'last_name' => 'One',
            'email' => 'barber@test.com', 'password' => bcrypt('password'), 'status' => 'active',
        ]);

        $employee = Employee::create([
            'branch_id' => $branch->id, 'user_id' => $barberUser->id,
            'employee_code' => 'EMP001', 'hire_date' => now()->toDateString(),
            'is_active' => true, 'is_visible' => true,
        ]);

        // DB'deki gerçek fiyat: 150 TL
        $service = Service::create([
            'branch_id'        => $branch->id, 'name' => 'Saç Kesimi',
            'slug'             => 'sac-kesimi', 'duration_minutes' => 30,
            'price'            => 150.00, 'is_active' => true,
        ]);

        return compact('manager', 'customer', 'branch', 'employee', 'service');
    }

    /** @test */
    public function appointment_price_is_taken_from_database_not_request(): void
    {
        $data = $this->setupBasicData();

        $this->actingAs($data['manager']);
        $this->withSession(['active_branch_id' => $data['branch']->id]);

        // Manipülasyon denemesi: unit_price=0 gönderiliyor
        $response = $this->post('/appointments', [
            'branch_id'   => $data['branch']->id,
            'customer_id' => $data['customer']->id,
            'employee_id' => $data['employee']->id,
            'start_at'    => now()->addDay()->setHour(10)->setMinute(0)->toDateTimeString(),
            'services'    => [
                [
                    'service_id' => $data['service']->id,
                    'quantity'   => 1,
                    'unit_price' => 0,          // Manipülasyon denemesi
                    'duration_minutes' => 1,    // Manipülasyon denemesi
                ],
            ],
        ]);

        // Randevu oluşturulmuş olmalı
        $this->assertDatabaseHas('appointments', [
            'customer_id' => $data['customer']->id,
            'branch_id'   => $data['branch']->id,
        ]);

        // Fiyat 0 değil, DB'deki 150 TL olmalı
        $this->assertDatabaseHas('appointment_services', [
            'service_id' => $data['service']->id,
            'unit_price'  => 150.00,  // DB fiyatı
        ]);

        $this->assertDatabaseMissing('appointment_services', [
            'service_id' => $data['service']->id,
            'unit_price'  => 0,  // Manipüle edilmiş fiyat DB'ye yazılmamalı
        ]);
    }

    /** @test */
    public function appointment_duration_is_taken_from_database_not_request(): void
    {
        $data = $this->setupBasicData();

        $this->actingAs($data['manager']);
        $this->withSession(['active_branch_id' => $data['branch']->id]);

        $this->post('/appointments', [
            'branch_id'   => $data['branch']->id,
            'customer_id' => $data['customer']->id,
            'employee_id' => $data['employee']->id,
            'start_at'    => now()->addDay()->setHour(11)->setMinute(0)->toDateTimeString(),
            'services'    => [
                [
                    'service_id'       => $data['service']->id,
                    'quantity'         => 1,
                    'duration_minutes' => 1, // Manipülasyon denemesi, DB'de 30 dakika
                ],
            ],
        ]);

        // Süre DB'den alınmış olmalı: 30 dakika
        $this->assertDatabaseHas('appointment_services', [
            'service_id'       => $data['service']->id,
            'duration_minutes' => 30, // DB değeri
        ]);
    }
}
