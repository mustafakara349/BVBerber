<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * ReportService
 *
 * ReportController'dan taşınan tüm veri toplama, tarih hesaplama
 * ve grafik verisi dönüşüm mantığını barındırır.
 * Controller sadece bu servisi çağırır ve view döner.
 */
class ReportService
{
    /**
     * Request parametrelerinden tarih aralığı hesaplar.
     */
    public function parseDateRange(string $period, ?string $startDateInput = null, ?string $endDateInput = null): array
    {
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate   = Carbon::parse($endDateInput)->endOfDay();
        } else {
            [$startDate, $endDate] = match ($period) {
                'today'        => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
                'this_week'    => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
                'last_30_days' => [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()],
                'last_month'   => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
                'this_year'    => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
                default        => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            };

            if (! in_array($period, ['today', 'this_week', 'last_30_days', 'last_month', 'this_year'])) {
                $period = 'this_month';
            }
        }

        return [$startDate, $endDate, $period];
    }

    // -------------------------------------------------------------------------
    // Finans Raporu
    // -------------------------------------------------------------------------

    public function financeReport(int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        $totalIncome  = Transaction::forBranch($branchId)->income()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $totalExpense = Expense::forBranch($branchId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $netProfit  = $totalIncome - $totalExpense;
        $diffInDays = $startDate->diffInDays($endDate);
        $groupByMonth = $diffInDays > 90;

        [$chartTimeline, $chartIncomeData, $chartExpenseData] = $this->buildFinanceTimeline(
            $branchId, $startDate, $endDate, $groupByMonth
        );

        $incomeByMethod = Transaction::forBranch($branchId)->income()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->map(fn($t) => [
                'label' => $t->payment_method ? $t->payment_method->label() : 'Belirtilmemiş',
                'total' => round($t->total, 2),
            ])->toArray();

        $expenseByCategory = Expense::where('expenses.branch_id', $branchId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name as category_name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn($e) => [
                'label' => $e->category_name,
                'total' => round($e->total, 2),
            ])->toArray();

        $incomes  = Transaction::forBranch($branchId)->income()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->latest('transaction_date')->take(50)->get();

        $expenses = Expense::forBranch($branchId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->latest('expense_date')->take(50)->get();

        return compact(
            'totalIncome', 'totalExpense', 'netProfit',
            'chartTimeline', 'chartIncomeData', 'chartExpenseData',
            'incomeByMethod', 'expenseByCategory',
            'incomes', 'expenses'
        );
    }

    private function buildFinanceTimeline(int $branchId, Carbon $startDate, Carbon $endDate, bool $groupByMonth): array
    {
        $timeline = $incomeData = $expenseData = [];

        if ($groupByMonth) {
            $incomeHistory  = Transaction::forBranch($branchId)->income()
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->select(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as month_key"), DB::raw('SUM(amount) as total'))
                ->groupBy('month_key')->pluck('total', 'month_key')->toArray();

            $expenseHistory = Expense::forBranch($branchId)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->select(DB::raw("DATE_FORMAT(expense_date, '%Y-%m') as month_key"), DB::raw('SUM(amount) as total'))
                ->groupBy('month_key')->pluck('total', 'month_key')->toArray();

            $current = clone $startDate;
            while ($current->lte($endDate)) {
                $key       = $current->format('Y-m');
                $timeline[]    = $current->translatedFormat('F Y');
                $incomeData[]  = round($incomeHistory[$key] ?? 0, 2);
                $expenseData[] = round($expenseHistory[$key] ?? 0, 2);
                $current->addMonth()->startOfMonth();
            }
        } else {
            $incomeHistory  = Transaction::forBranch($branchId)->income()
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->select(DB::raw('DATE(transaction_date) as date'), DB::raw('SUM(amount) as total'))
                ->groupBy('date')->pluck('total', 'date')->toArray();

            $expenseHistory = Expense::forBranch($branchId)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->select(DB::raw('DATE(expense_date) as date'), DB::raw('SUM(amount) as total'))
                ->groupBy('date')->pluck('total', 'date')->toArray();

            $current = clone $startDate;
            while ($current->lte($endDate)) {
                $key       = $current->format('Y-m-d');
                $timeline[]    = $current->format('d.m.Y');
                $incomeData[]  = round($incomeHistory[$key] ?? 0, 2);
                $expenseData[] = round($expenseHistory[$key] ?? 0, 2);
                $current->addDay();
            }
        }

        return [$timeline, $incomeData, $expenseData];
    }

    // -------------------------------------------------------------------------
    // Randevu Raporu
    // -------------------------------------------------------------------------

    public function appointmentReport(int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        $statusCounts = Appointment::forBranch($branchId)
            ->whereBetween('start_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalAppointments = array_sum($statusCounts);
        $completedCount    = $statusCounts['completed'] ?? 0;
        $cancelledCount    = $statusCounts['cancelled'] ?? 0;
        $rejectedCount     = $statusCounts['rejected']  ?? 0;
        $noShowCount       = $statusCounts['no_show']   ?? 0;

        $barberStats = DB::table('employees')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->leftJoin('appointments', function ($join) use ($startDate, $endDate) {
                $join->on('employees.id', '=', 'appointments.employee_id')
                     ->whereBetween('appointments.start_at', [$startDate, $endDate]);
            })
            ->where('employees.branch_id', $branchId)
            ->where('employees.is_active', true)
            ->select(
                'employees.id',
                'users.first_name',
                'users.last_name',
                'employees.title',
                DB::raw("COUNT(CASE WHEN appointments.status = 'completed' THEN 1 END) as completed_count"),
                DB::raw("COUNT(CASE WHEN appointments.status IN ('cancelled', 'rejected', 'no_show') THEN 1 END) as cancelled_count"),
                DB::raw("COALESCE(SUM(CASE WHEN appointments.status = 'completed' THEN appointments.total_price ELSE 0 END), 0) as total_revenue")
            )
            ->groupBy('employees.id', 'users.first_name', 'users.last_name', 'employees.title')
            ->orderByDesc('total_revenue')
            ->get();

        $serviceStats = DB::table('services')
            ->leftJoin('appointment_services', 'services.id', '=', 'appointment_services.service_id')
            ->leftJoin('appointments', function ($join) use ($startDate, $endDate) {
                $join->on('appointment_services.appointment_id', '=', 'appointments.id')
                     ->where('appointments.status', '=', 'completed')
                     ->whereBetween('appointments.start_at', [$startDate, $endDate]);
            })
            ->where('services.branch_id', $branchId)
            ->where('services.is_active', true)
            ->select(
                'services.id',
                'services.name',
                DB::raw('COUNT(appointments.id) as completed_count'),
                DB::raw('COALESCE(SUM(appointment_services.total_price), 0) as total_revenue')
            )
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('completed_count')
            ->get();

        $cancellationReasons = Appointment::forBranch($branchId)
            ->whereBetween('start_at', [$startDate, $endDate])
            ->whereIn('status', ['cancelled', 'rejected'])
            ->whereNotNull('cancellation_reason')
            ->where('cancellation_reason', '!=', '')
            ->select('cancellation_reason', DB::raw('count(*) as total'))
            ->groupBy('cancellation_reason')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $diffInDays   = $startDate->diffInDays($endDate);
        $groupByMonth = $diffInDays > 90;

        [$chartTimeline, $chartCompletedData, $chartCancelledData] = $this->buildAppointmentTimeline(
            $branchId, $startDate, $endDate, $groupByMonth
        );

        return compact(
            'totalAppointments', 'completedCount', 'cancelledCount', 'rejectedCount', 'noShowCount',
            'barberStats', 'serviceStats', 'cancellationReasons',
            'chartTimeline', 'chartCompletedData', 'chartCancelledData'
        );
    }

    private function buildAppointmentTimeline(int $branchId, Carbon $startDate, Carbon $endDate, bool $groupByMonth): array
    {
        $timeline = $completedData = $cancelledData = [];

        if ($groupByMonth) {
            $completedHistory = Appointment::forBranch($branchId)
                ->whereBetween('start_at', [$startDate, $endDate])->where('status', 'completed')
                ->select(DB::raw("DATE_FORMAT(start_at, '%Y-%m') as month_key"), DB::raw('count(*) as total'))
                ->groupBy('month_key')->pluck('total', 'month_key')->toArray();

            $cancelledHistory = Appointment::forBranch($branchId)
                ->whereBetween('start_at', [$startDate, $endDate])->whereIn('status', ['cancelled', 'rejected'])
                ->select(DB::raw("DATE_FORMAT(start_at, '%Y-%m') as month_key"), DB::raw('count(*) as total'))
                ->groupBy('month_key')->pluck('total', 'month_key')->toArray();

            $current = clone $startDate;
            while ($current->lte($endDate)) {
                $key           = $current->format('Y-m');
                $timeline[]    = $current->translatedFormat('F Y');
                $completedData[] = $completedHistory[$key] ?? 0;
                $cancelledData[] = $cancelledHistory[$key] ?? 0;
                $current->addMonth()->startOfMonth();
            }
        } else {
            $completedHistory = Appointment::forBranch($branchId)
                ->whereBetween('start_at', [$startDate, $endDate])->where('status', 'completed')
                ->select(DB::raw('DATE(start_at) as date'), DB::raw('count(*) as total'))
                ->groupBy('date')->pluck('total', 'date')->toArray();

            $cancelledHistory = Appointment::forBranch($branchId)
                ->whereBetween('start_at', [$startDate, $endDate])->whereIn('status', ['cancelled', 'rejected'])
                ->select(DB::raw('DATE(start_at) as date'), DB::raw('count(*) as total'))
                ->groupBy('date')->pluck('total', 'date')->toArray();

            $current = clone $startDate;
            while ($current->lte($endDate)) {
                $key             = $current->format('Y-m-d');
                $timeline[]      = $current->format('d.m.Y');
                $completedData[] = $completedHistory[$key] ?? 0;
                $cancelledData[] = $cancelledHistory[$key] ?? 0;
                $current->addDay();
            }
        }

        return [$timeline, $completedData, $cancelledData];
    }

    // -------------------------------------------------------------------------
    // Müşteri Raporu
    // -------------------------------------------------------------------------

    public function customerReport(int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        $newCustomers   = User::customers()->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalCustomers = User::customers()->count();

        $loyalCustomers = DB::table('appointments')
            ->where('branch_id', $branchId)->where('status', 'completed')
            ->select('customer_id', DB::raw('COUNT(*) as visit_count'))
            ->groupBy('customer_id')->having('visit_count', '>=', 3)->count();

        $genders = User::customers()
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')->pluck('count', 'gender')->toArray();

        $genderDistribution = [
            ['label' => 'Erkek',               'total' => $genders['male']   ?? 0],
            ['label' => 'Kadın',               'total' => $genders['female'] ?? 0],
            ['label' => 'Diğer/Belirtilmemiş', 'total' => ($genders['other'] ?? 0) + ($genders[''] ?? 0)],
        ];

        $topCustomers = User::customers()
            ->join('appointments', 'users.id', '=', 'appointments.customer_id')
            ->where('appointments.branch_id', $branchId)
            ->where('appointments.status', 'completed')
            ->whereBetween('appointments.start_at', [$startDate, $endDate])
            ->select(
                'users.id', 'users.first_name', 'users.last_name',
                'users.email', 'users.phone', 'users.profile_photo', 'users.created_at',
                DB::raw('COUNT(appointments.id) as visits_count'),
                DB::raw('SUM(appointments.total_price) as total_spent'),
                DB::raw('MAX(appointments.start_at) as last_visit_date')
            )
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.phone', 'users.profile_photo', 'users.created_at')
            ->orderByDesc('total_spent')
            ->take(20)->get();

        $diffInDays   = $startDate->diffInDays($endDate);
        $groupByMonth = $diffInDays > 90;

        [$chartTimeline, $chartRegData] = $this->buildCustomerTimeline($startDate, $endDate, $groupByMonth);

        return compact(
            'newCustomers', 'totalCustomers', 'loyalCustomers', 'genderDistribution',
            'topCustomers', 'chartTimeline', 'chartRegData'
        );
    }

    private function buildCustomerTimeline(Carbon $startDate, Carbon $endDate, bool $groupByMonth): array
    {
        $timeline = $regData = [];

        if ($groupByMonth) {
            $regHistory = User::customers()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"), DB::raw('count(*) as total'))
                ->groupBy('month_key')->pluck('total', 'month_key')->toArray();

            $current = clone $startDate;
            while ($current->lte($endDate)) {
                $key       = $current->format('Y-m');
                $timeline[] = $current->translatedFormat('F Y');
                $regData[]  = $regHistory[$key] ?? 0;
                $current->addMonth()->startOfMonth();
            }
        } else {
            $regHistory = User::customers()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
                ->groupBy('date')->pluck('total', 'date')->toArray();

            $current = clone $startDate;
            while ($current->lte($endDate)) {
                $key       = $current->format('Y-m-d');
                $timeline[] = $current->format('d.m.Y');
                $regData[]  = $regHistory[$key] ?? 0;
                $current->addDay();
            }
        }

        return [$timeline, $regData];
    }
}
