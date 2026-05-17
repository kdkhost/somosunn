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
 * Sistema UNN - Integration test do fluxo HTTP de troca de
 * provedor S3 via SettingController::update().
 *
 * Cobre o cenario real: usuario abre /admin/settings,
 * preenche o select "Driver de Armazenamento" + os campos
 * prefixados {provider}_* e submete o form. O controller deve:
 *   1) Persistir as chaves storage_active_provider/storage_driver
 *   2) Persistir as 7 chaves do provedor selecionado
 *   3) Auto-copiar storage_* (legado) para {provider}_* quando
 *      o request inclui ambos (compat com forms antigos)
 *
 * Tambem testa que o endpoint de teste de conexao
 * (settings.test-s3-provider) retorna JSON com a estrutura
 * esperada para ?provider=idrive|wasabi|aws.
 *
 * Spec: .kiro/specs/multi-provider-s3-storage (task 6.1 - extensao)
 * Requirements: 2.1, 2.2, 2.4, 5.5, 8.1
 */

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Support\StorageProviderRegistry;
use App\Support\UploadStorage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageProviderSwitchTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-storage-provider-switch.sqlite');
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

        // Tabela audit_logs precisa existir porque SettingController::update
        // chama AuditLogService::log() ao final do save.
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('action', 100);
            $table->string('target_type', 100)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('request_id', 100)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        // Storage::fake('s3') evita qualquer chamada de rede em
        // testes que indiretamente usem Storage::disk('s3').
        Storage::fake('s3');
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

    public function test_settings_update_persists_storage_active_provider_and_prefixed_fields(): void
    {
        $this->withoutMiddleware();

        $payload = [
            'current_group' => 'storage',
            'storage_driver' => 's3',
            'storage_active_provider' => 'wasabi',
            'wasabi_access_key' => 'WASABI_AK',
            'wasabi_secret_key' => 'WASABI_SK',
            'wasabi_bucket' => 'wasabi-bucket-test',
            'wasabi_region' => 'us-east-1',
            'wasabi_endpoint' => 's3.us-east-1.wasabisys.com',
            'wasabi_url' => '',
            'wasabi_path_style' => '1',
        ];

        $response = $this->post(route('admin.settings.update'), $payload);

        $response->assertRedirect();

        Setting::flushRuntimeCache();

        $this->assertSame('s3', Setting::get('storage_driver'));
        $this->assertSame('wasabi', Setting::get('storage_active_provider'));
        $this->assertSame('WASABI_AK', Setting::get('wasabi_access_key'));
        $this->assertSame('wasabi-bucket-test', Setting::get('wasabi_bucket'));
        $this->assertSame('1', Setting::get('wasabi_path_style'));
    }

    public function test_settings_update_auto_copies_legacy_storage_keys_to_active_provider_namespace(): void
    {
        $this->withoutMiddleware();

        // Cenario: form antigo ainda envia storage_* sem prefixo + escolha
        // de provedor ativo. O controller deve replicar para idrive_*.
        $payload = [
            'current_group' => 'storage',
            'storage_driver' => 's3',
            'storage_active_provider' => 'idrive',
            'storage_access_key' => 'LEGACY_AK',
            'storage_secret_key' => 'LEGACY_SK',
            'storage_bucket' => 'legacy-bucket',
            'storage_region' => 'us-east-2',
            'storage_endpoint' => 'https://idrivee2.example.com',
            'storage_url' => '',
            'storage_path_style' => '1',
        ];

        $this->post(route('admin.settings.update'), $payload)->assertRedirect();

        Setting::flushRuntimeCache();

        // Schema legado preservado.
        $this->assertSame('LEGACY_AK', Setting::get('storage_access_key'));
        $this->assertSame('legacy-bucket', Setting::get('storage_bucket'));

        // Auto-copia para idrive_*.
        $this->assertSame('LEGACY_AK', Setting::get('idrive_access_key'));
        $this->assertSame('LEGACY_SK', Setting::get('idrive_secret_key'));
        $this->assertSame('legacy-bucket', Setting::get('idrive_bucket'));
        $this->assertSame('us-east-2', Setting::get('idrive_region'));
    }

    public function test_settings_update_switching_provider_keeps_other_provider_credentials_intact(): void
    {
        $this->withoutMiddleware();

        // Pre-popula AWS com credenciais.
        Setting::set('aws_access_key', 'AWS_PRE_AK');
        Setting::set('aws_secret_key', 'AWS_PRE_SK');
        Setting::set('aws_bucket', 'aws-pre-bucket');
        Setting::set('aws_region', 'sa-east-1');
        Setting::set('aws_path_style', '0');
        Setting::flushRuntimeCache();

        // Usuario configura o IDrive e escolhe IDrive como ativo.
        $payload = [
            'current_group' => 'storage',
            'storage_driver' => 's3',
            'storage_active_provider' => 'idrive',
            'idrive_access_key' => 'IDR_AK',
            'idrive_secret_key' => 'IDR_SK',
            'idrive_bucket' => 'idrive-bucket',
            'idrive_region' => 'us-east-1',
            'idrive_endpoint' => 'https://idrivee2.example.com',
            'idrive_url' => '',
            'idrive_path_style' => '1',
        ];

        $this->post(route('admin.settings.update'), $payload)->assertRedirect();
        Setting::flushRuntimeCache();

        // AWS deve permanecer intocada (Req 1.5).
        $this->assertSame('AWS_PRE_AK', Setting::get('aws_access_key'));
        $this->assertSame('aws-pre-bucket', Setting::get('aws_bucket'));
        $this->assertSame('sa-east-1', Setting::get('aws_region'));
        $this->assertSame('0', Setting::get('aws_path_style'));

        // IDrive deve refletir a nova config.
        $this->assertSame('IDR_AK', Setting::get('idrive_access_key'));
        $this->assertSame('idrive-bucket', Setting::get('idrive_bucket'));

        // E o ativo deve ser idrive.
        $this->assertSame('idrive', Setting::get('storage_active_provider'));
    }

    public function test_test_storage_provider_endpoint_returns_json_with_results_array_for_unconfigured_provider(): void
    {
        $this->withoutMiddleware();

        // testStorageProvider() exige isSuperadmin(). Como nao usamos middleware,
        // simulamos um usuario superadmin autenticado.
        $user = $this->makeFakeSuperadmin();
        $this->actingAs($user);

        // Sem creds preenchidas -> o endpoint deve retornar JSON
        // com success=false e mensagem indicando creds invalidas.
        $response = $this->postJson(route('admin.settings.test-s3-provider'), [
            'provider' => 'aws',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'provider',
            'status',
            'results',
        ]);
        $response->assertJson([
            'success' => false,
            'provider' => 'aws',
        ]);
    }

    public function test_test_storage_provider_endpoint_rejects_invalid_provider_name(): void
    {
        $this->withoutMiddleware();
        $user = $this->makeFakeSuperadmin();
        $this->actingAs($user);

        $response = $this->postJson(route('admin.settings.test-s3-provider'), [
            'provider' => 'cloudflare',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_full_switch_cycle_idrive_wasabi_aws_keeps_each_credentials_intact(): void
    {
        $this->withoutMiddleware();

        // Round 1: salva IDrive como ativo.
        $this->post(route('admin.settings.update'), [
            'current_group' => 'storage',
            'storage_driver' => 's3',
            'storage_active_provider' => 'idrive',
            'idrive_access_key' => 'IDR_AK',
            'idrive_secret_key' => 'IDR_SK',
            'idrive_bucket' => 'idrive-b',
            'idrive_region' => 'us-east-1',
            'idrive_path_style' => '1',
        ])->assertRedirect();

        // Round 2: troca para Wasabi.
        $this->post(route('admin.settings.update'), [
            'current_group' => 'storage',
            'storage_driver' => 's3',
            'storage_active_provider' => 'wasabi',
            'wasabi_access_key' => 'WAS_AK',
            'wasabi_secret_key' => 'WAS_SK',
            'wasabi_bucket' => 'wasabi-b',
            'wasabi_region' => 'us-east-1',
            'wasabi_endpoint' => 's3.us-east-1.wasabisys.com',
            'wasabi_path_style' => '1',
        ])->assertRedirect();

        // Round 3: troca para AWS.
        $this->post(route('admin.settings.update'), [
            'current_group' => 'storage',
            'storage_driver' => 's3',
            'storage_active_provider' => 'aws',
            'aws_access_key' => 'AWS_AK',
            'aws_secret_key' => 'AWS_SK',
            'aws_bucket' => 'aws-b',
            'aws_region' => 'sa-east-1',
            'aws_path_style' => '0',
        ])->assertRedirect();

        Setting::flushRuntimeCache();

        // Os 3 conjuntos coexistem.
        $this->assertSame('IDR_AK', Setting::get('idrive_access_key'));
        $this->assertSame('idrive-b', Setting::get('idrive_bucket'));
        $this->assertSame('WAS_AK', Setting::get('wasabi_access_key'));
        $this->assertSame('wasabi-b', Setting::get('wasabi_bucket'));
        $this->assertSame('AWS_AK', Setting::get('aws_access_key'));
        $this->assertSame('aws-b', Setting::get('aws_bucket'));

        // O ativo final e aws.
        $this->assertSame('aws', Setting::get('storage_active_provider'));

        // Validar via Registry tambem.
        $registry = new StorageProviderRegistry();
        $this->assertSame('aws', $registry->activeProvider());
        $this->assertTrue($registry->isConfigured('idrive'));
        $this->assertTrue($registry->isConfigured('wasabi'));
        $this->assertTrue($registry->isConfigured('aws'));
    }

    public function test_setting_local_driver_disables_s3_active_provider_lookup(): void
    {
        $this->withoutMiddleware();

        // Primeiro configura IDrive ativo.
        $this->post(route('admin.settings.update'), [
            'current_group' => 'storage',
            'storage_driver' => 's3',
            'storage_active_provider' => 'idrive',
            'idrive_access_key' => 'IDR_AK',
            'idrive_secret_key' => 'IDR_SK',
            'idrive_bucket' => 'idrive-b',
            'idrive_region' => 'us-east-1',
            'idrive_path_style' => '1',
        ])->assertRedirect();

        // Agora muda driver para public (local).
        $this->post(route('admin.settings.update'), [
            'current_group' => 'storage',
            'storage_driver' => 'public',
            'storage_active_provider' => 'idrive',
        ])->assertRedirect();

        Setting::flushRuntimeCache();

        $this->assertSame('public', Setting::get('storage_driver'));

        // applyRuntimeConfig deve resolver para public.
        UploadStorage::applyRuntimeConfig();
        $this->assertSame('public', config('uploads.effective_disk'));
    }

    /**
     * Cria um stub de User com role superadmin para uso em
     * actingAs(). Nao toca a tabela users (que pode nao existir
     * neste setup minimo).
     */
    private function makeFakeSuperadmin(): \App\Models\User
    {
        $user = new \App\Models\User();
        $user->id = 1;
        $user->name = 'Test Superadmin';
        $user->email = 'super@test.local';
        $user->role = 'superadmin';
        $user->setRawAttributes([
            'id' => 1,
            'name' => 'Test Superadmin',
            'email' => 'super@test.local',
            'role' => 'superadmin',
        ], true);
        return $user;
    }
}
