@extends('layouts.app')

@section('title', $job->title)

@section('content')
    <style>
        .unn-jobs-hero {
            background: radial-gradient(circle at 70% 30%, rgba(31, 94, 219, 0.15) 0%, transparent 60%),
                radial-gradient(circle at 30% 70%, rgba(23, 127, 214, 0.1) 0%, transparent 50%),
                #ffffff;
            position: relative;
        }

        .unn-jobs-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%231f5edb' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>

    <div class="min-h-screen bg-slate-50">
        {{-- Hero Section --}}
        <section class="unn-jobs-hero pt-32 pb-20 border-b border-slate-100 overflow-hidden">
            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-4xl mx-auto">
                    {{-- Breadcrumb --}}
                    <nav
                        class="flex items-center gap-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-[0.2em] mb-8">
                        <a href="{{ route('home') }}" class="hover:text-unn-azul-1 transition-colors">Início</a>
                        <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
                        <a href="{{ route('jobs.public.index') }}"
                            class="hover:text-unn-azul-1 transition-colors">Oportunidades</a>
                        <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
                        <span class="text-slate-900">Detalhes</span>
                    </nav>

                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-4">
                                <span
                                    class="px-4 py-1 rounded-full bg-unn-azul-1/10 text-unn-azul-1 text-[10px] font-black uppercase tracking-widest border border-unn-azul-1/10">
                                    {{ $job->type }}
                                </span>
                                @if($job->is_demo)
                                    <span
                                        class="px-4 py-1 rounded-full bg-amber-500/10 text-amber-600 text-[10px] font-black uppercase tracking-widest border border-amber-500/10">
                                        <i class="fas fa-flask mr-1"></i> Demonstração
                                    </span>
                                @endif
                                <span
                                    class="px-4 py-1 rounded-full bg-emerald-500/10 text-emerald-600 text-[10px] font-black uppercase tracking-widest border border-emerald-500/10">
                                    Vaga Aberta
                                </span>
                                @if(Auth::check() && Auth::user()->isAdmin())
                                    <a href="{{ route('admin.jobs.edit', $job) }}"
                                        class="px-4 py-1 rounded-full bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-black transition-colors">
                                        <i class="fas fa-edit mr-1"></i> Editar no Painel
                                    </a>
                                @endif
                            </div>
                            <h1 class="text-4xl md:text-6xl font-black text-slate-900 leading-[1.1] mb-6">
                                {{ $job->title }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-6 text-slate-500 font-bold">
                                <div class="flex items-center gap-2 text-blue-600">
                                    <i class="fas fa-building text-lg"></i>
                                    <span>{{ $job->company_name ?? 'Empresa Confidencial' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-location-dot"></i>
                                    <span>{{ $job->location ?? 'Remoto / Não informado' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-check"></i>
                                    <span>Publicada em {{ $job->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Decorative Elements --}}
            <div
                class="absolute top-1/2 right-0 -translate-y-1/2 w-[500px] h-[500px] bg-unn-azul-1/5 rounded-full blur-[120px] -mr-64 opacity-60">
            </div>
            <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-unn-azul-2/5 rounded-full blur-[100px] -mb-32 opacity-40">
            </div>
        </section>

        {{-- Content Section --}}
        <section class="py-20 relative -mt-10 lg:-mt-16 z-20">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

                        {{-- Main Content Card --}}
                        <div class="lg:col-span-8">
                            <div
                                class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.06)] border border-slate-100 mb-10">

                                <div
                                    class="prose prose-slate max-w-none prose-headings:font-black prose-headings:text-slate-900 prose-p:text-slate-600 prose-p:leading-relaxed prose-p:font-medium prose-strong:text-slate-900 prose-ul:list-disc prose-li:text-slate-600">

                                    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-slate-50">
                                        <div class="w-2 h-10 btn-primary rounded-full"></div>
                                        <h2 class="text-2xl font-black text-slate-900 m-0">Sobre a Oportunidade</h2>
                                    </div>

                                    <div class="mb-12">
                                        {!! $job->description !!}
                                    </div>

                                    @if($job->requirements)
                                        <div class="flex items-center gap-3 mb-8 pb-4 border-b border-slate-50">
                                            <div class="w-2 h-10 bg-emerald-500 rounded-full"></div>
                                            <h2 class="text-2xl font-black text-slate-900 m-0">Requisitos</h2>
                                        </div>
                                        <div class="mb-12">
                                            {!! $job->requirements !!}
                                        </div>
                                    @endif

                                    @if($job->benefits)
                                        <div class="flex items-center gap-3 mb-8 pb-4 border-b border-slate-50">
                                            <div class="w-2 h-10 bg-purple-500 rounded-full"></div>
                                            <h2 class="text-2xl font-black text-slate-900 m-0">Benefícios</h2>
                                        </div>
                                        <div class="mb-12">
                                            {!! $job->benefits !!}
                                        </div>
                                    @endif
                                </div>

                                <div
                                    class="mt-16 pt-10 border-t border-slate-50 flex flex-col sm:flex-row items-center justify-between gap-6">
                                    <div class="text-slate-400 text-sm font-bold flex items-center gap-2">
                                        <i class="fas fa-info-circle text-blue-500"></i>
                                        Atenção: Candidaturas limitadas.
                                    </div>
                                    <div class="flex items-center gap-4">
                                        {{-- Share Button --}}
                                        <button
                                            onclick="navigator.share({title: '{{ $job->title }}', url: window.location.href})"
                                            class="w-12 h-12 rounded-xl bg-slate-50 text-slate-400 hover:text-unn-azul-1 hover:bg-unn-azul-1/5 flex items-center justify-center transition-all">
                                            <i class="fas fa-share-nodes"></i>
                                        </button>

                                        @if(Auth::check())
                                            <a href="{{ route('panel.jobs.show', $job) }}"
                                                class="px-8 py-4 btn-primary text-white rounded-2xl font-black shadow-xl shadow-blue-500/20 transition-all hover:scale-105 active:scale-95 flex items-center gap-3">
                                                Candidatar-se Agora <i class="fas fa-arrow-right text-sm"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('login') }}"
                                                class="px-8 py-4 bg-slate-900 hover:bg-black text-white rounded-2xl font-black shadow-xl shadow-slate-900/20 transition-all hover:scale-105 active:scale-95 flex items-center gap-3">
                                                Entrar para Candidatar <i class="fas fa-arrow-right text-sm"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Final CTA Area --}}
                            <div class="bg-unn-azul-1 rounded-[2.5rem] p-10 text-white relative overflow-hidden group">
                                <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
                                    <div class="flex-1 text-center md:text-left">
                                        <h3 class="text-2xl font-black mb-2">Quer saber mais?</h3>
                                        <p class="text-blue-100 font-bold opacity-80 leading-relaxed">
                                            Descubra como a UNN pode acelerar sua carreira no mercado tech através de
                                            mentorias e cursos exclusivos.
                                        </p>
                                    </div>
                                    <a href="{{ route('premium') }}"
                                        class="px-8 py-4 bg-white text-unn-azul-3 rounded-2xl font-black shadow-2xl hover:bg-blue-50 transition-all">
                                        Conhecer Planos
                                    </a>
                                </div>
                                {{-- Background shapes for CTA --}}
                                <div
                                    class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl group-hover:bg-white/20 transition-all duration-700">
                                </div>
                            </div>
                        </div>

                        {{-- Sidebar Area --}}
                        <div class="lg:col-span-4 space-y-8">
                            {{-- Quick Info Card --}}
                            <div class="bg-white rounded-[2rem] p-8 shadow-2xl shadow-slate-200/50 border border-slate-100">
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Informações
                                    Rápidas</h4>

                                <div class="space-y-6">
                                    <div class="flex items-start gap-4">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p
                                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-tight mb-1">
                                                Remuneração</p>
                                            <p class="font-extrabold text-slate-900 leading-tight">
                                                {{ $job->salary_range ?? 'A combinar' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-4">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 flex-shrink-0">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p
                                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">
                                                Carga Horária</p>
                                            <p class="font-extrabold text-slate-900">{{ $job->type }}</p>
                                        </div>
                                    </div>
                                    @if($job->is_demo)
                                        <div class="flex items-start gap-4">
                                            <div
                                                class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 flex-shrink-0">
                                                <i class="fas fa-flask"></i>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">
                                                    Tipo de Vaga</p>
                                                <p class="font-extrabold text-slate-900 text-sm">Apenas Demonstração</p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex items-start gap-4">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 flex-shrink-0">
                                            <i class="fas fa-shield-halved"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p
                                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-tight mb-1">
                                                Nível</p>
                                            <p class="font-extrabold text-slate-900 leading-tight">Não Informado</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8 pt-8 border-t border-slate-50">
                                    <a href="/contato"
                                        class="text-xs font-black text-blue-600 hover:text-blue-700 uppercase tracking-widest flex items-center justify-center gap-2">
                                        Comunicar problema <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>

                            {{-- Why UNN Card --}}
                            <div class="bg-slate-900 rounded-[2rem] p-8 text-white relative overflow-hidden">
                                <i class="fas fa-rocket absolute -right-4 -bottom-4 text-7xl text-white/5 -rotate-12"></i>
                                <h4 class="text-xs font-black text-blue-400 uppercase tracking-widest mb-4">Por que a UNN?
                                </h4>
                                <p class="text-sm font-bold text-slate-300 leading-relaxed mb-6">
                                    Somos a ponte entre grandes talentos e empresas inovadoras. Nosso selo de qualidade
                                    garante as melhores oportunidades.
                                </p>
                                <ul class="space-y-3">
                                    <li class="flex items-center gap-2 text-xs font-bold text-slate-400">
                                        <i class="fas fa-check text-blue-400"></i> Vagas verificadas
                                    </li>
                                    <li class="flex items-center gap-2 text-xs font-bold text-slate-400">
                                        <i class="fas fa-check text-blue-400"></i> Suporte ao candidato
                                    </li>
                                    <li class="flex items-center gap-2 text-xs font-bold text-slate-400">
                                        <i class="fas fa-check text-blue-400"></i> Networking exclusivo
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection