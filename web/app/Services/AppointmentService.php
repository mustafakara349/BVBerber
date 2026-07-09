<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentService as AppointmentServiceModel;
use App\Models\AppointmentStatusLog;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
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
                } else {
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

            $this->logStatusChange($appointment, null, AppointmentStatus::Pending->value);

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
