<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MobileApiController extends Controller
{
    use ApiResponse;

    /**
     * Mobile login endpoint.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $authService = app(\App\Services\AuthService::class);
        try {
            $result = $authService->apiLogin($request->only('email', 'password'));

            return $this->success([
                'user' => [
                    'id' => (string)$result['user']->id,
                    'email' => $result['user']->email,
                    'isActive' => $result['user']->status->value === 'active',
                    'name' => $result['user']->first_name,
                    'surname' => $result['user']->last_name,
                    'phone' => $result['user']->phone ?? '',
                    'profileImageUrl' => $result['user']->profile_photo ? $request->schemeAndHttpHost() . '/storage/' . $result['user']->profile_photo : '',
                    'role' => 'customer',
                ],
                'token' => $result['token'],
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 401);
        }
    }

    /**
     * Mobile registration endpoint.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $customerRole = \App\Models\Role::where('slug', 'customer')->first();
        if (!$customerRole) {
            return $this->error('Customer role not found', 500);
        }

        $user = \App\Models\User::create([
            'role_id' => $customerRole->id,
            'first_name' => $request->name,
            'last_name' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => \App\Enums\UserStatus::Active,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => [
                'id' => (string)$user->id,
                'email' => $user->email,
                'isActive' => true,
                'name' => $user->first_name,
                'surname' => $user->last_name,
                'phone' => $user->phone ?? '',
                'profileImageUrl' => '',
                'role' => 'customer',
            ],
            'token' => $token,
        ], 'Registration successful', 201);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'phone' => 'required|string',
        ]);

        $user->update([
            'first_name' => $data['name'],
            'last_name' => $data['surname'],
            'phone' => $data['phone'],
        ]);

        return $this->success(null, 'Profile updated');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:6',
        ]);

        if (!Hash::check($request->currentPassword, $user->password)) {
            return $this->error('Mevcut şifre hatalı.', 400);
        }

        $user->update([
            'password' => Hash::make($request->newPassword)
        ]);

        return $this->success(null, 'Password updated');
    }

    /**
     * Upload profile photo.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$request->hasFile('photo')) {
            return $this->error('Photo file required', 400);
        }

        $path = $request->file('photo')->store('profile_photos', 'public');
        
        $user->update([
            'profile_photo' => $path
        ]);

        return $this->success([
            'profileImageUrl' => $request->schemeAndHttpHost() . '/storage/' . $path
        ], 'Photo uploaded successfully');
    }

    /**
     * Save FCM registration token.
     */
    public function saveToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $request->input('fcmToken');

        if ($token) {
            $user->devices()->updateOrCreate(
                ['push_token' => $token],
                [
                    'device_type' => \App\Enums\DeviceType::Ios,
                    'last_active_at' => now(),
                ]
            );
            return $this->success(null, 'Token saved');
        }

        return $this->error('Token required', 400);
    }

    /**
     * Firestore generic query emulation layer.
     */
    public function query(Request $request): JsonResponse
    {
        $collection = $request->input('collection') ?? $request->route('collection');
        $where = $request->input('where', []);
        $orderBy = $request->input('orderBy');
        $descending = $request->input('descending', true);

        $user = auth('sanctum')->user() ?? $request->user();

        switch ($collection) {
            case 'services':
                $query = \App\Models\Service::query()->active();
                $services = $query->with('category')->get()->map(function ($service) use ($request) {
                    return [
                        'id' => (string)$service->id,
                        'name' => $service->name,
                        'description' => $service->description ?? '',
                        'category' => $service->category->name ?? 'Diğer',
                        'duration' => (int)$service->duration_minutes,
                        'price' => (int)$service->price,
                        'discountedPrice' => $service->discounted_price ? (int)$service->discounted_price : null,
                        'imageUrl' => $service->image ? $request->schemeAndHttpHost() . '/storage/' . $service->image : '',
                        'isActive' => (bool)$service->is_active,
                        'genderType' => $service->gender_type,
                        'isPopular' => (bool)$service->is_popular,
                        'isFeatured' => (bool)$service->is_featured,
                    ];
                });
                return $this->success($services);

            case 'barbers':
                $query = \App\Models\Employee::query()->active()->visible();
                $barbers = $query->with(['user', 'reviews'])->get()->map(function ($employee) use ($request) {
                    return [
                        'id' => (string)$employee->id,
                        'userId' => (string)$employee->user_id,
                        'fullName' => $employee->full_name,
                        'description' => $employee->biography ?? $employee->title ?? '',
                        'workingDays' => ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday"],
                        'isActive' => (bool)$employee->is_active,
                        'isAvailable' => (bool)$employee->is_active,
                        'rating' => (double)$employee->average_rating,
                        'reviewCount' => (int)$employee->reviews()->count(),
                        'profileImageUrl' => $employee->user->profile_photo ? $request->schemeAndHttpHost() . '/storage/' . $employee->user->profile_photo : '',
                    ];
                });
                return $this->success($barbers);

            case 'appointments':
                $userId = null;
                $barberId = null;
                $date = null;
                $time = null;
                $status = null;

                foreach ($where as $w) {
                    if ($w['field'] === 'userId') {
                        $userId = $w['value'];
                    }
                    if ($w['field'] === 'barberId') {
                        $barberId = $w['value'];
                    }
                    if ($w['field'] === 'date') {
                        $date = $w['value'];
                    }
                    if ($w['field'] === 'time') {
                        $time = $w['value'];
                    }
                    if ($w['field'] === 'status') {
                        $status = $w['value'];
                    }
                }

                if ($userId) {
                    if (!$user || (string)$user->id !== (string)$userId) {
                        return $this->error('Unauthorized', 403);
                    }
                    $query = \App\Models\Appointment::with(['employee.user', 'appointmentServices.service', 'review'])
                        ->where('customer_id', $userId);
                } else if ($barberId) {
                    $query = \App\Models\Appointment::with(['employee.user', 'appointmentServices.service', 'review'])
                        ->where('employee_id', $barberId);
                } else {
                    return $this->error('Invalid query params', 400);
                }

                if ($date) {
                    $query->whereDate('start_at', $date);
                }
                if ($time) {
                    $query->whereRaw("TIME_FORMAT(start_at, '%H:%i') = ?", [$time]);
                }
                if ($status) {
                    if ($status === 'active') {
                        $query->whereIn('status', [\App\Enums\AppointmentStatus::Pending, \App\Enums\AppointmentStatus::Confirmed]);
                    } elseif ($status === 'cancelled') {
                        $query->whereIn('status', [\App\Enums\AppointmentStatus::Cancelled, \App\Enums\AppointmentStatus::Rejected]);
                    } elseif ($status === 'completed') {
                        $query->whereIn('status', [\App\Enums\AppointmentStatus::Completed, \App\Enums\AppointmentStatus::NoShow]);
                    }
                }

                $appointments = $query->get()->map(function ($appt) use ($request) {
                    $services = $appt->appointmentServices->map(fn($as) => $as->service)->filter();
                    $serviceNames = $services->pluck('name')->join(' + ');
                    $firstServiceId = $services->first()?->id ?? '';
                    
                    return [
                        'id' => (string)$appt->id,
                        'userId' => (string)$appt->customer_id,
                        'barberId' => (string)$appt->employee_id,
                        'barberName' => $appt->employee->full_name ?? '',
                        'barberImageUrl' => ($appt->employee && $appt->employee->user && $appt->employee->user->profile_photo) ? $request->schemeAndHttpHost() . '/storage/' . $appt->employee->user->profile_photo : '',
                        'serviceId' => (string)$firstServiceId,
                        'serviceName' => $serviceNames ?: 'Diğer',
                        'date' => $appt->start_at->format('Y-m-d'),
                        'time' => $appt->start_at->format('H:i'),
                        'price' => (int)$appt->total_price,
                        'status' => $appt->status->value,
                        'isReviewed' => $appt->review !== null,
                        'rating' => $appt->review ? (int)$appt->review->rating : null,
                        'icalUrl' => $request->schemeAndHttpHost() . '/api/v1/mobile/appointments/' . $appt->id . '/ical?signature=' . hash_hmac('sha256', $appt->id, config('app.key')),
                        'createdAt' => $appt->created_at->toISOString(),
                        'updatedAt' => $appt->updated_at->toISOString(),
                    ];
                });

                return $this->success($appointments);

            case 'notifications':
                if (!$user) {
                    return $this->error('Unauthenticated', 401);
                }
                $notifications = \App\Models\Notification::where('user_id', $user->id)
                    ->orderBy('sent_at', 'desc')
                    ->get()
                    ->map(function ($n) {
                        return [
                            'id' => (string)$n->id,
                            'icon' => $n->data['icon'] ?? 'bell',
                            'title' => $n->title,
                            'message' => $n->body,
                            'userId' => (string)$n->user_id,
                            'createdAt' => $n->sent_at ? $n->sent_at->toISOString() : now()->toISOString(),
                        ];
                    });
                return $this->success($notifications);

            case 'campaigns':
                $query = \App\Models\Campaign::query()->with('categories')->active()->orderBy('end_date', 'asc');
                $campaigns = $query->get()->map(function ($camp) {
                    return [
                        'id' => (string)$camp->id,
                        'title' => $camp->title,
                        'description' => $camp->description ?? '',
                        'type' => $camp->type ?? 'auto_apply',
                        'minOrderAmount' => (double)($camp->min_order_amount ?? 0),
                        'maxDiscountAmount' => $camp->max_discount_amount ? (double)$camp->max_discount_amount : null,
                        'targetAudience' => $camp->target_audience ?? 'all',
                        'imagePath' => $camp->image_path ? request()->schemeAndHttpHost() . '/storage/' . $camp->image_path : null,
                        'priority' => (int)($camp->priority ?? 0),
                        'discountType' => $camp->discount_type->value,
                        'discountValue' => (double)$camp->discount_value,
                        'startDate' => $camp->start_date ? $camp->start_date->format('Y-m-d') : null,
                        'endDate' => $camp->end_date ? $camp->end_date->format('Y-m-d') : null,
                        'isActive' => (bool)$camp->is_active,
                        'perCustomerLimit' => $camp->per_customer_limit ? (int)$camp->per_customer_limit : null,
                        'categories' => $camp->categories->pluck('name')->toArray(),
                    ];
                });
                return $this->success($campaigns);

            case 'coupons':
                if (!$user) {
                    return $this->error('Unauthenticated', 401);
                }
                
                $query = \App\Models\Coupon::query()
                    ->where(function($q) use ($user) {
                        $q->whereNull('user_id')
                          ->orWhere('user_id', $user->id);
                    })
                    ->orderBy('created_at', 'desc');
                
                $coupons = $query->get()->map(function ($coupon) use ($user) {
                    return [
                        'id' => (string)$coupon->id,
                        'title' => $coupon->title ?? 'İndirim Kuponu',
                        'description' => $coupon->description ?? '',
                        'code' => $coupon->code,
                        'discountType' => $coupon->discount_type,
                        'discountValue' => (double)$coupon->discount_value,
                        'expiresAt' => $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : null,
                        'usageLimit' => (int)$coupon->usage_limit,
                        'usedCount' => (int)$coupon->used_count,
                        'perCustomerLimit' => (int)$coupon->per_customer_limit,
                        'remainingUsage' => (int)min(
                            max(0, (int)$coupon->usage_limit - (int)$coupon->used_count),
                            max(0, (int)$coupon->per_customer_limit - $coupon->usages()->where('customer_id', $user->id)->count())
                        ),
                        'isValid' => $coupon->isValid($user),
                    ];
                });
                return $this->success($coupons);

            case 'barberAvailability':
                $barberId = null;
                foreach ($where as $w) {
                    if ($w['field'] === 'barberId') {
                        $barberId = $w['value'];
                    }
                }

                if (!$barberId) {
                    return $this->error('Barber ID is required', 400);
                }

                $leaves = \App\Models\EmployeeLeave::where('employee_id', $barberId)
                    ->where('approval_status', \App\Enums\ApprovalStatus::Approved)
                    ->get();
                $schedules = \App\Models\EmployeeSchedule::where('employee_id', $barberId)->get();

                $availabilities = [];

                foreach ($leaves as $leave) {
                    $start = $leave->start_date;
                    $end = $leave->end_date;
                    $current = $start->copy();
                    while ($current->lte($end)) {
                        $dateStr = $current->format('Y-m-d');
                        $availabilities[] = [
                            'id' => 'leave_' . $leave->id . '_' . $dateStr,
                            'barberId' => (string)$barberId,
                            'date' => $dateStr,
                            'fullDayOff' => true,
                            'blockedSlots' => [],
                            'reason' => $leave->reason ?? 'İzinli',
                        ];
                        $current->addDay();
                    }
                }

                foreach ($schedules as $sched) {
                    if ($sched->is_day_off) {
                        $dateStr = $sched->work_date->format('Y-m-d');
                        $availabilities[] = [
                            'id' => 'sched_' . $sched->id,
                            'barberId' => (string)$barberId,
                            'date' => $dateStr,
                            'fullDayOff' => true,
                            'blockedSlots' => [],
                            'reason' => 'Haftalık İzin',
                        ];
                    }
                }

                return $this->success($availabilities);

            default:
                return $this->error('Collection not found', 404);
        }
    }

    /**
     * Get specific document emulation.
     */
    public function showDocument(string $collection, string $id, Request $request): JsonResponse
    {
        $user = $request->user();

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
                'barberId' => 'required',
                'serviceId' => 'nullable',
                'serviceIds' => 'nullable|array',
                'date' => 'required|date_format:Y-m-d',
                'time' => 'required|date_format:H:i',
                'couponCode' => 'nullable|string',
                'campaignId' => 'nullable|string'
            ]);

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
            } else {
                return $this->error('Service is required', 400);
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
                return $this->success([
                    'isValid' => true,
                    'discountAmount' => $result['discount_amount'],
                    'message' => $successMessage
                ]);
            }

            return $this->error($result['message'] ?? $errorMessage, 400);

        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->error('Doğrulama sırasında bir hata oluştu.', 500);
        }
    }

    /**
     * Delete generic document emulation.
     */
    public function deleteDocument(string $collection, string $id, Request $request): JsonResponse
    {
        return $this->error('Action not supported', 400);
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
