@extends('emails.layouts.system')

@section('content')
    <h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">
        Bem-vindo(a), {{ $user->name }}!
    </h2>

    <p style="margin: 0 0 14px 0;">
        Sua conta foi criada com sucesso e você já pode acessar a plataforma.
    </p>

    <p style="margin: 0 0 22px 0;">
        Aproveite para se conectar com outros membros, acessar conteúdos e explorar tudo o que a UNN oferece.
    </p>

    @php
        $buttonColor = $primaryColor ?? '#1F5EDB';
        $loginUrl = route('login');
    @endphp

    <p style="text-align: center; margin: 24px 0 26px 0;">
        <a href="{{ $loginUrl }}"
            style="display: inline-block; background-color: {{ $buttonColor }}; color: #ffffff; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 700;">
            Acessar minha conta
        </a>
    </p>

    <p style="margin: 0;">
        Obrigado,<br>
        {{ $siteName ?? config('app.name') }}
    </p>
@endsection

