<?php

namespace App\Mail;

use App\Mail\Concerns\UsesMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email de alerta do WAF (ataques, auto-bloqueio, picos).
 *
 * Usa o sistema de templates personalizaveis do painel admin
 * (MailTemplate com slug 'waf_alert_*').
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 16.2, 16.3, 16.4
 */
class WafAlertMail extends Mailable
{
    use Queueable, SerializesModels, UsesMailTemplate;

    public string $alertType;
    public array $alertData;

    /**
     * @param string $alertType  block_spike|auto_block|critical_finding
     * @param array  $alertData  Dados contextuais do alerta
     */
    public function __construct(string $alertType, array $alertData = [])
    {
        $this->alertType = $alertType;
        $this->alertData = $alertData;
    }

    public function build(): self
    {
        $slug = 'waf_alert_' . $this->alertType;

        $defaults = $this->getDefaults();

        $data = [
            'alert' => $this->alertData,
            'site'  => null, // preenchido pelo trait
        ];

        return $this->buildFromTemplate($slug, $data, $defaults);
    }

    private function getDefaults(): array
    {
        return match ($this->alertType) {
            'block_spike' => [
                'name'     => 'WAF - Pico de Bloqueios',
                'category' => 'seguranca',
                'subject'  => '⚠️ [{{site.name}}] WAF: Pico de requisicoes bloqueadas',
                'body'     => '<h2>Alerta de Seguranca - WAF</h2>'
                    . '<p>O WAF detectou um <strong>pico de requisicoes bloqueadas</strong> no seu site.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Total bloqueado</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.count}}</td></tr>'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Janela</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.window}} minutos</td></tr>'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Top IP</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.top_ip}}</td></tr>'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Data/Hora</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p>Acesse o <a href="{{site.url}}/admin/waf">Painel WAF</a> para investigar.</p>',
            ],
            'auto_block' => [
                'name'     => 'WAF - IP Auto-Bloqueado',
                'category' => 'seguranca',
                'subject'  => '🛡️ [{{site.name}}] WAF: IP bloqueado automaticamente',
                'body'     => '<h2>IP Bloqueado Automaticamente</h2>'
                    . '<p>O WAF bloqueou automaticamente um IP por acumulo de eventos suspeitos.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>IP</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.ip}}</td></tr>'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Eventos</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.event_count}} em {{alert.window}} min</td></tr>'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Duracao</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.duration}} horas</td></tr>'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Pais</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.country}}</td></tr>'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Data/Hora</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p>Acesse o <a href="{{site.url}}/admin/waf/blocklist">IP Blocklist</a> para gerenciar.</p>',
            ],
            'critical_finding' => [
                'name'     => 'WAF - Ataque Critico Detectado',
                'category' => 'seguranca',
                'subject'  => '🚨 [{{site.name}}] WAF: Ataque critico detectado!',
                'body'     => '<h2 style="color:#dc3545;">Ataque Critico Detectado</h2>'
                    . '<p>O WAF detectou uma tentativa de ataque de <strong>alta severidade</strong>.</p>'
                    . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Tipo</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.attack_pattern}}</td></tr>'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>IP</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.ip}}</td></tr>'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Rota</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.route}}</td></tr>'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Risk Score</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.risk_score}}/100</td></tr>'
                    . '<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Data/Hora</strong></td><td style="padding:8px;border:1px solid #ddd;">{{alert.timestamp}}</td></tr>'
                    . '</table>'
                    . '<p>Acesse o <a href="{{site.url}}/admin/waf/events">Eventos WAF</a> para investigar.</p>',
            ],
            default => [
                'name'     => 'WAF - Alerta de Seguranca',
                'category' => 'seguranca',
                'subject'  => '[{{site.name}}] WAF: Alerta de seguranca',
                'body'     => '<h2>Alerta de Seguranca - WAF</h2><p>{{alert.message}}</p>',
            ],
        };
    }
}
