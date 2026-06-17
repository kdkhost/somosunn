<?php

/**
 * @autor marcelo-brad rj
 * @contato Tel: 21 981325441
 * @contato Email: contato@kdkhost.com.br
 * @contato Telegram: @MARCELO_BRAD
 * @contato Instagram: @marcelobradrj
 * @contato WhatsApp: 21981325441
 */

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin', 'sensitive.production'])->group(function () {
    Route::get('/debug-test', function () {
        return response('Laravel is Running. PHP Version: ' . phpversion());
    });

    Route::get('/limpar-cache', function () {
        $log = [];

        foreach (glob(storage_path('framework/views') . '/*.php') ?: [] as $file) {
            @unlink($file);
            $log[] = 'View removida: ' . basename($file);
        }

        $bootstrapCache = base_path('bootstrap/cache');
        foreach (['routes-v7.php', 'routes.php', 'config.php', 'data.php'] as $cacheFile) {
            $path = $bootstrapCache . DIRECTORY_SEPARATOR . $cacheFile;
            if (file_exists($path)) {
                @unlink($path);
                $log[] = 'Cache removido: ' . $cacheFile;
            }
        }

        foreach (['view:clear', 'route:clear', 'cache:clear'] as $command) {
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
        Artisan::call('migrate', ['--force' => true]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Migrações executadas.',
        ]);
    });

    Route::post('/demo-somos-unicas', function () {
        $ownerId = auth()->id() ?: (\App\Models\User::where('role', 'superadmin')->value('id') ?: 1);

        \App\Models\Event::create([
            'user_id' => $ownerId,
            'title' => 'Palestra: Protagonismo Feminino nos Negócios',
            'speaker' => 'Dra. Luiza Helena',
            'description' => '<p>Encontro sobre liderança, networking e oportunidades de negócio.</p>',
            'start_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(5)->addHours(2)->format('Y-m-d H:i:s'),
            'location' => 'Auditório UNN - São Paulo',
            'price' => 0,
            'capacity' => 150,
            'published' => true,
            'is_somos_unicas' => true,
            'color' => '#ec4899',
            'image' => 'https://placehold.co/800x600/fdf2f8/ec4899?text=Protagonismo+Feminino',
        ]);

        \App\Models\Event::create([
            'user_id' => $ownerId,
            'title' => 'Workshop: Liderança Feminina na Prática',
            'speaker' => 'Camila Farani',
            'description' => '<p>Workshop focado em negociação, networking e desenvolvimento profissional.</p>',
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
            'short_description' => 'Aprenda como tirar sua ideia do papel e criar um negócio rentável.',
            'full_description' => '<p>Curso sobre criação, operação e vendas para novos negócios.</p>',
            'price' => 297.00,
            'author_name' => 'Equipe Somos UNNicas',
            'status' => 'published',
            'is_somos_unicas' => true,
            'thumbnail' => 'https://placehold.co/800x600/fce7f3/be185d?text=Empreendedorismo+A-Z',
        ]);

        \App\Models\Mentorship::create([
            'title' => 'Mentoria VIP: Decolando sua Carreira',
            'mentor_id' => $ownerId,
            'description' => '<p>Sessões individuais para desenvolvimento profissional.</p>',
            'price' => 997.00,
            'slots' => 10,
            'type' => 'online',
            'video_platform' => 'Zoom',
            'is_somos_unicas' => true,
            'image' => 'https://placehold.co/800x600/fecdd3/e11d48?text=Mentoria+VIP',
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Conteúdo demo criado.',
        ]);
    });
});

Route::middleware(['sensitive.production'])->prefix('install')->group(function () {
    Route::get('/', [\App\Http\Controllers\InstallController::class, 'index'])->name('install.index');
    Route::post('/run', [\App\Http\Controllers\InstallController::class, 'run'])->name('install.run');
    Route::post('/test-connection', [\App\Http\Controllers\InstallController::class, 'testConnection'])->name('install.test-connection');
});

Route::middleware(['sensitive.production'])->prefix('backend/install')->group(function () {
    Route::post('/run', [\App\Http\Controllers\InstallController::class, 'run'])->name('install.run.legacy');
    Route::post('/test-connection', [\App\Http\Controllers\InstallController::class, 'testConnection'])->name('install.test-connection.legacy');
});
