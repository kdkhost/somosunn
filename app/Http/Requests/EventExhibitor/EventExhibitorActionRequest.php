<?php

namespace App\Http\Requests\EventExhibitor;

use Illuminate\Foundation\Http\FormRequest;

class EventExhibitorActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reason' => 'nullable|string|max:500',
            'amount' => 'nullable|string|max:30',
        ];
    }
}
