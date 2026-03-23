@extends('panel.layouts.app')

@section('title', 'Painel do Membro - UNN')

@section('panel_content')
    @php
        $plan = $plan ?? null;
        $stats = $stats ?? [];
        $canAccessCommunity = auth()->user()->canAccessFeature('community');
        $canAccessCourses = auth()->user()->canAccessFeature('courses_access') || (method_exists(auth()->user(), 'hasPurchasedCourses') && auth()->user()->hasPurchasedCourses());
        $canSellOnMarketplace = auth()->user()->canSellOnMarketplace();
        $coursesCount = (int) ($stats['courses_count'] ?? 0);
        $ordersPaidCount = (int) ($stats['orders_paid_count'] ?? 0);
        $ordersPaidTotal = (float) ($stats['orders_paid_total'] ?? 0);
        $sellerPaidCount = (int) ($stats['seller_paid_count'] ?? 0);
        $sellerNetTotal = (float) ($stats['seller_net_total'] ?? 0);
        $communityCount = (int) ($stats['community_count'] ?? 0); // Added to ensure variable exists
        $visitMetrics = $visitMetrics ?? ['enabled' => false, 'owned_products_count' => 0, 'total_visits' => 0, 'last_24h' => 0, 'by_type' => [], 'top_items' => []];
        $dashboardRefreshMs = max(3000, (int) config('dashboard.refresh_interval_ms', 10000));
    @endphp
    
    @if(!auth()->user()->hasVerifiedEmail())
        <!-- Verification Alert Banner -->
        <div class="mb-8 animate-fade-in-up">
            <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 rounded-3xl p-1 shadow-xl shadow-amber-500/20 relative overflow-hidden group border border-amber-400/20">
                <div class="bg-white dark:bg-slate-900/90 backdrop-blur-md rounded-[1.4rem] p-6 flex flex-col md:flex-row items-center justify-between gap-6 relative z-10 border border-white/10">
                    <div class="flex items-center gap-5 text-center md:text-left">
                        <div class="w-14 h-14 rounded-2xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl shrink-0 shadow-inner">
                            <i class="fas fa-paper-plane animate-bounce-slow"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-black text-slate-900 dark:text-white mb-1 uppercase tracking-tight">Verifique seu e-mail</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Sua conta ainda não está ativa. Verifique sua caixa de entrada para liberar todos os recursos.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <form method="POST" action="{{ route('verification.send') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-amber-500/30 hover:-translate-y-1">
                                Reenviar E-mail
                            </button>
                        </form>
                        <a href="{{ route('panel.profile.edit') }}" class="px-6 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                            Meu Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Welcome Section -->
    <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-blue-700 via-indigo-800 to-slate-900 dark:from-blue-900/80 dark:via-indigo-950 dark:to-slate-950 p-10 shadow-[0_20px_60px_-15px_rgba(37,99,235,0.4)] dark:shadow-none mb-12 group border border-blue-400/20 dark:border-white/5">
        <!-- Abstract Background Shapes -->
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-white/10 dark:bg-blue-500/10 rounded-full blur-[100px] group-hover:bg-white/15 transition-all duration-1000"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-72 h-72 bg-blue-400/20 dark:bg-purple-500/10 rounded-full blur-[80px] group-hover:bg-blue-400/30 transition-all duration-1000"></div>

        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
            <div class="text-center lg:text-left">
                <div class="flex items-center justify-center lg:justify-start gap-3 mb-5">
                    <span class="px-4 py-1.5 rounded-full bg-white/10 text-white text-[11px] font-black uppercase tracking-[0.25em] backdrop-blur-xl border border-white/20 shadow-inner">
                        Painel VIP
                    </span>
                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.8)] animate-pulse"></span>
                </div>
                <h1 class="text-4xl md:text-[3.5rem] font-black text-white mb-4 tracking-tighter leading-tight drop-shadow-md">
                    Olá, {{ explode(' ', auth()->user()->name)[0] }}! <span class="inline-block animate-bounce-slow text-yellow-300">✨</span>
                </h1>
                <p class="text-blue-100/90 text-lg md:text-xl font-medium max-w-2xl leading-relaxed drop-shadow-sm">
                    {{ $plan?->name ? 'Seu acesso ' . $plan->name . ' está liberado. Explore todos os recursos da nossa rede.' : 'Transforme seu potencial em resultados reais. O que vamos conquistar hoje?' }}
                </p>
            </div>

            <div class="flex flex-wrap justify-center lg:justify-end gap-5 w-full lg:w-auto">
                <button onclick="window.openQuickUploadModal()"
                    class="group/btn relative px-8 py-4 bg-white/10 hover:bg-white/20 text-white rounded-2xl font-black backdrop-blur-xl transition-all border border-white/20 overflow-hidden shadow-xl hover:shadow-[0_8px_30px_rgba(255,255,255,0.15)] hover:-translate-y-1">
                    <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:animate-shimmer"></div>
                    <span class="relative flex items-center gap-3">
                        <i class="fas fa-camera text-blue-300"></i> Registrar Fotos
                    </span>
                </button>
                <a href="{{ route('panel.profile.edit') }}"
                    class="group/btn relative px-8 py-4 bg-white/10 hover:bg-white/20 text-white rounded-2xl font-black backdrop-blur-xl transition-all border border-white/20 overflow-hidden shadow-xl hover:shadow-[0_8px_30px_rgba(255,255,255,0.15)] hover:-translate-y-1">
                    <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:animate-shimmer"></div>
                    <span class="relative flex items-center gap-3">
                        <i class="fas fa-layer-group text-blue-300"></i> Meu Perfil
                    </span>
                </a>
                <a href="{{ route('planos') }}"
                    class="group/btn relative px-8 py-4 bg-gradient-to-br from-amber-400 to-orange-500 text-white rounded-2xl font-black shadow-[0_15px_40px_-10px_rgba(245,158,11,0.5)] hover:shadow-[0_20px_50px_-10px_rgba(245,158,11,0.7)] hover:-translate-y-1 transition-all flex items-center gap-3 border border-orange-300/50">
                    <i class="fas fa-crown text-yellow-100 group-hover/btn:rotate-12 transition-transform"></i>
                    <span>UPGRADE PREMIUM</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Access Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        @php
            $quickLinks = [
                ['route' => 'social.feed', 'icon' => 'fa-comments', 'label' => 'Feed Social', 'color' => 'bg-pink-500'],
                ['route' => 'courses.index', 'icon' => 'fa-play-circle', 'label' => 'Aulas', 'color' => 'bg-blue-500'],
                ['route' => 'panel.jobs.index', 'icon' => 'fa-search-dollar', 'label' => 'Vagas', 'color' => 'bg-emerald-500'],
                ['route' => 'chat.index', 'icon' => 'fa-paper-plane', 'label' => 'Mensagens', 'color' => 'bg-indigo-500'],
            ];
        @endphp
        @foreach($quickLinks as $link)
            <a href="{{ route($link['route']) }}" class="group relative bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl p-5 rounded-[2rem] border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] dark:hover:shadow-[0_20px_50px_rgba(37,99,235,0.2)] transition-all duration-300 hover:-translate-y-1 flex flex-col items-center justify-center text-center gap-4 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                <div class="w-14 h-14 rounded-2xl {{ $link['color'] }} text-white flex items-center justify-center text-xl shadow-lg shadow-{{ explode('-', $link['color'])[1] }}-500/40 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                    <i class="fas {{ $link['icon'] }}"></i>
                </div>
                <span class="text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-[0.2em] relative z-10">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </div>

    <!-- Section: Saúde e Visibilidade -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12 animate-fade-in-up" style="animation-delay: 100ms;">
        <!-- Card Saúde -->
        <div class="lg:col-span-4 bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl rounded-[2.5rem] shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] border border-white/50 dark:border-slate-800/60 p-10 flex flex-col items-center text-center group/card transition-all duration-500 hover:shadow-[0_25px_50px_-12px_rgba(0,0,0,0.1)] hover:-translate-y-1 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-blue-500/10 rounded-full -mr-16 -mt-16 blur-[60px] group-hover/card:bg-blue-500/20 transition-all duration-700"></div>

            <div class="relative w-40 h-40 mb-8 transform group-hover/card:scale-105 transition-transform duration-500">
                <div class="absolute inset-0 rounded-full shadow-[inset_0_0_20px_rgba(0,0,0,0.05)] dark:shadow-[inset_0_0_20px_rgba(255,255,255,0.02)]"></div>
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="80" cy="80" r="72" stroke="currentColor" stroke-width="12" fill="transparent" class="text-slate-100/50 dark:text-slate-800/30" />
                    <circle cx="80" cy="80" r="72" stroke="currentColor" stroke-width="12" fill="transparent" 
                        stroke-dasharray="{{ (2 * pi() * 72) * (($myHealth['score'] ?? 0) / 100) }} {{ (2 * pi() * 72) }}"
                        stroke-linecap="round"
                        class="transition-all duration-[2000ms] ease-out drop-shadow-lg" style="color: {{ $myHealth['color'] ?? '#1F5EDB' }}" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-4xl font-black text-slate-900 dark:text-white drop-shadow-sm">{{ $myHealth['score'] ?? 0 }}%</span>
                    <span class="text-[10px] uppercase font-black text-slate-400 dark:text-slate-500 tracking-[0.2em] mt-1">Score Único</span>
                </div>
            </div>

            <h4 class="text-2xl font-black text-slate-900 dark:text-white mb-3">Status da Conta</h4>
            <div class="px-6 py-2.5 rounded-2xl text-[11px] font-black uppercase tracking-widest mb-6 border transition-colors shadow-inner" style="background-color: {{ ($myHealth['color'] ?? '#1F5EDB') }}10; border-color: {{ ($myHealth['color'] ?? '#1F5EDB') }}20; color: {{ $myHealth['color'] ?? '#1F5EDB' }}">
                {{ $myHealth['level'] ?? 'MEMBRO INICIANTE' }}
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed font-semibold">
                {{ ($myHealth['score'] ?? 0) < 100 ? 'Sua autoridade no sistema cresce conforme você ativa e preenche o seu perfil.' : 'Expetacular! Você atingiu o nível máximo de autoridade na comunidade.' }}
            </p>
        </div>

        <!-- Card Checklist -->
        <div class="lg:col-span-8 bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl rounded-[2.5rem] shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] border border-white/50 dark:border-slate-800/60 p-10 flex flex-col relative overflow-hidden transition-all duration-500 hover:shadow-[0_25px_50px_-12px_rgba(0,0,0,0.1)]">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h4 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Checklist de Networking</h4>
                    <p class="text-slate-500 dark:text-slate-400 font-medium mt-1">Passos essenciais para ser notado na comunidade.</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center text-blue-500 shadow-inner">
                    <i class="fas fa-fingerprint text-2xl"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 flex-1">
                @php
                    $checkItems = [
                        ['key' => 'plano_ativo', 'label' => 'Plano Premium Ativo', 'icon' => 'fa-gem'],
                        ['key' => 'email_verificado', 'label' => 'E-mail Verificado', 'icon' => 'fa-envelope-circle-check'],
                        ['key' => 'perfil_completo', 'label' => 'Dados do Perfil', 'icon' => 'fa-id-card'],
                        ['key' => 'foto', 'label' => 'Headshot Profissional', 'icon' => 'fa-camera-retro'],
                        ['key' => 'bio', 'label' => 'Pitch de Apresentação', 'icon' => 'fa-quote-left'],
                        ['key' => 'telefone', 'label' => 'Canal de Contato', 'icon' => 'fa-mobile-screen'],
                        ['key' => 'ocupacao', 'label' => 'Área de Atuação', 'icon' => 'fa-briefcase'],
                    ];
                    if (!empty($sellerHealthChecks ?? [])) {
                        $checkItems = array_merge($checkItems, $sellerHealthChecks);
                    }
                @endphp

                @foreach($checkItems as $item)
                    @php $isDone = $myHealthDetails[$item['key']] ?? false; @endphp
                    <div class="flex items-center gap-4 p-5 rounded-3xl {{ $isDone ? 'bg-emerald-500/5 dark:bg-emerald-500/10 border-emerald-500/20' : 'bg-slate-50 dark:bg-slate-800/20 border-slate-100 dark:border-slate-800' }} border-2 transition-all hover:scale-[1.02] duration-300 group/item">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center {{ $isDone ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'bg-white dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700 shadow-sm' }}">
                            <i class="fas {{ $isDone ? 'fa-check' : $item['icon'] }} group-hover/item:scale-110 transition-transform"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-black {{ $isDone ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300' }} tracking-tight">{{ $item['label'] }}</span>
                            <span class="text-[10px] uppercase font-bold {{ $isDone ? 'text-emerald-500/60' : 'text-slate-400' }}">
                                {{ $isDone ? 'CONCLUÍDO' : 'PENDENTE' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Section: Estatísticas Dinâmicas -->
    <div class="mb-12 animate-fade-in-up" style="animation-delay: 200ms;">
        <div class="flex flex-col md:flex-row items-center justify-between mb-8 gap-4 px-2">
            <div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <i class="fas fa-bolt"></i>
                    </div>
                    Atividade Recente
                </h3>
            </div>
            <div class="flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-4 py-2 rounded-full">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

            @if($canAccessCourses)
                <!-- Widget: Cursos -->
                <div class="relative group bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-3xl mb-8 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h4 class="text-slate-400 dark:text-slate-500 text-xs font-black uppercase tracking-[0.2em] mb-3">Meus Cursos</h4>
                    <div class="flex items-end justify-between">
                        <span class="text-5xl font-black text-slate-900 dark:text-white tracking-tighter" id="counter-curso">{{ $coursesCount }}</span>
                        <div class="text-emerald-500 text-xs font-black flex items-center gap-1 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-lg">
                            <i class="fas fa-arrow-up"></i> ATIVO
                        </div>
                    </div>
                </div>
            @endif

            @if($canAccessCommunity)
                <!-- Widget: Comunidade -->
                <div class="relative group bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                    <div class="w-16 h-16 rounded-2xl bg-cyan-50 dark:bg-cyan-900/40 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-3xl mb-8 group-hover:scale-110 group-hover:bg-cyan-600 group-hover:text-white transition-all duration-500">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="text-slate-400 dark:text-slate-500 text-xs font-black uppercase tracking-[0.2em] mb-3">Interações</h4>
                    <div class="flex items-end justify-between">
                        <span class="text-5xl font-black text-slate-900 dark:text-white tracking-tighter" id="counter-community">{{ $communityCount }}</span>
                    </div>
                </div>
            @endif

            <!-- Widget: Pontos UNN -->
            @if($userPoints > 0 || true)
                <a href="{{ route('panel.points.index') }}" class="relative group bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 p-8 rounded-[2.5rem] border border-amber-200/60 dark:border-amber-800/40 shadow-sm hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-500 hover:-translate-y-2 block">
                    <div class="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-3xl mb-8 group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-white transition-all duration-500">
                        <i class="fas fa-star"></i>
                    </div>
                    <h4 class="text-amber-500 dark:text-amber-400 text-xs font-black uppercase tracking-[0.2em] mb-3">Meus Pontos</h4>
                    <div class="flex items-end justify-between">
                        <span class="text-5xl font-black text-slate-900 dark:text-white tracking-tighter" id="counter-pontos">{{ number_format($userPoints) }}</span>
                        <div class="text-amber-500 text-xs font-black flex items-center gap-1 bg-amber-100 dark:bg-amber-900/40 px-2 py-1 rounded-lg">
                            <i class="fas fa-trophy text-[10px]"></i> #{{ $rankPosition }}
                        </div>
                    </div>
                    <p class="text-xs text-amber-500/80 font-semibold mt-3">{{ $pontosEsteMes }} pts este mês</p>
                </a>
            @endif

            <!-- Widget: Investimento -->
            <div class="relative group bg-indigo-900 p-8 rounded-[2.5rem] border border-indigo-800 shadow-2xl transition-all duration-500 hover:-translate-y-2 col-span-1 md:col-span-2 overflow-hidden shadow-indigo-950/40">
                <div class="absolute top-0 right-0 p-8 opacity-10">
                    <i class="fas fa-gem text-9xl text-white"></i>
                </div>
                <div class="relative z-10">
                    <div class="w-16 h-16 rounded-2xl bg-white/10 text-indigo-300 flex items-center justify-center text-3xl mb-8">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h4 class="text-indigo-400 text-xs font-black uppercase tracking-[0.2em] mb-3">Total Investido</h4>
                    <div class="flex items-center gap-5">
                        <span class="text-4xl md:text-5xl font-black text-white tracking-tighter" id="counter-orders-total">R$ {{ number_format($ordersPaidTotal, 2, ',', '.') }}</span>
                        <div class="h-10 w-[2px] bg-white/10"></div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black text-indigo-400 uppercase">Compras</span>
                            <span class="text-2xl font-black text-white" id="counter-orders">{{ $ordersPaidCount }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(($visitMetrics['enabled'] ?? false) && (($visitMetrics['owned_products_count'] ?? 0) > 0))
        <div class="mb-12 animate-fade-in-up" style="animation-delay: 260ms;" id="owner-visit-radar">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 px-2">
                <div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <i class="fas fa-signal"></i>
                        </div>
                        Radar de Visitas
                    </h3>
                    <p class="text-slate-500 dark:text-slate-400 font-medium mt-2">Acompanhe o interesse pelos produtos sob sua responsabilidade.</p>
                </div>
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-300 text-xs font-black uppercase tracking-widest">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Em tempo real
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
                @foreach([
                    ['id' => 'counter-owned-visits-total', 'label' => 'Total de visitas', 'value' => $visitMetrics['total_visits'] ?? 0, 'icon' => 'fa-eye', 'color' => 'blue'],
                    ['id' => 'counter-owned-visits-day', 'label' => 'Últimas 24 horas', 'value' => $visitMetrics['last_24h'] ?? 0, 'icon' => 'fa-clock', 'color' => 'emerald'],
                    ['id' => 'counter-owned-products', 'label' => 'Produtos monitorados', 'value' => $visitMetrics['owned_products_count'] ?? 0, 'icon' => 'fa-briefcase', 'color' => 'violet'],
                    ['id' => 'counter-owned-visits-courses', 'label' => 'Cursos visitados', 'value' => $visitMetrics['by_type']['curso'] ?? 0, 'icon' => 'fa-graduation-cap', 'color' => 'amber'],
                ] as $metric)
                    <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                        <div class="w-14 h-14 rounded-2xl bg-{{ $metric['color'] }}-50 dark:bg-{{ $metric['color'] }}-900/20 text-{{ $metric['color'] }}-600 dark:text-{{ $metric['color'] }}-300 flex items-center justify-center text-xl mb-6">
                            <i class="fas {{ $metric['icon'] }}"></i>
                        </div>
                        <h4 class="text-slate-400 dark:text-slate-500 text-xs font-black uppercase tracking-[0.2em] mb-3">{{ $metric['label'] }}</h4>
                        <span class="text-5xl font-black text-slate-900 dark:text-white tracking-tighter" id="{{ $metric['id'] }}">{{ $metric['value'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h4 class="text-xl font-black text-slate-900 dark:text-white">Distribuição por tipo</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Quais formatos recebem mais atenção.</p>
                        </div>
                    </div>
                    <div class="space-y-4" id="owner-visit-types">
                        @foreach([
                            'Cursos' => $visitMetrics['by_type']['curso'] ?? 0,
                            'Eventos' => $visitMetrics['by_type']['evento'] ?? 0,
                            'Mentorias' => $visitMetrics['by_type']['mentoria'] ?? 0,
                            'Palestras' => $visitMetrics['by_type']['palestra'] ?? 0,
                        ] as $label => $value)
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                                <span class="font-bold text-slate-700 dark:text-slate-200">{{ $label }}</span>
                                <span class="text-lg font-black text-blue-600 dark:text-blue-300">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h4 class="text-xl font-black text-slate-900 dark:text-white">Produtos com maior tráfego</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Os itens mais procurados agora.</p>
                        </div>
                    </div>
                    <div class="space-y-4" id="owner-visit-top-items">
                        @forelse(($visitMetrics['top_items'] ?? []) as $item)
                            <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                                <div class="min-w-0">
                                    <p class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $item['label'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['type'] }}</p>
                                </div>
                                <span class="text-lg font-black text-emerald-600 dark:text-emerald-300">{{ $item['total'] }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Ainda não há visitas registradas nos seus produtos.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($canSellOnMarketplace)
        <!-- Section: Financeiro Vendedor -->
        <div class="mb-12 animate-fade-in-up" style="animation-delay: 300ms;">
            <div class="bg-slate-950 rounded-[3rem] p-10 md:p-14 shadow-3xl relative overflow-hidden group">
                <div class="absolute top-0 right-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[100px] animate-pulse"></div>

                <div class="relative z-10 flex flex-col xl:flex-row gap-12 items-center">
                    <div class="w-full xl:w-1/3">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/30">
                                <i class="fas fa-coins text-xl"></i>
                            </div>
                            <h3 class="text-2xl font-black text-white tracking-tight">Portfólio de Vendas</h3>
                        </div>
                        <p class="text-slate-400 font-medium leading-relaxed">Gerencie seu faturamento e acompanhe a performance dos seus produtos no Marketplace.</p>

                        <div class="mt-10 flex flex-wrap gap-4">
                            <a href="{{ route('panel.marketplace.sales') }}" class="px-6 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl text-white font-bold text-sm transition-all flex items-center gap-2">
                                <i class="fas fa-list-ul"></i> Ver Todas Vendas
                            </a>
                            <a href="{{ route('panel.marketplace.payments') }}" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 rounded-2xl text-white font-black text-sm transition-all shadow-lg shadow-amber-500/20">
                                SAQUE AGORA
                            </a>
                        </div>
                    </div>

                    <div class="w-full xl:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Widget: Faturamento -->
                        <div class="bg-white/5 border border-white/10 p-8 rounded-[2rem] hover:bg-white/10 transition-colors">
                            <h4 class="text-slate-500 text-[10px] font-black uppercase tracking-[0.2em] mb-4">Receita Líquida Total</h4>
                            <span class="text-4xl font-black text-white tracking-tighter block mb-2" id="counter-seller-total">R$ {{ number_format($sellerNetTotal, 2, ',', '.') }}</span>
                            <div class="text-slate-400 text-xs font-bold flex items-center gap-2">
                                <i class="fas fa-shopping-bag text-emerald-500"></i> {{ $sellerPaidCount }} Pedidos Concluídos
                            </div>
                        </div>

                        <!-- Widget: Mercado Pago -->
                        <div class="bg-blue-600 p-8 rounded-[2rem] shadow-xl shadow-blue-900/40 relative overflow-hidden group/mp">
                            <div class="absolute -right-4 -bottom-4 opacity-10 group-hover/mp:scale-110 transition-transform duration-700">
                                <i class="fas fa-university text-8xl text-white"></i>
                            </div>
                            <h4 class="text-blue-200 text-[10px] font-black uppercase tracking-[0.2em] mb-4">Disponível Mercado Pago</h4>
                            <span class="text-4xl font-black text-white tracking-tighter block mb-2" id="counter-mp-balance">
                                <i class="fas fa-circle-notch fa-spin opacity-50"></i>
                            </span>
                            <div class="text-blue-100/60 text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span> SINCRONIZADO
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-16 animate-fade-in-up" style="animation-delay: 400ms;">
        <!-- Chart Section -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-[3rem] shadow-sm border border-slate-100 dark:border-slate-800 p-10">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                        <i class="fas fa-chart-line text-blue-600"></i> Performance Histórica
                    </h3>
                    <p class="text-slate-400 font-medium mt-1">Análise de crescimento nos últimos ciclos</p>
                </div>
                <select class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-black text-slate-500 uppercase px-4 py-2">
                    <option>Últimos 6 Meses</option>
                </select>
            </div>
            <div class="relative h-80 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        @if(isset($suggestedUsers) && $suggestedUsers->count() > 0)
            <!-- Suggestions Section -->
            <div class="lg:col-span-4 bg-white dark:bg-slate-900 rounded-[3rem] shadow-sm border border-slate-100 dark:border-slate-800 p-10 flex flex-col">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shadow-sm">
                        <i class="fas fa-fire-alt"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Explorar Novos Membros</h3>
                </div>

                <div class="flex flex-col gap-5 overflow-y-auto max-h-[360px] pr-2 custom-scrollbar">
                    @foreach($suggestedUsers as $sUser)
                        <div class="flex items-center gap-4 p-4 rounded-3xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all border border-transparent hover:border-slate-100 dark:hover:border-slate-700 group/u">
                            <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 border-2 border-white dark:border-slate-700 shadow-md group-hover/u:scale-105 transition-transform">
                                @if($sUser->photo)
                                    <img src="{{ asset($sUser->photo) }}" alt="{{ $sUser->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-500 font-black text-xl">
                                        {{ mb_substr($sUser->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-black text-slate-800 dark:text-white text-sm truncate tracking-tight">{{ $sUser->name }}</h4>
                                <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest truncate mt-0.5">
                                    {{ $sUser->occupation ?? 'NETWORKER' }}
                                </p>
                            </div>
                            <a href="{{ route('social.profile', $sUser->id) }}"
                                class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                <i class="fas fa-plus text-sm"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('social.feed') }}"
                    class="mt-8 py-4 text-center text-xs font-black text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-2xl transition-all border border-blue-100 dark:border-blue-900/50 uppercase tracking-[0.2em]">
                    Ver Todos os Membros
                </a>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    @include('partials.service-visits-realtime')
    <script>
        const dashboardJsonFetchOptions = {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        function renderOwnerVisitMetrics(visitMetrics) {
            if (!visitMetrics || !document.getElementById('owner-visit-radar')) {
                return;
            }

            const cardValues = {
                'counter-owned-visits-total': visitMetrics.total_visits || 0,
                'counter-owned-visits-day': visitMetrics.last_24h || 0,
                'counter-owned-products': visitMetrics.owned_products_count || 0,
                'counter-owned-visits-courses': (visitMetrics.by_type && visitMetrics.by_type.curso) || 0,
            };

            Object.entries(cardValues).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = value;
                }
            });

            const typesContainer = document.getElementById('owner-visit-types');
            if (typesContainer) {
                const rows = [
                    ['Cursos', visitMetrics.by_type?.curso || 0],
                    ['Eventos', visitMetrics.by_type?.evento || 0],
                    ['Mentorias', visitMetrics.by_type?.mentoria || 0],
                    ['Palestras', visitMetrics.by_type?.palestra || 0],
                ];

                typesContainer.innerHTML = rows.map(([label, value]) => `
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                        <span class="font-bold text-slate-700 dark:text-slate-200">${label}</span>
                        <span class="text-lg font-black text-blue-600 dark:text-blue-300">${value}</span>
                    </div>
                `).join('');
            }

            const topItemsContainer = document.getElementById('owner-visit-top-items');
            if (topItemsContainer) {
                const topItems = visitMetrics.top_items || [];
                topItemsContainer.innerHTML = topItems.length
                    ? topItems.map((item) => `
                        <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                            <div class="min-w-0">
                                <p class="text-sm font-black text-slate-900 dark:text-white truncate">${item.label}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">${item.type}</p>
                            </div>
                            <span class="text-lg font-black text-emerald-600 dark:text-emerald-300">${item.total}</span>
                        </div>
                    `).join('')
                    : '<p class="text-sm text-slate-500 dark:text-slate-400">Ainda não há visitas registradas nos seus produtos.</p>';
            }
        }

        function syncDashboardWidgets(data) {
            if (!data || !data.stats) {
                return;
            }

            const elCurso = document.getElementById('counter-curso');
            if (elCurso) elCurso.textContent = data.stats.courses_count;

            const elOrders = document.getElementById('counter-orders');
            if (elOrders) elOrders.textContent = data.stats.orders_paid_count;

            const elOrdersTotal = document.getElementById('counter-orders-total');
            if (elOrdersTotal) elOrdersTotal.textContent = 'R$ ' + (data.stats.orders_paid_total).toLocaleString('pt-BR', { minimumFractionDigits: 2 });

            const elSeller = document.getElementById('counter-seller');
            if (elSeller) elSeller.textContent = data.stats.seller_paid_count;

            const elSellerTotal = document.getElementById('counter-seller-total');
            if (elSellerTotal) elSellerTotal.textContent = 'R$ ' + (data.stats.seller_net_total).toLocaleString('pt-BR', { minimumFractionDigits: 2 });

            const elMpBalance = document.getElementById('counter-mp-balance');
            if (elMpBalance) {
                if (data.stats.mp_balance) {
                    let val = data.stats.mp_balance.total_amount || 0;
                    elMpBalance.textContent = 'R$ ' + parseFloat(val).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                } else {
                    elMpBalance.innerHTML = '<span class="text-xs text-gray-400">N/D</span>';
                }
            }

            const elCommunity = document.getElementById('counter-community');
            if (elCommunity) elCommunity.textContent = data.stats.community_count;

            renderOwnerVisitMetrics(data.visit_metrics || null);

            document.querySelectorAll('.animate-pulse').forEach((element) => element.classList.remove('animate-pulse'));
        }

        function updateDashboardWidgets() {
            fetch('{{ route('panel.dashboard.stats') }}', dashboardJsonFetchOptions)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.stats) {
                        syncDashboardWidgets(data);
                        return;
                        const elCurso = document.getElementById('counter-curso');
                        if (elCurso) elCurso.textContent = data.stats.courses_count;

                        const elOrders = document.getElementById('counter-orders');
                        if (elOrders) elOrders.textContent = data.stats.orders_paid_count;

                        const elOrdersTotal = document.getElementById('counter-orders-total');
                        if (elOrdersTotal) elOrdersTotal.textContent = 'R$ ' + (data.stats.orders_paid_total).toLocaleString('pt-BR', { minimumFractionDigits: 2 });

                        const elSeller = document.getElementById('counter-seller');
                        if (elSeller) elSeller.textContent = data.stats.seller_paid_count;

                        const elSellerTotal = document.getElementById('counter-seller-total');
                        if (elSellerTotal) elSellerTotal.textContent = 'R$ ' + (data.stats.seller_net_total).toLocaleString('pt-BR', { minimumFractionDigits: 2 });

                        const elMpBalance = document.getElementById('counter-mp-balance');
                        if (elMpBalance) {
                            if (data.stats.mp_balance) {
                                let val = data.stats.mp_balance.total_amount || 0;
                                elMpBalance.textContent = 'R$ ' + parseFloat(val).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                            } else {
                                elMpBalance.innerHTML = '<span class="text-xs text-gray-400">N/D</span>';
                            }
                        }

                        const elCommunity = document.getElementById('counter-community');
                        if (elCommunity) elCommunity.textContent = data.stats.community_count;

                        const elCursoMsg = document.getElementById('widget-cursos-msg');
                        if (elCursoMsg) elCursoMsg.textContent = '';

                        // Remove animação de loading
                        document.querySelectorAll('.animate-pulse').forEach(e => e.classList.remove('animate-pulse'));
                    }
                }).catch(e => console.error('Dashboard stats update failed:', e));
        }
        document.addEventListener('DOMContentLoaded', function () {
            renderOwnerVisitMetrics(@json($visitMetrics));
            updateDashboardWidgets();
            window.UNNServiceVisitsRealtime.start({
                statsUrl: @json(route('panel.dashboard.stats')),
                refreshMs: @json($dashboardRefreshMs),
                onPayload: syncDashboardWidgets,
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function updateSalesChart(chart) {
            fetch('{{ route('panel.dashboard.stats') }}?chart=1', dashboardJsonFetchOptions)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.sales_chart) {
                        chart.data.labels = data.sales_chart.labels;
                        chart.data.datasets[0].data = data.sales_chart.data;
                        chart.update();
                    }
                });
        }
        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('salesChart').getContext('2d');

            // Custom Gradient for Chart
            var gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.5)'); // Blue
            gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

            var salesChart = new Chart(ctx, {
                type: 'line', // Changed to line for elegance, or bar with radius
                data: {
                    labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                    datasets: [{
                        label: 'Vendas pagas',
                        data: [0, 0, 0, 0, 0, 0],
                        backgroundColor: gradient,
                        borderColor: '#2563eb',
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#2563eb',
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: true,
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 13, family: 'Inter' },
                            bodyFont: { size: 13, family: 'Inter' },
                            cornerRadius: 8,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0, 0, 0, 0.05)', borderDash: [5, 5] },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            });
            updateSalesChart(salesChart);
            setInterval(function () { updateSalesChart(salesChart); }, {{ $dashboardRefreshMs }});
        });
    </script>
    <style>
        .animate-shimmer {
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }

        .animate-wave {
            animation: wave 2s infinite;
        }

        @keyframes wave {
            0% {
                transform: rotate(0deg);
            }

            10% {
                transform: rotate(14deg);
            }

            20% {
                transform: rotate(-8deg);
            }

            30% {
                transform: rotate(14deg);
            }

            40% {
                transform: rotate(-4deg);
            }

            50% {
                transform: rotate(10deg);
            }

            60% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(0deg);
            }
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush
