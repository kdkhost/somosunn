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
 * Sistema UNN - Property test (Property: Provider Config Isolation).
 *
 * Property:
 *   Para QUALQUER ordem de salvamentos em P1, P2, P3 e QUALQUER
 *   conjunto de valores gerado pelo Eris, salvar P_i nunca altera
 *   nenhum dos campos de P_j (j != i).
 *
 *   Equivale ao requisito 1.2 (persistir todos os campos do
 *   Provider_Config sem modificar as configuracoes dos demais
 *   provedores) + 1.5 (configuracoes inativas inalteradas).
 *
 * Estrategia:
 *   - SQLite isolado por iteracao (banco em memoria via :memory:)
 *     para zerar o estado entre rodadas e evitar I/O no MySQL real.
 *   - Setting::flushRuntimeCache entre operacoes para forcar releitura
 *     do banco (e nao confiar no cache estatico que ja esta populado).
 *   - Geramos um trio de StorageProviderConfig com Eris e validamos
 *     que apos salvar todos eles em ORDEM ALEATORIA cada um continua
 *     intacto.
 *
 * Spec: .kiro/specs/multi-provider-s3-storage (task 3.4)
 * Requirements: 1.2, 1.5
 */

namespace Tests\Property;

use App\Models\Setting;
use App\Support\StorageProviderConfig;
use App\Support\StorageProviderRegistry;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StorageProviderConfigIsolationTest extends TestCase
{
    use TestTrait;

    private string $sqlitePath;
    private StorageProviderRegistry $registry;

    /**
     * PHPUnit 10 + Eris 0.14.x compat: a trait do Eris ainda invoca
     * \PHPUnit\Util\Test::parseTestMethodAnnotations() (removida no
     * PHPUnit 10). Retornar [] faz a trait operar com defaults
     * (100 iteracoes, sem time-limit).
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

        // SQLite em arquivo isolado (mesmo padrao usado em outros
        // tests Property que mexem em DB).
        $this->sqlitePath = database_path('testing-storage-provider-isolation.sqlite');
        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }
        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        config()->set('cache.default', 'array');
        Cache::store('array')->flush();

        Setting::flushRuntimeCache();

        $this->registry = new StorageProviderRegistry();
    }

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();
        DB::disconnect('sqlite');
        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }
        parent::tearDown();
    }

    /**
     * Property: salvar P_i nao altera P_j para j != i.
     *
     * Geramos um trio de configs nao triviais (todas com isValid()=true)
     * e exercitamos varias ordens de salvamento, garantindo que apos a
     * sequencia de salvamentos cada provedor reflete EXATAMENTE seus
     * proprios valores e nada do(s) outro(s).
     *
     * Validates: Requirements 1.2, 1.5
     */
    public function test_persist_config_isolates_each_provider_from_the_others(): void
    {
        // Geradores de campo: usamos elements para limitar o universo a
        // valores nao vazios e seguros (Eris regex causaria dependencia
        // adicional do icomefromthenet/reverse-regex que nao temos).
        $accessKeyGen = Generator\elements([
            'AK1A2BC3DE4FG5HI6JKL', 'AK7Z8YA9XW0VC1UB2TPS', 'AK0M1N2O3P4Q5R6S7T8U',
            'AK_TESTE_1', 'AK_TESTE_2', 'AK_TESTE_3',
        ]);
        $secretKeyGen = Generator\elements([
            'sk-secret-1234567890abcdef', 'sk-secret-fedcba0987654321',
            'sk-some-other-secret-here', 'sk-another-test-value-x',
        ]);
        $bucketGen = Generator\elements([
            'bucket-alpha', 'bucket-bravo', 'bucket-charlie',
            'bucket-delta', 'bucket-echo',
        ]);
        $regionGen = Generator\elements([
            'us-east-1', 'us-west-2', 'sa-east-1', 'ca-central-1', 'eu-west-1',
        ]);
        $endpointGen = Generator\elements([
            's3.us-east-1.example.com', 's3.wasabisys.com', 's3.idrivee2.com', '',
        ]);
        $urlGen = Generator\elements([
            'https://cdn.example.com', 'https://files.cdn.com', '',
        ]);
        $pathStyleGen = Generator\elements([true, false]);
        $orderGen = Generator\elements([
            ['idrive', 'wasabi', 'aws'],
            ['wasabi', 'aws', 'idrive'],
            ['aws', 'idrive', 'wasabi'],
            ['idrive', 'aws', 'wasabi'],
            ['aws', 'wasabi', 'idrive'],
            ['wasabi', 'idrive', 'aws'],
        ]);

        $this
            ->forAll(
                $accessKeyGen, $secretKeyGen, $bucketGen, $regionGen, $endpointGen, $urlGen, $pathStyleGen, // P_idrive
                $accessKeyGen, $secretKeyGen, $bucketGen, $regionGen, $endpointGen, $urlGen, $pathStyleGen, // P_wasabi
                $accessKeyGen, $secretKeyGen, $bucketGen, $regionGen, $endpointGen, $urlGen, $pathStyleGen, // P_aws
                $orderGen
            )
            ->then(function (
                string $iAk, string $iSk, string $iB, string $iR, string $iE, string $iU, bool $iP,
                string $wAk, string $wSk, string $wB, string $wR, string $wE, string $wU, bool $wP,
                string $aAk, string $aSk, string $aB, string $aR, string $aE, string $aU, bool $aP,
                array $order
            ): void {
                // Limpa estado entre iteracoes (Eris reusa o mesmo metodo).
                DB::table('settings')->delete();
                Setting::flushRuntimeCache();

                $configs = [
                    'idrive' => new StorageProviderConfig($iAk, $iSk, $iB, $iR, $iE, $iU, $iP),
                    'wasabi' => new StorageProviderConfig($wAk, $wSk, $wB, $wR, $wE, $wU, $wP),
                    'aws'    => new StorageProviderConfig($aAk, $aSk, $aB, $aR, $aE, $aU, $aP),
                ];

                // Salva os 3 provedores na ordem aleatoria gerada.
                foreach ($order as $providerKey) {
                    $this->registry->persistConfig($providerKey, $configs[$providerKey]);
                }

                // Le tudo de volta forcando releitura do banco.
                Setting::flushRuntimeCache();

                // Cada provedor deve refletir EXATAMENTE sua propria config.
                foreach ($configs as $providerKey => $expected) {
                    $actual = $this->registry->configFor($providerKey);

                    $context = sprintf(
                        '[provider=%s, order=[%s]]',
                        $providerKey,
                        implode(',', $order)
                    );

                    $this->assertSame(
                        $expected->accessKey,
                        $actual->accessKey,
                        "Property violada: access_key de {$providerKey} divergiu apos saves dos demais. {$context}"
                    );
                    $this->assertSame(
                        $expected->secretKey,
                        $actual->secretKey,
                        "Property violada: secret_key de {$providerKey} divergiu. {$context}"
                    );
                    $this->assertSame(
                        $expected->bucket,
                        $actual->bucket,
                        "Property violada: bucket de {$providerKey} divergiu. {$context}"
                    );
                    $this->assertSame(
                        $expected->region,
                        $actual->region,
                        "Property violada: region de {$providerKey} divergiu. {$context}"
                    );
                    $this->assertSame(
                        $expected->endpoint,
                        $actual->endpoint,
                        "Property violada: endpoint de {$providerKey} divergiu. {$context}"
                    );
                    $this->assertSame(
                        $expected->url,
                        $actual->url,
                        "Property violada: url de {$providerKey} divergiu. {$context}"
                    );
                    $this->assertSame(
                        $expected->pathStyle,
                        $actual->pathStyle,
                        "Property violada: path_style de {$providerKey} divergiu. {$context}"
                    );
                }
            });
    }

    /**
     * Property complementar: salvar APENAS P_i nao cria nem modifica
     * chaves no namespace de P_j.
     *
     * Cobre o caso em que o usuario configura SOMENTE Wasabi e os
     * campos de IDrive e AWS devem permanecer ausentes (ou vazios)
     * no banco apos o save.
     */
    public function test_persist_config_does_not_create_keys_for_other_providers(): void
    {
        $accessKeyGen = Generator\elements(['AK_X', 'AK_Y', 'AK_Z']);
        $secretKeyGen = Generator\elements(['sk_aaa', 'sk_bbb', 'sk_ccc']);
        $bucketGen    = Generator\elements(['b1', 'b2', 'b3']);
        $regionGen    = Generator\elements(['us-east-1', 'sa-east-1']);
        $providerGen  = Generator\elements(['idrive', 'wasabi', 'aws']);

        $this
            ->forAll($providerGen, $accessKeyGen, $secretKeyGen, $bucketGen, $regionGen)
            ->then(function (string $providerKey, string $ak, string $sk, string $b, string $r): void {
                DB::table('settings')->delete();
                Setting::flushRuntimeCache();

                $config = new StorageProviderConfig($ak, $sk, $b, $r);
                $this->registry->persistConfig($providerKey, $config);

                $others = array_diff(StorageProviderRegistry::PROVIDERS, [$providerKey]);

                Setting::flushRuntimeCache();

                foreach ($others as $other) {
                    $otherConfig = $this->registry->configFor($other);

                    $this->assertFalse(
                        $otherConfig->isValid(),
                        sprintf(
                            'Property violada: salvar P=%s populou P=%s (access_key=%s, bucket=%s).',
                            $providerKey,
                            $other,
                            $otherConfig->accessKey,
                            $otherConfig->bucket
                        )
                    );

                    // Adicionalmente, verifica diretamente no banco que
                    // nenhuma chave do prefixo dos OUTROS provedores tem
                    // valor nao-vazio.
                    $rows = DB::table('settings')
                        ->where('key', 'like', $other . '_%')
                        ->where('value', '!=', '')
                        ->get();

                    $this->assertCount(
                        0,
                        $rows,
                        sprintf(
                            'Property violada: chaves nao vazias do prefixo %s_ apos save em %s: %s',
                            $other,
                            $providerKey,
                            $rows->pluck('key')->implode(',')
                        )
                    );
                }
            });
    }
}
