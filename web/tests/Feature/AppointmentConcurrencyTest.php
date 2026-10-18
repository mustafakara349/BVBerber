<?php

namespace Tests\Feature;

use App\Exceptions\AppointmentConflictException;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AppointmentConcurrencyTest extends TestCase
{
    use DatabaseTransactions;

    private AppointmentService $appointmentService;
    private Employee $employee;
    private Branch $branch;
    private Service $service60Min;
    private Service $service30Min;
    private User $customer1;
    private User $customer2;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'bvapp_db',
        ]);

        $this->appointmentService = app(AppointmentService::class);

        $this->branch = Branch::first();
        $this->employee = Employee::first();
        $this->customer1 = User::first();
        $this->customer2 = User::where('id', '!=', $this->customer1->id)->first() ?? $this->customer1;

        $this->service60Min = Service::where('duration_minutes', '>=', 60)->first() ?? Service::first();
        $this->service30Min = Service::where('duration_minutes', '<=', 30)->first() ?? Service::first();
    }

    public function test_overlapping_appointment_is_rejected_and_cannot_double_book(): void
    {
        $testDate = Carbon::now()->addDays(rand(90, 150))->toDateString();

        // 1. Müşteri 14:00 için 60 dakikalık randevu alıyor (14:00 - 15:00)
        $appt1 = $this->appointmentService->createAppointment([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer1->id,
            'employee_id' => $this->employee->id,
            'start_at' => Carbon::parse($testDate . ' 14:00:00'),
            'source' => \App\Enums\AppointmentSource::MobileApp,
            'services' => [
                ['service_id' => $this->service60Min->id, 'quantity' => 1],
            ],
        ]);

        $this->assertNotNull($appt1);
        $this->assertEquals($this->service60Min->duration_minutes, $appt1->total_duration);
        $this->assertEquals(
            Carbon::parse($testDate . ' 14:00:00')->addMinutes($this->service60Min->duration_minutes)->format('Y-m-d H:i:s'),
            $appt1->end_at->format('Y-m-d H:i:s')
        );

        // 2. Müşteri tam o aralığa denk gelen 14:30 için randevu almaya çalışıyor
        $this->expectException(AppointmentConflictException::class);

        $this->appointmentService->createAppointment([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer2->id,
            'employee_id' => $this->employee->id,
            'start_at' => Carbon::parse($testDate . ' 14:30:00'),
            'source' => \App\Enums\AppointmentSource::MobileApp,
            'services' => [
                ['service_id' => $this->service30Min->id, 'quantity' => 1],
            ],
        ]);
    }

    public function test_mobile_api_returns_409_conflict_on_overlapping_appointment(): void
    {
        $testDate = Carbon::now()->addDays(rand(160, 220))->toDateString();

        // İlk randevuyu oluştur (14:00 - 15:00)
        $this->appointmentService->createAppointment([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer1->id,
            'employee_id' => $this->employee->id,
            'start_at' => Carbon::parse($testDate . ' 14:00:00'),
            'source' => \App\Enums\AppointmentSource::MobileApp,
            'services' => [
                ['service_id' => $this->service60Min->id, 'quantity' => 1],
            ],
        ]);

        // 2. müşteri mobile API üzerinden 14:30'a randevu almaya çalışır
        $response = $this->actingAs($this->customer2, 'sanctum')
            ->postJson('/api/v1/mobile/document/appointments', [
                'barberId' => $this->employee->id,
                'serviceId' => $this->service30Min->id,
                'date' => $testDate,
                'time' => '14:30',
            ]);

        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
