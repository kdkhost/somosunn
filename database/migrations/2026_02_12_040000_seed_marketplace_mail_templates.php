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

        // Este projeto usa templates no banco via `mail_templates` (slug/category/locale).
        if (!Schema::hasColumn('mail_templates', 'slug')) {
            return;
        }

        $hasCategory = Schema::hasColumn('mail_templates', 'category');
        $hasLocale = Schema::hasColumn('mail_templates', 'locale');

        $now = now();

        $templates = [
            [
                'name' => 'Marketplace: Compra Confirmada (Cliente)',
                'slug' => 'marketplace_order_paid_buyer',
                'category' => 'marketplace',
                'locale' => 'pt-BR',
                'subject' => 'Compra confirmada! Pedido #{{ $order[\'id\'] ?? \'\' }} - {{ $site[\'name\'] ?? \'\' }}',
                'body' => <<<'HTML'
<h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">Compra confirmada!</h2>

<p style="margin: 0 0 14px 0;">Olá, <strong>{{ $user['name'] ?? 'Cliente' }}</strong>.</p>

<p style="margin: 0 0 22px 0;">
    Recebemos a confirmação do seu pagamento. Seus produtos já estão disponíveis na sua conta.
</p>

<div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 10px; margin: 0 0 18px 0;">
    <p style="margin: 0 0 6px 0;"><strong>Pedido:</strong> #{{ $order['id'] ?? '' }}</p>
    <p style="margin: 0 0 6px 0;"><strong>Data:</strong> {{ $order['date'] ?? '' }}</p>
    <p style="margin: 0;"><strong>Total:</strong> {{ $order['total'] ?? '' }}</p>
</div>

{!! $order['items_html'] ?? '' !!}

<p style="text-align: center; margin: 24px 0 26px 0;">
    <a href="{{ $links['account_url'] ?? ($site['url'] ?? '#') }}"
        style="display: inline-block; background-color: {{ $site['primary_color'] ?? '#1F5EDB' }}; color: #ffffff; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 700;">
        Acessar minha conta
    </a>
</p>

<p style="margin: 0;">Obrigado,<br>{{ $site['name'] ?? 'UNN' }}</p>
HTML,
                'is_active' => true,
            ],
            [
                'name' => 'Marketplace: Nova Venda (Vendedor)',
                'slug' => 'marketplace_order_paid_seller',
                'category' => 'marketplace',
                'locale' => 'pt-BR',
                'subject' => 'Nova venda! Pedido #{{ $order[\'id\'] ?? \'\' }} - {{ $site[\'name\'] ?? \'\' }}',
                'body' => <<<'HTML'
<h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">Você fez uma nova venda!</h2>

<p style="margin: 0 0 14px 0;">Olá, <strong>{{ $seller['name'] ?? 'Vendedor' }}</strong>.</p>

<p style="margin: 0 0 22px 0;">
    Uma compra foi aprovada no marketplace e já está registrada na sua conta.
</p>

<div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 10px; margin: 0 0 18px 0;">
    <p style="margin: 0 0 6px 0;"><strong>Pedido:</strong> #{{ $order['id'] ?? '' }}</p>
    <p style="margin: 0 0 6px 0;"><strong>Comprador:</strong> {{ $buyer['name'] ?? '' }} ({{ $buyer['email'] ?? '' }})</p>
    <p style="margin: 0;"><strong>Total do pedido:</strong> {{ $order['total'] ?? '' }}</p>
</div>

{!! $order['items_html'] ?? '' !!}

<p style="text-align: center; margin: 24px 0 26px 0;">
    <a href="{{ $links['seller_panel_url'] ?? ($site['url'] ?? '#') }}"
        style="display: inline-block; background-color: {{ $site['primary_color'] ?? '#1F5EDB' }}; color: #ffffff; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 700;">
        Ver vendas no painel
    </a>
</p>

<p style="margin: 0;">Obrigado,<br>{{ $site['name'] ?? 'UNN' }}</p>
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
