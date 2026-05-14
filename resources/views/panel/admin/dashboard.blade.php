@extends('panel.layouts.app')

@section('title', 'Painel Administrativo')

@section('panel_content')
    @php
        $serviceVisitSummary = $serviceVisitSummary ?? [
            'total' => 0,
            'last_24h' => 0,
            'site' => 0,
            'curso' => 0,
            'evento' => 0,
            'mentoria' => 0,
            'palestra' => 0,
            'monitored_products' => 0,
        ];
        $serviceVisitTopItems = collect($serviceVisitTopItems ?? []);
        $serviceVisitOwnerLeaders = collect($serviceVisitOwnerLeaders ?? []);
        $dashboardRefreshMs = max(3000, (int) config('dashboard.refresh_interval_ms', 10000));
        $gestao = [
            ['route' => 'panel.admin.users.index', 'icon' => 'fa-users-cog', 'color' => 'blue', 'title' => 'Usuários', 'desc' => 'Membros e níveis'],
            ['route' => 'panel.admin.plans.index', 'icon' => 'fa-gem', 'color' => 'indigo', 'title' => 'Planos', 'desc' => 'Pacotes e preços'],
            ['route' => 'panel.admin.orders.index', 'icon' => 'fa-shopping-basket', 'color' => 'emerald', 'title' => 'Vendas', 'desc' => 'Pedidos realizados'],
            ['route' => 'panel.admin.invoices.index', 'icon' => 'fa-file-invoice-dollar', 'color' => 'amber', 'title' => 'Faturas', 'desc' => 'Gestão de faturamento'],
            ['route' => 'admin.partners.index', 'icon' => 'fa-handshake', 'color' => 'sky', 'title' => 'Parceiros', 'desc' => 'Empresas e benefícios'],
        ];
        $conteudo = [
            ['route' => 'panel.admin.courses.index', 'icon' => 'fa-graduation-cap', 'color' => 'purple', 'title' => 'Cursos', 'desc' => 'Trilhas e aulas'],
            ['route' => 'panel.admin.mentorships.index', 'icon' => 'fa-chalkboard-teacher', 'color' => 'fuchsia', 'title' => 'Mentorias', 'desc' => 'Agenda e sessões'],
            ['route' => 'panel.admin.events.index', 'icon' => 'fa-calendar-day', 'color' => 'rose', 'title' => 'Eventos', 'desc' => 'Workshops e lives'],
            ['route' => 'panel.admin.certificates.index', 'icon' => 'fa-award', 'color' => 'orange', 'title' => 'Certificados', 'desc' => 'Emissão e design'],
        ];
        $ajustes = [
            ['route' => 'panel.admin.settings', 'icon' => 'fa-sliders-h', 'color' => 'slate', 'title' => 'Gerais', 'desc' => 'Identidade e dados', 'param' => ['group' => 'general']],
            ['route' => 'panel.admin.settings', 'icon' => 'fa-credit-card', 'color' => 'sky', 'title' => 'Gateways', 'desc' => 'Meios de pagamento', 'param' => ['group' => 'gateway']],
            ['route' => 'panel.admin.settings', 'icon' => 'fa-envelope-open-text', 'color' => 'cyan', 'title' => 'SMTP', 'desc' => 'Envio de e-mails', 'param' => ['group' => 'smtp']],
            ['route' => 'panel.admin.mailtemplates.index', 'icon' => 'fa-at', 'color' => 'violet', 'title' => 'Templates', 'desc' => 'Design das mensagens'],
        ];
        $totalUsersHealth = array_sum($customerHealth ?? []);
        $highHealthPercent = $totalUsersHealth > 0 ? (($customerHealth['Alta'] ?? 0) / $totalUsersHealth) * 100 : 0;
        $legacyAdminDashboardUrl = app('router')->has('admin.dashboard') ? route('admin.dashboard') : null;
    @endphp

    <div class="space-y-8">
        <div class="bg-gradient-to-br from-blue-700 via-indigo-800 to-slate-900 dark:from-blue-900/80 dark:via-indigo-950 dark:to-slate-950 rounded-[2.5rem] p-10 text-white shadow-[0_20px_60px_-15px_rgba(37,99,235,0.4)] dark:shadow-none border border-blue-400/20 dark:border-white/5 relative overflow-hidden group">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="max-w-2xl">
                    <h2 class="text-3xl font-bold mb-3">Central de Administração</h2>
                    <p class="text-blue-100/90 text-lg font-medium">
                        Gerencie a plataforma em um único lugar, com indicadores globais e atalhos operacionais.
                    </p>
                </div>
                <div class="shrink-0">
                    <a href="{{ route('panel.admin.quick-scanner') }}" class="inline-flex items-center gap-3 px-8 py-4 bg-white text-blue-700 rounded-3xl font-black text-lg hover:bg-blue-50 hover:scale-105 transition-all shadow-xl shadow-black/20 group">
                        <i class="fas fa-qrcode text-2xl group-hover:rotate-12 transition-transform"></i>
                        Escanear Ingressos
                    </a>
                </div>
            </div>
            <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-white/10 dark:bg-blue-500/10 rounded-full blur-[100px] group-hover:bg-white/15 transition-all duration-1000 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-72 h-72 bg-blue-400/20 dark:bg-purple-500/10 rounded-full blur-[80px] group-hover:bg-blue-400/30 transition-all duration-1000 pointer-events-none"></div>
        </div>

        {{-- Atalhos rápidos do superadmin (configurações críticas) --}}
        @if(auth()->user() && auth()->user()->isSuperAdmin())
            @php
                $quickAdminShortcuts = [
                    [
                        'label' => 'Armazenamento (S3)',
                        'desc' => 'Configurar IDrive e2 e migrar arquivos',
                        'url' => route('panel.admin.settings', ['group' => 'storage']),
                        'icon' => 'fa-cloud',
                        'color' => 'sky',
                    ],
                    [
                        'label' => 'Pagamentos',
                        'desc' => 'Mercado Pago, SumUp e taxas',
                        'url' => route('panel.admin.settings', ['group' => 'gateway']),
                        'icon' => 'fa-credit-card',
                        'color' => 'emerald',
                    ],
                    [
                        'label' => 'SMTP / E-mails',
                        'desc' => 'Servidor de envio e templates',
                        'url' => route('panel.admin.settings', ['group' => 'smtp']),
                        'icon' => 'fa-envelope',
                        'color' => 'violet',
                    ],
                    [
                        'label' => 'Sistema',
                        'desc' => 'Manutenção, debug, limites',
                        'url' => route('panel.admin.settings', ['group' => 'system']),
                        'icon' => 'fa-server',
                        'color' => 'amber',
                    ],
                ];
            @endphp
            <div class="space-y-4">
                <div class="px-2">
                    <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Atalhos do Superadmin</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Acesso direto às configurações críticas da plataforma.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach($quickAdminShortcuts as $shortcut)
                        <a href="{{ $shortcut['url'] }}"
                            class="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-{{ $shortcut['color'] }}-300 dark:hover:border-{{ $shortcut['color'] }}-700">
                            <div class="flex items-start gap-4">
                                <div class="shrink-0 w-12 h-12 rounded-xl bg-{{ $shortcut['color'] }}-50 dark:bg-{{ $shortcut['color'] }}-900/30 text-{{ $shortcut['color'] }}-600 dark:text-{{ $shortcut['color'] }}-300 flex items-center justify-center">
                                    <i class="fas {{ $shortcut['icon'] }} text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-black text-sm text-slate-800 dark:text-white truncate">{{ $shortcut['label'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-snug">{{ $shortcut['desc'] }}</p>
                                </div>
                                <i class="fas fa-arrow-right text-slate-300 dark:text-slate-600 group-hover:text-{{ $shortcut['color'] }}-500 group-hover:translate-x-1 transition-all"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="space-y-6" id="panel-admin-service-visits">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-2">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Rastreio em tempo real</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Visitas do site e dos produtos atualizadas automaticamente.</p>
                </div>
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-300 text-xs font-black uppercase tracking-widest">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Ao vivo
                </span>
            </div>

            @if($serviceVisitsEnabled ?? false)
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    @foreach([
                        ['id' => 'panel-admin-visits-total', 'label' => 'Total de visitas', 'value' => $serviceVisitSummary['total'] ?? 0, 'icon' => 'fa-chart-line', 'color' => 'blue'],
                        ['id' => 'panel-admin-visits-day', 'label' => 'Últimas 24 horas', 'value' => $serviceVisitSummary['last_24h'] ?? 0, 'icon' => 'fa-clock', 'color' => 'emerald'],
                        ['id' => 'panel-admin-visits-site', 'label' => 'Site institucional', 'value' => $serviceVisitSummary['site'] ?? 0, 'icon' => 'fa-globe', 'color' => 'violet'],
                        ['id' => 'panel-admin-visits-products', 'label' => 'Produtos monitorados', 'value' => $serviceVisitSummary['monitored_products'] ?? 0, 'icon' => 'fa-layer-group', 'color' => 'amber'],
                    ] as $metric)
                        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl p-6 rounded-[2rem] border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] dark:hover:shadow-[0_20px_50px_rgba(37,99,235,0.2)] hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                            <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            <div class="w-12 h-12 rounded-2xl bg-{{ $metric['color'] }}-50 dark:bg-{{ $metric['color'] }}-900/20 text-{{ $metric['color'] }}-600 dark:text-{{ $metric['color'] }}-300 flex items-center justify-center mb-5">
                                <i class="fas {{ $metric['icon'] }} text-lg"></i>
                            </div>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $metric['label'] }}</p>
                            <p class="mt-3 text-4xl font-black text-slate-900 dark:text-white" id="{{ $metric['id'] }}">{{ $metric['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-wrap gap-3" id="panel-admin-visit-chips">
                    @foreach([
                        'Cursos' => $serviceVisitSummary['curso'] ?? 0,
                        'Eventos' => $serviceVisitSummary['evento'] ?? 0,
                        'Mentorias' => $serviceVisitSummary['mentoria'] ?? 0,
                        'Palestras' => $serviceVisitSummary['palestra'] ?? 0,
                    ] as $label => $value)
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 text-sm font-semibold text-slate-600 dark:text-slate-300">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>{{ $label }}: <strong>{{ $value }}</strong>
                        </span>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl rounded-[2.5rem] border border-white/50 dark:border-slate-800/60 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] hover:-translate-y-1 hover:shadow-[0_25px_50px_-12px_rgba(0,0,0,0.1)] transition-all duration-500 p-8 relative overflow-hidden group/panel">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-3xl group-hover/panel:bg-blue-500/10 transition-all duration-700 pointer-events-none"></div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h4 class="text-xl font-bold text-slate-900 dark:text-white">Produtos mais visitados</h4>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Itens com maior volume de visitas no momento.</p>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-300 flex items-center justify-center">
                                <i class="fas fa-bullseye"></i>
                            </div>
                        </div>
                        <div class="space-y-4" id="panel-admin-top-items">
                            @forelse($serviceVisitTopItems as $item)
                                <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $item['label'] }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['type'] }}@if(!empty($item['owner_name'])) • {{ $item['owner_name'] }}@endif</p>
                                    </div>
                                    <span class="text-lg font-black text-blue-600 dark:text-blue-300">{{ $item['total'] }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">Ainda não há visitas registradas.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl rounded-[2.5rem] border border-white/50 dark:border-slate-800/60 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] hover:-translate-y-1 hover:shadow-[0_25px_50px_-12px_rgba(0,0,0,0.1)] transition-all duration-500 p-8 relative overflow-hidden group/panel">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full -mr-16 -mt-16 blur-3xl group-hover/panel:bg-emerald-500/10 transition-all duration-700 pointer-events-none"></div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h4 class="text-xl font-bold text-slate-900 dark:text-white">Responsáveis em destaque</h4>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Segmentação por dono de curso, evento e mentoria.</p>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">
                                <i class="fas fa-user-tie"></i>
                            </div>
                        </div>
                        <div class="space-y-4" id="panel-admin-owner-leaders">
                            @forelse($serviceVisitOwnerLeaders as $leader)
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-black text-slate-900 dark:text-white">{{ $leader['name'] }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Cursos {{ $leader['curso'] }} • Eventos {{ $leader['evento'] }} • Mentorias {{ $leader['mentoria'] }}</p>
                                        </div>
                                        <span class="text-lg font-black text-emerald-600 dark:text-emerald-300">{{ $leader['total'] }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">Ainda não há responsáveis ranqueados.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-700 rounded-[2.5rem] p-8 text-sm text-slate-500 dark:text-slate-400">
                    O rastreio de visitas ainda não está disponível neste ambiente.
                </div>
            @endif
        </div>

        <div>
            <div class="flex items-center gap-3 mb-6 px-2">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-wallet text-sm"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Gestão & Financeiro</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($gestao as $item)
                    <a href="{{ route($item['route']) }}" class="group relative bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl p-6 rounded-3xl border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] dark:hover:shadow-[0_20px_50px_rgba(37,99,235,0.2)] hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                        <div class="w-14 h-14 rounded-2xl bg-{{ $item['color'] }}-50 dark:bg-{{ $item['color'] }}-900/20 text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas {{ $item['icon'] }} text-xl"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-lg mb-1">{{ $item['title'] }}</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed">{{ $item['desc'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>

        <div>
            <div class="flex items-center gap-3 mb-6 px-2">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i class="fas fa-heartbeat text-sm"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Saúde da Comunidade</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl p-8 rounded-[2.5rem] border border-white/50 dark:border-slate-800/60 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] hover:-translate-y-1 hover:shadow-[0_25px_50px_-12px_rgba(0,0,0,0.1)] transition-all duration-500 flex flex-col items-center text-center relative overflow-hidden group/health">
                    <div class="absolute top-0 left-0 w-32 h-32 bg-emerald-500/5 rounded-full -ml-16 -mt-16 blur-3xl group-hover/health:bg-emerald-500/10 transition-all duration-700 pointer-events-none"></div>
                    <div class="relative w-32 h-32 mb-6">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-100 dark:text-slate-800" />
                            <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent"
                                stroke-dasharray="{{ (2 * pi() * 58) * ($highHealthPercent / 100) }} {{ (2 * pi() * 58) }}"
                                class="text-emerald-500 transition-all duration-1000" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-black text-slate-900 dark:text-white">{{ round($highHealthPercent) }}%</span>
                            <span class="text-[10px] uppercase font-bold text-slate-400">Engajamento</span>
                        </div>
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Saúde Global</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Percentual de membros com plano ativo e perfil completo.</p>
                </div>

                <div class="lg:col-span-2 bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl p-8 rounded-[2.5rem] border border-white/50 dark:border-slate-800/60 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] hover:-translate-y-1 hover:shadow-[0_25px_50px_-12px_rgba(0,0,0,0.1)] transition-all duration-500 relative overflow-hidden group/dist">
                    <div class="absolute bottom-0 right-0 w-40 h-40 bg-blue-500/5 rounded-full -mr-20 -mb-20 blur-3xl group-hover/dist:bg-blue-500/10 transition-all duration-700 pointer-events-none"></div>
                    <div class="relative z-10">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h4 class="text-xl font-bold text-slate-900 dark:text-white">Status dos Membros</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Distribuição baseada em atividade e perfil.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold text-slate-500">Total: {{ $totalUsersHealth }}</span>
                        </div>
                    </div>

                    <div class="space-y-6">
                        @foreach(['Alta' => ['color' => 'emerald', 'label' => 'Alta (Ótima)', 'icon' => 'fa-check-circle'], 'Média' => ['color' => 'amber', 'label' => 'Média (Regular)', 'icon' => 'fa-exclamation-circle'], 'Baixa' => ['color' => 'rose', 'label' => 'Baixa (Crítica)', 'icon' => 'fa-times-circle']] as $key => $meta)
                            @php
                                $count = $customerHealth[$key] ?? 0;
                                $percent = $totalUsersHealth > 0 ? ($count / $totalUsersHealth) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <i class="fas {{ $meta['icon'] }} text-{{ $meta['color'] }}-500"></i>
                                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $meta['label'] }}</span>
                                    </div>
                                    <span class="text-sm font-black text-slate-900 dark:text-white">{{ $count }} membros ({{ round($percent) }}%)</span>
                                </div>
                                <div class="w-full h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-{{ $meta['color'] }}-500 transition-all duration-1000" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="flex items-center gap-3 mb-6 px-2">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <i class="fas fa-layer-group text-sm"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Educação & Conteúdo</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($conteudo as $item)
                    <a href="{{ route($item['route']) }}" class="group relative bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl p-6 rounded-3xl border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] dark:hover:shadow-[0_20px_50px_rgba(37,99,235,0.2)] hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                        <div class="w-14 h-14 rounded-2xl bg-{{ $item['color'] }}-50 dark:bg-{{ $item['color'] }}-900/20 text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas {{ $item['icon'] }} text-xl"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-lg mb-1">{{ $item['title'] }}</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed">{{ $item['desc'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>

        <div>
            <div class="flex items-center gap-3 mb-6 px-2">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center">
                    <i class="fas fa-sliders-h text-sm"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Ajustes & Configurações</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($ajustes as $item)
                    <a href="{{ route($item['route'], $item['param'] ?? []) }}" class="group relative bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl p-6 rounded-3xl border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] dark:hover:shadow-[0_20px_50px_rgba(37,99,235,0.2)] hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                        <div class="w-13 h-13 rounded-2xl bg-{{ $item['color'] }}-50 dark:bg-{{ $item['color'] }}-900/20 text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas {{ $item['icon'] }} text-xl"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-lg mb-1">{{ $item['title'] }}</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed">{{ $item['desc'] }}</p>
                    </a>
                @endforeach

                @if(auth()->user()->isSuperAdmin() && $legacyAdminDashboardUrl)
                    <a href="{{ $legacyAdminDashboardUrl }}" target="_blank" class="group relative bg-slate-50/80 dark:bg-slate-950/80 backdrop-blur-3xl p-6 rounded-3xl border border-slate-300/80 dark:border-slate-700/80 border-dashed hover:border-blue-400 dark:hover:border-blue-600 hover:bg-blue-50/50 dark:hover:bg-blue-900/30 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="w-13 h-13 rounded-2xl bg-white dark:bg-slate-900 text-slate-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas fa-external-link-alt text-lg"></i>
                        </div>
                        <h4 class="font-bold text-slate-500 dark:text-slate-400 text-lg mb-1">Painel Legacy</h4>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium italic">Acesso fallback ao AdminLTE</p>
                    </a>
                @endif
            </div>
        </div>

        {{-- SYSTEM HEALTH WIDGET --}}
        <div>
            <div class="flex items-center gap-3 mb-6 px-2">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center">
                    <i class="fas fa-server text-sm"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Saúde do Sistema</h3>
                <button type="button" id="btn-refresh-health-panel" class="ml-auto text-slate-400 hover:text-blue-500 transition-colors" title="Atualizar">
                    <i class="fas fa-sync-alt text-sm"></i>
                </button>
            </div>
            <div id="system-health-panel" class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl rounded-[2.5rem] border border-white/50 dark:border-slate-800/60 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] p-8">
                <div class="text-center text-slate-400 py-4">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Carregando informações do sistema...
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('partials.service-visits-realtime')
    <script>
        function renderPanelAdminServiceVisits(payload) {
            const summary = payload.serviceVisitSummary || {};
            const topItems = payload.serviceVisitTopItems || [];
            const leaders = payload.serviceVisitOwnerLeaders || [];

            [
                ['panel-admin-visits-total', summary.total || 0],
                ['panel-admin-visits-day', summary.last_24h || 0],
                ['panel-admin-visits-site', summary.site || 0],
                ['panel-admin-visits-products', summary.monitored_products || 0],
            ].forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = value;
                }
            });

            const chips = document.getElementById('panel-admin-visit-chips');
            if (chips) {
                chips.innerHTML = [
                    ['Cursos', summary.curso || 0],
                    ['Eventos', summary.evento || 0],
                    ['Mentorias', summary.mentoria || 0],
                    ['Palestras', summary.palestra || 0],
                ].map(([label, value]) => `
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 text-sm font-semibold text-slate-600 dark:text-slate-300">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>${label}: <strong>${value}</strong>
                    </span>
                `).join('');
            }

            const topItemsContainer = document.getElementById('panel-admin-top-items');
            if (topItemsContainer) {
                topItemsContainer.innerHTML = topItems.length
                    ? topItems.map((item) => `
                        <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                            <div class="min-w-0">
                                <p class="text-sm font-black text-slate-900 dark:text-white truncate">${item.label}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">${item.type}${item.owner_name ? ` • ${item.owner_name}` : ''}</p>
                            </div>
                            <span class="text-lg font-black text-blue-600 dark:text-blue-300">${item.total}</span>
                        </div>
                    `).join('')
                    : '<p class="text-sm text-slate-500 dark:text-slate-400">Ainda não há visitas registradas.</p>';
            }

            const leadersContainer = document.getElementById('panel-admin-owner-leaders');
            if (leadersContainer) {
                leadersContainer.innerHTML = leaders.length
                    ? leaders.map((leader) => `
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-black text-slate-900 dark:text-white">${leader.name}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Cursos ${leader.curso} • Eventos ${leader.evento} • Mentorias ${leader.mentoria}</p>
                                </div>
                                <span class="text-lg font-black text-emerald-600 dark:text-emerald-300">${leader.total}</span>
                            </div>
                        </div>
                    `).join('')
                    : '<p class="text-sm text-slate-500 dark:text-slate-400">Ainda não há responsáveis ranqueados.</p>';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (!document.getElementById('panel-admin-service-visits')) {
                return;
            }

            const payload = {!! json_encode([
                'serviceVisitSummary' => $serviceVisitSummary,
                'serviceVisitTopItems' => $serviceVisitTopItems,
                'serviceVisitOwnerLeaders' => $serviceVisitOwnerLeaders,
            ]) !!};
            renderPanelAdminServiceVisits(payload);

            window.UNNServiceVisitsRealtime.start({
                statsUrl: @json(route('panel.admin.dashboard.stats')),
                refreshMs: @json($dashboardRefreshMs),
                onPayload: renderPanelAdminServiceVisits,
            });

            // System Health Widget
            function loadSystemHealthPanel() {
                const container = document.getElementById('system-health-panel');
                if (!container) return;

                fetch(@json(route('panel.admin.dashboard.system-health')), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    const diskColor = data.disk.percent > 90 ? 'rose' : (data.disk.percent > 75 ? 'amber' : 'emerald');
                    const cronColor = data.cron.active ? 'emerald' : 'rose';
                    const cronLabel = data.cron.active ? 'Ativo' : 'Inativo';

                    container.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-xl bg-${diskColor}-50 dark:bg-${diskColor}-900/20 text-${diskColor}-600 dark:text-${diskColor}-300 flex items-center justify-center">
                                        <i class="fas fa-hdd"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase">Disco</p>
                                        <p class="text-lg font-black text-slate-900 dark:text-white">${data.disk.percent}%</p>
                                    </div>
                                </div>
                                <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-${diskColor}-500 rounded-full transition-all" style="width:${data.disk.percent}%"></div>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">${data.disk.used_gb} GB / ${data.disk.total_gb} GB (${data.disk.free_gb} GB livres)</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-300 flex items-center justify-center">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase">Banco de Dados</p>
                                        <p class="text-lg font-black text-slate-900 dark:text-white">${data.database.size_mb} MB</p>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">PHP ${data.php_version} • Laravel ${data.laravel_version}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">
                                        <i class="fas fa-users-cog"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase">Capacidade</p>
                                        <p class="text-lg font-black text-slate-900 dark:text-white">~${data.capacity.estimated_concurrent}</p>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">${data.capacity.hosting_type} (${data.capacity.memory_limit})</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-xl bg-${cronColor}-50 dark:bg-${cronColor}-900/20 text-${cronColor}-600 dark:text-${cronColor}-300 flex items-center justify-center">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase">Cron</p>
                                        <p class="text-lg font-black text-${cronColor}-600 dark:text-${cronColor}-300">${cronLabel}</p>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">${data.cron.last_run ? 'Último: ' + data.cron.last_run : 'Nunca executou'}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                                <i class="fas fa-user-clock text-blue-500"></i>
                                <div>
                                    <p class="text-xs text-slate-400">Online agora</p>
                                    <p class="font-bold text-slate-900 dark:text-white">${data.users.online_now} <span class="text-xs text-slate-400 font-normal">/ ${data.users.total} total</span></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                                <i class="fas fa-shopping-cart text-amber-500"></i>
                                <div>
                                    <p class="text-xs text-slate-400">Pedidos Pendentes</p>
                                    <p class="font-bold text-slate-900 dark:text-white">${data.orders_pending}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                                <i class="fas fa-tasks text-indigo-500"></i>
                                <div>
                                    <p class="text-xs text-slate-400">Jobs na Fila</p>
                                    <p class="font-bold text-slate-900 dark:text-white">${data.queue.pending_jobs}</p>
                                </div>
                            </div>
                        </div>
                        ${data.disk.percent > 90 ? '<div class="mt-4 p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800"><p class="text-sm text-rose-700 dark:text-rose-300 font-semibold"><i class="fas fa-exclamation-triangle mr-2"></i>Disco com ' + data.disk.percent + '% de uso. Apenas ' + data.disk.free_gb + ' GB livres.</p></div>' : ''}
                        ${!data.cron.active ? '<div class="mt-4 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800"><p class="text-sm text-amber-700 dark:text-amber-300 font-semibold"><i class="fas fa-exclamation-circle mr-2"></i>Cron inativo. Verifique o middleware RunInternalCron.</p></div>' : ''}
                    `;
                })
                .catch(() => {
                    container.innerHTML = '<p class="text-sm text-slate-500 text-center py-4"><i class="fas fa-exclamation-circle mr-2"></i>Não foi possível carregar informações do sistema.</p>';
                });
            }

            loadSystemHealthPanel();
            document.getElementById('btn-refresh-health-panel')?.addEventListener('click', loadSystemHealthPanel);
        });
    </script>
@endpush
