<?php

namespace Tests\Feature\MultiGateway;

use App\Models\GatewayAccount;
use App\Models\Setting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Testes de integracao para resolucao de gateways multi-gateway.
 *
 * Valida: Requisitos 2.2, 2.3, 2.4, 2.5, 9.1, 9.2
 */
class GatewayResolutionTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::flushRuntimeCache();
        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase    = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-multi-gateway-resolution.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }
        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('gateway_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('provider');
            $table->text('public_key')->nullable();
            $table->text('access_token')->nullable();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->string('pix_key')->nullable();
            $table->boolean('enabled')->default(true);
            $table->json('extra')->nullable();
            $table->timestamps();
        });

        // Habilitar ambos os gateways globalmente por padrao
        Setting::set('mercadopago_enabled', 1);
        Setting::set('sumup_enabled', 1);
        Setting::set('mercadopago_env', 'sandbox');
        Setting::set('mercadopago_sandbox_public_key', 'TEST-pub-key');
        Setting::set('mercadopago_sandbox_access_token', 'TEST-access-token');
        Setting::set('sumup_api_key', 'sup_sk_test_key');
        Setting::set('sumup_merchant_code', 'MTEST001');
    }

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();

        Schema::dropIfExists('gateway_accounts');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('users');

        DB::purge('sqlite');

        config()->set('database.default', $this->originalDefaultConnection);
        config()->set('database.connections.sqlite.database', $this->originalSqliteDatabase);

        DB::reconnect($this->originalDefaultConnection);

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // resolveAllActiveGatewaysForSeller
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function retorna_dois_gateways_quando_ambos_estao_ativos_globalmente(): void
    {
        $result = GatewayAccount::resolveAllActiveGatewaysForSeller(0);

        $providers = array_column($result, 'provider');
        $this->assertCount(2, $result, 'Deve retornar exatamente 2 gateways ativos');
        $this->assertContains('mercadopago', $providers);
        $this->assertContains('sumup', $providers);
    }

    /** @test */
    public function retorna_apenas_mercadopago_quando_sumup_esta_desabilitado(): void
    {
        Setting::set('sumup_enabled', 0);
        Setting::flushRuntimeCache();

        $result = GatewayAccount::resolveAllActiveGatewaysForSeller(0);

        $providers = array_column($result, 'provider');
        $this->assertCount(1, $result, 'Deve retornar exatamente 1 gateway');
        $this->assertContains('mercadopago', $providers);
        $this->assertNotContains('sumup', $providers);
    }

    /** @test */
    public function retorna_apenas_sumup_quando_mercadopago_esta_desabilitado(): void
    {
        Setting::set('mercadopago_enabled', 0);
        Setting::flushRuntimeCache();

        $result = GatewayAccount::resolveAllActiveGatewaysForSeller(0);

        $providers = array_column($result, 'provider');
        $this->assertCount(1, $result, 'Deve retornar exatamente 1 gateway');
        $this->assertContains('sumup', $providers);
        $this->assertNotContains('mercadopago', $providers);
    }

    /** @test */
    public function retorna_array_vazio_quando_nenhum_gateway_esta_ativo(): void
    {
        Setting::set('mercadopago_enabled', 0);
        Setting::set('sumup_enabled', 0);
        Setting::flushRuntimeCache();

        $result = GatewayAccount::resolveAllActiveGatewaysForSeller(0);

        $this->assertIsArray($result);
        $this->assertEmpty($result, 'Deve retornar array vazio quando nenhum gateway esta ativo');
    }

    /** @test */
    public function retorna_array_vazio_quando_nenhum_gateway_tem_credenciais(): void
    {
        Setting::set('mercadopago_sandbox_public_key', '');
        Setting::set('mercadopago_sandbox_access_token', '');
        Setting::set('sumup_api_key', '');
        Setting::set('sumup_merchant_code', '');
        Setting::flushRuntimeCache();

        $result = GatewayAccount::resolveAllActiveGatewaysForSeller(0);

        $this->assertIsArray($result);
        $this->assertEmpty($result, 'Deve retornar array vazio quando nenhum gateway tem credenciais validas');
    }

    /** @test */
    public function usa_credenciais_do_vendedor_quando_disponivel(): void
    {
        // Criar usuario e gateway_account do vendedor
        $userId = DB::table('users')->insertGetId([
            'name'       => 'Vendedor Teste',
            'email'      => 'vendedor@teste.com',
            'password'   => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('gateway_accounts')->insert([
            'user_id'      => $userId,
            'provider'     => 'mercadopago',
            'public_key'   => 'SELLER-pub-key',
            'access_token' => 'SELLER-access-token',
            'enabled'      => 1,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $result = GatewayAccount::resolveAllActiveGatewaysForSeller($userId);

        $mpGateway = collect($result)->firstWhere('provider', 'mercadopago');
        $this->assertNotNull($mpGateway, 'Deve encontrar o gateway mercadopago');
        $this->assertEquals('seller', $mpGateway['source'], 'Deve usar credenciais do vendedor');
        $this->assertEquals('SELLER-pub-key', $mpGateway['config']['mpPublicKey']);
    }

    /** @test */
    public function usa_credenciais_globais_como_fallback_quando_vendedor_nao_tem_gateway(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name'       => 'Vendedor Sem Gateway',
            'email'      => 'semgateway@teste.com',
            'password'   => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Nao criar gateway_account para este vendedor
        $result = GatewayAccount::resolveAllActiveGatewaysForSeller($userId);

        $mpGateway = collect($result)->firstWhere('provider', 'mercadopago');
        $this->assertNotNull($mpGateway, 'Deve encontrar o gateway mercadopago via fallback global');
        $this->assertEquals('global', $mpGateway['source'], 'Deve usar credenciais globais como fallback');
    }

    /** @test */
    public function todos_os_gateways_retornados_tem_enabled_true(): void
    {
        $result = GatewayAccount::resolveAllActiveGatewaysForSeller(0);

        foreach ($result as $gateway) {
            $this->assertTrue($gateway['enabled'], "Gateway {$gateway['provider']} deve ter enabled = true");
        }
    }

    /** @test */
    public function estrutura_do_array_retornado_esta_correta(): void
    {
        $result = GatewayAccount::resolveAllActiveGatewaysForSeller(0);

        $this->assertNotEmpty($result);
        foreach ($result as $gateway) {
            $this->assertArrayHasKey('provider', $gateway);
            $this->assertArrayHasKey('enabled', $gateway);
            $this->assertArrayHasKey('config', $gateway);
            $this->assertArrayHasKey('source', $gateway);
            $this->assertIsArray($gateway['config']);
            $this->assertIsString($gateway['provider']);
            $this->assertIsBool($gateway['enabled']);
        }
    }

    /** @test */
    public function resolve_active_gateway_for_seller_ainda_funciona_para_compatibilidade(): void
    {
        // Garantir que o metodo legado ainda funciona
        $result = GatewayAccount::resolveActiveGatewayForSeller(0);

        $this->assertArrayHasKey('provider', $result);
        $this->assertArrayHasKey('enabled', $result);
        $this->assertArrayHasKey('config', $result);
        $this->assertArrayHasKey('source', $result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Independencia de estado entre gateways (Propriedade 1)
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function ativar_mercadopago_nao_altera_estado_do_sumup(): void
    {
        Setting::set('sumup_enabled', 0);
        Setting::flushRuntimeCache();

        // Ativar MP
        Setting::set('mercadopago_enabled', 1);
        Setting::flushRuntimeCache();

        // SumUp deve continuar desativado
        $sumupEnabled = (int) Setting::get('sumup_enabled', 0);
        $this->assertEquals(0, $sumupEnabled, 'Ativar MP nao deve alterar o estado do SumUp');
    }

    /** @test */
    public function ativar_sumup_nao_altera_estado_do_mercadopago(): void
    {
        Setting::set('mercadopago_enabled', 0);
        Setting::flushRuntimeCache();

        // Ativar SumUp
        Setting::set('sumup_enabled', 1);
        Setting::flushRuntimeCache();

        // MP deve continuar desativado
        $mpEnabled = (int) Setting::get('mercadopago_enabled', 0);
        $this->assertEquals(0, $mpEnabled, 'Ativar SumUp nao deve alterar o estado do MP');
    }
}
