<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Exceptions\AppointmentConflictException;
use App\Models\Appointment;
use App\Models\AppointmentService as AppointmentServiceModel;
use App\Models\AppointmentStatusLog;
use App\Models\Employee;
use App\Models\EmployeeTimeBlock;
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

                $startAt = Carbon::parse($data['start_at']);
                $endAt   = Carbon::parse($data['end_at']);

                // --- ÇAKIŞMA KORUMASI (Zaman Aralığı Overlap Kontrolü) ---
                $conflict = Appointment::where('employee_id', $employeeId)
                    ->whereIn('status', [
                        AppointmentStatus::Pending->value,
                        AppointmentStatus::Confirmed->value,
                        AppointmentStatus::InProgress->value,
                    ])
                    ->where(function ($query) use ($startAt, $endAt) {
                        // [A, B) ile [C, D) çakışması: A < D AND B > C
                        $query->where('start_at', '<', $endAt)
                              ->where('end_at', '>', $startAt);
                    })
                    ->exists();

                if ($conflict) {
                    throw new AppointmentConflictException('Seçilen saat aralığı dolu veya başka bir müşteri tarafından yeni alındı. Lütfen farklı bir saat veya berber seçiniz.');
                }

                // İzin / Saatlik Blok Kontrolü
                $timeBlockConflict = EmployeeTimeBlock::where('employee_id', $employeeId)
                    ->where('date', $startAt->toDateString())
                    ->where(function ($query) use ($startAt, $endAt) {
                        $query->where('type', 'full_day')
                              ->orWhere(function ($q) use ($startAt, $endAt) {
                                  $q->where('type', 'partial_time')
                                    ->whereRaw('? < STR_TO_DATE(CONCAT(date, " ", end_time), "%Y-%m-%d %H:%i:%s")', [$startAt->toDateTimeString()])
                                    ->whereRaw('? > STR_TO_DATE(CONCAT(date, " ", start_time), "%Y-%m-%d %H:%i:%s")', [$endAt->toDateTimeString()]);
                              });
                    })
                    ->exists();

                if ($timeBlockConflict) {
                    throw new AppointmentConflictException('Berber bu saat aralığında izinlidir veya moladadır.');
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
}
