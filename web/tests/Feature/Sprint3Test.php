<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\LoyaltyAccount;
use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint3Test extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $customer;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup initial data
        Role::firstOrCreate(['name' => 'manager'], ['label' => 'Yönetici', 'slug' => 'manager']);
        Role::firstOrCreate(['name' => 'customer'], ['label' => 'Müşteri', 'slug' => 'customer']);
        Role::firstOrCreate(['name' => 'barber'], ['label' => 'Berber', 'slug' => 'barber']);
        
        $this->branch = Branch::create([
            'name' => 'Merkez Şube',
            'slug' => 'merkez-sube',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'city' => 'Istanbul',
            'district' => 'Sisli',
            'address' => 'Test Adres',
        ]);
        
        $this->manager = User::create([
            'first_name' => 'Test',
            'last_name' => 'Manager',
            'email' => 'manager@test.com',
            'phone' => '1111111111',
            'password' => bcrypt('password'),
            'role_id' => Role::where('name', 'manager')->first()->id,
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        
        $this->customer = User::create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'customer@test.com',
            'phone' => '2222222222',
            'password' => bcrypt('password'),
            'role_id' => Role::where('name', 'customer')->first()->id,
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    public function test_loyalty_points_earned_on_appointment_completion()
    {
        $employeeUser = User::create([
            'first_name' => 'Test',
            'last_name' => 'Barber',
            'email' => 'barber@test.com',
            'phone' => '3333333333',
            'password' => bcrypt('password'),
            'role_id' => Role::where('name', 'barber')->first()->id,
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'branch_id' => $this->branch->id,
            'employee_code' => 'EMP-001',
            'hire_date' => now(),
            'salary_type' => 'commission',
            'commission_rate' => 50,
        ]);

        $service = Service::create([
            'name' => 'Saç Kesimi',
            'slug' => 'sac-kesimi',
            'branch_id' => $this->branch->id,
            'price' => 200,
            'duration_minutes' => 30
        ]);

        $appointment = Appointment::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'employee_id' => $employee->id,
            'status' => AppointmentStatus::Pending,
            'total_price' => 200,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addMinutes(30),
            'appointment_code' => 'TEST-001',
        ]);

        \App\Models\AppointmentService::create([
            'appointment_id' => $appointment->id,
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'quantity' => 1,
            'unit_price' => 200,
            'total_price' => 200,
            'duration_minutes' => 30,
        ]);

        $this->actingAs($this->manager)
            ->patch(route('appointments.update-status', $appointment), [
                'status' => AppointmentStatus::Completed->value,
            ]);

        $account = LoyaltyAccount::where('customer_id', $this->customer->id)->first();
        
        $this->assertNotNull($account);
        $this->assertEquals(200, $account->points_balance);
        $this->assertEquals(200, $account->total_earned);
    }

    public function test_product_stock_deduction_and_transaction_creation_on_sale()
    {
        $product = Product::create([
            'branch_id' => $this->branch->id,
            'name' => 'Şampuan',
            'purchase_price' => 50,
            'sell_price' => 100,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->post(route('products.sales.store'), [
                'product_id' => $product->id,
                'quantity' => 2,
                'customer_id' => $this->customer->id,
                'payment_method' => 'cash',
            ]);

        $response->assertRedirect(route('products.sales.index'));
        
        $product->refresh();
        $this->assertEquals(8, $product->stock_quantity);

        $this->assertDatabaseHas('product_sales', [
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => 200,
        ]);

        $this->assertDatabaseHas('transactions', [
            'amount' => 200,
            'transaction_type' => 'income',
        ]);
    }
}
