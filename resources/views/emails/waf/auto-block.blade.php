@extends('emails.layouts.system')

@section('content')
<div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; width: 60px; height: 60px; border-radius: 50%; background: #d4edda; line-height: 60px; font-size: 28px;">
        🛡️
    </div>
</div>

<h2 style="color: #155724; font-size: 20px; margin: 0 0 16px; text-align: center;">
    IP Bloqueado Automaticamente
</h2>

<p style="color: #555; font-size: 14px; margin-bottom: 20px;">
    O Firewall (WAF) bloqueou automaticamente um endereco IP por acumulo de atividade suspeita.
</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;">
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold; width: 40%;">IP Bloqueado</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;"><code style="background:#fee;padding:2px 6px;border-radius:3px;color:#dc3545;">{{ $alertData['ip'] ?? '—' }}</code></td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">Eventos Detectados</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;">{{ $alertData['event_count'] ?? '—' }} em {{ $alertData['window'] ?? '15' }} min</td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">Duracao do Bloqueio</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;">{{ $alertData['duration'] ?? '24' }} horas</td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">Pais</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;">{{ $alertData['country'] ?? 'Desconhecido' }}</td>
    </tr>
    <tr>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold;">Data/Hora</td>
        <td style="padding: 10px 14px; border: 1px solid #e9ecef;">{{ $alertData['timestamp'] ?? now()->format('d/m/Y H:i:s') }}</td>
    </tr>
</table>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ url('/admin/waf/blocklist') }}" style="display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px;">
        Gerenciar IP Blocklist
    </a>
</div>

<p style="color: #888; font-size: 12px; text-align: center; margin-top: 20px;">
    Voce pode desbloquear este IP manualmente pelo painel se for um falso positivo.
</p>
@endsection
