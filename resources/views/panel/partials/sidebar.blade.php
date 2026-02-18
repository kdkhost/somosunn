@php
    $user = auth()->user();
    $plan = $user ? $user->activePlan() : null;
    $isImpersonatingAdmin = session()->has('impersonator_id') && session()->get('impersonator_is_admin');
    $currentTheme = $user->theme_pref ?? 'light';

    $navItemClass = function (bool $active = false) {
        $base = 'flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-200';
        return $active
            ? $base . ' bg-blue-600/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 shadow-sm border border-blue-600/5 dark:border-blue-500/10'
            : $base . ' text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200';
    };
@endphp

<div
    class="{{ ($mobile ?? false) ? '' : 'bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 p-6 sticky top-24' }} transition-colors duration-300">
    <div class="flex items-center gap-4 mb-8">
        <div
            class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700">
            @if($user->profile_photo_url && !str_contains($user->profile_photo_url, 'default-user.svg'))
                <img src="{{ $user->profile_photo_url }}" alt="Avatar" class="w-full h-full object-cover">
            @else
                <i class="fas fa-user text-slate-400 dark:text-slate-500 text-lg"></i>
            @endif
        </div>
        <div class="min-w-0">
            <div class="font-bold text-slate-900 dark:text-white truncate text-lg">{{ $user->name }}</div>
            <div
                class="inline-flex items-center px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-tight">
                {{ $plan?->name ? $plan->name : 'Sem plano' }}
            </div>
        </div>
    </div>

    <div class="space-y-1">
        <a href="{{ route('panel.dashboard') }}" class="{{ $navItemClass(request()->routeIs('panel.dashboard')) }}">
            <i class="fas fa-th-large w-5 opacity-80"></i>
            Visão geral
        </a>

        <a href="{{ route('panel.profile.edit') }}" class="{{ $navItemClass(request()->routeIs('panel.profile.*')) }}">
            <i class="fas fa-user-circle w-5 opacity-80"></i>
            Meu perfil
        </a>

        <a href="{{ route('panel.wishlist.index') }}"
            class="{{ $navItemClass(request()->routeIs('panel.wishlist.*')) }}">
            <i class="fas fa-heart w-5 opacity-80"></i>
            Minha Lista
        </a>

        <a href="{{ route('portal') }}" class="{{ $navItemClass(request()->is('portal')) }}">
            <i class="fas fa-home w-5 opacity-80"></i>
            Portal
        </a>

        @if($user->canAccessFeature('community') || $isImpersonatingAdmin)
            <a href="{{ route('social.feed') }}" class="{{ $navItemClass(request()->routeIs('social.feed')) }}">
                <i class="fas fa-users w-5 opacity-80"></i>
                Comunidade
            </a>
        @endif

        @if($user->canAccessFeature('chat') || $isImpersonatingAdmin)
            <a href="{{ route('chat.index') }}" class="{{ $navItemClass(request()->routeIs('chat.*')) }}">
                <i class="fas fa-comments w-5 opacity-80"></i>
                Chat
            </a>
        @endif

        @if($user->canAccessFeature('marketplace.buy') || $isImpersonatingAdmin)
            <a href="{{ route('marketplace.index') }}" class="{{ $navItemClass(request()->routeIs('marketplace.*')) }}">
                <i class="fas fa-store w-5 opacity-80"></i>
                Marketplace
            </a>
        @endif

        @if((method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace()) || $isImpersonatingAdmin)
            <div class="pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
                <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4 px-4">
                    Vendedor
                </div>
                <a href="{{ route('panel.marketplace.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.marketplace.index')) }}">
                    <i class="fas fa-chart-line w-5 opacity-80"></i>
                    Painel de vendas
                </a>
                <a href="{{ route('panel.marketplace.payments') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.marketplace.payments')) }}">
                    <i class="fas fa-credit-card w-5 opacity-80"></i>
                    Pagamentos
                </a>
                <a href="{{ route('panel.marketplace.sales') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.marketplace.sales')) }}">
                    <i class="fas fa-receipt w-5 opacity-80"></i>
                    Minhas vendas
                </a>
            </div>
        @endif

        @if($user->isAdmin())
            <div class="pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
                <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4 px-4">
                    Administração
                </div>

                <a href="{{ route('panel.admin.dashboard') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.dashboard')) }}">
                    <i class="fas fa-shield-alt w-5 opacity-80"></i>
                    Painel administrativo
                </a>

                {{-- Gestão --}}
                <div
                    class="mt-4 mb-2 px-4 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">
                    Gestão</div>
                <a href="{{ route('panel.admin.users.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.users.*')) }}">
                    <i class="fas fa-users-cog w-5 opacity-80"></i>
                    Usuários
                </a>
                <a href="{{ route('panel.admin.plans.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.plans.*')) }}">
                    <i class="fas fa-gem w-5 opacity-80"></i>
                    Planos / Pacotes
                </a>
                <a href="{{ route('panel.admin.orders.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.orders.*')) }}">
                    <i class="fas fa-shopping-cart w-5 opacity-80"></i>
                    Vendas
                </a>
                <a href="{{ route('panel.admin.invoices.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.invoices.*')) }}">
                    <i class="fas fa-file-invoice w-5 opacity-80"></i>
                    Faturas
                </a>
                <a href="{{ route('panel.admin.coupons.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.coupons.*')) }}">
                    <i class="fas fa-ticket-alt w-5 opacity-80"></i>
                    Cupons
                </a>

                {{-- Conteúdo --}}
                <div
                    class="mt-4 mb-2 px-4 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">
                    Conteúdo</div>
                <a href="{{ route('panel.admin.courses.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.courses.*')) }}">
                    <i class="fas fa-graduation-cap w-5 opacity-80"></i>
                    Cursos
                </a>
                <a href="{{ route('panel.admin.mentorships.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.mentorships.*')) }}">
                    <i class="fas fa-chalkboard-teacher w-5 opacity-80"></i>
                    Mentorias
                </a>
                <a href="{{ route('panel.admin.events.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.events.*')) }}">
                    <i class="fas fa-calendar-alt w-5 opacity-80"></i>
                    Eventos
                </a>
                <a href="{{ route('panel.admin.certificates.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.certificates.*')) }}">
                    <i class="fas fa-certificate w-5 opacity-80"></i>
                    Certificados
                </a>

                {{-- Configurações --}}
                <div
                    class="mt-4 mb-2 px-4 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">
                    Ajustes</div>
                <a href="{{ route('panel.admin.settings', ['group' => 'general']) }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.settings') && request('group') == 'general') }}">
                    <i class="fas fa-cogs w-5 opacity-80"></i>
                    Gerais
                </a>
                <a href="{{ route('panel.admin.settings', ['group' => 'gateway']) }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.settings') && request('group') == 'gateway') }}">
                    <i class="fas fa-credit-card w-5 opacity-80"></i>
                    Pagamentos
                </a>
                <a href="{{ route('panel.admin.settings', ['group' => 'smtp']) }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.settings') && request('group') == 'smtp') }}">
                    <i class="fas fa-envelope w-5 opacity-80"></i>
                    SMTP
                </a>
                <a href="{{ route('panel.admin.mailtemplates.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.mailtemplates.*')) }}">
                    <i class="fas fa-at w-5 opacity-80"></i>
                    E-mails
                </a>
            </div>
        @endif

        {{-- Theme Toggle --}}
        <div class="pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
            <button onclick="toggleTheme()"
                class="w-full flex items-center justify-between gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 group">
                <div class="flex items-center gap-3">
                    <i
                        class="fas {{ $currentTheme === 'dark' ? 'fa-sun' : 'fa-moon' }} w-5 opacity-80 group-hover:rotate-12 transition-transform"></i>
                    <span>Modo {{ $currentTheme === 'dark' ? 'Claro' : 'Escuro' }}</span>
                </div>
                <div class="w-10 h-5 bg-slate-200 dark:bg-slate-700 rounded-full relative transition-colors">
                    <div
                        class="absolute top-1 {{ $currentTheme === 'dark' ? 'right-1' : 'left-1' }} w-3 h-3 bg-white dark:bg-blue-400 rounded-full shadow-sm transition-all">
                    </div>
                </div>
            </button>
        </div>
    </div>
</div>

<script>
    function toggleTheme() {
        const theme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

        // UI Update
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Save Preference
        fetch('{{ route("panel.theme.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ theme: theme })
        }).then(r => r.json()).then(data => {
            // Optional: reload to apply all server-side classes correctly if needed
            window.location.reload();
        });
    }
</script>