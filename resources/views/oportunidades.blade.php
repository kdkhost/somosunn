@extends('layouts.app')

@section('title', 'Oportunidades de Carreira - UNN')

@push('styles')
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-highlight: rgba(255, 255, 255, 0.15);
            --unn-accent-glow: rgba(43, 110, 250, 0.4);
            --unn-primary-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        }

        /* Hero complex background with animated blobs */
        .unn-jobs-hero {
            background-color: #020617;
            background-image: var(--unn-primary-gradient);
            position: relative;
            z-index: 1;
        }

        /* Animated Blob 1 */
        .blob-1 {
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, var(--unn-azul-1) 0%, transparent 60%);
            position: absolute;
            top: -300px;
            left: -200px;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            z-index: -1;
            animation: float-blob 20s infinite alternate ease-in-out;
            pointer-events: none;
        }

        /* Animated Blob 2 */
        .blob-2 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, #8b5cf6 0%, transparent 70%);
            position: absolute;
            bottom: -200px;
            right: -100px;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.25;
            z-index: -1;
            animation: float-blob-reverse 25s infinite alternate-reverse ease-in-out;
            pointer-events: none;
        }

        @keyframes float-blob {
            0% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(100px, 50px) scale(1.1);
            }

            100% {
                transform: translate(-50px, 100px) scale(0.9);
            }
        }

        @keyframes float-blob-reverse {
            0% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(-100px, -80px) scale(0.85);
            }

            100% {
                transform: translate(50px, -120px) scale(1.1);
            }
        }

        /* Glassmorphism Classes */
        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            box-shadow: inset 0 1px 0 0 var(--glass-highlight), 0 20px 40px -10px rgba(0, 0, 0, 0.5);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05), inset 0 1px 1px rgba(255, 255, 255, 0.8);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .dark .glass-card {
            background: rgba(15, 23, 42, 0.7);
            border-color: rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.05);
        }

        .glass-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 40px -15px rgba(0, 0, 0, 0.1), inset 0 1px 1px rgba(255, 255, 255, 0.9), 0 0 0 1px var(--unn-azul-2);
        }

        .dark .glass-card:hover {
            box-shadow: 0 25px 40px -15px rgba(0, 0, 0, 0.6), inset 0 1px 1px rgba(255, 255, 255, 0.1), 0 0 0 1px var(--unn-azul-1);
        }

        /* Modern Gradient Text */
        .text-gradient-primary {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(135deg, #fff 0%, #cbd5e1 100%);
        }

        .text-gradient-accent {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
        }

        /* Advanced Hover Utilities */
        .hover-lift {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 30px -10px var(--unn-accent-glow);
        }

        /* Custom Filter Controls */
        .filter-input {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 100px;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .filter-input:focus-within {
            background: #fff;
            border-color: var(--unn-azul-1);
            box-shadow: 0 0 0 4px rgba(43, 110, 250, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0);
        }

        /* Grid Background Pattern */
        .bg-grid-pattern {
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .dark .bg-grid-pattern {
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
        }

        /* Premium Button */
        .btn-premium {
            background: linear-gradient(135deg, var(--unn-azul-1) 0%, #4338ca 100%);
            color: white;
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .btn-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #4338ca 0%, #8b5cf6 100%);
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
        }

        .btn-premium:hover::before {
            opacity: 1;
        }
    </style>
@endpush

@section('content')
    @php
        $vagasCollection = method_exists($vagas, 'getCollection') ? $vagas->getCollection() : collect($vagas);
        $totalCount = method_exists($vagas, 'total') ? (int) $vagas->total() : $vagasCollection->count();
        $featuredJob = $vagasCollection->first();
        $otherJobs = $vagasCollection;
        if (request()->page == 1 || !request()->has('page')) {
            $otherJobs = $vagasCollection->skip(1); // skip only on pg 1 if featured is shown
        }
    @endphp

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-500 overflow-hidden">

        <!-- Premium Hero Section -->
        <section class="unn-jobs-hero pt-24 pb-32 md:pb-48 xl:pb-56 rounded-b-[3rem] shadow-2xl relative">
            <div class="blob-1"></div>
            <div class="blob-2"></div>

            <div
                class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+CjxwYXRoIGQ9Ik0wIDBoNDB2NDBIMHoiIGZpbGw9Im5vbmUiLz4KPHBhdGggZD0iTTAgMTBoNDBNMTAgMHY0ME0wIDIwaDQwTTIwIDB2NDBNMCAzMGg0ME0zMCAwdjQwIiBzdHJva2U9InJnYmEoMjU1LDI1NSwyNTUsMC4wMykiIHN0cm9rZS13aWR0aD0iMSIvPgo8L3N2Zz4=')] opacity-50">
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-panel text-blue-200 text-xs font-black tracking-widest uppercase mb-8 transform hover:scale-105 transition-transform cursor-default">
                    <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                    Carreiras & Oportunidades
                </div>

                <h1 class="text-5xl md:text-7xl font-black text-gradient-primary tracking-tight mb-6 leading-tight">
                    Descubra seu próximo passo <br class="hidden md:block" />
                    na <span class="text-gradient-accent">UNN Startups</span>
                </h1>

                <p class="text-lg md:text-xl text-slate-300 max-w-2xl mx-auto font-medium mb-12">
                    Conecte-se com empresas inovadoras, aplique para vagas exclusivas e acelere sua trajetória profissional
                    com precisão.
                </p>

                <!-- Search/Filter Bar directly on Hero -->
                <div class="max-w-4xl mx-auto glass-panel p-3 md:p-4 rounded-3xl md:rounded-full">
                    <form method="GET" action="" class="flex flex-col md:flex-row gap-3 md:gap-2 relative">
                        <div class="flex-1 filter-input relative flex items-center h-14 md:h-16 px-5 group">
                            <i class="fas fa-search text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="text" name="area" placeholder="Cargo, tecnologia ou palavra-chave..."
                                value="{{ request('area') }}"
                                class="w-full h-full bg-transparent border-none focus:ring-0 text-slate-700 font-medium placeholder:font-normal placeholder:text-slate-400">
                        </div>

                        <div class="w-full md:w-64 filter-input relative flex items-center h-14 md:h-16 px-5 group">
                            <i
                                class="fas fa-map-marker-alt text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="text" name="local" placeholder="Ex: São Paulo, Remoto"
                                value="{{ request('local') }}"
                                class="w-full h-full bg-transparent border-none focus:ring-0 text-slate-700 font-medium placeholder:font-normal placeholder:text-slate-400">
                        </div>

                        <button type="submit"
                            class="h-14 md:h-16 px-8 rounded-full btn-premium font-bold hover-lift flex items-center justify-center gap-2 shrink-0 shadow-lg shadow-blue-600/30">
                            Buscar Vagas <i class="fas fa-arrow-right text-sm"></i>
                        </button>
                    </form>
                </div>

                @if(request()->anyFilled(['area', 'local', 'empresa', 'tipo']))
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <span class="text-slate-400 text-sm">Filtros ativos:</span>
                        <a href="{{ route('jobs.public.index') }}"
                            class="text-xs bg-white/10 hover:bg-white/20 text-white px-3 py-1 rounded-full transition-colors flex items-center gap-2">
                            Limpar filtros <i class="fas fa-times"></i>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Wave divider -->
            <div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-[0]">
                <svg class="relative block w-[calc(100%+1.3px)] h-[80px] md:h-[120px]" data-name="Layer 1"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                    <path
                        d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,121.23,189.5,109.28Z"
                        class="fill-slate-50 dark:fill-slate-950 transition-colors duration-500"></path>
                </svg>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 -mt-10 md:-mt-24 mb-20">
            <!-- Featured Job Floating Card (Only on pg 1) -->
            @if($featuredJob && (request()->page == 1 || !request()->has('page')))
                <div class="glass-card rounded-[2.5rem] p-2 md:p-3 overflow-hidden group shadow-2xl mb-16 md:mb-24">
                    <div class="bg-white dark:bg-slate-900 rounded-[2rem] overflow-hidden flex flex-col lg:flex-row relative">
                        <div
                            class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 dark:bg-blue-600/20 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none">
                        </div>

                        <div
                            class="lg:w-2/5 p-8 md:p-12 border-b lg:border-b-0 lg:border-r border-slate-100 dark:border-slate-800 flex flex-col justify-center relative overflow-hidden bg-slate-50/50 dark:bg-slate-800/30">
                            <div class="absolute top-6 left-6">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-100/80 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-400 text-[10px] font-black uppercase tracking-widest rounded-full border border-yellow-200 dark:border-yellow-800/50 backdrop-blur-sm">
                                    <i class="fas fa-crown"></i> Destaque Especial
                                </span>
                            </div>

                            <div
                                class="w-24 h-24 md:w-32 md:h-32 bg-white dark:bg-slate-800 rounded-[1.5rem] shadow-xl p-4 flex items-center justify-center mx-auto mb-6 transform group-hover:scale-105 transition-transform duration-500 border border-slate-100 dark:border-slate-700 overflow-hidden">
                                @if($featuredJob->image)
                                    <img src="{{ asset($featuredJob->image) }}" alt="{{ $featuredJob->company_name }}"
                                        class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal">
                                @else
                                    <i class="fas fa-briefcase text-4xl text-blue-500/50"></i>
                                @endif
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white text-center mb-2">
                                {{ $featuredJob->company_name ?? 'Empresa Confidencial' }}</h3>
                            <div
                                class="flex items-center justify-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400">
                                <i class="fas fa-map-marker-alt text-red-400"></i>
                                {{ $featuredJob->location ?? 'Global/Remoto' }}
                            </div>
                        </div>

                        <div class="lg:w-3/5 p-8 md:p-12 flex flex-col bg-white dark:bg-slate-900 z-10">
                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <span
                                    class="px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-xs font-bold border border-blue-100 dark:border-blue-800">
                                    {{ $featuredJob->type }}
                                </span>
                                <span
                                    class="px-3 py-1 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-full text-xs font-bold border border-purple-100 dark:border-purple-800">
                                    {{ $featuredJob->level ?? 'Pleno/Sênior' }}
                                </span>
                                @if($featuredJob->is_demo)
                                    <span
                                        class="px-3 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-full text-xs font-bold border border-amber-100 dark:border-amber-800">
                                        <i class="fas fa-flask"></i> Modo Demo
                                    </span>
                                @endif
                            </div>

                            <h2
                                class="text-2xl md:text-4xl font-black text-slate-900 dark:text-white mb-4 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ $featuredJob->title }}
                            </h2>

                            <p class="text-slate-600 dark:text-slate-400 text-base md:text-lg mb-8 line-clamp-3">
                                {{ $featuredJob->short_description ?? Str::limit(strip_tags($featuredJob->description), 200) }}
                            </p>

                            <div class="mt-auto flex flex-col sm:flex-row gap-4 items-center">
                                <a href="{{ route('jobs.public.show', $featuredJob->id) }}"
                                    class="w-full sm:w-auto px-8 py-4 rounded-xl btn-premium font-bold hover-lift flex items-center justify-center gap-2 text-white shadow-xl shadow-blue-500/20">
                                    Explorar Oportunidade <i class="fas fa-arrow-right"></i>
                                </a>
                                @if($featuredJob->salary_range)
                                    <div
                                        class="text-slate-500 dark:text-slate-400 font-medium text-sm w-full sm:w-auto text-center sm:text-left">
                                        <i class="fas fa-coins text-yellow-500 mr-1"></i> Remuneração: <strong
                                            class="text-slate-800 dark:text-white">{{ $featuredJob->salary_range }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Job Listing Grid -->
            <div id="lista-vagas" class="scroll-mt-32">
                <div
                    class="flex flex-col md:flex-row md:items-end justify-between mb-8 border-b border-slate-200 dark:border-slate-800 pb-4 gap-4">
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                        Últimas Publicações
                        <span
                            class="bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-bold px-3 py-1 rounded-full border border-blue-200 dark:border-blue-800/50 align-middle">
                            {{ $totalCount }} vagas
                        </span>
                    </h2>
                </div>

                @if($otherJobs->isEmpty())
                    <div class="glass-card rounded-3xl p-16 text-center shadow-lg">
                        <div
                            class="w-24 h-24 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-search text-3xl text-slate-400 dark:text-slate-600"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Ops, nada por aqui ainda!</h3>
                        <p class="text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-8">Não encontramos oportunidades exatas
                            para o seu filtro neste momento.</p>
                        <a href="{{ route('jobs.public.index') }}"
                            class="inline-flex btn-primary items-center gap-2 px-6 py-3 rounded-xl font-bold text-white shadow-lg hover:scale-105 transition text-sm">
                            <i class="fas fa-sync-alt"></i> Limpar Buscas
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 relative">
                        <!-- Decorative background grid element -->
                        <div class="absolute inset-0 bg-grid-pattern opacity-50 z-0 pointer-events-none rounded-3xl"></div>

                        @foreach($otherJobs as $vaga)
                            <div class="glass-card rounded-2xl flex flex-col group relative z-10 overflow-hidden"
                                style="animation: fadeInUp 0.5s ease-out forwards; opacity:0; animation-delay: {{ $loop->index * 0.05 }}s;">

                                <div class="p-6 flex-1 flex flex-col">
                                    <div class="flex items-start justify-between mb-5">
                                        <div
                                            class="w-14 h-14 bg-white dark:bg-slate-800 rounded-xl shadow-md border border-slate-100 dark:border-slate-700 flex flex-shrink-0 items-center justify-center overflow-hidden p-2 relative z-10 group-hover:scale-110 transition-transform duration-500">
                                            @if($vaga->image)
                                                <img src="{{ asset($vaga->image) }}" alt="Logo"
                                                    class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal">
                                            @else
                                                <i class="fas fa-building text-xl text-slate-300 dark:text-slate-600"></i>
                                            @endif
                                        </div>
                                        <div class="flex flex-col items-end gap-1 text-right relative z-10">
                                            <span
                                                class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center gap-1">
                                                <i class="far fa-clock"></i>
                                                {{ $vaga->created_at ? $vaga->created_at->diffForHumans() : 'Recente' }}
                                            </span>
                                            @if($vaga->is_demo)
                                                <span
                                                    class="bg-amber-100 text-amber-600 text-[9px] px-2 py-0.5 rounded-full font-bold"><i
                                                        class="fas fa-flask"></i> DEMO</span>
                                            @endif
                                        </div>
                                    </div>

                                    <h3
                                        class="text-xl font-black text-slate-900 dark:text-white mb-1 line-clamp-2 leading-tight group-hover:text-blue-500 transition-colors">
                                        <a href="{{ route('jobs.public.show', $vaga->id) }}" class="focus:outline-none">
                                            <span class="absolute inset-0" aria-hidden="true"></span>
                                            {{ $vaga->title }}
                                        </a>
                                    </h3>

                                    <p class="text-sm font-bold text-blue-600 dark:text-blue-400 mb-4">
                                        {{ $vaga->company_name ?? 'Confidencial' }}</p>

                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span
                                            class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] px-2.5 py-1 rounded-md font-semibold border border-slate-200 dark:border-slate-700">
                                            {{ $vaga->type ?? 'Integral' }}
                                        </span>
                                        <span
                                            class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] px-2.5 py-1 rounded-md font-semibold border border-slate-200 dark:border-slate-700">
                                            <i class="fas fa-map-marker-alt text-slate-400 mr-1"></i>
                                            {{ Str::limit($vaga->location ?? 'Remoto', 15) }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-3 mb-6 flex-1">
                                        {{ $vaga->short_description ?? Str::limit(strip_tags($vaga->description), 120) }}
                                    </p>
                                </div>

                                <div
                                    class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between group-hover:bg-blue-50 dark:group-hover:bg-blue-900/30 transition-colors z-10 w-full shrink-0">
                                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200 truncate pr-2">
                                        {{ $vaga->salary_range ?? 'A combinar' }}
                                    </span>
                                    <span
                                        class="text-blue-600 dark:text-blue-400 text-sm opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all font-bold shrink-0">
                                        Ver vaga <i class="fas fa-long-arrow-alt-right ml-1"></i>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-16 flex justify-center">
                        {{ $vagas->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>
        </section>

        <!-- Call to Action Banner -->
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">
            <div
                class="rounded-[2.5rem] bg-gradient-to-r from-blue-600 to-indigo-700 overflow-hidden relative shadow-2xl hover:shadow-blue-500/30 transition-shadow">
                <div
                    class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+CjxwYXRoIGQ9Ik0wIDBoNDB2NDBIMHoiIGZpbGw9Im5vbmUiLz4KPHBhdGggZD0iTTAgMTBoNDBNMTAgMHY0ME0wIDIwaDQwTTIwIDB2NDBNMCAzMGg0ME0zMCAwdjQwIiBzdHJva2U9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIgc3Ryb2tlLXdpZHRoPSIxIi8+Cjwvc3ZnPg==')] opacity-30">
                </div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl -mr-20 -mt-20"></div>
                <div
                    class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-400 opacity-20 rounded-full blur-3xl -ml-20 -mb-20">
                </div>

                <div
                    class="relative z-10 px-8 py-12 md:py-16 text-center md:text-left flex flex-col md:flex-row items-center justify-between gap-10">
                    <div class="max-w-2xl text-white">
                        <span
                            class="inline-block px-3 py-1 bg-white/20 rounded-full text-xs font-bold uppercase tracking-widest backdrop-blur-md border border-white/30 mb-4">Banco
                            de Talentos</span>
                        <h2 class="text-3xl md:text-4xl lg:text-5xl font-black mb-4 leading-tight">Mantenha seu Radar Ativo
                        </h2>
                        <p class="text-blue-100 text-lg md:text-xl font-medium">As melhores empresas buscam talentos
                            diariamente. Prepare seu currículo e seja notado por recrutadores de ponta.</p>
                    </div>
                    <div class="shrink-0 flex flex-col gap-3 w-full md:w-auto">
                        <a href="{{ route('panel.profile.edit', ['ref_curriculum' => 1]) }}"
                            class="px-8 py-4 bg-white text-indigo-700 rounded-2xl font-black shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95 transition-all w-full text-center group flex items-center justify-center gap-2">
                            <i class="fas fa-file-invoice group-hover:-translate-y-1 transition-transform"></i> Cadastrar
                            Currículo
                        </a>
                        <!-- If there's a premium page link later on -->
                    </div>
                </div>
            </div>
        </section>

    </div>

    @push('styles')
        <style>
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    @endpush
@endsection