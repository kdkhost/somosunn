@php
    $user = auth()->user();
    $plan = $user ? $user->activePlan() : null;
    $currentTheme = $user->theme_pref ?? 'light';
    $canAccessInstructorArea = (method_exists($user, 'canAccessInstructorArea') && $user->canAccessInstructorArea());
    $hasPartnerProfile = false;
    if ($canAccessInstructorArea) {
        try {
            $hasPartnerProfile = \App\Models\Partner::where('user_id', $user->id)->exists();
        } catch (\Throwable $e) {
            $hasPartnerProfile = false;
        }
    }

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
            Visao geral
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

        @if($user->canAccessFeature('community'))
            <a href="{{ route('social.feed') }}" class="{{ $navItemClass(request()->routeIs('social.feed')) }}">
                <i class="fas fa-users w-5 opacity-80"></i>
                Comunidade
            </a>
        @endif

        @if($user->canAccessFeature('chat'))
            <a href="{{ route('chat.index') }}" class="{{ $navItemClass(request()->routeIs('chat.*')) }}">
                <i class="fas fa-comments w-5 opacity-80"></i>
                Chat
            </a>
        @endif

        @if($user->canAccessFeature('marketplace.buy'))
            <a href="{{ route('marketplace.index') }}" class="{{ $navItemClass(request()->routeIs('marketplace.*')) }}">
                <i class="fas fa-store w-5 opacity-80"></i>
                Marketplace
            </a>
        @endif

        <a href="{{ route('panel.jobs.index') }}" class="{{ $navItemClass(request()->routeIs('panel.jobs.*')) }}">
            <i class="fas fa-briefcase w-5 opacity-80"></i>
            Vagas
        </a>

        @if($user->canAccessFeature('vagas_create'))
            <a href="{{ route('panel.my-jobs.index') }}" class="{{ $navItemClass(request()->routeIs('panel.my-jobs.*')) }}">
                <i class="fas fa-plus-circle w-5 opacity-80"></i>
                Minhas Vagas
            </a>
        @endif

        <a href="{{ route('panel.redemptions.shop') }}"
            class="{{ $navItemClass(request()->routeIs('panel.redemptions.*')) }}">
            <i class="fas fa-gift w-5 opacity-80"></i>
            Loja de Pontos
        </a>

        @if((method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace()))
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

        @if($canAccessInstructorArea)
            <div class="pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
                <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4 px-4">
                    Instrutor
                </div>
                <a href="{{ route('panel.instructor.dashboard') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.instructor.*')) }}">
                    <i class="fas fa-chalkboard-teacher w-5 opacity-80"></i>
                    Central do instrutor
                </a>

                @if($user->hasPermission('courses.view') || $user->canAccessFeature('courses_access'))
                    <a href="{{ route('panel.admin.courses.index') }}"
                        class="{{ $navItemClass(request()->routeIs('panel.admin.courses.*')) }}">
                        <i class="fas fa-graduation-cap w-5 opacity-80"></i>
                        Meus cursos
                    </a>
                @endif

                @if($user->hasPermission('mentorships.view') || $user->canAccessFeature('mentorships_access'))
                    <a href="{{ route('panel.admin.mentorships.index') }}"
                        class="{{ $navItemClass(request()->routeIs('panel.admin.mentorships.*')) }}">
                        <i class="fas fa-user-tie w-5 opacity-80"></i>
                        Minhas mentorias
                    </a>
                @endif

                @if($user->hasPermission('events.view') || $user->canAccessFeature('events_access'))
                    <a href="{{ route('panel.admin.events.index') }}"
                        class="{{ $navItemClass(request()->routeIs('panel.admin.events.*')) }}">
                        <i class="fas fa-calendar-alt w-5 opacity-80"></i>
                        Meus eventos
                    </a>
                @endif

                @if($user->hasPermission('certificates.view') || $user->canAccessFeature('certificates_access'))
                    <a href="{{ route('panel.admin.certificates.index') }}"
                        class="{{ $navItemClass(request()->routeIs('panel.admin.certificates.*')) }}">
                        <i class="fas fa-certificate w-5 opacity-80"></i>
                        Certificados
                    </a>
                @endif

                @if($hasPartnerProfile)
                    <a href="{{ route('member.partner.index') }}"
                        class="{{ $navItemClass(request()->routeIs('member.partner.*')) }}">
                        <i class="fas fa-ticket-alt w-5 opacity-80"></i>
                        Cupons de parceiros
                    </a>
                @endif

                @if($user->canAccessFeature('vagas_create'))
                    <a href="{{ route('panel.my-jobs.index') }}"
                        class="{{ $navItemClass(request()->routeIs('panel.my-jobs.*')) }}">
                        <i class="fas fa-briefcase w-5 opacity-80"></i>
                        Minhas vagas
                    </a>
                @endif
            </div>
        @endif

        @if($user->isAdmin())
            <div class="pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
                <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4 px-4">
                    Administracao
                </div>

                <a href="{{ route('panel.admin.dashboard') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.dashboard')) }}">
                    <i class="fas fa-shield-alt w-5 opacity-80"></i>
                    Painel administrativo
                </a>

                <div class="mt-4 mb-2 px-4 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">
                    Gestao
                </div>
                <a href="{{ route('panel.admin.users.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.users.*')) }}">
                    <i class="fas fa-users-cog w-5 opacity-80"></i>
                    Usuarios
                </a>
                <a href="{{ route('panel.admin.plans.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.plans.*')) }}">
                    <i class="fas fa-gem w-5 opacity-80"></i>
                    Planos e pacotes
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
                <a href="{{ route('panel.admin.points-rules.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.points-rules.*')) }}">
                    <i class="fas fa-star w-5 opacity-80"></i>
                    Regras de pontos
                </a>
                <a href="{{ route('panel.admin.ranking.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.ranking.*')) }}">
                    <i class="fas fa-trophy w-5 opacity-80"></i>
                    Ranking
                </a>
                <a href="{{ route('panel.admin.jobs.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.jobs.*')) }}">
                    <i class="fas fa-id-card w-5 opacity-80"></i>
                    Vagas
                </a>
                <a href="{{ route('panel.admin.redemptions.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.redemptions.*')) }}">
                    <i class="fas fa-exchange-alt w-5 opacity-80"></i>
                    Resgates
                </a>
                <a href="{{ route('panel.admin.logs.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.logs.*')) }}">
                    <i class="fas fa-history w-5 opacity-80"></i>
                    Logs de atividade
                </a>

                <div class="mt-4 mb-2 px-4 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">
                    Conteudo
                </div>
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
                <a href="{{ route('panel.admin.faqs.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.faqs.*')) }}">
                    <i class="fas fa-question-circle w-5 opacity-80"></i>
                    FAQ
                </a>
                <a href="{{ route('panel.admin.testimonials.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.testimonials.*')) }}">
                    <i class="fas fa-quote-left w-5 opacity-80"></i>
                    Depoimentos
                </a>
                <a href="{{ route('panel.admin.cms.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.cms.*')) }}">
                    <i class="fas fa-pencil-ruler w-5 opacity-80"></i>
                    CMS e paginas
                </a>

                <div class="mt-4 mb-2 px-4 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">
                    Ajustes
                </div>
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
                <a href="{{ route('panel.admin.settings', ['group' => 'appearance']) }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.settings') && request('group') == 'appearance') }}">
                    <i class="fas fa-palette w-5 opacity-80"></i>
                    Aparencia
                </a>
                <a href="{{ route('panel.admin.settings', ['group' => 'images']) }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.settings') && request('group') == 'images') }}">
                    <i class="fas fa-images w-5 opacity-80"></i>
                    Imagens
                </a>
                <a href="{{ route('panel.admin.settings', ['group' => 'social']) }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.settings') && request('group') == 'social') }}">
                    <i class="fas fa-share-alt w-5 opacity-80"></i>
                    Login social
                </a>
                <a href="{{ route('panel.admin.settings', ['group' => 'seo']) }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.settings') && request('group') == 'seo') }}">
                    <i class="fas fa-search w-5 opacity-80"></i>
                    SEO
                </a>
                <a href="{{ route('panel.admin.mailtemplates.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.mailtemplates.*')) }}">
                    <i class="fas fa-at w-5 opacity-80"></i>
                    E-mails
                </a>
                <a href="{{ route('panel.admin.cron.index') }}"
                    class="{{ $navItemClass(request()->routeIs('panel.admin.cron.*')) }}">
                    <i class="fas fa-clock w-5 opacity-80"></i>
                    Cron interno
                </a>
            </div>
        @endif

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

        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        fetch('{{ route("panel.theme.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ theme: theme })
        }).then(r => r.json()).then(() => {
            window.location.reload();
        });
    }
</script>
