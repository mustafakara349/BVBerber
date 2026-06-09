<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'full_name' => $this->full_name,
            'title' => $this->title,
            'biography' => $this->biography,
            'appointment_color' => $this->appointment_color,
            'salary_type' => $this->salary_type?->value,
            'commission_rate' => $this->commission_rate,
            'is_active' => $this->is_active,
            'is_visible' => $this->is_visible,
            'average_rating' => $this->average_rating,
            'user' => new UserResource($this->whenLoaded('user')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
        ];
    }
}
