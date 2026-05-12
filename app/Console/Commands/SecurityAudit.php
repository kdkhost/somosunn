<?php

namespace App\Console\Commands;

use App\Services\Waf\Scanners\AuditContext;
use App\Services\Waf\Scanners\AuditFinding;
use App\Services\Waf\Scanners\AuditReportBuilder;
use App\Services\Waf\Scanners\AuthScanner;
use App\Services\Waf\Scanners\BladeScanner;
use App\Services\Waf\Scanners\ConfigScanner;
use App\Services\Waf\Scanners\Contracts\Scanner;
use App\Services\Waf\Scanners\HeaderScanner;
use App\Services\Waf\Scanners\PhpAstScanner;
use App\Services\Waf\Scanners\RouteScanner;
use App\Services\Waf\Scanners\UploadScanner;
use App\Services\Waf\Scanners\WebhookScanner;
use Illuminate\Console\Command;

/**
 * Comando Artisan que orquestra a auditoria de seguranca da Unn.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 1.1
 */
class SecurityAudit extends Command
{
    protected $signature = 'security:audit
                            {--paths=* : Paths (relativos a raiz) a analisar. Padrao: app,routes,config,database,resources/views,resources/js,public}
                            {--format=md : Formato de saida: md | json | both}
                            {--out= : Diretorio onde salvar o relatorio. Padrao: storage/app/security}
                            {--only=* : Restringir a scanners especificos: php-ast,blade,routes,upload,webhook,config,headers,auth}
                            {--no-summary : Nao exibir resumo no stdout}';

    protected $description = 'Roda a auditoria de seguranca ponta a ponta e gera relatorio em Markdown/JSON.';

    public function handle(): int
    {
        $paths = $this->option('paths');

        if (empty($paths)) {
            $paths = [
                'app',
                'routes',
                'config',
                'database',
                'resources/views',
                'resources/js',
                'public',
                '.env.example',
            ];
        }

        $ctx = new AuditContext(
            basePath: base_path(),
            paths:    array_values($paths),
        );

        $onlyScanners = (array) $this->option('only');
        $allScanners = $this->buildScanners();
        $scanners = empty($onlyScanners)
            ? $allScanners
            : array_filter($allScanners, fn (Scanner $s) => in_array($s->id(), $onlyScanners, true));

        $this->info('Iniciando auditoria de seguranca ...');
        $this->line('Paths: ' . implode(', ', $ctx->paths));
        $this->newLine();

        /** @var array<AuditFinding> $findings */
        $findings = [];

        foreach ($scanners as $scanner) {
            $this->line(sprintf(' - %s (%s) ...', $scanner->label(), $scanner->id()));

            try {
                foreach ($scanner->scan($ctx) as $finding) {
                    if (! $finding instanceof AuditFinding) {
                        continue;
                    }
                    $findings[] = $finding;
                }
            } catch (\Throwable $e) {
                $this->warn(sprintf('Scanner %s falhou: %s', $scanner->id(), $e->getMessage()));
            }
        }

        $this->newLine();
        $this->info(sprintf('Auditoria concluida: %d findings.', count($findings)));

        $builder = new AuditReportBuilder();
        $format  = $this->option('format');

        $outDir = $this->option('out') ?: storage_path('app/security');

        if (! is_dir($outDir) && ! @mkdir($outDir, 0775, true) && ! is_dir($outDir)) {
            $this->error(sprintf('Nao foi possivel criar diretorio de saida: %s', $outDir));

            return self::FAILURE;
        }

        $stamp = date('Ymd-His');

        $writtenFiles = [];

        if (in_array($format, ['md', 'both'], true)) {
            $mdPath = $outDir . DIRECTORY_SEPARATOR . 'audit-report-' . $stamp . '.md';
            file_put_contents($mdPath, $builder->toMarkdown($findings));
            $writtenFiles[] = $mdPath;

            // Sempre escreve o snapshot "ultimo"
            $latestMd = $outDir . DIRECTORY_SEPARATOR . 'audit-report-latest.md';
            @copy($mdPath, $latestMd);
            $writtenFiles[] = $latestMd;
        }

        if (in_array($format, ['json', 'both'], true)) {
            $jsonPath = $outDir . DIRECTORY_SEPARATOR . 'audit-report-' . $stamp . '.json';
            file_put_contents($jsonPath, $builder->toJson($findings));
            $writtenFiles[] = $jsonPath;

            $latestJson = $outDir . DIRECTORY_SEPARATOR . 'audit-report-latest.json';
            @copy($jsonPath, $latestJson);
            $writtenFiles[] = $latestJson;
        }

        foreach ($writtenFiles as $path) {
            $this->line('  > ' . $path);
        }

        if (! $this->option('no-summary')) {
            $this->printSummary($findings);
        }

        return self::SUCCESS;
    }

    /**
     * @return array<Scanner>
     */
    private function buildScanners(): array
    {
        return [
            new PhpAstScanner(),
            new BladeScanner(),
            new RouteScanner(),
            new UploadScanner(),
            new WebhookScanner(),
            new ConfigScanner(),
            new HeaderScanner(),
            new AuthScanner(),
        ];
    }

    /**
     * @param array<AuditFinding> $findings
     */
    private function printSummary(array $findings): void
    {
        $this->newLine();
        $this->info('Resumo:');

        $counts = [];
        foreach ($findings as $f) {
            $counts[$f->severity] = ($counts[$f->severity] ?? 0) + 1;
        }

        foreach (['critical', 'high', 'medium', 'low', 'info'] as $sev) {
            $this->line(sprintf(
                '  %-10s %d',
                strtoupper($sev),
                $counts[$sev] ?? 0
            ));
        }

        $byArea = [];
        foreach ($findings as $f) {
            $byArea[$f->area] = ($byArea[$f->area] ?? 0) + 1;
        }
        arsort($byArea);

        if (! empty($byArea)) {
            $this->newLine();
            $this->line('Top areas:');
            foreach (array_slice($byArea, 0, 10, true) as $area => $n) {
                $this->line(sprintf('  %-20s %d', $area, $n));
            }
        }
    }
}
