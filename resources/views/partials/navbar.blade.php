{{-- Navbar UNN com submenus e sidebar mobile --}}
@php
    $logoSrc = \App\Models\Setting::getUrl('logo_front') ?: \App\Models\Setting::getUrl('logo_image') ?: asset('img/logo.svg');
    $menuItems = [
        [
            'label' => 'Institucional',
            'href' => route('sobre'),
            'children' => [
                ['label' => 'Sobre a UNN', 'href' => route('sobre')],
                ['label' => 'Manifesto', 'href' => route('manifesto')],
                ['label' => 'Quem somos', 'href' => route('quem-somos')],
                ['label' => 'Como funciona', 'href' => route('como-funciona')],
                ['label' => 'Valores', 'href' => route('valores')],
                ['label' => 'Contato', 'href' => route('contato')],
            ],
        ],
        [
            'label' => 'Comunidade',
            'href' => route('portal'),
            'setting_key' => 'feature_community',
            'children' => [
                ['label' => 'Portal', 'href' => route('portal')],
                ['label' => 'Feed Social', 'href' => route('social.feed'), 'setting_key' => 'feature_social'],
                ['label' => 'Cursos', 'href' => route('courses.index'), 'setting_key' => 'feature_courses'],
                ['label' => 'Eventos', 'href' => route('events.index'), 'setting_key' => 'feature_events'],
                ['label' => 'Marketplace', 'href' => route('marketplace.index')],
                ['label' => 'Membros', 'href' => route('membros')],
                ['label' => 'Ranking', 'href' => route('ranking.public')],
                ['label' => 'Vagas Abertas', 'href' => route('jobs.public.index')],
            ],
        ],
        ['label' => 'Premium', 'href' => route('premium'), 'setting_key' => 'feature_premium'],
        ['label' => 'Somos Únicas', 'href' => route('somos-unicas')],
    ];

    $ctaLabel = 'Fazer parte';
    $ctaHref = route('register');
    $ctaClass = 'text-white shadow-lg';
    $ctaStyle = 'background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 55%, #1D3FC4 100%); box-shadow: 0 15px 30px -10px rgba(29,63,196,0.45);';

    $accountButtonClass = 'inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-bold text-white transition-all transform hover:scale-105 active:scale-95 whitespace-nowrap shadow-lg';
    $accountButtonStyle = 'background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 55%, #1D3FC4 100%); box-shadow: 0 15px 30px -10px rgba(29,63,196,0.45);';

    $isLogged = Auth::check();
    $currentUser = Auth::user();
    $filterVisibleChildren = function (array $children) use ($isLogged) {
        return array_values(array_filter($children, function (array $child) use ($isLogged) {
            $childSettingKey = $child['setting_key'] ?? null;
            $childEnabled = $childSettingKey ? \App\Models\Setting::get($childSettingKey, '1') === '1' : true;
            $childRequiresAuth = (bool) ($child['requires_auth'] ?? false);

            if ($childRequiresAuth && !$isLogged) {
                return false;
            }

            return $childEnabled;
        }));
    };

    $visibleMenuItems = array_values(array_filter(array_map(function (array $item) use ($filterVisibleChildren) {
        $settingKey = $item['setting_key'] ?? null;
        $isEnabled = $settingKey ? \App\Models\Setting::get($settingKey, '1') === '1' : true;

        if (!$isEnabled) {
            return null;
        }

        if (isset($item['children'])) {
            $item['children'] = $filterVisibleChildren($item['children']);
        }

        return $item;
    }, $menuItems)));
@endphp

<nav class="fixed inset-x-0 top-0 z-50 bg-white shadow-xl border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 md:px-10 lg:px-16 py-4 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
            <div class="inline-flex h-12 md:h-16 w-auto items-center justify-center overflow-hidden">
                <img src="{{ $logoSrc }}" alt="UNN" class="h-full w-auto object-contain"
                    onerror="this.style.display='none';">
            </div>
        </a>

        <div class="flex items-center gap-6">
            <div class="hidden lg:flex items-center gap-4 text-sm font-semibold text-gray-800">
                @foreach($visibleMenuItems as $item)
                    @if(!empty($item['children']))
                        <div class="relative group">
                            <a href="{{ $item['href'] }}"
                                class="inline-flex items-center gap-1 hover:text-[#1F5EDB] transition-colors py-2">
                                {{ $item['label'] }}
                                <i class="fas fa-chevron-down text-xs ml-0.5 opacity-70"></i>
                            </a>
                            <div class="absolute right-0 top-full pt-2 hidden group-hover:block z-40">
                                <div
                                    class="rounded-2xl bg-white shadow-xl border border-slate-100 min-w-[220px] py-2 overflow-hidden">
                                    @foreach($item['children'] as $child)
                                        <a href="{{ $child['href'] }}"
                                            class="block px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition bg-white">
                                            {{ $child['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ $item['href'] }}" class="hover:text-[#1F5EDB] transition-colors">{{ $item['label'] }}</a>
                    @endif
                @endforeach
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                @if($isLogged)
                    <div class="relative" x-data="{
                                    open: false,
                                    total: 0,
                                    items: [],
                                    loading: true,
                                    async fetchNotifications() {
                                        try {
                                            const r = await fetch('{{ route('notifications.hub') }}');
                                            if (!r.ok) return;
                                            const data = await r.json();
                                            this.total = data.total || 0;
                                            this.items = (data.items || []).filter(i => i.count > 0);
                                        } catch(e) {
                                            console.error('Notification hub failed:', e);
                                        } finally {
                                            this.loading = false;
                                        }
                                    }
                                }" x-init="fetchNotifications(); setInterval(() => fetchNotifications(), 60000)">

                        <button @click="open = !open; fetchNotifications()"
                            class="text-gray-500 dark:text-gray-400 hover:text-blue-600 transition relative p-2 focus:outline-none">
                            <i class="fas fa-bell text-xl"></i>
                            <template x-if="total > 0">
                                <span x-text="total"
                                    class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-white dark:ring-slate-900 shadow-sm animate-pulse"></span>
                            </template>
                        </button>

                        <div x-show="open" @click.away="open = false"
                            class="absolute right-0 mt-3 w-72 sm:w-80 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-800 z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200"
                            style="display: none;">
                            <div
                                class="p-4 border-b border-slate-50 dark:border-slate-800 flex justify-between items-center">
                                <h3 class="font-bold text-gray-800 dark:text-white">Notificacoes</h3>
                                <span x-text="total + ' Alerta(s)'"
                                    class="text-[10px] font-bold bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded-full uppercase tracking-wider"></span>
                            </div>

                            <div class="max-h-[350px] overflow-y-auto no-scrollbar">
                                <template x-if="total == 0">
                                    <div class="p-8 text-center text-gray-400 dark:text-slate-500">
                                        <i class="fas fa-check-circle text-3xl opacity-20 mb-3 block"></i>
                                        <p class="text-sm">Tudo em dia por aqui!</p>
                                    </div>
                                </template>

                                <template x-for="item in items" :key="item.type">
                                    <a :href="item.route"
                                        class="flex items-center gap-4 px-4 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition border-b border-slate-50 dark:border-slate-800 last:border-0">
                                        <div :class="item.bg + ' ' + item.color"
                                            class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
                                            <i :class="item.icon"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-800 dark:text-white truncate"
                                                x-text="item.count + ' ' + item.label"></p>
                                            <p class="text-[11px] text-gray-500 dark:text-slate-400">Clique para visualizar
                                            </p>
                                        </div>
                                        <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                                    </a>
                                </template>
                            </div>

                            <div class="p-3 bg-gray-50 dark:bg-slate-950/50 text-center">
                                <a href="{{ route('notifications.index') }}"
                                    class="text-[11px] font-bold text-blue-600 hover:underline">Ver todas as
                                    notificacoes</a>
                            </div>
                        </div>
                    </div>
                @endif

                <button id="mobile-menu-toggle" type="button"
                    class="lg:hidden inline-flex items-center justify-center rounded-full border-0 p-2 min-w-[44px] min-h-[44px] text-[#1F5EDB] hover:bg-[#1F5EDB]/10 relative z-[60] active:scale-95 transition-transform touch-manipulation">
                    <span class="sr-only">Abrir menu</span>
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <div class="hidden lg:flex items-center gap-3">
                @if(!$isLogged)
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 rounded-full border border-[#1F5EDB] px-6 py-2 text-sm font-bold text-[#1F5EDB] hover:bg-[#1F5EDB]/10 transition-all whitespace-nowrap">
                        Entrar
                    </a>
                @else
                    <div class="relative" x-data="{ accountOpen: false }" @keydown.escape.window="accountOpen = false">
                        <button type="button" @click="accountOpen = !accountOpen" class="{{ $accountButtonClass }}"
                            style="{{ $accountButtonStyle }}">
                            Minha Conta
                            <i class="fas fa-chevron-down text-xs opacity-70 transition-transform"
                                :class="{ 'rotate-180': accountOpen }"></i>
                        </button>

                        <div x-show="accountOpen" x-transition.origin.top.right @click.outside="accountOpen = false"
                            class="absolute right-0 top-full pt-3 z-50" style="display: none;">
                            <div
                                class="rounded-2xl bg-white shadow-2xl border border-slate-100 min-w-[280px] py-3 overflow-hidden">
                                <div class="px-5 py-2 border-b border-slate-50 mb-2">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Membro UNN</p>
                                    <p class="text-sm font-bold text-gray-800 truncate">{{ $currentUser?->name }}</p>
                                </div>

                                <a href="{{ route('panel.dashboard') }}"
                                    class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                    <i class="fas fa-th-large w-5 opacity-70"></i>
                                    Painel do membro
                                </a>

                                <a href="{{ route('panel.profile.edit') }}"
                                    class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                    <i class="fas fa-user-circle w-5 opacity-70"></i>
                                    Meu perfil
                                </a>

                                <a href="{{ route('portal') }}"
                                    class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                    <i class="fas fa-home w-5 opacity-70"></i>
                                    Portal
                                </a>

                                <a href="{{ route('marketplace.index') }}"
                                    class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                    <i class="fas fa-store w-5 opacity-70"></i>
                                    Marketplace
                                </a>

                                @if($currentUser && method_exists($currentUser, 'canSellOnMarketplace') && $currentUser->canSellOnMarketplace())
                                    <a href="{{ route('panel.marketplace.payments') }}"
                                        class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                        <i class="fas fa-credit-card w-5 opacity-70"></i>
                                        Configurar pagamentos
                                    </a>

                                    <a href="{{ route('panel.marketplace.sales') }}"
                                        class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                        <i class="fas fa-receipt w-5 opacity-70"></i>
                                        Minhas vendas
                                    </a>
                                @endif

                                @if($currentUser && method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin())
                                    <div class="mt-2 pt-2 border-t border-slate-50">
                                        <p class="px-5 pb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                            Administracao</p>

                                        <a href="{{ route('panel.admin.dashboard') }}"
                                            class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                            <i class="fas fa-shield-alt w-5 opacity-70"></i>
                                            Painel administrativo
                                        </a>

                                        <a href="{{ route('panel.admin.users.index') }}"
                                            class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                            <i class="fas fa-users-cog w-5 opacity-70"></i>
                                            Usuarios
                                        </a>

                                        <a href="{{ route('panel.admin.settings', ['group' => 'general']) }}"
                                            class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                            <i class="fas fa-cogs w-5 opacity-70"></i>
                                            Configuracoes
                                        </a>
                                    </div>
                                @endif

                                <div class="mt-2 pt-2 border-t border-slate-50">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="flex w-full items-center gap-3 px-5 py-3 text-sm text-red-600 hover:bg-red-50 transition-all font-semibold">
                                            <i class="fas fa-sign-out-alt w-5 opacity-70"></i>
                                            Sair da conta
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!$isLogged)
                    <a href="{{ $ctaHref }}"
                        class="inline-flex items-center gap-2 rounded-full {{ $ctaClass }} px-7 py-3 text-sm font-bold whitespace-nowrap"
                        style="{{ $ctaStyle }}">
                        {{ $ctaLabel }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</nav>

<div id="mobile-menu" class="fixed inset-0 z-[100] hidden" aria-hidden="true">
    <div id="mobile-menu-overlay"
        class="absolute inset-0 bg-black/40 opacity-0 transition-opacity duration-300 pointer-events-none"></div>
    <div id="mobile-menu-panel"
        class="relative z-10 w-4/5 max-w-sm h-full bg-white border-r border-white/80 shadow-2xl transform -translate-x-full transition-transform duration-300 ease-out overflow-y-auto pointer-events-auto">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="inline-flex h-10 w-auto items-center justify-center overflow-hidden">
                    <img src="{{ $logoSrc }}" alt="UNN" class="h-full w-auto object-contain"
                        onerror="this.style.display='none';">
                </div>
            </div>
            <button id="mobile-menu-close"
                class="text-gray-500 hover:text-gray-900 text-2xl leading-none px-3">&times;</button>
        </div>

        @if($isLogged)
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Membro UNN</p>
                <p class="font-bold text-gray-800 truncate">{{ $currentUser?->name }}</p>
            </div>
        @endif

        <nav class="px-4 py-4 flex flex-col gap-1 text-gray-700">
            @foreach($visibleMenuItems as $item)
            @php($mobileSection = \Illuminate\Support\Str::slug($item['label']))
            @if(!empty($item['children']))
                <div data-mobile-section="{{ $mobileSection }}"
                    class="rounded-2xl border border-slate-100 bg-slate-50/80 px-3 py-3">
                    <div class="flex items-center justify-between px-1 pb-2">
                        <a href="{{ $item['href'] }}"
                            class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400 hover:text-[#1F5EDB] transition-colors">
                            {{ $item['label'] }}
                        </a>
                        <span
                            class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-slate-500 shadow-sm">
                            {{ count($item['children']) }} itens
                        </span>
                    </div>

                    <div class="flex flex-col gap-1">
                        @foreach($item['children'] as $child)
                            <a href="{{ $child['href'] }}"
                                class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-white hover:text-[#1F5EDB] transition-colors">
                                <span>{{ $child['label'] }}</span>
                                <i class="fas fa-chevron-right text-[11px] opacity-50"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <a href="{{ $item['href'] }}" data-mobile-section="{{ $mobileSection }}"
                    class="block rounded-xl px-4 py-2.5 font-semibold hover:bg-slate-100 transition-colors">
                    {{ $item['label'] }}
                </a>
            @endif
            @endforeach

            @if($isLogged)
                <div class="border-t border-slate-100 my-2"></div>

                <a href="{{ route('panel.dashboard') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-slate-100 transition">
                    <i class="fas fa-th-large w-4 opacity-70"></i> Painel
                </a>

                <a href="{{ route('panel.profile.edit') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-slate-100 transition">
                    <i class="fas fa-user-circle w-4 opacity-70"></i> Meu perfil
                </a>

                <a href="{{ route('marketplace.index') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-slate-100 transition">
                    <i class="fas fa-store w-4 opacity-70"></i> Marketplace
                </a>

                @if($currentUser && method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin())
                    <a href="{{ route('panel.admin.dashboard') }}"
                        class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-slate-100 transition">
                        <i class="fas fa-cog w-4 opacity-70"></i> Administracao
                    </a>

                    <a href="{{ route('panel.admin.settings', ['group' => 'general']) }}"
                        class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-slate-100 transition">
                        <i class="fas fa-sliders-h w-4 opacity-70"></i> Configuracoes
                    </a>
                @endif
            @endif
        </nav>

        <div class="px-4 pb-6 space-y-2">
            @if(!$isLogged)
                <a href="{{ route('login') }}"
                    class="inline-flex w-full items-center justify-center rounded-full border border-[#1F5EDB] px-6 py-2.5 text-sm font-semibold text-[#1F5EDB] hover:bg-[#1F5EDB]/10 transition">
                    Entrar
                </a>
                <a href="{{ route('register') }}"
                    class="inline-flex w-full items-center justify-center rounded-full {{ $ctaClass }} px-6 py-2.5 text-sm font-bold text-white transition"
                    style="{{ $ctaStyle }}">
                    {{ $ctaLabel }}
                </a>
            @else
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 transition">
                        <i class="fas fa-sign-out-alt w-4 opacity-70"></i> Sair
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>