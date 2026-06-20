<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class SponsorLeadConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'sponsor_id' => ['required', 'integer', 'exists:sponsors,id'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'source' => ['nullable', 'string', 'max:60'],
            'consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent.accepted' => 'Voce precisa aceitar o compartilhamento de dados antes de enviar o lead.',
        ];
    }
}
