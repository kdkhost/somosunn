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

    $submenuToggleClass = function (bool $active = false, bool $nested = false) {
        $base = 'flex w-full items-center justify-between gap-3 rounded-2xl px-4 py-3 transition-all duration-200 cursor-pointer';
        $base .= $nested ? ' text-[13px] font-bold' : ' text-sm font-semibold';

        return $active
            ? $base . ' bg-blue-600/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-600/5 dark:border-blue-500/10 shadow-sm'
            : $base . ' text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200';
    };

    $submenuItemClass = function (bool $active = false) {
        $base = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-200';
        return $active
            ? $base . ' bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-300'
            : $base . ' text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-200';
    };

    $hasActiveItem = fn(array $items) => collect($items)->contains(fn(array $item) => $item['active']);

    $mainItems = array_values(array_filter([
        [
            'label' => 'Visão geral',
            'route' => route('panel.dashboard'),
            'icon' => 'fas fa-th-large',
            'active' => request()->routeIs('panel.dashboard'),
            'visible' => true,
        ],
        [
            'label' => 'Meu perfil',
            'route' => route('panel.profile.edit'),
            'icon' => 'fas fa-user-circle',
            'active' => request()->routeIs('panel.profile.*'),
            'visible' => true,
        ],
        [
            'label' => 'Minha Lista',
            'route' => route('panel.wishlist.index'),
            'icon' => 'fas fa-heart',
            'active' => request()->routeIs('panel.wishlist.*'),
            'visible' => true,
        ],
        [
            'label' => 'Portal',
            'route' => route('portal'),
            'icon' => 'fas fa-home',
            'active' => request()->is('portal'),
            'visible' => true,
        ],
        [
            'label' => 'Comunidade',
            'route' => route('social.feed'),
            'icon' => 'fas fa-users',
            'active' => request()->routeIs('social.feed'),
            'visible' => $user->canAccessFeature('community'),
        ],
        [
            'label' => 'Chat',
            'route' => route('chat.index'),
            'icon' => 'fas fa-comments',
            'active' => request()->routeIs('chat.*'),
            'visible' => $user->canAccessFeature('chat'),
        ],
        [
            'label' => 'Marketplace',
            'route' => route('marketplace.index'),
            'icon' => 'fas fa-store',
            'active' => request()->routeIs('marketplace.*'),
            'visible' => $user->canAccessFeature('marketplace.buy'),
        ],
        [
            'label' => 'Vagas',
            'route' => route('panel.jobs.index'),
            'icon' => 'fas fa-briefcase',
            'active' => request()->routeIs('panel.jobs.*'),
            'visible' => true,
        ],
        [
            'label' => 'Minhas vagas',
            'route' => route('panel.my-jobs.index'),
            'icon' => 'fas fa-plus-circle',
            'active' => request()->routeIs('panel.my-jobs.*'),
            'visible' => $user->canAccessFeature('vagas_create'),
        ],
        [
            'label' => 'Meus Pontos',
            'route' => route('panel.points.index'),
            'icon' => 'fas fa-coins',
            'active' => request()->routeIs('panel.points.*'),
            'visible' => true,
        ],
        [
            'label' => 'Indicações',
            'route' => route('panel.referral.index'),
            'icon' => 'fas fa-user-plus',
            'active' => request()->routeIs('panel.referral.*'),
            'visible' => true,
        ],
        [
            'label' => 'Loja de Pontos',
            'route' => route('panel.redemptions.shop'),
            'icon' => 'fas fa-gift',
            'active' => request()->routeIs('panel.redemptions.*'),
            'visible' => true,
        ],
    ], fn(array $item) => $item['visible']));

    $vendorItems = array_values(array_filter([
        [
            'label' => 'Painel de vendas',
            'route' => route('panel.marketplace.index'),
            'icon' => 'fas fa-chart-line',
            'active' => request()->routeIs('panel.marketplace.index'),
            'visible' => method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace(),
        ],
        [
            'label' => 'Pagamentos',
            'route' => route('panel.marketplace.payments'),
            'icon' => 'fas fa-credit-card',
            'active' => request()->routeIs('panel.marketplace.payments'),
            'visible' => method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace(),
        ],
        [
            'label' => 'Minhas vendas',
            'route' => route('panel.marketplace.sales'),
            'icon' => 'fas fa-receipt',
            'active' => request()->routeIs('panel.marketplace.sales'),
            'visible' => method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace(),
        ],
        [
            'label' => 'Meus recebimentos',
            'route' => route('panel.splits.index'),
            'icon' => 'fas fa-money-bill-wave',
            'active' => request()->routeIs('panel.splits.index'),
            'visible' => method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace(),
        ],
    ], fn(array $item) => $item['visible']));

    $instructorItems = array_values(array_filter([
        [
            'label' => 'Central do instrutor',
            'route' => route('panel.instructor.dashboard'),
            'icon' => 'fas fa-chalkboard-teacher',
            'active' => request()->routeIs('panel.instructor.*'),
            'visible' => $canAccessInstructorArea,
        ],
        [
            'label' => 'Meus cursos',
            'route' => route('panel.admin.courses.index'),
            'icon' => 'fas fa-graduation-cap',
            'active' => request()->routeIs('panel.admin.courses.*'),
            'visible' => $canAccessInstructorArea && ($user->hasPermission('courses.view') || $user->canAccessFeature('courses_access')),
        ],
        [
            'label' => 'Minhas mentorias',
            'route' => route('panel.admin.mentorships.index'),
            'icon' => 'fas fa-user-tie',
            'active' => request()->routeIs('panel.admin.mentorships.*'),
            'visible' => $canAccessInstructorArea && ($user->hasPermission('mentorships.view') || $user->canAccessFeature('mentorships_access')),
        ],
        [
            'label' => 'Meus eventos',
            'route' => route('panel.admin.events.index'),
            'icon' => 'fas fa-calendar-alt',
            'active' => request()->routeIs('panel.admin.events.*'),
            'visible' => $canAccessInstructorArea && ($user->hasPermission('events.view') || $user->canAccessFeature('events_access')),
        ],
        [
            'label' => 'Certificados',
            'route' => route('panel.admin.certificates.index'),
            'icon' => 'fas fa-certificate',
            'active' => request()->routeIs('panel.admin.certificates.*'),
            'visible' => $canAccessInstructorArea && ($user->hasPermission('certificates.view') || $user->canAccessFeature('certificates_access')),
        ],
        [
            'label' => 'Cupons de parceiros',
            'route' => route('member.partner.index'),
            'icon' => 'fas fa-ticket-alt',
            'active' => request()->routeIs('member.partner.*'),
            'visible' => $canAccessInstructorArea && $hasPartnerProfile,
        ],
        [
            'label' => 'Minhas vagas',
            'route' => route('panel.my-jobs.index'),
            'icon' => 'fas fa-briefcase',
            'active' => request()->routeIs('panel.my-jobs.*'),
            'visible' => $canAccessInstructorArea && $user->canAccessFeature('vagas_create'),
        ],
    ], fn(array $item) => $item['visible']));

    $adminDashboardItem = [
        'label' => 'Painel administrativo',
        'route' => route('panel.admin.dashboard'),
        'icon' => 'fas fa-shield-alt',
        'active' => request()->routeIs('panel.admin.dashboard'),
        'visible' => $user->isAdmin(),
    ];

    $adminManagementItems = array_values(array_filter([
        ['label' => 'Usuários', 'route' => route('panel.admin.users.index'), 'icon' => 'fas fa-users-cog', 'active' => request()->routeIs('panel.admin.users.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Planos e pacotes', 'route' => route('panel.admin.plans.index'), 'icon' => 'fas fa-gem', 'active' => request()->routeIs('panel.admin.plans.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Vendas', 'route' => route('panel.admin.orders.index'), 'icon' => 'fas fa-shopping-cart', 'active' => request()->routeIs('panel.admin.orders.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Faturas', 'route' => route('panel.admin.invoices.index'), 'icon' => 'fas fa-file-invoice', 'active' => request()->routeIs('panel.admin.invoices.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Cupons', 'route' => route('panel.admin.coupons.index'), 'icon' => 'fas fa-ticket-alt', 'active' => request()->routeIs('panel.admin.coupons.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Regras de pontos', 'route' => route('panel.admin.points-rules.index'), 'icon' => 'fas fa-star', 'active' => request()->routeIs('panel.admin.points-rules.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Ranking', 'route' => route('panel.admin.ranking.index'), 'icon' => 'fas fa-trophy', 'active' => request()->routeIs('panel.admin.ranking.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Vagas', 'route' => route('panel.admin.jobs.index'), 'icon' => 'fas fa-id-card', 'active' => request()->routeIs('panel.admin.jobs.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Resgates', 'route' => route('panel.admin.redemptions.index'), 'icon' => 'fas fa-exchange-alt', 'active' => request()->routeIs('panel.admin.redemptions.*'), 'visible' => $user->isAdmin() || (method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace())],
        ['label' => 'Logs de atividade', 'route' => route('panel.admin.logs.index'), 'icon' => 'fas fa-history', 'active' => request()->routeIs('panel.admin.logs.*'), 'visible' => $user->isAdmin()],
    ], fn(array $item) => $item['visible']));

    $adminContentItems = array_values(array_filter([
        ['label' => 'Cursos', 'route' => route('panel.admin.courses.index'), 'icon' => 'fas fa-graduation-cap', 'active' => request()->routeIs('panel.admin.courses.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Mentorias', 'route' => route('panel.admin.mentorships.index'), 'icon' => 'fas fa-chalkboard-teacher', 'active' => request()->routeIs('panel.admin.mentorships.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Eventos', 'route' => route('panel.admin.events.index'), 'icon' => 'fas fa-calendar-alt', 'active' => request()->routeIs('panel.admin.events.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Certificados', 'route' => route('panel.admin.certificates.index'), 'icon' => 'fas fa-certificate', 'active' => request()->routeIs('panel.admin.certificates.*'), 'visible' => $user->isAdmin()],
        ['label' => 'FAQ', 'route' => route('panel.admin.faqs.index'), 'icon' => 'fas fa-question-circle', 'active' => request()->routeIs('panel.admin.faqs.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Depoimentos', 'route' => route('panel.admin.testimonials.index'), 'icon' => 'fas fa-quote-left', 'active' => request()->routeIs('panel.admin.testimonials.*'), 'visible' => $user->isAdmin()],
        ['label' => 'CMS e páginas', 'route' => route('panel.admin.cms.index'), 'icon' => 'fas fa-pencil-ruler', 'active' => request()->routeIs('panel.admin.cms.*'), 'visible' => $user->isAdmin()],
    ], fn(array $item) => $item['visible']));

    $adminSettingsItems = array_values(array_filter([
        ['label' => 'Gerais', 'route' => route('panel.admin.settings', ['group' => 'general']), 'icon' => 'fas fa-cogs', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'general', 'visible' => $user->isAdmin()],
        ['label' => 'Pagamentos', 'route' => route('panel.admin.settings', ['group' => 'gateway']), 'icon' => 'fas fa-credit-card', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'gateway', 'visible' => $user->isAdmin()],
        ['label' => 'SMTP', 'route' => route('panel.admin.settings', ['group' => 'smtp']), 'icon' => 'fas fa-envelope', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'smtp', 'visible' => $user->isAdmin()],
        ['label' => 'Aparência', 'route' => route('panel.admin.settings', ['group' => 'appearance']), 'icon' => 'fas fa-palette', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'appearance', 'visible' => $user->isAdmin()],
        ['label' => 'Imagens', 'route' => route('panel.admin.settings', ['group' => 'images']), 'icon' => 'fas fa-images', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'images', 'visible' => $user->isAdmin()],
        ['label' => 'Login social', 'route' => route('panel.admin.settings', ['group' => 'social']), 'icon' => 'fas fa-share-alt', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'social', 'visible' => $user->isAdmin()],
        ['label' => 'SEO', 'route' => route('panel.admin.settings', ['group' => 'seo']), 'icon' => 'fas fa-search', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'seo', 'visible' => $user->isAdmin()],
        ['label' => 'E-mails', 'route' => route('panel.admin.mailtemplates.index'), 'icon' => 'fas fa-at', 'active' => request()->routeIs('panel.admin.mailtemplates.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Cron interno', 'route' => route('panel.admin.cron.index'), 'icon' => 'fas fa-clock', 'active' => request()->routeIs('panel.admin.cron.*'), 'visible' => $user->isAdmin()],
    ], fn(array $item) => $item['visible']));

    $mainOpen = $hasActiveItem($mainItems);
    $vendorOpen = $hasActiveItem($vendorItems);
    $instructorOpen = $hasActiveItem($instructorItems);
    $adminManagementOpen = $hasActiveItem($adminManagementItems);
    $adminContentOpen = $hasActiveItem($adminContentItems);
    $adminSettingsOpen = $hasActiveItem($adminSettingsItems);
    $adminOpen = $adminDashboardItem['active'] || $adminManagementOpen || $adminContentOpen || $adminSettingsOpen;
@endphp

<style>
    .panel-sidebar summary {
        list-style: none;
    }

    .panel-sidebar summary::-webkit-details-marker {
        display: none;
    }

    .panel-sidebar details[open] > summary .submenu-chevron {
        transform: rotate(180deg);
    }
</style>

<div
    class="panel-sidebar {{ ($mobile ?? false) ? '' : 'bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 p-6 sticky top-24 w-full' }} transition-colors duration-300">
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

    <div class="space-y-3">
        @if(count($mainItems))
            <details {{ $mainOpen ? 'open' : '' }}>
                <summary class="{{ $submenuToggleClass($mainOpen) }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-compass w-5 opacity-80"></i>
                        <span>Menu principal</span>
                    </div>
                    <i class="fas fa-chevron-down submenu-chevron text-[10px] transition-transform"></i>
                </summary>
                <div class="mt-2 ml-4 pl-3 border-l border-slate-100 dark:border-slate-800 space-y-1">
                    @foreach($mainItems as $item)
                        <a href="{{ $item['route'] }}" class="{{ $submenuItemClass($item['active']) }}">
                            <i class="{{ $item['icon'] }} w-4 opacity-80"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </details>
        @endif

        @if(count($vendorItems))
            <details {{ $vendorOpen ? 'open' : '' }}>
                <summary class="{{ $submenuToggleClass($vendorOpen) }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-store w-5 opacity-80"></i>
                        <span>Vendedor</span>
                    </div>
                    <i class="fas fa-chevron-down submenu-chevron text-[10px] transition-transform"></i>
                </summary>
                <div class="mt-2 ml-4 pl-3 border-l border-slate-100 dark:border-slate-800 space-y-1">
                    @foreach($vendorItems as $item)
                        <a href="{{ $item['route'] }}" class="{{ $submenuItemClass($item['active']) }}">
                            <i class="{{ $item['icon'] }} w-4 opacity-80"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </details>
        @endif

        @if(count($instructorItems))
            <details {{ $instructorOpen ? 'open' : '' }}>
                <summary class="{{ $submenuToggleClass($instructorOpen) }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-chalkboard-teacher w-5 opacity-80"></i>
                        <span>Instrutor</span>
                    </div>
                    <i class="fas fa-chevron-down submenu-chevron text-[10px] transition-transform"></i>
                </summary>
                <div class="mt-2 ml-4 pl-3 border-l border-slate-100 dark:border-slate-800 space-y-1">
                    @foreach($instructorItems as $item)
                        <a href="{{ $item['route'] }}" class="{{ $submenuItemClass($item['active']) }}">
                            <i class="{{ $item['icon'] }} w-4 opacity-80"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </details>
        @endif

        @if($user->isAdmin())
            <details {{ $adminOpen ? 'open' : '' }}>
                <summary class="{{ $submenuToggleClass($adminOpen) }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-shield-alt w-5 opacity-80"></i>
                        <span>Administração</span>
                    </div>
                    <i class="fas fa-chevron-down submenu-chevron text-[10px] transition-transform"></i>
                </summary>
                <div class="mt-2 ml-4 pl-3 border-l border-slate-100 dark:border-slate-800 space-y-2">
                    <a href="{{ $adminDashboardItem['route'] }}" class="{{ $submenuItemClass($adminDashboardItem['active']) }}">
                        <i class="{{ $adminDashboardItem['icon'] }} w-4 opacity-80"></i>
                        <span>{{ $adminDashboardItem['label'] }}</span>
                    </a>

                    <details {{ $adminManagementOpen ? 'open' : '' }}>
                        <summary class="{{ $submenuToggleClass($adminManagementOpen, true) }}">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-sitemap w-4 opacity-80"></i>
                                <span>Gestão</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-chevron text-[10px] transition-transform"></i>
                        </summary>
                        <div class="mt-2 ml-4 pl-3 border-l border-slate-100 dark:border-slate-800 space-y-1">
                            @foreach($adminManagementItems as $item)
                                <a href="{{ $item['route'] }}" class="{{ $submenuItemClass($item['active']) }}">
                                    <i class="{{ $item['icon'] }} w-4 opacity-80"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </details>

                    <details {{ $adminContentOpen ? 'open' : '' }}>
                        <summary class="{{ $submenuToggleClass($adminContentOpen, true) }}">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-folder-open w-4 opacity-80"></i>
                                <span>Conteúdo</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-chevron text-[10px] transition-transform"></i>
                        </summary>
                        <div class="mt-2 ml-4 pl-3 border-l border-slate-100 dark:border-slate-800 space-y-1">
                            @foreach($adminContentItems as $item)
                                <a href="{{ $item['route'] }}" class="{{ $submenuItemClass($item['active']) }}">
                                    <i class="{{ $item['icon'] }} w-4 opacity-80"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </details>

                    <details {{ $adminSettingsOpen ? 'open' : '' }}>
                        <summary class="{{ $submenuToggleClass($adminSettingsOpen, true) }}">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-sliders-h w-4 opacity-80"></i>
                                <span>Ajustes</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-chevron text-[10px] transition-transform"></i>
                        </summary>
                        <div class="mt-2 ml-4 pl-3 border-l border-slate-100 dark:border-slate-800 space-y-1">
                            @foreach($adminSettingsItems as $item)
                                <a href="{{ $item['route'] }}" class="{{ $submenuItemClass($item['active']) }}">
                                    <i class="{{ $item['icon'] }} w-4 opacity-80"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </details>
                </div>
            </details>
        @endif

        <div class="pt-3 mt-3 border-t border-slate-100 dark:border-slate-800">
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
