<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 *
 * Sistema UNN - FormRequest para validacao do formulario de
 * configuracao de um provedor S3.
 *
 * Spec: .kiro/specs/multi-provider-s3-storage (task 4.1)
 * Requirements: 1.3
 */

namespace App\Http\Requests;

use App\Support\StorageProviderConfig;
use App\Support\StorageProviderRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorageProviderUpdateRequest extends FormRequest
{
    /**
     * Autorizacao e feita no controller (middleware admin + superadmin.legacy).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', Rule::in(StorageProviderRegistry::PROVIDERS)],
            'access_key' => ['required', 'string', 'max:255'],
            'secret_key' => ['required', 'string', 'max:255'],
            'bucket' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:64'],
            'endpoint' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'path_style' => ['nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'provider.required' => 'O provedor deve ser informado.',
            'provider.in' => 'Provedor invalido. Use idrive, wasabi ou aws.',
            'access_key.required' => 'O Access Key e obrigatorio.',
            'secret_key.required' => 'O Secret Key e obrigatorio.',
            'bucket.required' => 'O Bucket e obrigatorio.',
            'region.required' => 'A Region e obrigatoria.',
        ];
    }

    /**
     * Retorna um StorageProviderConfig pronto a partir dos dados validados.
     */
    public function toConfig(): StorageProviderConfig
    {
        return new StorageProviderConfig(
            accessKey: trim((string) $this->input('access_key', '')),
            secretKey: trim((string) $this->input('secret_key', '')),
            bucket: trim((string) $this->input('bucket', '')),
            region: trim((string) $this->input('region', '')),
            endpoint: trim((string) $this->input('endpoint', '')),
            url: trim((string) $this->input('url', '')),
            pathStyle: filter_var(
                $this->input('path_style', '0'),
                FILTER_VALIDATE_BOOLEAN
            ),
        );
    }

    /**
     * Identificador do provedor sendo configurado (idrive | wasabi | aws).
     */
    public function provider(): string
    {
        return strtolower(trim((string) $this->input('provider', '')));
    }
}
