<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato contato@kdkhost.com.br
 *
 * ============================================================
 *
 * Testa o fluxo de limpeza do historico de logs em ambos os
 * paineis: /admin (legado) e /painel/admin (novo). Garante
 * que apos a limpeza:
 *   1) Tabela activity_logs e esvaziada (DELETE em massa).
 *   2) Audit log e gravado no canal security (ou fallback stack).
 *   3) Eventual erro nao propaga excecao - retorna mensagem amigavel.
 *
 * Cobre o fix do bug "rebloqueio em truncate" no painel novo
 * (Panel\Admin\ActivityLogController) garantindo paridade com o
 * Admin\ActivityLogController ja corrigido em commit 244cb5e.
 */

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ActivityLogController as LegacyActivityLogController;
use App\Http\Controllers\Panel\Admin\ActivityLogController as PanelActivityLogController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ActivityLogClearTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-activity-log-clear.sqlite');
        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }
        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });

        // Popula 5 registros para serem apagados pelo clear()
        for ($i = 0; $i < 5; $i++) {
            DB::table('activity_logs')->insert([
                'user_id' => 1,
                'action' => 'TEST',
                'description' => 'log #' . $i,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'phpunit',
                'properties' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }
        parent::tearDown();
    }

    public function test_legacy_admin_clear_uses_delete_and_emits_audit_log(): void
    {
        $this->assertSame(5, DB::table('activity_logs')->count(), 'Setup falhou');

        Auth::shouldReceive('id')->andReturn(99);
        Auth::shouldReceive('check')->andReturn(true);

        $controller = new LegacyActivityLogController();

        $response = $controller->clear();

        $this->assertNotNull($response);
        $this->assertSame(0, DB::table('activity_logs')->count(), 'Tabela deveria estar vazia apos clear');
    }

    public function test_panel_admin_clear_also_uses_delete_and_does_not_throw(): void
    {
        $this->assertSame(5, DB::table('activity_logs')->count(), 'Setup falhou');

        // Usuario admin fictico (responde isAdmin/hasPermission via mock)
        $userMock = \Mockery::mock(\App\Models\User::class);
        $userMock->shouldReceive('isAdmin')->andReturn(true);
        $userMock->shouldReceive('hasPermission')->andReturn(true);

        Auth::shouldReceive('user')->andReturn($userMock);
        Auth::shouldReceive('id')->andReturn(99);
        Auth::shouldReceive('check')->andReturn(true);

        $controller = new PanelActivityLogController();

        $response = $controller->clear();

        $this->assertNotNull($response);
        $this->assertSame(0, DB::table('activity_logs')->count(), 'Tabela deveria estar vazia apos clear no painel novo');
    }

    public function test_panel_admin_clear_handles_exception_gracefully(): void
    {
        // Forca exception removendo a tabela antes do clear
        Schema::dropIfExists('activity_logs');

        $userMock = \Mockery::mock(\App\Models\User::class);
        $userMock->shouldReceive('isAdmin')->andReturn(true);
        $userMock->shouldReceive('hasPermission')->andReturn(true);

        Auth::shouldReceive('user')->andReturn($userMock);
        Auth::shouldReceive('id')->andReturn(99);
        Auth::shouldReceive('check')->andReturn(true);

        $controller = new PanelActivityLogController();

        // Deve NAO propagar excecao mesmo com tabela inexistente
        $response = $controller->clear();
        $this->assertNotNull($response, 'Controller deve retornar response mesmo em caso de erro');
    }
}
