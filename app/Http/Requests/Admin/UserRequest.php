<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Rules\ValidEmailAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'doc' => preg_replace('/\D/', '', (string) $this->input('doc')) ?: null,
            'email_verified' => $this->boolean('email_verified'),
            'show_email_public' => $this->boolean('show_email_public'),
            'show_phone_public' => $this->boolean('show_phone_public'),
            'show_address_public' => $this->boolean('show_address_public'),
            'hide_profile' => $this->boolean('hide_profile'),
        ]);
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $isCreating = !$user instanceof User;
        $featureKeys = array_keys(\App\Models\Plan::siteFeatureLabels());

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                new ValidEmailAddress(),
                Rule::unique('users', 'email')->ignore($user?->getKey()),
            ],
            'password' => [$isCreating ? 'required' : 'nullable', 'string', 'min:6', 'confirmed'],
            'email_verified' => ['required', 'boolean'],
            'person_type' => ['nullable', Rule::in(['F', 'J'])],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'doc' => ['nullable', 'string', 'max:14'],
            'phone' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:100'],
            'segment' => ['nullable', 'string', 'max:120'],
            'interests' => ['nullable', 'string', 'max:1000'],
            'bio' => ['nullable', 'string', 'max:500'],
            'cep' => ['nullable', 'string', 'max:9'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'size:2'],
            'website' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'pix_key' => ['nullable', 'string', 'max:255'],
            'show_email_public' => ['required', 'boolean'],
            'show_phone_public' => ['required', 'boolean'],
            'show_address_public' => ['required', 'boolean'],
            'hide_profile' => ['required', 'boolean'],
            'role' => ['nullable', Rule::in(['member', 'membro', 'instrutor', 'admin', 'superadmin'])],
            'level' => ['nullable', Rule::in([
                'iniciante', 'intermediario', 'avancado', 'bronze', 'prata', 'ouro', 'diamante', 'sucesso', 'superadmin',
            ])],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'plan_expires_at' => ['nullable', 'date'],
            'extra_features' => ['nullable', 'array'],
            'extra_features.*' => ['string', Rule::in($featureKeys)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome completo',
            'email' => 'e-mail',
            'password' => 'senha',
            'email_verified' => 'validação do e-mail',
            'birth_date' => 'data de nascimento',
            'doc' => 'CPF/CNPJ',
            'phone' => 'telefone',
            'occupation' => 'ocupação',
            'company' => 'empresa',
            'segment' => 'segmento',
            'interests' => 'interesses',
            'cep' => 'CEP',
            'street' => 'logradouro',
            'number' => 'número',
            'neighborhood' => 'bairro',
            'city' => 'cidade',
            'state' => 'estado',
            'plan_expires_at' => 'expiração do plano',
        ];
    }
}
