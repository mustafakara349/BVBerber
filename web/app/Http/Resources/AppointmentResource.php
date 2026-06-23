<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'appointment_code' => $this->appointment_code,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'total_duration' => $this->total_duration,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_price' => $this->total_price,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'payment_status' => $this->payment_status?->value,
            'payment_status_label' => $this->payment_status?->label(),
            'payment_method' => $this->payment_method?->value,
            'source' => $this->source?->value,
            'customer_note' => $this->customer_note,
            'internal_note' => $this->internal_note,
            'no_show' => $this->no_show,
            'is_reviewed' => $this->review()->exists(),
            'rating' => $this->review?->rating,
            'ical_url' => $request->schemeAndHttpHost() . '/api/v1/mobile/appointments/' . $this->id . '/ical?signature=' . hash_hmac('sha256', $this->id, config('app.key')),
            'customer' => new UserResource($this->whenLoaded('customer')),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'services' => AppointmentServiceResource::collection($this->whenLoaded('appointmentServices')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
