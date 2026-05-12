<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email de alerta do WAF personalizado.
 *
 * Usa views Blade dedicadas com o layout padrao do sistema
 * (emails.layouts.system) para manter visual consistente.
 *
 * Tipos de alerta:
 *   - critical_finding: ataque critico detectado
 *   - block_spike: pico de requisicoes bloqueadas
 *   - auto_block: IP bloqueado automaticamente
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 16.2, 16.3, 16.4
 */
class WafAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $alertType;
    public array $alertData;

    public function __construct(string $alertType, array $alertData = [])
    {
        $this->alertType = $alertType;
        $this->alertData = $alertData;
    }

    public function build(): self
    {
        $subject = $this->resolveSubject();
        $view    = $this->resolveView();

        return $this
            ->subject($subject)
            ->view($view, ['alertData' => $this->alertData]);
    }

    private function resolveSubject(): string
    {
        $siteName = (string) (\App\Models\Setting::get('app_name') ?: config('app.name', 'UNN'));

        return match ($this->alertType) {
            'critical_finding' => "🚨 [{$siteName}] WAF: Ataque critico detectado!",
            'block_spike'      => "⚠️ [{$siteName}] WAF: Pico de requisicoes bloqueadas",
            'auto_block'       => "🛡️ [{$siteName}] WAF: IP bloqueado automaticamente",
            default            => "[{$siteName}] WAF: Alerta de seguranca",
        };
    }

    private function resolveView(): string
    {
        return match ($this->alertType) {
            'critical_finding' => 'emails.waf.critical-finding',
            'block_spike'      => 'emails.waf.block-spike',
            'auto_block'       => 'emails.waf.auto-block',
            default            => 'emails.waf.block-spike',
        };
    }
}
