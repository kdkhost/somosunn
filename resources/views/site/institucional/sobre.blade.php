@extends('layouts.app')

@section('title', \App\Models\SiteContent::getValue('institucional_sobre', 'title', 'Sobre a UNN - União Nacional de Networking'))

@section('content')
@php
    $fallbackBody = view('site.institucional._fallback.sobre')->render();
    $html = app(\App\Services\Site\SitePageContentService::class)->render('institucional_sobre', 'body', $fallbackBody);
@endphp

{!! $html !!}
@endsection

