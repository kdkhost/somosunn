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
 * Sistema UNN - Property test (Property 11) para retencao de backups
 *
 * Spec: .kiro/specs/advanced-security-performance (task 10.4)
 *
 * Property 11: Backup Retention Correctness
 *
 *   Para qualquer N (numero de backups a manter) e qualquer total T
 *   de arquivos presentes em backups/db:
 *     - Apos BackupService::deleteOldBackups($N, $cfgKeep) com $cfgKeep
 *       irrelevante (sem arquivos em backups/config), restam exatamente
 *       min($N, $T) arquivos no diretorio backups/db.
 *     - Os arquivos remanescentes sao os $min($N, $T) mais recentes (i.e.,
 *       com maior lastModified). Os mais antigos sao removidos.
 *     - A operacao e deterministica: mesmas entradas (N, T) e mesmas
 *       mtimes de arquivos produzem o mesmo conjunto remanescente.
 *
 * Detalhes de implementacao:
 *
 *   - Usamos Storage::fake('s3') para isolar o disco em uma pasta local
 *     temporaria; nenhuma chamada real a S3/IDrive E2 e feita.
 *   - listBackups() do BackupService ordena por lastModified() do disco,
 *     nao pelo timestamp embutido no nome do arquivo. Para tornar a
 *     ordenacao deterministica e correlata com o "timestamp logico" do
 *     backup, ajustamos a mtime de cada arquivo via touch() apos o put().
 *   - Geradores propositalmente excluem os defaults do contrato
 *     (keepDaily=30, keepWeekly=12) para nao acionar o override via tabela
 *     settings em BackupService::deleteOldBackups().
 *   - Como nao gravamos nada em backups/config, $cfgKeep e irrelevante;
 *     usamos valor 0 (estavel e diferente do default 12).
 *
 * Validates: Requirements 7.6
 */

namespace Tests\Property;

use App\Services\BackupService;
use Carbon\Carbon;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupRetentionTest extends TestCase
{
    use TestTrait;

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

    /**
     * Cria $total arquivos em backups/db com nomes contendo timestamps
     * escalonados (i=0 mais antigo, i=$total-1 mais recente) e ajusta a
     * mtime do arquivo no disco fake para casar com o timestamp logico,
     * garantindo ordenacao deterministica em listBackups().
     *
     * Retorna a lista de paths criados na ordem cronologica (do mais
     * antigo para o mais recente).
     *
     * @return array<int, string>
     */
    private function seedDbBackups(int $total, Carbon $now): array
    {
        $disk = Storage::disk('s3');
        $paths = [];

        for ($i = 0; $i < $total; $i++) {
            // Idade decrescente: i=0 -> $total dias atras, i=$total-1 -> 1 dia atras.
            $age = $total - $i;
            $when = $now->copy()->subDays($age);
            $name = BackupService::BACKUP_DIR_DB . '/' . $when->format('Y-m-d_His') . '.sql.gz';

            $disk->put($name, 'fake-content-' . $i);

            // Alinha a mtime do arquivo com o timestamp logico para que
            // listBackups() ordene exatamente como esperado.
            $absolute = $disk->path($name);
            if (is_file($absolute)) {
                @touch($absolute, $when->getTimestamp());
            }

            $paths[] = $name;
        }

        return $paths;
    }

    /**
     * Property 11.A: apos deleteOldBackups($n, 0), restam exatamente
     * min($n, $total) arquivos em backups/db, e estes sao os mais recentes.
     *
     * Validates: Requirements 7.6
     */
    public function test_retention_keeps_exactly_min_n_total_most_recent(): void
    {
        $this
            ->forAll(
                // keepDaily: 0..25 (exclui o default 30 -> sem override por settings).
                Generators::choose(0, 25),
                // total de arquivos presentes em backups/db: 0..25 (suficiente
                // para cobrir n<total, n=total e n>total sem custo excessivo).
                Generators::choose(0, 25)
            )
            ->then(function (int $n, int $total): void {
                Storage::fake('s3');

                $now = Carbon::create(2024, 6, 15, 12, 0, 0);
                $created = $this->seedDbBackups($total, $now);

                /** @var BackupService $service */
                $service = $this->app->make(BackupService::class);

                // keepWeekly=0 (!=12) evita override via settings;
                // backups/config esta vazio, entao nao remove nada de fato.
                $service->deleteOldBackups($n, 0);

                $remaining = (array) Storage::disk('s3')->files(BackupService::BACKUP_DIR_DB);
                $expectedKept = min($n, $total);

                // 1) Quantidade exata de arquivos remanescentes.
                $this->assertCount(
                    $expectedKept,
                    $remaining,
                    "Property 11 violada: esperava {$expectedKept} arquivos remanescentes, "
                        . 'obtidos ' . count($remaining)
                        . " (n={$n}, total={$total})."
                );

                // 2) Os arquivos remanescentes devem ser exatamente os
                //    $expectedKept mais recentes (cauda da lista cronologica).
                $expectedRemaining = array_slice($created, $total - $expectedKept);
                sort($expectedRemaining);
                $remainingSorted = $remaining;
                sort($remainingSorted);

                $this->assertSame(
                    $expectedRemaining,
                    $remainingSorted,
                    "Property 11 violada: arquivos remanescentes nao sao os "
                        . "{$expectedKept} mais recentes (n={$n}, total={$total})."
                );
            });
    }

    /**
     * Property 11.B (Determinismo): para o mesmo (n, total) e mesma
     * configuracao de mtimes, deleteOldBackups produz o mesmo conjunto
     * remanescente em execucoes independentes.
     *
     * Validates: Requirements 7.6
     */
    public function test_retention_is_deterministic_for_same_input(): void
    {
        $this
            ->forAll(
                Generators::choose(0, 15),
                Generators::choose(0, 15)
            )
            ->then(function (int $n, int $total): void {
                $now = Carbon::create(2024, 6, 15, 12, 0, 0);
                $runs = [];

                for ($run = 0; $run < 2; $run++) {
                    Storage::fake('s3');
                    $this->seedDbBackups($total, $now);

                    /** @var BackupService $service */
                    $service = $this->app->make(BackupService::class);
                    $service->deleteOldBackups($n, 0);

                    $remaining = (array) Storage::disk('s3')->files(BackupService::BACKUP_DIR_DB);
                    sort($remaining);
                    $runs[] = $remaining;
                }

                $this->assertSame(
                    $runs[0],
                    $runs[1],
                    "Property 11 violada: deleteOldBackups nao e deterministico "
                        . "para n={$n}, total={$total}."
                );
            });
    }
}
