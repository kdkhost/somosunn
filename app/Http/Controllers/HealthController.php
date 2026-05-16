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
 * Sistema UNN - HealthController
 *
 * Endpoint protegido por bearer token (HEALTH_TOKEN) que valida a saude
 * dos componentes criticos da plataforma: banco de dados, S3, escrita em
 * disco, fila de jobs e permissoes de diretorios de storage.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7
 */

namespace App\Http\Controllers;

use App\Contracts\HealthCheckInterface;
use App\Support\ComponentStatus;
use App\Support\HealthResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthController extends Controller implements HealthCheckInterface
{
    /**
     * Lista de componentes verificados, na ordem de execucao.
     *
     * @var array<int, string>
     */
    private const COMPONENTS = [
        'database',
        's3',
        'disk_write',
        'queue_health',
        'storage_permissions',
    ];

    /** Timeout (segundos) para a operacao de cada componente. */
    private const COMPONENT_TIMEOUT_SECONDS = 5;

    /** Timeout total (segundos) para a verificacao completa. */
    private const TOTAL_TIMEOUT_SECONDS = 10;

    /** Limite de jobs pendentes acima do qual queue_health vira "warning". */
    private const QUEUE_WARNING_THRESHOLD = 1000;

    /**
     * Endpoint publico GET /health.
     *
     * Fluxo:
     *   1. Valida bearer token (header Authorization ou query string ?token=).
     *   2. Executa todas as verificacoes via check().
     *   3. Retorna 200 se status = healthy, 503 se degraded ou unhealthy.
     */
    public function index(Request $request): JsonResponse
    {
        // 1) Autenticacao via bearer token (Requirement 9.2, 9.3)
        $expectedToken = (string) env('HEALTH_TOKEN', '');
        $providedToken = $request->bearerToken() ?: (string) $request->query('token', '');

        if ($expectedToken === '' || !hash_equals($expectedToken, (string) $providedToken)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        // 2) Executa as verificacoes (Requirement 9.4, 9.7)
        $start = microtime(true);
        $result = $this->check();
        $result->responseTimeMs = round((microtime(true) - $start) * 1000, 2);
        $result->timestamp = now()->toIso8601String();

        // 3) Status HTTP (Requirement 9.5, 9.6)
        $httpStatus = $result->status === HealthResult::STATUS_HEALTHY ? 200 : 503;

        return response()->json($result->toArray(), $httpStatus);
    }

    /**
     * Executa todas as verificacoes registradas e retorna o resultado agregado.
     *
     * Respeita um budget total aproximado de 10 segundos: caso o tempo
     * acumulado ultrapasse o limite, os componentes ainda nao verificados
     * sao marcados como "error" com mensagem de timeout global.
     */
    public function check(): HealthResult
    {
        $result = new HealthResult();
        $globalStart = microtime(true);

        foreach (self::COMPONENTS as $component) {
            $elapsed = microtime(true) - $globalStart;
            if ($elapsed >= self::TOTAL_TIMEOUT_SECONDS) {
                $result->components[] = new ComponentStatus(
                    $component,
                    ComponentStatus::STATUS_ERROR,
                    'global health check timeout exceeded',
                    0.0,
                );
                continue;
            }
            $result->components[] = $this->checkComponent($component);
        }

        $result->recomputeStatus();

        return $result;
    }

    /**
     * Executa a verificacao de um unico componente, isolando excecoes
     * para que uma falha individual nunca propague.
     */
    public function checkComponent(string $component): ComponentStatus
    {
        $start = microtime(true);
        try {
            switch ($component) {
                case 'database':
                    return $this->checkDatabase($start);
                case 's3':
                    return $this->checkS3($start);
                case 'disk_write':
                    return $this->checkDiskWrite($start);
                case 'queue_health':
                    return $this->checkQueueHealth($start);
                case 'storage_permissions':
                    return $this->checkStoragePermissions($start);
                default:
                    return new ComponentStatus(
                        $component,
                        ComponentStatus::STATUS_ERROR,
                        'unknown component',
                        $this->elapsedMs($start),
                    );
            }
        } catch (Throwable $e) {
            Log::error('HealthController: component check failed', [
                'component' => $component,
                'exception' => $e->getMessage(),
            ]);

            return new ComponentStatus(
                $component,
                ComponentStatus::STATUS_ERROR,
                $this->safeMessage($e),
                $this->elapsedMs($start),
            );
        }
    }

    /**
     * Verifica conectividade com o banco rodando "SELECT 1".
     */
    private function checkDatabase(float $start): ComponentStatus
    {
        $rows = DB::select('SELECT 1 AS alive');
        $alive = is_array($rows) && count($rows) > 0;
        $latency = $this->elapsedMs($start);

        if ($latency > self::COMPONENT_TIMEOUT_SECONDS * 1000) {
            return new ComponentStatus(
                'database',
                ComponentStatus::STATUS_ERROR,
                'database check exceeded component timeout',
                $latency,
            );
        }

        return new ComponentStatus(
            'database',
            $alive ? ComponentStatus::STATUS_OK : ComponentStatus::STATUS_ERROR,
            $alive ? null : 'unexpected empty response',
            $latency,
        );
    }

    /**
     * Verifica conectividade com o disco S3 listando arquivos no diretorio raiz.
     *
     * Integra com StorageProviderRegistry (multi-provider): se o provedor
     * ativo e' 'local', o check passa como OK informando que S3 nao esta
     * em uso; se e' um provedor S3 sem creds validas, retorna WARNING
     * sem chamar o disco (evita o erro generico "missing region").
     */
    private function checkS3(float $start): ComponentStatus
    {
        // Tenta consultar o Registry (multi-provider). Em caso de erro
        // (boot inicial, classe ausente), cai no comportamento legado.
        try {
            // Curto-circuito: se o storage_driver atual nao e 's3', S3
            // nao esta em uso pelo sistema. Acontece quando o Superadmin
            // selecionou disco local como driver primario.
            $effectiveDisk = (string) config('uploads.effective_disk', '');
            $selectedDisk = (string) config('uploads.selected_disk', '');
            if ($selectedDisk !== 's3' && $effectiveDisk !== 's3') {
                return new ComponentStatus(
                    's3',
                    ComponentStatus::STATUS_OK,
                    'storage local ativo (S3 nao em uso)',
                    $this->elapsedMs($start),
                );
            }

            if (class_exists(\App\Support\StorageProviderRegistry::class)) {
                /** @var \App\Support\StorageProviderRegistry $registry */
                $registry = app(\App\Support\StorageProviderRegistry::class);
                $active = $registry->activeProvider();

                // Provedor 'local': S3 nao esta em uso, considera OK.
                if ($active === \App\Support\StorageProviderRegistry::PROVIDER_LOCAL) {
                    return new ComponentStatus(
                        's3',
                        ComponentStatus::STATUS_OK,
                        'storage local ativo (S3 nao em uso)',
                        $this->elapsedMs($start),
                    );
                }

                // Provedor S3 ativo mas sem creds validas: warning explicito.
                if (!$registry->isConfigured($active)) {
                    return new ComponentStatus(
                        's3',
                        ComponentStatus::STATUS_WARNING,
                        sprintf(
                            'provedor "%s" sem credenciais validas',
                            $registry->displayName($active)
                        ),
                        $this->elapsedMs($start),
                    );
                }
            }
        } catch (\Throwable $e) {
            // Boot resiliente: ignora erro do Registry e tenta o caminho normal.
        }

        // Storage::disk('s3')->files lanca em caso de credenciais invalidas
        // ou de bucket inacessivel; o try/catch externo captura.
        $disk = Storage::disk('s3');
        $files = $disk->files('', false);
        // Forca a avaliacao caso o adapter retorne um iterator preguicoso.
        if ($files instanceof \Traversable) {
            $files = iterator_to_array($files);
        }
        $latency = $this->elapsedMs($start);

        if ($latency > self::COMPONENT_TIMEOUT_SECONDS * 1000) {
            return new ComponentStatus(
                's3',
                ComponentStatus::STATUS_ERROR,
                's3 check exceeded component timeout',
                $latency,
            );
        }

        return new ComponentStatus(
            's3',
            ComponentStatus::STATUS_OK,
            'listed ' . (is_array($files) ? count($files) : 0) . ' object(s)',
            $latency,
        );
    }

    /**
     * Verifica permissoes de escrita criando e removendo um arquivo temporario.
     */
    private function checkDiskWrite(float $start): ComponentStatus
    {
        $directory = storage_path('framework');
        if (!is_dir($directory)) {
            return new ComponentStatus(
                'disk_write',
                ComponentStatus::STATUS_ERROR,
                'storage/framework directory missing',
                $this->elapsedMs($start),
            );
        }

        $filename = 'health-check-' . microtime(true) . '-' . bin2hex(random_bytes(4)) . '.tmp';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        $written = @file_put_contents($path, 'health-check');

        if ($written === false) {
            return new ComponentStatus(
                'disk_write',
                ComponentStatus::STATUS_ERROR,
                'failed to write temporary file in storage/framework',
                $this->elapsedMs($start),
            );
        }

        $deleted = @unlink($path);

        $latency = $this->elapsedMs($start);

        if (!$deleted) {
            return new ComponentStatus(
                'disk_write',
                ComponentStatus::STATUS_WARNING,
                'wrote temp file but failed to delete it',
                $latency,
            );
        }

        if ($latency > self::COMPONENT_TIMEOUT_SECONDS * 1000) {
            return new ComponentStatus(
                'disk_write',
                ComponentStatus::STATUS_ERROR,
                'disk_write check exceeded component timeout',
                $latency,
            );
        }

        return new ComponentStatus(
            'disk_write',
            ComponentStatus::STATUS_OK,
            null,
            $latency,
        );
    }

    /**
     * Verifica a saude da fila contando jobs pendentes na tabela "jobs".
     * Retorna "warning" se o numero de jobs pendentes ultrapassar o threshold.
     */
    private function checkQueueHealth(float $start): ComponentStatus
    {
        $pending = (int) DB::table('jobs')->count();
        $latency = $this->elapsedMs($start);

        if ($latency > self::COMPONENT_TIMEOUT_SECONDS * 1000) {
            return new ComponentStatus(
                'queue_health',
                ComponentStatus::STATUS_ERROR,
                'queue_health check exceeded component timeout',
                $latency,
            );
        }

        if ($pending > self::QUEUE_WARNING_THRESHOLD) {
            return new ComponentStatus(
                'queue_health',
                ComponentStatus::STATUS_WARNING,
                $pending . ' pending jobs (threshold: ' . self::QUEUE_WARNING_THRESHOLD . ')',
                $latency,
            );
        }

        return new ComponentStatus(
            'queue_health',
            ComponentStatus::STATUS_OK,
            $pending . ' pending job(s)',
            $latency,
        );
    }

    /**
     * Verifica que os diretorios criticos de storage sao escritaveis.
     */
    private function checkStoragePermissions(float $start): ComponentStatus
    {
        $directories = [
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
        ];

        $issues = [];
        foreach ($directories as $label => $path) {
            if (!is_dir($path)) {
                $issues[] = $label . ' missing';
                continue;
            }
            if (!is_writable($path)) {
                $issues[] = $label . ' not writable';
            }
        }

        $latency = $this->elapsedMs($start);

        if ($issues !== []) {
            return new ComponentStatus(
                'storage_permissions',
                ComponentStatus::STATUS_ERROR,
                implode('; ', $issues),
                $latency,
            );
        }

        return new ComponentStatus(
            'storage_permissions',
            ComponentStatus::STATUS_OK,
            null,
            $latency,
        );
    }

    /**
     * Calcula o tempo decorrido em milissegundos a partir de um microtime(true).
     */
    private function elapsedMs(float $start): float
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Sanitiza a mensagem de excecao removendo paths absolutos para nao
     * vazar detalhes de filesystem na resposta JSON.
     */
    private function safeMessage(Throwable $e): string
    {
        $msg = $e->getMessage();
        // Remove caminhos absolutos do tipo "C:\..." ou "/var/...".
        $msg = preg_replace('#[A-Za-z]:\\\\[^\s\'"]+#', '<path>', $msg) ?? $msg;
        $msg = preg_replace('#/[^\s\'"]+#', '<path>', $msg) ?? $msg;

        return mb_substr($msg, 0, 250);
    }
}
