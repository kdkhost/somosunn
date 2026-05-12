<?php

namespace App\Services\Waf\Scanners;

/**
 * Scanner de cabecalhos de seguranca.
 *
 * Verifica se a aplicacao define cabecalhos de seguranca na cadeia
 * de resposta (via middleware dedicado ou via config HTTP).
 *
 * Cabecalhos cobertos:
 *   - Content-Security-Policy
 *   - Strict-Transport-Security (HSTS)
 *   - X-Frame-Options
 *   - X-Content-Type-Options
 *   - Referrer-Policy
 *   - Permissions-Policy
 *   - Cross-Origin-Opener-Policy
 *   - Cross-Origin-Resource-Policy
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 1.11, 18.1-18.7
 */
class HeaderScanner extends AbstractScanner
{
    private int $counter = 0;

    private const REQUIRED_HEADERS = [
        'Content-Security-Policy',
        'Strict-Transport-Security',
        'X-Frame-Options',
        'X-Content-Type-Options',
        'Referrer-Policy',
        'Permissions-Policy',
        'Cross-Origin-Opener-Policy',
        'Cross-Origin-Resource-Policy',
    ];

    public function id(): string
    {
        return 'headers';
    }

    public function label(): string
    {
        return 'Cabecalhos de seguranca ausentes na cadeia de resposta';
    }

    public function scan(AuditContext $ctx): iterable
    {
        // Consolida o conteudo dos middlewares e service providers
        $middlewareDir = $ctx->abs('app/Http/Middleware');
        $providerDir   = $ctx->abs('app/Providers');

        $aggregated = '';

        foreach ([$middlewareDir, $providerDir] as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($it as $file) {
                /** @var \SplFileInfo $file */
                if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
                    $aggregated .= "\n\n" . (@file_get_contents($file->getPathname()) ?: '');
                }
            }
        }

        // Tambem considera .htaccess na raiz e public/
        foreach (['.htaccess', 'public/.htaccess'] as $hta) {
            $abs = $ctx->abs($hta);
            if (is_file($abs)) {
                $aggregated .= "\n\n" . (@file_get_contents($abs) ?: '');
            }
        }

        // Tambem considera config/cors.php e config/session.php
        foreach (['config/cors.php', 'config/session.php'] as $cfg) {
            $abs = $ctx->abs($cfg);
            if (is_file($abs)) {
                $aggregated .= "\n\n" . (@file_get_contents($abs) ?: '');
            }
        }

        foreach (self::REQUIRED_HEADERS as $header) {
            $escaped = preg_quote($header, '/');

            if (preg_match('/' . $escaped . '/i', $aggregated) === 0) {
                $this->counter++;

                yield new AuditFinding(
                    id:              sprintf('SEC-HDR-%04d', $this->counter),
                    category:        'SEC-HEADERS',
                    severity:        $this->severityFor($header),
                    area:            'Headers',
                    title:           sprintf('Cabecalho `%s` ausente na cadeia de resposta', $header),
                    recommendation:  $this->recommendationFor($header),
                    file:            null,
                    line:            null,
                    context:         null,
                    wafMitigable:    false,
                    compensatingControl: 'Fase 5 da spec adiciona SecurityHeadersMiddleware com todos os cabecalhos.',
                    deadline:        AuditFinding::defaultDeadline($this->severityFor($header)),
                );
            }
        }

        // Detecta X-Powered-By nao removido
        if (! preg_match('/header_remove\(\s*[\'\"]X-Powered-By[\'\"]\s*\)/i', $aggregated)
            && ! preg_match('/Header\s+unset\s+X-Powered-By/i', $aggregated)
        ) {
            $this->counter++;

            yield new AuditFinding(
                id:              sprintf('SEC-HDR-%04d', $this->counter),
                category:        'SEC-HEADERS',
                severity:        AuditFinding::SEVERITY_LOW,
                area:            'Headers',
                title:           'X-Powered-By nao removido explicitamente',
                recommendation:  'Chamar `header_remove("X-Powered-By")` no middleware de seguranca ou remover via Apache/Nginx. Reduz informacao exposta sobre versao de PHP.',
                file:            null,
                line:            null,
                context:         null,
                wafMitigable:    false,
                deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_LOW),
            );
        }
    }

    private function severityFor(string $header): string
    {
        return match ($header) {
            'Content-Security-Policy', 'Strict-Transport-Security', 'X-Frame-Options' => AuditFinding::SEVERITY_HIGH,
            'X-Content-Type-Options', 'Referrer-Policy', 'Permissions-Policy' => AuditFinding::SEVERITY_MEDIUM,
            default => AuditFinding::SEVERITY_LOW,
        };
    }

    private function recommendationFor(string $header): string
    {
        return match ($header) {
            'Content-Security-Policy'     => 'Definir CSP com `default-src \'self\'`, `script-src` restrito, `object-src \'none\'` e `frame-ancestors \'self\'`. Preferir nonce ou hash para scripts inline.',
            'Strict-Transport-Security'   => 'Definir HSTS com `max-age=15552000; includeSubDomains`. Opcional: `preload` apos confirmar HTTPS em todo o dominio.',
            'X-Frame-Options'             => 'Definir `SAMEORIGIN` ou usar CSP `frame-ancestors`. Proteger contra clickjacking.',
            'X-Content-Type-Options'      => 'Definir `nosniff` para evitar MIME sniffing pelo navegador.',
            'Referrer-Policy'             => 'Definir `strict-origin-when-cross-origin` para limitar vazamento de URL via Referer.',
            'Permissions-Policy'          => 'Restringir `camera`, `microphone`, `geolocation`, `payment` conforme necessidade documentada de cada rota.',
            'Cross-Origin-Opener-Policy'  => 'Definir `same-origin` para isolar o contexto de navegacao.',
            'Cross-Origin-Resource-Policy'=> 'Definir `same-site` como default; flexibilizar por rota apenas quando necessario.',
            default                       => sprintf('Adicionar %s no middleware de seguranca conforme boas praticas.', $header),
        };
    }
}
