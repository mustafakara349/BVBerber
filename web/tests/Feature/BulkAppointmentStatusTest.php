<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class BulkAppointmentStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'bvapp_db',
        ]);
    }

    private function createTestUser(string $roleSlug, int $branchId = 1): User
    {
        $role = Role::firstOrCreate(['slug' => $roleSlug], ['name' => ucfirst($roleSlug)]);
        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'role_id' => $role->id,
            'first_name' => 'Test',
            'last_name' => ucfirst($roleSlug),
            'email' => 'bulk_' . $roleSlug . '_' . Str::random(6) . '@bvberber.com',
            'phone' => '555' . rand(1000000, 9999999),
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);

        if (in_array($roleSlug, ['barber', 'manager', 'receptionist'])) {
            Employee::create([
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'employee_code' => 'EMP-' . Str::upper(Str::random(6)),
                'hire_date' => now()->toDateString(),
                'salary_type' => 'commission',
                'is_active' => true,
            ]);
        }

        return $user;
    }

    private function createPendingAppointment(int $branchId = 1, ?int $employeeId = null): Appointment
    {
        if (!$employeeId) {
            $barberUser = $this->createTestUser('barber', $branchId);
            $employeeId = $barberUser->employee->id;
        }

        $customer = $this->createTestUser('customer', $branchId);

        return Appointment::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'employee_id' => $employeeId,
            'appointment_code' => 'APT-' . Str::upper(Str::random(6)),
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addHour(),
            'total_duration' => 60,
            'subtotal' => 500,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_price' => 500,
            'status' => AppointmentStatus::Pending,
        ]);
    }

    public function test_admin_can_bulk_approve_pending_appointments(): void
    {
        $admin = $this->createTestUser('super_admin');
        $branch = Branch::first() ?? Branch::create(['name' => 'Main Branch', 'slug' => 'main']);

        $apt1 = $this->createPendingAppointment($branch->id);
        $apt2 = $this->createPendingAppointment($branch->id);

        $response = $this->actingAs($admin)
            ->withSession(['active_branch_id' => $branch->id])
            ->post('/appointments/bulk-status', [
                'status' => 'confirmed',
                'appointment_ids' => "{$apt1->id},{$apt2->id}",
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(AppointmentStatus::Confirmed, $apt1->fresh()->status);
        $this->assertEquals(AppointmentStatus::Confirmed, $apt2->fresh()->status);
    }

    public function test_admin_can_bulk_reject_pending_appointments_with_reason(): void
    {
        $admin = $this->createTestUser('super_admin');
        $branch = Branch::first() ?? Branch::create(['name' => 'Main Branch', 'slug' => 'main']);

        $apt1 = $this->createPendingAppointment($branch->id);
        $apt2 = $this->createPendingAppointment($branch->id);

        $response = $this->actingAs($admin)
            ->withSession(['active_branch_id' => $branch->id])
            ->post('/appointments/bulk-status', [
                'status' => 'rejected',
                'cancellation_reason' => 'Salon tadilatı nedeniyle randevular iptal edildi.',
                'appointment_ids' => "{$apt1->id},{$apt2->id}",
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(AppointmentStatus::Rejected, $apt1->fresh()->status);
        $this->assertEquals('Salon tadilatı nedeniyle randevular iptal edildi.', $apt1->fresh()->cancellation_reason);
        $this->assertEquals(AppointmentStatus::Rejected, $apt2->fresh()->status);
    }

    public function test_bulk_reject_requires_cancellation_reason(): void
    {
        $admin = $this->createTestUser('super_admin');
        $branch = Branch::first() ?? Branch::create(['name' => 'Main Branch', 'slug' => 'main']);

        $apt = $this->createPendingAppointment($branch->id);

        $response = $this->actingAs($admin)
            ->withSession(['active_branch_id' => $branch->id])
            ->post('/appointments/bulk-status', [
                'status' => 'rejected',
                'cancellation_reason' => '',
                'appointment_ids' => (string) $apt->id,
            ]);

        $response->assertSessionHasErrors(['cancellation_reason']);
        $this->assertEquals(AppointmentStatus::Pending, $apt->fresh()->status);
    }
}
