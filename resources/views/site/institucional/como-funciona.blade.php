@extends('layouts.app')

@section('title', \App\Models\SiteContent::getValue('institucional_como_funciona', 'title', 'Como Funciona - UNN'))

@section('content')
@php
    $fallbackBody = view('site.institucional._fallback.como-funciona')->render();
    $html = app(\App\Services\Site\SitePageContentService::class)->render('institucional_como_funciona', 'body', $fallbackBody);
@endphp

{!! $html !!}
@endsection

