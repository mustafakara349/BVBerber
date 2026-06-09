<?php

namespace App\Repositories\Eloquent;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;

class AppointmentRepository extends BaseRepository implements AppointmentRepositoryInterface
{
    public function __construct(Appointment $model)
    {
        parent::__construct($model);
    }

    public function getForBranch(int $branchId, array $filters = [], int $perPage = 15)
    {
        $query = $this->model->with(['customer', 'employee.user', 'appointmentServices.service'])
            ->forBranch($branchId)
            ->orderByRaw('CASE WHEN start_at >= CURRENT_DATE THEN 0 ELSE 1 END')
            ->orderByRaw('CASE WHEN start_at >= CURRENT_DATE THEN start_at END ASC')
            ->orderByRaw('CASE WHEN start_at < CURRENT_DATE THEN start_at END DESC');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('start_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('start_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q2) => $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        return $query->paginate($perPage);
    }

    public function getForDate(int $branchId, string $date)
    {
        return $this->model->with(['customer', 'employee.user', 'appointmentServices.service'])
            ->forBranch($branchId)
            ->forDate($date)
            ->orderBy('start_at')
            ->get();
    }

    public function getForEmployee(int $employeeId, string $date)
    {
        return $this->model->with(['customer', 'appointmentServices.service'])
            ->where('employee_id', $employeeId)
            ->forDate($date)
            ->orderBy('start_at')
            ->get();
    }

    public function getUpcoming(int $branchId, int $limit = 10)
    {
        return $this->model->with(['customer', 'employee.user'])
            ->forBranch($branchId)
            ->upcoming()
            ->orderBy('start_at')
            ->limit($limit)
            ->get();
    }

    public function getTodayStats(int $branchId): array
    {
        $today = $this->model->forBranch($branchId)->today();

        return [
            'total' => (clone $today)->count(),
            'completed' => (clone $today)->where('status', AppointmentStatus::Completed)->count(),
            'pending' => (clone $today)->where('status', AppointmentStatus::Pending)->count(),
            'cancelled' => (clone $today)->where('status', AppointmentStatus::Cancelled)->count(),
            'revenue' => (clone $today)->where('status', AppointmentStatus::Completed)->sum('total_price'),
        ];
    }
}
