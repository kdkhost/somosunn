<?php

use App\Models\MailTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mail_templates')) {
            return;
        }

        $templates = [
            ['generic_system_email', 'Email Generico do Sistema', 'sistema', '{message.subject}', '{message.content}'],
            ['payment_confirmed', 'Pagamento Confirmado', 'financeiro', 'Pagamento confirmado - Pedido #{{order.id}}', '<h2>Pagamento confirmado!</h2><p>Ola, {{user.name}}.</p><p>O pagamento do pedido <strong>#{{order.id}}</strong>, no valor de <strong>{{order.total}}</strong>, foi confirmado.</p>'],
            ['buyer_communication', 'Comunicacao com Compradores', 'marketing', '{{notification.subject}}', '<div>{!! $notification[\'message\'] ?? \'\' !!}</div><p><a href="{{notification.action_url}}">{{notification.action_label}}</a></p>'],
            ['feedback_request', 'Solicitacao de Avaliacao', 'marketing', 'Avalie sua experiencia: {{item.name}}', '<h2>Ola, {{user.name}}!</h2><p>Gostariamos de saber sua opiniao sobre <strong>{{item.name}}</strong>.</p><p><a href="{{feedback.url}}">Deixar avaliacao</a></p>'],
            ['job_vacancy_published', 'Nova Vaga Publicada', 'vagas', 'Nova oportunidade: {{vacancy.title}}', '<h2>Ola, {{user.name}}!</h2><p>Uma nova vaga foi publicada.</p><p><strong>Vaga:</strong> {{vacancy.title}}<br><strong>Empresa:</strong> {{vacancy.company}}<br><strong>Localizacao:</strong> {{vacancy.location}}</p><p><a href="{{vacancy.url}}">Ver detalhes da vaga</a></p>'],
            ['redemption_status_updated', 'Atualizacao de Resgate', 'sistema', 'Atualizacao no seu resgate: {{redemption.status_label}}', '<h2>Ola, {{user.name}}!</h2><p>{{redemption.message}}</p><p><strong>Responsavel:</strong> {{redemption.provider_name}}<br><strong>Rastreio:</strong> {{redemption.tracking_code}}</p><p><a href="{{redemption.history_url}}">Ver meus resgates</a></p>'],
            ['redemption_requested_provider', 'Novo Resgate para Fornecedor', 'sistema', 'Novo resgate com UNNBIT: {{redemption.item_name}}', '<h2>Novo resgate recebido</h2><p><strong>Comprador:</strong> {{redemption.buyer_name}}<br><strong>Item:</strong> {{redemption.item_name}}<br><strong>Tipo:</strong> {{redemption.item_type}}<br><strong>UNNBIT:</strong> {{redemption.points}}</p><p><a href="{{redemption.action_url}}">Abrir gestao de resgates</a></p>'],
            ['connection_request', 'Solicitacao de Conexao', 'conta', 'Nova solicitacao de conexao', '<h2>Ola, {{user.name}}!</h2><p><strong>{{requester.name}}</strong> enviou uma solicitacao de conexao.</p><p><a href="{{requester.profile_url}}">Ver perfil e responder</a></p>'],
            ['social_post_reported', 'Denuncia de Post', 'sistema', 'Denuncia de post na comunidade', '<h2>Denuncia de post #{{post.id}}</h2><p><strong>Autor:</strong> {{post.author}}<br><strong>Denunciante:</strong> {{report.author}}</p><p><strong>Motivo:</strong> {{report.reason}}</p>'],
            ['contact_form_received', 'Formulario de Contato Recebido', 'sistema', '[Contato] {{contact.subject}} - {{site.name}}', '<h2>Novo contato recebido</h2><p><strong>Nome:</strong> {{contact.name}}<br><strong>Email:</strong> {{contact.email}}<br><strong>Telefone:</strong> {{contact.phone}}<br><strong>Assunto:</strong> {{contact.subject}}</p><p><strong>Mensagem:</strong></p><div>{!! $contact[\'message\'] ?? \'\' !!}</div><p><small>IP: {{contact.ip}}</small></p>'],
            ['email_verification', 'Verificacao de E-mail', 'sistema', 'Confirme seu e-mail - {{site.name}}', '<h2>Ola, {{user.name}}!</h2><p>Confirme seu endereco de e-mail para ativar sua conta.</p><p><a href="{{verify.url}}">Confirmar meu e-mail</a></p><p>Este link expira em {{verify.expire_minutes}} minutos.</p>'],
            ['password_reset', 'Redefinicao de Senha', 'sistema', 'Redefinicao de Senha - {{site.name}}', '<h2>Ola, {{user.name}}!</h2><p>Recebemos uma solicitacao de redefinicao de senha.</p><p><a href="{{reset.url}}">Redefinir senha</a></p><p>Este link expira em {{reset.expire_minutes}} minutos.</p>'],
            ['marketing_manager_assigned', 'Responsavel de Marketing Designado', 'marketing', 'Voce foi designado como Responsavel de Marketing da {{platform.name}}', '<h2>Ola, {{user.name}}!</h2><p>Voce foi designado como <strong>Responsavel de Marketing</strong> da {{platform.name}}.</p><p><a href="{{panel.url}}">Acessar painel de Marketing</a></p>'],
            ['job_apply_candidate', 'Candidatura Recebida pelo Candidato', 'vagas', 'Candidatura enviada para {{vacancy_title}}', '<h2>Ola, {{name}}!</h2><p>Sua candidatura para <strong>{{vacancy_title}}</strong>, na empresa {{company}}, foi enviada com sucesso.</p><p>Local: {{location}}</p>'],
            ['job_apply_owner', 'Nova Candidatura para Responsavel da Vaga', 'vagas', 'Nova candidatura para {{vacancy_title}}', '<h2>Ola, {{owner_name}}!</h2><p><strong>{{candidate}}</strong> candidatou-se para {{vacancy_title}}.</p><p><a href="{{candidates_url}}">Ver candidatos</a></p>'],
        ];

        foreach ($templates as [$slug, $name, $category, $subject, $body]) {
            MailTemplate::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'category' => $category,
                'locale' => 'pt-BR',
                'subject' => $subject,
                'body' => $body,
                'is_active' => true,
            ]);
        }
    }

    public function down(): void
    {
        // Templates editaveis podem ter sido personalizados; nao removemos no rollback.
    }
};
