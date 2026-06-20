<?php

/**
 * @autor marcelo-brad rj
 * @contato Tel: 21 981325441
 * @contato Email: contato@kdkhost.com.br
 * @contato Telegram: @MARCELO_BRAD
 * @contato Instagram: @marcelobradrj
 * @contato WhatsApp: 21981325441
 */

namespace App\Http\Requests\Admin;

use App\Models\Event;
use App\Models\EventCoupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class EventCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => EventCoupon::normalizeCodeValue($this->input('code')),
            'applies_to' => $this->input('applies_to') ?: EventCoupon::APPLIES_ATTENDEE,
            'discount_value' => $this->normalizeMoney($this->input('discount_value')),
            'starts_at' => $this->normalizeDateTime($this->input('starts_at')),
            'expires_at' => $this->normalizeDateTime($this->input('expires_at')),
        ]);
    }

    public function rules(): array
    {
        $event = $this->route('event');
        $coupon = $this->route('coupon');
        $eventId = $event instanceof Event ? (int) $event->id : (int) $event;
        $couponId = $coupon instanceof EventCoupon ? (int) $coupon->id : null;
        $uniqueCodeRule = Rule::unique('event_coupons', 'code')
            ->where(fn ($query) => $query->where('event_id', $eventId));

        if ($couponId) {
            $uniqueCodeRule->ignore($couponId);
        }

        $discountMaximum = $this->input('type') === EventCoupon::TYPE_PERCENT ? 100 : 999999.99;

        return [
            'code' => [
                'required',
                'string',
                'max:40',
                $uniqueCodeRule,
            ],
            'type' => ['required', Rule::in([EventCoupon::TYPE_FREE, EventCoupon::TYPE_PERCENT, EventCoupon::TYPE_FIXED])],
            'applies_to' => ['required', Rule::in([
                EventCoupon::APPLIES_ATTENDEE,
                EventCoupon::APPLIES_EXHIBITOR,
                EventCoupon::APPLIES_BOTH,
            ])],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'max:' . $discountMaximum],
            'max_uses' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'max_uses_per_user' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Informe o código do cupom.',
            'code.unique' => 'Já existe um cupom com este código para este evento.',
            'type.required' => 'Informe o tipo de desconto.',
            'discount_value.numeric' => 'Informe um valor de desconto válido.',
            'discount_value.max' => 'O desconto percentual não pode ultrapassar 100%.',
            'max_uses_per_user.integer' => 'Informe um limite por usuário válido.',
            'expires_at.after_or_equal' => 'A data de expiração deve ser igual ou posterior ao início.',
        ];
    }

    public function validatedPayload(): array
    {
        $data = $this->validated();
        $data['active'] = $this->boolean('active', true);
        $data['discount_value'] = (float) (($data['type'] ?? null) === EventCoupon::TYPE_FREE
            ? 100
            : ($data['discount_value'] ?? 0));

        if (in_array($data['applies_to'] ?? null, [EventCoupon::APPLIES_EXHIBITOR, EventCoupon::APPLIES_BOTH], true)) {
            $data['max_uses_per_user'] = (int) ($data['max_uses_per_user'] ?? 1);
        } else {
            $data['max_uses_per_user'] = null;
        }

        return $data;
    }

    private function normalizeMoney(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(['R$', ' ', "\xc2\xa0"], '', $value);
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return $value;
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace('T', ' ', $value);

        foreach (['d/m/Y H:i', 'd/m/Y H:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                continue;
            }
        }

        return $value;
    }
}
