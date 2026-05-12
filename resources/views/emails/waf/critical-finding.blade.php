@extends('emails.layouts.system')

@section('content')
<div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; width: 60px; height: 60px; border-radius: 50%; background: #f8d7da; line-height: 60px; font-size: 28px;">
        🚨
    </div>
</div>

<h2 style="color: #721c24; font-size: 20px; margin: 0 0 16px; text-align: center;">
    Ataque Critico Detectado
</h2>

<p style="color: #555; font-size: 14px; margin-bottom: 20px;">
    O Firewall (WAF) detectou uma tentativa de ataque de <strong>alta severidade</strong> contra o seu site.
    Acao imediata pode ser necessaria.
</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;">
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold; width: 40%;">Tipo de Ataque</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;">
            <span style="background: #dc3545; color: #fff; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">{{ $alertData['attack_pattern'] ?? '—' }}</span>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">IP Atacante</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;"><code style="background:#fee;padding:2px 6px;border-radius:3px;color:#dc3545;">{{ $alertData['ip'] ?? '—' }}</code></td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">Rota Alvo</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;"><code style="background:#eee;padding:2px 6px;border-radius:3px;">{{ $alertData['route'] ?? '—' }}</code></td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">Risk Score</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;">
            <strong style="color: #dc3545;">{{ $alertData['risk_score'] ?? '—' }}/100</strong>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">Decisao</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;">
            <span style="background: #dc3545; color: #fff; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">BLOQUEADO</span>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">Data/Hora</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;">{{ $alertData['timestamp'] ?? now()->format('d/m/Y H:i:s') }}</td>
    </tr>
</table>

<div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 14px; margin: 20px 0;">
    <p style="margin: 0; font-size: 13px; color: #856404;">
        <strong>Recomendacao:</strong> Verifique o evento no painel WAF. Se o IP for reincidente, considere bloquea-lo permanentemente.
    </p>
</div>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ url('/admin/waf/events') }}" style="display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px;">
        Investigar Evento
    </a>
</div>

<p style="color: #888; font-size: 12px; text-align: center; margin-top: 20px;">
    Este alerta foi gerado automaticamente pelo Firewall (WAF) da plataforma.
</p>
@endsection
