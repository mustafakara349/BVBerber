<?php

namespace App\Repositories\Contracts;

interface AppointmentRepositoryInterface extends BaseRepositoryInterface
{
    public function getForBranch(int $branchId, array $filters = [], int $perPage = 15);
    public function getForDate(int $branchId, string $date);
    public function getForEmployee(int $employeeId, string $date);
    public function getUpcoming(int $branchId, int $limit = 10);
    public function getTodayStats(int $branchId): array;
}
