<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * Belirtilen ay ve şube için çalışanların baz maaş ve prim hesaplamasını yapar.
     */
    public function calculateCommissions(int $branchId, Carbon $month): array
    {
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        // Şubedeki aktif çalışanları al
        $employees = Employee::forBranch($branchId)
            ->active()
            ->with('user')
            ->get();

        $results = [];

        foreach ($employees as $employee) {
            $baseSalary = 0;
            $commissionRate = $employee->commission_rate ?? 0;
            $totalRevenue = 0;
            $commissionEarned = 0;

            // Baz Maaş belirleme
            if (in_array($employee->salary_type->value ?? $employee->salary_type, ['fixed', 'fixed_plus_commission'])) {
                $baseSalary = (float) $employee->salary_amount;
            }

            // Prim hak edişi hesaplama (sadece completed randevular)
            if (in_array($employee->salary_type->value ?? $employee->salary_type, ['commission', 'fixed_plus_commission'])) {
                
                // Çalışanın o ayki tamamlanmış randevulardaki toplam hizmet geliri
                // Not: Her randevuda birden fazla hizmet olabilir, sadece bu personelin verdikleri
                $totalRevenue = DB::table('appointment_services')
                    ->join('appointments', 'appointment_services.appointment_id', '=', 'appointments.id')
                    ->where('appointment_services.employee_id', $employee->id)
                    ->where('appointments.branch_id', $branchId)
                    ->where('appointments.status', 'completed')
                    ->whereBetween('appointments.start_at', [$startDate, $endDate])
                    ->sum('appointment_services.total_price');

                if ($commissionRate > 0) {
                    $commissionEarned = $totalRevenue * ($commissionRate / 100);
                }
            }

            $totalEarnings = $baseSalary + $commissionEarned;

            $results[] = [
                'employee' => $employee,
                'salary_type' => $employee->salary_type->label() ?? $employee->salary_type,
                'base_salary' => $baseSalary,
                'commission_rate' => $commissionRate,
                'total_revenue' => $totalRevenue,
                'commission_earned' => $commissionEarned,
                'total_earnings' => $totalEarnings,
            ];
        }

        // Toplam kazanca göre büyükten küçüğe sırala
        usort($results, fn($a, $b) => $b['total_earnings'] <=> $a['total_earnings']);

        return $results;
    }
}
