@extends('layouts.app')

@section('title', \App\Models\SiteContent::getValue('institucional_manifesto', 'title', 'Manifesto UNN - Nossa Visão'))

@section('content')
@php
    $fallbackBody = view('site.institucional._fallback.manifesto')->render();
    $html = app(\App\Services\Site\SitePageContentService::class)->render('institucional_manifesto', 'body', $fallbackBody);
@endphp

{!! $html !!}
@endsection

