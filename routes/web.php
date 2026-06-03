<?php
// Rotas de gerenciamento de cron (admin/superadmin)
Route::middleware(['auth', 'admin'])->prefix('admin/cron')->name('admin.cron.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\CronController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\CronController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\CronController::class, 'store'])->name('store');
    Route::get('/{task}/edit', [\App\Http\Controllers\Admin\CronController::class, 'edit'])->name('edit');
    Route::put('/{task}', [\App\Http\Controllers\Admin\CronController::class, 'update'])->name('update');
    Route::delete('/{task}', [\App\Http\Controllers\Admin\CronController::class, 'destroy'])->name('destroy');
    Route::get('/{task}/logs', [\App\Http\Controllers\Admin\CronController::class, 'logs'])->name('logs');
    Route::post('/{task}/run', [\App\Http\Controllers\Admin\CronController::class, 'run'])->name('run');
    Route::post('/run-all', [\App\Http\Controllers\Admin\CronController::class, 'runAll'])->name('run-all');
});

// Rotas de upload chunked (admin)
Route::middleware(['auth', 'admin'])->prefix('admin/upload')->name('admin.upload.')->group(function () {
    Route::post('/chunk', [\App\Http\Controllers\Admin\ChunkedUploadController::class, 'chunk'])->name('chunk');
    Route::post('/assemble', [\App\Http\Controllers\Admin\ChunkedUploadController::class, 'assemble'])->name('assemble');
});

// Rotas de fontes personalizadas (admin)
Route::middleware(['auth', 'admin'])->prefix('admin/fonts')->name('admin.fonts.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\CustomFontController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Admin\CustomFontController::class, 'store'])->name('store');
    Route::post('/detect-metadata', [\App\Http\Controllers\Admin\CustomFontController::class, 'detectMetadata'])->name('detect-metadata');
    Route::get('/active', [\App\Http\Controllers\Admin\CustomFontController::class, 'getActiveFonts'])->name('active');
    Route::delete('/{font}', [\App\Http\Controllers\Admin\CustomFontController::class, 'destroy'])->name('destroy');
});
// Rota para checkout de assinatura (compatível com premium.blade.php) — sempre no início para garantir visibilidade
Route::get('/assinar/{plan}', [\App\Http\Controllers\SubscriptionController::class, 'checkout'])->name('subscription.checkout');
Route::post('/assinar/{plan}', [\App\Http\Controllers\SubscriptionController::class, 'process'])->name('subscription.process');
Route::post('/assinar/{plan}/prepare-sumup', [\App\Http\Controllers\SubscriptionController::class, 'prepareSumUp'])->name('subscription.prepare-sumup')->middleware('auth');
Route::get('/assinatura/sucesso/{order}', [\App\Http\Controllers\SubscriptionController::class, 'success'])->name('subscription.success');

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\MailTestController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\SatisfactionController;

// Health check (advanced-security-performance, Requirement 9)
Route::get('/health', [\App\Http\Controllers\HealthController::class, 'index'])->name('health');

Route::get('/storage/{path}', [\App\Http\Controllers\PublicStorageProxyController::class, 'storage'])
    ->where('path', '.*')
    ->name('public.storage');
Route::get('/uploads/{path}', [\App\Http\Controllers\PublicStorageProxyController::class, 'uploads'])
    ->where('path', '.*')
    ->name('public.uploads');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/portal', [HomeController::class, 'portal'])->name('portal');
Route::get('/planos', [HomeController::class, 'premium'])->name('planos');
Route::get('/galeria', [\App\Http\Controllers\GalleryController::class, 'index'])->name('gallery.index');
Route::get('/galeria/{event}', [\App\Http\Controllers\GalleryController::class, 'show'])->name('gallery.show');
Route::post('/depoimentos', [\App\Http\Controllers\TestimonialController::class, 'store'])->middleware('auth')->name('testimonials.store');

// Rota de Emergência para Diagnóstico (Protegida)
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {
    Route::get('/debug-test', function () {
        return "<h1>Laravel is Running!</h1> PHP Version: " . phpversion();
    });

    // Rota de Emergência para Limpeza de Cache
    Route::get('/limpar-cache', function () {
        $log = [];

        // 1. View Cache
        $viewPath = storage_path('framework/views');
        $files = glob("$viewPath/*.php");
        foreach ($files as $file) {
            @unlink($file);
            $log[] = "View deletada: " . basename($file);
        }

        // 2. Route/Config Cache
        $bootstrapCache = base_path('bootstrap/cache');
        $caches = ['routes-v7.php', 'routes.php', 'config.php', 'data.php'];
        foreach ($caches as $c) {
            $f = "$bootstrapCache/$c";
            if (file_exists($f)) {
                @unlink($f);
                $log[] = "Cache deletado: $c";
            }
        }

        // 3. Artisan commands (fallback)
        try {
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');
            \Artisan::call('cache:clear');
            $log[] = "Artisan commands executed.";
        } catch (\Exception $e) {
            $log[] = "Artisan error: " . $e->getMessage();
        }
        return response()->json($log);
    });
});

// Institutional Pages
Route::get('/sobre', [\App\Http\Controllers\InstitucionalController::class, 'sobre'])->name('sobre');
Route::get('/manifesto', [\App\Http\Controllers\InstitucionalController::class, 'manifesto'])->name('manifesto');
Route::get('/quem-somos', [\App\Http\Controllers\InstitucionalController::class, 'quemSomos'])->name('quem-somos');
Route::get('/como-funciona', [\App\Http\Controllers\InstitucionalController::class, 'comoFunciona'])->name('como-funciona');
Route::get('/valores', [\App\Http\Controllers\InstitucionalController::class, 'valores'])->name('valores');
Route::get('/termos', [\App\Http\Controllers\InstitucionalController::class, 'termos'])->name('site.termos');
Route::get('/privacidade', [\App\Http\Controllers\InstitucionalController::class, 'privacidade'])->name('site.privacidade');
Route::get('/consentimento-lgpd', [\App\Http\Controllers\InstitucionalController::class, 'lgpd'])->name('site.lgpd');
Route::post('/lgpd/aceite', [\App\Http\Controllers\LegalConsentController::class, 'store'])->middleware('auth')->name('lgpd.accept');
Route::get('/embed/afiliado/{referralCode}', [\App\Http\Controllers\AffiliateEmbedController::class, 'widget'])->name('affiliate.embed.widget');
Route::get('/embed/afiliado/{referralCode}/criativo/{preset}.svg', [\App\Http\Controllers\AffiliateEmbedController::class, 'graphic'])->name('affiliate.embed.graphic');
Route::get('/contato', fn() => view('site.institucional.contato'))->name('contato');
Route::post('/contato', [ContactController::class, 'send'])->middleware('throttle:5,1')->name('contato.send');

// Members
Route::get('/membros', [\App\Http\Controllers\MemberController::class, 'index'])->name('membros');
Route::get('/ranking', [\App\Http\Controllers\RankingPublicController::class, 'index'])->name('ranking.public');

// Somos Únicas
Route::get('/somos-unicas', [\App\Http\Controllers\SomosUnicasController::class, 'index'])->name('somos-unicas');
Route::get('/somos-unicas/sobre', [\App\Http\Controllers\SomosUnicasAboutController::class, 'index'])->name('site.somos-unicas.sobre');

// Migrations & Demo (protegidas por middleware global + explicito)
Route::middleware(['sensitive.production'])->group(function () {
    Route::get('/run-migrations', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            return "<h1>Migrações concluídas com sucesso!</h1>";
        } catch (\Exception $e) {
            return "<h1>Erro ao rodar migrações:</h1> <pre>" . $e->getMessage() . "</pre>";
        }
    });

    Route::get('/demo-somos-unicas', function () {
        try {
            $ownerId = auth()->check() ? auth()->id() : (\App\Models\User::where('role', 'superadmin')->first()->id ?? 1);

            // Palestra 1
            \App\Models\Event::create([
                'user_id' => $ownerId,
                'title' => 'Palestra: Protagonismo Feminino nos Negócios',
                'speaker' => 'Dra. Luiza Helena',
                'description' => '<p>Descubra como mulheres estão transformando o mercado corporativo e assumindo a linha de frente nos grandes negócios.</p>',
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

            // Palestra 2
            \App\Models\Event::create([
                'user_id' => $ownerId,
                'title' => 'Workshop: Liderança Feminina Na Prática',
                'speaker' => 'Camila Farani',
                'description' => '<p>Um workshop 100% focado em técnicas de negociação, networking e empoderamento feminino.</p>',
                'start_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'end_at' => now()->addDays(10)->addHours(4)->format('Y-m-d H:i:s'),
                'location' => 'Online (Zoom)',
                'price' => 97.00,
                'capacity' => 500,
                'published' => true,
                'is_somos_unicas' => true,
                'color' => '#db2777',
                'image' => 'https://placehold.co/800x600/fdf2f8/db2777?text=Lideranca+na+Pratica',
            ]);

            // Curso
            \App\Models\Course::create([
                'user_id' => $ownerId,
                'title' => 'Empreendedorismo Feminino de A a Z',
                'short_description' => 'Aprenda do zero como tirar sua ideia do papel e criar um negócio rentável.',
                'full_description' => '<p>Este curso abrange todos os passos para mulheres criarem negócios prósperos desde a ideação até as vendas avançadas.</p>',
                'price' => 297.00,
                'author_name' => 'Equipe Somos Únicas',
                'status' => 'published',
                'is_somos_unicas' => true,
                'thumbnail' => 'https://placehold.co/800x600/fce7f3/be185d?text=Empreendedorismo+A-Z',
            ]);

            // Mentoria
            \App\Models\Mentorship::create([
                'title' => 'Mentoria VIP: Decolando sua Carreira',
                'mentor_id' => $ownerId,
                'description' => '<p>Sessões individuais de mentoria exclusivas para mulheres buscando o próximo nível profissional.</p>',
                'price' => 997.00,
                'slots' => 10,
                'type' => 'online',
                'video_platform' => 'Zoom',
                'is_somos_unicas' => true,
                'image' => 'https://placehold.co/800x600/fecdd3/e11d48?text=Mentoria+VIP',
            ]);

            return "<h1>Conteúdo Demo criado com sucesso!</h1><p><a href='/somos-unicas'>Clique aqui para ver a página Somos Únicas</a></p>";
        } catch (\Exception $e) {
            return "<h1>Erro:</h1> <pre>" . $e->getMessage() . "</pre><br><pre>" . $e->getTraceAsString() . "</pre>";
        }
    });
});

// Vagas Públicas (Externas)
Route::get('/vagas-abertas', [\App\Http\Controllers\OportunidadesTesteController::class, 'index'])->name('jobs.public.index');
Route::get('/vagas-abertas/{job}', [\App\Http\Controllers\JobPublicController::class, 'show'])->name('jobs.public.show');
Route::post('/vagas-abertas/{job}/inscricao-vaga', [\App\Http\Controllers\JobPublicController::class, 'apply'])
    ->middleware(['auth', 'check.plan'])
    ->name('jobs.public.apply');
Route::get('/cadastro-curriculo', fn() => redirect()->to(route('jobs.public.index') . '#lista-vagas'))->name('curriculum.register');

// ── Parceiros ────────────────────────────────────────────────────────────────
Route::get('/parceiros', [\App\Http\Controllers\PublicPartnerController::class, 'index'])->name('partners.index');
Route::get('/parceiros/{partner:slug}', [\App\Http\Controllers\PublicPartnerController::class, 'show'])->name('partners.show');

Route::middleware(['auth', 'admin'])->prefix('admin/partners')->name('admin.partners.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\PartnerController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\PartnerController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\PartnerController::class, 'store'])->name('store');
    Route::get('/{partner}/edit', [\App\Http\Controllers\Admin\PartnerController::class, 'edit'])->name('edit');
    Route::put('/{partner}', [\App\Http\Controllers\Admin\PartnerController::class, 'update'])->name('update');
    Route::delete('/{partner}', [\App\Http\Controllers\Admin\PartnerController::class, 'destroy'])->name('destroy');
    Route::post('/order', [\App\Http\Controllers\Admin\PartnerController::class, 'updateOrder'])->name('order');

    // Cupons aninhados
    Route::post('/{partner}/coupons', [\App\Http\Controllers\Admin\PartnerCouponController::class, 'store'])->name('coupons.store');
    Route::put('/{partner}/coupons/{coupon}', [\App\Http\Controllers\Admin\PartnerCouponController::class, 'update'])->name('coupons.update');
    Route::delete('/{partner}/coupons/{coupon}', [\App\Http\Controllers\Admin\PartnerCouponController::class, 'destroy'])->name('coupons.destroy');
});

Route::middleware(['auth'])->prefix('meu-parceiro')->name('member.partner.')->group(function () {
    Route::get('/', [\App\Http\Controllers\MemberPartnerController::class, 'index'])->name('index');
    Route::post('/cupons', [\App\Http\Controllers\MemberPartnerController::class, 'store'])->name('coupons.store');
    Route::put('/cupons/{coupon}', [\App\Http\Controllers\MemberPartnerController::class, 'update'])->name('coupons.update');
    Route::delete('/cupons/{coupon}', [\App\Http\Controllers\MemberPartnerController::class, 'destroy'])->name('coupons.destroy');
});

// Eventos (Public)
Route::get('/eventos', [\App\Http\Controllers\EventController::class, 'index'])->name('events.index');
Route::get('/eventos/{event}', [\App\Http\Controllers\EventController::class, 'show'])->name('events.show');
Route::middleware(['auth', 'check.feature:events_create'])->group(function () {
    Route::get('/eventos/create', [\App\Http\Controllers\EventController::class, 'create'])->name('events.create');
    Route::post('/eventos', [\App\Http\Controllers\EventController::class, 'store'])->name('events.store');
});
Route::middleware(['auth', 'check.feature:events_edit'])->group(function () {
    Route::get('/eventos/{event}/edit', [\App\Http\Controllers\EventController::class, 'edit'])->name('events.edit');
    Route::put('/eventos/{event}', [\App\Http\Controllers\EventController::class, 'update'])->name('events.update');
});
Route::middleware(['auth', 'check.feature:events_delete'])->group(function () {
    Route::delete('/eventos/{event}', [\App\Http\Controllers\EventController::class, 'destroy'])->name('events.destroy');
});
Route::get('/eventos/{event}/checkout', [\App\Http\Controllers\EventReservationController::class, 'checkout'])->name('events.checkout');
Route::post('/eventos/{event}/reservar', [\App\Http\Controllers\EventReservationController::class, 'reserve'])->name('events.reserve');
Route::get('/eventos/{event}/expositor', [\App\Http\Controllers\EventExhibitorCheckoutController::class, 'show'])->name('events.exhibitor.show');
Route::post('/eventos/{event}/expositor/checkout', [\App\Http\Controllers\EventExhibitorCheckoutController::class, 'checkout'])->middleware('throttle:6,1')->name('events.exhibitor.checkout');
Route::get('/eventos/{event}/expositor/sucesso/{order}', [\App\Http\Controllers\EventExhibitorCheckoutController::class, 'success'])->name('events.exhibitor.success');
Route::get('/eventos/{event}/expositor/pendente/{order}', [\App\Http\Controllers\EventExhibitorCheckoutController::class, 'pending'])->name('events.exhibitor.pending');
Route::get('/eventos/{event}/expositor/falha/{order}', [\App\Http\Controllers\EventExhibitorCheckoutController::class, 'failure'])->name('events.exhibitor.failure');
Route::get('/eventos/pagamento/sucesso/{order}', [\App\Http\Controllers\EventReservationController::class, 'paymentSuccess'])->name('events.payment.success');
Route::get('/eventos/pagamento/pendente/{order}', [\App\Http\Controllers\EventReservationController::class, 'paymentPending'])->name('events.payment.pending');
Route::get('/eventos/pagamento/falha/{order}', [\App\Http\Controllers\EventReservationController::class, 'paymentFailure'])->name('events.payment.failure');
Route::get('/eventos/pagamento/selecionar-gateway/{order}', [\App\Http\Controllers\EventReservationController::class, 'selectGateway'])->name('events.payment.select-gateway');
Route::post('/eventos/pagamento/processar-gateway/{order}', [\App\Http\Controllers\EventReservationController::class, 'processGateway'])->name('events.payment.process-gateway');

// PWA & Utils
Route::get('/service-worker.js', function () {
    $path = public_path('service-worker.js');
    abort_unless(file_exists($path), 404);
    return response()->file($path, ['Content-Type' => 'application/javascript']);
});
Route::get('/favicon.ico', function () {
    $custom = \App\Models\Setting::get('favicon_image');
    if ($custom && file_exists(public_path($custom))) {
        return response()->file(public_path($custom));
    }
    $default = public_path('img/logo.svg');
    abort_unless(file_exists($default), 404);
    return response()->file($default, ['Content-Type' => 'image/svg+xml']);
});

Route::get('/post/{post}', [\App\Http\Controllers\SocialController::class, 'publicPost'])->name('social.post.public');

// Auth
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'authenticate'])->middleware('throttle:10,1');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
Route::get('/register', fn() => view('auth.register'))->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'store'])->middleware('throttle:5,1');
Route::get('/password/forgot', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:5,1');
Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update')->middleware('throttle:5,1');

// Social Auth
Route::get('/auth/redirect/{provider}', [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/callback/{provider}', [\App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])->name('social.callback');

// Uploads
Route::post('/upload', [\App\Http\Controllers\UploadController::class, 'upload'])->middleware('auth')->name('upload.file');

// Webhook
Route::post('/webhook/mercadopago', [\App\Http\Controllers\PaymentWebhookController::class, 'mercadopago'])->defaults('seller_id', 'platform');
Route::post('/webhook/sumup/{orderId}/{token}', [\App\Http\Controllers\PaymentWebhookController::class, 'sumup'])->name('webhook.sumup');

// Installer (protegido por middleware global + explicito)
Route::middleware(['sensitive.production'])->prefix('install')->group(function () {
    Route::get('/', [\App\Http\Controllers\InstallController::class, 'index'])->name('install.index');
    Route::post('/run', [\App\Http\Controllers\InstallController::class, 'run'])->name('install.run');
    Route::post('/test-connection', [\App\Http\Controllers\InstallController::class, 'testConnection'])->name('install.test-connection');
});
// Legacy installer routes (backward compat)
Route::middleware(['sensitive.production'])->prefix('backend/install')->group(function () {
    Route::post('/run', [\App\Http\Controllers\InstallController::class, 'run'])->name('install.run.legacy');
    Route::post('/test-connection', [\App\Http\Controllers\InstallController::class, 'testConnection'])->name('install.test-connection.legacy');
});

Route::post('/api/interactions', [InteractionController::class, 'store'])->middleware('auth')->name('api.interactions.store');
Route::post('/api/satisfactions', [SatisfactionController::class, 'store'])->middleware('auth')->name('api.satisfactions.store');
Route::get('/api/ranking', [RankingController::class, 'index'])->name('api.ranking.index');
Route::get('/api/venue-search', [\App\Http\Controllers\Api\VenueSearchController::class, 'search'])->middleware('auth')->name('api.venue-search');

Route::get('/manifest.webmanifest', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('manifest');
Route::get('/offline', fn() => view('offline'))->name('offline');

Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    return response("User-agent: *\nAllow: /\nSitemap: " . url('sitemap.xml'), 200, ['Content-Type' => 'text/plain']);
});

// Courses (Public)
Route::get('courses', [\App\Http\Controllers\CourseController::class, 'index'])->name('courses.index');
Route::get('courses/{course}', [\App\Http\Controllers\CourseController::class, 'show'])->name('courses.show');
Route::post('courses/{course}/complete', [\App\Http\Controllers\CourseController::class, 'complete'])->middleware(['auth'])->name('courses.complete');

// Mentorships (Public)
Route::get('mentorships', [\App\Http\Controllers\MentorshipController::class, 'index'])->name('mentorships.index');
Route::get('mentorships/{mentorship}', [\App\Http\Controllers\MentorshipController::class, 'show'])->name('mentorships.show');
Route::get('mentorships/{mentorship}/checkout', [\App\Http\Controllers\MentorshipCheckoutController::class, 'show'])->name('mentorships.checkout.show');
Route::post('mentorships/{mentorship}/checkout', [\App\Http\Controllers\MentorshipCheckoutController::class, 'process'])->name('mentorships.checkout.process');

// Revistas (Public/Members) — flipbook viewer
// Autenticacao exigida para leitura; a proteção de visibilidade ocorre no controller
Route::middleware(['auth'])->group(function () {
    Route::get('revistas', [\App\Http\Controllers\MagazineController::class, 'index'])->name('magazines.index');
    Route::get('revistas/{magazine:slug}', [\App\Http\Controllers\MagazineController::class, 'show'])->name('magazines.show');
});

// Reviews
Route::post('courses/{course}/reviews', [\App\Http\Controllers\ItemReviewController::class, 'storeCourse'])->middleware(['auth', 'check.feature:courses_review'])->name('courses.reviews.store');
Route::post('mentorships/{mentorship}/reviews', [\App\Http\Controllers\ItemReviewController::class, 'storeMentorship'])->middleware(['auth', 'check.feature:mentorships_review'])->name('mentorships.reviews.store');

// Lessons (Granular)
Route::prefix('courses/{course}/lessons')->name('courses.lessons.')->group(function () {
    Route::post('/', [\App\Http\Controllers\LessonController::class, 'store'])->middleware(['auth', 'check.feature:courses_lessons_create'])->name('store');
    Route::put('/{lesson}', [\App\Http\Controllers\LessonController::class, 'update'])->middleware(['auth', 'check.feature:courses_lessons_edit'])->name('update');
    Route::delete('/{lesson}', [\App\Http\Controllers\LessonController::class, 'destroy'])->middleware(['auth', 'check.feature:courses_lessons_delete'])->name('destroy');
    Route::get('/{lesson}', [\App\Http\Controllers\LessonController::class, 'show'])->middleware(['check.feature:courses_lessons_access'])->name('show');
    Route::get('/{lesson}/stream/key', [\App\Http\Controllers\LessonVideoStreamController::class, 'key'])->middleware(['check.feature:courses_lessons_access'])->name('stream.key');
    Route::get('/{lesson}/stream/{path?}', [\App\Http\Controllers\LessonVideoStreamController::class, 'stream'])->middleware(['check.feature:courses_lessons_access'])->where('path', '.*')->name('stream');
    Route::post('/{lesson}/attachments', [\App\Http\Controllers\LessonController::class, 'uploadAttachment'])->middleware(['auth', 'check.feature:courses_lessons_attachments_upload'])->name('attachments.upload');
    Route::get('/{lesson}/attachments/{attachment}/download', [\App\Http\Controllers\LessonController::class, 'downloadAttachment'])->middleware(['auth', 'check.feature:courses_lessons_attachments_download'])->name('attachments.download');
    Route::delete('/{lesson}/attachments/{attachment}', [\App\Http\Controllers\LessonController::class, 'deleteAttachment'])->middleware(['auth', 'check.feature:courses_lessons_attachments_delete'])->name('attachments.destroy');
    Route::put('/{lesson}/attachments/{attachment}', [\App\Http\Controllers\LessonController::class, 'renameAttachment'])->middleware(['auth', 'check.feature:courses_lessons_attachments_edit'])->name('attachments.rename');
    Route::get('/{lesson}/details', [\App\Http\Controllers\LessonController::class, 'getDetails'])->middleware(['auth', 'check.feature:courses_lessons_access'])->name('details');
    Route::post('/{lesson}/progress', [\App\Http\Controllers\LessonController::class, 'updatePlaybackProgress'])->middleware(['auth', 'check.feature:courses_lessons_access'])->name('progress.update');
    Route::post('/{lesson}/bookmarks', [\App\Http\Controllers\LessonController::class, 'storeBookmark'])->middleware(['auth', 'check.feature:courses_lessons_access'])->name('bookmarks.store');
    Route::delete('/{lesson}/bookmarks/{bookmark}', [\App\Http\Controllers\LessonController::class, 'destroyBookmark'])->middleware(['auth', 'check.feature:courses_lessons_access'])->name('bookmarks.destroy');
});

// Gateway OAuth
Route::middleware(['auth'])->prefix('gateway/mercadopago')->name('gateway.mercadopago.')->group(function () {
    Route::get('connect', [\App\Http\Controllers\GatewayAccountController::class, 'connect'])->name('connect');
    Route::get('callback', [\App\Http\Controllers\GatewayAccountController::class, 'callback'])->name('callback');
});

// Auth Required Group
Route::middleware(['auth', 'check.plan'])->group(function () {
    Route::get('/settings/payment', [\App\Http\Controllers\GatewayAccountController::class, 'index'])->name('settings.payment.index');
    Route::post('/settings/payment', [\App\Http\Controllers\GatewayAccountController::class, 'update'])->name('settings.payment.update');

    // Social / Community
    Route::middleware(['check.feature:community'])->group(function () {
        Route::get('/feed', [\App\Http\Controllers\SocialController::class, 'feed'])->name('social.feed');
        Route::get('/profile/{username}', [\App\Http\Controllers\SocialController::class, 'profile'])->name('social.profile');
        Route::post('/connect/{user}', [\App\Http\Controllers\ConnectionController::class, 'connect'])->name('connection.connect');
        Route::post('/connection/accept/{user}', [\App\Http\Controllers\ConnectionController::class, 'accept'])->name('connection.accept');
        Route::post('/connection/remove/{user}', [\App\Http\Controllers\ConnectionController::class, 'remove'])->name('connection.remove');
        Route::post('/connection/block/{user}', [\App\Http\Controllers\ConnectionController::class, 'block'])->name('connection.block');
        Route::get('/connection/blocked', [\App\Http\Controllers\ConnectionController::class, 'blockedUsers'])->name('connection.blocked');
        Route::post('/connection/unblock/{user}', [\App\Http\Controllers\ConnectionController::class, 'unblock'])->name('connection.unblock');
        Route::get('/connection/notifications', [\App\Http\Controllers\ConnectionController::class, 'notifications'])->name('connection.notifications');
        Route::post('/post', [\App\Http\Controllers\SocialController::class, 'storePost'])->name('social.post.store');
        Route::post('/post/{post}/react', [\App\Http\Controllers\SocialController::class, 'toggleReaction'])->name('social.post.react');
        Route::post('/post/{post}/comment', [\App\Http\Controllers\SocialController::class, 'storeComment'])->name('social.post.comment');
        Route::delete('/comment/{comment}', [\App\Http\Controllers\SocialController::class, 'destroyComment'])->name('social.comment.destroy');
        Route::post('/post/{post}/hide', [\App\Http\Controllers\SocialController::class, 'hidePost'])->name('social.post.hide');
        Route::post('/post/{post}/share', [\App\Http\Controllers\SocialController::class, 'sharePost'])->name('social.post.share');
        Route::post('/post/{post}/share-to-user', [\App\Http\Controllers\SocialController::class, 'sharePostToUser'])->name('social.post.share.user');
        Route::post('/post/{post}/report', [\App\Http\Controllers\SocialController::class, 'reportPost'])->name('social.post.report');
        Route::post('/post/{post}/unpublish', [\App\Http\Controllers\SocialController::class, 'unpublishPost'])->name('social.post.unpublish');
        Route::delete('/post/{post}', [\App\Http\Controllers\SocialController::class, 'destroyPost'])->name('social.post.destroy');
        Route::get('/compartilhamentos/pendentes', [\App\Http\Controllers\ShareRequestController::class, 'index'])->name('social.share-requests.index');
        Route::post('/compartilhamentos/{shareRequest}/aprovar', [\App\Http\Controllers\ShareRequestController::class, 'approve'])->name('social.share-requests.approve');
        Route::post('/compartilhamentos/{shareRequest}/recusar', [\App\Http\Controllers\ShareRequestController::class, 'reject'])->name('social.share-requests.reject');
        Route::get('/api/compartilhamentos/pendentes/count', [\App\Http\Controllers\ShareRequestController::class, 'pendingCount'])->name('social.share-requests.count');
    });

    // Chat
    Route::middleware(['check.feature:chat', 'check.connection'])->group(function () {
        Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/start/{user}', [\App\Http\Controllers\ChatController::class, 'start'])->name('chat.start');
        Route::get('/chat/list', [\App\Http\Controllers\ChatController::class, 'list'])->name('chat.list');
        Route::get('/chat/{conversation}/messages', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('/chat/{conversation}/message', [\App\Http\Controllers\ChatController::class, 'storeMessage'])->name('chat.message.store');
        Route::get('/chat/{conversation}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
        Route::get('/chat/with/{user}', [\App\Http\Controllers\ChatController::class, 'withUser'])->name('chat.with.user');
        Route::post('/chat/with/{user}/message', [\App\Http\Controllers\ChatController::class, 'storeMessageWithUser'])->name('chat.with.user.message');
    });

    // Notifications
    Route::get('/api/notifications/hub', [\App\Http\Controllers\NotificationHubController::class, 'index'])->name('notifications.hub');
    Route::post('/api/notifications/hub/acknowledge', [\App\Http\Controllers\NotificationHubController::class, 'acknowledge'])->name('notifications.hub.acknowledge');
    Route::get('/notificacoes', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notificacoes/read/{id?}', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markRead');
    Route::delete('/notificacoes/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/painel/compras', [\App\Http\Controllers\Panel\MarketplacePurchaseController::class, 'index'])->name('panel.purchases.index');
    Route::post('/painel/compras/{order}/retry', [\App\Http\Controllers\Panel\MarketplacePurchaseController::class, 'retry'])->name('panel.purchases.retry');
    Route::post('/painel/compras/{order}/cancel', [\App\Http\Controllers\Panel\MarketplacePurchaseController::class, 'cancel'])->name('panel.purchases.cancel');
    Route::get('/painel/compras/{order}/itens/{item}/download', [\App\Http\Controllers\Panel\MarketplacePurchaseController::class, 'downloadDigital'])->name('panel.purchases.download');
});

// Painel do Membro (Tailwind)
Route::prefix('painel')->name('panel.')->middleware(['auth', 'check.plan', 'check.blocked', 'superadmin.legacy'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Panel\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [\App\Http\Controllers\Panel\DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/ingressos', [\App\Http\Controllers\Panel\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/recebimentos', [\App\Http\Controllers\Panel\SplitController::class, 'index'])->name('splits.index');
    Route::get('/marketing', [\App\Http\Controllers\Panel\MarketingController::class, 'index'])->name('marketing.index');
    Route::get('/perfil', [\App\Http\Controllers\Panel\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/perfil', [\App\Http\Controllers\Panel\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/perfil/upload-photo', [\App\Http\Controllers\Panel\ProfileController::class, 'uploadPhoto'])->name('profile.upload-photo');
    Route::get('/certificados', [\App\Http\Controllers\Panel\CertificateController::class, 'index'])->name('certificates.index');
    Route::post('/certificados/gerar', [\App\Http\Controllers\Panel\CertificateController::class, 'generate'])->name('certificates.generate');
    Route::get('/certificados/{certificate}', [\App\Http\Controllers\Panel\CertificateController::class, 'show'])->name('certificates.show');
    Route::get('/certificados/{certificate}/download', [\App\Http\Controllers\Panel\CertificateController::class, 'download'])->name('certificates.download');

    Route::prefix('marketplace')->name('marketplace.')->middleware('check.marketplace.seller')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\MarketplaceController::class, 'index'])->name('index');
        Route::get('/pagamentos', [\App\Http\Controllers\Panel\MarketplaceController::class, 'payments'])->name('payments');
        Route::get('/pagamentos/configurar', [\App\Http\Controllers\Panel\MarketplaceController::class, 'editPayment'])->name('payments.edit');
        Route::post('/pagamentos/testar', [\App\Http\Controllers\Panel\MarketplaceController::class, 'testCredentials'])->name('payments.test');
        Route::get('/loja', [\App\Http\Controllers\Panel\MarketplaceStoreController::class, 'edit'])->name('store.edit');
        Route::post('/loja', [\App\Http\Controllers\Panel\MarketplaceStoreController::class, 'update'])->name('store.update');
        Route::post('/loja/upload', [\App\Http\Controllers\Panel\MarketplaceStoreController::class, 'uploadMedia'])->name('store.upload-media');
        Route::get('/produtos', [\App\Http\Controllers\Panel\SellerProductController::class, 'index'])->name('products.index');
        Route::get('/produtos/novo', [\App\Http\Controllers\Panel\SellerProductController::class, 'create'])->name('products.create');
        Route::post('/produtos', [\App\Http\Controllers\Panel\SellerProductController::class, 'store'])->name('products.store');
        Route::get('/produtos/{product}/editar', [\App\Http\Controllers\Panel\SellerProductController::class, 'edit'])->name('products.edit');
        Route::put('/produtos/{product}', [\App\Http\Controllers\Panel\SellerProductController::class, 'update'])->name('products.update');
        Route::delete('/produtos/{product}', [\App\Http\Controllers\Panel\SellerProductController::class, 'destroy'])->name('products.destroy');
        Route::delete('/produtos/{product}/midia/{media}', [\App\Http\Controllers\Panel\SellerProductController::class, 'destroyMedia'])->name('products.media.destroy');
        Route::get('/pedidos', [\App\Http\Controllers\Panel\SellerOrderController::class, 'index'])->name('orders.index');
        Route::post('/pedidos/{order}/envio', [\App\Http\Controllers\Panel\SellerOrderController::class, 'updateShipment'])->name('orders.shipment.update');
        Route::get('/vendas', [\App\Http\Controllers\Panel\MarketplaceController::class, 'sales'])->name('sales');
        Route::get('/contabilidade', [\App\Http\Controllers\Panel\MarketplaceAccountingController::class, 'index'])->name('accounting');
        Route::get('/contabilidade/exportar', [\App\Http\Controllers\Panel\MarketplaceAccountingController::class, 'export'])->name('accounting.export');
        Route::get('/contabilidade/imprimir', [\App\Http\Controllers\Panel\MarketplaceAccountingController::class, 'print'])->name('accounting.print');
    });

    Route::get('/vagas', [\App\Http\Controllers\Panel\JobController::class, 'index'])->name('jobs.index');
    Route::get('/vagas/{job}', [\App\Http\Controllers\Panel\JobController::class, 'show'])->name('jobs.show');
    Route::resource('my-jobs', \App\Http\Controllers\Panel\MyJobController::class);
    Route::get('my-jobs/{my_job}/candidates', [\App\Http\Controllers\Panel\MyJobController::class, 'candidates'])->name('my-jobs.candidates');
    Route::get('my-jobs/{my_job}/candidates/{application}/download', [\App\Http\Controllers\Panel\MyJobController::class, 'downloadResume'])->name('my-jobs.candidates.download');
    Route::post('my-jobs/applications/{application}/status', [\App\Http\Controllers\Panel\MyJobController::class, 'updateApplicationStatus'])->name('my-jobs.application.status');

    Route::get('/meus-pontos', [\App\Http\Controllers\Panel\PointsController::class, 'index'])->name('points.index');
    Route::get('/reputacao', [\App\Http\Controllers\Panel\ReputationController::class, 'show'])->name('reputation');
    Route::get('/indicacoes', [\App\Http\Controllers\Panel\ReferralController::class, 'index'])->name('referral.index');
    Route::prefix('solicitacoes-cancelamento')->name('cancellation-requests.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\CancellationRequestController::class, 'index'])->name('index');
        Route::get('/criar/{order_id}/{item_id?}', [\App\Http\Controllers\Panel\CancellationRequestController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Panel\CancellationRequestController::class, 'store'])->name('store');
    });
    Route::get('/indicacoes/exportar', [\App\Http\Controllers\Panel\ReferralController::class, 'export'])->name('referral.export');
    Route::get('/indicacoes/dados', [\App\Http\Controllers\Panel\ReferralController::class, 'stats'])->name('referral.stats');
    Route::post('/indicacoes/track', [\App\Http\Controllers\Panel\ReferralController::class, 'track'])->name('referral.track');
    Route::post('/indicacoes/sandbox', [\App\Http\Controllers\Panel\ReferralController::class, 'storeSandboxRequest'])->name('referral.sandbox.store');
    Route::post('/indicacoes/playground', [\App\Http\Controllers\Panel\ReferralController::class, 'playground'])->name('referral.playground.execute');
    Route::post('/indicacoes/tokens', [\App\Http\Controllers\Panel\ReferralController::class, 'storeToken'])->name('referral.tokens.store');
    Route::put('/indicacoes/tokens/{tokenId}', [\App\Http\Controllers\Panel\ReferralController::class, 'updateToken'])->name('referral.tokens.update');
    Route::delete('/indicacoes/tokens/{tokenId}', [\App\Http\Controllers\Panel\ReferralController::class, 'destroyToken'])->name('referral.tokens.destroy');

    Route::get('/resgate', [\App\Http\Controllers\RedemptionItemController::class, 'index'])->name('redemptions.shop');
    Route::get('/resgate/historico', [\App\Http\Controllers\RedemptionItemController::class, 'history'])->name('redemptions.history');
    Route::post('/resgate/{item}', [\App\Http\Controllers\RedemptionItemController::class, 'redeem'])->name('redemptions.redeem');

    Route::get('/minha-lista', [\App\Http\Controllers\Panel\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/minha-lista/toggle/{course}', [\App\Http\Controllers\Panel\WishlistController::class, 'toggle'])->name('wishlist.toggle');

    Route::get('/galeria', [\App\Http\Controllers\Panel\GalleryController::class, 'index'])->name('gallery.index');
    Route::post('/galeria/upload', [\App\Http\Controllers\Panel\GalleryController::class, 'upload'])->name('gallery.upload');
    Route::post('/galeria/eventos/{event}/capa/upload', [\App\Http\Controllers\Panel\GalleryController::class, 'uploadCover'])->name('gallery.cover.upload');
    Route::post('/galeria/media/{media}/capa', [\App\Http\Controllers\Panel\GalleryController::class, 'setCoverFromMedia'])->name('gallery.cover.media');
    Route::delete('/galeria/eventos/{event}/capa', [\App\Http\Controllers\Panel\GalleryController::class, 'clearCover'])->name('gallery.cover.clear');
    Route::delete('/galeria/{media}', [\App\Http\Controllers\Panel\GalleryController::class, 'destroy'])->name('gallery.destroy');
    Route::resource('coupons', \App\Http\Controllers\Panel\CouponController::class)->except(['show']);
    Route::resource('courses', \App\Http\Controllers\Panel\CourseController::class)->except(['show']);
    Route::resource('events', \App\Http\Controllers\Panel\EventController::class);
    Route::post('events/{event}/media', [\App\Http\Controllers\Panel\EventMediaController::class, 'store'])->name('events.media.store');
    Route::delete('events/{event}/media/{media}', [\App\Http\Controllers\Panel\EventMediaController::class, 'destroy'])->name('events.media.destroy');
    Route::get('events/{event}/scanner', [\App\Http\Controllers\Panel\EventScannerController::class, 'index'])->name('events.scanner');
    Route::post('events/{event}/scanner/validate', [\App\Http\Controllers\Panel\EventScannerController::class, 'validateTicket'])->name('events.scanner.validate');

    // Instructor Area
    Route::prefix('instrutor')->name('instructor.')->middleware([\App\Http\Middleware\EnsureInstructorAccess::class])->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\InstructorController::class, 'index'])->name('dashboard');
        Route::get('/scanner', [\App\Http\Controllers\Admin\QuickScannerController::class, 'index'])->name('scanner');
        Route::post('/scanner/validate', [\App\Http\Controllers\Admin\QuickScannerController::class, 'validateTicket'])->name('scanner.validate');
    });

    // Admin Area (Tailwind)
    Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/security', [\App\Http\Controllers\Admin\Waf\WafSettingsController::class, 'index'])->name('security');
        Route::put('/security', [\App\Http\Controllers\Admin\Waf\WafSettingsController::class, 'update'])->name('security.update');
        Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('/system-health', [\App\Http\Controllers\Admin\DashboardController::class, 'systemHealth'])->name('dashboard.system-health');
        Route::get('/quick-scanner', [\App\Http\Controllers\Admin\QuickScannerController::class, 'index'])->name('quick-scanner');

        // Settings
        Route::get('/settings/{group?}', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/toggle', [\App\Http\Controllers\Admin\SettingController::class, 'toggle'])->name('settings.toggle');
        Route::post('/settings/test-smtp', [\App\Http\Controllers\Admin\SettingController::class, 'testSmtp'])->name('settings.test-smtp');
        Route::post('/settings/test-gateway', [\App\Http\Controllers\Admin\SettingController::class, 'testGateway'])->name('settings.test_gateway');
        Route::post('/settings/test-s3', [\App\Http\Controllers\Admin\SettingController::class, 'testS3'])->name('settings.test-s3');
        Route::post('/settings/test-s3-provider', [\App\Http\Controllers\Admin\SettingController::class, 'testStorageProvider'])->name('settings.test-s3-provider');
        Route::post('/settings/storage/migrate', [\App\Http\Controllers\Admin\SettingController::class, 'migrateStorage'])->name('settings.storage.migrate');
        Route::get('/settings/storage/folders', [\App\Http\Controllers\Admin\SettingController::class, 'storageFolders'])->name('settings.storage.folders');

        // Cron
        Route::prefix('cron')->name('cron.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Admin\CronController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Panel\Admin\CronController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Panel\Admin\CronController::class, 'store'])->name('store');
            Route::get('/{task}/logs', [\App\Http\Controllers\Panel\Admin\CronController::class, 'logs'])->name('logs');
            Route::get('/{task}/edit', [\App\Http\Controllers\Panel\Admin\CronController::class, 'edit'])->name('edit');
            Route::put('/{task}', [\App\Http\Controllers\Panel\Admin\CronController::class, 'update'])->name('update');
            Route::delete('/{task}', [\App\Http\Controllers\Panel\Admin\CronController::class, 'destroy'])->name('destroy');
            Route::post('/{task}/run', [\App\Http\Controllers\Panel\Admin\CronController::class, 'run'])->name('run');
            Route::post('/run-all', [\App\Http\Controllers\Panel\Admin\CronController::class, 'runAll'])->name('run-all');
        });

        Route::post('users/{user}/marketing-manager', [\App\Http\Controllers\Admin\UserController::class, 'setMarketingManager'])->name('users.marketing-manager');
        Route::resource('users', \App\Http\Controllers\Panel\Admin\UserController::class);
        Route::post('plans/{plan}/toggle-active', [\App\Http\Controllers\Panel\Admin\PlanController::class, 'toggleActive'])->name('plans.toggle-active');
        Route::resource('plans', \App\Http\Controllers\Panel\Admin\PlanController::class);
        Route::resource('orders', \App\Http\Controllers\Panel\Admin\OrderController::class)->only(['index', 'show']);
        Route::post('orders/{order}/refund', [\App\Http\Controllers\Panel\Admin\OrderController::class, 'refund'])->name('orders.refund');
        Route::post('orders/{order}/cancel', [\App\Http\Controllers\Panel\Admin\OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('orders/{order}/invoice', [\App\Http\Controllers\Panel\Admin\InvoiceController::class, 'issueForOrder'])->name('orders.invoice');

        Route::prefix('buyer-communication')->name('buyer-communication.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Admin\BuyerCommunicationController::class, 'index'])->name('index');
            Route::post('individual', [\App\Http\Controllers\Panel\Admin\BuyerCommunicationController::class, 'sendIndividual'])->name('individual');
            Route::post('bulk', [\App\Http\Controllers\Panel\Admin\BuyerCommunicationController::class, 'sendBulk'])->name('bulk');
            Route::get('search-users', [\App\Http\Controllers\Panel\Admin\BuyerCommunicationController::class, 'searchUsers'])->name('search-users');
            Route::get('get-items', [\App\Http\Controllers\Panel\Admin\BuyerCommunicationController::class, 'getItems'])->name('get-items');
            Route::get('preview-recipients', [\App\Http\Controllers\Panel\Admin\BuyerCommunicationController::class, 'previewRecipients'])->name('preview-recipients');
        });
        Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\Panel\Admin\InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::post('invoices/{invoice}/send', [\App\Http\Controllers\Panel\Admin\InvoiceController::class, 'send'])->name('invoices.send');
        Route::resource('invoices', \App\Http\Controllers\Panel\Admin\InvoiceController::class);
        Route::get('invoices-editor', [\App\Http\Controllers\Admin\InvoiceEditorController::class, 'index'])->name('invoices.editor');
        Route::post('invoices-editor/save', [\App\Http\Controllers\Admin\InvoiceEditorController::class, 'save'])->name('invoices.editor.save');
        Route::get('invoices-editor/preview', [\App\Http\Controllers\Admin\InvoiceEditorController::class, 'preview'])->name('invoices.editor.preview');
        Route::post('invoices-editor/reset', [\App\Http\Controllers\Admin\InvoiceEditorController::class, 'resetDefaults'])->name('invoices.editor.reset');
        Route::resource('coupons', \App\Http\Controllers\Panel\Admin\CouponController::class);
        Route::match(['get', 'post'], 'courses/{course}/certificate/preview', [\App\Http\Controllers\Panel\Admin\CourseController::class, 'certificatePreview'])->name('courses.certificate.preview');
        Route::post('courses/{course}/lessons', [\App\Http\Controllers\LessonController::class, 'store'])->name('courses.lessons.store');
        Route::post('courses/{course}/lessons/content-image', [\App\Http\Controllers\LessonController::class, 'uploadContentImage'])->name('courses.lessons.content-image');
        Route::resource('courses', \App\Http\Controllers\Panel\Admin\CourseController::class);
        Route::post('courses/{course}/lessons/reorder', [\App\Http\Controllers\Panel\Admin\CourseController::class, 'reorderLessons'])->name('courses.lessons.reorder');
        Route::get('cms/{slug?}', [\App\Http\Controllers\Admin\CMSController::class, 'index'])->name('cms.index');
        Route::post('cms/{slug}', [\App\Http\Controllers\Admin\CMSController::class, 'update'])->name('cms.update');
        Route::resource('pages', \App\Http\Controllers\Panel\Admin\PageController::class);
        Route::post('pages/{page}/toggle-section', [\App\Http\Controllers\Panel\Admin\PageController::class, 'toggleSection'])->name('pages.toggle-section');
        Route::resource('faqs', \App\Http\Controllers\Panel\Admin\FaqController::class);
        Route::post('testimonials/{testimonial}/approve', [\App\Http\Controllers\Panel\Admin\TestimonialController::class, 'approve'])->name('testimonials.approve');
        Route::post('testimonials/{testimonial}/reject', [\App\Http\Controllers\Panel\Admin\TestimonialController::class, 'reject'])->name('testimonials.reject');
        Route::resource('testimonials', \App\Http\Controllers\Panel\Admin\TestimonialController::class);
        Route::get('logs', [\App\Http\Controllers\Panel\Admin\ActivityLogController::class, 'index'])->name('logs.index');
        Route::post('logs/clear', [\App\Http\Controllers\Panel\Admin\ActivityLogController::class, 'clear'])->name('logs.clear');
        Route::get('referrals', [\App\Http\Controllers\Panel\Admin\ReferralAnalyticsController::class, 'index'])->name('referrals.index');
        Route::get('referrals/export', [\App\Http\Controllers\Panel\Admin\ReferralAnalyticsController::class, 'export'])->name('referrals.export');
        Route::put('referrals/sandbox/{sandboxRequest}', [\App\Http\Controllers\Panel\Admin\ReferralAnalyticsController::class, 'updateSandboxRequest'])->name('referrals.sandbox.update');

        // Events
        Route::get('events/feed', [\App\Http\Controllers\Panel\Admin\EventController::class, 'feed'])->name('events.feed');
        Route::post('events/{event}/move', [\App\Http\Controllers\Panel\Admin\EventController::class, 'move'])->name('events.move');
        Route::get('events/list', [\App\Http\Controllers\Panel\Admin\EventController::class, 'list'])->name('events.list');
        Route::get('acervo', [\App\Http\Controllers\Panel\Admin\EventController::class, 'list'])->defaults('type', 'album')->name('events.acervo');
        Route::get('acervo/create', [\App\Http\Controllers\Panel\Admin\EventController::class, 'create'])->defaults('type', 'album')->name('acervo.create');
        Route::get('events/{event}/scanner', [\App\Http\Controllers\Panel\EventScannerController::class, 'index'])->name('events.scanner');
        Route::post('events/{event}/toggle-published', [\App\Http\Controllers\Panel\Admin\EventController::class, 'togglePublished'])->name('events.toggle-published');
        Route::post('events/{event}/toggle-field', [\App\Http\Controllers\Panel\Admin\EventController::class, 'toggleField'])->name('events.toggle-field');
        Route::post('events/{event}/set-cover', [\App\Http\Controllers\Panel\Admin\EventController::class, 'setCover'])->name('events.set-cover');
        Route::post('events/calendar/settings', [\App\Http\Controllers\Panel\Admin\EventController::class, 'updateCalendarSettings'])->name('events.calendar.settings');
        Route::get('events/{event}/exhibitors', [\App\Http\Controllers\Panel\Admin\EventExhibitorController::class, 'index'])->name('events.exhibitors.index');
        Route::post('events/{event}/exhibitors/settings', [\App\Http\Controllers\Panel\Admin\EventExhibitorController::class, 'settings'])->name('events.exhibitors.settings');
        Route::post('events/{event}/exhibitors/toggle', [\App\Http\Controllers\Panel\Admin\EventExhibitorController::class, 'toggle'])->name('events.exhibitors.toggle');
        Route::get('events/{event}/exhibitors/registrations', [\App\Http\Controllers\Panel\Admin\EventExhibitorController::class, 'registrations'])->name('events.exhibitors.registrations');
        Route::get('events/{event}/exhibitors/export', [\App\Http\Controllers\Panel\Admin\EventExhibitorController::class, 'export'])->name('events.exhibitors.export');
        Route::post('events/{event}/exhibitors/registrations/{registration}/confirm', [\App\Http\Controllers\Panel\Admin\EventExhibitorController::class, 'confirm'])->name('events.exhibitors.registrations.confirm');
        Route::post('events/{event}/exhibitors/registrations/{registration}/cancel', [\App\Http\Controllers\Panel\Admin\EventExhibitorController::class, 'cancel'])->name('events.exhibitors.registrations.cancel');
        Route::post('events/{event}/exhibitors/registrations/{registration}/refund', [\App\Http\Controllers\Panel\Admin\EventExhibitorController::class, 'refund'])->name('events.exhibitors.registrations.refund');
        Route::resource('events', \App\Http\Controllers\Panel\Admin\EventController::class);
        Route::post('events/{event}/media', [\App\Http\Controllers\Panel\EventMediaController::class, 'store'])->name('events.media.store');
        Route::delete('events/{event}/media/{media}', [\App\Http\Controllers\Panel\EventMediaController::class, 'destroy'])->name('events.media.destroy');

        // Mentorships
        Route::resource('mentorships', \App\Http\Controllers\Panel\Admin\MentorshipController::class);
        Route::post('mentorships/{mentorship}/media', [\App\Http\Controllers\Panel\MentorshipMediaController::class, 'store'])->name('mentorships.media.store');
        Route::delete('mentorships/{mentorship}/media/{media}', [\App\Http\Controllers\Panel\MentorshipMediaController::class, 'destroy'])->name('mentorships.media.destroy');

        // Revistas (digitais — flipbook)
        Route::resource('magazines', \App\Http\Controllers\Admin\MagazineController::class)->except(['show']);

        // Certificates
        Route::post('certificates/generate', [\App\Http\Controllers\Panel\Admin\CertificateController::class, 'generate'])->name('certificates.generate');
        Route::resource('certificates', \App\Http\Controllers\Panel\Admin\CertificateController::class);

        Route::resource('jobs', \App\Http\Controllers\Panel\Admin\JobController::class);
        Route::post('points-rules/exchange-settings', [\App\Http\Controllers\Panel\Admin\PointsRuleController::class, 'updateExchangeSettings'])->name('points-rules.exchange-settings');
        Route::resource('points-rules', \App\Http\Controllers\Panel\Admin\PointsRuleController::class);
        Route::get('marketplace/lojas', [\App\Http\Controllers\Panel\Admin\SellerStoreController::class, 'index'])->name('marketplace.stores.index');
        Route::post('marketplace/lojas/{store:id}/toggle', [\App\Http\Controllers\Panel\Admin\SellerStoreController::class, 'toggle'])->name('marketplace.stores.toggle');
        Route::get('marketplace/produtos', [\App\Http\Controllers\Panel\Admin\SellerProductController::class, 'index'])->name('marketplace.products.index');
        Route::post('marketplace/produtos/{product}/toggle', [\App\Http\Controllers\Panel\Admin\SellerProductController::class, 'toggle'])->name('marketplace.products.toggle');
        Route::post('redemptions/{redemption}/approve', [\App\Http\Controllers\Panel\Admin\RedemptionController::class, 'approve'])->name('redemptions.approve');
        Route::post('redemptions/{redemption}/ship', [\App\Http\Controllers\Panel\Admin\RedemptionController::class, 'ship'])->name('redemptions.ship');
        Route::post('redemptions/{redemption}/complete', [\App\Http\Controllers\Panel\Admin\RedemptionController::class, 'complete'])->name('redemptions.complete');
        Route::post('redemptions/{redemption}/cancel', [\App\Http\Controllers\Panel\Admin\RedemptionController::class, 'cancel'])->name('redemptions.cancel');
        Route::resource('redemptions', \App\Http\Controllers\Panel\Admin\RedemptionController::class);
        Route::get('fonts/active', [\App\Http\Controllers\Admin\CustomFontController::class, 'getActiveFonts'])->name('fonts.api.active');
        Route::resource('fonts', \App\Http\Controllers\Admin\CustomFontController::class);
        Route::get('ranking', [\App\Http\Controllers\Panel\Admin\RankingController::class, 'index'])->name('ranking.index');
        Route::post('quick-scanner/validate', [\App\Http\Controllers\Admin\QuickScannerController::class, 'validateTicket'])->name('quick-scanner.validate');

        // Mail Templates (Tailwind) - Panel Admin
        Route::get('mailtemplates/{mailtemplate}/preview', [\App\Http\Controllers\Admin\MailTemplateController::class, 'preview'])->name('mailtemplates.preview');
        Route::post('mailtemplates/{mailtemplate}/sendpreview', [\App\Http\Controllers\Admin\MailTemplateController::class, 'sendPreview'])->name('mailtemplates.sendpreview');
        Route::resource('mailtemplates', \App\Http\Controllers\Admin\MailTemplateController::class);

        // SumUp - Panel Admin
        Route::prefix('sumup')->name('sumup.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Admin\SumUpController::class, 'index'])->name('index');
            Route::get('/report', [\App\Http\Controllers\Panel\Admin\SumUpController::class, 'report'])->name('report');
            Route::get('/report/export', [\App\Http\Controllers\Panel\Admin\SumUpController::class, 'exportReport'])->name('report.export');
            Route::post('/test-connection', [\App\Http\Controllers\Panel\Admin\SumUpController::class, 'testConnection'])->name('test-connection');
            Route::get('/{sumupTransaction}', [\App\Http\Controllers\Panel\Admin\SumUpController::class, 'show'])->name('show');
            Route::post('/orders/{order}/refund', [\App\Http\Controllers\Panel\Admin\SumUpController::class, 'refund'])->name('refund');
        });
    });
});

// Marketplace (Público)
Route::get('/marketplace', [\App\Http\Controllers\MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/loja/carrinho', [\App\Http\Controllers\SellerProductCartController::class, 'show'])->name('seller-products.cart.show');
Route::post('/loja/produtos/{product}/carrinho', [\App\Http\Controllers\SellerProductCartController::class, 'store'])->name('seller-products.cart.add');
Route::post('/loja/carrinho', [\App\Http\Controllers\SellerProductCartController::class, 'update'])->name('seller-products.cart.update');
Route::post('/loja/carrinho/limpar', [\App\Http\Controllers\SellerProductCartController::class, 'clear'])->name('seller-products.cart.clear');
Route::get('/loja/checkout', [\App\Http\Controllers\SellerProductCheckoutController::class, 'show'])->name('seller-products.checkout.show');
Route::post('/loja/checkout', [\App\Http\Controllers\SellerProductCheckoutController::class, 'process'])->name('seller-products.checkout.process');
Route::get('/loja/{storeSlug}', [\App\Http\Controllers\SellerStorefrontController::class, 'show'])->name('seller-stores.show');
Route::get('/loja/{storeSlug}/p/{productSlug}', [\App\Http\Controllers\SellerStorefrontController::class, 'product'])->name('seller-stores.products.show');

// Share Product (Compartilhamento de produtos)
Route::get('/share/{code}', [\App\Http\Controllers\ShareController::class, 'product'])->name('share.product');

// Theme Toggle
Route::post('/theme/toggle', [\App\Http\Controllers\Panel\ThemeController::class, 'update'])->name('theme.toggle');

// Legacy Admin Routes (AdminLTE)
Route::prefix('admin')->name('admin.')->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class, \App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/settings/{group?}', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/toggle', [\App\Http\Controllers\Admin\SettingController::class, 'toggle'])->name('settings.toggle');
    Route::post('/settings/test-smtp', [\App\Http\Controllers\Admin\SettingController::class, 'testSmtp'])->name('settings.test-smtp');
    Route::post('/settings/test-gateway', [\App\Http\Controllers\Admin\SettingController::class, 'testGateway'])->name('settings.test_gateway');
    Route::post('/settings/test-s3', [\App\Http\Controllers\Admin\SettingController::class, 'testS3'])->name('settings.test-s3');
    Route::post('/settings/test-s3-provider', [\App\Http\Controllers\Admin\SettingController::class, 'testStorageProvider'])->name('settings.test-s3-provider');
    Route::post('/settings/upload', [\App\Http\Controllers\Admin\SettingController::class, 'uploadFile'])->name('settings.upload');
    Route::post('/settings/storage/migrate', [\App\Http\Controllers\Admin\SettingController::class, 'migrateStorage'])->name('settings.storage.migrate');
    Route::get('/settings/storage/folders', [\App\Http\Controllers\Admin\SettingController::class, 'storageFolders'])->name('settings.storage.folders');
    Route::get('/balance', [\App\Http\Controllers\Admin\DashboardController::class, 'getMpBalance'])->name('dashboard.balance');
    Route::get('/system-health', [\App\Http\Controllers\Admin\DashboardController::class, 'systemHealth'])->name('dashboard.system-health');
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/refund', [\App\Http\Controllers\Admin\OrderController::class, 'refund'])->name('orders.refund');
    Route::post('/orders/{order}/invoice', [\App\Http\Controllers\Panel\Admin\InvoiceController::class, 'issueForOrder'])->name('orders.invoice');
    Route::post('/orders/{order}/approve', [\App\Http\Controllers\Admin\OrderController::class, 'approveManually'])->name('orders.approve');
    Route::post('/orders/{order}/cancel', [\App\Http\Controllers\Admin\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/report/{format}', [\App\Http\Controllers\Admin\OrderController::class, 'exportReport'])->name('orders.report.export');

    Route::prefix('buyer-communication')->name('buyer-communication.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BuyerCommunicationController::class, 'index'])->name('index');
        Route::post('individual', [\App\Http\Controllers\Admin\BuyerCommunicationController::class, 'sendIndividual'])->name('individual');
        Route::post('bulk', [\App\Http\Controllers\Admin\BuyerCommunicationController::class, 'sendBulk'])->name('bulk');
        Route::get('search-users', [\App\Http\Controllers\Admin\BuyerCommunicationController::class, 'searchUsers'])->name('search-users');
        Route::get('get-items', [\App\Http\Controllers\Admin\BuyerCommunicationController::class, 'getItems'])->name('get-items');
        Route::get('preview-recipients', [\App\Http\Controllers\Admin\BuyerCommunicationController::class, 'previewRecipients'])->name('preview-recipients');
    });

    Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity_logs.index');
    Route::post('/activity-logs/clear', [\App\Http\Controllers\Admin\ActivityLogController::class, 'clear'])->name('activity_logs.clear');
    Route::get('/certificates', [\App\Http\Controllers\Admin\CertificateController::class, 'index'])->name('certificates.index');
    Route::post('/certificates/generate', [\App\Http\Controllers\Admin\CertificateController::class, 'generate'])->name('certificates.generate');
    Route::post('/certificates/{certificate}/regenerate', [\App\Http\Controllers\Admin\CertificateController::class, 'regenerate'])->name('certificates.regenerate');
    Route::post('/certificates/{certificate}/send', [\App\Http\Controllers\Admin\CertificateController::class, 'sendEmail'])->name('certificates.send');
    Route::delete('/certificates/{certificate}', [\App\Http\Controllers\Admin\CertificateController::class, 'destroy'])->name('certificates.destroy');
    Route::get('/certificates/{hash}', [\App\Http\Controllers\Admin\CertificateController::class, 'view'])->name('certificates.view');
    Route::get('/quick-scanner', [\App\Http\Controllers\Admin\QuickScannerController::class, 'index'])->name('quick-scanner');
    Route::post('/quick-scanner/validate', [\App\Http\Controllers\Admin\QuickScannerController::class, 'validateTicket'])->name('quick-scanner.validate');
    Route::get('events/feed', [\App\Http\Controllers\Admin\EventController::class, 'feed'])->name('events.feed');
    Route::get('events/list', [\App\Http\Controllers\Admin\EventController::class, 'list'])->name('events.list');
    Route::get('acervo', [\App\Http\Controllers\Admin\EventController::class, 'list'])->defaults('type', 'album')->name('events.acervo');
    Route::get('acervo/create', [\App\Http\Controllers\Admin\EventController::class, 'create'])->defaults('type', 'album')->name('acervo.create');
    Route::post('events/calendar/settings', [\App\Http\Controllers\Admin\EventController::class, 'updateCalendarSettings'])->name('events.calendar.settings');
    Route::get('events/{event}/scanner', [\App\Http\Controllers\Admin\EventScannerController::class, 'index'])->name('events.scanner');
    Route::post('events/{event}/scanner/validate', [\App\Http\Controllers\Admin\EventScannerController::class, 'validateTicket'])->name('events.scanner.validate');
    Route::post('events/{event}/toggle-field', [\App\Http\Controllers\Admin\EventController::class, 'toggleField'])->name('events.toggle-field');
    Route::post('events/{event}/toggle-published', [\App\Http\Controllers\Admin\EventController::class, 'togglePublished'])->name('events.toggle-published');
    Route::post('events/{event}/move', [\App\Http\Controllers\Admin\EventController::class, 'move'])->name('events.move');
    Route::post('events/{event}/set-cover', [\App\Http\Controllers\Admin\EventController::class, 'setCover'])->name('events.set-cover');
    Route::get('events/{event}/exhibitors', [\App\Http\Controllers\Admin\EventExhibitorController::class, 'index'])->name('events.exhibitors.index');
    Route::post('events/{event}/exhibitors/settings', [\App\Http\Controllers\Admin\EventExhibitorController::class, 'settings'])->name('events.exhibitors.settings');
    Route::post('events/{event}/exhibitors/toggle', [\App\Http\Controllers\Admin\EventExhibitorController::class, 'toggle'])->name('events.exhibitors.toggle');
    Route::get('events/{event}/exhibitors/registrations', [\App\Http\Controllers\Admin\EventExhibitorController::class, 'registrations'])->name('events.exhibitors.registrations');
    Route::get('events/{event}/exhibitors/export', [\App\Http\Controllers\Admin\EventExhibitorController::class, 'export'])->name('events.exhibitors.export');
    Route::post('events/{event}/exhibitors/registrations/{registration}/confirm', [\App\Http\Controllers\Admin\EventExhibitorController::class, 'confirm'])->name('events.exhibitors.registrations.confirm');
    Route::post('events/{event}/exhibitors/registrations/{registration}/cancel', [\App\Http\Controllers\Admin\EventExhibitorController::class, 'cancel'])->name('events.exhibitors.registrations.cancel');
    Route::post('events/{event}/exhibitors/registrations/{registration}/refund', [\App\Http\Controllers\Admin\EventExhibitorController::class, 'refund'])->name('events.exhibitors.registrations.refund');
    Route::post('events/{event}/media', [\App\Http\Controllers\Admin\EventMediaController::class, 'store'])->name('events.media.store');
    Route::delete('events/{event}/media/{media}', [\App\Http\Controllers\Admin\EventMediaController::class, 'destroy'])->name('events.media.destroy');
    Route::get('users/{user}/impersonate', [\App\Http\Controllers\Admin\ImpersonateController::class, 'impersonate'])->name('users.impersonate');
    Route::get('courses/available', [\App\Http\Controllers\Admin\CourseController::class, 'available'])->name('courses.available');
    Route::get('mentorships/available', [\App\Http\Controllers\Admin\MentorshipController::class, 'available'])->name('mentorships.available');
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MarketplaceController::class, 'index'])->name('index');
        Route::get('/payments', [\App\Http\Controllers\Admin\MarketplaceController::class, 'payments'])->name('payments');
        Route::get('/loja', [\App\Http\Controllers\Admin\MarketplaceStoreController::class, 'edit'])->name('store.edit');
        Route::post('/loja', [\App\Http\Controllers\Admin\MarketplaceStoreController::class, 'update'])->name('store.update');
        Route::get('/produtos', [\App\Http\Controllers\Admin\SellerProductController::class, 'index'])->name('products.index');
        Route::get('/produtos/novo', [\App\Http\Controllers\Admin\SellerProductController::class, 'create'])->name('products.create');
        Route::post('/produtos', [\App\Http\Controllers\Admin\SellerProductController::class, 'store'])->name('products.store');
        Route::get('/produtos/{product}/editar', [\App\Http\Controllers\Admin\SellerProductController::class, 'edit'])->name('products.edit');
        Route::put('/produtos/{product}', [\App\Http\Controllers\Admin\SellerProductController::class, 'update'])->name('products.update');
        Route::delete('/produtos/{product}', [\App\Http\Controllers\Admin\SellerProductController::class, 'destroy'])->name('products.destroy');
        Route::delete('/produtos/{product}/midia/{media}', [\App\Http\Controllers\Admin\SellerProductController::class, 'destroyMedia'])->name('products.media.destroy');
        Route::get('/pedidos', [\App\Http\Controllers\Admin\SellerOrderController::class, 'index'])->name('orders.index');
        Route::post('/pedidos/{order}/envio', [\App\Http\Controllers\Admin\SellerOrderController::class, 'updateShipment'])->name('orders.shipment.update');
        Route::get('/sales', [\App\Http\Controllers\Admin\MarketplaceController::class, 'sales'])->name('sales');
        Route::get('/lojas', [\App\Http\Controllers\Admin\MarketplaceController::class, 'stores'])->name('stores.index');
        Route::post('/lojas/{store:id}/toggle', [\App\Http\Controllers\Admin\MarketplaceController::class, 'toggleStore'])->name('stores.toggle');
        Route::get('/catalogo', [\App\Http\Controllers\Admin\MarketplaceController::class, 'catalog'])->name('catalog.index');
        Route::post('/catalogo/{product}/toggle', [\App\Http\Controllers\Admin\MarketplaceController::class, 'toggleCatalogProduct'])->name('catalog.toggle');
    });

    Route::get('splits', [\App\Http\Controllers\Admin\SplitController::class, 'index'])->name('splits.index');
    Route::post('splits/{split}/pay', [\App\Http\Controllers\Admin\SplitController::class, 'pay'])->name('splits.pay');

    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ChatController::class, 'index'])->name('index');
        Route::get('/start/{user}', [\App\Http\Controllers\Admin\ChatController::class, 'start'])->name('start');
        Route::get('/list', [\App\Http\Controllers\Admin\ChatController::class, 'list'])->name('list');
        Route::get('/{conversation}/messages', [\App\Http\Controllers\Admin\ChatController::class, 'getMessages'])->name('messages');
        Route::post('/{conversation}/message', [\App\Http\Controllers\Admin\ChatController::class, 'storeMessage'])->name('message.store');
        Route::get('/{conversation}', [\App\Http\Controllers\Admin\ChatController::class, 'show'])->name('show');
    });

    Route::get('social', [\App\Http\Controllers\Admin\SocialController::class, 'index'])->name('social.feed.internal');
    Route::delete('social/{post}', [\App\Http\Controllers\Admin\SocialController::class, 'destroy'])->name('social.destroy');

    Route::get('reviews', [\App\Http\Controllers\Admin\ItemReviewController::class, 'index'])->name('reviews.index');
    Route::post('reviews/{review}/approve', [\App\Http\Controllers\Admin\ItemReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('reviews/{review}/reject', [\App\Http\Controllers\Admin\ItemReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('reviews/{review}', [\App\Http\Controllers\Admin\ItemReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::post('testimonials/import-google', [\App\Http\Controllers\Admin\TestimonialController::class, 'importGoogle'])->name('testimonials.import-google');
    Route::post('testimonials/{testimonial}/toggle', [\App\Http\Controllers\Admin\TestimonialController::class, 'toggle'])->name('testimonials.toggle');
    Route::post('testimonials/{testimonial}/approve', [\App\Http\Controllers\Admin\TestimonialController::class, 'approve'])->name('testimonials.approve');
    Route::post('testimonials/{testimonial}/reject', [\App\Http\Controllers\Admin\TestimonialController::class, 'reject'])->name('testimonials.reject');

    Route::get('referrals', [\App\Http\Controllers\Admin\ReferralController::class, 'index'])->name('referrals.index');
    Route::get('referrals/export', [\App\Http\Controllers\Admin\ReferralController::class, 'export'])->name('referrals.export');
    Route::post('referrals/track', [\App\Http\Controllers\Admin\ReferralController::class, 'track'])->name('referrals.track');
    Route::post('referrals/tokens', [\App\Http\Controllers\Admin\ReferralController::class, 'storeToken'])->name('referrals.tokens.store');
    Route::put('referrals/tokens/{tokenId}', [\App\Http\Controllers\Admin\ReferralController::class, 'updateToken'])->name('referrals.tokens.update');
    Route::delete('referrals/tokens/{tokenId}', [\App\Http\Controllers\Admin\ReferralController::class, 'destroyToken'])->name('referrals.tokens.destroy');
    Route::put('referrals/sandbox/{sandboxRequest}', [\App\Http\Controllers\Admin\ReferralController::class, 'updateSandboxRequest'])->name('referrals.sandbox.update');

    // Traditional Admin Resources
    Route::post('users/{user}/marketing-manager', [\App\Http\Controllers\Admin\UserController::class, 'setMarketingManager'])->name('users.marketing-manager');
    Route::post('users/{user}/suspend', [\App\Http\Controllers\Admin\UserController::class, 'suspend'])->name('users.suspend');
    Route::post('users/{user}/unsuspend', [\App\Http\Controllers\Admin\UserController::class, 'unsuspend'])->name('users.unsuspend');
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::post('plans/{plan}/toggle-active', [\App\Http\Controllers\Admin\PlanController::class, 'toggleActive'])->name('plans.toggle-active');
    Route::post('plans/reorder', [\App\Http\Controllers\Admin\PlanController::class, 'reorder'])->name('plans.reorder');
    Route::resource('plans', \App\Http\Controllers\Admin\PlanController::class);
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)->except(['show']);
    Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class);
    Route::post('courses/{course}/lessons/content-image', [\App\Http\Controllers\LessonController::class, 'uploadContentImage'])->name('courses.lessons.content-image');
    Route::post('courses/{course}/lessons/reorder', [\App\Http\Controllers\Admin\CourseController::class, 'reorderLessons'])->name('courses.lessons.reorder');
    Route::delete('courses/{course}/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'destroy'])->name('courses.lessons.destroy');
    Route::resource('events', \App\Http\Controllers\Admin\EventController::class);
    Route::resource('mentorships', \App\Http\Controllers\Admin\MentorshipController::class);
    Route::resource('magazines', \App\Http\Controllers\Admin\MagazineController::class)->except(['show']);
    Route::get('invoices/editor', [\App\Http\Controllers\Admin\InvoiceEditorController::class, 'index'])->name('invoices.editor');
    Route::post('invoices/editor/save', [\App\Http\Controllers\Admin\InvoiceEditorController::class, 'save'])->name('invoices.editor.save');
    Route::get('invoices/editor/preview', [\App\Http\Controllers\Admin\InvoiceEditorController::class, 'preview'])->name('invoices.editor.preview');
    Route::post('invoices/editor/reset', [\App\Http\Controllers\Admin\InvoiceEditorController::class, 'resetDefaults'])->name('invoices.editor.reset');
    Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\Admin\InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/send', [\App\Http\Controllers\Admin\InvoiceController::class, 'send'])->name('invoices.send');
    Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class);
    Route::resource('jobs', \App\Http\Controllers\Admin\JobController::class)->except(['show']);
    Route::resource('faqs', \App\Http\Controllers\Admin\FaqController::class)->except(['show']);
    Route::resource('pages', \App\Http\Controllers\Admin\PageController::class)->only(['index', 'edit', 'update']);
    Route::post('pages/{page}/toggle-section', [\App\Http\Controllers\Admin\PageController::class, 'toggleSection'])->name('pages.toggle-section');
    Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class)->except(['show']);
    Route::post('points-rules/exchange-settings', [\App\Http\Controllers\Admin\PointsRuleController::class, 'updateExchangeSettings'])->name('points-rules.exchange-settings');
    Route::resource('points-rules', \App\Http\Controllers\Admin\PointsRuleController::class)->except(['show']);
    Route::post('redemptions/{redemption}/approve', [\App\Http\Controllers\Admin\RedemptionController::class, 'approve'])->name('redemptions.approve');
    Route::post('redemptions/{redemption}/cancel', [\App\Http\Controllers\Admin\RedemptionController::class, 'cancel'])->name('redemptions.cancel');
    Route::resource('redemptions', \App\Http\Controllers\Admin\RedemptionController::class)->except(['show', 'destroy']);
    Route::get('fonts/active', [\App\Http\Controllers\Admin\CustomFontController::class, 'getActiveFonts'])->name('fonts.api.active');
    Route::resource('fonts', \App\Http\Controllers\Admin\CustomFontController::class);
    Route::post('gallery/upload', [\App\Http\Controllers\Admin\GalleryController::class, 'upload'])->name('gallery.upload');
    Route::post('gallery/events/{event}/cover/upload', [\App\Http\Controllers\Admin\GalleryController::class, 'uploadCover'])->name('gallery.cover.upload');
    Route::post('gallery/media/{media}/cover', [\App\Http\Controllers\Admin\GalleryController::class, 'setCoverFromMedia'])->name('gallery.cover.media');
    Route::delete('gallery/events/{event}/cover', [\App\Http\Controllers\Admin\GalleryController::class, 'clearCover'])->name('gallery.cover.clear');
    Route::delete('gallery/{media}', [\App\Http\Controllers\Admin\GalleryController::class, 'destroy'])->name('gallery.destroy');
    Route::resource('gallery', \App\Http\Controllers\Admin\GalleryController::class)->except(['destroy']);
    Route::get('mailtemplates/{mailtemplate}/preview', [\App\Http\Controllers\Admin\MailTemplateController::class, 'preview'])->name('mailtemplates.preview');
    Route::post('mailtemplates/{mailtemplate}/sendpreview', [\App\Http\Controllers\Admin\MailTemplateController::class, 'sendPreview'])->name('mailtemplates.sendpreview');
    Route::resource('mailtemplates', \App\Http\Controllers\Admin\MailTemplateController::class)->except(['show']);
    Route::resource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class)->except(['show']);

    // CMS
    Route::get('cms/{slug?}', [\App\Http\Controllers\Admin\CMSController::class, 'index'])->name('cms.index');
    Route::post('cms/{slug}', [\App\Http\Controllers\Admin\CMSController::class, 'update'])->name('cms.update');

    // Mail Test
    Route::get('mailtest', [\App\Http\Controllers\MailTestController::class, 'showForm'])->name('mailtest.index');
    Route::post('mailtest/send', [\App\Http\Controllers\MailTestController::class, 'sendTest'])->name('mailtest.send');

    // Media Routes (Standardized)
    Route::post('events/{event}/media', [\App\Http\Controllers\Admin\EventMediaController::class, 'store'])->name('events.media.store');
    Route::delete('events/{event}/media/{media}', [\App\Http\Controllers\Admin\EventMediaController::class, 'destroy'])->name('events.media.destroy');
    Route::post('mentorships/{mentorship}/media', [\App\Http\Controllers\Panel\MentorshipMediaController::class, 'store'])->name('mentorships.media.store');
    Route::delete('mentorships/{mentorship}/media/{media}', [\App\Http\Controllers\Panel\MentorshipMediaController::class, 'destroy'])->name('mentorships.media.destroy');

    // Punishments (Gestao de Punicoes)
    Route::get('punishments', [\App\Http\Controllers\Admin\PunishmentController::class, 'index'])->name('punishments.index');
    Route::get('punishments/settings', [\App\Http\Controllers\Admin\PunishmentController::class, 'settings'])->name('punishments.settings');
    Route::put('punishments/settings', [\App\Http\Controllers\Admin\PunishmentController::class, 'updateSettings'])->name('punishments.settings.update');
    Route::get('punishments/{user}', [\App\Http\Controllers\Admin\PunishmentController::class, 'show'])->name('punishments.show');
    Route::post('punishments/apply', [\App\Http\Controllers\Admin\PunishmentController::class, 'apply'])->name('punishments.apply');
    Route::post('punishments/{user}/remove', [\App\Http\Controllers\Admin\PunishmentController::class, 'remove'])->name('punishments.remove');
    Route::put('punishments/{user}/edit', [\App\Http\Controllers\Admin\PunishmentController::class, 'edit'])->name('punishments.edit');
});

// Checkout process
Route::post('/checkout/process-payment', [\App\Http\Controllers\CheckoutController::class, 'processPayment'])->name('checkout.process_payment');
Route::post('/checkout/sumup/pix', [\App\Http\Controllers\CheckoutController::class, 'sumupPix'])->name('checkout.sumup.pix');
Route::get('/checkout/sumup/status', [\App\Http\Controllers\CheckoutController::class, 'sumupStatus'])->name('checkout.sumup.status');
Route::post('/checkout/sumup/recreate', [\App\Http\Controllers\CheckoutController::class, 'sumupRecreateCheckout'])->name('checkout.sumup.recreate');
Route::post('/checkout/{course}', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/{course}', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout.show');
Route::get('/checkout/sucesso/{order}', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/pendente/{order}', [\App\Http\Controllers\CheckoutController::class, 'pending'])->name('checkout.pending');
Route::get('/checkout/falha/{order}', [\App\Http\Controllers\CheckoutController::class, 'failure'])->name('checkout.failure');

// Impersonate Stop
Route::get('/admin/stop-impersonating', [\App\Http\Controllers\Admin\ImpersonateController::class, 'stop'])->middleware(['auth'])->name('admin.impersonate.stop');

// Email Verification
Route::get('/email/verify', [App\Http\Controllers\Auth\EmailVerificationController::class, '__invoke'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [App\Http\Controllers\Auth\EmailVerificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::prefix('admin')->name('admin.')->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/security', [\App\Http\Controllers\Admin\Waf\WafSettingsController::class, 'index'])->name('security');
    Route::put('/security', [\App\Http\Controllers\Admin\Waf\WafSettingsController::class, 'update'])->name('security.update');
});

/*
|--------------------------------------------------------------------------
| WAF Panel Routes (Superadmin only - AdminLTE)
|--------------------------------------------------------------------------
| Spec: .kiro/specs/waf-e-auditoria-seguranca
| Requisitos: 13.1, 13.2
*/
Route::prefix('admin/waf')->name('admin.waf.')->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\Admin\Waf\WafDashboardController::class, 'index'])->name('dashboard');
    Route::get('/data', [\App\Http\Controllers\Admin\Waf\WafDashboardController::class, 'data'])->name('data');
    Route::post('/mode', [\App\Http\Controllers\Admin\Waf\WafDashboardController::class, 'toggleMode'])->name('mode');

    // Events
    Route::get('/events', [\App\Http\Controllers\Admin\Waf\WafEventsController::class, 'index'])->name('events.index');
    Route::get('/events/export', [\App\Http\Controllers\Admin\Waf\WafEventsController::class, 'export'])->name('events.export');
    Route::get('/events/{id}', [\App\Http\Controllers\Admin\Waf\WafEventsController::class, 'show'])->name('events.show');
    Route::post('/events/{id}/false-positive', [\App\Http\Controllers\Admin\Waf\WafEventsController::class, 'markFalsePositive'])->name('events.false-positive');
    Route::post('/events/{id}/block-ip', [\App\Http\Controllers\Admin\Waf\WafEventsController::class, 'blockIp'])->name('events.block-ip');
    Route::post('/events/{id}/allow-ip', [\App\Http\Controllers\Admin\Waf\WafEventsController::class, 'allowIp'])->name('events.allow-ip');

    // Rules
    Route::resource('/rules', \App\Http\Controllers\Admin\Waf\WafRulesController::class)->names('rules');
    Route::post('/rules/{id}/toggle', [\App\Http\Controllers\Admin\Waf\WafRulesController::class, 'toggle'])->name('rules.toggle');
    Route::post('/rules/test', [\App\Http\Controllers\Admin\Waf\WafRulesController::class, 'test'])->name('rules.test');
    Route::get('/rules-export', [\App\Http\Controllers\Admin\Waf\WafRulesController::class, 'exportAll'])->name('rules.export');
    Route::post('/rules-import', [\App\Http\Controllers\Admin\Waf\WafRulesController::class, 'import'])->name('rules.import');

    // IP Lists
    Route::get('/blocklist', [\App\Http\Controllers\Admin\Waf\WafIpListController::class, 'blocklist'])->name('blocklist.index');
    Route::post('/blocklist', [\App\Http\Controllers\Admin\Waf\WafIpListController::class, 'storeBlock'])->name('blocklist.store');
    Route::delete('/blocklist/{id}', [\App\Http\Controllers\Admin\Waf\WafIpListController::class, 'destroyBlock'])->name('blocklist.destroy');
    Route::get('/allowlist', [\App\Http\Controllers\Admin\Waf\WafIpListController::class, 'allowlist'])->name('allowlist.index');
    Route::post('/allowlist', [\App\Http\Controllers\Admin\Waf\WafIpListController::class, 'storeAllow'])->name('allowlist.store');
    Route::delete('/allowlist/{id}', [\App\Http\Controllers\Admin\Waf\WafIpListController::class, 'destroyAllow'])->name('allowlist.destroy');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\Waf\WafSettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [\App\Http\Controllers\Admin\Waf\WafSettingsController::class, 'update'])->name('settings.update');

    // Alerts
    Route::get('/alerts', [\App\Http\Controllers\Admin\Waf\WafAlertsController::class, 'index'])->name('alerts.index');
    Route::post('/alerts', [\App\Http\Controllers\Admin\Waf\WafAlertsController::class, 'store'])->name('alerts.store');
    Route::put('/alerts/{id}', [\App\Http\Controllers\Admin\Waf\WafAlertsController::class, 'update'])->name('alerts.update');
    Route::delete('/alerts/{id}', [\App\Http\Controllers\Admin\Waf\WafAlertsController::class, 'destroy'])->name('alerts.destroy');
});
