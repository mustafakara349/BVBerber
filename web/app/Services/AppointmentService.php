<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Exceptions\AppointmentConflictException;
use App\Models\Appointment;
use App\Models\AppointmentService as AppointmentServiceModel;
use App\Models\AppointmentStatusLog;
use App\Models\Branch;
use App\Models\BranchSetting;
use App\Models\Employee;
use App\Models\EmployeeTimeBlock;
use App\Models\User;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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
        $employeeId = (int) $data['employee_id'];
        $dateStr = Carbon::parse($data['start_at'])->toDateString();
        $lockKey = "appointment_booking:employee:{$employeeId}:{$dateStr}";
        $lock = Cache::lock($lockKey, 10);

        return $lock->block(5, function () use ($data, $employeeId) {
            return DB::transaction(function () use ($data, $employeeId) {
                // 1) Eşzamanlılık Koruması: Berber satırı üzerinde Pessimistic Lock (InnoDB Exclusive Row Lock)
                Employee::where('id', $employeeId)->lockForUpdate()->first();

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
                    $unitPrice       = (float) ($dbService->discounted_price ?? $dbService->price);
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

                $data['end_at']         = Carbon::parse($data['start_at'])->addMinutes($totalDuration);
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
                $conflict = Appointment::where('employee_id', $employeeId)
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
                    throw new AppointmentConflictException('Seçilen saat veya süre aralığında berber dolu. Lütfen farklı bir saat veya berber seçiniz.');
                }

                // Personel İzin/Blok Çakışma Koruması
                $timeBlockConflict = EmployeeTimeBlock::where('employee_id', $employeeId)
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
                    throw new AppointmentConflictException('Berber seçilen saat veya tarih aralığında izinli/müsait değil.');
                }

                // Personel Onaylı Resmi İzin Koruması (EmployeeLeave)
                $leaveConflict = \App\Models\EmployeeLeave::where('employee_id', $employeeId)
                    ->where('approval_status', \App\Enums\ApprovalStatus::Approved)
                    ->whereDate('start_date', '<=', $newStart->toDateString())
                    ->whereDate('end_date', '>=', $newStart->toDateString())
                    ->exists();

                if ($leaveConflict) {
                    throw new AppointmentConflictException('Berber seçilen tarihte onaylı izinlidir.');
                }

                // Personel Çalışma Çizelgesi ve Mola Koruması (EmployeeSchedule)
                $empSchedule = \App\Models\EmployeeSchedule::where('employee_id', $employeeId)
                    ->whereDate('work_date', $newStart->toDateString())
                    ->first();

                if ($empSchedule) {
                    if ($empSchedule->is_day_off) {
                        throw new AppointmentConflictException('Berber seçilen tarihte haftalık izinlidir.');
                    }
                    if ($empSchedule->break_start && $empSchedule->break_end) {
                        $breakStartStr = $newStart->toDateString() . ' ' . (strlen($empSchedule->break_start) === 5 ? $empSchedule->break_start . ':00' : $empSchedule->break_start);
                        $breakEndStr = $newStart->toDateString() . ' ' . (strlen($empSchedule->break_end) === 5 ? $empSchedule->break_end . ':00' : $empSchedule->break_end);
                        if ($newStartStr < $breakEndStr && $newEndStr > $breakStartStr) {
                            throw new AppointmentConflictException('Seçilen randevu saati berberin mola / dinlenme saatine denk gelmektedir.');
                        }
                    }
                    if ($empSchedule->start_time && $empSchedule->end_time) {
                        $workStartStr = $newStart->toDateString() . ' ' . (strlen($empSchedule->start_time) === 5 ? $empSchedule->start_time . ':00' : $empSchedule->start_time);
                        $workEndStr = $newStart->toDateString() . ' ' . (strlen($empSchedule->end_time) === 5 ? $empSchedule->end_time . ':00' : $empSchedule->end_time);
                        if ($newStartStr < $workStartStr || $newEndStr > $workEndStr) {
                            throw new AppointmentConflictException('Seçilen randevu saati berberin çalışma saatleri dışındadır.');
                        }
                    }
                }

                // Şube Çalışma Saatleri Koruması (Açılış/Kapanış)
                $branchId = $data['branch_id'] ?? session('active_branch_id', 1);
                $branch = Branch::find($branchId);
                if ($branch && $branch->opening_time && $branch->closing_time) {
                    $openTimeVal = $branch->opening_time instanceof \DateTimeInterface ? $branch->opening_time->format('H:i:s') : (strlen($branch->opening_time) === 5 ? $branch->opening_time . ':00' : (string) $branch->opening_time);
                    $closeTimeVal = $branch->closing_time instanceof \DateTimeInterface ? $branch->closing_time->format('H:i:s') : (strlen($branch->closing_time) === 5 ? $branch->closing_time . ':00' : (string) $branch->closing_time);
                    $branchOpenStr = $newStart->toDateString() . ' ' . $openTimeVal;
                    $branchCloseStr = $newStart->toDateString() . ' ' . $closeTimeVal;
                    if ($newStartStr < $branchOpenStr || $newEndStr > $branchCloseStr) {
                        throw new AppointmentConflictException('Seçilen randevu şubenin çalışma saatleri (açılış/kapanış) dışındadır.');
                    }
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
        });
    }

    public function updateStatus(Appointment $appointment, AppointmentStatus $status, ?int $userId = null, ?string $note = null, ?string $cancellationReason = null): Appointment
    {
        if ($status === AppointmentStatus::Cancelled) {
            $this->validateCustomerCancellation($appointment, $userId);
        }

        $oldStatus = $appointment->status->value;

        $updateData = ['status' => $status];

        if ($status === AppointmentStatus::Completed) {
            $updateData['completed_at'] = now();
            
            // Sadakat Puanı Kazanımı (1 ₺ harcama = 1 Puan | 1.000 Puan = 1 ₺)
            if ($oldStatus !== AppointmentStatus::Completed->value && $appointment->customer_id) {
                try {
                    $earnRate = (float) config('bv.loyalty.earn_rate', 1);
                    $pointsToEarn = (int) round($appointment->total_price * $earnRate);
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

            // Tamamlanmış bir randevu iptal ediliyorsa kazanılan puanları geri al (1 ₺ = 1 Puan)
            if ($oldStatus === AppointmentStatus::Completed->value && $appointment->customer_id) {
                try {
                    $earnRate = (float) config('bv.loyalty.earn_rate', 1);
                    $pointsToRevoke = (int) round($appointment->total_price * $earnRate);
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

    /**
     * Validate whether an appointment can be cancelled by a customer.
     * Business staff (super_admin, owner, manager, receptionist, barber) are exempt.
     *
     * @throws \InvalidArgumentException
     */
    public function validateCustomerCancellation(Appointment $appointment, ?int $userId = null): void
    {
        $user = $userId ? User::find($userId) : (auth()->check() ? auth()->user() : null);

        // İşletme personeli (admin, super_admin, owner, manager, receptionist, barber) iptal sınırı kuralından muaftır
        if ($user && ! $user->isCustomer() && $user->role?->slug !== 'customer') {
            return;
        }

        // 1) Statü Kontrolü: Yalnızca beklemede veya onaylı randevular iptal edilebilir
        if (! in_array($appointment->status, [AppointmentStatus::Pending, AppointmentStatus::Confirmed])) {
            throw new \InvalidArgumentException('Yalnızca beklemede veya onaylanmış randevular iptal edilebilir.');
        }

        $timezone = config('app.timezone', 'Europe/Istanbul');
        $now = now()->setTimezone($timezone);
        $startAt = $appointment->start_at->copy()->setTimezone($timezone);

        // 2) Geçmiş Randevu Kontrolü
        if ($startAt->isPast()) {
            throw new \InvalidArgumentException('Geçmiş randevular iptal edilemez.');
        }

        // 3) Şube İptal Limiti Kontrolü
        $branchSetting = $appointment->branch?->settings
            ?? BranchSetting::where('branch_id', $appointment->branch_id)->first();

        $limitHours = (int) ($branchSetting?->cancellation_limit_hours ?? config('bv.appointment.cancellation_limit_hours', 2));

        if ($limitHours > 0) {
            $deadline = $startAt->copy()->subHours($limitHours);
            if ($now->greaterThanOrEqualTo($deadline)) {
                throw new \InvalidArgumentException(
                    "Randevunuza {$limitHours} saatten az kaldığı için uygulama üzerinden iptal edilemez. Lütfen işletmemiz ile telefonla iletişime geçiniz."
                );
            }
        }
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

        // Onaylı resmi izin kontrolü (EmployeeLeave)
        $hasApprovedLeave = \App\Models\EmployeeLeave::where('employee_id', $employeeId)
            ->where('approval_status', \App\Enums\ApprovalStatus::Approved)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();

        if ($hasApprovedLeave) {
            return [];
        }

        // Personel çalışma çizelgesi kontrolü (EmployeeSchedule)
        $schedule = \App\Models\EmployeeSchedule::where('employee_id', $employeeId)
            ->whereDate('work_date', $date)
            ->first();

        if ($schedule && $schedule->is_day_off) {
            return [];
        }

        // Şube çalışma saatleri
        $branch = Branch::find($branchId);
        $openTimeStr = '09:00:00';
        $closeTimeStr = '22:00:00';

        if ($branch) {
            if ($branch->opening_time) {
                $openTimeStr = $branch->opening_time instanceof \DateTimeInterface 
                    ? $branch->opening_time->format('H:i:s') 
                    : (strlen($branch->opening_time) === 5 ? $branch->opening_time . ':00' : (string) $branch->opening_time);
            }
            if ($branch->closing_time) {
                $closeTimeStr = $branch->closing_time instanceof \DateTimeInterface 
                    ? $branch->closing_time->format('H:i:s') 
                    : (strlen($branch->closing_time) === 5 ? $branch->closing_time . ':00' : (string) $branch->closing_time);
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

            // Personel mola saati kontrolü (EmployeeSchedule break_start - break_end)
            if ($isAvailable && $schedule && $schedule->break_start && $schedule->break_end) {
                $breakStart = Carbon::parse($date . ' ' . $schedule->break_start, config('app.timezone', 'Europe/Istanbul'));
                $breakEnd = Carbon::parse($date . ' ' . $schedule->break_end, config('app.timezone', 'Europe/Istanbul'));
                if ($slotTime >= $breakStart && $slotTime < $breakEnd) {
                    $isAvailable = false;
                    $isBusy = true;
                }
            }

            // Personel özel mesai saati kısıtı (EmployeeSchedule start_time - end_time)
            if ($isAvailable && $schedule && $schedule->start_time && $schedule->end_time) {
                $workStart = Carbon::parse($date . ' ' . $schedule->start_time, config('app.timezone', 'Europe/Istanbul'));
                $workEnd = Carbon::parse($date . ' ' . $schedule->end_time, config('app.timezone', 'Europe/Istanbul'));
                if ($slotTime < $workStart || $slotTime >= $workEnd) {
                    $isAvailable = false;
                    $isBusy = true;
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
