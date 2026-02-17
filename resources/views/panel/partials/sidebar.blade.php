@php
    $user = auth()->user();
    $plan = $user ? $user->activePlan() : null;
    $isImpersonatingAdmin = session()->has('impersonator_id') && session()->get('impersonator_is_admin');

    $navItemClass = function (bool $active = false) {
        $base = 'flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition';
        return $active
            ? $base . ' bg-[#1F5EDB]/10 text-[#1F5EDB]'
            : $base . ' text-slate-700 hover:bg-slate-100';
    };
@endphp

<div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5 sticky top-24">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center shrink-0">
            @if($user->profile_photo_url && !str_contains($user->profile_photo_url, 'default-user.svg'))
                <img src="{{ $user->profile_photo_url }}" alt="Avatar" class="w-full h-full object-cover">
            @else
                <span class="text-slate-500 font-bold text-xl"
                    aria-hidden="true">{{ mb_substr((string) ($user->name ?? ''), 0, 1) }}</span>
            @endif
        </div>
        <div class="min-w-0">
            <div class="font-bold text-slate-900 truncate">{{ $user->name }}</div>
            <div class="text-xs text-slate-500 truncate">
                {{ $plan?->name ? $plan->name : 'Sem plano ativo' }}
            </div>
        </div>
    </div>

    <div class="mt-5 space-y-1">
        <a href="{{ route('panel.dashboard') }}" class="{{ $navItemClass(request()->routeIs('panel.dashboard')) }}">
            <i class="fas fa-th-large w-5 opacity-80"></i>
            Visão geral
        </a>

        <a href="{{ route('panel.profile.edit') }}" class="{{ $navItemClass(request()->routeIs('panel.profile.*')) }}">
            <i class="fas fa-user-circle w-5 opacity-80"></i>
            Meu perfil
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
            <div class="pt-4 mt-4 border-t border-slate-100">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                    Marketplace (Vendas)
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
            <div class="pt-4 mt-4 border-t border-slate-100">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 px-4">
                    Administração
                </div>

                <a href="{{ route('panel.admin.dashboard') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.dashboard')) }}">
                    <i class="fas fa-shield-alt w-5 opacity-80"></i>
                    Painel administrativo
                </a>

                {{-- Gestão --}}
                <div class="mt-4 mb-2 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Gestão</div>
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
                <div class="mt-4 mb-2 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Conteúdo</div>
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
                <div class="mt-4 mb-2 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ajustes</div>
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
    </div>
</div>