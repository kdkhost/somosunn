@php
    $is = fn($patterns) => request()->routeIs($patterns) ? 'active' : '';
    $open = fn($patterns) => request()->routeIs($patterns) ? 'menu-open' : '';
@endphp
@php
    $brandLogo = \App\Models\Setting::getUrl('logo_admin') ?: \App\Models\Setting::getUrl('logo_image') ?: asset('img/logo.svg');
    $brandFavicon = \App\Models\Setting::getUrl('favicon_image') ?: asset('img/logo.svg');
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('admin.dashboard') }}" class="brand-link d-flex align-items-center justify-content-center p-0"
        style="height:60px; overflow:hidden;">
        <img src="{{ $brandLogo }}" alt="UNN" class="brand-logo-img"
            style="max-height: 44px; width: auto; max-width: 80%; object-fit: contain;">
        <img src="{{ $brandFavicon }}" alt="UNN" class="brand-favicon-img"
            style="max-height: 44px; width: auto; max-width: 80%; object-fit: contain;">
    </a>
    <style>
        /* Estado Padrão (Aberto): Logo Visível, Favicon Oculto */
        .brand-link .brand-logo-img {
            display: block;
        }

        .brand-link .brand-favicon-img {
            display: none;
        }

        /* Estado Fechado (.sidebar-collapse no body): Logo Oculto, Favicon Visível */
        body.sidebar-collapse .brand-link .brand-logo-img {
            display: none !important;
        }

        body.sidebar-collapse .brand-link .brand-favicon-img {
            display: block !important;
        }

        /* Estado Hover no Mini (Passar mouse quando fechado): Logo Volta, Favicon Some */
        body.sidebar-mini.sidebar-collapse .main-sidebar:hover .brand-link .brand-logo-img {
            display: block !important;
        }

        body.sidebar-mini.sidebar-collapse .main-sidebar:hover .brand-link .brand-favicon-img {
            display: none !important;
        }
    </style>
    <div class="sidebar" style="padding-bottom: 1.5rem;">
        {{-- User Panel with Plan --}}
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image mr-2">
                <img src="{{ auth()->user()->photo ? asset(auth()->user()->photo) : asset('img/user.png') }}"
                    class="img-circle elevation-2 border border-blue-200" alt="User Image"
                    style="width: 38px; height: 38px; object-fit: cover;">
            </div>
            <div class="info">
                <a href="{{ route('admin.profile.edit') }}" class="d-block text-wrap"
                    style="max-width: 160px;">{{ auth()->user()->name }}</a>
                <span class="text-muted small"><i
                        class="fas fa-crown text-warning mr-1"></i>{{ auth()->user()->activePlan() ? auth()->user()->activePlan()->name : 'Acesso Limitado' }}</span>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" data-accordion="true"
                id="sidebar-tree" role="menu" style="gap: 2px;">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ $is('admin.dashboard') }} rounded-lg font-semibold">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('panel.admin.dashboard') }}" class="nav-link">
                        <i class="nav-icon fas fa-rocket"></i>
                        <p>Painel Novo</p>
                    </a>
                </li>

                @php
                    $canMarketplaceSeller = auth()->user()->canSellOnMarketplace();
                @endphp

                @if($canMarketplaceSeller)
                    <li class="nav-header">MARKETPLACE</li>
                    <li class="nav-item has-treeview {{ $open('admin.marketplace.*') }}">
                        <a href="#" class="nav-link {{ $is('admin.marketplace.*') }}">
                            <i class="nav-icon fas fa-store"></i>
                            <p>Marketplace<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item">
                                <a href="{{ route('admin.marketplace.index') }}"
                                    class="nav-link {{ $is('admin.marketplace.index') }}">
                                    <i class="fas fa-chart-line nav-icon"></i>
                                    <p>Painel</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.marketplace.payments') }}"
                                    class="nav-link {{ $is('admin.marketplace.payments') }}">
                                    <i class="fas fa-credit-card nav-icon"></i>
                                    <p>Pagamentos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.marketplace.sales') }}"
                                    class="nav-link {{ $is('admin.marketplace.sales') }}">
                                    <i class="fas fa-receipt nav-icon"></i>
                                    <p>Minhas vendas</p>
                                </a>
                            </li>
                            @if(auth()->user()->isAdmin())
                                <li class="nav-item">
                                    <a href="{{ route('admin.splits.index') }}"
                                        class="nav-link {{ $is('admin.splits.index') }}">
                                        <i class="fas fa-money-bill-wave nav-icon text-success"></i>
                                        <p>Extrato de Splits</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Menu para Membros --}}
                {{-- Menu para Membros (Removido: Portal e Comunidade) --}}

                {{-- Itens disponíveis para todos (Membros e Admins) --}}
                @if(auth()->user()->canAccessFeature('courses') || auth()->user()->hasPurchasedCourses())
                    <li class="nav-item has-treeview {{ $open('admin.courses.*') }}">
                        <a href="#" class="nav-link {{ $is('admin.courses.*') }}">
                            <i class="nav-icon fas fa-graduation-cap"></i>
                            <p>Cursos<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item"><a href="{{ route('admin.courses.available') }}" class="nav-link"><i
                                        class="fas fa-list nav-icon"></i>
                                    <p>Meus Cursos</p>
                                </a></li>
                            @if(auth()->user()->isAdmin())
                                <li class="nav-item"><a href="{{ route('admin.courses.index') }}"
                                        class="nav-link {{ $is('admin.courses.index') }}"><i class="fas fa-cog nav-icon"></i>
                                        <p>Gerenciar</p>
                                    </a></li>
                                <li class="nav-item"><a href="{{ route('admin.courses.create') }}"
                                        class="nav-link {{ $is('admin.courses.create') }}"><i class="fas fa-plus nav-icon"></i>
                                        <p>Novo</p>
                                    </a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if(auth()->user()->canAccessFeature('events'))
                    <li class="nav-item has-treeview {{ $open('admin.events.*') }}">
                        <a href="#" class="nav-link {{ $is('admin.events.*') }}">
                            <i class="nav-icon fas fa-calendar"></i>
                            <p>Eventos<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item"><a href="{{ route('admin.events.index') }}" data-pjax="false"
                                    class="nav-link {{ $is('admin.events.index') }}"><i
                                        class="fas fa-calendar-alt nav-icon"></i>
                                    <p>Calendário</p>
                                </a></li>
                            <li class="nav-item"><a href="{{ route('admin.events.create') }}"
                                    class="nav-link {{ $is('admin.events.create') }}"><i class="fas fa-plus nav-icon"></i>
                                    <p>Novo</p>
                                </a></li>
                        </ul>
                    </li>
                @endif

                @if(auth()->user()->isAdmin() || auth()->user()->canAccessFeature('courses'))
                    <li class="nav-item">
                        <a href="{{ route('admin.certificates.index') }}"
                            class="nav-link {{ $is('admin.certificates.*') }}">
                            <i class="nav-icon fas fa-certificate"></i>
                            <p>Certificados</p>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->canAccessFeature('mentorships'))
                    <li class="nav-item has-treeview {{ $open('admin.mentorships.*') }}">
                        <a href="#" class="nav-link {{ $is('admin.mentorships.*') }}">
                            <i class="nav-icon fas fa-chalkboard-teacher"></i>
                            <p>Mentorias<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item"><a href="{{ route('admin.mentorships.available') }}"
                                    class="nav-link {{ $is('admin.mentorships.available') }}"><i
                                        class="fas fa-list nav-icon"></i>
                                    <p>Disponíveis</p>
                                </a></li>
                            @if(auth()->user()->isAdmin())
                                <li class="nav-item"><a href="{{ route('admin.mentorships.index') }}"
                                        class="nav-link {{ $is('admin.mentorships.index') }}"><i
                                            class="fas fa-cog nav-icon"></i>
                                        <p>Gerenciar</p>
                                    </a></li>
                                <li class="nav-item"><a href="{{ route('admin.mentorships.create') }}"
                                        class="nav-link {{ $is('admin.mentorships.create') }}"><i
                                            class="fas fa-plus nav-icon"></i>
                                        <p>Novo</p>
                                    </a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Itens exclusivos de Admin --}}
                @if(auth()->user()->isAdmin())
                    @php
                        $marketingPatterns = [
                            'admin.plans.*',
                            'admin.orders.*',
                            'admin.invoices.*',
                            'admin.coupons.*',
                            'admin.redemptions.*',
                            'admin.referrals.*',
                        ];

                        $contentPatterns = [
                            'admin.partners.*',
                            'admin.jobs.*',
                            'admin.fonts.*',
                            'admin.faqs.*',
                            'admin.pages.*',
                        ];

                        $systemPatterns = [
                            'admin.users.*',
                            'admin.permissions.*',
                            'admin.points-rules.*',
                            'admin.mailtemplates.*',
                            'admin.cron.*',
                        ];

                        $settingsPatterns = [
                            'admin.settings*',
                        ];
                    @endphp

                    <li class="nav-header">ADMINISTRAÇÃO</li>

                    {{-- 1. Marketing & Vendas --}}
                    <li class="nav-item has-treeview {{ $open($marketingPatterns) }}">
                        <a href="#" class="nav-link {{ $is($marketingPatterns) }}">
                            <i class="nav-icon fas fa-bullhorn"></i>
                            <p>Gestão & Vendas<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item">
                                <a href="{{ route('admin.plans.index') }}" class="nav-link {{ $is('admin.plans.*') }}">
                                    <i class="fas fa-tags nav-icon"></i>
                                    <p>Planos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.orders.index') }}" class="nav-link {{ $is('admin.orders.*') }}">
                                    <i class="fas fa-shopping-cart nav-icon"></i>
                                    <p>Vendas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.invoices.index') }}"
                                    class="nav-link {{ $is('admin.invoices.*') }}">
                                    <i class="fas fa-file-invoice nav-icon"></i>
                                    <p>Faturas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ $is('admin.coupons.*') }}">
                                    <i class="fas fa-ticket-alt nav-icon"></i>
                                    <p>Cupons</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.redemptions.index') }}"
                                    class="nav-link {{ $is('admin.redemptions.*') }}">
                                    <i class="fas fa-gift nav-icon"></i>
                                    <p>Itens de Resgate</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.referrals.index') }}"
                                    class="nav-link {{ $is('admin.referrals.*') }}">
                                    <i class="fas fa-share-alt nav-icon"></i>
                                    <p>Afiliados</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- 2. Conteúdo do Portal --}}
                    <li class="nav-item has-treeview {{ $open($contentPatterns) }}">
                        <a href="#" class="nav-link {{ $is($contentPatterns) }}">
                            <i class="nav-icon fas fa-photo-video"></i>
                            <p>Conteúdo do Portal<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item">
                                <a href="{{ route('admin.partners.index') }}"
                                    class="nav-link {{ $is('admin.partners.*') }}">
                                    <i class="fas fa-handshake nav-icon"></i>
                                    <p>Parceiros</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.jobs.index') }}" class="nav-link {{ $is('admin.jobs.*') }}">
                                    <i class="fas fa-briefcase nav-icon"></i>
                                    <p>Mural de Vagas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.fonts.index') }}" class="nav-link {{ $is('admin.fonts.*') }}">
                                    <i class="fas fa-font nav-icon"></i>
                                    <p>Fontes</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.faqs.index') }}" class="nav-link {{ $is('admin.faqs.*') }}">
                                    <i class="fas fa-question-circle nav-icon"></i>
                                    <p>FAQ</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.pages.index') }}" class="nav-link {{ $is('admin.pages.*') }}">
                                    <i class="fas fa-file-alt nav-icon"></i>
                                    <p>Páginas do Site</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- 3. Sistema e Acessos --}}
                    <li class="nav-item has-treeview {{ $open($systemPatterns) }}">
                        <a href="#" class="nav-link {{ $is($systemPatterns) }}">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Sistema e Acessos<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ $is('admin.users.*') }}">
                                    <i class="fas fa-users-cog nav-icon"></i>
                                    <p>Usuários</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.permissions.index') }}"
                                    class="nav-link {{ $is('admin.permissions.*') }}">
                                    <i class="fas fa-user-shield nav-icon"></i>
                                    <p>Permissões</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.points-rules.index') }}"
                                    class="nav-link {{ $is('admin.points-rules.*') }}">
                                    <i class="fas fa-star nav-icon"></i>
                                    <p>Regras de Pontos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.mailtemplates.index') }}"
                                    class="nav-link {{ $is('admin.mailtemplates.*') }}">
                                    <i class="fas fa-envelope nav-icon"></i>
                                    <p>E-mails Automáticos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('admin/activity-logs') }}"
                                    class="nav-link {{ request()->is('admin/activity-logs*') ? 'active' : '' }}">
                                    <i class="fas fa-history nav-icon"></i>
                                    <p>Logs de Atividade</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.cron.index') }}" class="nav-link {{ $is('admin.cron.*') }}">
                                    <i class="fas fa-clock nav-icon"></i>
                                    <p>Cron Jobs</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- 4. Configurações Globais --}}
                    <li class="nav-item has-treeview {{ $open($settingsPatterns) }}">
                        <a href="#" class="nav-link {{ $is($settingsPatterns) }}">
                            <i class="nav-icon fas fa-tools"></i>
                            <p>Configurações Globais<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'general']) }}"
                                    class="nav-link {{ request()->route('group') == 'general' || !request()->route('group') && request()->routeIs('admin.settings') ? 'active' : '' }}">
                                    <i class="fas fa-info-circle nav-icon"></i>
                                    <p>Geral</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'appearance']) }}"
                                    class="nav-link {{ request()->route('group') == 'appearance' ? 'active' : '' }}">
                                    <i class="fas fa-paint-brush nav-icon"></i>
                                    <p>Aparência</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'images']) }}"
                                    class="nav-link {{ request()->route('group') == 'images' ? 'active' : '' }}">
                                    <i class="fas fa-images nav-icon"></i>
                                    <p>Imagens</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'player']) }}"
                                    class="nav-link {{ request()->route('group') == 'player' ? 'active' : '' }}">
                                    <i class="fas fa-play-circle nav-icon"></i>
                                    <p>Player</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'ads']) }}"
                                    class="nav-link {{ request()->route('group') == 'ads' ? 'active' : '' }}">
                                    <i class="fas fa-ad nav-icon"></i>
                                    <p>Anúncios</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'pwa']) }}"
                                    class="nav-link {{ request()->route('group') == 'pwa' ? 'active' : '' }}">
                                    <i class="fas fa-mobile-alt nav-icon"></i>
                                    <p>PWA</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'marketplace']) }}"
                                    class="nav-link {{ request()->route('group') == 'marketplace' ? 'active' : '' }}">
                                    <i class="fas fa-store nav-icon"></i>
                                    <p>Marketplace</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'gateway']) }}"
                                    class="nav-link {{ request()->route('group') == 'gateway' ? 'active' : '' }}">
                                    <i class="fas fa-credit-card nav-icon"></i>
                                    <p>Pagamentos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'smtp']) }}"
                                    class="nav-link {{ request()->route('group') == 'smtp' ? 'active' : '' }}">
                                    <i class="fas fa-envelope nav-icon"></i>
                                    <p>SMTP</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'social']) }}"
                                    class="nav-link {{ request()->route('group') == 'social' ? 'active' : '' }}">
                                    <i class="fas fa-users nav-icon"></i>
                                    <p>Social</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'seo']) }}"
                                    class="nav-link {{ request()->route('group') == 'seo' ? 'active' : '' }}">
                                    <i class="fas fa-search nav-icon"></i>
                                    <p>SEO</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings', ['group' => 'system']) }}"
                                    class="nav-link {{ request()->route('group') == 'system' ? 'active' : '' }}">
                                    <i class="fas fa-server nav-icon"></i>
                                    <p>Sistema</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <li class="nav-header">PERSONALIZAÇÃO</li>

                <li class="nav-item">
                    <a href="{{ route('admin.profile.edit') }}" class="nav-link {{ $is('admin.profile.*') }}">
                        <i class="nav-icon fas fa-id-card"></i>
                        <p>Meu Perfil</p>
                    </a>
                </li>

                @if(auth()->user()->hasPermission('testimonials.view') || auth()->user()->hasPermission('testimonials.moderate') || auth()->user()->hasPermission('testimonials.delete'))
                    <li class="nav-item">
                        <a href="{{ route('admin.testimonials.index') }}"
                            class="nav-link {{ $is('admin.testimonials.*') }}">
                            <i class="nav-icon fas fa-quote-left"></i>
                            <p>Depoimentos
                                @if(isset($pendingTestimonialsCount) && $pendingTestimonialsCount > 0)
                                    <span class="badge badge-success right">{{ $pendingTestimonialsCount }}</span>
                                @endif
                            </p>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->isAdmin() || auth()->user()->canAccessFeature('courses') || auth()->user()->canAccessFeature('mentorships'))
                    <li class="nav-item">
                        <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ $is('admin.reviews.*') }}">
                            <i class="nav-icon fas fa-star-half-alt"></i>
                            <p>Avaliações
                                @if(isset($pendingReviewsCount) && $pendingReviewsCount > 0)
                                    <span class="badge badge-info right">{{ $pendingReviewsCount }}</span>
                                @endif
                            </p>
                        </a>
                    </li>
                @endif

                <!-- Comunidade -->
                @if(auth()->user()->canAccessFeature('community'))
                    <li class="nav-item">
                        {{-- Usa rota do feed se disponível, senão admin.social --}}
                        <a href="{{ route('admin.social.feed.internal') }}" class="nav-link {{ $is('admin.social.*') }}">
                            <i class="nav-icon fas fa-comments"></i>
                            <p>Comunidade
                                @if(isset($pendingConnectionsCount) && $pendingConnectionsCount > 0)
                                    <span class="badge badge-warning right">{{ $pendingConnectionsCount }}</span>
                                @endif
                            </p>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->canAccessFeature('chat'))
                    <li class="nav-item">
                        <a href="{{ route('admin.chat.index') }}" class="nav-link {{ $is('admin.chat.*') }}">
                            <i class="nav-icon fas fa-comment-dots"></i>
                            <p>Chat
                                @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                                    <span class="badge badge-danger right">{{ $unreadMessagesCount }}</span>
                                @endif
                            </p>
                        </a>
                    </li>
                @endif

            </ul>
        </nav>
        {{-- Remove custom color overrides so AdminLTE default styles apply --}}
    </div>
</aside>