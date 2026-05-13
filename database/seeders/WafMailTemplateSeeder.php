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
                'name'     => 'WAF - Ataque Crítico Detectado',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '🚨 [{{site.name}}] WAF: Ataque crítico detectado!',
                'body'     => '<h2 style="color:#dc3545;">Ataque Crítico Detectado</h2>'
                    . '<p>O Firewall (WAF) detectou uma tentativa de ataque de <strong>alta severidade</strong> contra o seu site.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;width:40%;">Tipo de Ataque</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.attack_pattern}}</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">IP Atacante</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Rota Alvo</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.route}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Risk Score</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><strong style="color:#dc3545;">{{alert.risk_score}}/100</strong></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p><a href="{{site.url}}/admin/waf/events" style="display:inline-block;padding:12px 24px;background:#dc3545;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Investigar Evento</a></p>',
                'is_active' => true,
            ],
            [
                'slug'     => 'waf_alert_block_spike',
                'name'     => 'WAF - Pico de Bloqueios',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '⚠️ [{{site.name}}] WAF: Pico de requisições bloqueadas',
                'body'     => '<h2 style="color:#856404;">Pico de Requisições Bloqueadas</h2>'
                    . '<p>O Firewall (WAF) detectou um volume anormal de requisições bloqueadas no seu site.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;width:40%;">Total Bloqueado</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.count}} requisições</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Janela de Tempo</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.window}} minutos</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Principal IP</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.top_ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p><a href="{{site.url}}/admin/waf" style="display:inline-block;padding:12px 24px;background:#1F5EDB;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Acessar Painel WAF</a></p>',
                'is_active' => true,
            ],
            [
                'slug'     => 'waf_alert_auto_block',
                'name'     => 'WAF - IP Bloqueado Automaticamente',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '🛡️ [{{site.name}}] WAF: IP bloqueado automaticamente',
                'body'     => '<h2 style="color:#155724;">IP Bloqueado Automaticamente</h2>'
                    . '<p>O Firewall (WAF) bloqueou automaticamente um endereço IP por acúmulo de atividade suspeita.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;width:40%;">IP Bloqueado</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Eventos Detectados</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.event_count}} em {{alert.window}} min</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Duração do Bloqueio</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.duration}} horas</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">País</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.country}}</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p><a href="{{site.url}}/admin/waf/blocklist" style="display:inline-block;padding:12px 24px;background:#1F5EDB;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Gerenciar IP Blocklist</a></p>',
                'is_active' => true,
            ],
            [
                'slug'     => 'waf_alert_sqli_detected',
                'name'     => 'WAF - Injeção SQL Detectada',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '🔴 [{{site.name}}] WAF: Tentativa de injeção SQL bloqueada',
                'body'     => '<h2 style="color:#dc3545;">Tentativa de Injeção SQL Bloqueada</h2>'
                    . '<p>O WAF bloqueou uma tentativa de <strong>SQL Injection</strong> contra o seu site.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">IP</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Rota</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.route}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Score</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.risk_score}}/100</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p><a href="{{site.url}}/admin/waf/events" style="display:inline-block;padding:12px 24px;background:#dc3545;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Ver Eventos</a></p>',
                'is_active' => true,
            ],
            [
                'slug'     => 'waf_alert_xss_detected',
                'name'     => 'WAF - XSS Detectado',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '🟠 [{{site.name}}] WAF: Tentativa de XSS bloqueada',
                'body'     => '<h2 style="color:#fd7e14;">Tentativa de XSS Bloqueada</h2>'
                    . '<p>O WAF bloqueou uma tentativa de <strong>Cross-Site Scripting (XSS)</strong>.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">IP</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Rota</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.route}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p><a href="{{site.url}}/admin/waf/events" style="display:inline-block;padding:12px 24px;background:#fd7e14;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Ver Eventos</a></p>',
                'is_active' => true,
            ],
            [
                'slug'     => 'waf_alert_brute_force',
                'name'     => 'WAF - Força Bruta Detectada',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '🔒 [{{site.name}}] WAF: Tentativa de força bruta detectada',
                'body'     => '<h2 style="color:#6f42c1;">Tentativa de Força Bruta Detectada</h2>'
                    . '<p>O WAF detectou múltiplas tentativas de login falhadas de um mesmo IP.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">IP</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Tentativas</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.event_count}}</td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p><a href="{{site.url}}/admin/waf/blocklist" style="display:inline-block;padding:12px 24px;background:#6f42c1;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Gerenciar Blocklist</a></p>',
                'is_active' => true,
            ],
            [
                'slug'     => 'waf_alert_rce_detected',
                'name'     => 'WAF - Execução Remota de Código (RCE)',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '💀 [{{site.name}}] WAF: Tentativa de RCE bloqueada!',
                'body'     => '<h2 style="color:#dc3545;">Tentativa de Execução Remota de Código (RCE)</h2>'
                    . '<p><strong>ALERTA MÁXIMO:</strong> O WAF bloqueou uma tentativa de executar comandos no servidor.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">IP</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Rota</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.route}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Score</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><strong style="color:#dc3545;">{{alert.risk_score}}/100</strong></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p style="background:#f8d7da;padding:12px;border-radius:8px;color:#721c24;"><strong>Recomendação:</strong> Bloqueie este IP permanentemente e investigue se houve comprometimento.</p>'
                    . '<p><a href="{{site.url}}/admin/waf/events" style="display:inline-block;padding:12px 24px;background:#dc3545;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Investigar Agora</a></p>',
                'is_active' => true,
            ],
            [
                'slug'     => 'waf_alert_upload_malicious',
                'name'     => 'WAF - Upload Malicioso Bloqueado',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '📎 [{{site.name}}] WAF: Upload malicioso bloqueado',
                'body'     => '<h2 style="color:#dc3545;">Upload Malicioso Bloqueado</h2>'
                    . '<p>O WAF bloqueou uma tentativa de enviar um arquivo potencialmente perigoso.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">IP</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Rota</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.route}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p><a href="{{site.url}}/admin/waf/events" style="display:inline-block;padding:12px 24px;background:#dc3545;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Ver Eventos</a></p>',
                'is_active' => true,
            ],
            [
                'slug'     => 'waf_alert_path_traversal',
                'name'     => 'WAF - Path Traversal Bloqueado',
                'category' => 'seguranca',
                'locale'   => 'pt-BR',
                'subject'  => '📂 [{{site.name}}] WAF: Tentativa de path traversal bloqueada',
                'body'     => '<h2 style="color:#dc3545;">Path Traversal Bloqueado</h2>'
                    . '<p>O WAF bloqueou uma tentativa de acessar arquivos fora do diretório permitido.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">IP</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.ip}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Rota</td><td style="padding:10px 14px;border:1px solid #e9ecef;"><code>{{alert.route}}</code></td></tr>'
                    . '<tr><td style="padding:10px 14px;border:1px solid #e9ecef;background:#f8f9fa;font-weight:bold;">Data/Hora</td><td style="padding:10px 14px;border:1px solid #e9ecef;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p><a href="{{site.url}}/admin/waf/events" style="display:inline-block;padding:12px 24px;background:#dc3545;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Ver Eventos</a></p>',
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
