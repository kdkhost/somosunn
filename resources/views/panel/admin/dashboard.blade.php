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
    @endphp

    <div class="space-y-8">
        <div class="bg-gradient-to-br from-blue-600 via-indigo-700 to-violet-800 rounded-[2.5rem] p-10 text-white shadow-2xl shadow-blue-500/20 relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-3xl font-bold mb-3">Central de Administração</h2>
                <p class="text-blue-100/90 max-w-2xl text-lg font-medium">
                    Gerencie a plataforma em um único lugar, com indicadores globais, acompanhamento em tempo real e atalhos operacionais.
                </p>
            </div>
            <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-64 h-64 bg-blue-400/20 rounded-full blur-2xl"></div>
        </div>

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
                        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm">
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
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm p-8">
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

                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm p-8">
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
                    <a href="{{ route($item['route']) }}" class="group bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/60 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
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
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex flex-col items-center text-center">
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

                <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm">
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
                    <a href="{{ route($item['route']) }}" class="group bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/60 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
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
                    <a href="{{ route($item['route'], $item['param'] ?? []) }}" class="group bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/60 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="w-13 h-13 rounded-2xl bg-{{ $item['color'] }}-50 dark:bg-{{ $item['color'] }}-900/20 text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas {{ $item['icon'] }} text-xl"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-lg mb-1">{{ $item['title'] }}</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed">{{ $item['desc'] }}</p>
                    </a>
                @endforeach

                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.dashboard') }}" target="_blank" class="group bg-slate-50 dark:bg-slate-950 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 border-dashed hover:border-blue-300 dark:hover:border-blue-700 hover:bg-blue-50/30 dark:hover:bg-blue-900/20 transition-all duration-300">
                        <div class="w-13 h-13 rounded-2xl bg-white dark:bg-slate-900 text-slate-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas fa-external-link-alt text-lg"></i>
                        </div>
                        <h4 class="font-bold text-slate-500 dark:text-slate-400 text-lg mb-1">Painel Legacy</h4>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium italic">Acesso fallback ao AdminLTE</p>
                    </a>
                @endif
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

            renderPanelAdminServiceVisits(@json([
                'serviceVisitSummary' => $serviceVisitSummary,
                'serviceVisitTopItems' => $serviceVisitTopItems,
                'serviceVisitOwnerLeaders' => $serviceVisitOwnerLeaders,
            ]));

            window.UNNServiceVisitsRealtime.start({
                statsUrl: @json(route('panel.admin.dashboard.stats')),
                refreshMs: @json($dashboardRefreshMs),
                onPayload: renderPanelAdminServiceVisits,
            });
        });
    </script>
@endpush
