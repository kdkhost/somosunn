<?php

namespace App\Services\Waf;

/**
 * Mascara dados sensiveis antes de persistir em WafEvent/samples.
 *
 * Invariante (Property 18): para toda string s com token sensivel t,
 *   maskString(s) NAO contem t literal.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 12.2
 */
final class SensitiveDataMasker
{
    public function __construct(
        private readonly bool $maskEmails = false,
        private readonly bool $maskPans   = true,
        private readonly bool $maskCpf    = true,
        private readonly bool $maskCnpj   = true,
    ) {}

    public static function fromConfig(): self
    {
        $m = (array) config('waf.masking', []);

        return new self(
            maskEmails: (bool) ($m['mask_emails'] ?? false),
            maskPans:   (bool) ($m['mask_pans']   ?? true),
            maskCpf:    (bool) ($m['mask_cpf']    ?? true),
            maskCnpj:   (bool) ($m['mask_cnpj']   ?? true),
        );
    }

    /**
     * Mascara uma string livre. Retorna sempre com ***.
     */
    public function maskString(string $input): string
    {
        if ($input === '') {
            return $input;
        }

        // Authorization: Bearer xxx / Basic xxx
        $input = preg_replace(
            '/(authorization\s*:\s*(?:bearer|basic|token)\s+)[^\s,"\']+/i',
            '$1***',
            $input
        ) ?? $input;

        // Cookie laravel_session=... / XSRF-TOKEN=...
        $input = preg_replace(
            '/((?:laravel_session|xsrf-token|remember_web_[^=]+)\s*=\s*)[^;\s,"\'&]+/i',
            '$1***',
            $input
        ) ?? $input;

        // password=... / senha=...
        $input = preg_replace(
            '/((?:password|senha|password_confirmation|senha_confirmacao)\s*[:=]\s*)"?[^"\s,&}]+"?/i',
            '$1"***"',
            $input
        ) ?? $input;

        // JSON: "password":"..." / "token":"..."
        $input = preg_replace(
            '/"((?:password|senha|token|secret|api_?key|access_?token|refresh_?token|client_?secret))"\s*:\s*"[^"]*"/i',
            '"$1":"***"',
            $input
        ) ?? $input;

        // PAN (cartao de credito com Luhn)
        if ($this->maskPans) {
            $input = $this->maskPans($input);
        }

        // CPF (xxx.xxx.xxx-xx ou 11 digitos)
        if ($this->maskCpf) {
            $input = preg_replace_callback(
                '/\b(\d{3})\.?(\d{3})\.?(\d{3})-?(\d{2})\b/',
                function ($m) {
                    $digits = $m[1] . $m[2] . $m[3] . $m[4];
                    return $this->cpfValid($digits) ? '***.***.***-**' : $m[0];
                },
                $input
            ) ?? $input;
        }

        // CNPJ
        if ($this->maskCnpj) {
            $input = preg_replace_callback(
                '/\b(\d{2})\.?(\d{3})\.?(\d{3})\/?(\d{4})-?(\d{2})\b/',
                function ($m) {
                    $digits = $m[1] . $m[2] . $m[3] . $m[4] . $m[5];
                    return $this->cnpjValid($digits) ? '**.***.***/****-**' : $m[0];
                },
                $input
            ) ?? $input;
        }

        // Email (opcional)
        if ($this->maskEmails) {
            $input = preg_replace(
                '/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i',
                '***@***',
                $input
            ) ?? $input;
        }

        return $input;
    }

    /**
     * Mascara array recursivamente - chaves comuns sensiveis ganham valor ***.
     */
    public function maskArray(array $data): array
    {
        $sensitiveKeys = [
            'password', 'senha', 'password_confirmation', 'senha_confirmacao',
            'token', 'api_token', 'access_token', 'refresh_token',
            'secret', 'client_secret', 'api_key', 'webhook_secret',
            'authorization', 'cookie', 'set-cookie',
            'xsrf-token', '_token',
        ];

        $out = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $out[$key] = $this->maskArray($value);
                continue;
            }

            $k = strtolower((string) $key);

            if (in_array($k, $sensitiveKeys, true)) {
                $out[$key] = '***';
                continue;
            }

            if (is_string($value)) {
                $out[$key] = $this->maskString($value);
                continue;
            }

            $out[$key] = $value;
        }

        return $out;
    }

    /**
     * Trunca a string em no maximo $maxBytes, ja mascarada.
     */
    public function truncated(string $input, int $maxBytes): string
    {
        $masked = $this->maskString($input);

        if (strlen($masked) <= $maxBytes) {
            return $masked;
        }

        return substr($masked, 0, max(0, $maxBytes - 3)) . '...';
    }

    /* ================================================================ */

    private function maskPans(string $input): string
    {
        return preg_replace_callback(
            '/\b(?:\d[ \-]?){13,19}\b/',
            function ($m) {
                $digits = preg_replace('/\D/', '', $m[0]);
                if (strlen($digits) < 13 || strlen($digits) > 19) {
                    return $m[0];
                }
                if (! $this->luhnValid($digits)) {
                    return $m[0];
                }
                return str_repeat('*', strlen($digits) - 4) . substr($digits, -4);
            },
            $input
        ) ?? $input;
    }

    private function luhnValid(string $digits): bool
    {
        $sum = 0;
        $alt = false;

        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $n = (int) $digits[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) $n -= 9;
            }
            $sum += $n;
            $alt = ! $alt;
        }

        return ($sum % 10) === 0;
    }

    private function cpfValid(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += ((int) $cpf[$c]) * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;

            if ((int) $cpf[$c] !== $d) {
                return false;
            }
        }

        return true;
    }

    private function cnpjValid(string $cnpj): bool
    {
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }

        $calc = function (string $n, int $len): int {
            $w = $len - 7;
            $s = 0;
            for ($i = $len; $i >= 1; $i--) {
                $s += ((int) $n[$len - $i]) * $w--;
                if ($w < 2) $w = 9;
            }
            $r = $s % 11;
            return $r < 2 ? 0 : 11 - $r;
        };

        $d1 = $calc($cnpj, 12);
        $d2 = $calc($cnpj, 13);

        return (int) $cnpj[12] === $d1 && (int) $cnpj[13] === $d2;
    }
}
