<?php

namespace App\Console\Commands;

use App\Models\Magazine;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportManchetePdfs extends Command
{
    protected $signature = 'magazines:import-manchete {--force : Reimporta revistas já cadastradas}';
    protected $description = 'Importa todas as edições da Revista Manchete (patrocinadora) para o módulo de revistas';

    /**
     * Lista completa das edições da Revista Manchete (fornecida oficialmente).
     * Cada entrada: [url_revista, título, edição, categoria, published_at]
     */
    protected array $sources = [
        [
            'url'       => 'https://revistamanchete.com.br/wp-content/themes/odin/assets/pdf/Revista-Manchete-Judiciario.pdf',
            'pdf_url'   => 'https://revistamanchete.com.br/wp-content/themes/odin/assets/pdf/Revista-Manchete-Judiciario.pdf',
            'title'     => 'Revista Manchete Judiciário',
            'edition'   => 'Especial Judiciário',
            'category'  => 'Judiciário',
            'published' => '2026-06-19',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revistas/revista-manchete-edicao-7-marco-2026/',
            'title'     => 'Revista Manchete - 7ª Edição',
            'edition'   => '#7 - Março/2026',
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
            'title'     => 'Revista Manchete Turismo - Búzios',
            'edition'   => 'Especial',
            'category'  => 'Turismo',
            'published' => '2026-01-10',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/capa-revista-manchete-edicao-6-janeiro-2026-zica-assis/',
            'title'     => 'Revista Manchete - 6ª Edição',
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
            'title'     => 'Revista Manchete - 5ª Edição',
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
            'title'     => 'Revista Manchete - 4ª Edição',
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
            'title'     => 'Revista Manchete - 3ª Edição',
            'edition'   => '#3 - Julho/2025',
            'category'  => 'Manchetes',
            'published' => '2025-07-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revistas/revista-manchete-edicao-2-maio-2025/',
            'title'     => 'Revista Manchete - 2ª Edição',
            'edition'   => '#2 - Maio/2025',
            'category'  => 'Manchetes',
            'published' => '2025-05-01',
        ],
        [
            'url'       => 'https://revistamanchete.com.br/revistas/revista-manchete-edicao-1-marco-2025/',
            'title'     => 'Revista Manchete - 1ª Edição',
            'edition'   => '#1 - Março/2025',
            'category'  => 'Manchetes',
            'published' => '2025-03-01',
        ],
    ];

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $sources = $this->catalogSources();
        $admin = User::whereIn('role', ['admin', 'superadmin'])->orderBy('id')->first();
        if (!$admin) {
            $this->error('Nenhum administrador ou superadministrador encontrado no sistema.');
            return self::FAILURE;
        }

        $this->info('Sincronizando ' . count($sources) . ' revistas da Revista Manchete...');
        $this->info('Responsável: ' . $admin->name . ' (id=' . $admin->id . ')');
        $this->newLine();

        $imported = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($sources as $i => $src) {
            $n = $i + 1;
            $this->info(sprintf('[%d/%d] %s', $n, count($sources), $src['title']));

            $slug = Str::slug($src['title']);

            // Já existe?
            $existing = Magazine::where('slug', $slug)->first();
            if ($existing && !$force && $this->storedPdfExists($existing)) {
                $existing->update([
                    'title' => $src['title'],
                    'category' => $src['category'],
                    'edition' => $src['edition'],
                    'published_at' => $src['published'],
                    'short_description' => 'Edição oficial da Revista Manchete, patrocinadora da plataforma.',
                    'status' => 'published',
                    'visibility' => 'public',
                ]);
                $this->warn('  > Já cadastrada; metadados em português sincronizados.');
                $skipped++;
                continue;
            }

            // Extrair URLs do PDF e capa
            $this->info('  > Buscando PDF + capa em ' . $src['url']);
            try {
                $assets = !empty($src['pdf_url'])
                    ? [
                        'pdf_url' => $src['pdf_url'],
                        'thumb_url' => $src['thumb_url'] ?? null,
                    ]
                    : $this->extractAssets($src['url']);
            } catch (\Throwable $e) {
                $this->error('  > Erro ao buscar assets: ' . $e->getMessage());
                $failed++;
                continue;
            }

            if (!$assets['pdf_url']) {
                $this->error('  > PDF não encontrado na página.');
                $failed++;
                continue;
            }

            // Baixar PDF
            $this->info('  > Baixando PDF: ' . $assets['pdf_url']);
            $pdfPath = $this->downloadFile($assets['pdf_url'], 'magazines/pdfs', 'magazine-pdf-' . $slug . '.pdf');
            if (!$pdfPath) {
                $this->error('  > Falha ao baixar o PDF.');
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
            if (!$thumbPath) {
                $this->info('  > Gerando capa pela primeira página do PDF.');
                $thumbPath = $this->generateThumbnailFromPdf($pdfPath, $slug);
            }

            // Persistir
            $data = [
                'user_id'           => $admin->id,
                'title'             => $src['title'],
                'slug'              => $slug,
                'category'          => $src['category'],
                'edition'           => $src['edition'],
                'published_at'      => $src['published'],
                'short_description' => 'Edição oficial da Revista Manchete, patrocinadora da plataforma.',
                'thumbnail'         => $thumbPath,
                'pdf_file'          => $pdfPath,
                'file_size_kb'      => $pdfSizeKb,
                'is_featured'       => str_contains(Str::ascii(mb_strtolower($src['edition'])), 'edicao'),
                'allow_download'    => true,
                'enable_sound'      => true,
                'status'            => 'published',
                'visibility'        => 'public',
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

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Combina a lista histórica com as edições encontradas no catálogo oficial.
     * A lista local mantém os metadados editoriais conhecidos e o catálogo acrescenta novidades.
     */
    protected function catalogSources(): array
    {
        $sources = collect($this->sources)->keyBy(fn (array $source) => rtrim($source['url'], '/'));

        try {
            $response = Http::timeout(30)->retry(2, 500)->withHeaders($this->requestHeaders())
                ->get('https://revistamanchete.com.br/revistas/');

            if (!$response->successful()) {
                throw new \RuntimeException('HTTP ' . $response->status());
            }

            foreach ($this->parseCatalog($response->body()) as $source) {
                $key = rtrim($source['url'], '/');
                $sources->put($key, array_merge($source, $sources->get($key, [])));
            }
        } catch (\Throwable $e) {
            $this->warn('Não foi possível consultar o catálogo; usando a lista local: ' . $e->getMessage());
        }

        return $sources->values()
            ->unique(fn (array $source) => Str::slug($source['title']))
            ->values()
            ->all();
    }

    protected function parseCatalog(string $html): array
    {
        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return [];
        }

        $xpath = new \DOMXPath($dom);
        $items = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' revista-item ')]");
        $sources = [];

        foreach ($items ?: [] as $item) {
            $link = $xpath->query('.//a[@href]', $item)?->item(0);
            $labelNode = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' revista-edicao ')]", $item)?->item(0);
            $image = $xpath->query('.//img[@src or @data-src]', $item)?->item(0);
            $url = trim((string) $link?->getAttribute('href'));
            $label = trim(preg_replace('/\s+/u', ' ', (string) $labelNode?->textContent));

            if (!$url || !$this->isOfficialUrl($url)) {
                continue;
            }

            $metadata = $this->metadataFromCatalog($url, $label);
            $thumb = $image ? trim($image->getAttribute('data-src') ?: $image->getAttribute('src')) : null;
            if ($thumb && $this->isOfficialUrl($thumb)) {
                $metadata['thumb_url'] = $thumb;
            }

            $sources[] = $metadata;
        }

        return $sources;
    }

    protected function metadataFromCatalog(string $url, string $label): array
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $name = $label ?: Str::headline(basename($path));
        $ascii = mb_strtolower(Str::ascii($name . ' ' . $path));
        preg_match('/(?:edicao[- ]|^)(\d{1,2})/i', $ascii, $editionMatch);
        preg_match('/(janeiro|fevereiro|marco|abril|maio|junho|julho|agosto|setembro|outubro|novembro|dezembro)(?:[- ](20\d{2}))?/i', $ascii, $dateMatch);

        $months = ['janeiro' => 1, 'fevereiro' => 2, 'marco' => 3, 'abril' => 4, 'maio' => 5, 'junho' => 6,
            'julho' => 7, 'agosto' => 8, 'setembro' => 9, 'outubro' => 10, 'novembro' => 11, 'dezembro' => 12];
        $knownPublished = [
            'revistas/revista-manchete-judiciario-julho' => '2026-07-01',
            'revistas/edicao-especial-energia' => '2026-08-01',
        ];
        $published = $knownPublished[$path] ?? (!empty($dateMatch)
            ? sprintf('%d-%02d-01', (int) ($dateMatch[2] ?? now()->year), $months[$dateMatch[1]])
            : now()->startOfMonth()->toDateString());

        $category = str_contains($ascii, 'judiciario') ? 'Judiciário'
            : (str_contains($ascii, 'energia') ? 'Energia' : (str_contains($ascii, 'turismo') ? 'Turismo' : 'Manchetes'));
        if (!empty($editionMatch)) {
            $title = sprintf('Revista Manchete - %dª Edição', (int) $editionMatch[1]);
        } elseif (str_contains($ascii, 'judiciario-julho')) {
            $title = 'Revista Manchete Judiciário - Julho ' . substr($published, 0, 4);
        } elseif (str_starts_with(Str::ascii($name), 'Manchete')) {
            $title = 'Revista ' . $name;
        } else {
            $title = str_starts_with(Str::ascii($name), 'Revista Manchete') ? $name : 'Revista Manchete - ' . $name;
        }

        return [
            'url' => $url,
            'title' => $title,
            'edition' => !empty($editionMatch) ? '#' . (int) $editionMatch[1] : $name,
            'category' => $category,
            'published' => $published,
        ];
    }

    protected function storedPdfExists(Magazine $magazine): bool
    {
        $path = ltrim((string) $magazine->pdf_file, '/');

        return $path !== '' && (is_file(public_path('storage/' . $path)) || is_file(storage_path('app/public/' . $path)));
    }

    /**
     * Extrai a URL do PDF e da imagem de capa da página da revista.
     */
    protected function extractAssets(string $url): array
    {
        if (!$this->isOfficialUrl($url)) {
            throw new \RuntimeException('Origem externa não permitida.');
        }

        $response = Http::timeout(30)->retry(2, 500)->withHeaders($this->requestHeaders())->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException('HTTP ' . $response->status());
        }

        $html = $response->body();
        $result = ['pdf_url' => null, 'thumb_url' => null];

        // PDF: procura link .pdf no WordPress
        if (preg_match('~https?://[^"\'\s]+\.pdf(?:\?[^"\'\s]*)?~i', $html, $m)) {
            $result['pdf_url'] = html_entity_decode($m[0]);
        }

        // Capa: og:image, imagem destacada ou primeira imagem JPG/PNG do domínio.
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
        if (!$this->isOfficialUrl($url)) {
            $this->error('      Origem externa não permitida.');
            return null;
        }

        $temporary = null;
        try {
            $targetDir = public_path('storage/' . trim($directory, '/'));
            if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true)) {
                return null;
            }

            // Sanitiza o nome do arquivo.
            $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
            $fullPath = $targetDir . '/' . $filename;
            $temporary = $fullPath . '.part-' . bin2hex(random_bytes(6));

            $response = Http::timeout(900)->connectTimeout(30)->retry(2, 1000)
                ->withHeaders($this->requestHeaders())
                ->withOptions(['sink' => $temporary])
                ->get($url);

            if (!$response->successful() || !is_file($temporary)) {
                return null;
            }

            $size = filesize($temporary);
            $isPdf = str_ends_with(strtolower($filename), '.pdf');
            $maximum = $isPdf ? 600 * 1024 * 1024 : 20 * 1024 * 1024;
            if ($size === false || $size < 1 || $size > $maximum) {
                throw new \RuntimeException('Arquivo vazio ou acima do limite permitido.');
            }

            if ($isPdf) {
                $handle = fopen($temporary, 'rb');
                $signature = $handle ? fread($handle, 5) : '';
                if ($handle) {
                    fclose($handle);
                }
                if ($signature !== '%PDF-') {
                    throw new \RuntimeException('A resposta não contém um PDF válido.');
                }
            }

            if (!@rename($temporary, $fullPath)) {
                throw new \RuntimeException('Não foi possível concluir a gravação do arquivo.');
            }
            $temporary = null;

            return trim($directory, '/') . '/' . $filename;
        } catch (\Throwable $e) {
            $this->error('      Erro ao baixar: ' . $e->getMessage());
            return null;
        } finally {
            if ($temporary && is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }

    protected function requestHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (compatible; SomosUNN-MagazineSync/2.0; +https://somosunn.com.br)',
            'Accept' => 'text/html,application/pdf,image/avif,image/webp,image/*,*/*;q=0.8',
        ];
    }

    protected function isOfficialUrl(string $url): bool
    {
        $parts = parse_url(html_entity_decode($url));
        $host = strtolower((string) ($parts['host'] ?? ''));

        return ($parts['scheme'] ?? null) === 'https'
            && in_array($host, ['revistamanchete.com.br', 'www.revistamanchete.com.br'], true);
    }

    protected function generateThumbnailFromPdf(string $pdfPath, string $slug): ?string
    {
        if (!extension_loaded('imagick')) {
            $this->warn('      Extensão Imagick indisponível; capa não gerada.');
            return null;
        }

        $targetDir = public_path('storage/magazines/thumbs');
        if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true)) {
            return null;
        }

        $targetPath = $targetDir . '/magazine-thumb-' . $slug . '.jpg';

        try {
            $image = new \Imagick();
            $image->setResolution(144, 144);
            $image->readImage(public_path('storage/' . $pdfPath) . '[0]');
            $image->setIteratorIndex(0);
            $image->setImageBackgroundColor('white');
            $image = $image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            $image->setImageFormat('jpeg');
            $image->setImageCompressionQuality(88);
            $image->thumbnailImage(1200, 0);
            $image->stripImage();
            $image->writeImage($targetPath);
            $image->clear();
            $image->destroy();

            return 'magazines/thumbs/' . basename($targetPath);
        } catch (\Throwable $e) {
            $this->warn('      Não foi possível gerar a capa: ' . $e->getMessage());
            return null;
        }
    }
}
