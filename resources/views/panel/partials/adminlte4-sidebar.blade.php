@php
    $user = auth()->user();
    $plan = $user ? $user->activePlan() : null;
    $brandLogo = \App\Models\Setting::getUrl('logo_admin') ?: \App\Models\Setting::getUrl('logo_image') ?: asset('img/logo.svg');
    $brandFavicon = \App\Models\Setting::getUrl('favicon_image') ?: asset('img/logo.svg');

    $hasActiveItem = fn(array $items) => collect($items)->contains(fn(array $item) => !empty($item['active']));

    $adminManagementItems = array_values(array_filter([
        ['label' => 'Usuários', 'route' => route('panel.admin.users.index'), 'icon' => 'fas fa-users-cog', 'active' => request()->routeIs('panel.admin.users.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Planos e pacotes', 'route' => route('panel.admin.plans.index'), 'icon' => 'fas fa-gem', 'active' => request()->routeIs('panel.admin.plans.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Vendas', 'route' => route('panel.admin.orders.index'), 'icon' => 'fas fa-shopping-cart', 'active' => request()->routeIs('panel.admin.orders.index') || request()->routeIs('panel.admin.orders.show'), 'visible' => $user->isAdmin()],
        ['label' => 'Relatório de vendas', 'route' => route('panel.admin.orders.sales-report'), 'icon' => 'fas fa-chart-bar', 'active' => request()->routeIs('panel.admin.orders.sales-report'), 'visible' => $user->isAdmin()],
        ['label' => 'Contabilidade de Rateios', 'route' => route('panel.admin.splits.index'), 'icon' => 'fas fa-money-bill-wave', 'active' => request()->routeIs('panel.admin.splits.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Faturas', 'route' => route('panel.admin.invoices.index'), 'icon' => 'fas fa-file-invoice', 'active' => request()->routeIs('panel.admin.invoices.*') && !request()->routeIs('panel.admin.invoices.editor*'), 'visible' => $user->isAdmin()],
        ['label' => 'Editor de Faturas', 'route' => route('panel.admin.invoices.editor'), 'icon' => 'fas fa-paint-brush', 'active' => request()->routeIs('panel.admin.invoices.editor*'), 'visible' => $user->isAdmin()],
        ['label' => 'Cupons', 'route' => route('panel.admin.coupons.index'), 'icon' => 'fas fa-ticket-alt', 'active' => request()->routeIs('panel.admin.coupons.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Afiliados', 'route' => route('panel.admin.referrals.index'), 'icon' => 'fas fa-bullhorn', 'active' => request()->routeIs('panel.admin.referrals.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Comunicação compradores', 'route' => route('panel.admin.buyer-communication.index'), 'icon' => 'fas fa-envelope-open-text', 'active' => request()->routeIs('panel.admin.buyer-communication.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Empresas', 'route' => route('panel.admin.companies.index'), 'icon' => 'fas fa-building', 'active' => request()->routeIs('panel.admin.companies.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Patrocinadores', 'route' => route('panel.admin.sponsors.index'), 'icon' => 'fas fa-handshake', 'active' => request()->routeIs('panel.admin.sponsors.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Planos patrocínio', 'route' => route('panel.admin.sponsor-plans.index'), 'icon' => 'fas fa-medal', 'active' => request()->routeIs('panel.admin.sponsor-plans.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Banners patrocinados', 'route' => route('panel.admin.sponsor-banners.index'), 'icon' => 'fas fa-image', 'active' => request()->routeIs('panel.admin.sponsor-banners.*'), 'visible' => $user->isAdmin()],
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
        ['label' => 'Eventos', 'route' => route('panel.admin.events.index'), 'icon' => 'fas fa-calendar-alt', 'active' => request()->routeIs('panel.admin.events.*') && !request()->routeIs('panel.admin.events.acervo'), 'visible' => $user->isAdmin()],
        ['label' => 'Revistas', 'route' => route('panel.admin.magazines.index'), 'icon' => 'fas fa-book-open', 'active' => request()->routeIs('panel.admin.magazines.*'), 'visible' => $user->isAdmin() || $user->canAccessFeature('magazines.publish') || $user->canAccessFeature('magazines_create')],
        ['label' => 'Acervo de Mídia', 'route' => route('panel.admin.events.acervo'), 'icon' => 'fas fa-photo-video', 'active' => request()->routeIs('panel.admin.events.acervo'), 'visible' => $user->isAdmin()],
        ['label' => 'Certificados', 'route' => route('panel.admin.certificates.index'), 'icon' => 'fas fa-certificate', 'active' => request()->routeIs('panel.admin.certificates.*'), 'visible' => $user->isAdmin()],
        ['label' => 'FAQ', 'route' => route('panel.admin.faqs.index'), 'icon' => 'fas fa-question-circle', 'active' => request()->routeIs('panel.admin.faqs.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Depoimentos', 'route' => route('panel.admin.testimonials.index'), 'icon' => 'fas fa-quote-left', 'active' => request()->routeIs('panel.admin.testimonials.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Páginas', 'route' => route('panel.admin.pages.index'), 'icon' => 'fas fa-pencil-ruler', 'active' => request()->routeIs('panel.admin.pages.*'), 'visible' => $user->isAdmin()],
    ], fn(array $item) => $item['visible']));

    $adminSettingsItems = array_values(array_filter([
        ['label' => 'Gerais', 'route' => route('panel.admin.settings', ['group' => 'general']), 'icon' => 'fas fa-cogs', 'active' => request()->routeIs('panel.admin.settings') && request('group', 'general') === 'general', 'visible' => $user->isAdmin()],
        ['label' => 'Pagamentos', 'route' => route('panel.admin.settings', ['group' => 'gateway']), 'icon' => 'fas fa-credit-card', 'active' => request()->routeIs('panel.admin.settings') && request('group') === 'gateway', 'visible' => $user->isAdmin()],
        ['label' => 'SMTP', 'route' => route('panel.admin.settings', ['group' => 'smtp']), 'icon' => 'fas fa-envelope', 'active' => request()->routeIs('panel.admin.settings') && request('group') === 'smtp', 'visible' => $user->isAdmin()],
        ['label' => 'Aparência', 'route' => route('panel.admin.settings', ['group' => 'appearance']), 'icon' => 'fas fa-palette', 'active' => request()->routeIs('panel.admin.settings') && request('group') === 'appearance', 'visible' => $user->isAdmin()],
        ['label' => 'Imagens', 'route' => route('panel.admin.settings', ['group' => 'images']), 'icon' => 'fas fa-images', 'active' => request()->routeIs('panel.admin.settings') && request('group') === 'images', 'visible' => $user->isAdmin()],
        ['label' => 'Login social', 'route' => route('panel.admin.settings', ['group' => 'social']), 'icon' => 'fas fa-share-alt', 'active' => request()->routeIs('panel.admin.settings') && request('group') === 'social', 'visible' => $user->isAdmin()],
        ['label' => 'SEO', 'route' => route('panel.admin.settings', ['group' => 'seo']), 'icon' => 'fas fa-search', 'active' => request()->routeIs('panel.admin.settings') && request('group') === 'seo', 'visible' => $user->isAdmin()],
        ['label' => 'Armazenamento', 'route' => route('panel.admin.settings', ['group' => 'storage']), 'icon' => 'fas fa-cloud', 'active' => request()->routeIs('panel.admin.settings') && request('group') === 'storage', 'visible' => $user->isAdmin()],
        ['label' => 'Segurança (WAF)', 'route' => route('panel.admin.security'), 'icon' => 'fas fa-shield-alt', 'active' => request()->routeIs('panel.admin.security*'), 'visible' => $user->isSuperAdmin()],
        ['label' => 'E-mails', 'route' => route('panel.admin.mailtemplates.index'), 'icon' => 'fas fa-at', 'active' => request()->routeIs('panel.admin.mailtemplates.*'), 'visible' => $user->isAdmin()],
        ['label' => 'SumUp', 'route' => route('panel.admin.sumup.index'), 'icon' => 'fas fa-credit-card', 'active' => request()->routeIs('panel.admin.sumup.*'), 'visible' => $user->isAdmin()],
        ['label' => 'Cron interno', 'route' => route('panel.admin.cron.index'), 'icon' => 'fas fa-clock', 'active' => request()->routeIs('panel.admin.cron.*'), 'visible' => $user->isAdmin()],
    ], fn(array $item) => $item['visible']));

    $managementOpen = $hasActiveItem($adminManagementItems);
    $contentOpen = $hasActiveItem($adminContentItems);
    $settingsOpen = $hasActiveItem($adminSettingsItems);
@endphp

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    {{-- Sidebar Brand --}}
    <div class="sidebar-brand">
        <a href="{{ route('panel.admin.dashboard') }}" class="brand-link">
            <img src="{{ $brandLogo }}" alt="UNN" class="brand-logo-img"
                style="max-height: 40px; width: auto; max-width: 85%; object-fit: contain;">
            <img src="{{ $brandFavicon }}" alt="UNN" class="brand-favicon-img"
                style="max-height: 34px; width: auto; max-width: 85%; object-fit: contain;">
        </a>
    </div>

    {{-- Sidebar Wrapper --}}
    <div class="sidebar-wrapper">
        {{-- User Panel --}}
        <div class="sidebar-user-panel">
            <img src="{{ $user->photo ? asset($user->photo) : asset('img/user.png') }}"
                class="rounded-circle object-fit-cover flex-shrink-0 border border-primary-subtle" alt="Avatar"
                style="width: 38px; height: 38px;" loading="lazy" decoding="async">
            <div class="user-info overflow-hidden">
                <a href="{{ route('panel.profile.edit') }}" class="d-block text-white text-decoration-none fw-semibold text-truncate"
                    style="font-size: 0.9rem;">{{ $user->name }}</a>
                <span class="text-secondary small d-block text-truncate">
                    <i class="fas fa-crown text-warning me-1"></i>{{ $plan ? $plan->name : 'Administrador' }}
                </span>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="true" id="panel-admin-sidebar-tree">
                <li class="nav-header">CENTRAL ADMIN</li>

                <li class="nav-item">
                    <a href="{{ route('panel.admin.dashboard') }}" class="nav-link {{ request()->routeIs('panel.admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Painel Administrativo</p>
                    </a>
                </li>

                @if(\Illuminate\Support\Facades\Route::has('panel.admin.quick-scanner'))
                    <li class="nav-item">
                        <a href="{{ route('panel.admin.quick-scanner') }}" class="nav-link {{ request()->routeIs('panel.admin.quick-scanner*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-qrcode"></i>
                            <p>Escanear Ingressos</p>
                        </a>
                    </li>
                @endif

                <li class="nav-item">
                    <a href="{{ route('panel.dashboard') }}" class="nav-link">
                        <i class="nav-icon fas fa-user-circle"></i>
                        <p>Área do Membro</p>
                    </a>
                </li>

                @if(\Illuminate\Support\Facades\Route::has('admin.dashboard') && $user->isAdmin())
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">
                            <i class="nav-icon fas fa-shield-alt"></i>
                            <p>Painel Global (/admin)</p>
                        </a>
                    </li>
                @endif

                @if(count($adminManagementItems))
                    <li class="nav-header">OPERAÇÃO &amp; GESTÃO</li>
                    <li class="nav-item {{ $managementOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $managementOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-briefcase"></i>
                            <p>
                                Gestão &amp; Financeiro
                                <i class="nav-arrow fas fa-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach($adminManagementItems as $item)
                                <li class="nav-item">
                                    <a href="{{ $item['route'] }}" class="nav-link {{ $item['active'] ? 'active' : '' }}">
                                        <i class="nav-icon {{ $item['icon'] }}"></i>
                                        <p>{{ $item['label'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif

                @if(count($adminContentItems))
                    <li class="nav-item {{ $contentOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $contentOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-layer-group"></i>
                            <p>
                                Conteúdo &amp; Mídia
                                <i class="nav-arrow fas fa-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach($adminContentItems as $item)
                                <li class="nav-item">
                                    <a href="{{ $item['route'] }}" class="nav-link {{ $item['active'] ? 'active' : '' }}">
                                        <i class="nav-icon {{ $item['icon'] }}"></i>
                                        <p>{{ $item['label'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif

                @if(count($adminSettingsItems))
                    <li class="nav-header">PLATAFORMA</li>
                    <li class="nav-item {{ $settingsOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $settingsOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-sliders-h"></i>
                            <p>
                                Configurações
                                <i class="nav-arrow fas fa-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach($adminSettingsItems as $item)
                                <li class="nav-item">
                                    <a href="{{ $item['route'] }}" class="nav-link {{ $item['active'] ? 'active' : '' }}">
                                        <i class="nav-icon {{ $item['icon'] }}"></i>
                                        <p>{{ $item['label'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
