<?php

namespace App\Http\Controllers\Web;

use App\Enums\SalaryType;
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
        try {
            $selectedMonth = Carbon::createFromFormat('Y-m', $monthInput);
        } catch (\Exception $e) {
            $monthInput = now()->format('Y-m');
            $selectedMonth = now();
        }

        $commissions = $this->commissionService->calculateCommissions($branchId, $selectedMonth);

        // Arama filtresi (Personel Adı)
        if ($request->filled('search')) {
            $search = mb_strtolower(trim($request->input('search')));
            $commissions = array_filter($commissions, function ($row) use ($search) {
                $name = mb_strtolower($row['employee']->full_name ?? '');
                return str_contains($name, $search);
            });
        }

        // Maaş Tipi filtresi
        if ($request->filled('salary_type')) {
            $salaryType = $request->input('salary_type');
            $commissions = array_filter($commissions, function ($row) use ($salaryType) {
                $type = $row['employee']->salary_type instanceof SalaryType 
                    ? $row['employee']->salary_type->value 
                    : (string) $row['employee']->salary_type;
                return $type === $salaryType;
            });
        }

        // Aylar için dropdown doldur (Son 12 ay)
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $months[$date->format('Y-m')] = $date->translatedFormat('F Y');
        }

        $salaryTypes = SalaryType::cases();

        return view('finance.commissions.index', compact('commissions', 'selectedMonth', 'monthInput', 'months', 'salaryTypes'));
    }
}
