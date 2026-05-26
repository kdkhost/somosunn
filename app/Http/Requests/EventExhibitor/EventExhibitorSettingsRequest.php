<?php

namespace App\Http\Requests\EventExhibitor;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;

class EventExhibitorSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'exhibitor_sales_enabled' => 'nullable|boolean',
            'exhibitor_total_slots' => 'nullable|integer|min:0|max:100000',
            'exhibitor_description' => 'nullable|string|max:20000',
            'exhibitor_internal_notes' => 'nullable|string|max:20000',
            'exhibitor_area_image' => 'nullable|image|max:10240',
            'remove_exhibitor_area_image' => 'nullable|boolean',
            'exhibitor_includes_ticket' => 'nullable|boolean',
            'exhibitor_show_publicly' => 'nullable|boolean',
            'exhibitor_batch_1_price' => 'nullable|numeric|min:0|max:999999.99',
            'exhibitor_batch_1_deadline' => 'nullable|date',
            'exhibitor_batch_1_slots' => 'nullable|integer|min:0|max:100000',
            'exhibitor_batch_2_price' => 'nullable|numeric|min:0|max:999999.99',
            'exhibitor_batch_2_deadline' => 'nullable|date',
            'exhibitor_batch_2_slots' => 'nullable|integer|min:0|max:100000',
            'exhibitor_batch_3_price' => 'nullable|numeric|min:0|max:999999.99',
            'exhibitor_batch_3_deadline' => 'nullable|date',
            'exhibitor_batch_3_slots' => 'nullable|integer|min:0|max:100000',
        ];
    }

    protected function prepareForValidation(): void
    {
        $moneyFields = [
            'exhibitor_batch_1_price',
            'exhibitor_batch_2_price',
            'exhibitor_batch_3_price',
        ];

        foreach ($moneyFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => $this->normalizeMoney($this->input($field))]);
            }
        }

        foreach ([
            'exhibitor_batch_1_deadline',
            'exhibitor_batch_2_deadline',
            'exhibitor_batch_3_deadline',
        ] as $field) {
            if ($this->filled($field)) {
                $this->merge([$field => str_replace('T', ' ', (string) $this->input($field))]);
            }
        }
    }

    private function normalizeMoney(mixed $value): ?float
    {
        return Plan::parseMoneyValue($value);
    }
}
