<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Enums\AppointmentStatus;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use App\Services\AppointmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AppointmentRepositoryInterface $appointmentRepo,
        private AppointmentService $appointmentService
    ) {}

    public function availableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'employee_id' => 'required|exists:employees,id',
            'duration' => 'nullable|integer|min:0',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $date = $request->date;
        $employeeId = (int) $request->employee_id;
        $branchId = (int) $request->get('branch_id', 1);
        $duration = (int) $request->get('duration', 30);

        $slots = $this->appointmentService->getAvailableSlots($branchId, $employeeId, $date, $duration);

        return $this->success([
            'slots' => $slots,
            'busy_times' => collect($slots)->where('is_busy', true)->pluck('time')->values()->all(),
            'available_times' => collect($slots)->where('is_available', true)->pluck('time')->values()->all(),
        ], 'Müsait randevu saatleri başarıyla getirildi.');
    }

    public function index(Request $request): JsonResponse
    {
        $branchId = $request->get('branch_id', 1);
        $user = $request->user();

        $filters = [
            'status' => $request->get('status'),
            'employee_id' => $request->get('employee_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search'),
        ];

        if ($user && $user->isCustomer()) {
            $filters['customer_id'] = $user->id;
        }

        $appointments = $this->appointmentRepo->getForBranch($branchId, $filters, $request->get('per_page', 15));

        return $this->paginatedSuccess(
            $appointments->through(fn ($a) => new AppointmentResource($a)),
            'Appointments fetched successfully'
        );
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $data['created_by'] = $user->id;

        // IDOR Koruması: Müşteri rolündeki kullanıcı yalnızca kendi adına randevu oluşturabilir
        if ($user->isCustomer()) {
            $data['customer_id'] = $user->id;
        }

        try {
            $appointment = $this->appointmentService->createAppointment($data);
        } catch (\App\Exceptions\AppointmentConflictException $e) {
            return $this->error($e->getMessage(), 409);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new AppointmentResource($appointment),
            'Appointment created successfully',
            201
        );
    }

    public function show(Appointment $appointment): JsonResponse
    {
        if ($appointment->customer_id != request()->user()->id && request()->user()->isCustomer()) {
            return $this->error('Bu randevu size ait değil.', 403);
        }

        $appointment->load([
            'customer', 'employee.user', 'branch',
            'appointmentServices.service', 'payments',
            'statusLogs.changedBy', 'review',
        ]);

        return $this->success(
            new AppointmentResource($appointment),
            'Appointment fetched successfully'
        );
    }

    public function updateStatus(Request $request, Appointment $appointment): JsonResponse
    {
        $request->validate(['status' => 'required|string']);

        $user = $request->user();

        if ($appointment->customer_id != $user->id && $user->isCustomer()) {
            return $this->error('Bu randevu size ait değil.', 403);
        }

        $status = AppointmentStatus::from($request->status);

        // Müşteri sadece randevusunu iptal edebilir; tamamlandı veya diğer statülere çekemez
        if ($user->isCustomer() && $status !== AppointmentStatus::Cancelled) {
            return $this->error('Müşteriler yalnızca randevularını iptal edebilir.', 403);
        }

        $appointment = $this->appointmentService->updateStatus(
            $appointment,
            $status,
            $user->id,
            $request->get('note')
        );

        return $this->success(
            new AppointmentResource($appointment),
            'Appointment status updated successfully'
        );
    }
}
