<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MailTemplate;

MailTemplate::updateOrCreate(
    ['slug' => 'marketing_manager_assigned'],
    [
        'name' => 'Designacao de Responsavel de Marketing',
        'slug' => 'marketing_manager_assigned',
        'category' => 'admin',
        'locale' => 'pt-BR',
        'subject' => 'Voce foi designado como Responsavel de Marketing - {{platform.name}}',
        'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; padding-bottom: 20px;">
        <h1 style="color: #1F5EDB; font-size: 24px;">Responsavel de Marketing</h1>
    </div>
    <div style="color: #333; line-height: 1.7;">
        <p>Ola <strong>{{user.name}}</strong>,</p>
        <p>O administrador da plataforma <strong>{{platform.name}}</strong> designou voce como <strong>Responsavel de Marketing</strong>.</p>
        <p>A partir de agora voce tera acesso a uma area exclusiva no seu painel com informacoes sobre:</p>
        <ul style="color: #555;">
            <li>Valores destinados ao marketing da plataforma</li>
            <li>Saldo de comissoes por vendas realizadas</li>
            <li>Coordenacao do profissional de trafego pago</li>
        </ul>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{panel.url}}" style="background: linear-gradient(135deg, #1F5EDB, #177FD6); color: #fff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">Acessar Painel de Marketing</a>
        </p>
        <p style="color: #666; font-size: 14px;">Qualquer duvida, entre em contato com o administrador da plataforma.</p>
    </div>
</div>',
        'is_active' => true,
    ]
);

echo "Template 'marketing_manager_assigned' criado/atualizado com sucesso.\n";
