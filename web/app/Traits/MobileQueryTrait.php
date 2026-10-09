<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait MobileQueryTrait
{
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
                $barbers = $query->with(['user', 'reviews', 'schedules'])->get()->map(function ($employee) use ($request) {
                    // Sonraki 30 gün içindeki tam günlük izinleri bul
                    $dayOffDates = $employee->schedules
                        ->where('is_day_off', true)
                        ->whereBetween('work_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
                        ->pluck('work_date')
                        ->map(fn($d) => strtolower(\Carbon\Carbon::parse($d)->format('l')))
                        ->toArray();

                    // Haftalık olarak sürekli kapalı olan günleri tespit et (30 günde 4+ kez kapalıysa)
                    $dayCounts = array_count_values($dayOffDates);
                    $permanentDaysOff = array_keys(array_filter($dayCounts, fn($count) => $count >= 4));

                    $allDays = ['monday','tuesday','wednesday','thursday','friday','saturday'];
                    $workingDays = array_values(array_diff($allDays, $permanentDaysOff));

                    return [
                        'id' => (string)$employee->id,
                        'userId' => (string)$employee->user_id,
                        'fullName' => $employee->full_name,
                        'description' => $employee->biography ?? $employee->title ?? '',
                        'workingDays' => $workingDays,
                        'isActive' => (bool)$employee->is_active,
                        'isAvailable' => (bool)$employee->is_active,
                        'rating' => (double)$employee->average_rating,
                        'reviewCount' => (int)$employee->reviews()->count(),
                        'profileImageUrl' => ($employee->user && $employee->user->profile_photo) ? $request->schemeAndHttpHost() . '/storage/' . $employee->user->profile_photo : '',
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
                    $query = \App\Models\Appointment::with(['employee.user', 'appointmentServices.service', 'review', 'campaign'])
                        ->where('customer_id', $userId);
                    $isAuthorizedToViewDetails = true;
                } else if ($barberId) {
                    $query = \App\Models\Appointment::with(['employee.user', 'appointmentServices.service', 'review', 'campaign'])
                        ->where('employee_id', $barberId);
                    $isAuthorizedToViewDetails = $user && ($user->isAdmin() || $user->isBarber());
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

                $appointments = $query->get()->map(function ($appt) use ($request, $user, $isAuthorizedToViewDetails) {
                    $services = $appt->appointmentServices->map(fn($as) => $as->service)->filter();
                    $serviceNames = $services->pluck('name')->join(' + ');
                    $firstServiceId = $services->first()?->id ?? '';

                    $isOwner = $user && (string)$appt->customer_id === (string)$user->id;
                    $canView = $isAuthorizedToViewDetails || $isOwner;

                    if (!$canView) {
                        return [
                            'id' => (string)$appt->id,
                            'userId' => '', // Masked
                            'barberId' => (string)$appt->employee_id,
                            'barberName' => '',
                            'barberImageUrl' => '',
                            'serviceId' => '',
                            'serviceName' => 'Gizli Randevu',
                            'date' => $appt->start_at->format('Y-m-d'),
                            'time' => $appt->start_at->format('H:i'),
                            'price' => 0,
                            'status' => $appt->status->value,
                            'isReviewed' => false,
                            'rating' => null,
                            'icalUrl' => '',
                            'createdAt' => $appt->created_at->toISOString(),
                            'updatedAt' => $appt->updated_at->toISOString(),
                        ];
                    }
                    
                    $rewardType = null;
                    $rewardName = null;
                    if ($appt->campaign && $appt->campaign->reward_type !== 'discount') {
                        $rewardType = $appt->campaign->reward_type;
                        if ($rewardType === 'gift_product' && $appt->campaign->reward_product_id) {
                            $prod = \App\Models\Product::find($appt->campaign->reward_product_id);
                            if ($prod) $rewardName = $prod->name;
                        } elseif ($rewardType === 'gift_cafe' && $appt->campaign->reward_cafe_product_id) {
                            $cprod = \App\Models\CafeProduct::find($appt->campaign->reward_cafe_product_id);
                            if ($cprod) $rewardName = $cprod->name;
                        }
                    }

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
                        'originalPrice' => (int)$appt->subtotal,
                        'price' => (int)$appt->total_price,
                        'status' => $appt->status->value,
                        'isReviewed' => $appt->review !== null,
                        'rating' => $appt->review ? (int)$appt->review->rating : null,
                        'rewardType' => $rewardType,
                        'rewardName' => $rewardName,
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
                        'triggerType' => $camp->trigger_type ?? 'all',
                        'rewardType' => $camp->reward_type ?? 'discount',
                        'rewardProductId' => $camp->reward_product_id ? (string)$camp->reward_product_id : null,
                        'rewardCafeProductId' => $camp->reward_cafe_product_id ? (string)$camp->reward_cafe_product_id : null,
                        'minOrderAmount' => (double)($camp->min_order_amount ?? 0),
                        'maxDiscountAmount' => $camp->max_discount_amount ? (double)$camp->max_discount_amount : null,
                        'targetAudience' => $camp->target_audience ?? 'all',
                        'imagePath' => $camp->image_path ? request()->schemeAndHttpHost() . '/storage/' . $camp->image_path : null,
                        'priority' => (int)($camp->priority ?? 0),
                        'discountType' => $camp->discount_type ? $camp->discount_type->value : 'percentage',
                        'discountValue' => (double)$camp->discount_value,
                        'startDate' => $camp->start_date ? $camp->start_date->format('Y-m-d') : null,
                        'endDate' => $camp->end_date ? $camp->end_date->format('Y-m-d') : null,
                        'isActive' => (bool)$camp->is_active,
                        'perCustomerLimit' => $camp->per_customer_limit ? (int)$camp->per_customer_limit : null,
                        'terms' => $camp->terms ?? '',
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
                        $q->doesntHave('users')
                          ->orWhereHas('users', function($q2) use ($user) {
                              $q2->where('users.id', $user->id);
                          });
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

            case 'cafe_products':
                // Mobile API context: always use branch 1 (no web session available)
                $branchId = 1;
                $query = \App\Models\CafeProduct::with('cafeCategory')
                    ->forBranch($branchId)
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('name');

                // Category filter (optional where clause)
                $categoryFilter = null;
                foreach ($where as $w) {
                    if ($w['field'] === 'category') {
                        $categoryFilter = $w['value'];
                    }
                }
                if ($categoryFilter) {
                    $query->byCategory($categoryFilter);
                }

                $cafeProducts = $query->get()->map(function ($item) use ($request) {
                    return [
                        'id'          => (string)$item->id,
                        'name'        => $item->name,
                        'category_id' => $item->cafe_category_id ? (string)$item->cafe_category_id : '0',
                        'category_name' => $item->cafeCategory ? $item->cafeCategory->name : 'Diğer',
                        'description' => $item->description ?? '',
                        'ingredients' => $item->ingredients ?? '',
                        'price'       => (float)$item->price,
                        'imageUrl'    => $item->image_path
                            ? $request->schemeAndHttpHost() . '/storage/' . $item->image_path
                            : '',
                        'isActive'    => (bool)$item->is_active,
                        'isFeatured'  => (bool)$item->is_featured,
                        'sortOrder'   => (int)$item->sort_order,
                    ];
                });
                return $this->success($cafeProducts);

            case 'sale_products':
                // Mobile API context: always use branch 1 (no web session available)
                $branchId = 1;
                $query = \App\Models\Product::with('productCategory')
                    ->forBranch($branchId)
                    ->active()
                    ->orderBy('name');

                // Category filter
                $categoryFilter = null;
                $searchFilter   = null;
                foreach ($where as $w) {
                    if ($w['field'] === 'category') { $categoryFilter = $w['value']; }
                    if ($w['field'] === 'search')   { $searchFilter   = $w['value']; }
                }
                if ($categoryFilter && $categoryFilter !== 'all') {
                    $query->where('product_category_id', $categoryFilter);
                }
                if ($searchFilter) {
                    $query->where('name', 'like', "%{$searchFilter}%");
                }

                $saleProducts = $query->get()->map(function ($product) use ($request) {
                    return [
                        'id'            => (string)$product->id,
                        'name'          => $product->name,
                        'category_id'   => $product->product_category_id ? (string)$product->product_category_id : '0',
                        'category_name' => $product->productCategory ? $product->productCategory->name : 'Diğer',
                        'description'   => $product->description ?? '',
                        'sku'           => $product->sku ?? '',
                        'barcode'       => $product->barcode ?? '',
                        'sellPrice'     => (float)$product->sell_price,
                        'purchasePrice' => (float)$product->purchase_price,
                        'stockQuantity' => (int)$product->stock_quantity,
                        'criticalStock' => (int)($product->critical_stock ?? 5),
                        'imageUrl'      => $product->image
                            ? $request->schemeAndHttpHost() . '/storage/' . $product->image
                            : '',
                        'isActive'      => (bool)$product->is_active,
                        'isInStock'     => $product->stock_quantity > 0,
                    ];
                });
                return $this->success($saleProducts);

            default:
                return $this->error('Collection not found', 404);
        }
    }
}
