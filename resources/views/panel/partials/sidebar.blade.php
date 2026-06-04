@php
    $user = auth()->user();
    $plan = $user ? $user->activePlan() : null;
    $currentTheme = $user->theme_pref ?? 'light';
    $storefrontModuleInstalled = \App\Models\SellerStore::tableAvailable() && \App\Models\SellerProduct::tableAvailable();
    $canAccessInstructorArea = (method_exists($user, 'canAccessInstructorArea') && $user->canAccessInstructorArea());
    $canManageEventExhibitors = (method_exists($user, 'canManageEventExhibitors') && $user->canManageEventExhibitors());
    $hasPartnerProfile = false;

    // Interesse em Noticias (exibe modulo Revistas)
    $hasNewsInterest = \App\Models\Magazine::userHasNewsInterest($user);

    // Contagem de usuarios bloqueados
    $blockedCount = \App\Models\Connection::where('status', 'blocked')
        ->where(function ($q) use ($user) {
            $q->where('requester_id', $user->id)
              ->orWhere('requested_id', $user->id);
        })->count();

    if ($canAccessInstructorArea) {
        try {
            $hasPartnerProfile = \App\Models\Partner::where('user_id', $user->id)->exists();
        } catch (\Throwable $e) {
            $hasPartnerProfile = false;
        }
    }

    $navItemClass = function (bool $active = false) {
        $base = 'flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-300 group';
        return $active
            ? $base . ' bg-blue-600/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 shadow-inner border border-blue-600/10 dark:border-blue-500/20 active-nav-glow'
            : $base . ' text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200 hover:translate-x-1.5';
    };

    $submenuToggleClass = function (bool $active = false, bool $nested = false) {
        $base = 'flex w-full items-center justify-between gap-3 rounded-2xl px-4 py-3 transition-all duration-300 cursor-pointer group';
        $base .= $nested ? ' text-[13px] font-bold' : ' text-sm font-semibold';

        return $active
            ? $base . ' bg-blue-600/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-600/10 dark:border-blue-500/20 shadow-inner active-nav-glow'
            : $base . ' text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200 hover:translate-x-1.5';
    };

    $submenuItemClass = function (bool $active = false) {
        $base = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition-all duration-300 group relative overflow-hidden';
        return $active
            ? $base . ' bg-blue-50/80 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border-l-[3px] border-blue-600 dark:border-blue-500 shadow-sm'
            : $base . ' text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-slate-200 hover:translate-x-1.5 hover:shadow-sm';
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
            'label' => 'Meus Ingressos',
            'route' => route('panel.tickets.index'),
            'icon' => 'fas fa-ticket-alt',
            'active' => request()->routeIs('panel.tickets.*'),
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
            'label' => 'Galeria',
            'route' => route('panel.gallery.index'),
            'icon' => 'fas fa-images',
            'active' => request()->routeIs('panel.gallery.index'),
            'visible' => true,
        ],
        [
            'label' => 'Revistas',
            'route' => route('magazines.index'),
            'icon' => 'fas fa-book-open',
            'active' => request()->routeIs('magazines.*'),
            'visible' => $user->isAdmin() || $hasNewsInterest,
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
            'label' => 'Bloqueados',
            'route' => route('connection.blocked'),
            'icon' => 'fas fa-user-slash',
            'active' => request()->routeIs('connection.blocked'),
            'visible' => $user->canAccessFeature('community'),
            'badge' => $blockedCount,
        ],
        [
            'label' => 'Marketplace',
            'route' => route('marketplace.index'),
            'icon' => 'fas fa-store',
            'active' => request()->routeIs('marketplace.*'),
            'visible' => $user->canAccessFeature('marketplace.buy'),
        ],
        [
            'label' => 'Minhas compras',
            'route' => route('panel.purchases.index'),
            'icon' => 'fas fa-bag-shopping',
            'active' => request()->routeIs('panel.purchases.*'),
            'visible' => true,
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
            'label' => 'Minha Reputacao',
            'route' => route('panel.reputation'),
            'icon' => 'fas fa-medal',
            'active' => request()->routeIs('panel.reputation'),
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
            'label' => $user->isSuperAdmin() ? 'Loja da plataforma' : 'Minha loja',
            'route' => route('panel.marketplace.store.edit'),
            'icon' => 'fas fa-store',
            'active' => request()->routeIs('panel.marketplace.store.*'),
            'visible' => method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace() && $storefrontModuleInstalled,
        ],
        [
            'label' => 'Produtos proprios',
            'route' => route('panel.marketplace.products.index'),
            'icon' => 'fas fa-box-open',
            'active' => request()->routeIs('panel.marketplace.products.*'),
            'visible' => method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace() && $storefrontModuleInstalled,
        ],
        [
            'label' => 'Pedidos da loja',
            'route' => route('panel.marketplace.orders.index'),
            'icon' => 'fas fa-truck',
            'active' => request()->routeIs('panel.marketplace.orders.*'),
            'visible' => method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace() && $storefrontModuleInstalled,
        ],
        [
            'label' => 'Minhas vendas',
            'route' => route('panel.marketplace.sales'),
            'icon' => 'fas fa-receipt',
            'active' => request()->routeIs('panel.marketplace.sales'),
            'visible' => method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace(),
        ],
        [
            'label' => 'Contabilidade',
            'route' => route('panel.marketplace.accounting'),
            'icon' => 'fas fa-file-invoice-dollar',
            'active' => request()->routeIs('panel.marketplace.accounting*'),
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

    // Responsavel de Marketing (menu proprio, independente de vendedor/instrutor)
    $isMarketingManager = (int) \App\Models\Setting::get('platform_marketing_user_id', 0) === (int) $user->id;
    $marketingItems = $isMarketingManager ? [
        [
            'label' => 'Painel de Marketing',
            'route' => route('panel.marketing.index'),
            'icon' => 'fas fa-chart-line',
            'active' => request()->routeIs('panel.marketing.index'),
            'visible' => true,
        ],
    ] : [];

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
            'visible' => $canAccessInstructorArea && ($user->hasPermission('events.view') || $user->canAccessFeature('events_access') || $canManageEventExhibitors),
        ],
        [
            'label' => 'Areas para expositores',
            'route' => route('panel.admin.events.list'),
            'icon' => 'fas fa-store',
            'active' => request()->routeIs('panel.admin.events.exhibitors.*'),
            'visible' => $canAccessInstructorArea && $canManageEventExhibitors,
        ],
        [
            'label' => 'Scanner de ingressos',
            'route' => route('panel.admin.quick-scanner'),
            'icon' => 'fas fa-qrcode',
            'active' => request()->routeIs('panel.admin.quick-scanner'),
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
        ['label' => 'Contabilidade de Rateios', 'route' => route('panel.admin.splits.index'), 'icon' => 'fas fa-money-bill-wave', 'active' => request()->routeIs('panel.admin.splits.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Faturas', 'route' => route('panel.admin.invoices.index'), 'icon' => 'fas fa-file-invoice', 'active' => request()->routeIs('panel.admin.invoices.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Editor de Faturas', 'route' => route('panel.admin.invoices.editor'), 'icon' => 'fas fa-paint-brush', 'active' => request()->routeIs('panel.admin.invoices.editor*'), 'visible' => $user->isAdmin()],
        ['label' => 'Cupons', 'route' => route('panel.admin.coupons.index'), 'icon' => 'fas fa-ticket-alt', 'active' => request()->routeIs('panel.admin.coupons.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Afiliados', 'route' => route('panel.admin.referrals.index'), 'icon' => 'fas fa-bullhorn', 'active' => request()->routeIs('panel.admin.referrals.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Comunicação com compradores', 'route' => route('panel.admin.buyer-communication.index'), 'icon' => 'fas fa-envelope-open-text', 'active' => request()->routeIs('panel.admin.buyer-communication.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Lojas marketplace', 'route' => route('panel.admin.marketplace.stores.index'), 'icon' => 'fas fa-store-alt', 'active' => request()->routeIs('panel.admin.marketplace.stores.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Produtos marketplace', 'route' => route('panel.admin.marketplace.products.index'), 'icon' => 'fas fa-boxes-stacked', 'active' => request()->routeIs('panel.admin.marketplace.products.*'), 'visible' => $user->isAdmin()],
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
        ['label' => 'Revistas', 'route' => route('panel.admin.magazines.index'), 'icon' => 'fas fa-book-open', 'active' => request()->routeIs('panel.admin.magazines.*'), 'visible' => $user->isAdmin() || $user->canAccessFeature('magazines.publish') || $user->canAccessFeature('magazines_create')],
        ['label' => 'Acervo de Mídia', 'route' => route('panel.admin.events.acervo'), 'icon' => 'fas fa-photo-video', 'active' => request()->routeIs('panel.admin.events.acervo'), 'visible' => $user->isAdmin()],
        ['label' => 'Certificados', 'route' => route('panel.admin.certificates.index'), 'icon' => 'fas fa-certificate', 'active' => request()->routeIs('panel.admin.certificates.*'), 'visible' => $user->isAdmin()],
        ['label' => 'FAQ', 'route' => route('panel.admin.faqs.index'), 'icon' => 'fas fa-question-circle', 'active' => request()->routeIs('panel.admin.faqs.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Depoimentos', 'route' => route('panel.admin.testimonials.index'), 'icon' => 'fas fa-quote-left', 'active' => request()->routeIs('panel.admin.testimonials.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Páginas', 'route' => route('panel.admin.pages.index'), 'icon' => 'fas fa-pencil-ruler', 'active' => request()->routeIs('panel.admin.pages.*'), 'visible' => $user->isAdmin()],
    ], fn(array $item) => $item['visible']));

    $adminSettingsItems = array_values(array_filter([
        ['label' => 'Gerais', 'route' => route('panel.admin.settings', ['group' => 'general']), 'icon' => 'fas fa-cogs', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'general', 'visible' => $user->isAdmin()],
        ['label' => 'Pagamentos', 'route' => route('panel.admin.settings', ['group' => 'gateway']), 'icon' => 'fas fa-credit-card', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'gateway', 'visible' => $user->isAdmin()],
        ['label' => 'SMTP', 'route' => route('panel.admin.settings', ['group' => 'smtp']), 'icon' => 'fas fa-envelope', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'smtp', 'visible' => $user->isAdmin()],
        ['label' => 'Aparência', 'route' => route('panel.admin.settings', ['group' => 'appearance']), 'icon' => 'fas fa-palette', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'appearance', 'visible' => $user->isAdmin()],
        ['label' => 'Imagens', 'route' => route('panel.admin.settings', ['group' => 'images']), 'icon' => 'fas fa-images', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'images', 'visible' => $user->isAdmin()],
        ['label' => 'Login social', 'route' => route('panel.admin.settings', ['group' => 'social']), 'icon' => 'fas fa-share-alt', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'social', 'visible' => $user->isAdmin()],
        ['label' => 'SEO', 'route' => route('panel.admin.settings', ['group' => 'seo']), 'icon' => 'fas fa-search', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'seo', 'visible' => $user->isAdmin()],
        ['label' => 'Armazenamento', 'route' => route('panel.admin.settings', ['group' => 'storage']), 'icon' => 'fas fa-cloud', 'active' => request()->routeIs('panel.admin.settings') && request('group') == 'storage', 'visible' => $user->isAdmin()],
        ['label' => 'Seguranca (WAF)', 'route' => route('panel.admin.security'), 'icon' => 'fas fa-shield-alt', 'active' => request()->routeIs('panel.admin.security*'), 'visible' => $user->isSuperAdmin()],
        ['label' => 'E-mails', 'route' => route('panel.admin.mailtemplates.index'), 'icon' => 'fas fa-at', 'active' => request()->routeIs('panel.admin.mailtemplates.*'), 'visible' => $user->isAdmin()],
        ['label' => 'SumUp', 'route' => route('panel.admin.sumup.index'), 'icon' => 'fas fa-credit-card', 'active' => request()->routeIs('panel.admin.sumup.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Cron interno', 'route' => route('panel.admin.cron.index'), 'icon' => 'fas fa-clock', 'active' => request()->routeIs('panel.admin.cron.*'), 'visible' => $user->isAdmin()],
    ], fn(array $item) => $item['visible']));

    $mainOpen = $hasActiveItem($mainItems);
    $vendorOpen = $hasActiveItem($vendorItems);
    $instructorOpen = $hasActiveItem($instructorItems);
    $marketingOpen = $hasActiveItem($marketingItems);
    $adminManagementOpen = $hasActiveItem($adminManagementItems);
    $adminContentOpen = $hasActiveItem($adminContentItems);
    $adminSettingsOpen = $hasActiveItem($adminSettingsItems);
    $adminOpen = $adminDashboardItem['active'] || $adminManagementOpen || $adminContentOpen || $adminSettingsOpen;
@endphp

<style>
    .panel-sidebar summary { list-style: none; }
    .panel-sidebar summary::-webkit-details-marker { display: none; }
    .panel-sidebar details[open] > summary .submenu-chevron { transform: rotate(180deg); }
    
    /* Smooth Accordion Animation */
    .panel-sidebar details[open] > div {
        animation: details-show 0.4s ease-out forwards;
        transform-origin: top;
    }
    @keyframes details-show {
        0% { opacity: 0; transform: translateY(-10px) scaleY(0.95); }
        100% { opacity: 1; transform: translateY(0) scaleY(1); }
    }
    
    /* Active Item Left-Border Glow */
    .active-nav-glow {
        box-shadow: inset 4px 0 0 0 currentColor;
    }
    
    /* Scrollbar invisible for nested submenus if any */
    .panel-sidebar::-webkit-scrollbar { width: 0px; background: transparent; }
</style>

<div class="panel-sidebar {{ ($mobile ?? false) ? '' : 'bg-white/95 dark:bg-slate-900/95 backdrop-blur-3xl rounded-[2.5rem] shadow-[0_15px_60px_-15px_rgba(0,0,0,0.1)] dark:shadow-[0_15px_60px_-15px_rgba(0,0,0,0.4)] border border-slate-100 dark:border-slate-800/80 p-7 sticky top-24 w-full support-[backdrop-filter]:bg-white/80' }} transition-all duration-500 relative overflow-hidden group/sidebar">
    <!-- Subtle glow background effect on hover -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/5 dark:bg-blue-400/5 rounded-full blur-[80px] -mr-32 -mt-32 pointer-events-none transition-opacity duration-1000 opacity-30 group-hover/sidebar:opacity-100"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 bg-indigo-500/5 dark:bg-indigo-400/5 rounded-full blur-[60px] -ml-24 -mb-24 pointer-events-none transition-opacity duration-1000 opacity-20 group-hover/sidebar:opacity-80"></div>
    
    <div class="relative z-10 w-full h-full flex flex-col">
        <!-- User Profile Card -->
        <div class="relative mb-10 p-5 rounded-[2rem] bg-gradient-to-br from-slate-50 to-white dark:from-slate-800/40 dark:to-slate-900 border border-slate-100/80 dark:border-slate-800/80 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] transition-all duration-500 hover:scale-[1.02] hover:shadow-[0_8px_30px_-15px_rgba(0,0,0,0.1)] group/profile overflow-hidden">
            <div class="absolute inset-0 rounded-[2rem] bg-gradient-to-tr from-blue-500/5 to-purple-500/5 dark:from-blue-500/10 dark:to-purple-500/10 opacity-0 transition-opacity duration-500 group-hover/profile:opacity-100 pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col xl:flex-row items-center xl:items-start gap-4 text-center xl:text-left">
                <div class="w-16 h-16 xl:w-14 xl:h-14 rounded-[1.2rem] overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 border-[3px] border-white dark:border-slate-700 shadow-md transform transition-all duration-500 group-hover/profile:rotate-3 group-hover/profile:scale-105">
                    @if($user->profile_photo_url && !str_contains($user->profile_photo_url, 'default-user.svg'))
                        <img src="{{ $user->profile_photo_url }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-user text-slate-400 dark:text-slate-500 text-xl"></i>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="font-black text-slate-900 dark:text-white leading-tight truncate text-lg xl:text-[1.05rem]">{{ $user->name }}</div>
                    <div class="mt-2 xl:mt-1.5 inline-flex items-center gap-1.5 px-3 py-1 xl:px-2.5 xl:py-0.5 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/40 dark:to-indigo-900/40 border border-blue-100/50 dark:border-blue-800/50 shadow-inner">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse shadow-[0_0_8px_rgba(59,130,246,0.6)]"></div>
                        <span class="text-[9px] font-black text-blue-700 dark:text-blue-300 uppercase tracking-widest">{{ $plan?->name ? $plan->name : 'Sem plano' }}</span>
                    </div>
                </div>
            </div>
        </div>

    <div class="space-y-3">
        @if(count($mainItems))
            <details name="sidebar-menu" {{ $mainOpen ? 'open' : '' }}>
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
                            @if(!empty($item['badge']))
                                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </details>
        @endif

        @if(count($vendorItems))
            <details name="sidebar-menu" {{ $vendorOpen ? 'open' : '' }}>
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
                    @if(method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace() && !$storefrontModuleInstalled)
                        <div class="rounded-xl border border-amber-200/70 bg-amber-50/90 px-3 py-3 text-[13px] text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-triangle-exclamation mt-0.5 w-4 text-amber-500"></i>
                                <div>
                                    <div class="font-bold">Loja virtual pendente</div>
                                    <div class="mt-1 text-[12px] text-amber-800/80 dark:text-amber-100/70">Execute <span class="font-black">php artisan migrate --force</span> no servidor.</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </details>
        @endif

        @if(count($instructorItems))
            <details name="sidebar-menu" {{ $instructorOpen ? 'open' : '' }}>
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

        @if(count($marketingItems))
            <details name="sidebar-menu" {{ $marketingOpen ? 'open' : '' }}>
                <summary class="{{ $submenuToggleClass($marketingOpen) }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-bullhorn w-5 opacity-80 text-purple-500"></i>
                        <span>Marketing da Plataforma</span>
                        <span class="ml-auto text-[9px] font-black px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 uppercase tracking-widest">Exclusivo</span>
                    </div>
                    <i class="fas fa-chevron-down submenu-chevron text-[10px] transition-transform"></i>
                </summary>
                <div class="mt-2 ml-4 pl-3 border-l border-purple-100 dark:border-purple-900/50 space-y-1">
                    @foreach($marketingItems as $item)
                        <a href="{{ $item['route'] }}" class="{{ $submenuItemClass($item['active']) }}">
                            <i class="{{ $item['icon'] }} w-4 opacity-80"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </details>
        @endif

        @if($user->isAdmin())
            <details name="sidebar-menu" {{ $adminOpen ? 'open' : '' }}>
                <summary class="{{ $submenuToggleClass($adminOpen) }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-shield-alt w-5 opacity-80"></i>
                        <span>Administração</span>
                    </div>
                    <i class="fas fa-chevron-down submenu-chevron text-[10px] transition-transform"></i>
                </summary>
                <div class="mt-2 ml-4 pl-3 border-l border-slate-100 dark:border-slate-800 space-y-2">
                    <a href="{{ $adminDashboardItem['route'] }}"
                        class="{{ $submenuItemClass($adminDashboardItem['active']) }}">
                        <i class="{{ $adminDashboardItem['icon'] }} w-4 opacity-80"></i>
                        <span>{{ $adminDashboardItem['label'] }}</span>
                    </a>

                    <details name="admin-submenu" {{ $adminManagementOpen ? 'open' : '' }}>
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

                    <details name="admin-submenu" {{ $adminContentOpen ? 'open' : '' }}>
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

                    <details name="admin-submenu" {{ $adminSettingsOpen ? 'open' : '' }}>
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

        <div class="mt-8 px-4">
            <button onclick="toggleTheme(this)"
                class="w-full flex items-center justify-between gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 group">
                <div class="flex items-center gap-3">
                    <i
                        class="fas {{ $currentTheme === 'dark' ? 'fa-sun' : 'fa-moon' }} w-5 opacity-80 group-hover:rotate-12 transition-transform"></i>
                    <span>Modo {{ $currentTheme === 'dark' ? 'Claro' : 'Escuro' }}</span>
                </div>
                <div class="w-10 h-5 bg-slate-200 dark:bg-slate-700 rounded-full relative transition-colors">
                    <div
                        class="absolute top-1 {{ $currentTheme === 'dark' ? 'right-1' : 'left-1' }} w-3 h-3 bg-white dark:bg-blue-400 rounded-full shadow-sm transition-all duration-300">
                    </div>
                </div>
            </button>
        </div>
    </div>
</div>

<script>
    function toggleTheme(btn) {
        const isDark = document.documentElement.classList.contains('dark');
        const newTheme = isDark ? 'light' : 'dark';

        if (newTheme === 'dark') {
            document.documentElement.classList.add('dark');
            if (btn) {
                const icon = btn.querySelector('i.fas');
                if (icon) { icon.classList.remove('fa-moon'); icon.classList.add('fa-sun'); }
                const span = btn.querySelector('span');
                if (span) span.innerText = 'Modo Claro';
                const knob = btn.querySelector('.absolute.top-1');
                if (knob) { knob.classList.remove('left-1'); knob.classList.add('right-1'); }
            }
        } else {
            document.documentElement.classList.remove('dark');
            if (btn) {
                const icon = btn.querySelector('i.fas');
                if (icon) { icon.classList.remove('fa-sun'); icon.classList.add('fa-moon'); }
                const span = btn.querySelector('span');
                if (span) span.innerText = 'Modo Escuro';
                const knob = btn.querySelector('.absolute.top-1');
                if (knob) { knob.classList.remove('right-1'); knob.classList.add('left-1'); }
            }
        }

        fetch('{{ route("theme.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ theme: newTheme })
        }).catch(() => {});
    }
</script>
