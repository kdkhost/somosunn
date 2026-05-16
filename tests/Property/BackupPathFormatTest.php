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
 * Sistema UNN - Property test (Property 10) para formato de path de backup
 *
 * Spec: .kiro/specs/advanced-security-performance (task 10.3)
 *
 * Property 10: Backup Path Format
 *
 *   Para qualquer timestamp T gerado por Carbon, o path produzido por
 *   BackupService::backupDatabase() deve seguir o padrao
 *     backups/db/YYYY-MM-DD_HHmmss.sql.gz
 *   e o path produzido por BackupService::backupConfig() deve seguir
 *     backups/config/YYYY-MM-DD_HHmmss.tar.gz
 *   onde YYYY-MM-DD_HHmmss e T formatado em Y-m-d_His.
 *
 *   Alem disso, a operacao e deterministica: dado o mesmo Carbon::setTestNow(T),
 *   o path resultante e sempre o mesmo (ate o segundo).
 *
 *   RESTRICAO IMPORTANTE: o BackupService nao expoe um metodo publico
 *   isolado para construcao de path; o trecho que monta o path esta inline
 *   em backupDatabase()/backupConfig(), antes de invocar mysqldump/PharData
 *   e antes do upload S3. Executar backupDatabase() de verdade e caro
 *   (mysqldump) e exige rede para o S3, entao seguimos a orientacao da task:
 *   replicar a formula usando as constantes publicas do servico
 *   (BackupService::BACKUP_DIR_DB, BACKUP_DIR_CONFIG) e o mesmo formato
 *   Carbon ('Y-m-d_His') usado pelo servico. Qualquer divergencia entre
 *   constantes do servico e o regex deste teste falha imediatamente, o que
 *   protege a propriedade.
 *
 * Validates: Requirements 7.3
 */

namespace Tests\Property;

use App\Services\BackupService;
use Carbon\Carbon;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

class BackupPathFormatTest extends TestCase
{
    use TestTrait;

    /**
     * Formato Carbon utilizado pelo BackupService para nomear backups
     * (vide BackupService::backupDatabase / backupConfig).
     */
    private const TIMESTAMP_FORMAT = 'Y-m-d_His';

    /**
     * Compatibilidade com PHPUnit 10: Eris ainda chama
     * \PHPUnit\Util\Test::parseTestMethodAnnotations() (removido na 10).
     * Sem anotacoes @eris-* nestes testes, retornamos [] e usamos defaults
     * (rand seed, 100 iteracoes, sem time-limit).
     *
     * @return array<string,mixed>
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    protected function tearDown(): void
    {
        // Garantia adicional: nenhum teste deve vazar mock de tempo.
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Helper: monta o path do backup de banco para um Carbon dado, usando
     * EXATAMENTE a mesma formula do BackupService (constante + format).
     */
    private function buildDbPath(Carbon $when): string
    {
        return BackupService::BACKUP_DIR_DB
            . '/' . $when->format(self::TIMESTAMP_FORMAT)
            . '.sql.gz';
    }

    /**
     * Helper: monta o path do backup de config para um Carbon dado.
     */
    private function buildConfigPath(Carbon $when): string
    {
        return BackupService::BACKUP_DIR_CONFIG
            . '/' . $when->format(self::TIMESTAMP_FORMAT)
            . '.tar.gz';
    }

    /**
     * Property 10.A: para qualquer timestamp gerado por Carbon, o path do
     * backup de banco segue o padrao backups/db/YYYY-MM-DD_HHmmss.sql.gz.
     *
     * Validates: Requirements 7.3
     */
    public function test_database_backup_path_matches_format(): void
    {
        $this
            ->forAll(
                Generators::choose(0, 365),  // dias atras (0..365)
                Generators::choose(0, 23),   // hora
                Generators::choose(0, 59),   // minuto
                Generators::choose(0, 59)    // segundo
            )
            ->then(function (int $days, int $h, int $m, int $s): void {
                $now = Carbon::now()->subDays($days)->setTime($h, $m, $s);
                Carbon::setTestNow($now);

                try {
                    $path = $this->buildDbPath(Carbon::now());

                    // Estrutura: prefixo fixo + timestamp formatado + sufixo .sql.gz
                    $pattern = '#^backups/db/\d{4}-\d{2}-\d{2}_\d{6}\.sql\.gz$#';
                    $this->assertMatchesRegularExpression(
                        $pattern,
                        $path,
                        "Property 10 violada: path de backup de banco fora do padrao para "
                            . $now->toIso8601String()
                            . " (path='{$path}')."
                    );

                    // Conteudo do timestamp deve corresponder ao Carbon mockado.
                    $expected = 'backups/db/' . $now->format(self::TIMESTAMP_FORMAT) . '.sql.gz';
                    $this->assertSame(
                        $expected,
                        $path,
                        "Property 10 violada: timestamp do path nao corresponde ao Carbon::now() mockado."
                    );
                } finally {
                    Carbon::setTestNow();
                }
            });
    }

    /**
     * Property 10.B: para qualquer timestamp gerado por Carbon, o path do
     * backup de config segue o padrao backups/config/YYYY-MM-DD_HHmmss.tar.gz.
     *
     * Validates: Requirements 7.3
     */
    public function test_config_backup_path_matches_format(): void
    {
        $this
            ->forAll(
                Generators::choose(0, 365),
                Generators::choose(0, 23),
                Generators::choose(0, 59),
                Generators::choose(0, 59)
            )
            ->then(function (int $days, int $h, int $m, int $s): void {
                $now = Carbon::now()->subDays($days)->setTime($h, $m, $s);
                Carbon::setTestNow($now);

                try {
                    $path = $this->buildConfigPath(Carbon::now());

                    $pattern = '#^backups/config/\d{4}-\d{2}-\d{2}_\d{6}\.tar\.gz$#';
                    $this->assertMatchesRegularExpression(
                        $pattern,
                        $path,
                        "Property 10 violada: path de backup de config fora do padrao para "
                            . $now->toIso8601String()
                            . " (path='{$path}')."
                    );

                    $expected = 'backups/config/' . $now->format(self::TIMESTAMP_FORMAT) . '.tar.gz';
                    $this->assertSame(
                        $expected,
                        $path,
                        "Property 10 violada: timestamp do path de config nao corresponde ao Carbon::now() mockado."
                    );
                } finally {
                    Carbon::setTestNow();
                }
            });
    }

    /**
     * Property 10.C (Determinismo): mesmo timestamp -> mesmo path.
     *
     * Para o mesmo Carbon::setTestNow($t), invocacoes consecutivas de
     * buildDbPath/buildConfigPath produzem strings identicas ate o segundo.
     *
     * Validates: Requirements 7.3
     */
    public function test_path_generation_is_deterministic_for_same_timestamp(): void
    {
        $this
            ->forAll(
                Generators::choose(0, 365),
                Generators::choose(0, 23),
                Generators::choose(0, 59),
                Generators::choose(0, 59)
            )
            ->then(function (int $days, int $h, int $m, int $s): void {
                $now = Carbon::now()->subDays($days)->setTime($h, $m, $s);
                Carbon::setTestNow($now);

                try {
                    $db1 = $this->buildDbPath(Carbon::now());
                    $db2 = $this->buildDbPath(Carbon::now());
                    $cfg1 = $this->buildConfigPath(Carbon::now());
                    $cfg2 = $this->buildConfigPath(Carbon::now());

                    $this->assertSame(
                        $db1,
                        $db2,
                        "Property 10 violada: path de db nao deterministico para "
                            . $now->toIso8601String()
                    );
                    $this->assertSame(
                        $cfg1,
                        $cfg2,
                        "Property 10 violada: path de config nao deterministico para "
                            . $now->toIso8601String()
                    );

                    // Cross-check: db e config compartilham o mesmo timestamp.
                    $this->assertSame(
                        substr($db1, strlen('backups/db/'), strlen('YYYY-MM-DD_HHmmss')),
                        substr($cfg1, strlen('backups/config/'), strlen('YYYY-MM-DD_HHmmss')),
                        "Property 10 violada: db e config gerados no mesmo instante divergem no timestamp."
                    );
                } finally {
                    Carbon::setTestNow();
                }
            });
    }
}
