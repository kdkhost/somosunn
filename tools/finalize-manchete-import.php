<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Magazine;
use App\Models\User;

$admin = User::whereIn('role', ['admin', 'superadmin'])->orderBy('id')->first();

// 1) Registrar a 2a Edicao que faltou (PDF ja baixado via curl)
$slug = 'revista-manchete-2a-edicao';
$pdfRel = 'magazines/pdfs/magazine-pdf-revista-manchete-2a-edicao.pdf';
$pdfAbs = public_path('storage/' . $pdfRel);

if (file_exists($pdfAbs)) {
    $existing = Magazine::where('slug', $slug)->first();
    if (!$existing) {
        // Baixa a capa (10MB eh OK)
        $capaUrl = 'https://revistamanchete.com.br/wp-content/uploads/2025/05/Capa-Manchete-Maio-2025.jpg';
        $capaRel = 'magazines/thumbs/magazine-thumb-revista-manchete-2a-edicao.jpg';
        $capaAbs = public_path('storage/' . $capaRel);
        @mkdir(dirname($capaAbs), 0755, true);
        $capaContent = @file_get_contents($capaUrl);
        if ($capaContent) file_put_contents($capaAbs, $capaContent);

        Magazine::create([
            'user_id'           => $admin->id,
            'title'             => 'Revista Manchete - 2a Edicao',
            'slug'              => $slug,
            'category'          => 'Manchetes',
            'edition'           => '#2 - Maio/2025',
            'published_at'      => '2025-05-01',
            'short_description' => 'Edicao oficial da Revista Manchete, patrocinadora da plataforma.',
            'thumbnail'         => file_exists($capaAbs) ? $capaRel : null,
            'pdf_file'          => $pdfRel,
            'file_size_kb'      => (int) round(filesize($pdfAbs) / 1024),
            'is_featured'       => true,
            'allow_download'    => true,
            'enable_sound'      => true,
            'status'            => 'published',
            'visibility'        => 'public',
        ]);
        echo "Criada: Revista Manchete - 2a Edicao\n";
    } else {
        echo "2a Edicao ja existe (id=" . $existing->id . ")\n";
    }
} else {
    echo "PDF da 2a Edicao nao encontrado em $pdfAbs\n";
}

// 2) Mudar visibilidade de TODAS as revistas para 'public'
$updated = Magazine::query()->update(['visibility' => 'public']);
echo "Visibilidade atualizada para 'public' em $updated revistas.\n";

// Resumo final
echo "\n=== Total ===\n";
echo "Total: " . Magazine::count() . PHP_EOL;
foreach (Magazine::orderByDesc('published_at')->get() as $m) {
    echo sprintf("  #%d - %s (visibility=%s)\n", $m->id, $m->title, $m->visibility);
}
