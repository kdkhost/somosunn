<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    private const VALID_TYPES = ['db', 'config'];
    private const VALID_DISKS = ['s3', 'local'];

    public function __construct(private readonly BackupService $backupService)
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $this->authorizeSuperadmin();

        $dbBackups = $this->decorateBackups($this->backupService->listBackups('db'), 'db');
        $configBackups = $this->decorateBackups($this->backupService->listBackups('config'), 'config');

        return view('admin.backups.index', [
            'dbBackups' => $dbBackups,
            'configBackups' => $configBackups,
            'stats' => $this->makeStats($dbBackups, $configBackups),
            'backupSettings' => [
                'backup_keep_daily' => (int) Setting::get('backup_keep_daily', 30),
                'backup_keep_weekly' => (int) Setting::get('backup_keep_weekly', 12),
                'backup_notify_success' => $this->settingBool('backup_notify_success', true),
            ],
            's3Status' => $this->makeS3Status(),
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin();

        $data = $request->validate([
            'type' => 'required|in:database,config',
        ]);

        $result = $data['type'] === 'database'
            ? $this->backupService->backupDatabase()
            : $this->backupService->backupConfig();

        if (!$result->success) {
            return back()->with('error', 'Backup falhou: ' . $result->error);
        }

        return back()->with(
            'success',
            'Backup gerado com sucesso em ' . $result->path . ' (' . $this->formatBytes($result->sizeBytes) . ').'
        );
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin();

        $data = $request->validate([
            'backup_keep_daily' => 'required|integer|min:1|max:365',
            'backup_keep_weekly' => 'required|integer|min:1|max:104',
            'backup_notify_success' => 'nullable|boolean',
        ]);

        Setting::set('backup_keep_daily', (string) $data['backup_keep_daily'], 'backup');
        Setting::set('backup_keep_weekly', (string) $data['backup_keep_weekly'], 'backup');
        Setting::set('backup_notify_success', $request->boolean('backup_notify_success') ? '1' : '0', 'backup');

        return back()->with('success', 'Configuracoes de backup atualizadas.');
    }

    public function download(Request $request): StreamedResponse
    {
        $this->authorizeSuperadmin();

        [$type, $diskName, $path] = $this->validatedBackupTarget($request);
        $disk = Storage::disk($diskName);

        abort_unless($disk->exists($path), 404, 'Backup nao encontrado.');

        $stream = $disk->readStream($path);
        abort_if($stream === false, 404, 'Backup nao pode ser lido.');

        $filename = basename($path);

        return response()->streamDownload(function () use ($stream): void {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $filename, [
            'Content-Type' => $this->contentTypeFor($type),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin();

        [, $diskName, $path] = $this->validatedBackupTarget($request);
        $disk = Storage::disk($diskName);

        if (!$disk->exists($path)) {
            return back()->with('error', 'Backup nao encontrado.');
        }

        if (!$disk->delete($path)) {
            return back()->with('error', 'Nao foi possivel remover o backup.');
        }

        Log::warning('backup.admin.deleted', [
            'user_id' => auth()->id(),
            'disk' => $diskName,
            'path' => $path,
        ]);

        return back()->with('success', 'Backup removido com sucesso.');
    }

    private function authorizeSuperadmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    /**
     * @param array<int,array<string,mixed>> $items
     * @return array<int,array<string,mixed>>
     */
    private function decorateBackups(array $items, string $type): array
    {
        return array_map(function (array $item) use ($type): array {
            $modified = (int) ($item['modified'] ?? 0);

            return array_merge($item, [
                'type' => $type,
                'type_label' => $type === 'db' ? 'Banco de dados' : 'Configuracoes',
                'disk' => (string) ($item['disk'] ?? 's3'),
                'size_label' => $this->formatBytes((int) ($item['size'] ?? 0)),
                'modified_label' => $modified > 0
                    ? Carbon::createFromTimestamp($modified)->format('d/m/Y H:i:s')
                    : 'Nao informado',
            ]);
        }, $items);
    }

    /**
     * @param array<int,array<string,mixed>> $dbBackups
     * @param array<int,array<string,mixed>> $configBackups
     * @return array<string,mixed>
     */
    private function makeStats(array $dbBackups, array $configBackups): array
    {
        $all = array_merge($dbBackups, $configBackups);
        $totalSize = array_sum(array_map(static fn (array $item): int => (int) ($item['size'] ?? 0), $all));

        return [
            'total' => count($all),
            'db_total' => count($dbBackups),
            'config_total' => count($configBackups),
            'total_size' => $this->formatBytes($totalSize),
        ];
    }

    /**
     * @return array{configured:bool,missing:array<int,string>}
     */
    private function makeS3Status(): array
    {
        $s3 = (array) config('filesystems.disks.s3', []);
        $required = [
            'key' => 'AWS_ACCESS_KEY_ID',
            'secret' => 'AWS_SECRET_ACCESS_KEY',
            'region' => 'AWS_DEFAULT_REGION',
            'bucket' => 'AWS_BUCKET',
        ];

        $missing = [];
        foreach ($required as $configKey => $envName) {
            if (trim((string) ($s3[$configKey] ?? '')) === '') {
                $missing[] = $envName;
            }
        }

        return [
            'configured' => $missing === [],
            'missing' => $missing,
        ];
    }

    /**
     * @return array{0:string,1:string,2:string}
     */
    private function validatedBackupTarget(Request $request): array
    {
        $data = $request->validate([
            'type' => 'required|in:db,config',
            'disk' => 'required|in:s3,local',
            'path' => 'required|string|max:255',
        ]);

        $type = (string) $data['type'];
        $diskName = (string) $data['disk'];
        $path = str_replace('\\', '/', (string) $data['path']);
        $prefix = $type === 'db' ? BackupService::BACKUP_DIR_DB : BackupService::BACKUP_DIR_CONFIG;

        abort_if(!in_array($type, self::VALID_TYPES, true), 422);
        abort_if(!in_array($diskName, self::VALID_DISKS, true), 422);
        abort_if(str_contains($path, '..') || !str_starts_with($path, $prefix . '/'), 422);

        return [$type, $diskName, $path];
    }

    private function settingBool(string $key, bool $default): bool
    {
        $value = Setting::get($key, $default ? '1' : '0');

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'sim', 'yes', 'on'], true);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $size = $bytes / 1024;

        foreach ($units as $unit) {
            if ($size < 1024 || $unit === 'TB') {
                return number_format($size, 2, ',', '.') . ' ' . $unit;
            }
            $size /= 1024;
        }

        return $bytes . ' B';
    }

    private function contentTypeFor(string $type): string
    {
        return $type === 'db' ? 'application/gzip' : 'application/gzip';
    }
}
