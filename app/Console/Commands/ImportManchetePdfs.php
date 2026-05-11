<?php

namespace App\Console\Commands;

use App\Models\Magazine;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportManchetePdfs extends Command
{
    protected $signature = 'magazines:import-manchete {--force : Re-importa revistas ja cadastradas}';
    protected $description = 'Importa todas as edicoes da Revista Manchete (patrocinadora) para o modulo de revistas';

    /**
     * Lista completa das edicoes da Revista Manchete (fornecida oficialmente).
     * Cada entrada: [url_revista, titulo, edicao, categoria, published_at]
     */
    protected array $sources = [
        [
            'url'       => 'https://revistamanchete.com.br/revistas/revista-manchete-edicao-7-marco-2026/',
            'title'     => 'Revista Manchete - 7a Edicao',
            'edition'   => '#7 - Marco/2026',
            'category'  => 'Manchetes',
            'published' => '2026-03-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revistas/revista-manchete-especial-turismall/',
            'title'     => 'Revista Manchete Especial Turismall',
            'edition'   => 'Especial',
            'category'  => 'Especial',
            'published' => '2026-02-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revistas/revista-manchete-agro/',
            'title'     => 'Revista Manchete Agro',
            'edition'   => 'Especial',
            'category'  => 'Agro',
            'published' => '2026-01-15',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revistas/revista-manchete-turismo-buzios/',
            'title'     => 'Revista Manchete Turismo - Buzios',
            'edition'   => 'Especial',
            'category'  => 'Turismo',
            'published' => '2026-01-10',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/capa-revista-manchete-edicao-6-janeiro-2026-zica-assis/',
            'title'     => 'Revista Manchete - 6a Edicao',
            'edition'   => '#6 - Janeiro/2026',
            'category'  => 'Manchetes',
            'published' => '2026-01-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revista-manchete-especial-20-empresas-do-rio-2025/',
            'title'     => 'Revista Manchete Especial - 20 Empresas do Rio 2025',
            'edition'   => 'Especial',
            'category'  => 'Especial',
            'published' => '2025-12-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revista-manchete-edicao-5-novembro-2025/',
            'title'     => 'Revista Manchete - 5a Edicao',
            'edition'   => '#5 - Novembro/2025',
            'category'  => 'Manchetes',
            'published' => '2025-11-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revista-manchete-rio-innovation-week/',
            'title'     => 'Revista Manchete - Rio Innovation Week',
            'edition'   => 'Especial',
            'category'  => 'Especial',
            'published' => '2025-10-15',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/manchete-turismo/',
            'title'     => 'Revista Manchete Turismo (Outubro 2025)',
            'edition'   => 'Especial',
            'category'  => 'Turismo',
            'published' => '2025-10-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revista-manchete-edicao-4-setembro-2025/',
            'title'     => 'Revista Manchete - 4a Edicao',
            'edition'   => '#4 - Setembro/2025',
            'category'  => 'Manchetes',
            'published' => '2025-09-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revista-manchete-turismo/',
            'title'     => 'Revista Manchete Turismo',
            'edition'   => 'Especial',
            'category'  => 'Turismo',
            'published' => '2025-08-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revistas/revista-manchete-edicao-3-julho-2025/',
            'title'     => 'Revista Manchete - 3a Edicao',
            'edition'   => '#3 - Julho/2025',
            'category'  => 'Manchetes',
            'published' => '2025-07-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revistas/revista-manchete-edicao-2-maio-2025/',
            'title'     => 'Revista Manchete - 2a Edicao',
            'edition'   => '#2 - Maio/2025',
            'category'  => 'Manchetes',
            'published' => '2025-05-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revistas/revista-manchete-edicao-1-marco-2025/',
            'title'     => 'Revista Manchete - 1a Edicao',
            'edition'   => '#1 - Marco/2025',
            'category'  => 'Manchetes',
            'published' => '2025-03-01',
        ],
    ];

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $admin = User::whereIn('role', ['admin', 'superadmin'])->orderBy('id')->first();
        if (!$admin) {
            $this->error('Nenhum admin/superadmin encontrado no sistema.');
            return self::FAILURE;
        }

        $this->info('Importando ' . count($this->sources) . ' revistas (Revista Manchete)...');
        $this->info('Owner: ' . $admin->name . ' (id=' . $admin->id . ')');
        $this->newLine();

        $imported = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($this->sources as $i => $src) {
            $n = $i + 1;
            $this->info(sprintf('[%d/%d] %s', $n, count($this->sources), $src['title']));

            $slug = Str::slug($src['title']);

            // Ja existe?
            $existing = Magazine::where('slug', $slug)->first();
            if ($existing && !$force) {
                $this->warn('  > Ja cadastrada (use --force para reimportar). Pulando.');
                $skipped++;
                continue;
            }

            // Extrair URLs do PDF e capa
            $this->info('  > Buscando PDF + capa em ' . $src['url']);
            try {
                $assets = $this->extractAssets($src['url']);
            } catch (\Throwable $e) {
                $this->error('  > Erro ao buscar assets: ' . $e->getMessage());
                $failed++;
                continue;
            }

            if (!$assets['pdf_url']) {
                $this->error('  > PDF nao encontrado na pagina.');
                $failed++;
                continue;
            }

            // Baixar PDF
            $this->info('  > Baixando PDF: ' . $assets['pdf_url']);
            $pdfPath = $this->downloadFile($assets['pdf_url'], 'magazines/pdfs', 'magazine-pdf-' . $slug . '.pdf');
            if (!$pdfPath) {
                $this->error('  > Falha ao baixar PDF.');
                $failed++;
                continue;
            }
            $pdfSizeKb = (int) round(filesize(public_path('storage/' . $pdfPath)) / 1024);

            // Baixar thumbnail
            $thumbPath = null;
            if ($assets['thumb_url']) {
                $this->info('  > Baixando capa: ' . $assets['thumb_url']);
                $ext = strtolower(pathinfo(parse_url($assets['thumb_url'], PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
                $thumbPath = $this->downloadFile($assets['thumb_url'], 'magazines/thumbs', 'magazine-thumb-' . $slug . '.' . $ext);
            }

            // Persistir
            $data = [
                'user_id'           => $admin->id,
                'title'             => $src['title'],
                'slug'              => $slug,
                'category'          => $src['category'],
                'edition'           => $src['edition'],
                'published_at'      => $src['published'],
                'short_description' => 'Edicao oficial da Revista Manchete, patrocinadora da plataforma.',
                'thumbnail'         => $thumbPath,
                'pdf_file'          => $pdfPath,
                'file_size_kb'      => $pdfSizeKb,
                'is_featured'       => str_contains(strtolower($src['edition']), 'edicao') ? true : false,
                'allow_download'    => true,
                'enable_sound'      => true,
                'status'            => 'published',
                'visibility'        => 'interest',
            ];

            if ($existing) {
                $existing->update($data);
                $this->info('  > Atualizada (id=' . $existing->id . ')');
            } else {
                $mag = Magazine::create($data);
                $this->info('  > Criada (id=' . $mag->id . ')');
            }

            $imported++;
            $this->newLine();
        }

        $this->info('=== Resumo ===');
        $this->info('Importadas: ' . $imported);
        $this->info('Puladas: ' . $skipped);
        $this->info('Falhas: ' . $failed);

        return self::SUCCESS;
    }

    /**
     * Extrai URL do PDF e da imagem de capa da pagina da revista.
     */
    protected function extractAssets(string $url): array
    {
        $response = Http::timeout(30)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (SomosUNN-ImportBot/1.0)',
        ])->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException('HTTP ' . $response->status());
        }

        $html = $response->body();
        $result = ['pdf_url' => null, 'thumb_url' => null];

        // PDF: procura link .pdf no WordPress
        if (preg_match('~https?://[^"\'\s]+\.pdf~i', $html, $m)) {
            $result['pdf_url'] = html_entity_decode($m[0]);
        }

        // Thumbnail: og:image, featured image, ou primeira imagem jpg/png do dominio
        if (preg_match('~<meta[^>]+property="og:image"[^>]+content="([^"]+)"~i', $html, $m)) {
            $result['thumb_url'] = html_entity_decode($m[1]);
        } elseif (preg_match('~<img[^>]+src="(https?://revistamanchete\.com\.br/[^"]+\.(?:jpg|jpeg|png|webp))"~i', $html, $m)) {
            $result['thumb_url'] = html_entity_decode($m[1]);
        }

        return $result;
    }

    /**
     * Baixa um arquivo e salva em public/storage/{directory}/{filename}.
     * Retorna o caminho relativo (directory/filename) ou null em caso de falha.
     */
    protected function downloadFile(string $url, string $directory, string $filename): ?string
    {
        try {
            $response = Http::timeout(120)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (SomosUNN-ImportBot/1.0)',
            ])->get($url);

            if (!$response->successful()) {
                return null;
            }

            $targetDir = public_path('storage/' . trim($directory, '/'));
            if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true)) {
                return null;
            }

            // Sanitiza filename
            $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
            $fullPath = $targetDir . '/' . $filename;

            file_put_contents($fullPath, $response->body());

            return trim($directory, '/') . '/' . $filename;
        } catch (\Throwable $e) {
            $this->error('      Erro download: ' . $e->getMessage());
            return null;
        }
    }
}
