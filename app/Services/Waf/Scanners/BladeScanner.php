<?php

namespace App\Services\Waf\Scanners;

/**
 * Scanner de templates Blade.
 *
 * Busca por {!! $var !!} (echo sem escape) em views, classificando:
 *   - CRITICA quando $var vem aparentemente de entrada do usuario
 *     (request(), old(), Input::, $_GET, $_POST, $request->)
 *   - ALTA quando a variavel tem nome suspeito (content, html, description,
 *     bio, message, body, comment) que frequentemente carrega texto livre
 *   - MEDIA caso geral (pode ser legitimo se o controller sanitiza, mas
 *     precisa ser revisado)
 *
 * Tambem flaga @php ... @endphp com funcoes perigosas (eval, shell_exec)
 * dentro de Blade.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 1.3, 3.3, 3.4
 */
class BladeScanner extends AbstractScanner
{
    private int $counter = 0;

    /** Variaveis cujo nome sugere conteudo livre do usuario. */
    private const HIGH_RISK_NAMES = [
        'content', 'body', 'html', 'message', 'description',
        'bio', 'comment', 'text', 'post', 'review', 'answer',
        'question', 'note', 'observacao', 'descricao',
    ];

    /** Padroes que fortemente sugerem entrada direta do usuario. */
    private const USER_INPUT_SIGNS = [
        'request(',
        'request()->',
        'old(',
        '$_GET',
        '$_POST',
        '$_REQUEST',
        'Input::',
        'Request::',
        '$request->',
    ];

    public function id(): string
    {
        return 'blade';
    }

    public function label(): string
    {
        return 'Blade - {!! !!} sem escape, @php com funcoes perigosas';
    }

    public function scan(AuditContext $ctx): iterable
    {
        foreach ($this->iterateFiles($ctx, ['.blade.php'], ['resources/views']) as $file) {
            $absPath = $file->getPathname();
            $rel     = $ctx->rel($absPath);

            $content = @file_get_contents($absPath);

            if ($content === false || $content === '') {
                continue;
            }

            yield from $this->scanUnescapedEchoes($absPath, $rel, $content);
            yield from $this->scanInlinePhp($absPath, $rel, $content);
        }
    }

    /** Detecta {!! $var !!} e variantes. */
    private function scanUnescapedEchoes(string $absPath, string $rel, string $content): iterable
    {
        // Padrao simples: {!! ... !!}
        if (preg_match_all('/\{!!(.*?)!!}/s', $content, $matches, PREG_OFFSET_CAPTURE) === false) {
            return;
        }

        foreach ($matches[1] as $match) {
            [$expression, $offset] = $match;
            $line = substr_count(substr($content, 0, $offset), "\n") + 1;
            $expr = trim($expression);

            // Ignora casos claramente seguros (usos conhecidos):
            //   {!! __() !!} para traducoes com HTML controlado
            //   {!! csrf_field() !!} / method_field() / @csrf
            //   {!! Markdown::convert($var) !!} com sanitizacao conhecida
            //   {!! e($var) !!} (escape explicito redundante)
            if (preg_match('/^(__\s*\(|csrf_field\s*\(|method_field\s*\(|e\s*\(|asset\s*\(|route\s*\(|url\s*\(|trans\s*\(|\@)/i', $expr)) {
                continue;
            }

            $severity        = AuditFinding::SEVERITY_MEDIUM;
            $looksLikeInput  = false;
            foreach (self::USER_INPUT_SIGNS as $sign) {
                if (stripos($expr, $sign) !== false) {
                    $severity       = AuditFinding::SEVERITY_CRITICAL;
                    $looksLikeInput = true;
                    break;
                }
            }

            if (! $looksLikeInput) {
                foreach (self::HIGH_RISK_NAMES as $name) {
                    if (stripos($expr, '$' . $name) !== false) {
                        $severity = AuditFinding::SEVERITY_HIGH;
                        break;
                    }
                }
            }

            $this->counter++;

            yield new AuditFinding(
                id:                  sprintf('SEC-XSS-%04d', $this->counter),
                category:            'SEC-XSS',
                severity:            $severity,
                area:                $this->areaFromPath($rel),
                title:               'Blade echo sem escape: {!! ... !!}',
                recommendation:      'Preferir {{ $var }} (escape automatico). Para conteudo WYSIWYG, sanitizar com allowlist (ex.: HTMLPurifier) antes de gravar e apenas renderizar HTML sanitizado.',
                file:                $rel,
                line:                $line,
                context:             $this->excerpt($absPath, $line),
                wafMitigable:        true,
                compensatingControl: $looksLikeInput ? 'Ate corrigir, garantir CSP restritivo e regra WAF XSS ativa em detection-only.' : null,
                deadline:            AuditFinding::defaultDeadline($severity),
            );
        }
    }

    /** Detecta @php ... @endphp com funcoes perigosas. */
    private function scanInlinePhp(string $absPath, string $rel, string $content): iterable
    {
        if (preg_match_all('/@php(.*?)@endphp/s', $content, $matches, PREG_OFFSET_CAPTURE) === false) {
            return;
        }

        foreach ($matches[1] as $match) {
            [$body, $offset] = $match;
            $line = substr_count(substr($content, 0, $offset), "\n") + 1;

            if (preg_match('/\b(eval|shell_exec|exec|passthru|system|proc_open|popen|assert)\s*\(/i', $body, $m)) {
                $this->counter++;

                yield new AuditFinding(
                    id:              sprintf('SEC-BLADE-INLINE-%04d', $this->counter),
                    category:        'SEC-RCE',
                    severity:        AuditFinding::SEVERITY_CRITICAL,
                    area:            $this->areaFromPath($rel),
                    title:           sprintf('@php inline em Blade usa funcao perigosa (%s)', strtolower($m[1])),
                    recommendation:  'Mover a logica para um controller ou service PHP; nao executar funcoes de sistema dentro de Blade.',
                    file:            $rel,
                    line:            $line,
                    context:         $this->excerpt($absPath, $line),
                    wafMitigable:    false,
                    deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_CRITICAL),
                );
            }
        }
    }
}
