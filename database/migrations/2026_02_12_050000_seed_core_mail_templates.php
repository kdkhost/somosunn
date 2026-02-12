<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mail_templates')) {
            return;
        }

        if (!Schema::hasColumn('mail_templates', 'slug')) {
            return;
        }

        $hasCategory = Schema::hasColumn('mail_templates', 'category');
        $hasLocale = Schema::hasColumn('mail_templates', 'locale');

        $now = now();

        $templates = [
            [
                'name' => 'Boas-vindas',
                'slug' => 'welcome_email',
                'category' => 'auth',
                'locale' => 'pt-BR',
                'subject' => 'Bem-vindo à {{site.name}}!',
                'body' => <<<'HTML'
<h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">
    Bem-vindo(a), {{user.name}}!
</h2>

<p style="margin: 0 0 14px 0;">
    Sua conta foi criada com sucesso e você já pode acessar a plataforma.
</p>

<p style="margin: 0 0 22px 0;">
    Aproveite para se conectar com outros membros, acessar conteúdos e explorar tudo o que a {{site.name}} oferece.
</p>

<p style="text-align: center; margin: 24px 0 26px 0;">
    <a href="{{links.account_url}}"
        style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 700;">
        Acessar minha conta
    </a>
</p>

<p style="margin: 0;">
    Obrigado,<br>
    {{site.name}}
</p>
HTML,
                'is_active' => true,
            ],
            [
                'name' => 'Pagamento Aprovado',
                'slug' => 'payment_paid',
                'category' => 'order',
                'locale' => 'pt-BR',
                'subject' => 'Pagamento confirmado! Pedido #{{order.id}} - {{site.name}}',
                'body' => <<<'HTML'
<h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">
    Pagamento confirmado!
</h2>

<p style="margin: 0 0 14px 0;">
    Olá, <strong>{{user.name}}</strong>.
</p>

<p style="margin: 0 0 22px 0;">
    Recebemos a confirmação do seu pagamento. Seu acesso já está liberado.
</p>

<div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 10px; margin: 0 0 22px 0;">
    <p style="margin: 0 0 6px 0;"><strong>Pedido:</strong> #{{order.id}}</p>
    <p style="margin: 0 0 6px 0;"><strong>Plano:</strong> {{order.plan_title}}</p>
    <p style="margin: 0;"><strong>Valor:</strong> {{order.total}}</p>
</div>

<p style="text-align: center; margin: 24px 0 26px 0;">
    <a href="{{links.portal_url}}"
        style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 700;">
        Acessar Portal
    </a>
</p>

<p style="margin: 0;">
    Obrigado,<br>
    {{site.name}}
</p>
HTML,
                'is_active' => true,
            ],
            [
                'name' => 'Fatura (PDF)',
                'slug' => 'invoice_email',
                'category' => 'financeiro',
                'locale' => 'pt-BR',
                'subject' => 'Fatura {{invoice.number}} - {{site.name}}',
                'body' => <<<'HTML'
<h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">
    Fatura {{invoice.number}}
</h2>

<p style="margin: 0 0 14px 0;">
    Olá, <strong>{{user.name}}</strong>.
</p>

<p style="margin: 0 0 22px 0;">
    Segue em anexo o PDF da sua fatura para conferência e registro.
</p>

<div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 10px; margin: 0 0 22px 0;">
    <p style="margin: 0 0 6px 0;"><strong>Número:</strong> {{invoice.number}}</p>
    <p style="margin: 0 0 6px 0;"><strong>Data:</strong> {{invoice.issued_at}}</p>
    <p style="margin: 0;"><strong>Valor:</strong> {{invoice.total}}</p>
</div>

<p style="margin: 0;">
    Obrigado,<br>
    {{site.name}}
</p>
HTML,
                'is_active' => true,
            ],
            [
                'name' => 'Certificado Emitido',
                'slug' => 'certificate_issued',
                'category' => 'sistema',
                'locale' => 'pt-BR',
                'subject' => 'Seu certificado está disponível - {{certificate.item_title}}',
                'body' => <<<'HTML'
<h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">
    Parabéns, {{user.name}}!
</h2>

<p style="margin: 0 0 14px 0;">
    Você concluiu {{certificate.item_type_label}} <strong>{{certificate.item_title}}</strong> com sucesso.
</p>

<p style="margin: 0 0 22px 0;">
    Seu certificado oficial já está disponível. Você pode baixá-lo clicando no botão abaixo.
</p>

<p style="text-align: center; margin: 24px 0 26px 0;">
    <a href="{{links.download_url}}"
        style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 700;">
        Baixar Certificado
    </a>
</p>

<p style="margin: 0;">
    Obrigado,<br>
    {{site.name}}
</p>
HTML,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            $existing = DB::table('mail_templates')->where('slug', $template['slug'])->first();

            if (!$existing) {
                $insert = [
                    'name' => $template['name'],
                    'slug' => $template['slug'],
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'is_active' => (bool) $template['is_active'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($hasCategory) {
                    $insert['category'] = $template['category'];
                }
                if ($hasLocale) {
                    $insert['locale'] = $template['locale'];
                }

                DB::table('mail_templates')->insert($insert);
                continue;
            }

            // Não sobrescreve templates já existentes (respeita edições do admin).
            $update = [];

            if ($hasCategory && empty($existing->category)) {
                $update['category'] = $template['category'];
            }
            if ($hasLocale && empty($existing->locale)) {
                $update['locale'] = $template['locale'];
            }
            if (trim((string) ($existing->subject ?? '')) === '') {
                $update['subject'] = $template['subject'];
            }
            if (trim((string) ($existing->body ?? '')) === '') {
                $update['body'] = $template['body'];
            }

            if (!empty($update)) {
                $update['updated_at'] = $now;
                DB::table('mail_templates')->where('id', (int) $existing->id)->update($update);
            }
        }
    }

    public function down(): void
    {
        // no-op (safety)
    }
};

