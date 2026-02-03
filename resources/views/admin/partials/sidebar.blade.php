@php
    $is = fn($patterns) => request()->routeIs($patterns) ? 'active' : '';
    $open = fn($patterns) => request()->routeIs($patterns) ? 'menu-open' : '';
@endphp
@php
    // Tenta carregar logo específica do admin, se não tiver, usa a logo principal, senão usa o padrão
    $logoAdmin = \App\Models\Setting::get('logo_admin');
    $logoMain = \App\Models\Setting::get('logo_image');
    $logoFavicon = \App\Models\Setting::get('favicon_image');
    
    $brandLogo = $logoAdmin ? asset($logoAdmin) : ($logoMain ? asset($logoMain) : asset('img/logo.svg'));
    $brandFavicon = $logoFavicon ? asset($logoFavicon) : (file_exists(public_path('favicon.ico')) ? asset('favicon.ico') : $brandLogo);
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('admin.dashboard') }}" class="brand-link d-flex align-items-center justify-content-center p-0" style="height:60px; overflow:hidden;">
        <img src="{{ $brandLogo }}" alt="UNN" class="brand-logo-img" style="width: auto; height: 100%; max-width: 100%; object-fit: contain; padding: 5px;">
        <img src="{{ $brandFavicon }}" alt="UNN" class="brand-favicon-img" style="width: auto; height: 100%; max-width: 100%; object-fit: contain; padding: 5px; display: none;">
    </a>
    <style>
        .sidebar-collapse .brand-link .brand-logo-img { display: none !important; }
        .sidebar-collapse .brand-link .brand-favicon-img { display: block !important; }
        .sidebar-mini.sidebar-collapse .main-sidebar:hover .brand-link .brand-logo-img { display: block !important; }
        .sidebar-mini.sidebar-collapse .main-sidebar:hover .brand-link .brand-favicon-img { display: none !important; }
    </style>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" data-accordion="true" id="sidebar-tree" role="menu">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $is('admin.dashboard') }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item has-treeview {{ $open('admin.courses.*') }}">
                    <a href="#" class="nav-link {{ $is('admin.courses.*') }}">
                        <i class="nav-icon fas fa-graduation-cap"></i>
                        <p>Cursos<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="{{ route('admin.courses.index') }}" class="nav-link {{ $is('admin.courses.index') }}"><i class="fas fa-list nav-icon"></i><p>Listar</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.courses.create') }}" class="nav-link {{ $is('admin.courses.create') }}"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ $open('admin.users.*') }}">
                    <a href="#" class="nav-link {{ $is('admin.users.*') }}">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Usuários<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link {{ $is('admin.users.index') }}"><i class="fas fa-list nav-icon"></i><p>Listar</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.users.create') }}" class="nav-link {{ $is('admin.users.create') }}"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ $open('admin.events.*') }}">
                    <a href="#" class="nav-link {{ $is('admin.events.*') }}">
                        <i class="nav-icon fas fa-calendar"></i>
                        <p>Eventos<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="{{ route('admin.events.index') }}" class="nav-link {{ $is('admin.events.index') }}"><i class="fas fa-calendar-alt nav-icon"></i><p>Calendário</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.events.create') }}" class="nav-link {{ $is('admin.events.create') }}"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ $open('admin.mentorships.*') }}">
                    <a href="#" class="nav-link {{ $is('admin.mentorships.*') }}">
                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                        <p>Mentorias<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="{{ route('admin.mentorships.index') }}" class="nav-link {{ $is('admin.mentorships.index') }}"><i class="fas fa-list nav-icon"></i><p>Listar</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.mentorships.create') }}" class="nav-link {{ $is('admin.mentorships.create') }}"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ $open('admin.plans.*') }}">
                    <a href="#" class="nav-link {{ $is('admin.plans.*') }}">
                        <i class="nav-icon fas fa-tags"></i>
                        <p>Planos<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="{{ route('admin.plans.index') }}" class="nav-link {{ $is('admin.plans.index') }}"><i class="fas fa-list nav-icon"></i><p>Listar</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.plans.create') }}" class="nav-link {{ $is('admin.plans.create') }}"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ $open('admin.certificates.*') }}">
                    <a href="#" class="nav-link {{ $is('admin.certificates.*') }}">
                        <i class="nav-icon fas fa-certificate"></i>
                        <p>Certificados<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="{{ route('admin.certificates.create') }}" class="nav-link {{ $is('admin.certificates.create') }}"><i class="fas fa-file-signature nav-icon"></i><p>Gerar</p></a></li>
                    </ul>
                </li>



                <li class="nav-item has-treeview {{ $open(['admin.mailtemplates.*','admin.mailtest']) }}">
                    <a href="#" class="nav-link {{ $is(['admin.mailtemplates.*','admin.mailtest']) }}">
                        <i class="nav-icon fas fa-envelope"></i>
                        <p>E-mails<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="{{ route('admin.mailtemplates.index') }}" class="nav-link {{ $is('admin.mailtemplates.index') }}"><i class="fas fa-table nav-icon"></i><p>Templates</p></a></li>

                    </ul>
                </li>

                <li class="nav-item has-treeview {{ $open(['admin.points-rules.*','admin.ranking']) }}">
                    <a href="#" class="nav-link {{ $is(['admin.points-rules.*','admin.ranking']) }}">
                        <i class="nav-icon fas fa-star"></i>
                        <p>Pontuação<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="{{ route('admin.points-rules.index') }}" class="nav-link {{ $is('admin.points-rules.index') }}"><i class="fas fa-sliders-h nav-icon"></i><p>Regras</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.ranking') }}" class="nav-link {{ $is('admin.ranking') }}"><i class="fas fa-trophy nav-icon"></i><p>Ranking</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ $open('admin.permissions.*') }}">
                    <a href="#" class="nav-link {{ $is('admin.permissions.*') }}">
                        <i class="nav-icon fas fa-user-shield"></i>
                        <p>Permissões<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="{{ route('admin.permissions.index') }}" class="nav-link {{ $is('admin.permissions.index') }}"><i class="fas fa-list nav-icon"></i><p>Listar</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.permissions.create') }}" class="nav-link {{ $is('admin.permissions.create') }}"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <!-- Financeiro / Vendas -->
                <li class="nav-item">
                    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ $is('admin.orders.*') }}">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>Vendas</p>
                    </a>
                </li>

                <!-- Comunidade -->
                <li class="nav-item">
                    <a href="{{ route('admin.social.index') }}" class="nav-link {{ $is('admin.social.*') }}">
                        <i class="nav-icon fas fa-comments"></i>
                        <p>Comunidade</p>
                    </a>
                </li>


                
                <li class="nav-item"><a href="{{ route('admin.settings') }}" class="nav-link {{ $is('admin.settings') }}"><i class="nav-icon fas fa-cogs"></i><p>Configurações</p></a></li>
            </ul>
        </nav>
    </div>
</aside>
