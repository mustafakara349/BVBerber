<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'buffer_time' => $this->buffer_time,
            'price' => $this->price,
            'discounted_price' => $this->discounted_price,
            'effective_price' => $this->effective_price,
            'gender_type' => $this->gender_type,
            'image' => $this->image,
            'is_popular' => $this->is_popular,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
        ];
    }
}
