<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * @return array<string,string>
     */
    private function settings(): array
    {
        return [
            'system_timezone' => 'America/Sao_Paulo',
            'cron_orders_cancel_enabled' => '1',
            'cron_orders_unpaid_reminders_enabled' => '1',
            'orders_unpaid_cancel_after_hours' => '24',
            'orders_unpaid_reminder_hours' => '2,12,20',
            'orders_unpaid_admin_bcc_enabled' => '1',
            'backup_database_enabled' => '1',
            'backup_database_time' => '03:00',
            'backup_config_enabled' => '1',
            'backup_config_weekday' => '0',
            'backup_config_time' => '04:00',
        ];
    }

    /**
     * @return array<int,array<string,string>>
     */
    private function templates(): array
    {
        return [
            [
                'slug' => 'order_unpaid_payment_reminder',
                'name' => 'Lembrete de pedido nao pago',
                'category' => 'pedidos',
                'locale' => 'pt-BR',
                'subject' => 'Pedido #{{order.id}} aguardando pagamento - {{site.name}}',
                'body' => '<h2>Ola, {{user.name}}!</h2>
<p>Seu pedido <strong>#{{order.id}}</strong> ainda esta aguardando pagamento.</p>
<p><strong>Valor:</strong> {{order.total}}<br><strong>Compra feita em:</strong> {{order.created_at}}<br><strong>Prazo para pagamento:</strong> {{order.cancel_at}}</p>
<p>{{order.items_html}}</p>
<p style="text-align:center;margin:26px 0;">
    <a href="{{order.payment_url}}" style="display:inline-block;background-color:{{site.primary_color}};color:#ffffff;padding:14px 28px;text-decoration:none;border-radius:8px;font-weight:bold;">Finalizar pagamento</a>
</p>
<p style="color:#666;font-size:13px;">Se voce ja pagou, desconsidere este e-mail. O sistema confirmara o pagamento automaticamente quando o gateway retornar a aprovacao.</p>',
            ],
            [
                'slug' => 'order_unpaid_auto_cancelled',
                'name' => 'Pedido nao pago cancelado automaticamente',
                'category' => 'pedidos',
                'locale' => 'pt-BR',
                'subject' => 'Pedido #{{order.id}} cancelado automaticamente - {{site.name}}',
                'body' => '<h2>Ola, {{user.name}}!</h2>
<p>Seu pedido <strong>#{{order.id}}</strong> foi cancelado automaticamente porque o pagamento nao foi identificado dentro do prazo de <strong>{{order.cancel_after_hours}} horas</strong>.</p>
<p><strong>Valor:</strong> {{order.total}}<br><strong>Compra feita em:</strong> {{order.created_at}}<br><strong>Cancelado em:</strong> {{cancellation.cancelled_at}}</p>
<p>{{order.items_html}}</p>
<p>Se ainda tiver interesse, acesse a plataforma e refaca a compra.</p>
<p style="text-align:center;margin:26px 0;">
    <a href="{{site.url}}" style="display:inline-block;background-color:{{site.primary_color}};color:#ffffff;padding:14px 28px;text-decoration:none;border-radius:8px;font-weight:bold;">Acessar plataforma</a>
</p>',
            ],
        ];
    }

    public function up(): void
    {
        $now = Carbon::now();

        if (Schema::hasTable('settings')) {
            foreach ($this->settings() as $key => $value) {
                $existing = DB::table('settings')->where('key', $key)->first();
                $group = $key === 'system_timezone' ? 'system' : 'cron';

                if ($existing) {
                    DB::table('settings')->where('key', $key)->update([
                        'group' => $existing->group ?: $group,
                        'updated_at' => $now,
                    ]);
                    continue;
                }

                DB::table('settings')->insert([
                        'key' => $key,
                        'value' => $value,
                        'group' => $group,
                        'updated_at' => $now,
                        'created_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('mail_templates')) {
            foreach ($this->templates() as $template) {
                $existing = DB::table('mail_templates')->where('slug', $template['slug'])->first();

                if ($existing) {
                    continue;
                }

                DB::table('mail_templates')->insert(array_merge($template, [
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        if (Schema::hasTable('scheduled_tasks')) {
            $existing = DB::table('scheduled_tasks')
                ->where('command', 'orders:send-unpaid-reminders')
                ->first();

            if (!$existing) {
                DB::table('scheduled_tasks')->insert([
                    'command' => 'orders:send-unpaid-reminders',
                    'frequency' => '*/15 * * * *',
                    'active' => true,
                    'last_run_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('scheduled_tasks')
                ->where('command', 'orders:cancel-unpaid')
                ->where('frequency', '0 * * * *')
                ->update([
                    'frequency' => '*/5 * * * *',
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->whereIn('key', array_keys($this->settings()))
                ->delete();
        }

        if (Schema::hasTable('mail_templates')) {
            DB::table('mail_templates')
                ->whereIn('slug', array_column($this->templates(), 'slug'))
                ->delete();
        }

        if (Schema::hasTable('scheduled_tasks')) {
            DB::table('scheduled_tasks')
                ->where('command', 'orders:send-unpaid-reminders')
                ->delete();
        }
    }
};
