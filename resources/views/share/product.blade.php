@extends('layouts.app')

@section('title', $metaTitle ?? 'Marketplace UNN')
@section('meta_title', $metaTitle ?? 'Marketplace UNN')
@section('meta_description', $metaDescription ?? '')
@section('meta_image', $metaImage ?? '')
@section('canonical', $canonical ?? url()->current())
@section('og_type', 'product')

@section('content')
@php
    $metaTitle = (string) ($metaTitle ?? 'Marketplace UNN');
    $metaDescription = (string) ($metaDescription ?? '');
    $metaImage = (string) ($metaImage ?? '');
    $canonical = (string) ($canonical ?? url()->current());
    $title = (string) ($title ?? '');
    $label = (string) ($label ?? 'Produto');
    $targetUrl = (string) ($targetUrl ?? url('/'));
@endphp

<div class="min-h-screen bg-slate-50 pt-24 pb-16 px-4 md:px-12 lg:px-24">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 md:p-10 text-center">
            <div class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-black text-white shadow"
                style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                <i class="fas fa-link"></i> Link do Marketplace
            </div>

            <h1 class="mt-5 text-2xl md:text-3xl font-black text-slate-900">
                {{ $title !== '' ? $title : $metaTitle }}
            </h1>

            @if($metaDescription !== '')
                <p class="mt-3 text-slate-600">
                    {{ $metaDescription }}
                </p>
            @endif

            @if($metaImage !== '')
                <div class="mt-6">
                    <img src="{{ $metaImage }}" alt="{{ $label }}" class="w-full max-h-72 object-cover rounded-2xl border border-slate-100">
                </div>
            @endif

            <div class="mt-7 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ $targetUrl }}"
                    class="btn-primary text-white px-6 py-3 rounded-2xl font-black shadow-md inline-flex items-center justify-center gap-2">
                    <i class="fas fa-up-right-from-square"></i> Abrir agora
                </a>
                <button type="button"
                    class="px-6 py-3 rounded-2xl font-black border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center gap-2"
                    onclick="navigator.clipboard.writeText(@json($canonical)); toastr.success('Link copiado!');">
                    <i class="fas fa-copy"></i> Copiar link
                </button>
            </div>

            <p class="mt-6 text-xs text-slate-500">
                Você será redirecionado automaticamente em instantes.
            </p>
        </div>
    </div>
</div>

<script>
    setTimeout(function () {
        window.location.href = @json($targetUrl);
    }, 1600);
</script>
@endsection

