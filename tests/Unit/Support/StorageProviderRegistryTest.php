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
 * Sistema UNN - Unit tests para StorageProviderRegistry.
 *
 * Cobre:
 *   - activeProvider() com fallback para default
 *   - setActiveProvider() persiste, valida e invoca flushS3LocationCache
 *   - configFor() le 7 chaves prefixadas do Setting cache
 *   - allConfigs() retorna os 3 provedores
 *   - persistConfig() salva apenas as 7 chaves do provedor (NAO toca outros)
 *   - isConfigured() delega para Config::isValid
 *   - diskConfigArray() monta config compativel com filesystems.disks.s3
 *   - displayName() retorna labels esperadas
 *   - guard rejeita providers desconhecidos
 *
 * Setup isolado: SQLite por arquivo proprio com tabela `settings`,
 * mesmo padrao usado em AdvancedRateLimitMiddlewareTest e
 * AnomalyDetectorServiceTest.
 *
 * Spec: .kiro/specs/multi-provider-s3-storage (task 3.5)
 * Requirements: 1.1-1.5, 2.1, 2.4, 8.4
 */

namespace Tests\Unit\Support;

use App\Models\Setting;
use App\Support\StorageProviderConfig;
use App\Support\StorageProviderRegistry;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StorageProviderRegistryTest extends TestCase
{
    private string $sqlitePath;
    private StorageProviderRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        // Banco SQLite isolado por arquivo (mesmo padrao usado em
        // AdvancedRateLimitMiddlewareTest).
        $this->sqlitePath = database_path('testing-storage-provider-registry.sqlite');
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

        // Cache file/array para o flushS3LocationCache nao explodir
        config()->set('cache.default', 'array');

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

    /* -------------- activeProvider -------------- */

    public function test_active_provider_falls_back_to_default_when_setting_absent(): void
    {
        $this->assertSame(
            StorageProviderRegistry::DEFAULT_PROVIDER,
            $this->registry->activeProvider()
        );
    }

    public function test_active_provider_returns_stored_value_when_valid(): void
    {
        Setting::set(StorageProviderRegistry::KEY_ACTIVE_PROVIDER, 'wasabi');
        Setting::flushRuntimeCache();

        $this->assertSame('wasabi', $this->registry->activeProvider());
    }

    public function test_active_provider_returns_local_when_explicitly_set(): void
    {
        Setting::set(StorageProviderRegistry::KEY_ACTIVE_PROVIDER, 'local');
        Setting::flushRuntimeCache();

        $this->assertSame(StorageProviderRegistry::PROVIDER_LOCAL, $this->registry->activeProvider());
    }

    public function test_active_provider_falls_back_to_default_when_value_is_invalid(): void
    {
        Setting::set(StorageProviderRegistry::KEY_ACTIVE_PROVIDER, 'invalido');
        Setting::flushRuntimeCache();

        $this->assertSame(
            StorageProviderRegistry::DEFAULT_PROVIDER,
            $this->registry->activeProvider()
        );
    }

    public function test_active_provider_normalizes_case_and_whitespace(): void
    {
        Setting::set(StorageProviderRegistry::KEY_ACTIVE_PROVIDER, '  AWS  ');
        Setting::flushRuntimeCache();

        $this->assertSame('aws', $this->registry->activeProvider());
    }

    /* -------------- setActiveProvider -------------- */

    public function test_set_active_provider_persists_and_can_be_read_back(): void
    {
        $this->registry->setActiveProvider('aws');

        $this->assertSame('aws', $this->registry->activeProvider());
        $this->assertSame('aws', Setting::get(StorageProviderRegistry::KEY_ACTIVE_PROVIDER));
    }

    public function test_set_active_provider_accepts_local(): void
    {
        $this->registry->setActiveProvider(StorageProviderRegistry::PROVIDER_LOCAL);
        $this->assertSame(StorageProviderRegistry::PROVIDER_LOCAL, $this->registry->activeProvider());
    }

    public function test_set_active_provider_throws_for_unknown_provider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->registry->setActiveProvider('cloudflare');
    }

    /* -------------- configFor / allConfigs -------------- */

    public function test_config_for_returns_empty_config_when_no_settings(): void
    {
        $config = $this->registry->configFor('idrive');

        $this->assertInstanceOf(StorageProviderConfig::class, $config);
        $this->assertFalse($config->isValid());
        $this->assertSame('', $config->accessKey);
    }

    public function test_config_for_reads_settings_with_correct_prefix(): void
    {
        Setting::set('wasabi_access_key', 'WASABI_AK');
        Setting::set('wasabi_secret_key', 'WASABI_SK');
        Setting::set('wasabi_bucket', 'wasabi-bucket');
        Setting::set('wasabi_region', 'us-east-1');
        Setting::set('wasabi_endpoint', 's3.us-east-1.wasabisys.com');
        Setting::set('wasabi_url', '');
        Setting::set('wasabi_path_style', '1');
        Setting::flushRuntimeCache();

        $config = $this->registry->configFor('wasabi');

        $this->assertTrue($config->isValid());
        $this->assertSame('WASABI_AK', $config->accessKey);
        $this->assertSame('WASABI_SK', $config->secretKey);
        $this->assertSame('wasabi-bucket', $config->bucket);
        $this->assertSame('us-east-1', $config->region);
        $this->assertSame('s3.us-east-1.wasabisys.com', $config->endpoint);
        $this->assertSame('', $config->url);
        $this->assertTrue($config->pathStyle);
    }

    public function test_config_for_throws_for_unknown_provider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->registry->configFor('cloudflare');
    }

    public function test_all_configs_returns_three_providers_keyed_by_name(): void
    {
        $all = $this->registry->allConfigs();

        $this->assertCount(3, $all);
        $this->assertArrayHasKey('idrive', $all);
        $this->assertArrayHasKey('wasabi', $all);
        $this->assertArrayHasKey('aws', $all);

        foreach ($all as $config) {
            $this->assertInstanceOf(StorageProviderConfig::class, $config);
        }
    }

    /* -------------- persistConfig (isolation) -------------- */

    public function test_persist_config_saves_only_keys_of_target_provider(): void
    {
        // Pre-popula AWS para ter algo que NAO deve ser tocado.
        $awsConfig = new StorageProviderConfig('AWS_AK', 'AWS_SK', 'aws-bucket', 'us-east-1');
        $this->registry->persistConfig('aws', $awsConfig);

        // Salva config no IDrive.
        $idriveConfig = new StorageProviderConfig('IDR_AK', 'IDR_SK', 'idr-bucket', 'us-east-2');
        $this->registry->persistConfig('idrive', $idriveConfig);

        // AWS deve permanecer intocada (Req 1.5).
        Setting::flushRuntimeCache();
        $awsAfter = $this->registry->configFor('aws');
        $this->assertSame('AWS_AK', $awsAfter->accessKey);
        $this->assertSame('aws-bucket', $awsAfter->bucket);
        $this->assertSame('us-east-1', $awsAfter->region);

        // IDrive deve refletir a nova config.
        $idriveAfter = $this->registry->configFor('idrive');
        $this->assertSame('IDR_AK', $idriveAfter->accessKey);
        $this->assertSame('idr-bucket', $idriveAfter->bucket);
        $this->assertSame('us-east-2', $idriveAfter->region);
    }

    public function test_persist_config_overwrites_only_existing_provider_fields(): void
    {
        $first = new StorageProviderConfig('AK1', 'SK1', 'b1', 'r1', 'e1', 'u1', true);
        $this->registry->persistConfig('idrive', $first);

        $second = new StorageProviderConfig('AK2', 'SK2', 'b2', 'r2', 'e2', 'u2', false);
        $this->registry->persistConfig('idrive', $second);

        Setting::flushRuntimeCache();
        $afterReload = $this->registry->configFor('idrive');

        $this->assertSame('AK2', $afterReload->accessKey);
        $this->assertSame('b2', $afterReload->bucket);
        $this->assertFalse($afterReload->pathStyle);
    }

    public function test_persist_config_throws_for_unknown_provider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->registry->persistConfig('cloudflare', new StorageProviderConfig());
    }

    /* -------------- isConfigured -------------- */

    public function test_is_configured_returns_false_when_provider_has_no_credentials(): void
    {
        $this->assertFalse($this->registry->isConfigured('idrive'));
        $this->assertFalse($this->registry->isConfigured('wasabi'));
        $this->assertFalse($this->registry->isConfigured('aws'));
    }

    public function test_is_configured_returns_true_after_persist_with_required_fields(): void
    {
        $this->registry->persistConfig('idrive', new StorageProviderConfig('AK', 'SK', 'b', 'r'));

        $this->assertTrue($this->registry->isConfigured('idrive'));
        $this->assertFalse($this->registry->isConfigured('wasabi'), 'wasabi nao foi configurado');
    }

    public function test_is_configured_returns_true_for_local_always(): void
    {
        $this->assertTrue($this->registry->isConfigured(StorageProviderRegistry::PROVIDER_LOCAL));
    }

    /* -------------- diskConfigArray -------------- */

    public function test_disk_config_array_has_expected_filesystems_s3_shape(): void
    {
        $config = new StorageProviderConfig(
            accessKey: 'AK',
            secretKey: 'SK',
            bucket: 'my-bucket',
            region: 'sa-east-1',
            endpoint: 's3.example.com',
            url: 'https://cdn.example.com',
            pathStyle: true,
        );
        $this->registry->persistConfig('aws', $config);
        Setting::flushRuntimeCache();

        $disk = $this->registry->diskConfigArray('aws');

        $this->assertSame('s3', $disk['driver']);
        $this->assertSame('AK', $disk['key']);
        $this->assertSame('SK', $disk['secret']);
        $this->assertSame('my-bucket', $disk['bucket']);
        $this->assertSame('sa-east-1', $disk['region']);
        $this->assertSame('https://cdn.example.com', $disk['url']);
        $this->assertSame('https://s3.example.com', $disk['endpoint'], 'endpoint sem scheme deve ser normalizado para https://');
        $this->assertTrue($disk['use_path_style_endpoint']);
        $this->assertFalse($disk['throw']);
        $this->assertSame('public', $disk['visibility']);
    }

    public function test_disk_config_array_uses_us_east_1_when_region_blank(): void
    {
        // Salva config valida exceto region (vazio aciona default us-east-1).
        $this->registry->persistConfig('idrive', new StorageProviderConfig('AK', 'SK', 'b', ''));
        Setting::flushRuntimeCache();

        $disk = $this->registry->diskConfigArray('idrive');
        $this->assertSame('us-east-1', $disk['region']);
    }

    public function test_disk_config_array_passes_endpoint_with_scheme_unchanged(): void
    {
        $config = new StorageProviderConfig('AK', 'SK', 'b', 'r', endpoint: 'https://already.example.com');
        $this->registry->persistConfig('idrive', $config);
        Setting::flushRuntimeCache();

        $disk = $this->registry->diskConfigArray('idrive');
        $this->assertSame('https://already.example.com', $disk['endpoint']);
    }

    public function test_disk_config_array_returns_null_endpoint_when_blank(): void
    {
        $this->registry->persistConfig('aws', new StorageProviderConfig('AK', 'SK', 'b', 'us-east-1'));
        Setting::flushRuntimeCache();

        $disk = $this->registry->diskConfigArray('aws');
        $this->assertNull($disk['endpoint']);
    }

    /* -------------- displayName -------------- */

    public function test_display_name_returns_human_readable_labels(): void
    {
        $this->assertSame('IDrive e2', $this->registry->displayName('idrive'));
        $this->assertSame('Wasabi', $this->registry->displayName('wasabi'));
        $this->assertSame('AWS S3', $this->registry->displayName('aws'));
        $this->assertSame('Local (disco publico)', $this->registry->displayName('local'));
    }

    public function test_display_name_falls_back_to_raw_key_for_unknown(): void
    {
        $this->assertSame('???', $this->registry->displayName('???'));
    }

    /* -------------- testConnection (apenas o caminho de creds invalidas) -------------- */

    public function test_test_connection_fails_fast_when_credentials_are_invalid(): void
    {
        // Sem creds preenchidas, testConnection retorna failed sem fazer I/O.
        $result = $this->registry->testConnection('idrive');

        $this->assertSame(\App\Support\StorageTestResult::STATUS_FAILED, $result->status);
        $this->assertSame('idrive', $result->provider);
        $this->assertSame([], $result->steps, 'sem creds nao deve chegar a executar nenhum step');
        $this->assertNotNull($result->errorMessage);
        $this->assertStringContainsString('credenciais', strtolower((string) $result->errorMessage));
    }

    public function test_test_connection_throws_for_unknown_provider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->registry->testConnection('cloudflare');
    }
}
