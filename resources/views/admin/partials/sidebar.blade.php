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
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('admin.dashboard') }}" class="brand-link d-flex align-items-center justify-content-center p-0"
        style="height:60px; overflow:hidden;">
        {{-- Logo Grande (Padrão) --}}
        <img src="{{ $brandLogo }}" alt="UNN" class="brand-logo-img"
            style="max-height: 50px; width: auto; max-width: 90%; object-fit: contain;">
        {{-- Favicon (Mini) --}}
        <img src="{{ $brandFavicon }}" alt="UNN" class="brand-favicon-img"
            style="max-height: 50px; width: auto; max-width: 90%; object-fit: contain;">
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
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" data-accordion="true"
                id="sidebar-tree" role="menu">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $is('admin.dashboard') }}">
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
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item"><a href="{{ route('courses.index') }}" class="nav-link"><i
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

                @if(auth()->user()->isAdmin() && auth()->user()->canAccessFeature('events'))
                    <li class="nav-item has-treeview {{ $open('admin.events.*') }}">
                        <a href="#" class="nav-link {{ $is('admin.events.*') }}">
                            <i class="nav-icon fas fa-calendar"></i>
                            <p>Eventos<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item"><a href="{{ route('admin.events.index') }}" class="nav-link {{ $is('admin.events.index') }}"><i
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
                            <li class="nav-item"><a href="{{ route('mentorships.index') }}" class="nav-link"><i
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
                    <li class="nav-header">ADMINISTRAÇÃO</li>

                    <li class="nav-item has-treeview {{ $open('admin.users.*') }}">
                        <a href="#" class="nav-link {{ $is('admin.users.*') }}">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>Usuários<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item"><a href="{{ route('admin.users.index') }}"
                                    class="nav-link {{ $is('admin.users.index') }}"><i class="fas fa-list nav-icon"></i>
                                    <p>Listar</p>
                                </a></li>
                            <li class="nav-item"><a href="{{ route('admin.users.create') }}"
                                    class="nav-link {{ $is('admin.users.create') }}"><i class="fas fa-plus nav-icon"></i>
                                    <p>Novo</p>
                                </a></li>
                        </ul>
                    </li>
                    </li>

                    <li class="nav-item has-treeview {{ $open('admin.plans.*') }}">
                        <a href="#" class="nav-link {{ $is('admin.plans.*') }}">
                            <i class="nav-icon fas fa-tags"></i>
                            <p>Planos<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item"><a href="{{ route('admin.plans.index') }}"
                                    class="nav-link {{ $is('admin.plans.index') }}"><i class="fas fa-list nav-icon"></i>
                                    <p>Listar</p>
                                </a></li>
                            <li class="nav-item"><a href="{{ route('admin.plans.create') }}"
                                    class="nav-link {{ $is('admin.plans.create') }}"><i class="fas fa-plus nav-icon"></i>
                                    <p>Novo</p>
                                </a></li>
                        </ul>
                    </li>

                    <li class="nav-item has-treeview {{ $open('admin.certificates.*') }}">
                        <a href="#" class="nav-link {{ $is('admin.certificates.*') }}">
                            <i class="nav-icon fas fa-certificate"></i>
                            <p>Certificados<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item"><a href="{{ route('admin.certificates.create') }}"
                                    class="nav-link {{ $is('admin.certificates.create') }}"><i
                                        class="fas fa-file-signature nav-icon"></i>
                                    <p>Gerar</p>
                                </a></li>
                        </ul>
                    </li>

                    <li class="nav-item has-treeview {{ $open(['admin.mailtemplates.*', 'admin.mailtest']) }}">
                        <a href="#" class="nav-link {{ $is(['admin.mailtemplates.*', 'admin.mailtest']) }}">
                            <i class="nav-icon fas fa-envelope"></i>
                            <p>E-mails<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item"><a href="{{ route('admin.mailtemplates.index') }}"
                                    class="nav-link {{ $is('admin.mailtemplates.index') }}"><i
                                        class="fas fa-table nav-icon"></i>
                                    <p>Templates</p>
                                </a></li>
                        </ul>
                    </li>

                    <li class="nav-item has-treeview {{ $open(['admin.points-rules.*', 'admin.ranking']) }}">
                        <a href="#" class="nav-link {{ $is(['admin.points-rules.*', 'admin.ranking']) }}">
                            <i class="nav-icon fas fa-star"></i>
                            <p>Pontuação<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item"><a href="{{ route('admin.points-rules.index') }}"
                                    class="nav-link {{ $is('admin.points-rules.index') }}"><i
                                        class="fas fa-sliders-h nav-icon"></i>
                                    <p>Regras</p>
                                </a></li>
                            <li class="nav-item"><a href="{{ route('admin.ranking') }}"
                                    class="nav-link {{ $is('admin.ranking') }}"><i class="fas fa-trophy nav-icon"></i>
                                    <p>Ranking</p>
                                </a></li>
                        </ul>
                    </li>

                    <li class="nav-item has-treeview {{ $open('admin.permissions.*') }}">
                        <a href="#" class="nav-link {{ $is('admin.permissions.*') }}">
                            <i class="nav-icon fas fa-user-shield"></i>
                            <p>Permissões<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview pl-4">
                            <li class="nav-item"><a href="{{ route('admin.permissions.index') }}"
                                    class="nav-link {{ $is('admin.permissions.index') }}"><i
                                        class="fas fa-list nav-icon"></i>
                                    <p>Listar</p>
                                </a></li>
                            <li class="nav-item"><a href="{{ route('admin.permissions.create') }}"
                                    class="nav-link {{ $is('admin.permissions.create') }}"><i
                                        class="fas fa-plus nav-icon"></i>
                                    <p>Novo</p>
                                </a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.orders.index') }}" class="nav-link {{ $is('admin.orders.*') }}">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Vendas</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ $is('admin.coupons.*') }}">
                            <i class="nav-icon fas fa-ticket-alt"></i>
                            <p>Cupons</p>
                        </a>
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
                        <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ $is('admin.testimonials.*') }}">
                            <i class="nav-icon fas fa-quote-left"></i>
                            <p>Depoimentos</p>
                        </a>
                    </li>
                @endif

                <!-- Comunidade -->
                @if(auth()->user()->canAccessFeature('community'))
                    <li class="nav-item">
                        {{-- Usa rota do feed se disponível, senão admin.social --}}
                        <a href="{{ Route::has('social.feed') ? route('social.feed') : route('admin.social.index') }}"
                            class="nav-link {{ $is(['admin.social.*', 'social.*']) }}">
                            <i class="nav-icon fas fa-comments"></i>
                            <p>Comunidade</p>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->canAccessFeature('chat'))
                    <li class="nav-item">
                        <a href="{{ route('chat.index') }}" class="nav-link {{ $is('chat.*') }}">
                            <i class="nav-icon fas fa-comment-dots"></i>
                            <p>Chat</p>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->isAdmin())
                    <li class="nav-item"><a href="{{ route('admin.settings') }}"
                            class="nav-link {{ $is('admin.settings') }}"><i class="nav-icon fas fa-cogs"></i>
                            <p>Configurações</p>
                        </a></li>
                @endif
        </nav>
    </div>
</aside>
