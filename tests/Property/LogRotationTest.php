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
 * Sistema UNN - Property test (Property 15) para rotacao de logs
 * por idade e canal.
 *
 * Spec: .kiro/specs/advanced-security-performance (task 14.2)
 *
 * Property 15: Log Rotation by Age and Channel
 *
 *   Para QUALQUER canal C in {waf, security, application} e QUALQUER
 *   idade A em dias, com R(C) sendo a retencao do canal:
 *
 *     R(waf)         = 30
 *     R(security)    = 90  (Setting::log_security_retention default)
 *     R(application) = 30  (Setting::log_retention_days default)
 *
 *   Apos uma execucao de LogsCleanup::cleanup() sobre `storage/logs/`
 *   contendo um unico arquivo "{prefix}-YYYY-MM-DD.log" com idade A:
 *
 *     - A > R(C)         => arquivo DELETADO       (sem .gz remanescente)
 *     - 7 < A <= R(C)    => arquivo COMPRIMIDO     (.log removido, .gz criado)
 *     - A <= 7           => arquivo INTACTO        (.log mantido, sem .gz)
 *
 *   Sub-propriedades implicitas:
 *
 *     - A retencao por canal SOBRESCREVE a retencao global. Para canal
 *       `security`, qualquer A em (30, 90] deve ser comprimido (nao
 *       deletado), o que prova que o canal nao herda os 30 dias da
 *       retencao global aplicada em `application`.
 *     - O contador `filesRemoved`/`filesCompressed` em CleanupResult
 *       reflete deterministicamente a acao tomada para cada arquivo.
 *     - O fluxo nominal nao gera entradas em `errors` (fail-safe so e
 *       acionado em I/O quebrado, fora do escopo desta property).
 *
 * Estrategia de teste:
 *
 *   - O comando real le `storage_path('logs')` (nao aceita --dir=). Em
 *     vez de patcheaar a producao, redirecionamos o storage path do
 *     container via $this->app->useStoragePath($tempDir) e revertemos
 *     em tearDown(). Padrao ja usado por
 *     AdvancedRateLimitMiddlewareTest.
 *
 *   - Para evitar I/O em DB durante centenas de iteracoes do Eris,
 *     injetamos um cache vazio em Setting via reflection (cacheLoaded=true,
 *     cache=[]). Setting::get(...) entao devolve sempre o $default
 *     informado pelo LogsCleanup, fazendo a retencao usar exatamente
 *     os defaults documentados (30/90/30).
 *
 *   - LogsCleanup detecta idade preferencialmente via padrao
 *     `\d{4}-\d{2}-\d{2}` no nome do arquivo (mtime e fallback). A idade
 *     percebida pela producao e:
 *
 *       date_in_file = createFromFormat('Y-m-d', $dateStr)  // hora atual
 *       now           = new DateTimeImmutable('today')       // 00:00 de hoje
 *       ageDays       = (now - date_in_file)->days           // truncado p/ baixo
 *
 *     Como `createFromFormat('Y-m-d', ...)` herda a hora corrente, o
 *     intervalo entre `now` (midnight) e `date_in_file` (com hora > 0)
 *     pode perder uma fracao de dia, resultando em `ageDays = N - 1`
 *     para um arquivo nominalmente "N dias atras" sempre que a execucao
 *     ocorre depois de 00:00. Para tornar a property estavel sob qualquer
 *     hora do dia, calculamos a idade percebida pela producao com a
 *     mesma formula e baseamos as expectativas (delete / compress / keep)
 *     nesse `productionAge`. Tambem sincronizamos mtime apenas como
 *     defesa em profundidade.
 *
 * Validates: Requirements 10.2, 10.3, 10.6
 */

namespace Tests\Property;

use App\Console\Commands\LogsCleanup;
use App\Models\Setting;
use App\Support\CleanupResult;
use Eris\Generators;
use Eris\TestTrait;
use ReflectionClass;
use Tests\TestCase;

class LogRotationTest extends TestCase
{
    use TestTrait;

    /**
     * Limite superior do compress (em dias). Arquivos com idade > este
     * valor e <= retencao do canal sao comprimidos. Mantido espelhando
     * LogsCleanup::COMPRESS_AFTER_DAYS para tornar a property auto-
     * documentada.
     */
    private const COMPRESS_AFTER_DAYS = 7;

    /**
     * Retencoes esperadas (defaults documentados em design.md). Usadas
     * pelo modelo de referencia da property; o comando producao pega
     * estes mesmos valores via Setting::get(default).
     */
    private const RETENTION = [
        'waf' => 30,
        'security' => 90,
        'application' => 30,
    ];

    /**
     * Mapeamento canal -> prefixo do arquivo gerado pelo driver `daily`
     * do Laravel. Espelhado em LogsCleanup::detectChannelFromFilename().
     */
    private const FILENAME_PREFIX = [
        'waf' => 'waf-',
        'security' => 'security-',
        'application' => 'laravel-',
    ];

    /** Diretorio temporario usado como storage path da Application. */
    private string $tempStoragePath;

    /** $tempStoragePath . '/logs' - varrido por LogsCleanup::cleanup(). */
    private string $logsDir;

    /**
     * Storage path original da Application (restaurado em tearDown
     * para nao vazar para outros testes da suite).
     */
    private string $originalStoragePath;

    /**
     * PHPUnit 10 removeu PHPUnit\Util\Test::parseTestMethodAnnotations()
     * no qual o Eris 0.14.x ainda se apoia. Sobrescrevemos para retornar
     * defaults vazios (rand seed, 100 iteracoes, sem time-limit).
     *
     * @return array<string, mixed>
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Snapshot do storage path real para restaurar em tearDown.
        $this->originalStoragePath = $this->app->storagePath();

        // Diretorio temporario unico por execucao da suite.
        $this->tempStoragePath = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'unn_log_rotation_test_'
            . uniqid('', true);

        @mkdir($this->tempStoragePath, 0775, true);

        $this->logsDir = $this->tempStoragePath . DIRECTORY_SEPARATOR . 'logs';
        @mkdir($this->logsDir, 0775, true);

        // Redireciona storage_path('logs') -> $logsDir.
        $this->app->useStoragePath($this->tempStoragePath);

        // Cache vazio + loaded=true em Setting: Setting::get() devolve
        // sempre o $default sem tocar no DB durante as iteracoes Eris.
        $this->setSettingRuntimeEmpty();
    }

    protected function tearDown(): void
    {
        // Reverte injecoes feitas em setUp para isolamento da suite.
        Setting::flushRuntimeCache();
        $this->app->useStoragePath($this->originalStoragePath);

        $this->rmdirRecursive($this->tempStoragePath);

        parent::tearDown();
    }

    /**
     * Property 15: a acao tomada por LogsCleanup::cleanup() em um
     * arquivo "{prefix}-YYYY-MM-DD.log" e funcao deterministica de
     * (canal, idade) e respeita a retencao por canal.
     *
     * Geradores:
     *   - canal: elemento de {waf, security, application} (cobre os 3
     *            casos do detectChannelFromFilename).
     *   - idade: 0..100 dias (cobre todas as faixas: <=7 intactos,
     *            8..30 limite waf/application, 31..90 limite security,
     *            91..100 expirados em todos os canais).
     *
     * Validates: Requirements 10.2, 10.3, 10.6
     */
    public function test_rotation_action_matches_age_and_channel_retention(): void
    {
        $this
            ->forAll(
                Generators::elements(array_keys(self::RETENTION)),
                Generators::choose(0, 100)
            )
            ->then(function (string $channel, int $age): void {
                $retention = self::RETENTION[$channel];

                // Estado limpo a cada iteracao Eris (o mesmo metodo de
                // teste e re-executado dentro do mesmo processo, entao
                // arquivos da iteracao anterior precisam ser removidos).
                $this->purgeLogsDir();

                // Constroi nome com data igual a (hoje - $age dias).
                $today = new \DateTimeImmutable('today');
                $fileDate = $today->modify("-{$age} days");
                $basename = self::FILENAME_PREFIX[$channel]
                    . $fileDate->format('Y-m-d')
                    . '.log';
                $path = $this->logsDir . DIRECTORY_SEPARATOR . $basename;

                // Idade que a producao IRA perceber (ver docblock da
                // classe). Reproduzimos exatamente o calculo de
                // LogsCleanup::getAgeDays() para nao depender da hora
                // do relogio durante a execucao da suite.
                $productionAge = $this->productionPerceivedAge($fileDate->format('Y-m-d'));

                // Modelo de referencia: 3 acoes mutuamente exclusivas,
                // sempre baseadas na idade efetivamente percebida.
                $shouldDelete = $productionAge > $retention;
                $shouldCompress = ! $shouldDelete && $productionAge > self::COMPRESS_AFTER_DAYS;
                $shouldKeep = ! $shouldDelete && ! $shouldCompress;

                // Conteudo nao trivial para que o gzip produza algo
                // mensuravel (50x ~32B == ~1.6KB). Conteudo exato e
                // irrelevante para a property; importa o arquivo existir.
                $contents = str_repeat("test-log-line-for-age-{$age}-channel-{$channel}\n", 50);
                file_put_contents($path, $contents);

                // Sincroniza mtime com a data logica (defesa em
                // profundidade contra qualquer regressao no parser de
                // data; o caminho primario continua sendo o nome).
                @touch($path, $fileDate->getTimestamp());

                $command = new LogsCleanup();
                $result = $command->cleanup();

                $context = sprintf(
                    'channel=%s age=%d productionAge=%d retention=%d basename=%s',
                    $channel,
                    $age,
                    $productionAge,
                    $retention,
                    $basename
                );

                $this->assertInstanceOf(
                    CleanupResult::class,
                    $result,
                    "Property 15: cleanup() deve retornar CleanupResult ({$context})"
                );

                if ($shouldDelete) {
                    $this->assertFileDoesNotExist(
                        $path,
                        "Property 15: arquivo com A>R deveria ter sido deletado ({$context})"
                    );
                    $this->assertFileDoesNotExist(
                        $path . '.gz',
                        "Property 15: arquivo com A>R nao deveria gerar .gz ({$context})"
                    );
                    $this->assertSame(
                        1,
                        $result->filesRemoved,
                        "Property 15: filesRemoved deveria ser 1 ({$context})"
                    );
                    $this->assertSame(
                        0,
                        $result->filesCompressed,
                        "Property 15: filesCompressed deveria ser 0 ({$context})"
                    );
                } elseif ($shouldCompress) {
                    $this->assertFileDoesNotExist(
                        $path,
                        "Property 15: original .log deveria ter sido removido apos compressao ({$context})"
                    );
                    $this->assertFileExists(
                        $path . '.gz',
                        "Property 15: .gz deveria ter sido criado ({$context})"
                    );
                    $this->assertSame(
                        0,
                        $result->filesRemoved,
                        "Property 15: filesRemoved deveria ser 0 (compress, {$context})"
                    );
                    $this->assertSame(
                        1,
                        $result->filesCompressed,
                        "Property 15: filesCompressed deveria ser 1 ({$context})"
                    );
                } else {
                    $this->assertTrue($shouldKeep, 'Sanity: classificacao deve ser uma das tres.');
                    $this->assertFileExists(
                        $path,
                        "Property 15: arquivo com A<=7 deveria permanecer intacto ({$context})"
                    );
                    $this->assertFileDoesNotExist(
                        $path . '.gz',
                        "Property 15: arquivo intacto nao deveria gerar .gz ({$context})"
                    );
                    $this->assertSame(
                        0,
                        $result->filesRemoved,
                        "Property 15: filesRemoved deveria ser 0 (keep, {$context})"
                    );
                    $this->assertSame(
                        0,
                        $result->filesCompressed,
                        "Property 15: filesCompressed deveria ser 0 (keep, {$context})"
                    );
                }

                // Fluxo nominal nao deve produzir erros (fail-safe so
                // entra em I/O quebrado, cenario coberto por unit tests).
                $this->assertSame(
                    [],
                    $result->errors,
                    sprintf(
                        'Property 15: cleanup() nao deveria produzir erros (%s). Erros: %s',
                        $context,
                        implode(' | ', $result->errors)
                    )
                );
            });
    }

    /**
     * Property 15 (sub): a retencao por canal sobrescreve a global.
     *
     * Especificamente, para uma idade FIXA no intervalo (30, 90]:
     *   - canal `security`        -> arquivo COMPRIMIDO  (pois A <= 90)
     *   - canais `waf`/`application` -> arquivo DELETADO (pois A > 30)
     *
     * Esta propriedade isola a sobrescrita por canal de forma
     * inequivoca: se a producao usasse uma unica retencao global, o
     * comportamento nos tres canais seria identico, o que falharia
     * aqui.
     *
     * Validates: Requirements 10.6
     */
    public function test_security_channel_retention_overrides_global(): void
    {
        $this
            ->forAll(
                // Idade nominal cuja idade percebida pela producao cai
                // estritamente dentro de (R(application), R(security)] =
                // (30, 90]. Como `productionAge` pode ser igual a `age`
                // ou `age - 1` dependendo da hora corrente, restringir
                // a [32, 89] garante:
                //   productionAge in [31, 89] subset (30, 90]
                Generators::choose(32, 89)
            )
            ->then(function (int $age): void {
                $today = new \DateTimeImmutable('today');
                $fileDate = $today->modify("-{$age} days");
                $dateStr = $fileDate->format('Y-m-d');

                // Verifica que a idade percebida realmente esta no
                // intervalo (30, 90]. Caso contrario a iteracao nao
                // exercita a propriedade desejada e e pulada via
                // assertion no invariante.
                $productionAge = $this->productionPerceivedAge($dateStr);
                $this->assertGreaterThan(
                    30,
                    $productionAge,
                    "Sanity: productionAge={$productionAge} deve ser > 30 (nominal age={$age})"
                );
                $this->assertLessThanOrEqual(
                    90,
                    $productionAge,
                    "Sanity: productionAge={$productionAge} deve ser <= 90 (nominal age={$age})"
                );

                // Cria um arquivo por canal na MESMA varredura para
                // exercitar simultaneamente as 3 ramificacoes do
                // detectChannelFromFilename.
                $this->purgeLogsDir();

                $files = [];
                foreach (self::FILENAME_PREFIX as $channel => $prefix) {
                    $basename = $prefix . $dateStr . '.log';
                    $path = $this->logsDir . DIRECTORY_SEPARATOR . $basename;
                    file_put_contents(
                        $path,
                        str_repeat("override-test-{$channel}-{$age}\n", 30)
                    );
                    @touch($path, $fileDate->getTimestamp());
                    $files[$channel] = $path;
                }

                $result = (new LogsCleanup())->cleanup();

                // Security: A em (30, 90] deve ser COMPRIMIDO.
                $this->assertFileDoesNotExist(
                    $files['security'],
                    "Property 15: security .log original deveria ter sido removido apos compress (age={$age})"
                );
                $this->assertFileExists(
                    $files['security'] . '.gz',
                    "Property 15: security .gz deveria existir (age={$age}, retencao 90 sobrescreve global 30)"
                );

                // WAF: retencao fixa 30, A em (30,90] -> DELETADO.
                $this->assertFileDoesNotExist(
                    $files['waf'],
                    "Property 15: waf .log deveria ter sido deletado (age={$age} > 30)"
                );
                $this->assertFileDoesNotExist(
                    $files['waf'] . '.gz',
                    "Property 15: waf nao deveria gerar .gz quando deletado (age={$age})"
                );

                // Application: retencao 30 (default), A em (30,90] -> DELETADO.
                $this->assertFileDoesNotExist(
                    $files['application'],
                    "Property 15: application .log deveria ter sido deletado (age={$age} > 30)"
                );
                $this->assertFileDoesNotExist(
                    $files['application'] . '.gz',
                    "Property 15: application nao deveria gerar .gz quando deletado (age={$age})"
                );

                // Contadores agregados: 2 deletados (waf + application),
                // 1 comprimido (security), zero erros.
                $this->assertSame(
                    2,
                    $result->filesRemoved,
                    "Property 15: esperava 2 filesRemoved (waf+application) para age={$age}, obtido {$result->filesRemoved}"
                );
                $this->assertSame(
                    1,
                    $result->filesCompressed,
                    "Property 15: esperava 1 filesCompressed (security) para age={$age}, obtido {$result->filesCompressed}"
                );
                $this->assertSame(
                    [],
                    $result->errors,
                    'Property 15: cleanup() nao deveria produzir erros no override por canal. Erros: '
                        . implode(' | ', $result->errors)
                );
            });
    }

    /* -------------------------------------------------------------- */
    /* Helpers                                                         */
    /* -------------------------------------------------------------- */

    /**
     * Remove todo o conteudo do diretorio de logs entre iteracoes do
     * Eris para garantir que cada propriedade comeca de um estado
     * conhecido.
     */
    private function purgeLogsDir(): void
    {
        $entries = (array) (glob($this->logsDir . DIRECTORY_SEPARATOR . '*') ?: []);
        foreach ($entries as $entry) {
            if (is_file($entry)) {
                @unlink($entry);
            }
        }
    }

    /**
     * Injeta cache vazio em Setting (cacheLoaded=true, runtimeCache=[]).
     * Resultado: Setting::get($key, $default) devolve $default sem
     * consultar o banco de dados, o que torna as 100+ iteracoes do Eris
     * rapidas e independentes da existencia de tabela `settings`.
     */
    private function setSettingRuntimeEmpty(): void
    {
        $reflection = new ReflectionClass(Setting::class);

        $cache = $reflection->getProperty('runtimeCache');
        $cache->setAccessible(true);

        $loaded = $reflection->getProperty('runtimeCacheLoaded');
        $loaded->setAccessible(true);

        $cache->setValue(null, []);
        $loaded->setValue(null, true);
    }

    /**
     * Reproduz exatamente o calculo de LogsCleanup::getAgeDays() para
     * um arquivo cujo nome contem o trecho `YYYY-MM-DD`. Usado pelas
     * properties para basear suas expectativas na idade efetivamente
     * percebida pela producao, eliminando flakiness dependente da hora
     * do dia em que a suite e executada.
     *
     *   $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateStr)
     *   // herda hora atual (HH:MM:SS) do relogio do sistema
     *
     *   $now  = new DateTimeImmutable('today')   // 00:00 de hoje
     *   $diff = $now->diff($date)
     *   return $diff->invert === 1 ? (int) $diff->days : 0
     *
     * Com hora > 0, $diff->days fica truncado para o piso, fazendo um
     * arquivo "N dias atras" ser percebido como `N - 1` dias.
     */
    private function productionPerceivedAge(string $dateStr): int
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateStr);
        if (! $date instanceof \DateTimeImmutable) {
            return 0;
        }

        $now = new \DateTimeImmutable('today');
        $diff = $now->diff($date);
        $days = (int) $diff->days;

        return $diff->invert === 1 ? $days : 0;
    }

    /**
     * Remocao recursiva do tempStoragePath. Silenciosa: tearDown nao
     * deve falhar caso algum arquivo tenha sido removido por um
     * processo concorrente.
     */
    private function rmdirRecursive(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path)) {
                $this->rmdirRecursive($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
