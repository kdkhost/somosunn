<?php

namespace Database\Seeders;

use App\Models\MailTemplate;
use Illuminate\Database\Seeder;

/**
 * Registra os templates de email do WAF na tabela mail_templates
 * para que aparecam no painel de edicao de emails do admin.
 */
class WafMailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'slug'     => 'waf_alert_critical_finding',
                'name'     => 'WAF - Ataque Critico Detectado',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '🚨 [{{site.name}}] WAF: Ataque critico detectado!',
                'body'     => '<h2 style="color:#dc3545;">Ataque Critico Detectado</h2>'
                    . '<p>O Firewall (WAF) detectou uma tentativa de ataque de <strong>alta severidade</strong> contra o seu site.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;width:40%;">Tipo de Ataque</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.attack_pattern}}</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">IP Atacante</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Rota Alvo</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.route}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Risk Score</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><strong style="color:#dc3545;">{{alert.risk_score}}/100</strong></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p><a href="{{site.url}}/admin/waf/events" style="display:inline-block;padding:12px 24px;background:#dc3545;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Investigar Evento</a></p>'
                    . '<p style="color:#888;font-size:12px;">Este alerta foi gerado automaticamente pelo Firewall (WAF).</p>',
                'is_active' => true,
            ],
            [
                'slug'     => 'waf_alert_block_spike',
                'name'     => 'WAF - Pico de Bloqueios',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '⚠️ [{{site.name}}] WAF: Pico de requisicoes bloqueadas',
                'body'     => '<h2 style="color:#856404;">Pico de Requisicoes Bloqueadas</h2>'
                    . '<p>O Firewall (WAF) detectou um volume anormal de requisicoes bloqueadas no seu site.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;width:40%;">Total Bloqueado</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.count}} requisicoes</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Janela de Tempo</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.window}} minutos</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Principal IP</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.top_ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p><a href="{{site.url}}/admin/waf" style="display:inline-block;padding:12px 24px;background:#1F5EDB;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Acessar Painel WAF</a></p>'
                    . '<p style="color:#888;font-size:12px;">Este alerta foi gerado automaticamente pelo sistema de seguranca.</p>',
                'is_active' => true,
            ],
            [
                'slug'     => 'waf_alert_auto_block',
                'name'     => 'WAF - IP Bloqueado Automaticamente',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '🛡️ [{{site.name}}] WAF: IP bloqueado automaticamente',
                'body'     => '<h2 style="color:#155724;">IP Bloqueado Automaticamente</h2>'
                    . '<p>O Firewall (WAF) bloqueou automaticamente um endereco IP por acumulo de atividade suspeita.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;width:40%;">IP Bloqueado</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Eventos Detectados</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.event_count}} em {{alert.window}} min</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Duracao do Bloqueio</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.duration}} horas</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Pais</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.country}}</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p><a href="{{site.url}}/admin/waf/blocklist" style="display:inline-block;padding:12px 24px;background:#1F5EDB;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Gerenciar IP Blocklist</a></p>'
                    . '<p style="color:#888;font-size:12px;">Voce pode desbloquear este IP manualmente pelo painel se for um falso positivo.</p>',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $data) {
            MailTemplate::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
