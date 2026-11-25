<?php

namespace App\Services\Payment;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EfiService
{
    private const CERT_STORAGE_DIR = 'certificates/efi';

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
        $configured = trim((string) (Setting::get('efi_certificate_path') ?: config('payments.efi.certificate_path', '')));

        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        // Fallback para arquivo padrão no storage
        $defaultPath = storage_path('app/' . self::CERT_STORAGE_DIR . '/certificado.pem');
        if (is_file($defaultPath)) {
            return $defaultPath;
        }

        return $configured;
    }

    /**
     * Realiza o upload, conversão e armazenamento seguro do certificado da Efí Pay (.pem ou .p12)
     *
     * @param UploadedFile $file
     * @param string|null $passphrase Senha opcional caso o arquivo .p12 possua senha
     * @return array{success: bool, path: string, filename: string, message: string}
     */
    public function uploadCertificate(UploadedFile $file, ?string $passphrase = null): array
    {
        $dir = storage_path('app/' . self::CERT_STORAGE_DIR);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0700, true, true);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $finalPath = $dir . DIRECTORY_SEPARATOR . 'certificado.pem';

        if ($extension === 'p12') {
            // Conversão automática de .p12 para .pem compatível com cURL/Guzzle
            $p12Content = file_get_contents($file->getRealPath());
            $certs = [];
            $unlocked = openssl_pkcs12_read($p12Content, $certs, (string) ($passphrase ?? ''));

            if (!$unlocked) {
                // Tenta sem senha caso tenha falhado
                $unlocked = openssl_pkcs12_read($p12Content, $certs, '');
            }

            if (!$unlocked || empty($certs['cert']) || empty($certs['pkey'])) {
                throw new \RuntimeException('Não foi possível ler o certificado .p12 da Efí. Verifique se o arquivo está correto ou se requer senha.');
            }

            $pemContent = $certs['cert'] . "\n" . $certs['pkey'];
            if (!empty($certs['extracerts'])) {
                foreach ((array) $certs['extracerts'] as $extra) {
                    $pemContent .= "\n" . $extra;
                }
            }

            file_put_contents($finalPath, $pemContent);
            @chmod($finalPath, 0600);
        } else {
            // Arquivo .pem ou .crt direto
            $file->move($dir, 'certificado.pem');
            @chmod($finalPath, 0600);
        }

        Setting::set('efi_certificate_path', $finalPath, 'gateway');
        Setting::set('efi_certificate_filename', $originalName, 'gateway');

        return [
            'success' => true,
            'path' => $finalPath,
            'filename' => $originalName,
            'message' => 'Certificado Efí Pay processado e ativado com sucesso!',
        ];
    }

    /**
     * Retorna diagnóstico detalhado do certificado Efí ativo
     *
     * @return array<string, mixed>
     */
    public function getCertificateInfo(): array
    {
        $path = $this->certificatePath();
        $filename = (string) Setting::get('efi_certificate_filename', basename($path));

        if ($path === '' || !is_file($path)) {
            return [
                'exists' => false,
                'path' => $path,
                'filename' => null,
                'is_valid' => false,
                'message' => 'Nenhum certificado carregado.',
            ];
        }

        $info = [
            'exists' => true,
            'path' => $path,
            'filename' => $filename,
            'is_valid' => false,
            'valid_to' => null,
            'days_remaining' => null,
            'is_expired' => false,
            'subject' => null,
            'message' => 'Certificado presente no servidor.',
        ];

        try {
            $content = file_get_contents($path);
            $parsed = openssl_x509_parse($content);
            if (is_array($parsed) && !empty($parsed['validTo_time_t'])) {
                $validTo = $parsed['validTo_time_t'];
                $now = time();
                $daysRemaining = (int) floor(($validTo - $now) / 86400);

                $info['is_valid'] = true;
                $info['valid_to'] = date('d/m/Y H:i', $validTo);
                $info['days_remaining'] = $daysRemaining;
                $info['is_expired'] = $daysRemaining < 0;
                $info['subject'] = $parsed['subject']['CN'] ?? ($parsed['subject']['O'] ?? 'Efí Pay Client');
                $info['message'] = $info['is_expired']
                    ? "Certificado EXPIRADO em {$info['valid_to']}."
                    : "Certificado válido até {$info['valid_to']} ({$daysRemaining} dias restantes).";
            }
        } catch (\Throwable $e) {
            $info['message'] = 'Certificado presente, mas não foi possível extrair metadados X.509.';
        }

        return $info;
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
     * Realiza autenticação OAuth2 mTLS na API Pix da Efí Pro
     */
    public function requestAccessToken(?string $overrideClientId = null, ?string $overrideClientSecret = null, ?string $overrideEnv = null): array
    {
        $clientId = trim((string) ($overrideClientId ?: $this->clientId()));
        $clientSecret = trim((string) ($overrideClientSecret ?: $this->clientSecret()));
        $env = strtolower(trim((string) ($overrideEnv ?: $this->environment())));

        if ($clientId === '' || $clientSecret === '') {
            throw new \RuntimeException('Client ID e Client Secret da Efí Pro são obrigatórios.');
        }

        $baseUrl = $env === 'production'
            ? 'https://pix.api.efipay.com.br'
            : 'https://pix-h.api.efipay.com.br';

        $options = [
            'timeout' => 20,
        ];

        $certPath = $this->certificatePath();
        if ($certPath !== '' && is_file($certPath)) {
            $options['cert'] = $certPath;
            $options['ssl_key'] = $certPath;
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
            throw new \RuntimeException('Falha na autenticação Efí Pro: ' . $error);
        }

        return (array) $response->json();
    }

    /**
     * Cria cobrança Pix imediata na Efí Pay (/v2/cob/{txid})
     *
     * @param string $txid Identificador único da transação (alfanumérico, 26 a 35 caracteres)
     * @param float $value Valor da cobrança
     * @param string|null $description Mensagem para o pagador
     * @param array<string, string>|null $payer Dados do pagador ['cpf'|'cnpj' => '...', 'nome' => '...']
     * @param int $expirationSeconds Tempo de expiração em segundos (padrão: 3600s = 1h)
     * @return array<string, mixed>
     */
    public function createImmediatePixCharge(string $txid, float $value, ?string $description = null, ?array $payer = null, int $expirationSeconds = 3600): array
    {
        $tokenData = $this->requestAccessToken();
        $token = $tokenData['access_token'] ?? null;
        if (!$token) {
            throw new \RuntimeException('Token de acesso Efí não obtido.');
        }

        $payload = [
            'calendario' => [
                'expiracao' => $expirationSeconds,
            ],
            'valor' => [
                'original' => number_format($value, 2, '.', ''),
            ],
            'chave' => $this->pixKey(),
        ];

        if ($description) {
            $payload['solicitacaoPagador'] = mb_substr($description, 0, 140);
        }

        if (!empty($payer['cpf']) || !empty($payer['cnpj'])) {
            $payload['devedor'] = [
                'nome' => mb_substr($payer['nome'] ?? 'Cliente', 0, 200),
            ];
            if (!empty($payer['cpf'])) {
                $payload['devedor']['cpf'] = preg_replace('/\D/', '', $payer['cpf']);
            } elseif (!empty($payer['cnpj'])) {
                $payload['devedor']['cnpj'] = preg_replace('/\D/', '', $payer['cnpj']);
            }
        }

        $options = ['timeout' => 20];
        $certPath = $this->certificatePath();
        if ($certPath !== '' && is_file($certPath)) {
            $options['cert'] = $certPath;
            $options['ssl_key'] = $certPath;
        }

        $response = Http::withOptions($options)
            ->withToken($token)
            ->acceptJson()
            ->put($this->baseUrl() . '/v2/cob/' . $txid, $payload);

        if (!$response->successful()) {
            $msg = $response->json('mensagem') ?: $response->json('error_description') ?: ('HTTP ' . $response->status());
            throw new \RuntimeException('Falha ao gerar cobrança Pix na Efí: ' . $msg);
        }

        $cobData = $response->json();
        $locId = $cobData['loc']['id'] ?? null;

        // Se tiver locId, busca o QR Code e Copia e Cola
        $qrCodeData = [];
        if ($locId) {
            $qrCodeData = $this->getPixQrCode($locId, $token);
        }

        return array_merge($cobData, [
            'copia_e_cola' => $qrCodeData['qrcode'] ?? ($cobData['pixCopiaECola'] ?? null),
            'imagem_qrcode' => $qrCodeData['imagemQrcode'] ?? null,
        ]);
    }

    /**
     * Busca o QR Code e Copia e Cola de um locId da Efí (/v2/loc/{id}/qrcode)
     *
     * @param int $locId
     * @param string|null $token
     * @return array<string, mixed>
     */
    public function getPixQrCode(int $locId, ?string $token = null): array
    {
        if (!$token) {
            $tokenData = $this->requestAccessToken();
            $token = $tokenData['access_token'] ?? '';
        }

        $options = ['timeout' => 15];
        $certPath = $this->certificatePath();
        if ($certPath !== '' && is_file($certPath)) {
            $options['cert'] = $certPath;
            $options['ssl_key'] = $certPath;
        }

        $response = Http::withOptions($options)
            ->withToken($token)
            ->acceptJson()
            ->get($this->baseUrl() . "/v2/loc/{$locId}/qrcode");

        return $response->successful() ? (array) $response->json() : [];
    }

    /**
     * Consulta status de uma cobrança Pix (/v2/cob/{txid})
     */
    public function getPixCharge(string $txid): array
    {
        $tokenData = $this->requestAccessToken();
        $token = $tokenData['access_token'] ?? '';

        $options = ['timeout' => 15];
        $certPath = $this->certificatePath();
        if ($certPath !== '' && is_file($certPath)) {
            $options['cert'] = $certPath;
            $options['ssl_key'] = $certPath;
        }

        $response = Http::withOptions($options)
            ->withToken($token)
            ->acceptJson()
            ->get($this->baseUrl() . "/v2/cob/{$txid}");

        return $response->successful() ? (array) $response->json() : [];
    }

    /**
     * Consulta o saldo disponível na conta Efí Pay (/v2/gn/saldo).
     */
    public function getAccountBalance(): ?float
    {
        $tokenData = $this->requestAccessToken();
        $token = $tokenData['access_token'] ?? '';
        if ($token === '') {
            return null;
        }

        $options = ['timeout' => 15];
        $certPath = $this->certificatePath();
        if ($certPath !== '' && is_file($certPath)) {
            $options['cert'] = $certPath;
            $options['ssl_key'] = $certPath;
        }

        $response = Http::withOptions($options)
            ->withToken($token)
            ->acceptJson()
            ->get($this->baseUrl() . '/v2/gn/saldo');

        if (!$response->successful()) {
            Log::warning('EfiService: falha ao consultar saldo', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $saldo = $response->json('saldo');

        return is_numeric($saldo) ? (float) $saldo : null;
    }

    /**
     * Dispara um Pix Out (transferência automática) via API Efí Pay (PUT /v2/gn/pix/{idEnvio}).
     * Permite transferir automaticamente comissões para uma chave Pix externa (ex.: Mercado Pago).
     *
     * @return array{idEnvio:string,e2eId:?string,status:string,raw:array<string,mixed>}
     */
    public function sendPix(string $idEnvio, float $amount, string $targetPixKey, ?string $description = null): array
    {
        $cleanIdEnvio = preg_replace('/[^a-zA-Z0-9]/', '', $idEnvio) ?: ('UNN' . time());
        $cleanIdEnvio = substr($cleanIdEnvio, 0, 35);

        $payerKey = $this->pixKey();
        if ($payerKey === '') {
            throw new \RuntimeException('Chave Pix de origem (Efí Pay) não configurada.');
        }

        $targetPixKey = trim($targetPixKey);
        if ($targetPixKey === '') {
            throw new \RuntimeException('Chave Pix de destino do recebedor não informada.');
        }

        if ($amount <= 0) {
            throw new \RuntimeException('Valor inválido para envio de Pix automático.');
        }

        $tokenData = $this->requestAccessToken();
        $token = $tokenData['access_token'] ?? '';
        if ($token === '') {
            throw new \RuntimeException('Token de acesso Efí Pay não obtido.');
        }

        $payload = [
            'valor' => number_format($amount, 2, '.', ''),
            'pagador' => [
                'chave' => $payerKey,
            ],
            'favorecido' => [
                'chave' => $targetPixKey,
            ],
        ];

        if ($description !== null && trim($description) !== '') {
            $payload['pagador']['infoPagador'] = mb_substr(trim($description), 0, 140);
        }

        $options = ['timeout' => 25];
        $certPath = $this->certificatePath();
        if ($certPath !== '' && is_file($certPath)) {
            $options['cert'] = $certPath;
            $options['ssl_key'] = $certPath;
        }

        $response = Http::withOptions($options)
            ->withToken($token)
            ->acceptJson()
            ->put($this->baseUrl() . '/v2/gn/pix/' . $cleanIdEnvio, $payload);

        if (!$response->successful()) {
            $errorMsg = $response->json('mensagem')
                ?: $response->json('error_description')
                ?: $response->json('detail')
                ?: ('HTTP ' . $response->status());

            Log::error('EfiService: falha no envio de Pix Out', [
                'idEnvio' => $cleanIdEnvio,
                'amount' => $amount,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Falha no envio de Pix automático (Efí Pay): ' . $errorMsg);
        }

        $json = (array) $response->json();

        return [
            'idEnvio' => (string) ($json['idEnvio'] ?? $cleanIdEnvio),
            'e2eId' => isset($json['e2eId']) ? (string) $json['e2eId'] : null,
            'status' => (string) ($json['status'] ?? 'EM_PROCESSAMENTO'),
            'raw' => $json,
        ];
    }

    /**
     * Consulta o status de um Pix enviado pelo identificador endToEndId (/v2/pix/{e2eId}).
     */
    public function getPixSendStatus(string $e2eId): array
    {
        $tokenData = $this->requestAccessToken();
        $token = $tokenData['access_token'] ?? '';
        if ($token === '') {
            return [];
        }

        $options = ['timeout' => 15];
        $certPath = $this->certificatePath();
        if ($certPath !== '' && is_file($certPath)) {
            $options['cert'] = $certPath;
            $options['ssl_key'] = $certPath;
        }

        $response = Http::withOptions($options)
            ->withToken($token)
            ->acceptJson()
            ->get($this->baseUrl() . '/v2/pix/' . urlencode($e2eId));

        return $response->successful() ? (array) $response->json() : [];
    }
}
