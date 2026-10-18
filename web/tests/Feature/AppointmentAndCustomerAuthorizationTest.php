<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class AppointmentAndCustomerAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private Branch $branch;
    private User $manager;
    private User $receptionist;
    private User $barber1User;
    private Employee $barber1;
    private User $barber2User;
    private Employee $barber2;
    private User $customer1;
    private User $customer2;
    private Appointment $aptBarber1;
    private Appointment $aptBarber2;
    private Role $custRole;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'bvapp_db',
        ]);

        $mgrRole = Role::firstOrCreate(['slug' => 'manager'], ['name' => 'manager', 'label' => 'Yönetici']);
        $recRole = Role::firstOrCreate(['slug' => 'receptionist'], ['name' => 'receptionist', 'label' => 'Resepsiyonist']);
        $barberRole = Role::firstOrCreate(['slug' => 'barber'], ['name' => 'barber', 'label' => 'Berber']);
        $this->custRole = Role::firstOrCreate(['slug' => 'customer'], ['name' => 'customer', 'label' => 'Müşteri']);
        $custRole = $this->custRole;

        $suffix = Str::lower(Str::random(6));

        $this->branch = Branch::firstOrCreate(
            ['slug' => 'auth-test-sube'],
            [
                'name' => 'Auth Test Şubesi',
                'email' => "authtest_{$suffix}@test.com",
                'phone' => '055' . rand(1000000, 9999999),
                'city' => 'Istanbul',
                'district' => 'Kadikoy',
                'address' => 'Test Adres',
                'is_active' => true,
            ]
        );

        $this->manager = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Manager',
            'last_name' => 'User',
            'email' => "mgr_{$suffix}@test.com",
            'phone' => '055' . rand(1000000, 9999999),
            'password' => bcrypt('password'),
            'role_id' => $mgrRole->id,
            'status' => 'active',
        ]);

        $this->receptionist = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Receptionist',
            'last_name' => 'User',
            'email' => "rec_{$suffix}@test.com",
            'phone' => '055' . rand(1000000, 9999999),
            'password' => bcrypt('password'),
            'role_id' => $recRole->id,
            'status' => 'active',
        ]);

        $this->barber1User = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Barber',
            'last_name' => 'One',
            'email' => "barber1_{$suffix}@test.com",
            'phone' => '055' . rand(1000000, 9999999),
            'password' => bcrypt('password'),
            'role_id' => $barberRole->id,
            'status' => 'active',
        ]);

        $this->barber1 = Employee::create([
            'user_id' => $this->barber1User->id,
            'branch_id' => $this->branch->id,
            'employee_code' => "B1-{$suffix}",
            'hire_date' => now()->toDateString(),
            'salary_type' => 'commission',
            'commission_rate' => 50,
            'is_visible' => true,
            'is_active' => true,
        ]);

        $this->barber2User = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Barber',
            'last_name' => 'Two',
            'email' => "barber2_{$suffix}@test.com",
            'phone' => '055' . rand(1000000, 9999999),
            'password' => bcrypt('password'),
            'role_id' => $barberRole->id,
            'status' => 'active',
        ]);

        $this->barber2 = Employee::create([
            'user_id' => $this->barber2User->id,
            'branch_id' => $this->branch->id,
            'employee_code' => "B2-{$suffix}",
            'hire_date' => now()->toDateString(),
            'salary_type' => 'commission',
            'commission_rate' => 50,
            'is_visible' => true,
            'is_active' => true,
        ]);

        $this->customer1 = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Customer',
            'last_name' => 'One',
            'email' => "cust1_{$suffix}@test.com",
            'phone' => '055' . rand(1000000, 9999999),
            'password' => bcrypt('password'),
            'role_id' => $custRole->id,
            'status' => 'active',
        ]);

        $this->customer2 = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Customer',
            'last_name' => 'Two',
            'email' => "cust2_{$suffix}@test.com",
            'phone' => '055' . rand(1000000, 9999999),
            'password' => bcrypt('password'),
            'role_id' => $custRole->id,
            'status' => 'active',
        ]);

        $this->aptBarber1 = Appointment::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer1->id,
            'employee_id' => $this->barber1->id,
            'appointment_code' => "APT1-{$suffix}",
            'start_at' => now()->addDay()->setHour(10)->setMinute(0),
            'end_at' => now()->addDay()->setHour(10)->setMinute(30),
            'total_duration' => 30,
            'total_price' => 250.00,
            'status' => AppointmentStatus::Confirmed,
            'created_by' => $this->manager->id,
        ]);

        $this->aptBarber2 = Appointment::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer2->id,
            'employee_id' => $this->barber2->id,
            'appointment_code' => "APT2-{$suffix}",
            'start_at' => now()->addDay()->setHour(11)->setMinute(0),
            'end_at' => now()->addDay()->setHour(11)->setMinute(30),
            'total_duration' => 30,
            'total_price' => 300.00,
            'status' => AppointmentStatus::Confirmed,
            'created_by' => $this->manager->id,
        ]);
    }

    /** @test */
    public function test_barber_can_view_own_appointment(): void
    {
        $response = $this->actingAs($this->barber1User)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->get(route('appointments.show', $this->aptBarber1));

        $response->assertStatus(200);
        $response->assertSee($this->customer1->first_name);
    }

    /** @test */
    public function test_barber_cannot_view_another_barbers_appointment(): void
    {
        // Barber 1 tries to access Barber 2's appointment
        $response = $this->actingAs($this->barber1User)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->get(route('appointments.show', $this->aptBarber2));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_barber_cannot_edit_another_barbers_appointment(): void
    {
        $response = $this->actingAs($this->barber1User)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->get(route('appointments.edit', $this->aptBarber2));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_barber_cannot_update_status_of_another_barbers_appointment(): void
    {
        $response = $this->actingAs($this->barber1User)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->patch(route('appointments.update-status', $this->aptBarber2), [
                'status' => AppointmentStatus::Cancelled->value,
            ]);

        $response->assertStatus(403);

        $this->aptBarber2->refresh();
        $this->assertEquals(AppointmentStatus::Confirmed, $this->aptBarber2->status);
    }

    /** @test */
    public function test_barber_can_update_status_of_own_appointment(): void
    {
        $response = $this->actingAs($this->barber1User)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->patch(route('appointments.update-status', $this->aptBarber1), [
                'status' => AppointmentStatus::Completed->value,
            ]);

        $response->assertRedirect();

        $this->aptBarber1->refresh();
        $this->assertEquals(AppointmentStatus::Completed, $this->aptBarber1->status);
    }

    /** @test */
    public function test_barber_cannot_delete_payment_on_own_appointment(): void
    {
        $payment = Payment::create([
            'appointment_id' => $this->aptBarber1->id,
            'amount' => 250.00,
            'payment_method' => 'cash',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->barber1User)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->delete(route('appointments.payments.destroy', [
                'appointment' => $this->aptBarber1,
                'payment' => $payment,
            ]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }

    /** @test */
    public function test_cannot_delete_payment_when_payment_does_not_belong_to_appointment(): void
    {
        // Payment belongs to aptBarber2, but request targets aptBarber1
        $payment = Payment::create([
            'appointment_id' => $this->aptBarber2->id,
            'amount' => 300.00,
            'payment_method' => 'cash',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->delete(route('appointments.payments.destroy', [
                'appointment' => $this->aptBarber1,
                'payment' => $payment,
            ]));

        // IDOR / cross-appointment mismatch must yield 404 Not Found
        $response->assertStatus(404);
        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }

    /** @test */
    public function test_manager_can_delete_payment_on_matching_appointment(): void
    {
        $payment = Payment::create([
            'appointment_id' => $this->aptBarber1->id,
            'amount' => 250.00,
            'payment_method' => 'cash',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->delete(route('appointments.payments.destroy', [
                'appointment' => $this->aptBarber1,
                'payment' => $payment,
            ]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    /** @test */
    public function test_barber_calendar_events_returns_only_own_appointments(): void
    {
        $start = now()->addDay()->startOfDay()->toIso8601String();
        $end = now()->addDay()->endOfDay()->toIso8601String();

        $response = $this->actingAs($this->barber1User)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->getJson(route('appointments.events', ['start' => $start, 'end' => $end]));

        $response->assertStatus(200);

        $events = $response->json();
        $eventIds = array_column($events, 'id');

        $this->assertContains($this->aptBarber1->id, $eventIds);
        $this->assertNotContains($this->aptBarber2->id, $eventIds);
    }

    /** @test */
    public function test_barber_index_list_filters_only_own_appointments(): void
    {
        $response = $this->actingAs($this->barber1User)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->get(route('appointments.index'));

        $response->assertStatus(200);
        $response->assertSee($this->aptBarber1->appointment_code);
        $response->assertDontSee($this->aptBarber2->appointment_code);
    }

    /** @test */
    public function test_receptionist_can_view_all_branch_appointments(): void
    {
        $response1 = $this->actingAs($this->receptionist)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->get(route('appointments.show', $this->aptBarber1));

        $response1->assertStatus(200);

        $response2 = $this->actingAs($this->receptionist)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->get(route('appointments.show', $this->aptBarber2));

        $response2->assertStatus(200);
    }

    /** @test */
    public function test_barber_cannot_access_customers_module(): void
    {
        $response = $this->actingAs($this->barber1User)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->get(route('customers.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_receptionist_cannot_delete_customer(): void
    {
        $response = $this->actingAs($this->receptionist)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->delete(route('customers.destroy', $this->customer1));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $this->customer1->id, 'deleted_at' => null]);
    }

    /** @test */
    public function test_manager_can_delete_customer_and_it_is_soft_deleted(): void
    {
        $targetCustomer = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Delete',
            'last_name' => 'Customer',
            'email' => 'del_' . Str::random(8) . '@test.com',
            'phone' => '055' . rand(1000000, 9999999),
            'password' => bcrypt('password'),
            'role_id' => $this->custRole->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->delete(route('customers.destroy', $targetCustomer));

        $response->assertRedirect(route('customers.index'));
        $this->assertSoftDeleted('users', ['id' => $targetCustomer->id]);
    }
}
