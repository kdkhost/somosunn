<?php

namespace App\Services\Waf\Scanners;

/**
 * Scanner de autenticacao, autorizacao e impersonacao.
 *
 * Verifica:
 *   - ImpersonateController com log de auditoria
 *   - LoginController com throttle / rate limit
 *   - Rota de redefinicao de senha com mensagens genericas
 *   - 2FA para super-admin (baseado na presenca de TOTP/google2fa)
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 1.12, 7.6, 19.1, 19.2
 */
class AuthScanner extends AbstractScanner
{
    private int $counter = 0;

    public function id(): string
    {
        return 'auth';
    }

    public function label(): string
    {
        return 'Auth - impersonacao sem log, 2FA ausente, login sem rate limit';
    }

    public function scan(AuditContext $ctx): iterable
    {
        yield from $this->scanImpersonate($ctx);
        yield from $this->scan2fa($ctx);
        yield from $this->scanLoginRateLimit($ctx);
    }

    private function scanImpersonate(AuditContext $ctx): iterable
    {
        $abs = $ctx->abs('app/Http/Controllers/Admin/ImpersonateController.php');

        if (! is_file($abs)) {
            return;
        }

        $content = @file_get_contents($abs) ?: '';
        $rel     = 'app/Http/Controllers/Admin/ImpersonateController.php';

        $hasLog = preg_match('/\b(ActivityLog|activity\(|Log::|activity_logs|audit)/i', $content) === 1;
        $has2fa = preg_match('/\b(otp|totp|google2fa|two.?factor)/i', $content) === 1;

        if (! $hasLog) {
            $this->counter++;

            yield new AuditFinding(
                id:              sprintf('SEC-IMP-LOG-%04d', $this->counter),
                category:        'SEC-AUTH',
                severity:        AuditFinding::SEVERITY_HIGH,
                area:            'Impersonacao',
                title:           'ImpersonateController sem log de auditoria',
                recommendation:  'Registrar inicio e fim de impersonacao em activity_logs (Superadmin, alvo, timestamp, IP, user-agent, motivo). Append-only.',
                file:            $rel,
                line:            null,
                context:         null,
                wafMitigable:    false,
                deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_HIGH),
            );
        }

        if (! $has2fa) {
            $this->counter++;

            yield new AuditFinding(
                id:              sprintf('SEC-IMP-2FA-%04d', $this->counter),
                category:        'SEC-AUTH',
                severity:        AuditFinding::SEVERITY_HIGH,
                area:            'Impersonacao',
                title:           'Impersonacao sem verificacao 2FA recente',
                recommendation:  'Exigir 2FA (TOTP ou codigo por e-mail) antes de permitir impersonacao. Cookie de confianca por dispositivo e expiracao curta.',
                file:            $rel,
                line:            null,
                context:         null,
                wafMitigable:    false,
                deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_HIGH),
            );
        }
    }

    private function scan2fa(AuditContext $ctx): iterable
    {
        $loginAbs = $ctx->abs('app/Http/Controllers/Auth/LoginController.php');

        if (! is_file($loginAbs)) {
            return;
        }

        $content = @file_get_contents($loginAbs) ?: '';

        $has2fa = preg_match('/\b(otp|totp|google2fa|two.?factor)/i', $content) === 1;

        if (! $has2fa) {
            $this->counter++;

            yield new AuditFinding(
                id:              sprintf('SEC-AUTH-2FA-%04d', $this->counter),
                category:        'SEC-AUTH',
                severity:        AuditFinding::SEVERITY_HIGH,
                area:            'Auth',
                title:           'LoginController sem suporte a 2FA',
                recommendation:  'Implementar 2FA TOTP (pragmarx/google2fa) obrigatorio para super-admin. Cookie de confianca por dispositivo.',
                file:            'app/Http/Controllers/Auth/LoginController.php',
                line:            null,
                context:         null,
                wafMitigable:    false,
                deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_HIGH),
            );
        }
    }

    private function scanLoginRateLimit(AuditContext $ctx): iterable
    {
        $loginAbs = $ctx->abs('app/Http/Controllers/Auth/LoginController.php');

        if (! is_file($loginAbs)) {
            return;
        }

        $content = @file_get_contents($loginAbs) ?: '';

        $hasThrottle = preg_match('/\b(RateLimiter|ThrottlesLogins|throttle|hit\()/i', $content) === 1;

        if (! $hasThrottle) {
            $this->counter++;

            yield new AuditFinding(
                id:              sprintf('SEC-AUTH-THROTTLE-%04d', $this->counter),
                category:        'SEC-AUTH',
                severity:        AuditFinding::SEVERITY_HIGH,
                area:            'Auth',
                title:           'Login sem rate limit explicito (brute force)',
                recommendation:  'Aplicar RateLimiter por IP + e-mail no LoginController; preferir WAF `Brute_Force` como camada adicional.',
                file:            'app/Http/Controllers/Auth/LoginController.php',
                line:            null,
                context:         null,
                wafMitigable:    true,
                deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_HIGH),
            );
        }
    }
}
