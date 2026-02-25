@extends('panel.layouts.app')

@section('title', 'Painel do Membro - UNN')

@section('panel_content')
    @php
        $plan = $plan ?? null;
        $stats = $stats ?? [];
        $isImpersonatingAdmin = session()->has('impersonator_id') && session()->get('impersonator_is_admin');
        $canAccessCommunity = auth()->user()->canAccessFeature('community') || $isImpersonatingAdmin;
        $canAccessCourses = auth()->user()->canAccessFeature('courses_access') || (method_exists(auth()->user(), 'hasPurchasedCourses') && auth()->user()->hasPurchasedCourses()) || $isImpersonatingAdmin;
        $canSellOnMarketplace = auth()->user()->canSellOnMarketplace() || $isImpersonatingAdmin;
        $coursesCount = (int) ($stats['courses_count'] ?? 0);
        $ordersPaidCount = (int) ($stats['orders_paid_count'] ?? 0);
        $ordersPaidTotal = (float) ($stats['orders_paid_total'] ?? 0);
        $sellerPaidCount = (int) ($stats['seller_paid_count'] ?? 0);
        $sellerNetTotal = (float) ($stats['seller_net_total'] ?? 0);
        $communityCount = (int) ($stats['community_count'] ?? 0); // Added to ensure variable exists
    @endphp

    <!-- Welcome Section -->
    <div
        class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 p-8 shadow-2xl shadow-blue-900/20 mb-10 group">
        <!-- Abstract Background Shapes -->
        <div
            class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-white/5 rounded-full blur-3xl group-hover:bg-white/10 transition-all duration-700">
        </div>
        <div
            class="absolute bottom-0 left-0 -mb-20 -ml-20 w-60 h-60 bg-black/10 rounded-full blur-3xl group-hover:bg-black/20 transition-all duration-700">
        </div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start gap-3 mb-2">
                    <span
                        class="px-3 py-1 rounded-full bg-white/20 text-white text-[10px] font-bold uppercase tracking-wider backdrop-blur-sm">Painel
                        do Membro</span>
                </div>
                <h1 class="text-3xl md:text-5xl font-black text-white mb-3 tracking-tight">
                    Olá, {{ explode(' ', auth()->user()->name)[0] }}! <span
                        class="inline-block animate-wave origin-bottom-right">👋</span>
                </h1>
                <p class="text-blue-100 text-lg font-medium max-w-xl leading-relaxed opacity-90">
                    {{ $plan?->name ? '✨ Seu plano ' . $plan->name . ' está ativo e pronto para uso.' : '🚀 Preparado para levar sua carreira ao próximo nível?' }}
                </p>
            </div>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('panel.profile.edit') }}"
                    class="group/btn relative px-6 py-3.5 bg-white/10 hover:bg-white/20 text-white rounded-2xl font-bold backdrop-blur-md transition-all border border-white/10 overflow-hidden">
                    <div
                        class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover/btn:animate-shimmer">
                    </div>
                    <span class="relative flex items-center gap-2">
                        <i class="fas fa-user-edit text-blue-200"></i> Editar Perfil
                    </span>
                </a>
                <a href="{{ route('premium') }}"
                    class="px-6 py-3.5 bg-white text-blue-700 rounded-2xl font-bold shadow-xl shadow-blue-900/10 hover:shadow-2xl hover:shadow-blue-900/20 hover:-translate-y-1 transition-all flex items-center gap-2">
                    <i class="fas fa-crown text-amber-500"></i>
                    <span>Ver Planos</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Section: Minha Saúde na UNN -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12 animate-fade-in-up" style="animation-delay: 100ms;">
        <div class="lg:col-span-1 bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 flex flex-col items-center text-center group/card transition-all">
            <div class="relative w-32 h-32 mb-6">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-100 dark:text-slate-800" />
                    <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" 
                        stroke-dasharray="{{ (2 * pi() * 58) * (($myHealth['score'] ?? 0) / 100) }} {{ (2 * pi() * 58) }}"
                        class="transition-all duration-1000" style="color: {{ $myHealth['color'] ?? '#1F5EDB' }}" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $myHealth['score'] ?? 0 }}%</span>
                    <span class="text-[10px] uppercase font-bold text-slate-400">Saúde</span>
                </div>
            </div>
            <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Sua Saúde na UNN {{ $myHealth['emoji'] ?? '' }}</h4>
            <div class="px-4 py-1.5 rounded-full text-xs font-bold mb-4" style="background-color: {{ ($myHealth['color'] ?? '#1F5EDB') }}20; color: {{ $myHealth['color'] ?? '#1F5EDB' }}">
                Status: {{ $myHealth['level'] ?? 'Iniciante' }}
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed px-4">
                {{ ($myHealth['score'] ?? 0) < 100 ? 'Complete seu perfil para aumentar sua autoridade na rede.' : 'Parabéns! Seu perfil está otimizado para o máximo de visibilidade.' }}
            </p>
        </div>

        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 flex flex-col">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white">Checklist de Visibilidade</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Aumente suas chances de networking completando seu cadastro.</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                    <i class="fas fa-list-check text-xl"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1">
                @php
                    $checkItems = [
                        ['key' => 'plano_ativo', 'label' => 'Plano Ativo', 'icon' => 'fa-gem'],
                        ['key' => 'perfil_completo', 'label' => 'Cadastro Completo', 'icon' => 'fa-user-check'],
                        ['key' => 'foto', 'label' => 'Foto Profissional', 'icon' => 'fa-camera'],
                        ['key' => 'bio', 'label' => 'Biografia/Bio', 'icon' => 'fa-paragraph'],
                        ['key' => 'telefone', 'label' => 'WhatsApp Contato', 'icon' => 'fa-phone-flip'],
                        ['key' => 'ocupacao', 'label' => 'Cargo/Ocupação', 'icon' => 'fa-briefcase'],
                    ];
                @endphp

                @foreach($checkItems as $item)
                    @php $isDone = $myHealthDetails[$item['key']] ?? false; @endphp
                    <div class="flex items-center gap-3 p-4 rounded-2xl {{ $isDone ? 'bg-emerald-50/50 dark:bg-emerald-900/10 border-emerald-100/50 dark:border-emerald-800/20' : 'bg-slate-50 dark:bg-slate-800/30 border-slate-100 dark:border-slate-800' }} border transition-all hover:scale-[1.02] duration-300">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $isDone ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-200 dark:bg-slate-700 text-slate-400' }}">
                            <i class="fas {{ $isDone ? 'fa-check' : $item['icon'] }}"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold {{ $isDone ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-400' }}">{{ $item['label'] }}</span>
                            <span class="text-[10px] font-medium {{ $isDone ? 'text-emerald-600/70 dark:text-emerald-500/60' : 'text-slate-400' }}">
                                {{ $isDone ? 'Concluido' : 'Pendente' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Section: Visão Geral -->
    <div class="mb-12 animate-fade-in-up">
        <div class="flex items-center justify-between mb-6 px-1">
            <h3 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i class="fas fa-chart-pie"></i>
                </div>
                Visão Geral
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6" id="dashboard-widgets-row1">

            @if($canAccessCourses)
                <!-- Widget: Meus Cursos -->
                <div
                    class="relative group bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:hover:shadow-none hover:border-blue-200 dark:hover:border-blue-800 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div
                            class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-widest mb-2">Meus
                            Cursos</h4>
                        <div class="flex items-end justify-between">
                            <span class="text-4xl font-black text-slate-900 dark:text-white tracking-tight"
                                id="counter-curso">{{ $coursesCount }}</span>
                            <a href="{{ route('courses.index') }}"
                                class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if($canAccessCommunity)
                <!-- Widget: Comunidade -->
                <div
                    class="relative group bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:hover:shadow-none hover:border-cyan-200 dark:hover:border-cyan-800 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div
                            class="w-14 h-14 rounded-2xl bg-cyan-50 dark:bg-cyan-900/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-widest mb-2">
                            Comunidade</h4>
                        <div class="flex items-end justify-between">
                            <span class="text-4xl font-black text-slate-900 dark:text-white tracking-tight"
                                id="counter-community">{{ $communityCount }}</span>
                            <a href="{{ route('social.feed') }}"
                                class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 flex items-center justify-center hover:bg-cyan-600 hover:text-white transition-all transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Widget: Compras Pagas -->
            <div
                class="relative group bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:hover:shadow-none hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div
                        class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div>
                    <h4 class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-widest mb-2">Compras
                        Pagas</h4>
                    <div class="flex items-end justify-between">
                        <span class="text-4xl font-black text-slate-900 dark:text-white tracking-tight"
                            id="counter-orders">{{ $ordersPaidCount }}</span>
                        <a href="{{ route('marketplace.index') }}"
                            class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100">
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Widget: Total em Compras -->
            <div
                class="relative group bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:hover:shadow-none hover:border-green-200 dark:hover:border-green-800 transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div
                        class="w-14 h-14 rounded-2xl bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div>
                    <h4 class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-widest mb-2">Total
                        Investido</h4>
                    <div class="flex items-end justify-between">
                        <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight"
                            id="counter-orders-total">R$ {{ number_format($ordersPaidTotal, 2, ',', '.') }}</span>
                        <a href="{{ route('marketplace.index') }}"
                            class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 flex items-center justify-center hover:bg-green-600 hover:text-white transition-all transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100">
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($canSellOnMarketplace)
        <!-- Section: Financeiro -->
        <div class="mb-12 animate-fade-in-up" style="animation-delay: 100ms;">
            <div class="flex items-center justify-between mb-6 px-1">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <i class="fas fa-coins"></i>
                    </div>
                    Financeiro (Vendas)
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6" id="dashboard-widgets-row2">
                <!-- Widget: Vendas Realizadas -->
                <div
                    class="relative group bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:hover:shadow-none hover:border-purple-200 dark:hover:border-purple-800 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div
                            class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-widest mb-2">Vendas
                            Realizadas</h4>
                        <div class="flex items-end justify-between">
                            <span class="text-4xl font-black text-slate-900 dark:text-white tracking-tight"
                                id="counter-seller">{{ $sellerPaidCount }}</span>
                            <a href="{{ route('panel.marketplace.sales') }}"
                                class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Widget: Receita Líquida -->
                <div
                    class="relative group bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:hover:shadow-none hover:border-amber-200 dark:hover:border-amber-800 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div
                            class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                            <i class="fas fa-sack-dollar"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-widest mb-2">Receita
                            Líquida</h4>
                        <div class="flex items-end justify-between">
                            <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight"
                                id="counter-seller-total">R$ {{ number_format($sellerNetTotal, 2, ',', '.') }}</span>
                            <a href="{{ route('panel.marketplace.payments') }}"
                                class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 flex items-center justify-center hover:bg-amber-600 hover:text-white transition-all transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Widget: Saldo Mercado Pago -->
                <div
                    class="relative group bg-slate-900 dark:bg-slate-950 p-6 rounded-[2rem] border border-slate-800 dark:border-slate-800 col-span-1 md:col-span-2 shadow-lg overflow-hidden">
                    <div class="absolute top-0 right-0 p-3 opacity-10">
                        <i class="fas fa-university text-9xl text-white"></i>
                    </div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-blue-400">
                                <i class="fas fa-university"></i>
                            </div>
                            <h4 class="text-slate-400 text-xs font-bold uppercase tracking-widest">Saldo Mercado Pago</h4>
                        </div>
                        <div>
                            <span class="text-4xl font-black text-white tracking-tight" id="counter-mp-balance">
                                <i class="fas fa-spinner fa-spin text-2xl opacity-50"></i>
                            </span>
                            <p class="text-slate-500 text-xs mt-2">Saldo disponível para saque imediato.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12 animate-fade-in-up" style="animation-delay: 200ms;">
        <!-- Chart Section -->
        <div
            class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-chart-bar text-blue-500"></i> Desempenho de Vendas
                    </h3>
                    <p class="text-slate-400 text-sm mt-1">Acompanhe suas vendas nos últimos 6 meses</p>
                </div>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        @if(isset($suggestedUsers) && $suggestedUsers->count() > 0)
            <!-- Suggestions Section -->
            <div
                class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 flex flex-col">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-6">
                    <i class="fas fa-bolt text-yellow-400"></i> Conexões Sugeridas
                </h3>

                <div class="flex flex-col gap-4 overflow-y-auto max-h-[300px] pr-2 custom-scrollbar">
                    @foreach($suggestedUsers as $sUser)
                        <div
                            class="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors border border-transparent hover:border-slate-100 dark:hover:border-slate-800">
                            <div
                                class="w-12 h-12 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 border-2 border-white dark:border-slate-700 shadow-sm">
                                @if($sUser->photo)
                                    <img src="{{ asset($sUser->photo) }}" alt="{{ $sUser->name }}" class="w-full h-full object-cover">
                                @else
                                    <div
                                        class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-500 font-bold">
                                        {{ mb_substr($sUser->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-slate-800 dark:text-white text-sm truncate">{{ $sUser->name }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                    {{ $sUser->occupation ?? 'Membro da Comunidade' }}</p>
                            </div>
                            <a href="{{ route('social.profile', $sUser->id) }}"
                                class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all">
                                <i class="fas fa-plus text-xs"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('social.feed') }}"
                    class="mt-auto pt-4 text-center text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline">Ver
                    todos os membros</a>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        function updateDashboardWidgets() {
            fetch('{{ route('panel.dashboard.stats') }}')
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.stats) {
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
            updateDashboardWidgets();
            setInterval(updateDashboardWidgets, 10000); // Atualiza a cada 10s
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function updateSalesChart(chart) {
            fetch('{{ route('panel.dashboard.stats') }}?chart=1')
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
            setInterval(function () { updateSalesChart(salesChart); }, 10000);
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