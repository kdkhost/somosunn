@extends('layouts.app')

@section('title', \App\Models\SiteContent::getValue('institucional_valores', 'title', 'Nossos Valores - UNN'))

@section('content')
@php
    $fallbackBody = view('site.institucional._fallback.valores')->render();
    $html = app(\App\Services\Site\SitePageContentService::class)->render('institucional_valores', 'body', $fallbackBody);
@endphp

{!! $html !!}
@endsection

