{{-- Cabeçalho AdminLTE --}}
<nav class="main-header navbar navbar-expand navbar-dark sidebar-dark-primary">
    <ul class="navbar-nav mr-auto align-items-center">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('home') }}" class="nav-link" target="_blank" rel="noopener">Ver site</a>
        </li>
        @if(session()->has('impersonator_id'))
        <li class="nav-item ml-3">
            <div class="bg-warning px-3 py-1 rounded d-flex align-items-center">
                <span class="text-dark mr-2 font-weight-bold"><i class="fas fa-user-secret mr-1"></i> Acessando como: {{ auth()->user()->name }}</span>
                <a href="{{ route('admin.impersonate.stop') }}" class="btn btn-xs btn-dark">Voltar ao Admin</a>
            </div>
        </li>
        @endif
    </ul>

    <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item mr-2 d-flex align-items-center">
            <form method="POST" action="{{ route('admin.settings.update') }}" id="themeToggleForm" class="m-0 p-0">
                @csrf
                <input type="hidden" name="site_theme" id="site_theme_input" value="{{ $settings['site_theme'] ?? 'light' }}">
                <button type="button" class="btn btn-sm btn-outline-light d-flex align-items-center" id="themeToggleBtn" title="Alternar tema">
                    <i class="fas {{ ($settings['site_theme'] ?? 'light') === 'dark' ? 'fa-sun' : 'fa-moon' }}"></i>
                </button>
            </form>
        </li>
        @auth
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" aria-expanded="false">
                @if(auth()->user()->photo)
                    <img src="{{ asset('storage/'.auth()->user()->photo) }}" alt="User" class="img-circle mr-2" style="width:30px;height:30px;object-fit:cover;">
                @else
                    <i class="fas fa-user-circle mr-1" style="font-size: 1.5rem;"></i>
                @endif
                <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <span class="dropdown-item-text text-muted text-sm text-center font-weight-bold">
                    {{ (auth()->user()->role ?? 'user') === 'superadmin' ? 'Superadmin' : (auth()->user()->role === 'admin' ? 'Admin' : 'Membro') }}
                </span>
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.profile.edit') }}" class="dropdown-item">
                    <i class="fas fa-id-card mr-2 text-primary"></i> Meu Perfil
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i> Sair
                    </button>
                </form>
            </div>
        </li>
        @endauth
    </ul>
</nav>
