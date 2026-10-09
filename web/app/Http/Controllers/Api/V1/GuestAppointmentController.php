<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AppointmentService;
use App\Services\GuestCustomerService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GuestAppointmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private GuestCustomerService $guestService,
        private AppointmentService $appointmentService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName'    => 'required|string|max:50',
            'lastName'     => 'required|string|max:50',
            'phone'        => 'required|string|min:10|max:15',
            'barberId'     => 'required|integer|exists:employees,id',
            'serviceIds'   => 'required|array|min:1',
            'serviceIds.*' => 'integer|exists:services,id',
            'date'         => 'required|date_format:Y-m-d|after_or_equal:today',
            'time'         => 'required|date_format:H:i',
        ]);

        try {
            DB::beginTransaction();

            // Misafir (Gölge) Kullanıcı Bul veya Oluştur
            $user = $this->guestService->findOrCreateGuestUser(
                $data['firstName'],
                $data['lastName'],
                $data['phone']
            );

            // Hizmetleri formatla
            $servicesData = [];
            foreach ($data['serviceIds'] as $sId) {
                $servicesData[] = [
                    'service_id' => (int)$sId,
                    'quantity' => 1,
                ];
            }

            // Randevu verisini hazırla
            $appointmentData = [
                'branch_id' => 1,
                'customer_id' => $user->id,
                'employee_id' => (int)$data['barberId'],
                'start_at' => Carbon::parse($data['date'] . ' ' . $data['time']),
                'source' => \App\Enums\AppointmentSource::MobileApp,
                'services' => $servicesData,
                // Misafir kullanıcılarda kampanya ve kupon KESİNLİKLE null olacak
                'coupon_code' => null,
                'campaign_id' => null,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'status' => \App\Enums\AppointmentStatus::Pending,
            ];

            $appt = $this->appointmentService->createAppointment($appointmentData);

            DB::commit();

            return $this->success([
                'id' => (string)$appt->id
            ], 'Appointment created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 400);
        }
    }
}
