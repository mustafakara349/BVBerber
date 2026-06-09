<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $branchId = $request->get('branch_id', 1);

        $employees = Employee::query()
            ->forBranch($branchId)
            ->active()
            ->visible()
            ->with(['user:id,first_name,last_name,profile_photo'])
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->user->first_name ?? '',
                    'surname' => $employee->user->last_name ?? '',
                    'profile_image_url' => $employee->user->profile_photo 
                        ? url('storage/' . $employee->user->profile_photo) 
                        : null,
                    'title' => $employee->title,
                    'biography' => $employee->biography,
                    'rating' => $employee->average_rating,
                ];
            });

        return $this->success($employees, 'Employees list fetched successfully');
    }
}
