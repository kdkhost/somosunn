@php
    $is = fn($patterns) => request()->routeIs($patterns) ? 'active' : '';
    $open = fn($patterns) => request()->routeIs($patterns) ? 'menu-open' : '';
@endphp
@php
    $brandLogo = asset('img/logo.svg'); // Default fallback
    $brandFavicon = asset('img/logo.svg'); // Default fallback

    try {
        $logoAdmin = \App\Models\Setting::get('logo_admin');
        $logoMain = \App\Models\Setting::get('logo_image');
        $logoFavicon = \App\Models\Setting::get('favicon_image');

        // Tenta usar logo_admin primeiro, depois logo_image
        if ($logoAdmin && file_exists(public_path($logoAdmin))) {
            $brandLogo = asset($logoAdmin);
        } elseif ($logoMain && file_exists(public_path($logoMain))) {
            $brandLogo = asset($logoMain);
        }

        // Tenta usar favicon personalizado
        if ($logoFavicon && file_exists(public_path($logoFavicon))) {
            $brandFavicon = asset($logoFavicon);
        } elseif (file_exists(public_path('favicon.ico'))) {
            $brandFavicon = asset('favicon.ico');
        }
    } catch (\Throwable $e) {
        // Usa fallback padrão em caso de erro
        \Log::error('Erro ao carregar logo da sidebar: ' . $e->getMessage());
    }
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4" style="font-family: 'Inter', sans-serif;">
    <a href="{{ route('admin.dashboard') }}" class="brand-link d-flex align-items-center justify-content-center p-0 bg-white border-b border-gray-200"
        style="height:60px; overflow:hidden;">
        <img src="{{ $brandLogo }}" alt="UNN" class="brand-logo-img transition-all duration-300"
            style="max-height: 44px; width: auto; max-width: 80%; object-fit: contain;">
        <img src="{{ $brandFavicon }}" alt="UNN" class="brand-favicon-img transition-all duration-300"
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
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center bg-gradient-to-r from-blue-50 to-white rounded-xl shadow-sm mx-2 px-2">
            <div class="image mr-2">
                <img src="{{ auth()->user()->photo ? asset(auth()->user()->photo) : asset('img/user.png') }}"
                    class="img-circle elevation-2 border border-blue-200" alt="User Image"
                    style="width: 38px; height: 38px; object-fit: cover;">
            </div>
            <div class="info">
                <a href="{{ route('admin.profile.edit') }}" class="d-block text-wrap font-bold text-blue-900"
                    style="max-width: 160px;">{{ auth()->user()->name }}</a>
                <span class="text-muted small"><i
                        class="fas fa-crown text-warning mr-1"></i>{{ auth()->user()->activePlan() ? auth()->user()->activePlan()->name : 'Acesso Limitado' }}</span>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" data-accordion="true"
                id="sidebar-tree" role="menu" style="gap: 2px;">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $is('admin.dashboard') }} rounded-lg font-semibold">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- Menu para Membros --}}
                {{-- Menu para Membros (Removido: Portal e Comunidade) --}}

                {{-- Itens disponíveis para todos (Membros e Admins) --}}
                @if(auth()->user()->canAccessFeature('courses'))
                    <li class="nav-item has-treeview {{ $open('admin.courses.*') }}">
                        <a href="#" class="nav-link {{ $is('admin.courses.*') }}">
                            <i class="nav-icon fas fa-graduation-cap"></i>
                            <p>Cursos<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4" style="background: #f8fafc; border-radius: 0 0 12px 12px;">
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
                        $adminMenuPatterns = [
                            'admin.users.*',
                            'admin.plans.*',
                            'admin.orders.*',
                            'admin.invoices.*',
                            'admin.coupons.*',
                            'admin.permissions.*',
                            'admin.points-rules.*',
                            'admin.ranking',
                            'admin.mailtemplates.*',
                            'admin.certificates.*',
                            'admin.fonts.*',
                            'admin.faqs.*',
                            'admin.settings',
                        ];
                    @endphp

                    <li class="nav-header">ADMINISTRAÇÃO</li>

                    <li class="nav-item has-treeview {{ $open($adminMenuPatterns) }}">
                        <a href="#" class="nav-link {{ $is($adminMenuPatterns) }}">
                            <i class="nav-icon fas fa-tools"></i>
                            <p>Administração<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ $is('admin.users.*') }}">
                                    <i class="fas fa-users-cog nav-icon"></i>
                                    <p>Usuários</p>
                                </a>
                            </li>
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
                                    <p>Pontuação</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.mailtemplates.index') }}"
                                    class="nav-link {{ $is('admin.mailtemplates.*') }}">
                                    <i class="fas fa-envelope nav-icon"></i>
                                    <p>E-mails</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.certificates.create') }}"
                                    class="nav-link {{ $is('admin.certificates.*') }}">
                                    <i class="fas fa-certificate nav-icon"></i>
                                    <p>Certificados</p>
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
                                <a href="{{ route('admin.settings') }}" class="nav-link {{ $is('admin.settings') }}">
                                    <i class="fas fa-cogs nav-icon"></i>
                                    <p>Configurações</p>
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


        </nav>
        <style>
        /* Sidebar custom UX */
        .main-sidebar {
            font-size: 1rem;
        }
        .main-sidebar .nav-link {
            transition: background 0.18s, color 0.18s;
        }
        .main-sidebar .nav-link.active, .main-sidebar .nav-link:hover {
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff !important;
        }
        .main-sidebar .nav-link.active i, .main-sidebar .nav-link:hover i {
            color: #fff !important;
        }
        .main-sidebar .nav-link i {
            color: #2563eb;
            transition: color 0.18s;
        }
        .main-sidebar .nav-treeview .nav-link {
            font-size: 0.97em;
            padding-left: 2.2rem;
        }
        @media (max-width: 900px) {
            .main-sidebar {
                font-size: 0.97rem;
            }
            .user-panel .info a {
                font-size: 0.97rem;
            }
        }
        @media (max-width: 600px) {
            .main-sidebar {
                font-size: 0.93rem;
            }
            .user-panel {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.2rem;
            }
            .user-panel .image {
                margin-bottom: 0.2rem;
            }
        }
        </style>
    </div>
</aside>