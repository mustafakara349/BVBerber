<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    public function getRevenueStats(int $branchId, string $period = 'month'): array;
    public function getAppointmentStats(int $branchId, string $period = 'month'): array;
    public function getFilteredAppointmentStats(int $branchId, string $period, ?string $startDate = null, ?string $endDate = null): array;
    public function getBarberPerformance(int $branchId): array;
    public function getTopServices(int $branchId, int $limit = 5): array;
    public function getCustomerStats(int $branchId): array;
    public function getFinancialOverview(int $branchId): array;
    public function getRevenueChart(int $branchId, string $period = 'year'): array;
    public function getHourlyDensity(int $branchId, string $date): array;
}
