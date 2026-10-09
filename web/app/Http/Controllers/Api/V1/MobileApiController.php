<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Traits\MobileAuthTrait;
use App\Traits\MobileDocumentTrait;
use App\Traits\MobileQueryTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileApiController extends Controller
{
    use ApiResponse, MobileAuthTrait, MobileDocumentTrait, MobileQueryTrait;

    public function validateCoupon(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        $data = $request->validate([
            'couponCode' => 'nullable|string',
            'campaignId' => 'nullable|string',
            'serviceIds' => 'required|array',
            'subtotal'   => 'required|numeric'
        ]);

        if (empty($data['couponCode']) && empty($data['campaignId'])) {
            return $this->error('Kupon kodu veya kampanya seçimi gerekli.', 400);
        }

        try {
            $campaignService = app(\App\Services\CampaignService::class);
            
            if (!empty($data['couponCode'])) {
                $result = $campaignService->validateCoupon($user, 1, $data['couponCode'], $data['subtotal'], $data['serviceIds']);
                $successMessage = 'Kupon başarıyla uygulandı.';
                $errorMessage = 'Geçersiz kupon.';
            } else {
                $result = $campaignService->validateCampaign($user, 1, $data['campaignId'], $data['subtotal'], $data['serviceIds']);
                $successMessage = 'Kampanya başarıyla uygulandı.';
                $errorMessage = 'Geçersiz kampanya.';
            }

            if ($result['valid']) {
                $responseData = [
                    'isValid' => true,
                    'discountAmount' => $result['discount_amount'],
                    'message' => $successMessage
                ];

                if (isset($result['campaign'])) {
                    $campaign = $result['campaign'];
                    $responseData['rewardType'] = $campaign->reward_type;
                    
                    if ($campaign->reward_type === 'gift_product' && $campaign->reward_product_id) {
                        $responseData['rewardProductId'] = (string) $campaign->reward_product_id;
                        $prod = \App\Models\Product::find($campaign->reward_product_id);
                        if($prod) $responseData['rewardProductName'] = $prod->name;
                    } elseif ($campaign->reward_type === 'gift_cafe' && $campaign->reward_cafe_product_id) {
                        $responseData['rewardCafeProductId'] = (string) $campaign->reward_cafe_product_id;
                        $cprod = \App\Models\CafeProduct::find($campaign->reward_cafe_product_id);
                        if($cprod) $responseData['rewardProductName'] = $cprod->name;
                    }
                }

                return $this->success($responseData);
            }

            return $this->error($result['message'] ?? $errorMessage, 400);

        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->error('Doğrulama sırasında bir hata oluştu.', 500);
        }
    }

    /**
     * Generate secure iCal (.ics) calendar file.
     */
    public function generateIcal(\App\Models\Appointment $appointment, Request $request)
    {
        // Simple security signature check to prevent ID enumeration
        $signature = hash_hmac('sha256', $appointment->id, config('app.key'));
        if ($request->query('signature') !== $signature) {
            abort(403, 'Yetkisiz erişim. Geçersiz takvim imzası.');
        }

        $appointment->load(['employee.user', 'appointmentServices.service']);

        $start = $appointment->start_at->utc()->format('Ymd\THis\Z');
        $duration = $appointment->appointmentServices->sum(function($as) {
            return $as->service->duration_minutes ?? 30;
        }) ?: 30;
        $end = $appointment->start_at->copy()->addMinutes($duration)->utc()->format('Ymd\THis\Z');
        
        $summary = "B&V Barber Randevusu - " . ($appointment->employee->full_name ?? 'Berber');
        $description = "Hizmet: " . ($appointment->appointmentServices->first()?->service->name ?? 'Berberlik Hizmeti');
        $location = "B&V Barber & Coffee";

        $ical = "BEGIN:VCALENDAR\r\n" .
                "VERSION:2.0\r\n" .
                "PRODID:-//BVBarber//NONSGML Calendar//EN\r\n" .
                "CALSCALE:GREGORIAN\r\n" .
                "BEGIN:VEVENT\r\n" .
                "UID:appointment-" . $appointment->id . "@bvbarber.com\r\n" .
                "DTSTAMP:" . now()->utc()->format('Ymd\THis\Z') . "\r\n" .
                "DTSTART:" . $start . "\r\n" .
                "DTEND:" . $end . "\r\n" .
                "SUMMARY:" . $summary . "\r\n" .
                "DESCRIPTION:" . $description . "\r\n" .
                "LOCATION:" . $location . "\r\n" .
                "END:VEVENT\r\n" .
                "END:VCALENDAR";

        return response($ical)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="randevu-' . $appointment->id . '.ics"');
    }

    /**
     * Create review and rating for a completed appointment.
     */
    public function createReview(Request $request): JsonResponse
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $appointment = \App\Models\Appointment::findOrFail($request->appointment_id);

        if ($appointment->customer_id != $user->id) {
            return $this->error('Bu randevu size ait değil.', 403);
        }

        $allowedStatuses = [
            \App\Enums\AppointmentStatus::Completed,
            \App\Enums\AppointmentStatus::Confirmed,
        ];

        if (!in_array($appointment->status, $allowedStatuses)) {
            return $this->error('Sadece tamamlanmış randevular için yorum yapabilirsiniz.', 400);
        }

        if ($appointment->status === \App\Enums\AppointmentStatus::Confirmed && $appointment->start_at->isFuture()) {
            return $this->error('Henüz gerçekleşmemiş randevular için yorum yapamazsınız.', 400);
        }

        if ($appointment->review()->exists()) {
            return $this->error('Bu randevu için zaten yorum yapılmış.', 400);
        }

        $review = \App\Models\Review::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $user->id,
            'employee_id' => $appointment->employee_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return $this->success($review, 'Yorumunuz başarıyla kaydedildi.');
    }
}
