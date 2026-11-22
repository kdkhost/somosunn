<?php

namespace App\Services\Payment;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EfiService
{
    public function isEnabled(): bool
    {
        $dbEnabled = Setting::get('efi_enabled');
        if ($dbEnabled !== null) {
            return in_array(strtolower(trim((string) $dbEnabled)), ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) config('payments.efi.enabled', false);
    }

    public function environment(): string
    {
        $env = strtolower(trim((string) (Setting::get('efi_env') ?: config('payments.efi.env', 'sandbox'))));

        return $env === 'production' ? 'production' : 'sandbox';
    }

    public function clientId(): string
    {
        return trim((string) (Setting::get('efi_client_id') ?: config('payments.efi.client_id', '')));
    }

    public function clientSecret(): string
    {
        return trim((string) (Setting::get('efi_client_secret') ?: config('payments.efi.client_secret', '')));
    }

    public function pixKey(): string
    {
        return trim((string) (Setting::get('efi_pix_key') ?: config('payments.efi.pix_key', '')));
    }

    public function webhookSecret(): string
    {
        return trim((string) (Setting::get('efi_webhook_secret') ?: config('payments.efi.webhook_secret', '')));
    }

    public function baseUrl(): string
    {
        return $this->environment() === 'production'
            ? 'https://pix.api.efipay.com.br'
            : 'https://pix-h.api.efipay.com.br';
    }

    public function certificatePath(): string
    {
        return trim((string) (Setting::get('efi_certificate_path') ?: config('payments.efi.certificate_path', '')));
    }

    /**
     * Valida a autenticidade do webhook da Efí Pro usando segredo HMAC-SHA256 ou token compartilhado.
     */
    public function validateWebhookRequest(string $rawPayload, string $providedSignature = '', string $providedHmacQuery = ''): bool
    {
        $secret = $this->webhookSecret();
        if ($secret === '') {
            return !app()->environment('production');
        }

        if ($providedHmacQuery !== '' && hash_equals($secret, $providedHmacQuery)) {
            return true;
        }

        if ($providedSignature === '') {
            return false;
        }

        $expectedHex = hash_hmac('sha256', $rawPayload, $secret);
        $cleanSignature = preg_replace('/^sha256=/i', '', trim($providedSignature)) ?: '';

        return $cleanSignature !== '' && hash_equals($expectedHex, strtolower($cleanSignature));
    }

    /**
     * Realiza autenticacao OAuth2 mTLS na API Pix da Efí Pro para teste de conectividade ou emissao.
     */
    public function requestAccessToken(?string $overrideClientId = null, ?string $overrideClientSecret = null, ?string $overrideEnv = null): array
    {
        $clientId = trim((string) ($overrideClientId ?: $this->clientId()));
        $clientSecret = trim((string) ($overrideClientSecret ?: $this->clientSecret()));
        $env = strtolower(trim((string) ($overrideEnv ?: $this->environment())));

        if ($clientId === '' || $clientSecret === '') {
            throw new \RuntimeException('Client ID e Client Secret da Efi Pro sao obrigatorios.');
        }

        $baseUrl = $env === 'production'
            ? 'https://pix.api.efipay.com.br'
            : 'https://pix-h.api.efipay.com.br';

        $options = [
            'timeout' => 15,
        ];

        $certPath = $this->certificatePath();
        if ($certPath !== '' && is_file($certPath)) {
            $options['cert'] = $certPath;
        }

        $response = Http::withOptions($options)
            ->withBasicAuth($clientId, $clientSecret)
            ->acceptJson()
            ->post($baseUrl . '/oauth/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            $error = $response->json('error_description') ?: $response->json('message') ?: ('HTTP ' . $response->status());
            Log::warning('EfiService OAuth error', ['status' => $response->status(), 'error' => $error]);
            throw new \RuntimeException('Falha na autenticacao Efi Pro: ' . $error);
        }

        return (array) $response->json();
    }
}
