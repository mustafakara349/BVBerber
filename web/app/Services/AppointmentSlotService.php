<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeTimeBlock;
use App\Services\Contracts\AppointmentSlotServiceInterface;
use Carbon\Carbon;

class AppointmentSlotService implements AppointmentSlotServiceInterface
{
    /**
     * Belirtilen berber, tarih ve işlem süresine göre günün tüm slotlarını ve müsaitlik durumlarını döner.
     */
    public function getAvailableSlots(
        int $employeeId,
        string $date,
        int $duration = 30,
        ?int $branchId = null,
        ?int $excludeAppointmentId = null
    ): array {
        $employee = Employee::findOrFail($employeeId);

        // İşe başlama tarihinden önce randevu alınamaz
        if ($employee->hire_date && Carbon::parse($date)->startOfDay()->lt($employee->hire_date->startOfDay())) {
            return [];
        }

        // İşten ayrılma tarihinden sonra randevu alınamaz
        if ($employee->leave_date && Carbon::parse($date)->startOfDay()->gt($employee->leave_date->startOfDay())) {
            return [];
        }

        $effectiveBranchId = $branchId ?: ($employee->branch_id ?: 1);

        // Çalışanın İzin ve Kısıtlamalarını Çek
        $timeBlocks = EmployeeTimeBlock::where('employee_id', $employeeId)
            ->where('date', $date)
            ->get();

        // Eğer o gün tüm gün izinliyse boş liste dön (randevu alınamaz)
        if ($timeBlocks->where('type', 'full_day')->count() > 0) {
            return [];
        }

        // Şube açılış ve kapanış saatlerini belirle
        $branch = Branch::find($effectiveBranchId);
        $openTimeStr = '08:00:00';
        $closeTimeStr = '20:00:00';

        if ($branch) {
            if ($branch->opening_time) {
                $openTimeStr = $branch->opening_time instanceof \DateTimeInterface
                    ? $branch->opening_time->format('H:i:s')
                    : (string) $branch->opening_time;
            }
            if ($branch->closing_time) {
                $closeTimeStr = $branch->closing_time instanceof \DateTimeInterface
                    ? $branch->closing_time->format('H:i:s')
                    : (string) $branch->closing_time;
            }
        }

        // Berberin o günkü aktif randevuları
        $appointmentQuery = Appointment::where('employee_id', $employeeId)
            ->whereDate('start_at', $date)
            ->whereNotIn('status', [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::Rejected->value,
                AppointmentStatus::NoShow->value,
            ]);

        if ($excludeAppointmentId) {
            $appointmentQuery->where('id', '!=', $excludeAppointmentId);
        }

        $appointments = $appointmentQuery->get();

        $slots = [];
        $startTime = Carbon::parse($date . ' ' . $openTimeStr);
        $endTime = Carbon::parse($date . ' ' . $closeTimeStr);

        while ($startTime < $endTime) {
            $slotTime = $startTime->copy();
            $isAvailable = true;
            $reason = null;

            // 1) Mevcut randevu çakışması (Interval Overlap: [start_at, end_at))
            foreach ($appointments as $apt) {
                $aptStart = Carbon::parse($apt->start_at);
                $aptEnd = $apt->end_at
                    ? Carbon::parse($apt->end_at)
                    : $aptStart->copy()->addMinutes($apt->total_duration ?: 30);

                if ($slotTime >= $aptStart && $slotTime < $aptEnd) {
                    $isAvailable = false;
                    $reason = 'booked';
                    break;
                }
            }

            // 2) Kısmi saatlik izin / bloklama kontrolü
            if ($isAvailable) {
                foreach ($timeBlocks as $block) {
                    if ($block->type === 'partial_time') {
                        $blockStart = Carbon::parse($date . ' ' . $block->start_time);
                        $blockEnd = Carbon::parse($date . ' ' . $block->end_time);

                        if ($slotTime >= $blockStart && $slotTime < $blockEnd) {
                            $isAvailable = false;
                            $reason = 'blocked';
                            break;
                        }
                    }
                }
            }

            // 3) Geçmiş zaman kontrolü (Bugünün geçmiş saatleri seçilemez)
            if ($isAvailable && $date === today()->toDateString() && $slotTime <= now()) {
                $isAvailable = false;
                $reason = 'past';
            }

            $slotItem = [
                'time' => $slotTime->format('H:i'),
                'is_available' => $isAvailable,
            ];

            if ($reason) {
                $slotItem['reason'] = $reason;
            }

            $slots[] = $slotItem;

            $startTime->addMinutes(30);
        }

        // 4) Hizmet süresine göre ardışık slot gereksinimi kontrolü
        $duration = max(15, (int) $duration);
        $requiredSlotsCount = max(1, (int) ceil($duration / 30));

        if ($requiredSlotsCount > 1) {
            $totalSlots = count($slots);
            for ($index = 0; $index < $totalSlots; $index++) {
                if (!$slots[$index]['is_available']) {
                    continue;
                }

                $consecutiveAvailable = true;
                for ($i = 1; $i < $requiredSlotsCount; $i++) {
                    $nextIndex = $index + $i;
                    if (!isset($slots[$nextIndex]) || !$slots[$nextIndex]['is_available']) {
                        $consecutiveAvailable = false;
                        break;
                    }
                }

                if (!$consecutiveAvailable) {
                    $slots[$index]['is_available'] = false;
                    $slots[$index]['reason'] = 'insufficient_duration';
                }
            }
        }

        return $slots;
    }
}
