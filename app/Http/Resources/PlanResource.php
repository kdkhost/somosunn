<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\ApiMedia;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'period' => $this->period,
            'billing_cycle' => $this->billing_cycle,
            'prorata' => (bool) $this->prorata,
            'description' => $this->description,
            'image_url' => ApiMedia::url($this->image),
            'is_featured' => (bool) $this->is_featured,
            'highlight' => (bool) $this->highlight,
            'coupons_enabled' => (bool) $this->coupons_enabled,
            'benefits' => $this->benefits,
            'permissions' => $this->permissions,
            'comparison' => $this->comparison,
            'is_active' => (bool) $this->is_active,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
