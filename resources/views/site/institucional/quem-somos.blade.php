@extends('layouts.app')

@section('title', \App\Models\SiteContent::getValue('institucional_quem_somos', 'title', 'Quem Somos - Equipe UNN'))

@section('content')
@php
    $fallbackBody = view('site.institucional._fallback.quem-somos')->render();
    $html = app(\App\Services\Site\SitePageContentService::class)->render('institucional_quem_somos', 'body', $fallbackBody);
@endphp

{!! $html !!}
@endsection

