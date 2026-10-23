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
        $topServices = $this->dashboardService->getTopServices($branchId, 'month');
        $revenueChart = $this->dashboardService->getRevenueChart($branchId);
        $todayAppointments = $this->dashboardService->getTodayAppointments($branchId);
        $pendingAppointments = $this->dashboardService->getPendingAppointments($branchId);
        $awaitingActionAppointments = $this->dashboardService->getAwaitingActionAppointments($branchId);

        return view('dashboard.index', compact('widgets', 'barbers', 'topServices', 'revenueChart', 'todayAppointments', 'pendingAppointments', 'awaitingActionAppointments'));
    }

    public function liveSections(): \Illuminate\Http\JsonResponse
    {
        $branchId = $this->getActiveBranchId();

        $widgets = $this->dashboardService->getWidgetData($branchId);
        $todayAppointments = $this->dashboardService->getTodayAppointments($branchId);
        $pendingAppointments = $this->dashboardService->getPendingAppointments($branchId);
        $awaitingActionAppointments = $this->dashboardService->getAwaitingActionAppointments($branchId);

        return response()->json([
            'pending_html' => view('dashboard.partials.pending_appointments', compact('pendingAppointments'))->render(),
            'pending_count' => $pendingAppointments->count(),
            'awaiting_html' => view('dashboard.partials.awaiting_action_appointments', compact('awaitingActionAppointments'))->render(),
            'awaiting_count' => $awaitingActionAppointments->count(),
            'today_html' => view('dashboard.partials.today_appointments', compact('todayAppointments'))->render(),
            'today_count' => $todayAppointments->count(),
            'widgets_html' => view('dashboard.partials.revenue_widgets', compact('widgets'))->render(),
            'widgets_data' => [
                'today_total' => $widgets['appointments']['today_total'] ?? 0,
                'today_completed' => $widgets['appointments']['today_completed'] ?? 0,
                'daily_revenue' => number_format($widgets['revenue']['daily'] ?? 0, 2, ',', '.'),
                'daily_expense' => number_format($widgets['revenue']['daily_expense'] ?? 0, 2, ',', '.'),
                'new_customers' => $widgets['customers']['new_this_month'] ?? 0,
                'monthly_profit' => number_format($widgets['financial']['profit'] ?? 0, 2, ',', '.'),
                'monthly_income' => number_format($widgets['financial']['income'] ?? 0, 2, ',', '.'),
                'monthly_expense' => number_format($widgets['financial']['expense'] ?? 0, 2, ',', '.'),
            ],
        ]);
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

    public function topServices(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $branchId = $this->getActiveBranchId();
        $period = $request->input('period', 'month');

        $topServices = $this->dashboardService->getTopServices($branchId, $period);

        return response()->json($topServices);
    }

    private function getActiveBranchId(): int
    {
        return session('active_branch_id', 1);
    }
}
