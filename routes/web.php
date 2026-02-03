<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\MailTestController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\SatisfactionController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/portal', [HomeController::class, 'portal'])->name('portal');
Route::get('/premium', [HomeController::class, 'premium'])->name('premium');
Route::redirect('/membros', '/portal')->name('membros');

// PWA static files to avoid 404 in production
Route::get('/service-worker.js', function () {
    $path = public_path('service-worker.js');
    abort_unless(file_exists($path), 404);
    return response()->file($path, ['Content-Type' => 'application/javascript']);
});
Route::get('/manifest.webmanifest', function () {
    $path = public_path('manifest.webmanifest');
    abort_unless(file_exists($path), 404);
    return response()->file($path, ['Content-Type' => 'application/manifest+json']);
});
Route::get('/favicon.ico', function () {
    $custom = \App\Models\Setting::get('favicon_image');
    if($custom && file_exists(public_path($custom))){
        return response()->file(public_path($custom));
    }
    $default = public_path('img/logo.svg');
    abort_unless(file_exists($default), 404);
    return response()->file($default, ['Content-Type' => 'image/svg+xml']);
});

// Auth scaffold (simplificado)
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'authenticate']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/register', fn () => view('auth.register'))->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'store']);

// Password reset (feedback visual simples)
Route::get('/password/forgot', fn () => view('auth.passwords.email'))->name('password.request');
Route::post('/password/email', fn () => back()->with('status','Link de redefinição enviado (simulado).'))->name('password.email');
Route::get('/password/reset/{token?}', fn ($token = null) => view('auth.passwords.reset', ['token' => $token]))->name('password.reset');
Route::post('/password/reset', fn (\Illuminate\Http\Request $request) => redirect()->route('login')->with('status','Senha redefinida com sucesso (simulado).'))->name('password.update');

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
Route::get('/install/run', fn () => redirect()->route('install.index'));
Route::get('/backend/install', [\App\Http\Controllers\InstallController::class, 'index'])->name('install.index.legacy');
Route::post('/backend/install/run', [\App\Http\Controllers\InstallController::class, 'run'])->name('install.run.legacy');
Route::post('/backend/install/test-connection', [\App\Http\Controllers\InstallController::class, 'testConnection'])->name('install.test-connection.legacy');
Route::get('/backend/install/run', fn () => redirect()->route('install.index.legacy'));

Route::post('/api/interactions', [InteractionController::class, 'store'])->name('api.interactions.store');
Route::post('/api/satisfactions', [SatisfactionController::class, 'store'])->name('api.satisfactions.store');
Route::get('/api/ranking', [RankingController::class, 'index'])->name('api.ranking.index');

// PWA manifest (dynamic)
Route::get('/manifest.webmanifest', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('manifest');

// Admin templates / tests
Route::get('/admin/mail-test', [MailTestController::class, 'showForm'])->name('admin.mailtest');
Route::post('/admin/mail-test/send', [MailTestController::class, 'sendTest'])->name('admin.mailtest.send');

// Public & Creator Course Routes
Route::resource('courses', \App\Http\Controllers\CourseController::class);
Route::post('courses/{course}/lessons', [\App\Http\Controllers\LessonController::class, 'store'])->name('courses.lessons.store');
Route::put('courses/{course}/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'update'])->name('courses.lessons.update');
Route::delete('courses/{course}/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'destroy'])->name('courses.lessons.destroy');
Route::get('courses/{course}/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'show'])->name('courses.lessons.show');

// Public Events
Route::get('/eventos/{event}', [\App\Http\Controllers\EventController::class, 'show'])->name('events.show');

// Social / Community
Route::get('/feed', [\App\Http\Controllers\SocialController::class, 'feed'])->name('social.feed');
Route::get('/profile/{username}', [\App\Http\Controllers\SocialController::class, 'profile'])->name('social.profile');
Route::post('/post', [\App\Http\Controllers\SocialController::class, 'storePost'])->name('social.post.store');

// Chat
Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
Route::get('/chat/list', [\App\Http\Controllers\ChatController::class, 'list'])->name('chat.list');
Route::get('/chat/{conversation}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
Route::post('/chat/{conversation}/message', [\App\Http\Controllers\ChatController::class, 'storeMessage'])->name('chat.message.store');

// Payments & Checkout
Route::get('/settings/payment', [\App\Http\Controllers\GatewayAccountController::class, 'index'])->name('settings.payment');
Route::post('/settings/payment', [\App\Http\Controllers\GatewayAccountController::class, 'update'])->name('settings.payment.update');

Route::get('/checkout/{course}', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{course}', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success/{order}', fn () => view('checkout.success'))->name('checkout.success');
Route::get('/checkout/failure/{order}', fn () => view('checkout.failure'))->name('checkout.failure');
Route::get('/checkout/pending/{order}', fn () => view('checkout.pending'))->name('checkout.pending');

Route::post('/webhook/mercadopago/{seller_id}', [\App\Http\Controllers\PaymentWebhookController::class, 'mercadopago'])->name('webhook.mercadopago');

// Admin routes (simple scaffold)
Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function(){
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-smtp', [\App\Http\Controllers\Admin\SettingController::class, 'testSmtp'])->name('settings.test-smtp');

    // Usuários
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('users');
    // Permissões / Papéis
    Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class)->names('permissions');

    Route::get('/upload-test', fn () => view('admin.upload_test'))->name('upload.test');

    // Courses CRUD
    Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class)->names('courses');

    // Events CRUD
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
    Route::resource('plans', \App\Http\Controllers\Admin\PlanController::class)->names('plans');

    // Orders / Financeiro
    Route::post('orders/{order}/refund', [\App\Http\Controllers\Admin\OrderController::class, 'refund'])->name('orders.refund');
    Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)->only(['index', 'show'])->names('orders');
    
    // Social / Comunidade Moderação
    Route::get('/social', [\App\Http\Controllers\Admin\SocialController::class, 'index'])->name('social.index');
    Route::delete('/social/{post}', [\App\Http\Controllers\Admin\SocialController::class, 'destroy'])->name('social.destroy');

    // Rota de Emergência para corrigir Banco de Dados
    Route::get('/fix-migration', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            return '<h3>Sucesso! Banco de dados atualizado.</h3><pre>' . \Illuminate\Support\Facades\Artisan::output() . '</pre>';
        } catch (\Exception $e) {
            return '<h3>Erro ao migrar:</h3> ' . $e->getMessage();
        }
    })->middleware('auth'); // Protegido por auth admin
});
