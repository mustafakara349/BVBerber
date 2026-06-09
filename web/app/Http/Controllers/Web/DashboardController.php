<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(): View
    {
        $branchId = $this->getActiveBranchId();

        $widgets = $this->dashboardService->getWidgetData($branchId);
        $barbers = $this->dashboardService->getBarberPerformance($branchId);
        $topServices = $this->dashboardService->getTopServices($branchId);
        $revenueChart = $this->dashboardService->getRevenueChart($branchId);
        $todayAppointments = $this->dashboardService->getTodayAppointments($branchId);
        $pendingAppointments = $this->dashboardService->getPendingAppointments($branchId);
        $awaitingActionAppointments = $this->dashboardService->getAwaitingActionAppointments($branchId);

        return view('dashboard.index', compact('widgets', 'barbers', 'topServices', 'revenueChart', 'todayAppointments', 'pendingAppointments', 'awaitingActionAppointments'));
    }

    public function appointmentStats(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $branchId = $this->getActiveBranchId();
        $period = $request->input('period', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $stats = $this->dashboardService->getFilteredAppointmentStats($branchId, $period, $startDate, $endDate);

        return response()->json($stats);
    }

    private function getActiveBranchId(): int
    {
        return session('active_branch_id', 1);
    }
}
