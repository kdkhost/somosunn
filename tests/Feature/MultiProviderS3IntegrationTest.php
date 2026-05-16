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
 * Sistema UNN - Integration test do fluxo completo multi-provider:
 *   1) Salvar config dos 3 provedores
 *   2) Alternar provedor ativo
 *   3) Verificar que filesystems.disks.s3 reflete o provedor ativo
 *      apos UploadStorage::applyRuntimeConfig()
 *   4) Verificar que cache S3 e flushed no switch (via marker)
 *
 * Spec: .kiro/specs/multi-provider-s3-storage (task 6.1)
 * Requirements: 2.1, 2.2, 2.4
 */

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\StorageProviderConfig;
use App\Support\StorageProviderRegistry;
use App\Support\UploadStorage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MultiProviderS3IntegrationTest extends TestCase
{
    private string $sqlitePath;
    private StorageProviderRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-multi-provider-integration.sqlite');
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

    public function test_complete_flow_save_three_providers_switch_and_verify_disk_config(): void
    {
        // 1) Salvar config dos 3 provedores com bucket distinto.
        $idriveConfig = new StorageProviderConfig(
            accessKey: 'IDRIVE_KEY',
            secretKey: 'IDRIVE_SECRET',
            bucket: 'bucket-idrive',
            region: 'us-east-2',
            endpoint: 's3.idrivee2.example.com',
            pathStyle: true,
        );
        $wasabiConfig = new StorageProviderConfig(
            accessKey: 'WASABI_KEY',
            secretKey: 'WASABI_SECRET',
            bucket: 'bucket-wasabi',
            region: 'us-east-1',
            endpoint: 's3.us-east-1.wasabisys.com',
            pathStyle: true,
        );
        $awsConfig = new StorageProviderConfig(
            accessKey: 'AWS_KEY',
            secretKey: 'AWS_SECRET',
            bucket: 'bucket-aws',
            region: 'sa-east-1',
            endpoint: '',
            pathStyle: false,
        );

        $this->registry->persistConfig('idrive', $idriveConfig);
        $this->registry->persistConfig('wasabi', $wasabiConfig);
        $this->registry->persistConfig('aws', $awsConfig);

        // Verificacao: as 3 configuracoes devem coexistir.
        $this->assertTrue($this->registry->isConfigured('idrive'));
        $this->assertTrue($this->registry->isConfigured('wasabi'));
        $this->assertTrue($this->registry->isConfigured('aws'));

        // 2) Habilita storage_driver=s3 e parte ativa = idrive.
        Setting::set('storage_driver', 's3');
        $this->registry->setActiveProvider('idrive');

        // Aplicar runtime config e validar que o disco S3 aponta para IDrive.
        UploadStorage::applyRuntimeConfig();

        $this->assertSame('IDRIVE_KEY', config('filesystems.disks.s3.key'));
        $this->assertSame('IDRIVE_SECRET', config('filesystems.disks.s3.secret'));
        $this->assertSame('bucket-idrive', config('filesystems.disks.s3.bucket'));
        $this->assertSame('us-east-2', config('filesystems.disks.s3.region'));
        $this->assertSame(
            'https://s3.idrivee2.example.com',
            config('filesystems.disks.s3.endpoint'),
            'endpoint deve ser normalizado para https://'
        );
        $this->assertTrue((bool) config('filesystems.disks.s3.use_path_style_endpoint'));

        // 3) Switch para Wasabi e revalidar.
        $this->registry->setActiveProvider('wasabi');
        UploadStorage::applyRuntimeConfig();

        $this->assertSame('WASABI_KEY', config('filesystems.disks.s3.key'));
        $this->assertSame('bucket-wasabi', config('filesystems.disks.s3.bucket'));
        $this->assertSame('us-east-1', config('filesystems.disks.s3.region'));

        // 4) Switch para AWS e revalidar (path_style=false).
        $this->registry->setActiveProvider('aws');
        UploadStorage::applyRuntimeConfig();

        $this->assertSame('AWS_KEY', config('filesystems.disks.s3.key'));
        $this->assertSame('bucket-aws', config('filesystems.disks.s3.bucket'));
        $this->assertSame('sa-east-1', config('filesystems.disks.s3.region'));
        $this->assertNull(
            config('filesystems.disks.s3.endpoint'),
            'AWS sem endpoint custom -> diskConfigArray retorna null'
        );
        $this->assertFalse((bool) config('filesystems.disks.s3.use_path_style_endpoint'));

        // 5) Switch para Local: storage_driver=public, sem usar S3.
        $this->registry->setActiveProvider(StorageProviderRegistry::PROVIDER_LOCAL);
        Setting::set('storage_driver', 'public');
        Setting::flushRuntimeCache();
        UploadStorage::applyRuntimeConfig();

        $this->assertSame('public', config('uploads.effective_disk'));
    }

    public function test_switching_to_unconfigured_provider_falls_back_to_local_disk(): void
    {
        // Apenas idrive esta configurado.
        $this->registry->persistConfig('idrive', new StorageProviderConfig(
            'AK', 'SK', 'b', 'us-east-1'
        ));

        Setting::set('storage_driver', 's3');

        // Active = aws (que esta vazio). Spec Req 1.4: fallback para public.
        $this->registry->setActiveProvider('aws');
        UploadStorage::applyRuntimeConfig();

        // Como aws nao tem creds e idrive nao e' o ativo, o resolveActiveProviderConfig
        // retorna null -> cai no schema legado -> sem creds storage_* -> public.
        $this->assertSame('public', config('uploads.effective_disk'));
    }

    public function test_switching_provider_invalidates_s3_location_cache(): void
    {
        $this->registry->persistConfig('idrive', new StorageProviderConfig(
            'AK', 'SK', 'b1', 'r1'
        ));
        $this->registry->persistConfig('wasabi', new StorageProviderConfig(
            'AK2', 'SK2', 'b2', 'r2'
        ));

        $this->registry->setActiveProvider('idrive');

        // Versao inicial do cache.
        $versionBefore = (string) Cache::get('s3_location_cache_version', '');

        // Switch para outro provedor invoca flushS3LocationCache que
        // atualiza s3_location_cache_version.
        $this->registry->setActiveProvider('wasabi');

        $versionAfter = (string) Cache::get('s3_location_cache_version', '');

        $this->assertNotSame(
            $versionBefore,
            $versionAfter,
            'switch de provedor deveria atualizar s3_location_cache_version (Req 2.4)'
        );
    }

    public function test_persisting_config_for_active_provider_applies_runtime_config_immediately(): void
    {
        Setting::set('storage_driver', 's3');
        $this->registry->setActiveProvider('aws');

        // Salvar config NO PROVEDOR ATIVO deve refletir imediatamente no
        // disco s3 sem precisar de outra chamada de applyRuntimeConfig.
        $awsConfig = new StorageProviderConfig(
            'NEW_AWS_KEY', 'NEW_AWS_SECRET', 'novo-bucket-aws', 'us-east-2'
        );
        $this->registry->persistConfig('aws', $awsConfig);

        $this->assertSame('NEW_AWS_KEY', config('filesystems.disks.s3.key'));
        $this->assertSame('novo-bucket-aws', config('filesystems.disks.s3.bucket'));
    }
}
