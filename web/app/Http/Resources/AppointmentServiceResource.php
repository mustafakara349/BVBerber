<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'quantity' => $this->quantity,
            'duration_minutes' => $this->duration_minutes,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
        ];
    }
}
