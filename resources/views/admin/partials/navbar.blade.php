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
                    <span class="text-dark mr-2 font-weight-bold"><i class="fas fa-user-secret mr-1"></i> Acessando como:
                        {{ auth()->user()->name }}</span>
                    <a href="{{ route('admin.impersonate.stop') }}" class="btn btn-xs btn-danger">Voltar ao Admin</a>
                </div>
            </li>
        @endif
    </ul>

    <ul class="navbar-nav ml-auto align-items-center">
        <!-- Chat Icon -->
        <li class="nav-item dropdown mr-2">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-comments"></i>
                @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                    <span class="badge badge-danger navbar-badge">{{ $unreadMessagesCount }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">{{ $unreadMessagesCount ?? 0 }} Mensagens</span>
                <div class="dropdown-divider"></div>
                @if(isset($unreadMessagesGroups) && $unreadMessagesGroups->isNotEmpty())
                    @foreach($unreadMessagesGroups as $group)
                        <a href="{{ route('admin.chat.start', $group->user->id) }}" class="dropdown-item">
                            <div class="media">
                                <img src="{{ $group->user->photo ? asset($group->user->photo) : asset('img/default-user.png') }}"
                                    alt="User Avatar" class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        {{ $group->user->name }}
                                        <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                    </h3>
                                    <p class="text-sm">{{ $group->count }} nova(s) mensagem(ns)</p>
                                    <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i>
                                        {{ $group->latest->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                    @endforeach
                @else
                    <a href="#" class="dropdown-item">Nenhuma mensagem nova</a>
                @endif
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.chat.index') }}" class="dropdown-item dropdown-footer">Ver Todas as Mensagens</a>
            </div>
        </li>

        <!-- Bell Icon (Notifications) -->
        <li class="nav-item dropdown mr-2">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                @if(isset($pendingConnectionsCount) && $pendingConnectionsCount > 0)
                    <span class="badge badge-warning navbar-badge">{{ $pendingConnectionsCount }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">{{ $pendingConnectionsCount ?? 0 }} Notificações</span>
                <div class="dropdown-divider"></div>
                @if(isset($pendingConnections) && $pendingConnections->isNotEmpty())
                    @foreach($pendingConnections as $conn)
                        <div class="dropdown-item">
                            <div class="media">
                                <img src="{{ $conn->requester->photo ? asset($conn->requester->photo) : asset('img/default-user.png') }}"
                                    class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        {{ \Illuminate\Support\Str::limit($conn->requester->name, 15) }}
                                    </h3>
                                    <p class="text-sm">Solicitou conexão</p>
                                    <div class="mt-2 text-right">
                                        <button onclick="acceptConnection({{ $conn->requester_id }})"
                                            class="btn btn-xs btn-success" title="Aceitar"><i class="fas fa-check"></i></button>
                                        <button onclick="removeConnection({{ $conn->requester_id }})"
                                            class="btn btn-xs btn-secondary" title="Recusar"><i
                                                class="fas fa-times"></i></button>
                                        <button onclick="blockConnection({{ $conn->requester_id }})"
                                            class="btn btn-xs btn-danger" title="Bloquear"><i class="fas fa-ban"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                    @endforeach
                @else
                    <span class="dropdown-item text-center text-muted">Sem novas notificações</span>
                @endif
            </div>
        </li>

        <li class="nav-item mr-2 d-flex align-items-center">
            <form method="POST" action="{{ route('admin.settings.update') }}" id="themeToggleForm" class="m-0 p-0">
                @csrf
                <input type="hidden" name="site_theme" id="site_theme_input"
                    value="{{ $settings['site_theme'] ?? 'light' }}">
                <button type="button" class="btn btn-sm btn-outline-light d-flex align-items-center" id="themeToggleBtn"
                    title="Alternar tema">
                    <i class="fas {{ ($settings['site_theme'] ?? 'light') === 'dark' ? 'fa-sun' : 'fa-moon' }}"></i>
                </button>
            </form>
        </li>
        @auth
            <li class="nav-item dropdown">
                <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" aria-expanded="false">
                    @if(auth()->user()->photo)
                        <img src="{{ asset(auth()->user()->photo) }}" alt="User" class="img-circle mr-2"
                            style="width:30px;height:30px;object-fit:cover;">
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
    @push('scripts')
        <script>
            function acceptConnection(userId) {
                fetch(`/connection/accept/${userId}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { toastr.success(data.message); location.reload(); }
                        else { toastr.error(data.message); }
                    });
            }
            function removeConnection(userId) {
                Swal.fire({
                    title: 'Recusar conexão?',
                    text: "Este usuário não será adicionado às suas conexões.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, recusar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/connection/remove/${userId}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) { toastr.info(data.message); location.reload(); }
                                else { toastr.error(data.message); }
                            });
                    }
                });
            }
            function blockConnection(userId) {
                Swal.fire({
                    title: 'Bloquear usuário?',
                    text: "Ele não poderá mais solicitar conexão.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, bloquear!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/connection/block/${userId}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) { toastr.warning(data.message); location.reload(); }
                                else { toastr.error(data.message); }
                            });
                    }
                });
            }
        </script>
    @endpush
</nav>
