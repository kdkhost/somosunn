<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MailTemplate;

class MailTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            ['Boas-vindas','bem-vindo','conta','pt-BR','Bem-vindo à {{site.name}}','<p>Olá {{user.name}},</p><p>Bem-vindo à {{site.name}}! Estamos felizes em ter você.</p><p>Acesse: {{site.url}}</p>'],
            ['Aniversário','aniversario','marketing','pt-BR','Feliz aniversário, {{user.name}}','<p>Parabéns, {{user.name}}!</p><p>Preparamos um presente especial para você.</p>'],
            ['Compra aprovada','compra-aprovada','financeiro','pt-BR','Sua compra {{order.id}} foi aprovada','<p>Olá {{user.name}},</p><p>Recebemos o pagamento do pedido {{order.id}} no valor de {{order.total}}.</p>'],
            ['Pagamento pendente','pagamento-pendente','financeiro','pt-BR','Pagamento pendente do pedido {{order.id}}','<p>Seu pagamento ainda está pendente.</p><p>Vencimento: {{payment.due_date}}</p><p>Link: {{payment.link}}</p>'],
            ['Conta suspensa','conta-suspensa','conta','pt-BR','Conta suspensa','<p>Olá {{user.name}},</p><p>Sua conta foi suspensa. Contate o suporte: {{site.support_email}}</p>'],
            ['Pontos expirando','pontos-expirando','sistema','pt-BR','Seus pontos vão expirar','<p>{{user.name}}, você tem {{user.points}} pontos próximos de expirar.</p>'],
            ['Evento confirmado','evento-confirmado','sistema','pt-BR','Evento confirmado: {{event.title}}','<p>Você está confirmado em {{event.title}} em {{event.date}}.</p><p>Detalhes: {{event.link}}</p>'],
            ['Mentoria agendada','mentoria-agendada','sistema','pt-BR','Mentoria agendada: {{mentorship.title}}','<p>Mentoria: {{mentorship.title}}</p><p>Data: {{event.date}}</p>'],
            ['Senha redefinida','senha-redefinida','conta','pt-BR','Senha redefinida com sucesso','<p>Olá {{user.name}}, sua senha foi redefinida.</p>'],
        ];

        foreach ($templates as [$name,$slug,$cat,$loc,$subject,$body]) {
            MailTemplate::updateOrCreate(
                ['slug'=>$slug],
                [
                    'name'=>$name,
                    'category'=>$cat,
                    'locale'=>$loc,
                    'subject'=>$subject,
                    'body'=>$body,
                    'is_active'=>true,
                ]
            );
        }
    }
}