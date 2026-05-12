<?php

namespace App\Services\Waf;

use Illuminate\Http\Response;

/**
 * ChallengeManager - constroi respostas de desafio para requisicoes
 * classificadas como `challenged`.
 *
 * Implementa JS cookie challenge basico. Captcha/tarpit podem ser
 * adicionados em evolucoes.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 7.3, 10.6
 */
final class ChallengeManager
{
    private const COOKIE_NAME = 'waf_challenge';

    /**
     * Constroi a resposta de desafio (HTML minimalista com JS que seta
     * cookie de verificacao e recarrega a pagina).
     */
    public function buildResponse(WafContext $ctx): Response
    {
        $token = bin2hex(random_bytes(16));

        $html = <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Verificando...</title>
<meta name="robots" content="noindex">
</head>
<body>
<noscript>
<p>Esta pagina precisa de JavaScript para continuar.</p>
</noscript>
<p>Verificando conexao, por favor aguarde...</p>
<script>
document.cookie = 'waf_challenge={$token}; Path=/; Max-Age=600; SameSite=Lax';
setTimeout(function () { window.location.reload(); }, 1200);
</script>
</body>
</html>
HTML;

        return new Response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * Verifica se o cliente ja passou pelo desafio nesta sessao.
     */
    public function hasValidChallenge(WafContext $ctx): bool
    {
        return isset($ctx->cookies[self::COOKIE_NAME])
            && strlen($ctx->cookies[self::COOKIE_NAME]) === 32;
    }
}
