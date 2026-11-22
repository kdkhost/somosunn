<?php

use App\Http\Controllers\AffiliateEmbedController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstitucionalController;
use App\Http\Controllers\JobPublicController;
use App\Http\Controllers\MagazineController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MentorshipController;
use App\Http\Controllers\OportunidadesTesteController;
use App\Http\Controllers\PublicPartnerController;
use App\Http\Controllers\PublicStorageProxyController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\RankingPublicController;
use App\Http\Controllers\SellerStorefrontController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\SomosUnicasAboutController;
use App\Http\Controllers\SomosUnicasController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Publicas Cacheaveis (somosunn.com.br)
|--------------------------------------------------------------------------
| Paginas institucionais e catalogos GET/HEAD ultra-leves servidos sem
| sessao em disco e sem cookie para visitantes anonimos (Edge/LSCache).
*/

Route::get('/health', [HealthController::class, 'index'])->name('health');

Route::match(['GET', 'HEAD', 'OPTIONS'], '/storage/{path}', [PublicStorageProxyController::class, 'storage'])
    ->where('path', '.*')
    ->name('public.storage');
Route::match(['GET', 'HEAD', 'OPTIONS'], '/uploads/{path}', [PublicStorageProxyController::class, 'uploads'])
    ->where('path', '.*')
    ->name('public.uploads');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/portal', [HomeController::class, 'portal'])->name('portal');
Route::get('/planos', [HomeController::class, 'premium'])->name('planos');
Route::get('/galeria', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/galeria/{event}', [GalleryController::class, 'show'])->name('gallery.show');

// Institucionais
Route::get('/sobre', [InstitucionalController::class, 'sobre'])->name('sobre');
Route::get('/manifesto', [InstitucionalController::class, 'manifesto'])->name('manifesto');
Route::get('/quem-somos', [InstitucionalController::class, 'quemSomos'])->name('quem-somos');
Route::get('/como-funciona', [InstitucionalController::class, 'comoFunciona'])->name('como-funciona');
Route::get('/valores', [InstitucionalController::class, 'valores'])->name('valores');
Route::get('/termos', [InstitucionalController::class, 'termos'])->name('site.termos');
Route::get('/privacidade', [InstitucionalController::class, 'privacidade'])->name('site.privacidade');
Route::get('/consentimento-lgpd', [InstitucionalController::class, 'lgpd'])->name('site.lgpd');
Route::get('/contato', [InstitucionalController::class, 'contato'])->name('contato');

// Embeds publicos de afiliados
Route::get('/embed/afiliado/{referralCode}', [AffiliateEmbedController::class, 'widget'])->name('affiliate.embed.widget');
Route::get('/embed/afiliado/{referralCode}/criativo/{preset}.svg', [AffiliateEmbedController::class, 'graphic'])->name('affiliate.embed.graphic');

// Membros, Ranking e Somos Unicas
Route::get('/membros', [MemberController::class, 'index'])->name('membros');
Route::get('/ranking', [RankingPublicController::class, 'index'])->name('ranking.public');
Route::get('/somos-unicas', [SomosUnicasController::class, 'index'])->name('somos-unicas');
Route::get('/somos-unicas/sobre', [SomosUnicasAboutController::class, 'index'])->name('site.somos-unicas.sobre');

// Vagas publicas e Parceiros
Route::get('/vagas-abertas', [OportunidadesTesteController::class, 'index'])->name('jobs.public.index');
Route::get('/vagas-abertas/{job}', [JobPublicController::class, 'show'])->name('jobs.public.show');
Route::get('/cadastro-curriculo', fn () => redirect()->to(route('jobs.public.index') . '#lista-vagas'))->name('curriculum.register');
Route::get('/parceiros', [PublicPartnerController::class, 'index'])->name('partners.index');
Route::get('/parceiros/{partner:slug}', [PublicPartnerController::class, 'show'])->name('partners.show');
Route::get('/empresa/{slug}', [CompanyProfileController::class, 'show'])->name('companies.show');

// Catalogo publico de Eventos (exclui /eventos/create da captura dinamica)
Route::get('/eventos', [EventController::class, 'index'])->name('events.index');
Route::get('/eventos/{event}', [EventController::class, 'show'])
    ->where('event', '^(?!create$)[^/]+$')
    ->name('events.show');

// Catalogo publico de Cursos, Mentorias e Revistas
Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('mentorships', [MentorshipController::class, 'index'])->name('mentorships.index');
Route::get('mentorships/{mentorship}', [MentorshipController::class, 'show'])->name('mentorships.show');
Route::get('revistas', [MagazineController::class, 'index'])->name('magazines.index');
Route::get('revistas/{magazine:slug}', [MagazineController::class, 'show'])->name('magazines.show');

// Marketplace e Lojas publicas (exclui rotas de carrinho e checkout)
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/loja/{storeSlug}', [SellerStorefrontController::class, 'show'])
    ->where('storeSlug', '^(?!carrinho$|checkout$|produtos$)[^/]+$')
    ->name('seller-stores.show');
Route::get('/loja/{storeSlug}/p/{productSlug}', [SellerStorefrontController::class, 'product'])
    ->where('storeSlug', '^(?!carrinho$|checkout$|produtos$)[^/]+$')
    ->name('seller-stores.products.show');
Route::get('/share/{code}', [ShareController::class, 'product'])->name('share.product');
Route::get('/post/{post}', [SocialController::class, 'publicPost'])->name('social.post.public');

// PWA, Sitemap e Assets de raiz
Route::get('/service-worker.js', function () {
    $path = public_path('service-worker.js');
    abort_unless(file_exists($path), 404);

    return response()->file($path, ['Content-Type' => 'application/javascript']);
});

Route::get('/favicon.ico', function () {
    $custom = Setting::get('favicon_image');
    if ($custom && file_exists(public_path($custom))) {
        return response()->file(public_path($custom));
    }

    $ico = public_path('favicon.ico');
    if (file_exists($ico)) {
        return response()->file($ico, ['Content-Type' => 'image/x-icon']);
    }

    $default = public_path('img/logo.svg');
    abort_unless(file_exists($default), 404);

    return response()->file($default, ['Content-Type' => 'image/svg+xml']);
});

Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('manifest');
Route::get('/offline', fn () => view('offline'))->name('offline');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    return response("User-agent: *\nAllow: /\nSitemap: " . url('sitemap.xml'), 200, ['Content-Type' => 'text/plain']);
});
