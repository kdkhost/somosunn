@component('mail::message')
# Parabéns, {{ $user->name }}!

Você concluiu o curso **{{ $course->title }}** com sucesso.

Seu certificado oficial já está disponível. Você pode baixá-lo clicando no botão abaixo ou acessando sua área de
membros.

@component('mail::button', ['url' => $url])
Baixar Certificado
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent