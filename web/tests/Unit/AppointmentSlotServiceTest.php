<?php

namespace Tests\Unit;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeTimeBlock;
use App\Models\User;
use App\Services\Contracts\AppointmentSlotServiceInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AppointmentSlotServiceTest extends TestCase
{
    use DatabaseTransactions;

    private AppointmentSlotServiceInterface $slotService;
    private Employee $employee;
    private Branch $branch;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->slotService = app(AppointmentSlotServiceInterface::class);

        $this->branch = Branch::first();
        $this->branch->update([
            'opening_time' => '09:00:00',
            'closing_time' => '18:00:00',
        ]);

        $this->customer = User::first();
        $this->employee = Employee::first();
    }

    public function test_it_generates_slots_for_empty_day(): void
    {
        $testDate = Carbon::now()->addDays(60)->toDateString();
        $slots = $this->slotService->getAvailableSlots(
            employeeId: $this->employee->id,
            date: $testDate,
            duration: 30,
            branchId: $this->branch->id
        );

        $this->assertNotEmpty($slots);
        $this->assertEquals('09:00', $slots[0]['time']);
        $this->assertTrue($slots[0]['is_available']);
    }

    public function test_60_minute_appointment_blocks_consecutive_slots(): void
    {
        $testDate = Carbon::now()->addDays(60)->toDateString();

        // 14:00 - 15:00 arası 60 dakikalık randevu oluştur
        Appointment::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'appointment_code' => 'TEST-001',
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'employee_id' => $this->employee->id,
            'start_at' => Carbon::parse($testDate . ' 14:00:00'),
            'end_at' => Carbon::parse($testDate . ' 15:00:00'),
            'total_duration' => 60,
            'status' => AppointmentStatus::Confirmed,
            'total_price' => 100,
        ]);

        $slots = $this->slotService->getAvailableSlots(
            employeeId: $this->employee->id,
            date: $testDate,
            duration: 30,
            branchId: $this->branch->id
        );

        $slot1400 = collect($slots)->firstWhere('time', '14:00');
        $slot1430 = collect($slots)->firstWhere('time', '14:30');
        $slot1500 = collect($slots)->firstWhere('time', '15:00');

        $this->assertNotNull($slot1400);
        $this->assertNotNull($slot1430);
        $this->assertNotNull($slot1500);

        // Hem 14:00 hem de 14:30 kapalı olmalı!
        $this->assertFalse($slot1400['is_available']);
        $this->assertEquals('booked', $slot1400['reason']);

        $this->assertFalse($slot1430['is_available']);
        $this->assertEquals('booked', $slot1430['reason']);

        // 15:00 ise açık olmalı!
        $this->assertTrue($slot1500['is_available']);
    }

    public function test_multi_slot_duration_closes_prior_slot_if_consecutive_not_available(): void
    {
        $testDate = Carbon::now()->addDays(60)->toDateString();

        // 14:00'te 30 dakikalık bir randevu olsun (14:00 - 14:30)
        Appointment::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'appointment_code' => 'TEST-002',
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'employee_id' => $this->employee->id,
            'start_at' => Carbon::parse($testDate . ' 14:00:00'),
            'end_at' => Carbon::parse($testDate . ' 14:30:00'),
            'total_duration' => 30,
            'status' => AppointmentStatus::Confirmed,
            'total_price' => 50,
        ]);

        // Müşteri 60 dakikalık işlem arıyor
        $slots = $this->slotService->getAvailableSlots(
            employeeId: $this->employee->id,
            date: $testDate,
            duration: 60,
            branchId: $this->branch->id
        );

        $slot1330 = collect($slots)->firstWhere('time', '13:30');

        // 13:30 normalde boştur ancak 60 dk işlem 13:30-14:30 süreceğinden ve 14:00 dolu olduğundan 13:30 da KAPALI olmalıdır!
        $this->assertNotNull($slot1330);
        $this->assertFalse($slot1330['is_available']);
        $this->assertEquals('insufficient_duration', $slot1330['reason']);
    }

    public function test_employee_time_block_partial_and_full_day(): void
    {
        $testDate = Carbon::now()->addDays(60)->toDateString();

        // 11:00 - 12:00 arası izin
        EmployeeTimeBlock::create([
            'employee_id' => $this->employee->id,
            'date' => $testDate,
            'type' => 'partial_time',
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'reason' => 'Yemek Molası',
        ]);

        $slots = $this->slotService->getAvailableSlots(
            employeeId: $this->employee->id,
            date: $testDate,
            duration: 30,
            branchId: $this->branch->id
        );

        $slot1100 = collect($slots)->firstWhere('time', '11:00');
        $slot1130 = collect($slots)->firstWhere('time', '11:30');

        $this->assertFalse($slot1100['is_available']);
        $this->assertEquals('blocked', $slot1100['reason']);

        $this->assertFalse($slot1130['is_available']);
        $this->assertEquals('blocked', $slot1130['reason']);
    }
}
