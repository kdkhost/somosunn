<?php

namespace App\Services\Waf\Scanners;

/**
 * Consolida uma lista de AuditFindings em Markdown e JSON.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 23.1, 23.2, 23.4, 23.5
 */
class AuditReportBuilder
{
    /**
     * @param array<AuditFinding> $findings
     */
    public function toMarkdown(array $findings, array $meta = []): string
    {
        // Ordena por severidade (desc) e depois por area
        usort(
            $findings,
            fn (AuditFinding $a, AuditFinding $b) => ($b->severityWeight() <=> $a->severityWeight())
                ?: strcmp($a->area, $b->area)
        );

        $total = count($findings);
        $bySev = $this->countBy($findings, fn ($f) => $f->severity);
        $byArea = $this->countBy($findings, fn ($f) => $f->area);
        $wafMitigable = count(array_filter($findings, fn ($f) => $f->wafMitigable));

        $lines = [];
        $lines[] = '# Relatorio de Auditoria de Seguranca — Unn';
        $lines[] = '';
        $lines[] = sprintf('- **Data:** %s', $meta['generated_at'] ?? now()->toDateTimeString());
        $lines[] = sprintf('- **Total de findings:** %d', $total);
        $lines[] = sprintf('- **Criticos:** %d  |  **Altos:** %d  |  **Medios:** %d  |  **Baixos:** %d  |  **Info:** %d',
            $bySev['critical'] ?? 0,
            $bySev['high']     ?? 0,
            $bySev['medium']   ?? 0,
            $bySev['low']      ?? 0,
            $bySev['info']     ?? 0
        );
        $lines[] = sprintf('- **Mitigaveis pelo WAF:** %d (%.1f%%)',
            $wafMitigable,
            $total > 0 ? ($wafMitigable / $total) * 100 : 0
        );
        $lines[] = '';
        $lines[] = '> Spec: `.kiro/specs/waf-e-auditoria-seguranca/`';
        $lines[] = '> Gerado por: `php artisan security:audit`';
        $lines[] = '';

        $lines[] = '## Resumo por Area';
        $lines[] = '';
        $lines[] = '| Area | Total |';
        $lines[] = '|------|-------|';
        foreach ($byArea as $area => $count) {
            $lines[] = sprintf('| %s | %d |', $area, $count);
        }
        $lines[] = '';

        $lines[] = '## Resumo por Severidade';
        $lines[] = '';
        $lines[] = '| Severidade | Prazo alvo | Total |';
        $lines[] = '|------------|------------|-------|';
        foreach (['critical' => 'imediato', 'high' => '7d', 'medium' => '30d', 'low' => '90d', 'info' => 'backlog'] as $sev => $deadline) {
            $lines[] = sprintf('| %s | %s | %d |', strtoupper($sev), $deadline, $bySev[$sev] ?? 0);
        }
        $lines[] = '';

        $lines[] = '## Findings Detalhados';
        $lines[] = '';

        // Agrupa por severidade
        $grouped = [];
        foreach ($findings as $f) {
            $grouped[$f->severity][] = $f;
        }

        foreach (['critical', 'high', 'medium', 'low', 'info'] as $sev) {
            if (empty($grouped[$sev])) {
                continue;
            }

            $lines[] = sprintf('### %s (%d)', strtoupper($sev), count($grouped[$sev]));
            $lines[] = '';

            foreach ($grouped[$sev] as $f) {
                $lines[] = sprintf('#### %s — %s', $f->id, $f->title);
                $lines[] = '';
                $lines[] = sprintf('- **Area:** %s', $f->area);
                $lines[] = sprintf('- **Categoria:** %s', $f->category);
                $lines[] = sprintf('- **Prazo alvo:** %s', $f->deadline ?? AuditFinding::defaultDeadline($f->severity));
                $lines[] = sprintf('- **Mitigavel pelo WAF:** %s', $f->wafMitigable ? 'sim' : 'nao');

                if ($f->compensatingControl) {
                    $lines[] = sprintf('- **Mitigacao compensatoria:** %s', $f->compensatingControl);
                }

                if ($f->file) {
                    $lineInfo = $f->line ? (' (linha ' . $f->line . ')') : '';
                    $lines[] = sprintf('- **Arquivo:** `%s`%s', $f->file, $lineInfo);
                }

                $lines[] = '';
                $lines[] = sprintf('**Recomendacao:** %s', $f->recommendation);
                $lines[] = '';

                if ($f->context && in_array($sev, ['critical', 'high'], true)) {
                    $lines[] = '**Contexto:**';
                    $lines[] = '';
                    $lines[] = '```';
                    $lines[] = $f->context;
                    $lines[] = '```';
                    $lines[] = '';
                }
            }
        }

        if (empty($findings)) {
            $lines[] = '## Resultado';
            $lines[] = '';
            $lines[] = 'Nenhum finding encontrado. Parabens! Reavaliar periodicamente.';
            $lines[] = '';
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * @param array<AuditFinding> $findings
     */
    public function toJson(array $findings, array $meta = []): string
    {
        $data = [
            'generated_at' => $meta['generated_at'] ?? now()->toIso8601String(),
            'totals' => [
                'all'       => count($findings),
                'by_severity' => $this->countBy($findings, fn ($f) => $f->severity),
                'by_area'   => $this->countBy($findings, fn ($f) => $f->area),
                'waf_mitigable' => count(array_filter($findings, fn ($f) => $f->wafMitigable)),
            ],
            'findings' => array_map(fn ($f) => $f->toArray(), $findings),
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function countBy(array $findings, \Closure $key): array
    {
        $out = [];
        foreach ($findings as $f) {
            $k = $key($f);
            $out[$k] = ($out[$k] ?? 0) + 1;
        }

        return $out;
    }
}
