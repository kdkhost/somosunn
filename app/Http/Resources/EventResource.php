<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\ApiMedia;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'speaker' => $this->speaker,
            'description' => $this->description,
            'image_url' => ApiMedia::url($this->image),
            'start_at' => optional($this->start_at)->toIso8601String(),
            'end_at' => optional($this->end_at)->toIso8601String(),
            'all_day' => (bool) $this->all_day,
            'location' => $this->location,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'price' => $this->price,
            'current_price' => $this->current_price,
            'batch_label' => $this->current_batch_label,
            'capacity' => $this->capacity,
            'confirmed_seats' => $this->confirmed_seats,
            'remaining_seats' => $this->remaining_seats,
            'published' => (bool) $this->published,
            'color' => $this->color,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
