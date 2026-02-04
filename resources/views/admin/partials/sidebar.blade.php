@php
    $is = fn($patterns) => request()->routeIs($patterns) ? 'active' : '';
    $open = fn($patterns) => request()->routeIs($patterns) ? 'menu-open' : '';
@endphp
@php
    try {
        $logoAdmin = \App\Models\Setting::get('logo_admin');
        $logoMain = \App\Models\Setting::get('logo_image');
        $logoFavicon = \App\Models\Setting::get('favicon_image');
        $brandLogo = $logoAdmin ? asset($logoAdmin) : ($logoMain ? asset($logoMain) : asset('img/logo.svg'));
        $brandFavicon = $logoFavicon ? asset($logoFavicon) : (file_exists(public_path('favicon.ico')) ? asset('favicon.ico') : $brandLogo);
    } catch (\Throwable $e) {
        $brandLogo = asset('img/logo.svg');
        $brandFavicon = asset('img/logo.svg');
    }
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('admin.dashboard') }}" class="brand-link d-flex align-items-center justify-content-center p-0" style="height:60px; overflow:hidden; background: #fff;">
        <img src="{{ $brandLogo }}" alt="UNN" class="brand-logo-img" style="max-height: 50px; width: auto; max-width: 90%; object-fit: contain;">
        <img src="{{ $brandFavicon }}" alt="UNN" class="brand-favicon-img" style="max-height: 50px; width: auto; max-width: 90%; object-fit: contain; display: none;">
    </a>
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

                <!-- DEBUG: Remaining items hidden (Certificates, Emails, Points, Permissions, Sales, Community) -->
            </ul>
        </nav>
    </div>
</aside>
