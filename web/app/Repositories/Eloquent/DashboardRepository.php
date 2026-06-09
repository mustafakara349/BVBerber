<?php

namespace App\Repositories\Eloquent;

use App\Enums\AppointmentStatus;
use App\Enums\TransactionType;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Review;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getRevenueStats(int $branchId, string $period = 'month'): array
    {
        $base = Transaction::forBranch($branchId)->income();
        $dailyExpense = Expense::forBranch($branchId)->whereDate('expense_date', today())->sum('amount');

        return [
            'daily' => round((clone $base)->whereDate('transaction_date', today())->sum('amount'), 2),
            'daily_expense' => round($dailyExpense, 2),
            'weekly' => round((clone $base)->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount'), 2),
            'monthly' => round((clone $base)->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->sum('amount'), 2),
            'yearly' => round((clone $base)->whereYear('transaction_date', now()->year)->sum('amount'), 2),
        ];
    }

    public function getAppointmentStats(int $branchId, string $period = 'month'): array
    {
        $base = Appointment::forBranch($branchId);
        $today = (clone $base)->today();

        $monthBase = (clone $base)->whereMonth('start_at', now()->month)->whereYear('start_at', now()->year);
        $totalMonth = (clone $monthBase)->count();

        return [
            'today_total' => (clone $today)->count(),
            'today_completed' => (clone $today)->where('status', AppointmentStatus::Completed)->count(),
            'today_pending' => (clone $today)->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])->count(),
            'month_total' => $totalMonth,
            'month_cancelled' => (clone $monthBase)->where('status', AppointmentStatus::Cancelled)->count(),
            'month_no_show' => (clone $monthBase)->where('status', AppointmentStatus::NoShow)->count(),
            'cancellation_rate' => $totalMonth > 0
                ? round((clone $monthBase)->where('status', AppointmentStatus::Cancelled)->count() / $totalMonth * 100, 1)
                : 0,
        ];
    }

    public function getFilteredAppointmentStats(int $branchId, string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Appointment::forBranch($branchId);

        if ($period === 'today') {
            $query->whereDate('start_at', today());
        } elseif ($period === 'month') {
            $query->whereMonth('start_at', now()->month)->whereYear('start_at', now()->year);
        } elseif ($period === 'year') {
            $query->whereYear('start_at', now()->year);
        } elseif ($period === 'custom' && $startDate && $endDate) {
            $query->whereBetween('start_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $total = (clone $query)->count();
        $cancelled = (clone $query)->where('status', AppointmentStatus::Cancelled)->count();
        $noShow = (clone $query)->where('status', AppointmentStatus::NoShow)->count();

        $avgSpending = (clone $query)->where('status', AppointmentStatus::Completed)->avg('total_price');

        return [
            'total' => $total,
            'cancelled' => $cancelled,
            'no_show' => $noShow,
            'cancellation_rate' => $total > 0 ? round(($cancelled / $total) * 100, 1) : 0,
            'avg_spending' => round($avgSpending ?? 0, 2),
        ];
    }

    public function getBarberPerformance(int $branchId): array
    {
        return Employee::with('user.role')
            ->whereHas('user.role', function($q) {
                $q->where('slug', 'barber');
            })
            ->forBranch($branchId)
            ->active()
            ->withCount(['appointments as completed_appointments_count' => function ($q) {
                $q->where('status', AppointmentStatus::Completed)
                    ->whereMonth('start_at', now()->month);
            }])
            ->withSum(['appointments as monthly_revenue' => function ($q) {
                $q->where('status', AppointmentStatus::Completed)
                    ->whereMonth('start_at', now()->month);
            }], 'total_price')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('monthly_revenue')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->full_name,
                'title' => $e->title,
                'photo' => $e->user?->profile_photo,
                'completed_appointments' => $e->completed_appointments_count ?? 0,
                'revenue' => round($e->monthly_revenue ?? 0, 2),
                'rating' => round($e->reviews_avg_rating ?? 0, 1),
                'commission_rate' => $e->commission_rate,
            ])
            ->toArray();
    }

    public function getTopServices(int $branchId, int $limit = 5): array
    {
        return DB::table('services')
            ->leftJoin('appointment_services', 'services.id', '=', 'appointment_services.service_id')
            ->leftJoin('appointments', function($join) use ($branchId) {
                $join->on('appointment_services.appointment_id', '=', 'appointments.id')
                     ->where('appointments.branch_id', '=', $branchId)
                     ->where('appointments.status', '=', AppointmentStatus::Completed->value);
            })
            ->where('services.branch_id', $branchId)
            ->where('services.is_popular', true)
            ->where('services.is_active', true)
            ->select(
                'services.id',
                'services.name',
                DB::raw('COUNT(appointments.id) as usage_count'),
                DB::raw('COALESCE(SUM(appointment_services.total_price), 0) as total_revenue')
            )
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => (array) $item)
            ->toArray();
    }

    public function getCustomerStats(int $branchId): array
    {
        $thisMonth = now()->startOfMonth();

        $newCustomers = User::customers()
            ->where('created_at', '>=', $thisMonth)
            ->count();

        $totalCustomers = User::customers()->count();

        $avgSpending = Appointment::forBranch($branchId)
            ->completed()
            ->whereMonth('completed_at', now()->month)
            ->avg('total_price');

        $loyalCustomers = DB::table('appointments')
            ->where('branch_id', $branchId)
            ->where('status', AppointmentStatus::Completed->value)
            ->select('customer_id', DB::raw('COUNT(*) as visit_count'))
            ->groupBy('customer_id')
            ->having('visit_count', '>=', 3)
            ->count();

        $genders = User::customers()
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        $male = $genders['male'] ?? 0;
        $female = $genders['female'] ?? 0;
        $other = $genders['other'] ?? 0;
        // if null is returned, we can ignore or add to other.
        $nullCount = $genders[''] ?? 0;
        $other += $nullCount;

        return [
            'total' => $totalCustomers,
            'new_this_month' => $newCustomers,
            'loyal' => $loyalCustomers,
            'avg_spending' => round($avgSpending ?? 0, 2),
            'genders' => [
                'male' => $male,
                'female' => $female,
                'other' => $other,
            ],
        ];
    }

    public function getFinancialOverview(int $branchId): array
    {
        $month = now()->month;
        $year = now()->year;

        $income = Transaction::forBranch($branchId)
            ->income()
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('amount');

        $expense = Expense::forBranch($branchId)
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->sum('amount');

        return [
            'income' => round($income, 2),
            'expense' => round($expense, 2),
            'profit' => round($income - $expense, 2),
        ];
    }

    public function getRevenueChart(int $branchId, string $period = 'year'): array
    {
        if ($period === 'day') {
            return collect(range(0, 23))->map(function ($hour) use ($branchId) {
                $revenue = Transaction::forBranch($branchId)
                    ->income()
                    ->whereDate('transaction_date', today())
                    ->whereRaw('HOUR(transaction_date) = ?', [$hour])
                    ->sum('amount');

                return [
                    'month' => null,
                    'label' => sprintf('%02d:00', $hour),
                    'revenue' => round($revenue, 2),
                ];
            })->toArray();
        }

        if ($period === 'month') {
            return collect(range(1, now()->daysInMonth))->map(function ($day) use ($branchId) {
                $revenue = Transaction::forBranch($branchId)
                    ->income()
                    ->whereDate('transaction_date', now()->setDay($day)->toDateString())
                    ->sum('amount');

                return [
                    'month' => now()->month,
                    'label' => $day . ' ' . now()->translatedFormat('M'),
                    'revenue' => round($revenue, 2),
                ];
            })->toArray();
        }

        $months = collect(range(1, 12))->map(function ($month) use ($branchId) {
            $revenue = Transaction::forBranch($branchId)
                ->income()
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount');

            return [
                'month' => $month,
                'label' => now()->month($month)->translatedFormat('M'),
                'revenue' => round($revenue, 2),
            ];
        });

        return $months->toArray();
    }

    public function getHourlyDensity(int $branchId, string $date): array
    {
        return Appointment::forBranch($branchId)
            ->forDate($date)
            ->select(DB::raw('HOUR(start_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->toArray();
    }
}
