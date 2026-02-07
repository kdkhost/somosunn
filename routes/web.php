<?php

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

// Rota de Emergência para Diagnóstico
Route::get('/debug-test', function () {
    return "<h1>Laravel is Running!</h1> PHP Version: " . phpversion();
});

// Rota de Emergência para Limpeza de Cache (Brute Force)
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

    return "<h1>Limpeza Concluída</h1><pre>" . implode("\n", $log) . "</pre><br><a href='/admin'>Voltar ao Admin</a>";
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

// Events
Route::get('/assinar/{plan}', [\App\Http\Controllers\SubscriptionController::class, 'checkout'])->name('subscription.checkout');
Route::post('/assinar/{plan}', [\App\Http\Controllers\SubscriptionController::class, 'process'])->name('subscription.process');
Route::get('/assinar/sucesso/{order}', [\App\Http\Controllers\SubscriptionController::class, 'success'])->name('subscription.success');

// Public Events (Site)
Route::get('/eventos', [\App\Http\Controllers\EventController::class, 'index'])->name('events.index');
Route::get('/eventos/{event}/checkout', [\App\Http\Controllers\EventReservationController::class, 'checkout'])->name('events.checkout');
Route::post('/eventos/{event}/reservar', [\App\Http\Controllers\EventReservationController::class, 'reserve'])->name('events.reserve');
Route::get('/eventos/{event}', [\App\Http\Controllers\EventController::class, 'show'])->name('events.show');

Route::get('/eventos/pagamento/sucesso/{order}', [\App\Http\Controllers\EventReservationController::class, 'paymentSuccess'])->name('events.payment.success');
Route::get('/eventos/pagamento/pendente/{order}', [\App\Http\Controllers\EventReservationController::class, 'paymentPending'])->name('events.payment.pending');
Route::get('/eventos/pagamento/falha/{order}', [\App\Http\Controllers\EventReservationController::class, 'paymentFailure'])->name('events.payment.failure');

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

// Auth scaffold (simplificado)
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'authenticate']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/register', fn() => view('auth.register'))->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'store']);

// Password reset (feedback visual simples)
Route::get('/password/forgot', fn() => view('auth.passwords.email'))->name('password.request');
Route::post('/password/email', fn() => back()->with('status', 'Link de redefinição enviado (simulado).'))->name('password.email');
Route::get('/password/reset/{token?}', fn($token = null) => view('auth.passwords.reset', ['token' => $token]))->name('password.reset');
Route::post('/password/reset', fn(\Illuminate\Http\Request $request) => redirect()->route('login')->with('status', 'Senha redefinida com sucesso (simulado).'))->name('password.update');

// Social Auth
Route::get('/auth/redirect/{provider}', [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/callback/{provider}', [\App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])->name('social.callback');

// File uploads
Route::post('/upload', [\App\Http\Controllers\UploadController::class, 'upload'])->name('upload.file');

// Webhooks and payments endpoints (placeholders)
Route::post('/webhook/mercadopago', [\App\Http\Controllers\PaymentWebhookController::class, 'mercadoPago']);
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



// Public & Creator Course Routes
// Public & Creator Course Routes
Route::resource('courses', \App\Http\Controllers\CourseController::class);

// Feature: Mentorships
Route::middleware(['check.feature:mentorships'])->group(function () {
    Route::resource('mentorships', \App\Http\Controllers\MentorshipController::class)->only(['index', 'show']);
});

Route::post('courses/{course}/lessons', [\App\Http\Controllers\LessonController::class, 'store'])->name('courses.lessons.store');
Route::put('courses/{course}/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'update'])->name('courses.lessons.update');
Route::delete('courses/{course}/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'destroy'])->name('courses.lessons.destroy');
Route::get('courses/{course}/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'show'])->name('courses.lessons.show');

// Attachments
Route::post('courses/{course}/lessons/{lesson}/attachments', [\App\Http\Controllers\LessonController::class, 'uploadAttachment'])->name('courses.lessons.attachments.upload');
Route::get('courses/{course}/lessons/{lesson}/attachments/{attachment}/download', [\App\Http\Controllers\LessonController::class, 'downloadAttachment'])->name('courses.lessons.attachments.download');
Route::delete('courses/{course}/lessons/{lesson}/attachments/{attachment}', [\App\Http\Controllers\LessonController::class, 'deleteAttachment'])->name('courses.lessons.attachments.destroy');
Route::put('courses/{course}/lessons/{lesson}/attachments/{attachment}', [\App\Http\Controllers\LessonController::class, 'renameAttachment'])->name('courses.lessons.attachments.rename');
Route::get('courses/{course}/lessons/{lesson}/details', [\App\Http\Controllers\LessonController::class, 'getDetails'])->name('courses.lessons.details');

// (events.show/events.index defined above as public routes)

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
    });
});

// Payments & Checkout
Route::get('/settings/payment', [\App\Http\Controllers\GatewayAccountController::class, 'index'])->name('settings.payment');
Route::post('/settings/payment', [\App\Http\Controllers\GatewayAccountController::class, 'update'])->name('settings.payment.update');

Route::get('/checkout/{course}', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{course}', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success/{order}', fn() => view('checkout.success'))->name('checkout.success');
Route::get('/checkout/failure/{order}', fn() => view('checkout.failure'))->name('checkout.failure');
Route::get('/checkout/pending/{order}', fn() => view('checkout.pending'))->name('checkout.pending');

Route::post('/webhook/mercadopago/{seller_id}', [\App\Http\Controllers\PaymentWebhookController::class, 'mercadopago'])->name('webhook.mercadopago');

// Admin routes (simple scaffold)
Route::prefix('admin')->name('admin.')->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class, 'check.plan'])->group(function () {
    // Rotas de Membro (Comum a todos no painel)
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/portal', [\App\Http\Controllers\Admin\MemberController::class, 'portal'])->name('portal.index');
    Route::get('/comunidade', [\App\Http\Controllers\Admin\MemberController::class, 'socialFeed'])->middleware('check.feature:community')->name('social.feed.internal');

    // Chat interno (mantém layout do painel)
    Route::middleware(['check.feature:chat', 'check.connection'])->group(function () {
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

    // Depoimentos (moderação por permissões)
    Route::get('/testimonials', [\App\Http\Controllers\Admin\TestimonialController::class, 'index'])->name('testimonials.index');
    Route::get('/testimonials/{testimonial}/edit', [\App\Http\Controllers\Admin\TestimonialController::class, 'edit'])->name('testimonials.edit');
    Route::put('/testimonials/{testimonial}', [\App\Http\Controllers\Admin\TestimonialController::class, 'update'])->name('testimonials.update');
    Route::post('/testimonials/{testimonial}/approve', [\App\Http\Controllers\Admin\TestimonialController::class, 'approve'])->name('testimonials.approve');
    Route::post('/testimonials/{testimonial}/reject', [\App\Http\Controllers\Admin\TestimonialController::class, 'reject'])->name('testimonials.reject');
    Route::delete('/testimonials/{testimonial}', [\App\Http\Controllers\Admin\TestimonialController::class, 'destroy'])->name('testimonials.destroy');

    // Impersonate Stop (disponível se estiver impersonando, sessão controla)
    Route::get('/stop-impersonating', [\App\Http\Controllers\Admin\ImpersonateController::class, 'stop'])->name('impersonate.stop');

    // Rotas Restritas (Apenas Admin/Superadmin)
    Route::middleware([\App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {

        // Impersonate Start (Apenas SuperAdmin)
        Route::get('/users/{user}/impersonate', [\App\Http\Controllers\Admin\ImpersonateController::class, 'impersonate'])->name('users.impersonate');

        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-smtp', [\App\Http\Controllers\Admin\SettingController::class, 'testSmtp'])->name('settings.test-smtp');

        // Usuários
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('users');
        // Permissões / Papéis
        Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class)->names('permissions');

        // Plans CRUD
        Route::resource('plans', \App\Http\Controllers\Admin\PlanController::class)->names('plans');

        // Coupons
        Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)->names('coupons');

        // Courses CRUD
        Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class)->names('courses');

        // Custom Fonts
        Route::get('/fonts', [\App\Http\Controllers\Admin\CustomFontController::class, 'index'])->name('fonts.index');
        Route::post('/fonts', [\App\Http\Controllers\Admin\CustomFontController::class, 'store'])->name('fonts.store');
        Route::delete('/fonts/{font}', [\App\Http\Controllers\Admin\CustomFontController::class, 'destroy'])->name('fonts.destroy');
        Route::get('/fonts/api/active', [\App\Http\Controllers\Admin\CustomFontController::class, 'getActiveFonts'])->name('fonts.api.active');

        // FAQ (Perguntas frequentes)
        Route::resource('faqs', \App\Http\Controllers\Admin\FaqController::class)->names('faqs');

        // Events CRUD
        Route::get('events/feed', [\App\Http\Controllers\Admin\EventController::class, 'feed'])->name('events.feed');
        Route::post('events/calendar/settings', [\App\Http\Controllers\Admin\EventController::class, 'updateCalendarSettings'])->name('events.calendar.settings');
        Route::resource('events', \App\Http\Controllers\Admin\EventController::class)->names('events');

        // Points Rules
        Route::resource('points-rules', \App\Http\Controllers\Admin\PointsRuleController::class)->names('points-rules');

        // Mentorships CRUD
        Route::resource('mentorships', \App\Http\Controllers\Admin\MentorshipController::class)->names('mentorships');

        // Certificates
        Route::get('/certificates/create', [\App\Http\Controllers\Admin\CertificateController::class, 'createForm'])->name('certificates.create');
        Route::post('/certificates/generate', [\App\Http\Controllers\Admin\CertificateController::class, 'generate'])->name('certificates.generate');
        Route::get('/certificates/view/{hash}', [\App\Http\Controllers\Admin\CertificateController::class, 'view'])->name('certificates.view');

        // Ranking
        Route::get('/ranking', [\App\Http\Controllers\Admin\RankingController::class, 'index'])->name('ranking');


        // FAILSAFE ROUTES (DO NOT REMOVE)
        // Essas rotas existem para prevenir erros de cache em produção
        Route::get('/upload/test', fn() => null)->name('upload.test');
        Route::get('/mailtest', fn() => null)->name('mailtest');

        // Chunked uploads
        Route::post('/upload/chunk', [\App\Http\Controllers\UploadChunkController::class, 'storeChunk'])->name('upload.chunk');
        Route::post('/upload/assemble', [\App\Http\Controllers\UploadChunkController::class, 'assemble'])->name('upload.assemble');

        // Mail templates
        Route::get('/mailtemplates', [\App\Http\Controllers\Admin\MailTemplateController::class, 'index'])->name('mailtemplates.index');
        Route::get('/mailtemplates/create', [\App\Http\Controllers\Admin\MailTemplateController::class, 'create'])->name('mailtemplates.create');
        Route::post('/mailtemplates', [\App\Http\Controllers\Admin\MailTemplateController::class, 'store'])->name('mailtemplates.store');
        Route::get('/mailtemplates/{mailtemplate}/edit', [\App\Http\Controllers\Admin\MailTemplateController::class, 'edit'])->name('mailtemplates.edit');
        Route::put('/mailtemplates/{mailtemplate}', [\App\Http\Controllers\Admin\MailTemplateController::class, 'update'])->name('mailtemplates.update');
        Route::delete('/mailtemplates/{mailtemplate}', [\App\Http\Controllers\Admin\MailTemplateController::class, 'destroy'])->name('mailtemplates.destroy');
        Route::get('/mailtemplates/{mailtemplate}/preview', [\App\Http\Controllers\Admin\MailTemplateController::class, 'preview'])->name('mailtemplates.preview');
        Route::post('/mailtemplates/{mailtemplate}/send-preview', [\App\Http\Controllers\Admin\MailTemplateController::class, 'sendPreview'])->name('mailtemplates.sendpreview');

        // Plans
        Route::post('orders/{order}/refund', [\App\Http\Controllers\Admin\OrderController::class, 'refund'])->name('orders.refund');
        Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)->only(['index', 'show'])->names('orders');

        // Faturas (PDF)
        Route::post('orders/{order}/invoice', [\App\Http\Controllers\Admin\InvoiceController::class, 'issueForOrder'])->name('orders.invoice');
        Route::post('invoices/{invoice}/send', [\App\Http\Controllers\Admin\InvoiceController::class, 'send'])->name('invoices.send');
        Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\Admin\InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class)->names('invoices');

        // Social / Comunidade Moderação
        Route::get('/social', [\App\Http\Controllers\Admin\SocialController::class, 'index'])->name('social.index');
        Route::delete('/social/{post}', [\App\Http\Controllers\Admin\SocialController::class, 'destroy'])->name('social.destroy');
    });
});
