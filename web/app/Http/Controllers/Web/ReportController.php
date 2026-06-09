<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Expense;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index()
    {
        $branchId = session('active_branch_id', 1);

        $generalStats = [
            'total_appointments' => Appointment::forBranch($branchId)->count(),
            'total_customers'    => User::customers()->count(),
            'total_income'       => Transaction::forBranch($branchId)->income()->sum('amount'),
            'total_expense'      => Expense::forBranch($branchId)->sum('amount'),
        ];

        return view('reports.index', compact('generalStats'));
    }

    public function show(string $type, Request $request)
    {
        $branchId = session('active_branch_id', 1);

        [$startDate, $endDate, $period] = $this->reportService->parseDateRange(
            $request->get('period', 'this_month'),
            $request->get('start_date'),
            $request->get('end_date')
        );

        $filters = [
            'period'     => $period,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date'   => $endDate->format('Y-m-d'),
        ];

        return match ($type) {
            'finance' => view('reports.finance', array_merge(
                $this->reportService->financeReport($branchId, $startDate, $endDate),
                compact('filters')
            )),
            'appointments' => view('reports.appointments', array_merge(
                $this->reportService->appointmentReport($branchId, $startDate, $endDate),
                compact('filters')
            )),
            'customers' => view('reports.customers', array_merge(
                $this->reportService->customerReport($branchId, $startDate, $endDate),
                compact('filters')
            )),
            default => abort(404),
        };
    }
}
