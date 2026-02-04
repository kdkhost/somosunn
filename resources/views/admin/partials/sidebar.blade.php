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
                <!-- DEBUG: REST OF MENU TEMPORARILY HIDDEN TO CHECK IF CRASH IS ABOVE -->
            </ul>
        </nav>
    </div>
</aside>
