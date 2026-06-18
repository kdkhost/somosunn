<?php

/**
 * @autor marcelo-brad rj
 * @contato Tel: 21 981325441
 * @contato Email: contato@kdkhost.com.br
 * @contato Telegram: @MARCELO_BRAD
 * @contato Instagram: @marcelobradrj
 * @contato WhatsApp: 21981325441
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

$localOnly = static function (): void {
    abort_unless(app()->environment('local', 'testing'), 404);
};

Route::middleware(['auth', 'admin'])->group(function () use ($localOnly) {
    Route::get('/debug-test', function () use ($localOnly) {
        $localOnly();

        return response('Laravel is running.');
    });

    Route::post('/limpar-cache', function (Request $request) use ($localOnly) {
        $localOnly();
        abort_unless((string) $request->input('confirmacao') === 'LIMPAR_CACHE_LOCAL', 403);

        $log = [];
        foreach (['view:clear', 'route:clear', 'config:clear', 'cache:clear'] as $command) {
            try {
                Artisan::call($command);
                $log[] = 'Artisan executado: ' . $command;
            } catch (Throwable $exception) {
                $log[] = 'Falha no comando ' . $command;
            }
        }

        return response()->json($log);
    });

    Route::post('/run-migrations', function () {
        return response()->json([
            'status' => 'blocked',
            'message' => 'Use php artisan migrate pelo terminal. Migracoes por HTTP foram desativadas.',
        ], 410);
    });

    Route::post('/demo-somos-unicas', function (Request $request) use ($localOnly) {
        $localOnly();
        abort_unless((string) $request->input('confirmacao') === 'CRIAR_DEMO_LOCAL', 403);

        $ownerId = auth()->id() ?: (\App\Models\User::where('role', 'superadmin')->value('id') ?: 1);

        \App\Models\Event::create([
            'user_id' => $ownerId,
            'title' => 'Palestra: Protagonismo Feminino nos Negocios',
            'speaker' => 'Dra. Luiza Helena',
            'description' => '<p>Encontro sobre lideranca, networking e oportunidades de negocio.</p>',
            'start_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(5)->addHours(2)->format('Y-m-d H:i:s'),
            'location' => 'Auditorio UNN - Sao Paulo',
            'price' => 0,
            'capacity' => 150,
            'published' => true,
            'is_somos_unicas' => true,
            'color' => '#ec4899',
            'image' => 'https://placehold.co/800x600/fdf2f8/ec4899?text=Protagonismo+Feminino',
        ]);

        \App\Models\Event::create([
            'user_id' => $ownerId,
            'title' => 'Workshop: Lideranca Feminina na Pratica',
            'speaker' => 'Camila Farani',
            'description' => '<p>Workshop focado em negociacao, networking e desenvolvimento profissional.</p>',
            'start_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(10)->addHours(4)->format('Y-m-d H:i:s'),
            'location' => 'Online',
            'price' => 97.00,
            'capacity' => 500,
            'published' => true,
            'is_somos_unicas' => true,
            'color' => '#db2777',
            'image' => 'https://placehold.co/800x600/fdf2f8/db2777?text=Lideranca+na+Pratica',
        ]);

        \App\Models\Course::create([
            'user_id' => $ownerId,
            'title' => 'Empreendedorismo Feminino de A a Z',
            'short_description' => 'Aprenda como tirar sua ideia do papel e criar um negocio rentavel.',
            'full_description' => '<p>Curso sobre criacao, operacao e vendas para novos negocios.</p>',
            'price' => 297.00,
            'author_name' => 'Equipe Somos UNNicas',
            'status' => 'published',
            'is_somos_unicas' => true,
            'thumbnail' => 'https://placehold.co/800x600/fce7f3/be185d?text=Empreendedorismo+A-Z',
        ]);

        \App\Models\Mentorship::create([
            'title' => 'Mentoria VIP: Decolando sua Carreira',
            'mentor_id' => $ownerId,
            'description' => '<p>Sessoes individuais para desenvolvimento profissional.</p>',
            'price' => 997.00,
            'slots' => 10,
            'type' => 'online',
            'video_platform' => 'Zoom',
            'is_somos_unicas' => true,
            'image' => 'https://placehold.co/800x600/fecdd3/e11d48?text=Mentoria+VIP',
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Conteudo demo criado.',
        ]);
    });
});

Route::prefix('install')->group(function () {
    Route::get('/', [\App\Http\Controllers\InstallController::class, 'index'])->name('install.index');
    Route::post('/run', [\App\Http\Controllers\InstallController::class, 'run'])->name('install.run');
    Route::post('/test-connection', [\App\Http\Controllers\InstallController::class, 'testConnection'])->name('install.test-connection');
});

Route::prefix('backend/install')->group(function () {
    Route::post('/run', [\App\Http\Controllers\InstallController::class, 'run'])->name('install.run.legacy');
    Route::post('/test-connection', [\App\Http\Controllers\InstallController::class, 'testConnection'])->name('install.test-connection.legacy');
});
