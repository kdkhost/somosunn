{{-- Cabeçalho Oficial AdminLTE 4 para /painel/admin --}}
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        {{-- Start Navbar Links --}}
        <ul class="navbar-nav align-items-center">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Alternar menu">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-block">
                <a href="{{ route('home') }}" class="nav-link" target="_blank" rel="noopener">
                    <i class="fas fa-external-link-alt me-1 small opacity-75"></i> Ver site
                </a>
            </li>
            <li class="nav-item d-none d-lg-block">
                <a href="{{ route('panel.dashboard') }}" class="nav-link">
                    <i class="fas fa-user me-1 small opacity-75"></i> Área do Membro
                </a>
            </li>
        </ul>

        {{-- End Navbar Links --}}
        <ul class="navbar-nav ms-auto align-items-center">
            {{-- Quick Upload Icon --}}
            <li class="nav-item me-1">
                <a class="nav-link" href="#" onclick="event.preventDefault(); if (typeof window.openQuickUploadModal === 'function') window.openQuickUploadModal();" title="Registrar Fotos de Evento">
                    <i class="fas fa-camera"></i>
                </a>
            </li>

            {{-- Escanear Ingressos --}}
            @if(\Illuminate\Support\Facades\Route::has('panel.admin.quick-scanner'))
                <li class="nav-item me-1">
                    <a class="nav-link" href="{{ route('panel.admin.quick-scanner') }}" title="Escanear Ingressos">
                        <i class="fas fa-qrcode"></i>
                    </a>
                </li>
            @endif

            {{-- Alternar Tema Dark/Light --}}
            @php
                $panelCurrentTheme = ($siteTheme ?? 'light') === 'dark' ? 'dark' : 'light';
            @endphp
            <li class="nav-item me-1 d-flex align-items-center">
                <a class="nav-link" href="#" id="panelAdminThemeToggleBtn" role="button" title="Alternar tema Claro/Escuro">
                    <i class="fas {{ $panelCurrentTheme === 'dark' ? 'fa-sun text-warning' : 'fa-moon' }}" id="panelAdminThemeIcon"></i>
                </a>
            </li>

            {{-- User Menu Dropdown --}}
            @auth
                <li class="nav-item dropdown user-menu">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                        @if(auth()->user()->photo)
                            <img src="{{ asset(auth()->user()->photo) }}" alt="User" class="rounded-circle me-2 object-fit-cover"
                                style="width:30px;height:30px;" loading="lazy" decoding="async">
                        @else
                            <i class="fas fa-user-circle me-1 fs-5"></i>
                        @endif
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <span class="dropdown-item-text text-secondary small text-center fw-bold d-block">
                            {{ (auth()->user()->role ?? 'user') === 'superadmin' ? 'Superadmin' : (auth()->user()->role === 'admin' ? 'Admin' : 'Membro') }}
                        </span>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('panel.profile.edit') }}" class="dropdown-item">
                            <i class="fas fa-id-card me-2 text-primary"></i> Meu Perfil
                        </a>
                        @if(\Illuminate\Support\Facades\Route::has('admin.dashboard') && auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                                <i class="fas fa-shield-alt me-2 text-info"></i> Painel Global (/admin)
                            </a>
                        @endif
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Sair
                            </button>
                        </form>
                    </div>
                </li>
            @endauth
        </ul>
    </div>
</nav>
