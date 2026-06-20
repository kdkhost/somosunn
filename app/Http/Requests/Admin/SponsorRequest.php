<?php

namespace App\Http\Requests\Admin;

use App\Models\Sponsor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SponsorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'sponsor_plan_id' => ['required', 'integer', 'exists:sponsor_plans,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in([
                Sponsor::STATUS_PENDING,
                Sponsor::STATUS_ACTIVE,
                Sponsor::STATUS_EXPIRED,
                Sponsor::STATUS_CANCELLED,
            ])],
        ];
    }
}
