<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentService as AppointmentServiceModel;
use App\Models\AppointmentStatusLog;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeTimeBlock;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentService
{
    public function __construct(
        private AppointmentRepositoryInterface $appointmentRepo,
        private CampaignService $campaignService
    ) {}

    public function createAppointment(array $data): Appointment
    {
        return DB::transaction(function () use ($data) {
            $data['uuid'] = (string) Str::uuid();
            $data['appointment_code'] = $this->generateCode();

            $requestedServices = $data['services'] ?? [];
            unset($data['services']);
            
            $couponCode = $data['coupon_code'] ?? null;
            unset($data['coupon_code']);

            // Fiyat ve süreyi veritabanından çek; client değerlerini yoksay.
            $serviceIds = collect($requestedServices)->pluck('service_id')->unique()->toArray();
            $dbServices = \App\Models\Service::whereIn('id', $serviceIds)
                ->get()
                ->keyBy('id');

            $totalDuration = 0;
            $subtotal = 0;
            $enrichedServices = [];

            foreach ($requestedServices as $item) {
                $serviceId = $item['service_id'];
                $quantity  = $item['quantity'] ?? 1;

                $dbService = $dbServices->get($serviceId);

                if (! $dbService) {
                    throw new \InvalidArgumentException("Hizmet bulunamadı: ID #{$serviceId}");
                }

                // Geçerli fiyat: varsa indirimli, yoksa normal fiyat
                $unitPrice      = (float) ($dbService->discounted_price ?? $dbService->price);
                $durationMinutes = (int) $dbService->duration_minutes;
                $lineTotal       = $unitPrice * $quantity;

                $totalDuration += $durationMinutes * $quantity;
                $subtotal      += $lineTotal;

                $enrichedServices[] = [
                    'service_id'       => $serviceId,
                    'quantity'         => $quantity,
                    'duration_minutes' => $durationMinutes,
                    'unit_price'       => $unitPrice,
                    'total_price'      => $lineTotal,
                ];
            }

            // Kampanya ve İndirim Hesaplama
            $customer = \App\Models\User::find($data['customer_id']);
            $discountAmount = 0;
            $campaignId = null;
            $couponId = null;
            $appliedCoupon = null;

            if ($customer) {
                if ($couponCode) {
                    $couponResult = $this->campaignService->validateCoupon($customer, $data['branch_id'], $couponCode, $subtotal, $serviceIds);
                    if ($couponResult['valid']) {
                        $discountAmount = $couponResult['discount_amount'];
                        $campaignId = $couponResult['campaign']?->id;
                        $couponId = $couponResult['coupon']->id;
                        $appliedCoupon = $couponResult['coupon'];
                    } else {
                        throw new \InvalidArgumentException($couponResult['message']);
                    }
                } elseif (!empty($data['campaign_id'])) {
                    $campaignResult = $this->campaignService->validateCampaign($customer, $data['branch_id'], $data['campaign_id'], $subtotal, $serviceIds);
                    if ($campaignResult['valid']) {
                        $discountAmount = $campaignResult['discount_amount'];
                        $campaignId = $campaignResult['campaign']->id;
                    } else {
                        throw new \InvalidArgumentException($campaignResult['message']);
                    }
                } elseif (!isset($data['source']) || $data['source'] !== 'admin_panel') {
                    $autoCampaignResult = $this->campaignService->evaluateCart($customer, $data['branch_id'], $subtotal, $serviceIds);
                    if ($autoCampaignResult) {
                        $discountAmount = $autoCampaignResult['discount_amount'];
                        $campaignId = $autoCampaignResult['campaign']->id;
                    }
                }
            }

            $data['end_at']         = \Carbon\Carbon::parse($data['start_at'])->addMinutes($totalDuration);
            $data['total_duration'] = $totalDuration;
            $data['subtotal']       = $subtotal;
            $data['discount_amount']= $discountAmount;
            $data['tax_amount']     = $data['tax_amount'] ?? 0;
            $data['total_price']    = max(0, $subtotal - $discountAmount + $data['tax_amount']);
            $data['campaign_id']    = $campaignId;
            $data['coupon_id']      = $couponId;
            $data['status']         = $data['status'] ?? AppointmentStatus::Pending;

            $newStart = Carbon::parse($data['start_at']);
            $newEnd = isset($data['end_at']) 
                ? Carbon::parse($data['end_at']) 
                : $newStart->copy()->addMinutes($totalDuration);
            $newStartStr = $newStart->format('Y-m-d H:i:s');
            $newEndStr = $newEnd->format('Y-m-d H:i:s');

            // --- ÇAKIŞMA KORUMASI ---
            $conflict = Appointment::where('employee_id', $data['employee_id'])
                ->whereNotIn('status', [
                    AppointmentStatus::Cancelled->value,
                    AppointmentStatus::Rejected->value,
                    AppointmentStatus::NoShow->value,
                ])
                ->where(function ($query) use ($newStartStr, $newEndStr) {
                    $query->where('start_at', '<', $newEndStr)
                          ->where(function ($sub) use ($newStartStr) {
                              $sub->where('end_at', '>', $newStartStr)
                                  ->orWhere(function ($fallback) use ($newStartStr) {
                                      $fallback->whereNull('end_at')
                                               ->whereRaw('DATE_ADD(start_at, INTERVAL COALESCE(total_duration, 30) MINUTE) > ?', [$newStartStr]);
                                  });
                          });
                })
                ->lockForUpdate() // Pessimistic lock: aynı anda gelen diğer istek bekler
                ->exists();

            if ($conflict) {
                throw new \RuntimeException('Seçilen saat veya süre aralığında berber dolu. Lütfen farklı bir saat veya berber seçiniz.');
            }

            // Personel İzin/Blok Çakışma Koruması
            $timeBlockConflict = EmployeeTimeBlock::where('employee_id', $data['employee_id'])
                ->whereDate('date', $newStart->toDateString())
                ->where(function ($q) use ($newStartStr, $newEndStr) {
                    $q->where('type', 'full_day')
                      ->orWhere(function ($sub) use ($newStartStr, $newEndStr) {
                          $sub->where('type', 'partial_time')
                              ->whereRaw('CONCAT(date, " ", start_time) < ?', [$newEndStr])
                              ->whereRaw('CONCAT(date, " ", end_time) > ?', [$newStartStr]);
                      });
                })
                ->exists();

            if ($timeBlockConflict) {
                throw new \RuntimeException('Berber seçilen saat veya tarih aralığında izinli/müsait değil.');
            }
            // --- ÇAKIŞMA KORUMASI SONU ---

            $appointment = $this->appointmentRepo->create($data);

            foreach ($enrichedServices as $service) {
                AppointmentServiceModel::create([
                    'appointment_id'   => $appointment->id,
                    'service_id'       => $service['service_id'],
                    'employee_id'      => $data['employee_id'],
                    'quantity'         => $service['quantity'],
                    'duration_minutes' => $service['duration_minutes'],
                    'unit_price'       => $service['unit_price'],
                    'total_price'      => $service['total_price'],
                ]);
            }

            // Kupon kullanıldıysa kaydını düş
            if ($appliedCoupon && $customer) {
                $this->campaignService->recordCouponUsage($appliedCoupon, $customer, $appointment->id);
            }

            // Kampanya kullanıldıysa kaydını düş
            if ($campaignId && $customer) {
                $campaign = \App\Models\Campaign::find($campaignId);
                if ($campaign) {
                    $this->campaignService->recordCampaignUsage($campaign, $customer, $appointment->id);
                }
            }

            $this->logStatusChange($appointment, null, $appointment->status->value);

            return $appointment->load(['customer', 'employee.user', 'appointmentServices.service']);
        });
    }

    public function updateStatus(Appointment $appointment, AppointmentStatus $status, ?int $userId = null, ?string $note = null, ?string $cancellationReason = null): Appointment
    {
        $oldStatus = $appointment->status->value;

        $updateData = ['status' => $status];

        if ($status === AppointmentStatus::Completed) {
            $updateData['completed_at'] = now();
            
            // Sadakat Puanı Kazanımı (1 TL = 1 Puan)
            if ($oldStatus !== AppointmentStatus::Completed->value && $appointment->customer_id) {
                try {
                    $pointsToEarn = (int) $appointment->total_price;
                    if ($pointsToEarn > 0) {
                        app(\App\Services\LoyaltyService::class)->earnPoints(
                            $appointment->customer_id, 
                            $pointsToEarn, 
                            "Randevu Tamamlandı (#{$appointment->appointment_code})"
                        );
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Sadakat puanı eklenemedi: ' . $e->getMessage());
                }
            }
        }

        if ($status === AppointmentStatus::Cancelled || $status === AppointmentStatus::Rejected) {
            $updateData['cancelled_at'] = now();
            $updateData['cancelled_by'] = $userId;
            if ($cancellationReason) {
                $updateData['cancellation_reason'] = $cancellationReason;
            }

            // Tamamlanmış bir randevu iptal ediliyorsa kazanılan puanları geri al
            if ($oldStatus === AppointmentStatus::Completed->value && $appointment->customer_id) {
                try {
                    $pointsToRevoke = (int) $appointment->total_price;
                    if ($pointsToRevoke > 0) {
                        app(\App\Services\LoyaltyService::class)->revokePoints(
                            $appointment->customer_id,
                            $pointsToRevoke,
                            "Randevu İptal Edildi (#{$appointment->appointment_code})"
                        );
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Sadakat puanı geri alınamadı: ' . $e->getMessage());
                }
            }
        }

        if ($status === AppointmentStatus::NoShow) {
            $updateData['no_show'] = true;
        }

        $appointment->update($updateData);

        $this->logStatusChange($appointment, $oldStatus, $status->value, $userId, $note);

        return $appointment->fresh();
    }

    private function logStatusChange(Appointment $appointment, ?string $old, string $new, ?int $userId = null, ?string $note = null): void
    {
        AppointmentStatusLog::create([
            'appointment_id' => $appointment->id,
            'changed_by' => $userId,
            'old_status' => $old,
            'new_status' => $new,
            'note' => $note,
        ]);
    }

    private function generateCode(): string
    {
        do {
            $code = 'BV-' . strtoupper(Str::random(8));
        } while (Appointment::where('appointment_code', $code)->exists());

        return $code;
    }

    public function getAvailableSlots(int $branchId, int $employeeId, string $date, int $duration = 30): array
    {
        $employee = Employee::find($employeeId);
        if (! $employee) {
            return [];
        }

        // İşe başlama tarihinden önce veya ayrılma tarihinden sonra randevu alınamaz
        $dateCarbon = Carbon::parse($date);
        if ($employee->hire_date && $dateCarbon->copy()->startOfDay()->lt($employee->hire_date->startOfDay())) {
            return [];
        }
        if ($employee->leave_date && $dateCarbon->copy()->startOfDay()->gt($employee->leave_date->startOfDay())) {
            return [];
        }

        // Tüm gün izinli veya bloklu ise randevu alınamaz
        $timeBlocks = EmployeeTimeBlock::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->get();

        if ($timeBlocks->where('type', 'full_day')->count() > 0) {
            return [];
        }

        // Şube çalışma saatleri
        $branch = Branch::find($branchId);
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

        // O günkü tüm aktif randevular (tüm müşteriler dahil)
        $appointments = Appointment::forBranch($branchId)
            ->where('employee_id', $employeeId)
            ->whereDate('start_at', $date)
            ->whereNotIn('status', [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::Rejected->value,
                AppointmentStatus::NoShow->value,
            ])
            ->get();

        $slots = [];
        $startTime = Carbon::parse($date . ' ' . $openTimeStr, config('app.timezone', 'Europe/Istanbul'));
        $endTime = Carbon::parse($date . ' ' . $closeTimeStr, config('app.timezone', 'Europe/Istanbul'));
        $now = Carbon::now(config('app.timezone', 'Europe/Istanbul'));
        $isToday = $date === $now->toDateString();

        while ($startTime < $endTime) {
            $slotTime = $startTime->copy();
            $isAvailable = true;
            $isBusy = false;
            $isPast = false;

            // Randevu çakışması kontrolü
            foreach ($appointments as $apt) {
                $aptStart = Carbon::parse($apt->start_at, config('app.timezone', 'Europe/Istanbul'));
                $aptEnd = $apt->end_at 
                    ? Carbon::parse($apt->end_at, config('app.timezone', 'Europe/Istanbul')) 
                    : $aptStart->copy()->addMinutes($apt->total_duration ?? 30);
                
                // Eğer slot randevu aralığına denk geliyorsa
                if ($slotTime >= $aptStart && $slotTime < $aptEnd) {
                    $isAvailable = false;
                    $isBusy = true;
                    break;
                }
            }

            // Saatlik izin/bloklama çakışması kontrolü
            if ($isAvailable) {
                foreach ($timeBlocks as $block) {
                    if ($block->type === 'partial_time') {
                        $blockStart = Carbon::parse($date . ' ' . $block->start_time, config('app.timezone', 'Europe/Istanbul'));
                        $blockEnd = Carbon::parse($date . ' ' . $block->end_time, config('app.timezone', 'Europe/Istanbul'));
                        
                        if ($slotTime >= $blockStart && $slotTime < $blockEnd) {
                            $isAvailable = false;
                            $isBusy = true;
                            break;
                        }
                    }
                }
            }

            // Geçmiş saat kontrolü (Bugün için)
            if ($isToday && $slotTime <= $now) {
                $isAvailable = false;
                $isPast = true;
            }

            $slots[] = [
                'time' => $slotTime->format('H:i'),
                'is_available' => $isAvailable,
                'is_busy' => $isBusy,
                'is_past' => $isPast,
            ];

            $startTime->addMinutes(30);
        }

        // İstenen süre (duration) 30 dakikadan fazlaysa ardışık slot kontrolü
        $requiredSlotsCount = max(1, (int) ceil($duration / 30));

        if ($requiredSlotsCount > 1) {
            foreach ($slots as $index => &$slot) {
                if (! $slot['is_available']) {
                    continue;
                }

                $consecutiveAvailable = true;
                for ($i = 1; $i < $requiredSlotsCount; $i++) {
                    if (! isset($slots[$index + $i]) || ! $slots[$index + $i]['is_available']) {
                        $consecutiveAvailable = false;
                        break;
                    }
                }

                if (! $consecutiveAvailable) {
                    $slot['is_available'] = false;
                }
            }
            unset($slot);
        }

        return $slots;
    }
}
