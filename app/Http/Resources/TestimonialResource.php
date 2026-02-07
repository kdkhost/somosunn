<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author_name' => $this->author_name,
            'author_title' => $this->author_title,
            'rating' => $this->rating,
            'content' => $this->content,
            'is_featured' => (bool) $this->is_featured,
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
