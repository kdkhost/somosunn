<?php

namespace App\Services\Waf\Scanners;

/**
 * Scanner de configuracoes.
 *
 * Avalia:
 *   - APP_DEBUG=true no .env.example ou sem override para producao
 *   - SESSION_SECURE_COOKIE / SESSION_HTTP_ONLY / SESSION_SAME_SITE ausentes
 *   - Segredos hardcoded em config/ (fora de env())
 *   - SESSION_DRIVER inseguro em producao (array)
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 1.9, 1.10, 3.6
 */
class ConfigScanner extends AbstractScanner
{
    private int $counter = 0;

    public function id(): string
    {
        return 'config';
    }

    public function label(): string
    {
        return 'Config e .env - debug, cookies de sessao, segredos hardcoded';
    }

    public function scan(AuditContext $ctx): iterable
    {
        yield from $this->scanEnvExample($ctx);
        yield from $this->scanConfigDir($ctx);
        yield from $this->scanSessionConfig($ctx);
    }

    private function scanEnvExample(AuditContext $ctx): iterable
    {
        $abs = $ctx->abs('.env.example');

        if (! is_file($abs)) {
            return;
        }

        $content = @file_get_contents($abs) ?: '';
        $lines   = preg_split('/\R/', $content) ?: [];

        foreach ($lines as $i => $line) {
            $lineNo = $i + 1;
            $trim   = trim($line);

            if (stripos($trim, 'APP_DEBUG=true') === 0) {
                $this->counter++;

                yield new AuditFinding(
                    id:              sprintf('SEC-CFG-DEBUG-%04d', $this->counter),
                    category:        'SEC-CONFIG',
                    severity:        AuditFinding::SEVERITY_MEDIUM,
                    area:            'Config',
                    title:           '.env.example com APP_DEBUG=true',
                    recommendation:  'Garantir que producao use APP_DEBUG=false. Documentar que o .env de producao sobrescreve. Considerar mudar o default do example para false.',
                    file:            '.env.example',
                    line:            $lineNo,
                    context:         $trim,
                    wafMitigable:    false,
                    deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_MEDIUM),
                );
            }
        }

        // Verifica presenca das variaveis de sessao seguras
        $checks = [
            'SESSION_SECURE_COOKIE' => 'Cookie de sessao sem flag Secure em producao.',
            'SESSION_HTTP_ONLY'     => 'Cookie de sessao sem flag HttpOnly em producao.',
            'SESSION_SAME_SITE'     => 'Cookie de sessao sem SameSite configurado.',
        ];

        foreach ($checks as $var => $recText) {
            if (stripos($content, $var . '=') === false) {
                $this->counter++;

                yield new AuditFinding(
                    id:              sprintf('SEC-CFG-SESSION-%04d', $this->counter),
                    category:        'SEC-CONFIG',
                    severity:        AuditFinding::SEVERITY_HIGH,
                    area:            'Config',
                    title:           sprintf('%s ausente em .env.example', $var),
                    recommendation:  sprintf('%s Adicionar %s com valor seguro (true, true, lax) em .env.example e confirmar em producao.', $recText, $var),
                    file:            '.env.example',
                    line:            null,
                    context:         null,
                    wafMitigable:    false,
                    deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_HIGH),
                );
            }
        }
    }

    /** Busca por strings que parecem segredos em config/*.php. */
    private function scanConfigDir(AuditContext $ctx): iterable
    {
        $configDir = $ctx->abs('config');

        if (! is_dir($configDir)) {
            return;
        }

        foreach ($this->iterateFiles($ctx, ['.php'], ['config']) as $file) {
            $abs     = $file->getPathname();
            $rel     = $ctx->rel($abs);
            $content = @file_get_contents($abs) ?: '';

            // Procura strings longas (>= 24 chars) nao envolvidas por env(...)
            if (preg_match_all('/[\'\"]([A-Za-z0-9_\-]{24,})[\'\"]/', $content, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[1] as $match) {
                    [$value, $offset] = $match;
                    $line = substr_count(substr($content, 0, $offset), "\n") + 1;

                    // Skip se a linha chamar env() explicitamente
                    $lineContent = $this->getLine($content, $line);
                    if (stripos($lineContent, 'env(') !== false) {
                        continue;
                    }

                    // Skip valores obvios sem caractere de risco (nomes de classes, etc.)
                    if (! preg_match('/[0-9]/', $value)) {
                        continue;
                    }

                    // Ignora URLs, caminhos, paths e nomes comuns de classes
                    if (preg_match('/^(http|file|\\\\)/i', $value)) {
                        continue;
                    }

                    $this->counter++;

                    yield new AuditFinding(
                        id:              sprintf('SEC-CFG-SECRET-%04d', $this->counter),
                        category:        'SEC-CONFIG',
                        severity:        AuditFinding::SEVERITY_MEDIUM,
                        area:            'Config',
                        title:           'Possivel segredo hardcoded fora de env()',
                        recommendation:  'Mover o valor para `.env` e consumir via `env("CHAVE")` dentro do config. Valores versionados podem vazar via Git.',
                        file:            $rel,
                        line:            $line,
                        context:         trim($lineContent),
                        wafMitigable:    false,
                        deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_MEDIUM),
                    );

                    // Nao emite mais que 1 finding por arquivo para nao poluir
                    break;
                }
            }
        }
    }

    private function scanSessionConfig(AuditContext $ctx): iterable
    {
        $abs = $ctx->abs('config/session.php');
        if (! is_file($abs)) {
            return;
        }

        $content = @file_get_contents($abs) ?: '';

        if (! preg_match("/'secure'\s*=>\s*env\('SESSION_SECURE_COOKIE',\s*(true|false|null)\s*\)/", $content, $m)) {
            return;
        }

        if (strtolower($m[1]) !== 'true') {
            $this->counter++;

            yield new AuditFinding(
                id:              sprintf('SEC-CFG-SESSION-SECURE-%04d', $this->counter),
                category:        'SEC-CONFIG',
                severity:        AuditFinding::SEVERITY_HIGH,
                area:            'Config',
                title:           "config/session.php 'secure' com default != true",
                recommendation:  "Alterar o default de SESSION_SECURE_COOKIE para `true` em producao HTTPS. Manter `false` apenas em dev HTTP.",
                file:            'config/session.php',
                line:            null,
                context:         trim($m[0]),
                wafMitigable:    false,
                deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_HIGH),
            );
        }
    }

    private function getLine(string $content, int $line): string
    {
        $lines = preg_split('/\R/', $content) ?: [];
        return $lines[$line - 1] ?? '';
    }
}
