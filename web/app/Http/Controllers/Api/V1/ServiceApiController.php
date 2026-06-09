<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $branchId = $request->get('branch_id', 1);
        $type = $request->get('type'); // 'barber' or 'cafe'

        $query = Service::query()
            ->forBranch($branchId)
            ->active();

        if ($type) {
            $query->where('type', $type);
        }

        $services = $query->with('category:id,name')->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'slug' => $service->slug,
                    'description' => $service->description,
                    'duration_minutes' => $service->duration_minutes,
                    'price' => $service->price,
                    'discounted_price' => $service->discounted_price,
                    'effective_price' => $service->effective_price,
                    'gender_type' => $service->gender_type,
                    'type' => $service->type ?? 'barber',
                    'image_url' => $service->image ? url('storage/' . $service->image) : null,
                    'category_name' => $service->category->name ?? 'Diğer',
                    'is_popular' => $service->is_popular,
                    'is_featured' => $service->is_featured,
                ];
            });

        return $this->success($services, 'Services list fetched successfully');
    }
}
