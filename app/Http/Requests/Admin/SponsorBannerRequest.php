<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SponsorBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'sponsor_id' => ['required', 'integer', 'exists:sponsors,id'],
            'title' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:6144'],
            'url' => ['nullable', 'url', 'max:255'],
            'position' => ['required', Rule::in(array_keys(\App\Models\SponsorBanner::POSITIONS))],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'active' => ['nullable', 'boolean'],
            'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }
}
