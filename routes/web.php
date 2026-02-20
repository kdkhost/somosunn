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
});
// Rota para checkout de assinatura (compatível com premium.blade.php) — sempre no início para garantir visibilidade
Route::get('/assinar/{plan}', [\App\Http\Controllers\SubscriptionController::class, 'checkout'])->name('subscription.checkout');
Route::post('/assinar/{plan}', [\App\Http\Controllers\SubscriptionController::class, 'process'])->name('subscription.process');
Route::get('/assinatura/sucesso/{order}', [\App\Http\Controllers\SubscriptionController::class, 'success'])->name('subscription.success');

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\MailTestController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\SatisfactionController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/portal', [HomeController::class, 'portal'])->name('portal');
Route::get('/premium', [HomeController::class, 'premium'])->name('premium');
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


    });
});






// Institutional Pages
Route::get('/sobre', fn() => view('site.institucional.sobre'))->name('sobre');
Route::get('/manifesto', fn() => view('site.institucional.manifesto'))->name('manifesto');
Route::get('/quem-somos', fn() => view('site.institucional.quem-somos'))->name('quem-somos');
Route::get('/como-funciona', fn() => view('site.institucional.como-funciona'))->name('como-funciona');
Route::get('/valores', fn() => view('site.institucional.valores'))->name('valores');
Route::get('/contato', fn() => view('site.institucional.contato'))->name('contato');
Route::post('/contato', [ContactController::class, 'send'])->middleware('throttle:5,1')->name('contato.send');

// Members
Route::get('/membros', [\App\Http\Controllers\MemberController::class, 'index'])->name('membros');

// Eventos (público: vitrine/SEO; compra/reserva controla acesso por pedido/inscrição)
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
Route::get('/eventos/pagamento/sucesso/{order}', [\App\Http\Controllers\EventReservationController::class, 'paymentSuccess'])
    ->name('events.payment.success');
Route::get('/eventos/pagamento/pendente/{order}', [\App\Http\Controllers\EventReservationController::class, 'paymentPending'])
    ->name('events.payment.pending');
Route::get('/eventos/pagamento/falha/{order}', [\App\Http\Controllers\EventReservationController::class, 'paymentFailure'])
    ->name('events.payment.failure');

// PWA static files to avoid 404 in production
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

Route::get('/post/{post}', [\App\Http\Controllers\SocialController::class, 'publicPost'])
    ->name('social.post.public');

// Auth scaffold (simplificado)
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'authenticate']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/register', fn() => view('auth.register'))->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'store']);

// Password reset
Route::get('/password/forgot', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

// Social Auth
Route::get('/auth/redirect/{provider}', [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/callback/{provider}', [\App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])->name('social.callback');

// File uploads
Route::post('/upload', [\App\Http\Controllers\UploadController::class, 'upload'])->name('upload.file');

// Webhooks and payments endpoints (placeholders)
Route::post('/webhook/mercadopago', [\App\Http\Controllers\PaymentWebhookController::class, 'mercadopago'])
    ->defaults('seller_id', 'platform');
Route::post('/webhook/pagseguro', [\App\Http\Controllers\PaymentWebhookController::class, 'pagSeguro']);

// Installer
Route::get('/install', [\App\Http\Controllers\InstallController::class, 'index'])->name('install.index');
Route::post('/install/run', [\App\Http\Controllers\InstallController::class, 'run'])->name('install.run');
Route::post('/install/test-connection', [\App\Http\Controllers\InstallController::class, 'testConnection'])->name('install.test-connection');
Route::get('/install/run', fn() => redirect()->route('install.index'));
Route::get('/backend/install', [\App\Http\Controllers\InstallController::class, 'index'])->name('install.index.legacy');
Route::post('/backend/install/run', [\App\Http\Controllers\InstallController::class, 'run'])->name('install.run.legacy');
Route::post('/backend/install/test-connection', [\App\Http\Controllers\InstallController::class, 'testConnection'])->name('install.test-connection.legacy');
Route::get('/backend/install/run', fn() => redirect()->route('install.index.legacy'));

Route::post('/api/interactions', [InteractionController::class, 'store'])->name('api.interactions.store');
Route::post('/api/satisfactions', [SatisfactionController::class, 'store'])->name('api.satisfactions.store');
Route::get('/api/ranking', [RankingController::class, 'index'])->name('api.ranking.index');

// PWA manifest (dynamic)
Route::get('/manifest.webmanifest', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('manifest');
Route::get('/offline', fn() => view('offline'))->name('offline');

// Sitemap & Robots
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    return response("User-agent: *\nAllow: /\nSitemap: " . url('sitemap.xml'), 200, ['Content-Type' => 'text/plain']);
});



// Cursos (público: vitrine/SEO; acesso às aulas continua restrito por permissões/compra)
Route::get('courses', [\App\Http\Controllers\CourseController::class, 'index'])->name('courses.index');
Route::get('courses/{course}', [\App\Http\Controllers\CourseController::class, 'show'])->name('courses.show');
Route::middleware(['auth', 'check.feature:courses_create'])->group(function () {
    Route::get('courses/create', [\App\Http\Controllers\CourseController::class, 'create'])->name('courses.create');
    Route::post('courses', [\App\Http\Controllers\CourseController::class, 'store'])->name('courses.store');
});
Route::middleware(['auth', 'check.feature:courses_edit'])->group(function () {
    Route::get('courses/{course}/edit', [\App\Http\Controllers\CourseController::class, 'edit'])->name('courses.edit');
    Route::put('courses/{course}', [\App\Http\Controllers\CourseController::class, 'update'])->name('courses.update');
});
Route::middleware(['auth', 'check.feature:courses_delete'])->group(function () {
    Route::delete('courses/{course}', [\App\Http\Controllers\CourseController::class, 'destroy'])->name('courses.destroy');
    Route::post('courses/{course}/complete', [\App\Http\Controllers\CourseController::class, 'complete'])->name('courses.complete');
});

// Mentorias (público: vitrine/SEO; contratação/agenda continua restrita)
Route::get('mentorships', [\App\Http\Controllers\MentorshipController::class, 'index'])->name('mentorships.index');
Route::get('mentorships/{mentorship}', [\App\Http\Controllers\MentorshipController::class, 'show'])->name('mentorships.show');
Route::middleware(['auth', 'check.feature:mentorships_create'])->group(function () {
    Route::get('mentorships/create', [\App\Http\Controllers\MentorshipController::class, 'create'])->name('mentorships.create');
    Route::post('mentorships', [\App\Http\Controllers\MentorshipController::class, 'store'])->name('mentorships.store');
});
Route::middleware(['auth', 'check.feature:mentorships_edit'])->group(function () {
    Route::get('mentorships/{mentorship}/edit', [\App\Http\Controllers\MentorshipController::class, 'edit'])->name('mentorships.edit');
    Route::put('mentorships/{mentorship}', [\App\Http\Controllers\MentorshipController::class, 'update'])->name('mentorships.update');
});
Route::middleware(['auth', 'check.feature:mentorships_delete'])->group(function () {
    Route::delete('mentorships/{mentorship}', [\App\Http\Controllers\MentorshipController::class, 'destroy'])->name('mentorships.destroy');
});

// Reviews (granular)
Route::post('courses/{course}/reviews', [\App\Http\Controllers\ItemReviewController::class, 'storeCourse'])
    ->middleware(['auth', 'check.feature:courses_review'])
    ->name('courses.reviews.store');
Route::post('mentorships/{mentorship}/reviews', [\App\Http\Controllers\ItemReviewController::class, 'storeMentorship'])
    ->middleware(['auth', 'check.feature:mentorships_review'])
    ->name('mentorships.reviews.store');

// Lessons (granular)
Route::post('courses/{course}/lessons', [\App\Http\Controllers\LessonController::class, 'store'])
    ->middleware(['auth', 'check.feature:courses_lessons_create'])
    ->name('courses.lessons.store');
Route::put('courses/{course}/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'update'])
    ->middleware(['auth', 'check.feature:courses_lessons_edit'])
    ->name('courses.lessons.update');
Route::delete('courses/{course}/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'destroy'])
    ->middleware(['auth', 'check.feature:courses_lessons_delete'])
    ->name('courses.lessons.destroy');
Route::get('courses/{course}/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'show'])
    ->middleware(['check.feature:courses_lessons_access'])
    ->name('courses.lessons.show');

// Attachments (granular)
Route::post('courses/{course}/lessons/{lesson}/attachments', [\App\Http\Controllers\LessonController::class, 'uploadAttachment'])
    ->middleware(['auth', 'check.feature:courses_lessons_attachments_upload'])
    ->name('courses.lessons.attachments.upload');
Route::get('courses/{course}/lessons/{lesson}/attachments/{attachment}/download', [\App\Http\Controllers\LessonController::class, 'downloadAttachment'])
    ->middleware(['auth', 'check.feature:courses_lessons_attachments_download'])
    ->name('courses.lessons.attachments.download');
Route::delete('courses/{course}/lessons/{lesson}/attachments/{attachment}', [\App\Http\Controllers\LessonController::class, 'deleteAttachment'])
    ->middleware(['auth', 'check.feature:courses_lessons_attachments_delete'])
    ->name('courses.lessons.attachments.destroy');
Route::put('courses/{course}/lessons/{lesson}/attachments/{attachment}', [\App\Http\Controllers\LessonController::class, 'renameAttachment'])
    ->middleware(['auth', 'check.feature:courses_lessons_attachments_edit'])
    ->name('courses.lessons.attachments.rename');
Route::get('courses/{course}/lessons/{lesson}/details', [\App\Http\Controllers\LessonController::class, 'getDetails'])
    ->middleware(['auth', 'check.feature:courses_lessons_access'])
    ->name('courses.lessons.details');
Route::middleware('auth')->group(function () {
    Route::post('courses/{course}/lessons/{lesson}/progress', [\App\Http\Controllers\LessonController::class, 'updatePlaybackProgress'])
        ->middleware('check.feature:courses_lessons_access')
        ->name('courses.lessons.progress.update');
    Route::post('courses/{course}/lessons/{lesson}/bookmarks', [\App\Http\Controllers\LessonController::class, 'storeBookmark'])
        ->middleware('check.feature:courses_lessons_access')
        ->name('courses.lessons.bookmarks.store');
    Route::delete('courses/{course}/lessons/{lesson}/bookmarks/{bookmark}', [\App\Http\Controllers\LessonController::class, 'destroyBookmark'])
        ->middleware('check.feature:courses_lessons_access')
        ->name('courses.lessons.bookmarks.destroy');
});

// (events.show/events.index defined above as public routes)


// Gateway OAuth Routes
Route::middleware(['auth'])->prefix('gateway/mercadopago')->name('gateway.mercadopago.')->group(function () {
    Route::get('connect', [\App\Http\Controllers\GatewayAccountController::class, 'connect'])->name('connect');
    Route::get('callback', [\App\Http\Controllers\GatewayAccountController::class, 'callback'])->name('callback');
});

// Auth Required Routes
Route::middleware(['auth', 'check.plan'])->group(function () {

    // Social / Community (Feature: community)
    Route::middleware(['check.feature:community'])->group(function () {
        Route::get('/feed', [\App\Http\Controllers\SocialController::class, 'feed'])->name('social.feed');
        Route::get('/profile/{username}', [\App\Http\Controllers\SocialController::class, 'profile'])->name('social.profile');

        // Connections
        Route::post('/connect/{user}', [\App\Http\Controllers\ConnectionController::class, 'connect'])->name('connection.connect');
        Route::post('/connection/accept/{user}', [\App\Http\Controllers\ConnectionController::class, 'accept'])->name('connection.accept');
        Route::post('/connection/remove/{user}', [\App\Http\Controllers\ConnectionController::class, 'remove'])->name('connection.remove');
        Route::post('/connection/block/{user}', [\App\Http\Controllers\ConnectionController::class, 'block'])->name('connection.block');
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
    });

    // Chat (Feature: chat)
    Route::middleware(['check.feature:chat', 'check.connection'])->group(function () {
        Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/start/{user}', [\App\Http\Controllers\ChatController::class, 'start'])->name('chat.start');
        Route::get('/chat/list', [\App\Http\Controllers\ChatController::class, 'list'])->name('chat.list');
        Route::get('/chat/{conversation}/messages', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('/chat/{conversation}/message', [\App\Http\Controllers\ChatController::class, 'storeMessage'])->name('chat.message.store');
        Route::get('/chat/{conversation}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');

        // Floating Chat Routes
        Route::get('/chat/with/{user}', [\App\Http\Controllers\ChatController::class, 'withUser'])->name('chat.with.user');
        Route::post('/chat/with/{user}/message', [\App\Http\Controllers\ChatController::class, 'storeMessageWithUser'])->name('chat.with.user.message');

        // Notification Hub
        Route::get('/api/notifications/hub', [\App\Http\Controllers\NotificationHubController::class, 'index'])->name('notifications.hub');

        // General Notification Panel
        Route::get('/notificacoes', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notificacoes/read/{id?}', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markRead');
        Route::delete('/notificacoes/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    });
});

// Painel do Membro (novo - layout do front-end)
Route::prefix('painel')->name('panel.')->middleware(['auth', 'check.plan'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Panel\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [\App\Http\Controllers\Panel\DashboardController::class, 'stats'])->name('dashboard.stats');

    // Admin Routes within Panel (Tailwind)
    // PROTECTED: Only Admins can access these routes
    Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Settings
        Route::get('/settings/{group?}', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-smtp', [\App\Http\Controllers\Admin\SettingController::class, 'testSmtp'])->name('settings.test-smtp');
        Route::post('/settings/test-gateway', [\App\Http\Controllers\Admin\SettingController::class, 'testGateway'])->name('settings.test-gateway');

        // Mail Templates
        Route::get('/mailtemplates', [\App\Http\Controllers\Admin\MailTemplateController::class, 'index'])->name('mailtemplates.index');
        Route::get('/mailtemplates/create', [\App\Http\Controllers\Admin\MailTemplateController::class, 'create'])->name('mailtemplates.create');
        Route::post('/mailtemplates', [\App\Http\Controllers\Admin\MailTemplateController::class, 'store'])->name('mailtemplates.store');
        Route::get('/mailtemplates/{mailtemplate}/edit', [\App\Http\Controllers\Admin\MailTemplateController::class, 'edit'])->name('mailtemplates.edit');
        Route::put('/mailtemplates/{mailtemplate}', [\App\Http\Controllers\Admin\MailTemplateController::class, 'update'])->name('mailtemplates.update');
        Route::delete('/mailtemplates/{mailtemplate}', [\App\Http\Controllers\Admin\MailTemplateController::class, 'destroy'])->name('mailtemplates.destroy');
        Route::get('/mailtemplates/{mailtemplate}/preview', [\App\Http\Controllers\Admin\MailTemplateController::class, 'preview'])->name('mailtemplates.preview');
        Route::post('/mailtemplates/{mailtemplate}/send-preview', [\App\Http\Controllers\Admin\MailTemplateController::class, 'sendPreview'])->name('mailtemplates.sendpreview');

        // Users Management
        Route::resource('users', \App\Http\Controllers\Panel\Admin\UserController::class);

        // Plans Management
        Route::post('plans/{plan}/toggle-active', [\App\Http\Controllers\Panel\Admin\PlanController::class, 'toggleActive'])->name('plans.toggle-active');
        Route::resource('plans', \App\Http\Controllers\Panel\Admin\PlanController::class);

        // Orders Management
        Route::post('orders/{order}/refund', [\App\Http\Controllers\Panel\Admin\OrderController::class, 'refund'])->name('orders.refund');
        Route::resource('orders', \App\Http\Controllers\Panel\Admin\OrderController::class)->only(['index', 'show']);
        Route::post('orders/{order}/invoice', [\App\Http\Controllers\Panel\Admin\InvoiceController::class, 'issueForOrder'])->name('orders.invoice');

        // Invoices Management
        Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\Panel\Admin\InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::post('invoices/{invoice}/send', [\App\Http\Controllers\Panel\Admin\InvoiceController::class, 'send'])->name('invoices.send');
        Route::resource('invoices', \App\Http\Controllers\Panel\Admin\InvoiceController::class);

        // Coupons Management
        Route::resource('coupons', \App\Http\Controllers\Panel\Admin\CouponController::class);

        // Courses Management
        Route::resource('courses', \App\Http\Controllers\Panel\Admin\CourseController::class);
        Route::post('courses/{course}/lessons/reorder', [\App\Http\Controllers\Panel\Admin\CourseController::class, 'reorderLessons'])->name('courses.lessons.reorder');
        // Mentorships Management
        Route::resource('mentorships', \App\Http\Controllers\Panel\Admin\MentorshipController::class);

        // Events Management
        Route::resource('events', \App\Http\Controllers\Panel\Admin\EventController::class);

        // Certificates Management
        Route::resource('certificates', \App\Http\Controllers\Panel\Admin\CertificateController::class);

        // CMS & Engagement (Phase 4)
        Route::resource('faqs', \App\Http\Controllers\Panel\Admin\FaqController::class);
        Route::resource('testimonials', \App\Http\Controllers\Panel\Admin\TestimonialController::class);
        Route::post('testimonials/{testimonial}/approve', [\App\Http\Controllers\Panel\Admin\TestimonialController::class, 'approve'])->name('testimonials.approve');
        Route::post('testimonials/{testimonial}/reject', [\App\Http\Controllers\Panel\Admin\TestimonialController::class, 'reject'])->name('testimonials.reject');

        Route::group(['prefix' => 'cms', 'as' => 'cms.'], function () {
            Route::get('/', [\App\Http\Controllers\Panel\Admin\CMSController::class, 'index'])->name('index');
            Route::post('/{slug}', [\App\Http\Controllers\Panel\Admin\CMSController::class, 'update'])->name('update');
        });

        Route::get('logs', [\App\Http\Controllers\Panel\Admin\ActivityLogController::class, 'index'])->name('logs.index');
        Route::post('logs/clear', [\App\Http\Controllers\Panel\Admin\ActivityLogController::class, 'clear'])->name('logs.clear');

        // Engagement Tools
        Route::resource('points-rules', \App\Http\Controllers\Panel\Admin\PointsRuleController::class);
        Route::get('ranking', [\App\Http\Controllers\Panel\Admin\RankingController::class, 'index'])->name('ranking.index');

        Route::prefix('courses/{course}')->name('courses.')->group(function () {
            Route::post('lessons', [\App\Http\Controllers\LessonController::class, 'store'])->name('lessons.store');
            Route::prefix('lessons/{lesson}')->name('lessons.')->group(function () {
                Route::put('/', [\App\Http\Controllers\LessonController::class, 'update'])->name('update');
                Route::delete('/', [\App\Http\Controllers\LessonController::class, 'destroy'])->name('destroy');
                Route::get('details', [\App\Http\Controllers\LessonController::class, 'getDetails'])->name('details');

                // Attachments
                Route::post('attachments', [\App\Http\Controllers\LessonController::class, 'uploadAttachment'])->name('attachments.store');
                Route::put('attachments/{attachment}', [\App\Http\Controllers\LessonController::class, 'renameAttachment'])->name('attachments.rename');
                Route::delete('attachments/{attachment}', [\App\Http\Controllers\LessonController::class, 'deleteAttachment'])->name('attachments.destroy');
            });

            // Preview de Certificado
            Route::post('certificate/preview', [\App\Http\Controllers\Panel\Admin\CourseController::class, 'certificatePreview'])->name('certificate.preview');
        });
    });

    // Preferência de Tema
    Route::post('/theme/toggle', [\App\Http\Controllers\Panel\ThemeController::class, 'update'])->name('theme.toggle');

    // Perfil (completo) - permitido mesmo sem plano ativo (whitelist no middleware)
    Route::get('/perfil', [\App\Http\Controllers\Panel\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/perfil', [\App\Http\Controllers\Panel\ProfileController::class, 'update'])->name('profile.update');

    // Certificados (Aluno)
    Route::get('/certificados', [\App\Http\Controllers\Panel\CertificateController::class, 'index'])->name('certificates.index');
    Route::post('/certificados/gerar', [\App\Http\Controllers\Panel\CertificateController::class, 'generate'])->name('certificates.generate');
    // Note: show/download routes might be missing or exist elsewhere. Let's check Controller methods.
    // Panel\CertificateController has show/download. We need routes for them too.
    Route::get('/certificados/{certificate}', [\App\Http\Controllers\Panel\CertificateController::class, 'show'])->name('certificates.show');
    Route::get('/certificados/{certificate}/download', [\App\Http\Controllers\Panel\CertificateController::class, 'download'])->name('certificates.download');

    // Marketplace (Painel do vendedor)
    Route::prefix('marketplace')->name('marketplace.')->middleware('check.marketplace.seller')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\MarketplaceController::class, 'index'])->name('index');
        Route::get('/pagamentos', [\App\Http\Controllers\Panel\MarketplaceController::class, 'payments'])->name('payments');
        Route::get('/pagamentos/configurar', [\App\Http\Controllers\Panel\MarketplaceController::class, 'editPayment'])->name('payments.edit');
        Route::post('/pagamentos/testar', [\App\Http\Controllers\Panel\MarketplaceController::class, 'testCredentials'])->name('payments.test');
        Route::get('/vendas', [\App\Http\Controllers\Panel\MarketplaceController::class, 'sales'])->name('sales');
    });

    // Wishlist
    Route::get('/minha-lista', [\App\Http\Controllers\Panel\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/minha-lista/toggle/{course}', [\App\Http\Controllers\Panel\WishlistController::class, 'toggle'])->name('wishlist.toggle');
});

// Payments (legacy) - redireciona para o painel do marketplace (admin)
Route::middleware(['auth'])->group(function () {
    Route::get('/settings/payment', function () {
        return redirect()->route('panel.marketplace.payments');
    })->name('settings.payment');
    Route::post('/settings/payment', [\App\Http\Controllers\GatewayAccountController::class, 'update'])->name('settings.payment.update');
});

Route::get('/checkout/{course}', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{course}', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success/{order}', fn() => view('checkout.success'))->name('checkout.success');
Route::get('/checkout/failure/{order}', fn() => view('checkout.failure'))->name('checkout.failure');
Route::get('/checkout/pending/{order}', fn() => view('checkout.pending'))->name('checkout.pending');
Route::post('/checkout/process-payment', [\App\Http\Controllers\CheckoutController::class, 'processPayment'])->name('checkout.process_payment');

Route::get('/checkout/mentorships/{mentorship}', [\App\Http\Controllers\MentorshipCheckoutController::class, 'show'])->name('mentorships.checkout.show');
Route::post('/checkout/mentorships/{mentorship}', [\App\Http\Controllers\MentorshipCheckoutController::class, 'process'])->name('mentorships.checkout.process');

Route::post('/webhook/mercadopago/{seller_id}', [\App\Http\Controllers\PaymentWebhookController::class, 'mercadopago'])->name('webhook.mercadopago');

// Short links (SEO/OG para compartilhamento)
Route::get('/p/{code}', [\App\Http\Controllers\ShareController::class, 'product'])->name('share.product');

// Marketplace (público)
Route::get('/marketplace', [\App\Http\Controllers\MarketplaceController::class, 'index'])->name('marketplace.index');

// Marketplace (legado: vendas) - vendedores agora tratam tudo via painel admin
Route::middleware(['auth'])->group(function () {
    Route::get('/marketplace/vendas', function () {
        return redirect()->route('panel.marketplace.sales');
    })->name('marketplace.sales');
});

// Impersonate Stop (sempre acessível quando estiver impersonando)
Route::get('/admin/stop-impersonating', [\App\Http\Controllers\Admin\ImpersonateController::class, 'stop'])
    ->middleware(['auth'])
    ->name('admin.impersonate.stop');

// Admin routes (granular)
Route::prefix('admin')->name('admin.')->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class, \App\Http\Middleware\EnsureUserIsAdmin::class, 'check.plan'])->group(function () {
    Route::post('/settings/upload', [\App\Http\Controllers\Admin\SettingController::class, 'uploadFile'])->name('settings.upload'); // AJAX Upload
    // Rotas de Membro (Comum a todos no painel)
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/balance', [\App\Http\Controllers\Admin\DashboardController::class, 'getMpBalance'])->name('dashboard.balance'); // AJAX Balance
    Route::get('/portal', [\App\Http\Controllers\Admin\MemberController::class, 'portal'])->name('portal.index');
    Route::get('/comunidade', [\App\Http\Controllers\Admin\MemberController::class, 'socialFeed'])->middleware('check.feature:community_access')->name('social.feed.internal');
    Route::get('/courses/available', [\App\Http\Controllers\Admin\CourseController::class, 'available'])->name('courses.available');
    Route::get('/mentorships/available', [\App\Http\Controllers\Admin\MentorshipController::class, 'available'])->middleware('check.feature:mentorships_access')->name('mentorships.available');

    // Chat interno (mantém layout do painel)
    Route::middleware(['check.feature:chat_access', 'check.connection'])->group(function () {
        Route::get('/chat', [\App\Http\Controllers\Admin\ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/start/{user}', [\App\Http\Controllers\Admin\ChatController::class, 'start'])->name('chat.start');
        Route::get('/chat/list', [\App\Http\Controllers\Admin\ChatController::class, 'list'])->name('chat.list');
        Route::get('/chat/{conversation}/messages', [\App\Http\Controllers\Admin\ChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('/chat/{conversation}/message', [\App\Http\Controllers\Admin\ChatController::class, 'storeMessage'])->name('chat.message.store');
        Route::get('/chat/{conversation}', [\App\Http\Controllers\Admin\ChatController::class, 'show'])->name('chat.show');
    });

    // Perfil (Acessível a membros para completar cadastro)
    Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    // Marketplace (Admin - visão interna)
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MarketplaceController::class, 'index'])->name('index');
        Route::get('/pagamentos', [\App\Http\Controllers\Admin\MarketplaceController::class, 'payments'])->name('payments');
        Route::get('/vendas', [\App\Http\Controllers\Admin\MarketplaceController::class, 'sales'])->name('sales');
    });

    // Certificates (Acessível a Admin e Instrutores)
    Route::get('/certificates', [\App\Http\Controllers\Admin\CertificateController::class, 'index'])
        ->middleware('check.feature:certificates_access')->name('certificates.index');
    Route::post('/certificates/send/{certificate}', [\App\Http\Controllers\Admin\CertificateController::class, 'sendEmail'])
        ->middleware('check.feature:certificates_create')->name('certificates.send');
    Route::get('/certificates/create', [\App\Http\Controllers\Admin\CertificateController::class, 'createForm'])
        ->middleware('check.feature:certificates_create')->name('certificates.create');
    Route::post('/certificates/generate', [\App\Http\Controllers\Admin\CertificateController::class, 'generate'])
        ->middleware('check.feature:certificates_generate')->name('certificates.generate');
    Route::post('/certificates/{certificate}/regenerate', [\App\Http\Controllers\Admin\CertificateController::class, 'regenerate'])
        ->middleware('check.feature:certificates_generate')->name('certificates.regenerate');
    Route::get('/certificates/view/{hash}', [\App\Http\Controllers\Admin\CertificateController::class, 'view'])
        ->middleware('check.feature:certificates_access')->name('certificates.view');
    Route::get('/certificates/preview-html/{hash}', [\App\Http\Controllers\Admin\CertificateController::class, 'previewHtml'])
        ->middleware('check.feature:certificates_access')->name('certificates.preview-html');
    Route::delete('/certificates/{certificate}', [\App\Http\Controllers\Admin\CertificateController::class, 'destroy'])
        ->middleware('check.feature:certificates_delete')->name('certificates.destroy');

    // Atalho para Membros (membro/perfil)
    Route::prefix('membro')->group(function () {
        Route::get('/perfil', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('membro.perfil');
    });

    // Depoimentos (moderação por permissões)
    Route::get('/testimonials', [\App\Http\Controllers\Admin\TestimonialController::class, 'index'])->middleware('check.feature:testimonials_access')->name('testimonials.index');
    Route::get('/testimonials/{testimonial}/edit', [\App\Http\Controllers\Admin\TestimonialController::class, 'edit'])->middleware('check.feature:testimonials_edit')->name('testimonials.edit');
    Route::put('/testimonials/{testimonial}', [\App\Http\Controllers\Admin\TestimonialController::class, 'update'])->middleware('check.feature:testimonials_edit')->name('testimonials.update');
    Route::post('/testimonials/{testimonial}/approve', [\App\Http\Controllers\Admin\TestimonialController::class, 'approve'])->middleware('check.feature:testimonials_approve')->name('testimonials.approve');
    Route::post('/testimonials/{testimonial}/reject', [\App\Http\Controllers\Admin\TestimonialController::class, 'reject'])->middleware('check.feature:testimonials_reject')->name('testimonials.reject');
    Route::delete('/testimonials/{testimonial}', [\App\Http\Controllers\Admin\TestimonialController::class, 'destroy'])->middleware('check.feature:testimonials_delete')->name('testimonials.destroy');

    // Avaliações de cursos e mentorias (moderação)
    Route::get('/reviews', [\App\Http\Controllers\Admin\ItemReviewController::class, 'index'])->middleware('check.feature:reviews_access')->name('reviews.index');
    Route::post('/reviews/{review}/approve', [\App\Http\Controllers\Admin\ItemReviewController::class, 'approve'])->middleware('check.feature:reviews_approve')->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [\App\Http\Controllers\Admin\ItemReviewController::class, 'reject'])->middleware('check.feature:reviews_reject')->name('reviews.reject');
    Route::delete('/reviews/{review}', [\App\Http\Controllers\Admin\ItemReviewController::class, 'destroy'])->middleware('check.feature:reviews_delete')->name('reviews.destroy');

    // Rotas Restritas (Apenas Admin/Superadmin)
    Route::middleware([\App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {

        // Impersonate Start (Apenas SuperAdmin)
        Route::get('/users/{user}/impersonate', [\App\Http\Controllers\Admin\ImpersonateController::class, 'impersonate'])->name('users.impersonate');

        Route::get('/settings/{group?}', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/toggle', [\App\Http\Controllers\Admin\SettingController::class, 'toggle'])->name('settings.toggle');
        Route::post('/settings/test-smtp', [\App\Http\Controllers\Admin\SettingController::class, 'testSmtp'])->name('settings.test-smtp');
        Route::post('/settings/test-gateway', [\App\Http\Controllers\Admin\SettingController::class, 'testGateway'])->name('settings.test_gateway');

        // Usuários
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('users');
        // Permissões / Papéis
        Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class)
            ->middleware('check.feature:permissions_access')->names('permissions');

        // Plans CRUD
        Route::post('plans/{plan}/toggle-active', [\App\Http\Controllers\Admin\PlanController::class, 'toggleActive'])
            ->middleware('check.feature:plans_access')->name('plans.toggle-active');
        Route::resource('plans', \App\Http\Controllers\Admin\PlanController::class)
            ->middleware('check.feature:plans_access')->names('plans');

        // Coupons
        Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)
            ->middleware('check.feature:coupons_access')->names('coupons');

        // Courses CRUD
        Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class)
            ->middleware('check.feature:courses_access')->names('courses');

        // Custom Fonts
        Route::get('/fonts', [\App\Http\Controllers\Admin\CustomFontController::class, 'index'])
            ->middleware('check.feature:fonts_access')->name('fonts.index');
        Route::post('/fonts', [\App\Http\Controllers\Admin\CustomFontController::class, 'store'])
            ->middleware('check.feature:fonts_create')->name('fonts.store');
        Route::delete('/fonts/{font}', [\App\Http\Controllers\Admin\CustomFontController::class, 'destroy'])
            ->middleware('check.feature:fonts_delete')->name('fonts.destroy');
        Route::get('/fonts/api/active', [\App\Http\Controllers\Admin\CustomFontController::class, 'getActiveFonts'])
            ->middleware('check.feature:fonts_access')->name('fonts.api.active');

        // FAQ (Perguntas frequentes)
        Route::resource('faqs', \App\Http\Controllers\Admin\FaqController::class)
            ->middleware('check.feature:faqs_access')->names('faqs');

        // Logs de Atividade
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])
            ->name('activity_logs.index');
        Route::post('/activity-logs/clear', [\App\Http\Controllers\Admin\ActivityLogController::class, 'clear'])
            ->name('activity_logs.clear');

        // Events CRUD
        Route::get('events/feed', [\App\Http\Controllers\Admin\EventController::class, 'feed'])
            ->middleware('check.feature:events_access')->name('events.feed');
        Route::post('events/calendar/settings', [\App\Http\Controllers\Admin\EventController::class, 'updateCalendarSettings'])
            ->middleware('check.feature:events_edit')->name('events.calendar.settings');
        Route::resource('events', \App\Http\Controllers\Admin\EventController::class)
            ->middleware('check.feature:events_access')->names('events');

        // Points Rules
        Route::resource('points-rules', \App\Http\Controllers\Admin\PointsRuleController::class)
            ->middleware('check.feature:points_access')->names('points-rules');

        // Mentorships CRUD
        Route::resource('mentorships', \App\Http\Controllers\Admin\MentorshipController::class)
            ->middleware('check.feature:mentorships_access')->names('mentorships');

        // Ranking
        Route::get('/ranking', [\App\Http\Controllers\Admin\RankingController::class, 'index'])
            ->middleware('check.feature:ranking_access')->name('ranking');

        // FAILSAFE ROUTES (DO NOT REMOVE)
        Route::get('/upload/test', fn() => null)->middleware('check.feature:uploads_access')->name('upload.test');
        Route::get('/mailtest', fn() => null)->middleware('check.feature:mailtest_access')->name('mailtest');

        // Chunked uploads
        Route::post('/upload/chunk', [\App\Http\Controllers\UploadChunkController::class, 'storeChunk'])
            ->middleware('check.feature:uploads_chunk')->name('upload.chunk');
        Route::post('/upload/assemble', [\App\Http\Controllers\UploadChunkController::class, 'assemble'])
            ->middleware('check.feature:uploads_assemble')->name('upload.assemble');

        // Mail templates
        Route::get('/mailtemplates', [\App\Http\Controllers\Admin\MailTemplateController::class, 'index'])
            ->middleware('check.feature:mailtemplates_access')->name('mailtemplates.index');
        Route::get('/mailtemplates/create', [\App\Http\Controllers\Admin\MailTemplateController::class, 'create'])
            ->middleware('check.feature:mailtemplates_create')->name('mailtemplates.create');
        Route::post('/mailtemplates', [\App\Http\Controllers\Admin\MailTemplateController::class, 'store'])
            ->middleware('check.feature:mailtemplates_store')->name('mailtemplates.store');
        Route::get('/mailtemplates/{mailtemplate}/edit', [\App\Http\Controllers\Admin\MailTemplateController::class, 'edit'])
            ->middleware('check.feature:mailtemplates_edit')->name('mailtemplates.edit');
        Route::put('/mailtemplates/{mailtemplate}', [\App\Http\Controllers\Admin\MailTemplateController::class, 'update'])
            ->middleware('check.feature:mailtemplates_update')->name('mailtemplates.update');
        Route::delete('/mailtemplates/{mailtemplate}', [\App\Http\Controllers\Admin\MailTemplateController::class, 'destroy'])
            ->middleware('check.feature:mailtemplates_delete')->name('mailtemplates.destroy');
        Route::get('/mailtemplates/{mailtemplate}/preview', [\App\Http\Controllers\Admin\MailTemplateController::class, 'preview'])
            ->middleware('check.feature:mailtemplates_preview')->name('mailtemplates.preview');
        Route::post('/mailtemplates/{mailtemplate}/send-preview', [\App\Http\Controllers\Admin\MailTemplateController::class, 'sendPreview'])
            ->middleware('check.feature:mailtemplates_sendpreview')->name('mailtemplates.sendpreview');

        // Plans
        Route::post('orders/{order}/refund', [\App\Http\Controllers\Admin\OrderController::class, 'refund'])
            ->middleware('check.feature:orders_refund')->name('orders.refund');
        Route::post('orders/{order}/approve', [\App\Http\Controllers\Admin\OrderController::class, 'approveManually'])
            ->name('orders.approve');
        Route::post('orders/{order}/cancel', [\App\Http\Controllers\Admin\OrderController::class, 'cancel'])
            ->name('orders.cancel');
        Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)
            ->middleware('check.feature:orders_access')->only(['index', 'show'])->names('orders');

        // Faturas (PDF)
        Route::post('orders/{order}/invoice', [\App\Http\Controllers\Admin\InvoiceController::class, 'issueForOrder'])
            ->middleware('check.feature:invoices_issue')->name('orders.invoice');
        Route::post('invoices/{invoice}/send', [\App\Http\Controllers\Admin\InvoiceController::class, 'send'])
            ->middleware('check.feature:invoices_send')->name('invoices.send');
        Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\Admin\InvoiceController::class, 'pdf'])
            ->middleware('check.feature:invoices_pdf')->name('invoices.pdf');
        Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class)
            ->middleware('check.feature:invoices_access')->names('invoices');

        // Social / Comunidade Moderação
        Route::get('/social', [\App\Http\Controllers\Admin\SocialController::class, 'index'])
            ->middleware('check.feature:social_access')->name('social.index');
        Route::delete('/social/{post}', [\App\Http\Controllers\Admin\SocialController::class, 'destroy'])
            ->middleware('check.feature:social_delete')->name('social.destroy');
    });
});


