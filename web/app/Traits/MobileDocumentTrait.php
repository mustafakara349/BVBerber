<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

trait MobileDocumentTrait
{
    /**
     * Get specific document emulation.
     */
    public function showDocument(string $collection, string $id, Request $request): JsonResponse
    {
        $user = auth('sanctum')->user() ?? $request->user();

        if ($collection === 'store' && $id === 'main') {
            $branch = \App\Models\Branch::with('settings')->first();
            if (!$branch) {
                return $this->error('Store not found', 404);
            }

            $workingHours = [
                "monday" => ["open" => "09:00", "close" => "22:00", "closed" => false],
                "tuesday" => ["open" => "09:00", "close" => "22:00", "closed" => false],
                "wednesday" => ["open" => "09:00", "close" => "22:00", "closed" => false],
                "thursday" => ["open" => "09:00", "close" => "22:00", "closed" => false],
                "friday" => ["open" => "09:00", "close" => "22:00", "closed" => false],
                "saturday" => ["open" => "09:00", "close" => "22:00", "closed" => false],
                "sunday" => ["open" => null, "close" => null, "closed" => true]
            ];

            return $this->success([
                'name' => $branch->name,
                'description' => 'B&V Premium Barber & Coffee',
                'phone' => $branch->phone ?? '',
                'address' => $branch->address ?? '',
                'isActive' => (bool)$branch->is_active,
                'settings' => [
                    'allowCancellation' => true,
                    'appointmentInterval' => $branch->settings->appointment_interval ?? 30,
                    'autoApproveAppointments' => true,
                    'bufferBetweenAppointments' => 0,
                    'cancellationLimitHours' => $branch->settings->cancellation_limit_hours ?? 2,
                    'maxBookingDaysAhead' => 30,
                    'maxDailyAppointmentsPerBarber' => 12,
                ],
                'workingHours' => $workingHours,
            ]);
        }

        if ($collection === 'users') {
            if ($id === 'me' && $user) {
                $id = $user->id;
            }
            if (!$user || $user->id != $id) {
                return $this->error('Unauthorized', 403);
            }

            return $this->success([
                'id' => (string)$user->id,
                'email' => $user->email,
                'isActive' => $user->status->value === 'active',
                'name' => $user->first_name,
                'surname' => $user->last_name,
                'phone' => $user->phone ?? '',
                'profileImageUrl' => $user->profile_photo ? $request->schemeAndHttpHost() . '/storage/' . $user->profile_photo : '',
                'role' => 'customer',
            ]);
        }

        return $this->error('Document not found', 404);
    }

    /**
     * Add generic document emulation.
     */
    public function addDocument(string $collection, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        if ($collection === 'appointments') {
            $data = $request->validate([
                'barberId'     => 'required|integer|exists:employees,id',
                'serviceId'    => 'nullable|integer|exists:services,id',
                'serviceIds'   => 'nullable|array|min:1',
                'serviceIds.*' => 'integer|exists:services,id',
                'date'         => 'required|date_format:Y-m-d|after_or_equal:today',
                'time'         => 'required|date_format:H:i',
                'couponCode'   => 'nullable|string|max:50',
                'campaignId'   => 'nullable|integer',
            ]);

            if (!empty($data['couponCode']) && !empty($data['campaignId'])) {
                return $this->error('Kupon kodu ve kampanya aynı anda kullanılamaz.', 422);
            }

            if (empty($data['serviceIds']) && empty($data['serviceId'])) {
                return $this->error('En az bir hizmet seçilmelidir.', 422);
            }

            $servicesData = [];
            if (!empty($data['serviceIds'])) {
                foreach ($data['serviceIds'] as $sId) {
                    $servicesData[] = [
                        'service_id' => (int)$sId,
                        'quantity' => 1,
                    ];
                }
            } elseif (!empty($data['serviceId'])) {
                $servicesData[] = [
                    'service_id' => (int)$data['serviceId'],
                    'quantity' => 1,
                ];
            }

            $appointmentData = [
                'branch_id' => 1,
                'customer_id' => $user->id,
                'employee_id' => (int)$data['barberId'],
                'start_at' => Carbon::parse($data['date'] . ' ' . $data['time']),
                'source' => \App\Enums\AppointmentSource::MobileApp,
                'services' => $servicesData,
                'coupon_code' => $data['couponCode'] ?? null,
                'campaign_id' => $data['campaignId'] ?? null,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ];

            try {
                $appointmentService = app(\App\Services\AppointmentService::class);
                $appt = $appointmentService->createAppointment($appointmentData);

                return $this->success([
                    'id' => (string)$appt->id
                ], 'Appointment created');
            } catch (\Exception $e) {
                return $this->error($e->getMessage(), 400);
            }
        }

        return $this->error('Action not supported', 400);
    }

    /**
     * Update generic document emulation.
     */
    public function updateDocument(string $collection, string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        if ($collection === 'appointments') {
            $appt = \App\Models\Appointment::findOrFail($id);
            if ($appt->customer_id != $user->id) {
                return $this->error('Unauthorized', 403);
            }

            $status = $request->input('status');
            if ($status === 'cancelled') {
                try {
                    $appointmentService = app(\App\Services\AppointmentService::class);
                    $appointmentService->updateStatus($appt, \App\Enums\AppointmentStatus::Cancelled, $user->id, 'Cancelled via Mobile App');
                    return $this->success(null, 'Appointment cancelled');
                } catch (\Exception $e) {
                    return $this->error($e->getMessage(), 400);
                }
            }
        }

        return $this->error('Action not supported', 400);
    }

    /**
     * Delete generic document emulation.
     */
    public function deleteDocument(string $collection, string $id, Request $request): JsonResponse
    {
        return $this->error('Action not supported', 400);
    }
}
