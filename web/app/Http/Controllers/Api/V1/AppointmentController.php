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
        $data['created_by'] = $request->user()->id;

        $appointment = $this->appointmentService->createAppointment($data);

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

        if ($appointment->customer_id != $request->user()->id && $request->user()->isCustomer()) {
            return $this->error('Bu randevu size ait değil.', 403);
        }

        $status = AppointmentStatus::from($request->status);

        $appointment = $this->appointmentService->updateStatus(
            $appointment,
            $status,
            $request->user()->id,
            $request->get('note')
        );

        return $this->success(
            new AppointmentResource($appointment),
            'Appointment status updated successfully'
        );
    }
}
