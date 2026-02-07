<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\ApiMedia;

class MentorshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $mentor = $this->whenLoaded('mentor');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'slots' => $this->slots,
            'schedule' => $this->schedule,
            'mentor' => $mentor ? [
                'id' => $mentor->id,
                'name' => $mentor->name,
                'photo_url' => ApiMedia::url($mentor->photo),
            ] : null,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
