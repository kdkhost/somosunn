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
            'price_periods' => method_exists($this->resource, 'resolvedPricePeriods')
                ? $this->resolvedPricePeriods()
                : $this->price_periods,
            'period_settings' => method_exists($this->resource, 'resolvedPeriodSettings')
                ? $this->resolvedPeriodSettings()
                : $this->period_settings,
            'available_periods' => method_exists($this->resource, 'getAvailablePeriods')
                ? $this->getAvailablePeriods()
                : [],
            'billing_cycle' => $this->billing_cycle,
            'prorata' => (bool) $this->prorata,
            'description' => method_exists($this->resource, 'marketingDescription')
                ? $this->marketingDescription()
                : $this->description,
            'image_url' => ApiMedia::url($this->image),
            'is_featured' => (bool) $this->is_featured,
            'highlight' => (bool) $this->highlight,
            'coupons_enabled' => (bool) $this->coupons_enabled,
            'benefits' => method_exists($this->resource, 'resolvedBenefits')
                ? $this->resolvedBenefits()
                : $this->benefits,
            'permissions' => method_exists($this->resource, 'resolvedPermissions')
                ? $this->resolvedPermissions()
                : $this->permissions,
            'comparison' => $this->comparison,
            'is_active' => (bool) $this->is_active,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
