<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'phone' => $this->phone,
            'email' => $this->email,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timezone' => $this->timezone,
            'logo' => $this->logo,
            'cover_image' => $this->cover_image,
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
            'is_active' => $this->is_active,
            'settings' => $this->whenLoaded('settings'),
        ];
    }
}
