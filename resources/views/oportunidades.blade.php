@extends('layouts.app')

@section('title', 'Oportunidades de Carreira - UNN')

@push('styles')
    <style>
        .unn-jobs-hero {
            background:
                radial-gradient(1200px circle at 15% 20%, rgba(255, 255, 255, 0.18) 0%, transparent 55%),
                radial-gradient(900px circle at 85% 0%, rgba(255, 255, 255, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
        }

        .unn-jobs-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(rgba(255, 255, 255, 0.35) 1px, transparent 1px),
                radial-gradient(rgba(255, 255, 255, 0.18) 1px, transparent 1px);
            background-size: 36px 36px, 64px 64px;
            background-position: 0 0, 18px 18px;
            opacity: 0.28;
            pointer-events: none;
        }

        .unn-jobs-cta {
            background: linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
        }

        .glass-tabs {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
@endpush

@section('content')
    @php
        $vagasCollection = method_exists($vagas, 'getCollection') ? $vagas->getCollection() : collect($vagas);
        $totalCount = method_exists($vagas, 'total') ? (int) $vagas->total() : $vagasCollection->count();

        $featuredJob = $vagasCollection->first();
        $otherJobs = $vagasCollection->skip(1);
    @endphp

    <div class="min-h-screen">
        <!-- Hero Section -->
        <section class="unn-jobs-hero relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/25 pointer-events-none"></div>

            <div class="px-4 md:px-12 lg:px-24 pt-10 md:pt-14 pb-14 md:pb-20 relative">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center">
                        <span
                            class="inline-flex items-center justify-center px-6 py-2 rounded-full text-sm font-bold text-white border border-white/20 bg-white/15 backdrop-blur">
                            Carreiras
                        </span>
                        <h1 class="mt-6 text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white">
                            Oportunidades UNN
                        </h1>
                        <p class="mt-3 text-white/80 text-base sm:text-lg">
                            Conecte-se com as melhores empresas da nossa comunidade
                        </p>
                    </div>

                    @if($featuredJob)
                        <div class="mt-10 md:mt-14">
                            <div
                                class="rounded-[32px] overflow-hidden border border-white/20 bg-white/10 backdrop-blur shadow-[0_40px_120px_-60px_rgba(0,0,0,0.65)]">
                                <div class="grid lg:grid-cols-2">
                                    <div
                                        class="relative min-h-[260px] md:min-h-[320px] bg-slate-900 flex items-center justify-center p-12 overflow-hidden">
                                        <div class="absolute inset-0 opacity-20"
                                            style="background-image: radial-gradient(var(--unn-azul-1) 1px, transparent 1px); background-size: 20px 20px;">
                                        </div>
                                        <div class="relative z-10 text-center">
                                            <div
                                                class="w-24 h-24 rounded-3xl bg-white/10 border border-white/20 flex items-center justify-center mx-auto mb-6 backdrop-blur">
                                                <i class="fas fa-briefcase text-4xl text-white"></i>
                                            </div>
                                            <div
                                                class="inline-flex items-center gap-2 rounded-full bg-white/15 border border-white/20 px-4 py-2 text-sm font-bold text-white backdrop-blur">
                                                <i class="fas fa-star text-yellow-400"></i> Vaga em Destaque
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-8 md:p-10 bg-white">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight">
                                                    {{ $featuredJob->title }}
                                                </h2>
                                                <p class="mt-3 text-blue-600 font-bold flex items-center gap-2">
                                                    <i class="fas fa-building text-slate-400"></i>
                                                    {{ $featuredJob->company_name ?? 'Empresa Confidencial' }}
                                                </p>
                                            </div>
                                            <span
                                                class="shrink-0 inline-flex items-center gap-2 rounded-full bg-blue-50 text-blue-700 px-4 py-2 text-xs font-black uppercase tracking-wider">
                                                {{ $featuredJob->type }}
                                            </span>
                                        </div>

                                        <p class="mt-4 text-slate-600">
                                            {{ $featuredJob->short_description ?? Str::limit(strip_tags($featuredJob->description), 180) }}
                                        </p>

                                        <div class="mt-6 grid sm:grid-cols-2 gap-3">
                                            <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Localização
                                                </p>
                                                <p class="text-slate-900 font-black mt-1 flex items-center gap-2">
                                                    <i class="fas fa-map-marker-alt text-red-500"></i>
                                                    {{ $featuredJob->location ?? 'Não informado' }}
                                                </p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Remuneração
                                                </p>
                                                <p class="text-slate-900 font-black mt-1 flex items-center gap-2">
                                                    <i class="fas fa-coins text-yellow-500"></i>
                                                    {{ $featuredJob->salary_range ?? 'A combinar' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-8 flex flex-col sm:flex-row gap-3">
                                            <a href="{{ route('jobs.public.show', $featuredJob->id) }}"
                                                class="px-8 py-4 rounded-xl font-bold btn-primary shadow-lg hover:shadow-xl transition inline-flex items-center justify-center">
                                                Ver Detalhes da Vaga
                                            </a>
                                            <a href="/cadastro-curriculo"
                                                class="px-8 py-4 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center">
                                                Enviar Currículo
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- Filtros Section -->
        <section class="bg-white border-b border-slate-100 sticky top-[72px] z-30 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <form method="GET" action="" class="flex flex-wrap items-center justify-center gap-3">
                    <div class="relative flex-1 min-w-[200px] max-w-xs">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="area" placeholder="Área ou Cargo..." value="{{ request('area') }}"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div class="relative flex-1 min-w-[150px] max-w-[200px]">
                        <i class="fas fa-map-marker-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="local" placeholder="Local..." value="{{ request('local') }}"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div class="relative flex-1 min-w-[150px] max-w-[200px]">
                        <i class="fas fa-building absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="empresa" placeholder="Empresa..." value="{{ request('empresa') }}"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <button type="submit"
                        class="btn-primary text-white px-6 py-3 rounded-xl font-bold text-sm shadow-md hover:shadow-lg transition">
                        Filtrar
                    </button>
                    @if(request()->anyFilled(['area', 'local', 'empresa', 'tipo']))
                        <a href="{{ url()->current() }}"
                            class="px-4 py-3 rounded-xl font-bold text-sm text-slate-500 hover:bg-slate-50 transition">
                            Limpar
                        </a>
                    @endif
                </form>
            </div>
        </section>

        <!-- Lista de Vagas -->
        <section id="lista-vagas"
            class="bg-gradient-to-br from-slate-50 to-blue-50 py-12 md:py-16 px-4 md:px-12 lg:px-24 scroll-mt-28">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold"
                            style="background: var(--unn-azul-1); color: white">
                            <i class="fas fa-briefcase"></i> Oportunidades
                        </span>
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 mt-4">Vagas Recentes</h2>
                        <p class="text-gray-600 mt-2 max-w-2xl">
                            Explore as novas posições abertas em nossa rede de parceiros exclusivos.
                        </p>
                    </div>
                    <span
                        class="text-sm font-bold text-slate-500 bg-white/50 backdrop-blur px-4 py-2 rounded-full border border-slate-200">
                        {{ $totalCount }} {{ $totalCount === 1 ? 'vaga encontrada' : 'vagas encontradas' }}
                    </span>
                </div>

                @if($vagasCollection->isEmpty())
                    <div class="bg-white rounded-[32px] shadow-xl p-16 text-center border border-slate-100">
                        <div class="w-20 h-20 rounded-3xl bg-slate-50 flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-briefcase text-4xl text-slate-300"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-900 mb-2">Nenhuma vaga encontrada</h4>
                        <p class="text-slate-600 max-w-md mx-auto">Não encontramos vagas com os filtros aplicados. Tente limpar
                            os filtros ou volte mais tarde.</p>
                    </div>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($otherJobs as $vaga)
                            <div
                                class="bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group overflow-hidden">
                                <div class="px-8 pt-8 pb-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <span
                                            class="px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest border border-blue-100">
                                            {{ $vaga->type ?? 'Integral' }}
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-400">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            {{ $vaga->created_at ? $vaga->created_at->format('d/m/Y') : '' }}
                                        </span>
                                    </div>
                                    <h3
                                        class="text-xl font-black text-slate-900 mb-1 group-hover:text-blue-600 transition-colors line-clamp-2 min-h-[3.5rem]">
                                        {{ $vaga->title }}
                                    </h3>
                                    <p class="text-blue-600 font-bold text-sm mb-4">
                                        {{ $vaga->company_name ?? 'Empresa Confidencial' }}
                                    </p>

                                    <div class="space-y-2 mb-6">
                                        <div class="flex items-center gap-2 text-xs text-slate-500">
                                            <i class="fas fa-map-marker-alt text-slate-300 w-4"></i>
                                            {{ $vaga->location ?? 'Não informado' }}
                                        </div>
                                        @if($vaga->salary_range)
                                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                                <i class="fas fa-coins text-slate-300 w-4"></i> {{ $vaga->salary_range }}
                                            </div>
                                        @endif
                                    </div>

                                    <p class="text-slate-500 text-sm line-clamp-3 mb-8">
                                        {{ $vaga->short_description ?? Str::limit(strip_tags($vaga->description), 140) }}
                                    </p>
                                </div>

                                <div class="mt-auto px-8 pb-8">
                                    <a href="{{ route('jobs.public.show', $vaga->id) }}"
                                        class="w-full py-4 bg-slate-50 hover:btn-primary border border-slate-100 hover:border-transparent text-slate-900 hover:text-white rounded-2xl font-bold text-center transition-all block">
                                        Ver Detalhes
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-12 flex justify-center">
                        {{ $vagas->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>
        </section>

        <!-- Informative Section -->
        <section class="py-12 md:py-20 px-4 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12 md:mb-16">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold"
                        style="background: var(--unn-azul-1); color: white">
                        <i class="fas fa-question-circle"></i> Ajuda
                    </span>
                    <h2 class="mt-4 text-3xl sm:text-4xl font-black text-slate-900">Como funciona a candidatura</h2>
                    <p class="mt-2 text-slate-600 max-w-2xl mx-auto">
                        Apoiamos sua jornada do início ao fim. Veja como é fácil se conectar com novas oportunidades.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 transition hover:shadow-lg">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-xl mb-6 shadow-lg"
                            style="background: var(--unn-azul-1)">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 mb-3">Cadastro de Perfil</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Mantenha seus dados e currículo atualizados no seu portal do membro para aumentar suas chances.
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 transition hover:shadow-lg">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-xl mb-6 shadow-lg"
                            style="background: var(--unn-azul-2)">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 mb-3">Envio Direto</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Candidate-se às vagas de seu interesse com apenas um clique. Seus dados são enviados com
                            segurança.
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 transition hover:shadow-lg">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-xl mb-6 shadow-lg"
                            style="background: var(--unn-azul-3)">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 mb-3">Feedback e Networking</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Acompanhe o status e conecte-se com recrutadores através da nossa rede social interna.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="unn-jobs-cta py-12 md:py-20 px-4 md:px-12 lg:px-24 text-white">
            <div class="max-w-6xl mx-auto">
                <div
                    class="rounded-[40px] border border-white/10 bg-white/10 backdrop-blur-xl p-10 md:p-16 text-center relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10"
                        style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 32px 32px;">
                    </div>
                    <div class="relative z-10">
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-black mb-6">Impulsione sua Carreira</h2>
                        <p class="text-white/80 text-lg mb-10 max-w-2xl mx-auto">
                            Não encontrou a vaga ideal hoje? Cadastre seu currículo em nosso banco de talentos e seja
                            notificado por empresas parceiras.
                        </p>
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <a href="/cadastro-curriculo"
                                class="px-10 py-5 rounded-2xl font-black bg-white text-blue-700 hover:bg-blue-50 shadow-2xl transition inline-flex items-center gap-3">
                                <i class="fas fa-file-upload"></i> Cadastrar Currículo
                            </a>
                            <a href="{{ route('premium') }}"
                                class="px-10 py-5 rounded-2xl font-black border-2 border-white/20 hover:bg-white/10 transition inline-flex items-center gap-3">
                                <i class="fas fa-crown"></i> Torne-se Premium
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection