<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestS3Connection extends Command
{
    protected $signature = 'storage:test-s3';

    protected $description = 'Testa a conexao com o storage S3 (upload, exists, url, read, delete)';

    public function handle(): int
    {
        $this->info('');
        $this->info('=== Teste de Conexao S3 ===');
        $this->info('');

        $config = config('filesystems.disks.s3');

        $this->line('Endpoint: ' . ($config['endpoint'] ?? '(padrao AWS)'));
        $this->line('Bucket:   ' . ($config['bucket'] ?? '(nao definido)'));
        $this->line('Region:   ' . ($config['region'] ?? '(nao definido)'));
        $this->line('Path Style: ' . ($config['use_path_style_endpoint'] ? 'Sim' : 'Nao'));
        $this->info('');

        if (empty($config['key']) || empty($config['secret']) || empty($config['bucket'])) {
            $this->error('Configuracao incompleta. Verifique access key, secret key e bucket.');
            return self::FAILURE;
        }

        $disk = Storage::disk('s3');
        $testFile = '_somos_unn_cli_test_' . time() . '.txt';
        $testContent = 'SOMOS UNN S3 CLI Test - ' . now()->toDateTimeString();
        $allPassed = true;

        // 1. Upload
        $this->line('[1/5] Upload...');
        try {
            $disk->put($testFile, $testContent);
            $this->info('      OK - Arquivo enviado: ' . $testFile);
        } catch (\Throwable $e) {
            $this->error('      FALHA - ' . $e->getMessage());
            return self::FAILURE;
        }

        // 2. Exists
        $this->line('[2/5] Verificar existencia...');
        try {
            $exists = $disk->exists($testFile);
            if ($exists) {
                $this->info('      OK - Arquivo encontrado no bucket');
            } else {
                $this->warn('      AVISO - Arquivo nao encontrado (pode ser cache)');
                $allPassed = false;
            }
        } catch (\Throwable $e) {
            $this->error('      FALHA - ' . $e->getMessage());
            $allPassed = false;
        }

        // 3. URL
        $this->line('[3/5] Gerar URL...');
        try {
            $url = $disk->url($testFile);
            $this->info('      OK - ' . $url);
        } catch (\Throwable $e) {
            $this->error('      FALHA - ' . $e->getMessage());
            $allPassed = false;
        }

        // 4. Read
        $this->line('[4/5] Leitura do conteudo...');
        try {
            $readContent = $disk->get($testFile);
            if ($readContent === $testContent) {
                $this->info('      OK - Conteudo confere');
            } else {
                $this->warn('      AVISO - Conteudo divergente');
                $allPassed = false;
            }
        } catch (\Throwable $e) {
            $this->error('      FALHA - ' . $e->getMessage());
            $allPassed = false;
        }

        // 5. Delete
        $this->line('[5/5] Exclusao...');
        try {
            $disk->delete($testFile);
            $deleted = !$disk->exists($testFile);
            if ($deleted) {
                $this->info('      OK - Arquivo removido');
            } else {
                $this->warn('      AVISO - Arquivo pode ainda existir (cache/propagacao)');
            }
        } catch (\Throwable $e) {
            $this->error('      FALHA - ' . $e->getMessage());
            $allPassed = false;
        }

        $this->info('');
        if ($allPassed) {
            $this->info('Resultado: TODOS OS TESTES PASSARAM');
            return self::SUCCESS;
        } else {
            $this->warn('Resultado: ALGUNS TESTES FALHARAM (verifique acima)');
            return self::FAILURE;
        }
    }
}
