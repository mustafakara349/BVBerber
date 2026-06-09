<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CommissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function __construct(private CommissionService $commissionService) {}

    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 1);
        
        // Varsayılan ay: Bu ay
        $monthInput = $request->get('month', now()->format('Y-m'));
        $selectedMonth = Carbon::createFromFormat('Y-m', $monthInput);

        $commissions = $this->commissionService->calculateCommissions($branchId, $selectedMonth);

        // Aylar için dropdown doldur (Son 12 ay)
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $months[$date->format('Y-m')] = $date->translatedFormat('F Y');
        }

        return view('finance.commissions.index', compact('commissions', 'selectedMonth', 'monthInput', 'months'));
    }
}
