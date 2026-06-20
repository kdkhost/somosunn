<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SponsorPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'max_banners' => ['required', 'integer', 'min:0'],
            'max_events' => ['required', 'integer', 'min:0'],
            'max_leads' => ['required', 'integer', 'min:0'],
            'priority' => ['required', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
