<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(private DashboardService $dashboardService) {}

    public function index(Request $request): JsonResponse
    {
        $branchId = $request->get('branch_id', 1);

        return $this->success([
            'widgets' => $this->dashboardService->getWidgetData($branchId),
            'barbers' => $this->dashboardService->getBarberPerformance($branchId),
            'top_services' => $this->dashboardService->getTopServices($branchId),
            'revenue_chart' => $this->dashboardService->getRevenueChart($branchId),
        ], 'Dashboard data fetched successfully');
    }
}
