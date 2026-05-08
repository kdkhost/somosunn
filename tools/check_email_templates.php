<?php
// Verificar e criar MailTemplates essenciais do sistema
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MailTemplate;

echo "=== VERIFICANDO MAIL TEMPLATES ===\n\n";

$essentialTemplates = [
    'email_verification' => [
        'name' => 'Verificação de E-mail',
        'category' => 'sistema',
        'subject' => 'Confirme seu e-mail - {{site.name}}',
        'body' => '<h2>Olá, {{user.name}}!</h2>
<p>Bem-vindo(a) à {{site.name}}. Para ativar sua conta, confirme seu e-mail clicando no botão abaixo.</p>
<p style="text-align: center; margin: 26px 0;">
    <a href="{{verify.url}}" style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold;">Confirmar meu e-mail</a>
</p>
<p>Este link expira em {{verify.expire_minutes}} minutos.</p>',
    ],
    'password_reset' => [
        'name' => 'Redefinição de Senha',
        'category' => 'sistema',
        'subject' => 'Redefinição de Senha - {{site.name}}',
        'body' => '<h2>Olá, {{user.name}}!</h2>
<p>Você solicitou a redefinição de senha na {{site.name}}.</p>
<p style="text-align: center; margin: 26px 0;">
    <a href="{{reset.url}}" style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold;">Redefinir Senha</a>
</p>
<p>Este link expira em {{reset.expire_minutes}} minutos.</p>',
    ],
    'welcome' => [
        'name' => 'Boas-vindas',
        'category' => 'sistema',
        'subject' => 'Bem-vindo(a) à {{site.name}}!',
        'body' => '<h2>Olá, {{user.name}}!</h2>
<p>Seja muito bem-vindo(a) à {{site.name}}. Estamos felizes em ter você aqui.</p>
<p><a href="{{site.url}}" style="color: {{site.primary_color}};">Acessar plataforma</a></p>',
    ],
];

foreach ($essentialTemplates as $slug => $data) {
    $existing = MailTemplate::where('slug', $slug)->first();

    if ($existing) {
        echo "OK: Template '$slug' já existe (id={$existing->id}, active=" . ($existing->is_active ? 'SIM' : 'NAO') . ")\n";
    } else {
        $data['slug'] = $slug;
        $data['is_active'] = true;
        $data['locale'] = 'pt-BR';
        $template = MailTemplate::create($data);
        echo "CRIADO: Template '$slug' (id={$template->id})\n";
    }
}

echo "\n=== TOTAL DE MAIL TEMPLATES ===\n";
$all = MailTemplate::orderBy('category')->orderBy('name')->get(['id', 'slug', 'name', 'category', 'is_active']);
foreach ($all as $t) {
    echo sprintf("[%d] %-40s (%s) %s %s\n",
        $t->id,
        $t->slug,
        $t->category ?? 'sem categoria',
        $t->is_active ? '✓ ativo' : '✗ inativo',
        $t->name
    );
}
echo "\nTotal: " . $all->count() . " templates\n";
