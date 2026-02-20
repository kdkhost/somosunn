@extends('site.layouts.app')

@section('title', $job->title)

@section('content')
    <div class="py-20 bg-slate-50">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-8">
                    <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Início</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <a href="{{ route('jobs.public.index') }}" class="hover:text-blue-600 transition-colors">Vagas</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-slate-900">Detalhes</span>
                </nav>

                <div
                    class="bg-white rounded-[3rem] p-8 md:p-12 shadow-2xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
                    {{-- Decorative element --}}
                    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full blur-3xl -mt-32 -mr-32 opacity-50">
                    </div>

                    <div class="relative z-10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                            <div>
                                <span
                                    class="px-4 py-1.5 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider mb-4 inline-block">
                                    {{ $job->type }}
                                </span>
                                <h1 class="text-3xl md:text-5xl font-black text-slate-900 mb-2">{{ $job->title }}</h1>
                                <p class="text-xl text-blue-600 font-bold">
                                    {{ $job->company_name ?? 'Empresa Confidencial' }}</p>
                            </div>

                            @if(Auth::check())
                                <a href="{{ route('panel.jobs.show', $job) }}"
                                    class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold shadow-xl shadow-blue-500/20 transition-all text-center">
                                    Candidatar-se no Painel
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="px-8 py-4 bg-slate-900 hover:bg-black text-white rounded-2xl font-bold shadow-xl shadow-slate-900/20 transition-all text-center">
                                    Faça Login para Candidatar-se
                                </a>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100 transition-colors">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Localização
                                </p>
                                <p class="font-bold text-slate-900">{{ $job->location ?? 'Remoto / Não informado' }}</p>
                            </div>
                            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100 transition-colors">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Remuneração
                                </p>
                                <p class="font-bold text-slate-900">{{ $job->salary_range ?? 'A combinar' }}</p>
                            </div>
                            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-100 transition-colors">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Publicada em
                                </p>
                                <p class="font-bold text-slate-900">{{ $job->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>

                        <div class="space-y-12">
                            <section>
                                <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                                    <div class="w-2 h-8 bg-blue-600 rounded-full"></div>
                                    Sobre a Oportunidade
                                </h2>
                                <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                                    {!! $job->description !!}
                                </div>
                            </section>

                            @if($job->requirements)
                                <section>
                                    <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                                        <div class="w-2 h-8 bg-emerald-500 rounded-full"></div>
                                        Requisitos e Qualificações
                                    </h2>
                                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                                        {!! $job->requirements !!}
                                    </div>
                                </section>
                            @endif

                            @if($job->benefits)
                                <section>
                                    <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                                        <div class="w-2 h-8 bg-purple-500 rounded-full"></div>
                                        Benefícios e Vantagens
                                    </h2>
                                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                                        {!! $job->benefits !!}
                                    </div>
                                </section>
                            @endif
                        </div>

                        <div
                            class="mt-16 pt-10 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-6">
                            <div class="text-slate-500 text-sm font-medium">
                                Interessado nesta vaga? Não perca tempo, as vagas são limitadas.
                            </div>
                            @if(Auth::check())
                                <a href="{{ route('panel.jobs.show', $job) }}"
                                    class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold shadow-xl shadow-blue-500/20 transition-all">
                                    Quero me candidatar
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="px-8 py-4 bg-slate-900 hover:bg-black text-white rounded-2xl font-bold shadow-xl shadow-slate-900/20 transition-all">
                                    Entrar para me candidatar
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection