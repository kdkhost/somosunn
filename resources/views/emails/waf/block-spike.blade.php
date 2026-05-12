@extends('emails.layouts.system')

@section('content')
<div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; width: 60px; height: 60px; border-radius: 50%; background: #fef3cd; line-height: 60px; font-size: 28px;">
        ⚠️
    </div>
</div>

<h2 style="color: #856404; font-size: 20px; margin: 0 0 16px; text-align: center;">
    Pico de Requisicoes Bloqueadas
</h2>

<p style="color: #555; font-size: 14px; margin-bottom: 20px;">
    O Firewall (WAF) detectou um volume anormal de requisicoes bloqueadas no seu site.
    Isso pode indicar um ataque em andamento.
</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;">
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold; width: 40%;">Total Bloqueado</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;">{{ $alertData['count'] ?? '—' }} requisicoes</td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">Janela de Tempo</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;">{{ $alertData['window'] ?? '5' }} minutos</td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">Principal IP</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;"><code style="background:#eee;padding:2px 6px;border-radius:3px;">{{ $alertData['top_ip'] ?? '—' }}</code></td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">Data/Hora</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;">{{ $alertData['timestamp'] ?? now()->format('d/m/Y H:i:s') }}</td>
    </tr>
</table>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ url('/admin/waf') }}" style="display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px;">
        Acessar Painel WAF
    </a>
</div>

<p style="color: #888; font-size: 12px; text-align: center; margin-top: 20px;">
    Este alerta foi gerado automaticamente pelo sistema de seguranca.
</p>
@endsection
