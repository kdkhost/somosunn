<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;
use App\Models\User;

/**
 * Sistema UNN - Install Controller
 *
 * Autor: George Marcelo (KDKHOST SOLUÇÕES)
 * Telefone: +55 (21) 98132-5441
 * Telegram: https://t.me/MARCELO_BRAD
 *
 * Copyright (c) 2026 Kdkhost Soluções. Todos os direitos reservados.
 *
 * AVISO LEGAL:
 * Este software e seu código-fonte são propriedade intelectual de kdkhost soluções.
 * É proibida a reprodução, distribuição, modificação, engenharia reversa ou uso não autorizado,
 * total ou parcial, sem autorização prévia e por escrito.
 *
 * Contato: contato@kdkhost.com.br
 * Licenciamento: Uso restrito conforme contrato/termos aplicáveis.
 */
class InstallController extends Controller
{
    public function index(Request $request)
    {
        if ($this->isApplicationInstalled()) {
            return redirect('/')->with('error', 'Aplicacao ja instalada.');
        }

        $this->authorizeInstallerAccess($request);
        $this->ensureEnvFile();

        if(env('APP_INSTALLED')){
            return redirect('/')->with('error','Aplicação já instalada.');
        }

        return view('install.index', [
            'requirements' => $this->getRequirements(),
            'detectedUrl' => rtrim(request()->root(), '/'),
            'envSnapshot' => $this->envSnapshot(),
        ]);
    }

    public function run(Request $request)
    {
        abort_if($this->isApplicationInstalled(), 404);
        $this->authorizeInstallerAccess($request);

        $request->validate([
            'name'=>'required',
            'email'=>'required|email',
            'password'=>'required|min:8|confirmed',
            'db_host' => 'required',
            'db_port' => 'required',
            'db_database' => 'required',
            'db_username' => 'required',
            'app_url' => 'required|url',
        ]);

        $this->ensureEnvFile();
        if(env('APP_INSTALLED')) return redirect('/')->with('error','Aplicação já instalada.');

        $this->configureDatabaseFromRequest($request);
        $this->configureAppUrl($request);

        Artisan::call('key:generate');
        Artisan::call('migrate', ['--force'=>true]);

        // Garantir carregamento das seeders mesmo se o autoload estiver desatualizado
        $this->includeSeederIfMissing(base_path('database/seeders/DatabaseSeeder.php'));
        $this->includeSeederIfMissing(base_path('database/seeders/SettingsSeeder.php'));
        $this->includeSeederIfMissing(base_path('database/seeders/UserSeeder.php'));
        $this->includeSeederIfMissing(base_path('database/seeders/PointsRulesSeeder.php'));
        // caminhos antigos (caso o autoload ainda referencie backend/)
        $this->includeSeederIfMissing(base_path('backend/database/seeders/DatabaseSeeder.php'));
        $this->includeSeederIfMissing(base_path('backend/database/seeders/SettingsSeeder.php'));
        $this->includeSeederIfMissing(base_path('backend/database/seeders/UserSeeder.php'));
        $this->includeSeederIfMissing(base_path('backend/database/seeders/PointsRulesSeeder.php'));

        try {
            Artisan::call('db:seed', [
                '--force'=>true,
                '--class'=>'Database\\Seeders\\DatabaseSeeder',
            ]);
        } catch (\Throwable $e) {
            // fallback manual se o Artisan/Container ainda não localizar a classe
            if (class_exists(\Database\Seeders\DatabaseSeeder::class)) {
                app()->make(\Database\Seeders\DatabaseSeeder::class)->run();
            } elseif (class_exists('DatabaseSeeder')) {
                app()->make('DatabaseSeeder')->run();
            } else {
                throw $e;
            }
        }

        $admin = User::firstWhere('email', $request->input('email'));
        $adminData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'is_admin' => true,
            'role' => 'superadmin',
            'level' => 'sucesso',
        ];

        if(!$admin){
            $admin = User::create($adminData);
        } else {
            $admin->update($adminData);
        }

        $this->markInstalled();

        return view('install.success', ['admin' => $admin]);
    }

    private function getRequirements(): array
    {
        $checks = [
            [
                'label' => 'PHP >= 8.1',
                'ok' => version_compare(PHP_VERSION, '8.1', '>='),
                'detail' => 'Atual: '.PHP_VERSION,
            ],
            [
                'label' => 'Extensão OpenSSL',
                'ok' => extension_loaded('openssl'),
                'detail' => 'Instalada: '.(extension_loaded('openssl') ? 'sim' : 'não'),
            ],
            [
                'label' => 'Extensão Fileinfo',
                'ok' => extension_loaded('fileinfo'),
                'detail' => 'Instalada: '.(extension_loaded('fileinfo') ? 'sim' : 'não'),
            ],
            [
                'label' => 'storage/ pronta para escrita',
                'ok' => $this->dirWritable(storage_path()),
                'detail' => 'storage/ acessível',
            ],
            [
                'label' => 'bootstrap/cache pronta para escrita',
                'ok' => $this->dirWritable(base_path('bootstrap/cache')),
                'detail' => 'bootstrap/cache acessível',
            ],
        ];

        return $checks;
    }

    private function dirWritable(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }
        $testFile = $path.'/.__write_test';
        $ok = @file_put_contents($testFile, 'ok') !== false;
        if ($ok) {
            @unlink($testFile);
        }
        return $ok;
    }

    private function markInstalled(): void
    {
        $envPath = base_path('.env');
        if(is_writable($envPath)){
            $content = file_get_contents($envPath);
            if(strpos($content, 'APP_INSTALLED=') === false){
                file_put_contents($envPath, $content . "\nAPP_INSTALLED=true\n");
            } else {
                $content = preg_replace('/APP_INSTALLED=.*/', 'APP_INSTALLED=true', $content);
                file_put_contents($envPath, $content);
            }
        }

        $lockPath = (string) config('maintenance.installed_lock_path', storage_path('app/installed.lock'));
        $lockDir = dirname($lockPath);
        if (!is_dir($lockDir)) {
            @mkdir($lockDir, 0755, true);
        }

        if (is_dir($lockDir) && is_writable($lockDir)) {
            @file_put_contents($lockPath, 'installed_at=' . now()->toIso8601String() . PHP_EOL);
        }
    }

    private function ensureEnvFile(): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            $example = base_path('.env.example');
            if (file_exists($example)) {
                copy($example, $envPath);
            } else {
                file_put_contents($envPath, $this->defaultEnvContent());
            }
        }
    }

    public function testConnection(Request $request): JsonResponse
    {
        abort_if($this->isApplicationInstalled(), 404);
        $this->authorizeInstallerAccess($request);

        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required',
            'db_database' => 'required',
            'db_username' => 'required',
        ]);

        try {
            $this->attemptConnection($request);
            return $this->jsonSuccess('Conexão bem sucedida.');
        } catch (\Throwable $e) {
            return $this->jsonError('Falha ao conectar: '.$e->getMessage(), $e);
        }
    }

    private function jsonSuccess(string $message): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
        ]);
    }

    private function jsonError(string $message, ?\Throwable $exception = null): JsonResponse
    {
        $payload = [
            'status' => false,
            'message' => $message,
        ];

        if ($exception && app()->environment('local')) {
            $payload['debug'] = [
                'exception' => get_class($exception),
                'trace' => collect(explode("\n", $exception->getTraceAsString()))->take(3)->all(),
            ];
        }

        return response()->json($payload, 422);
    }

    private function configureDatabaseFromRequest(Request $request): void
    {
        $this->writeEnvVars([
            'DB_HOST' => $request->input('db_host'),
            'DB_PORT' => $request->input('db_port'),
            'DB_DATABASE' => $request->input('db_database'),
            'DB_USERNAME' => $request->input('db_username'),
            'DB_PASSWORD' => $request->input('db_password', ''),
        ]);

        Config::set('database.connections.mysql.host', $request->input('db_host'));
        Config::set('database.connections.mysql.port', $request->input('db_port'));
        Config::set('database.connections.mysql.database', $request->input('db_database'));
        Config::set('database.connections.mysql.username', $request->input('db_username'));
        Config::set('database.connections.mysql.password', $request->input('db_password', ''));
    }

    private function configureAppUrl(Request $request): void
    {
        $url = rtrim($request->input('app_url'), '/');
        if ($url === '') {
            return;
        }

        $this->writeEnvVars([
            'APP_URL' => $url,
        ]);

        Config::set('app.url', $url);
    }

    private function attemptConnection(Request $request): void
    {
        $connectionName = 'install_test';
        $info = [
            'driver' => 'mysql',
            'host' => $request->input('db_host'),
            'port' => $request->input('db_port'),
            'database' => $request->input('db_database'),
            'username' => $request->input('db_username'),
            'password' => $request->input('db_password', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        Config::set("database.connections.{$connectionName}", $info);
        DB::purge($connectionName);
        DB::connection($connectionName)->getPdo();
        DB::disconnect($connectionName);
    }

    private function writeEnvVars(array $values): void
    {
        $envPath = base_path('.env');
        if (!is_writable($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}=" . str_replace("\n", '', $value);
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= "\n{$replacement}";
            }
        }

        file_put_contents($envPath, $content);
    }

    private function defaultEnvContent(): string
    {
        return <<<'TXT'
APP_NAME=UNN
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
TXT;
    }

    private function envSnapshot(): array
    {
        return [
            'APP_ENV' => env('APP_ENV', 'local'),
            'APP_DEBUG' => config('app.debug') ? 'true' : 'false',
            'APP_URL' => env('APP_URL', request()->root()),
            'DB_HOST' => env('DB_HOST', '127.0.0.1'),
            'DB_DATABASE' => env('DB_DATABASE', ''),
            'DB_USERNAME' => env('DB_USERNAME', ''),
        ];
    }

    private function includeSeederIfMissing(string $path): void
    {
        if (is_file($path)) {
            $class = 'Database\\Seeders\\'.pathinfo($path, PATHINFO_FILENAME);
            if (!class_exists($class, false)) {
                require_once $path;
            }
            // Garantir alias plano (DatabaseSeeder) para o SeedCommand
            $base = pathinfo($path, PATHINFO_FILENAME);
            if ($base === 'DatabaseSeeder' && !class_exists('DatabaseSeeder', false) && class_exists($class, false)) {
                class_alias($class, 'DatabaseSeeder');
            }
        }
    }

    private function authorizeInstallerAccess(Request $request): void
    {
        if (!app()->environment('production')) {
            return;
        }

        if (!(bool) config('maintenance.allow_installer', false)) {
            $this->logBlockedInstallerAttempt($request, 'installer_disabled');
            abort(404);
        }

        $expected = trim((string) config('maintenance.installer_token', ''));
        if ($expected === '') {
            $this->logBlockedInstallerAttempt($request, 'missing_installer_token');
            abort(404);
        }

        $provided = trim((string) (
            $request->header('X-Installer-Token')
            ?: $request->input('installer_token')
            ?: $request->query('token', '')
        ));

        if ($provided === '' || !hash_equals($expected, $provided)) {
            $this->logBlockedInstallerAttempt($request, 'invalid_installer_token');
            abort(404);
        }
    }

    private function isApplicationInstalled(): bool
    {
        if ((bool) env('APP_INSTALLED', false)) {
            return true;
        }

        $lockPath = (string) config('maintenance.installed_lock_path', storage_path('app/installed.lock'));
        if ($lockPath !== '' && is_file($lockPath)) {
            return true;
        }

        if (filled((string) config('app.key'))) {
            return true;
        }

        try {
            foreach (['users', 'settings', 'migrations'] as $table) {
                if (Schema::hasTable($table)) {
                    return true;
                }
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    private function logBlockedInstallerAttempt(Request $request, string $reason): void
    {
        try {
            Log::warning('Tentativa de instalador bloqueada', [
                'reason' => $reason,
                'path' => '/' . ltrim($request->path(), '/'),
                'method' => $request->method(),
                'ip_hash' => hash('sha256', (string) $request->ip()),
            ]);
        } catch (\Throwable) {
            // Nao interrompe a resposta de bloqueio caso o log esteja indisponivel.
        }
    }
}
