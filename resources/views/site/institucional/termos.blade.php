@extends('layouts.app')

@section('title', $page->get('seo_title', 'Termos de Uso - UNN'))
@section('meta_description', $page->get('seo_description', 'Leia nossos termos e condições de uso da plataforma UNN.'))

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                {{ $page->get('hero_title', 'Termos de Uso') }}
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                {{ $page->get('hero_subtitle', 'Regras e diretrizes para o uso da nossa plataforma e serviços.') }}
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-4xl mx-auto bg-white rounded-[2.5rem] p-8 md:p-16 shadow-xl shadow-blue-500/5">
            <div class="prose prose-blue max-w-none text-gray-700 leading-relaxed font-medium">
                {!! $page->get('body_content', '<p>O conteúdo dos Termos de Uso está sendo carregado...</p>') !!}
            </div>
        </div>
    </section>
</div>

<style>
    .unn-title-gradient {
        background: linear-gradient(90deg, #2E3192 0%, #0071BC 60%, #29ABE2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        color: transparent;
    }
    .unn-title-max {
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }
    .prose h2 { font-weight: 800; color: #1e293b; margin-top: 2rem; margin-bottom: 1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem; font-size: 1.5rem; }
    .prose h3 { font-weight: 700; color: #334155; margin-top: 1.5rem; font-size: 1.25rem; }
    .prose p { margin-bottom: 1.25rem; }
</style>
@endsection
