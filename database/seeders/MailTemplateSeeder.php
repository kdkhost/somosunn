<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MailTemplate;

class MailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Boas-vindas',
                'slug' => 'welcome_email',
                'category' => 'auth',
                'locale' => 'pt-BR',
                'subject' => 'Bem-vindo à {{site.name}}!',
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
    <div style="text-align: center; padding-bottom: 20px;">
        <h1 style="color: #333;">Bem-vindo, {{user.name}}!</h1>
    </div>
    <div style="color: #555; line-height: 1.6;">
        <p>Estamos muito felizes em ter você conosco.</p>
        <p>A partir de agora, você tem acesso à nossa plataforma exclusiva. Explore cursos, conecte-se com a comunidade e aproveite todo o conteúdo.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{site.url}}" style="background-color: #007bff; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">Acessar Plataforma</a>
        </p>
        <p>Se tiver alguma dúvida, nossa equipe de suporte está à disposição.</p>
    </div>
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #999;">
        <p>&copy; {{year}} {{site.name}}. Todos os direitos reservados.</p>
    </div>
</div>',
                'is_active' => true,
            ],
            [
                'name' => 'Recuperação de Senha',
                'slug' => 'reset_password',
                'category' => 'auth',
                'locale' => 'pt-BR',
                'subject' => 'Redefinição de Senha - {{site.name}}',
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
    <div style="text-align: center; padding-bottom: 20px;">
        <h2 style="color: #333;">Redefinição de Senha</h2>
    </div>
    <div style="color: #555; line-height: 1.6;">
        <p>Olá, {{user.name}}.</p>
        <p>Recebemos uma solicitação para redefinir a senha da sua conta.</p>
        <p>Se foi você, clique no botão abaixo para criar uma nova senha:</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{action_url}}" style="background-color: #dc3545; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">Redefinir Senha</a>
        </p>
        <p>Se não solicitou esta alteração, ignore este e-mail.</p>
    </div>
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #999;">
        <p>&copy; {{year}} {{site.name}}.</p>
    </div>
</div>',
                'is_active' => true,
            ],
            [
                'name' => 'Pedido Criado (Pix)',
                'slug' => 'order_created_pix',
                'category' => 'order',
                'locale' => 'pt-BR',
                'subject' => 'Recebemos seu pedido #{{order.id}}',
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
    <h2 style="color: #333;">Olá, {{user.name}}!</h2>
    <p style="color: #555;">Recebemos seu pedido <strong>#{{order.id}}</strong>.</p>
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p><strong>Valor:</strong> R$ {{order.total}}</p>
        <p><strong>Status:</strong> Aguardando Pagamento</p>
    </div>
    <p>Para concluir sua compra, utilize o código Pix abaixo (se ainda não pagou):</p>
    <pre style="background: #eee; padding: 10px; overflow-x: auto;">{{pix_code}}</pre>
    <p style="color: #777; font-size: 14px;">O acesso será liberado automaticamente após a confirmação do pagamento.</p>
</div>',
                'is_active' => true,
            ],
            [
                'name' => 'Pagamento Aprovado',
                'slug' => 'payment_paid',
                'category' => 'order',
                'locale' => 'pt-BR',
                'subject' => 'Pagamento Aprovado! Pedido #{{order.id}}',
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #28a745; border-radius: 8px;">
    <div style="text-align: center;">
        <h1 style="color: #28a745;">Pagamento Confirmado!</h1>
    </div>
    <div style="color: #555; line-height: 1.6;">
        <p>Olá, {{user.name}}.</p>
        <p>O pagamento do pedido <strong>#{{order.id}}</strong> foi aprovado com sucesso.</p>
        <p>Seu acesso ao conteúdo já está liberado!</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{site.url}}/portal" style="background-color: #28a745; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">Acessar Agora</a>
        </p>
    </div>
</div>',
                'is_active' => true,
            ]
        ];

        foreach ($templates as $tmpl) {
            MailTemplate::updateOrCreate(
                ['slug' => $tmpl['slug']],
                $tmpl
            );
        }
    }
}