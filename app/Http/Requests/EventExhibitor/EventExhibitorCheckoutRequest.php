<?php

namespace App\Http\Requests\EventExhibitor;

use App\Rules\ValidEmailAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventExhibitorCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guest = !$this->user();

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                new ValidEmailAddress(),
                'max:255',
                $guest ? Rule::unique('users', 'email') : 'nullable',
            ],
            'phone' => 'required|string|max:50',
            'document' => 'required|string|max:30',
            'company_name' => 'required|string|max:255',
            'company_document' => 'nullable|string|max:30',
            'brand_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'coupon_code' => 'nullable|string|max:40',
            'quantity' => 'required|integer|min:1|max:20',
            'terms' => 'accepted',
            'gateway' => 'nullable|string|in:mercadopago,sumup',
            'password' => $guest ? 'required|string|min:8|confirmed' : 'nullable|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
        }

        foreach (['phone', 'document', 'company_document'] as $field) {
            if ($this->has($field)) {
                $this->merge([$field => trim((string) $this->input($field))]);
            }
        }

        if ($this->has('quantity')) {
            $this->merge(['quantity' => max(1, (int) $this->input('quantity'))]);
        }
    }
}
