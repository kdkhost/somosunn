<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Migra arquivos locais para o S3 (IDrive e2).
 * Permite migrar um arquivo individual, uma pasta inteira ou tudo.
 *
 * Uso:
 *   php artisan storage:migrate-to-s3                          # Lista pastas disponiveis
 *   php artisan storage:migrate-to-s3 --path=uploads/videos    # Migra pasta especifica
 *   php artisan storage:migrate-to-s3 --file=event-images/foto.jpg  # Migra arquivo individual
 *   php artisan storage:migrate-to-s3 --all                    # Migra TUDO (cuidado!)
 *   php artisan storage:migrate-to-s3 --dry-run --path=uploads # Simula sem mover
 */
class MigrateFilesToS3 extends Command
{
    protected $signature = 'storage:migrate-to-s3
        {--path= : Pasta especifica para migrar (ex: event-images, uploads/videos)}
        {--file= : Arquivo individual para migrar (ex: event-images/foto.jpg)}
        {--all : Migrar TODOS os arquivos locais para S3}
        {--dry-run : Simular sem mover arquivos}
        {--delete-local : Apagar arquivo local apos confirmar upload no S3}';

    protected $description = 'Migra arquivos do disco local para o S3 (IDrive e2)';

    public function handle(): int
    {
        $localDisk = Storage::disk('public');
        $s3Disk = Storage::disk('s3');

        // Verificar se S3 esta configurado
        $bucket = config('filesystems.disks.s3.bucket');
        if (empty($bucket)) {
            $this->error('S3 nao configurado. Configure em Configuracoes > Armazenamento.');
            return self::FAILURE;
        }

        $dryRun = $this->option('dry-run');
        $deleteLocal = $this->option('delete-local');
        $singleFile = $this->option('file');
        $path = $this->option('path');
        $all = $this->option('all');

        if ($dryRun) {
            $this->warn('MODO SIMULACAO — nenhum arquivo sera movido.');
            $this->newLine();
        }

        // Migrar arquivo individual
        if ($singleFile) {
            return $this->migrateFile($localDisk, $s3Disk, $singleFile, $dryRun, $deleteLocal);
        }

        // Migrar pasta especifica
        if ($path) {
            return $this->migratePath($localDisk, $s3Disk, $path, $dryRun, $deleteLocal);
        }

        // Migrar tudo
        if ($all) {
            if (!$dryRun) {
                if (!$this->confirm('Tem certeza que deseja migrar TODOS os arquivos locais para o S3? Isso pode demorar.')) {
                    $this->info('Operacao cancelada.');
                    return self::SUCCESS;
                }
            }
            return $this->migratePath($localDisk, $s3Disk, '', $dryRun, $deleteLocal);
        }

        // Sem opcao: listar pastas disponiveis
        $this->info('Pastas disponiveis para migracao:');
        $this->newLine();

        $directories = $localDisk->directories('');
        if (empty($directories)) {
            $this->warn('Nenhuma pasta encontrada no disco local.');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($directories as $dir) {
            $files = $localDisk->allFiles($dir);
            $size = 0;
            foreach ($files as $f) {
                $size += $localDisk->size($f);
            }
            $rows[] = [
                $dir,
                count($files),
                $this->formatBytes($size),
            ];
        }

        $this->table(['Pasta', 'Arquivos', 'Tamanho'], $rows);
        $this->newLine();
        $this->info('Para migrar uma pasta: php artisan storage:migrate-to-s3 --path=NOME_DA_PASTA');
        $this->info('Para migrar um arquivo: php artisan storage:migrate-to-s3 --file=CAMINHO/ARQUIVO.ext');
        $this->info('Para simular: adicione --dry-run');
        $this->info('Para apagar local apos migrar: adicione --delete-local');

        return self::SUCCESS;
    }

    private function migrateFile($localDisk, $s3Disk, string $file, bool $dryRun, bool $deleteLocal): int
    {
        if (!$localDisk->exists($file)) {
            $this->error("Arquivo nao encontrado: {$file}");
            return self::FAILURE;
        }

        $size = $localDisk->size($file);
        $this->info("Migrando: {$file} ({$this->formatBytes($size)})");

        if ($dryRun) {
            $this->info('  [DRY-RUN] Seria enviado para S3.');
            return self::SUCCESS;
        }

        try {
            // Upload para S3
            $content = $localDisk->get($file);
            $s3Disk->put($file, $content, 'public');

            // Verificar se existe no S3
            if (!$s3Disk->exists($file)) {
                $this->error("  FALHA — arquivo nao confirmado no S3.");
                return self::FAILURE;
            }

            $url = $s3Disk->url($file);
            $this->info("  OK — URL: {$url}");

            // Apagar local se solicitado
            if ($deleteLocal) {
                $localDisk->delete($file);
                $this->info("  Local apagado.");
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("  ERRO: {$e->getMessage()}");
            Log::error("storage:migrate-to-s3 falhou para {$file}: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function migratePath($localDisk, $s3Disk, string $path, bool $dryRun, bool $deleteLocal): int
    {
        $files = $localDisk->allFiles($path);

        if (empty($files)) {
            $this->warn("Nenhum arquivo encontrado em: " . ($path ?: '(raiz)'));
            return self::SUCCESS;
        }

        $totalFiles = count($files);
        $totalSize = 0;
        foreach ($files as $f) {
            $totalSize += $localDisk->size($f);
        }

        $this->info("Pasta: " . ($path ?: '(tudo)'));
        $this->info("Arquivos: {$totalFiles} | Tamanho total: {$this->formatBytes($totalSize)}");
        $this->newLine();

        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        $migrated = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $bar->advance();

            // Pular arquivos que ja existem no S3
            if ($s3Disk->exists($file)) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $migrated++;
                continue;
            }

            try {
                $content = $localDisk->get($file);
                $s3Disk->put($file, $content, 'public');

                if ($s3Disk->exists($file)) {
                    $migrated++;

                    if ($deleteLocal) {
                        $localDisk->delete($file);
                    }
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::warning("storage:migrate-to-s3 falhou para {$file}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Resultado:");
        $this->info("  Migrados: {$migrated}");
        if ($skipped > 0) $this->info("  Ja existiam no S3: {$skipped}");
        if ($failed > 0) $this->error("  Falharam: {$failed}");
        if ($dryRun) $this->warn("  (SIMULACAO — nenhum arquivo foi movido)");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}
