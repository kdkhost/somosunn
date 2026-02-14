@extends('emails.layouts.system')

@section('content')
    <h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">
        Parabéns, {{ $user->name }}!
    </h2>

    <p style="margin: 0 0 14px 0;">
        Você concluiu {{ $itemTypeLabel }} <strong>{{ $itemTitle }}</strong> com sucesso.
    </p>

    <p style="margin: 0 0 22px 0;">
        Seu certificado oficial já está disponível. Você pode baixá-lo clicando no botão abaixo ou acessando sua área de
        membros.
    </p>

    @php
        $buttonColor = $primaryColor ?? '#1F5EDB';
    @endphp

    <p style="text-align: center; margin: 24px 0 26px 0;">
        <a href="{{ $url }}"
            style="display: inline-block; background-color: {{ $buttonColor }}; color: #ffffff; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 700;">
            Baixar Certificado
        </a>
    </p>

    <p style="margin: 0;">
        Obrigado,<br>
        {{ $siteName ?? config('app.name') }}
    </p>
@endsection
