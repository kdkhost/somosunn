@extends('layouts.app')

@php
    $cmsSlug = 'institucional_quem_somos';

    $metaImagePath = (string) \App\Models\SiteContent::getValue($cmsSlug, 'meta_image', '');
    $metaImageUrl = '';
    if (trim($metaImagePath) !== '') {
        $metaImageUrl = (str_starts_with($metaImagePath, 'http://') || str_starts_with($metaImagePath, 'https://'))
            ? $metaImagePath
            : asset('storage/' . ltrim($metaImagePath, '/'));
    }

    $twitterImagePath = (string) \App\Models\SiteContent::getValue($cmsSlug, 'twitter_image', '');
    $twitterImageUrl = '';
    if (trim($twitterImagePath) !== '') {
        $twitterImageUrl = (str_starts_with($twitterImagePath, 'http://') || str_starts_with($twitterImagePath, 'https://'))
            ? $twitterImagePath
            : asset('storage/' . ltrim($twitterImagePath, '/'));
    } elseif ($metaImageUrl !== '') {
        $twitterImageUrl = $metaImageUrl;
    }
@endphp

@section('title', \App\Models\SiteContent::getValue($cmsSlug, 'title', 'Quem Somos - Equipe UNN'))
@section('meta_title', \App\Models\SiteContent::getValue($cmsSlug, 'meta_title', ''))
@section('meta_description', \App\Models\SiteContent::getValue($cmsSlug, 'meta_description', ''))
@section('meta_keywords', \App\Models\SiteContent::getValue($cmsSlug, 'meta_keywords', ''))
@section('meta_robots', \App\Models\SiteContent::getValue($cmsSlug, 'meta_robots', ''))
@section('canonical', \App\Models\SiteContent::getValue($cmsSlug, 'canonical', ''))
@section('og_type', \App\Models\SiteContent::getValue($cmsSlug, 'og_type', ''))
@section('twitter_card', \App\Models\SiteContent::getValue($cmsSlug, 'twitter_card', ''))
@section('meta_image', $metaImageUrl)
@section('twitter_image', $twitterImageUrl)

@section('content')
    @php
        $fallbackBody = view('site.institucional._fallback.quem-somos')->render();
        $html = app(\App\Services\Site\SitePageContentService::class)->render($cmsSlug, 'body', $fallbackBody);
    @endphp

    {!! $html !!}
@endsection

