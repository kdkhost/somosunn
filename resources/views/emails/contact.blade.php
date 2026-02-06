@php
    $siteName = \App\Models\Setting::get('app_name') ?: config('app.name', 'UNN');
@endphp
<div style="font-family: Arial, Helvetica, sans-serif; line-height: 1.5; color: #0f172a;">
    <h2 style="margin: 0 0 12px 0;">Nova mensagem de contato — {{ $siteName }}</h2>

    <p style="margin: 0 0 6px 0;"><strong>Nome:</strong> {{ $data['name'] }}</p>
    <p style="margin: 0 0 6px 0;"><strong>E-mail:</strong> {{ $data['email'] }}</p>
    @if(!empty($data['phone']))
        <p style="margin: 0 0 6px 0;"><strong>Telefone:</strong> {{ $data['phone'] }}</p>
    @endif
    <p style="margin: 0 0 12px 0;"><strong>Assunto:</strong> {{ $data['subject'] }}</p>

    <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:14px;border-radius:10px;">
        {!! nl2br(e($data['message'])) !!}
    </div>

    <hr style="border:none;border-top:1px solid #e2e8f0;margin:18px 0;">
    <p style="font-size: 12px; color: #64748b; margin: 0;">
        IP: {{ $data['ip'] }}<br>
        User-Agent: {{ $data['userAgent'] }}
    </p>
</div>

