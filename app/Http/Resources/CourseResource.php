<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\ApiMedia;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'price' => $this->price,
            'duration' => $this->duration,
            'total_hours' => $this->total_hours,
            'thumbnail_url' => ApiMedia::url($this->thumbnail),
            'short_description' => $this->short_description,
            'full_description' => $this->full_description,
            'author_name' => $this->author_name,
            'status' => $this->status,
            'is_featured' => (bool) $this->is_featured,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
