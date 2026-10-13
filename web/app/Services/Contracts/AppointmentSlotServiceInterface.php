<?php

namespace App\Services\Contracts;

interface AppointmentSlotServiceInterface
{
    /**
     * Belirtilen berber, tarih ve işlem süresine göre günün tüm slotlarını ve müsaitlik durumlarını döner.
     *
     * @param int $employeeId
     * @param string $date (Y-m-d)
     * @param int $duration (dakika cinsinden toplam süre, varsayılan 30)
     * @param int|null $branchId (opsiyonel şube ID)
     * @param int|null $excludeAppointmentId (güncelleme durumunda hariç tutulacak randevu ID)
     * @return array<int, array{time: string, is_available: bool, reason?: string}>
     */
    public function getAvailableSlots(
        int $employeeId,
        string $date,
        int $duration = 30,
        ?int $branchId = null,
        ?int $excludeAppointmentId = null
    ): array;
}
