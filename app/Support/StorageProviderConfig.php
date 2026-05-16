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
 * Sistema UNN - Value object representando a configuracao de
 * um provedor S3-compativel (IDrive e2, Wasabi, AWS S3).
 *
 * Spec: .kiro/specs/multi-provider-s3-storage (task 2.1)
 * Requirements: 1.1, 1.3, 3.5, 4.5
 */

namespace App\Support;

final class StorageProviderConfig
{
    /**
     * Os 7 campos que compoem a configuracao de um provedor.
     * Ordem definida pelo design.md (decisao 1).
     */
    public const FIELDS = [
        'access_key',
        'secret_key',
        'bucket',
        'region',
        'endpoint',
        'url',
        'path_style',
    ];

    /**
     * Os 4 campos minimos para que isValid() retorne true.
     * Conforme design.md: access_key + secret_key + bucket + region.
     */
    public const REQUIRED_FIELDS = [
        'access_key',
        'secret_key',
        'bucket',
        'region',
    ];

    public function __construct(
        public string $accessKey = '',
        public string $secretKey = '',
        public string $bucket = '',
        public string $region = '',
        public string $endpoint = '',
        public string $url = '',
        public bool $pathStyle = true,
    ) {
    }

    /**
     * Indica se a configuracao tem todos os campos obrigatorios
     * preenchidos. Whitespace-only e tratado como vazio.
     */
    public function isValid(): bool
    {
        return trim($this->accessKey) !== ''
            && trim($this->secretKey) !== ''
            && trim($this->bucket) !== ''
            && trim($this->region) !== '';
    }

    /**
     * Retorna o secret key mascarado para exibicao na UI.
     * Mantem apenas os ultimos 4 caracteres em texto claro.
     * Usado nas views AdminLTE e Tailwind (Req 3.5 / 4.5).
     */
    public function maskedSecret(): string
    {
        $secret = $this->secretKey;
        $length = strlen($secret);

        if ($length === 0) {
            return '';
        }

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($secret, -4);
    }

    /**
     * Constroi um Config a partir de um array indexado por chave
     * de settings (com prefixo). Tolera chaves ausentes.
     *
     * Ex.: fromSettings(['idrive_access_key' => 'X', ...], 'idrive_')
     *      retorna Config com accessKey='X' e demais vazios.
     *
     * @param  array<string, mixed>  $settings
     */
    public static function fromSettings(array $settings, string $prefix): self
    {
        return new self(
            accessKey: (string) ($settings[$prefix . 'access_key'] ?? ''),
            secretKey: (string) ($settings[$prefix . 'secret_key'] ?? ''),
            bucket: (string) ($settings[$prefix . 'bucket'] ?? ''),
            region: (string) ($settings[$prefix . 'region'] ?? ''),
            endpoint: (string) ($settings[$prefix . 'endpoint'] ?? ''),
            url: (string) ($settings[$prefix . 'url'] ?? ''),
            pathStyle: self::asBool($settings[$prefix . 'path_style'] ?? true),
        );
    }

    /**
     * Retorna o array de chave/valor pronto para persistir na
     * tabela settings, com o prefixo do provedor.
     *
     * Ex.: toSettingsArray('idrive_') retorna
     *      ['idrive_access_key' => '...', 'idrive_secret_key' => '...', ...]
     *
     * @return array<string, string>
     */
    public function toSettingsArray(string $prefix): array
    {
        return [
            $prefix . 'access_key' => $this->accessKey,
            $prefix . 'secret_key' => $this->secretKey,
            $prefix . 'bucket' => $this->bucket,
            $prefix . 'region' => $this->region,
            $prefix . 'endpoint' => $this->endpoint,
            $prefix . 'url' => $this->url,
            $prefix . 'path_style' => $this->pathStyle ? '1' : '0',
        ];
    }

    /**
     * Normaliza valores variados (string, int, bool) para boolean
     * conforme convencao usada na tabela settings ('1'/'0', 'true'/'false').
     */
    private static function asBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value !== 0;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }
}
