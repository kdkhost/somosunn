{{-- Cabeçalho AdminLTE 4 --}}
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
                <a href="{{ route('home') }}" class="nav-link" target="_blank" rel="noopener">Ver site</a>
            </li>
        </ul>

        {{-- End Navbar Links --}}
        <ul class="navbar-nav ms-auto align-items-center">
            {{-- Chat Icon --}}
            <li class="nav-item dropdown me-1">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false" title="Mensagens">
                    <i class="far fa-comments"></i>
                    @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                        <span class="navbar-badge badge text-bg-danger">{{ $unreadMessagesCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <span class="dropdown-item dropdown-header">{{ $unreadMessagesCount ?? 0 }} Mensagens</span>
                    <div class="dropdown-divider"></div>
                    @if(isset($unreadMessagesGroups) && $unreadMessagesGroups->isNotEmpty())
                        @foreach($unreadMessagesGroups as $group)
                            <a href="{{ route('admin.chat.start', $group->user->id) }}" class="dropdown-item">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <img src="{{ $group->user->photo ? asset($group->user->photo) : asset('img/default-user.svg') }}"
                                            alt="Avatar" class="rounded-circle object-fit-cover" style="width:42px;height:42px;" loading="lazy" decoding="async">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h3 class="dropdown-item-title mb-1">
                                            {{ $group->user->name }}
                                            <span class="float-end fs-7 text-danger"><i class="fas fa-star"></i></span>
                                        </h3>
                                        <p class="fs-7 mb-1">{{ $group->count }} nova(s) mensagem(ns)</p>
                                        <p class="fs-7 text-secondary mb-0">
                                            <i class="far fa-clock me-1"></i>{{ $group->latest->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                        @endforeach
                    @else
                        <span class="dropdown-item text-center text-secondary">Nenhuma mensagem nova</span>
                    @endif
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('admin.chat.index') }}" class="dropdown-item dropdown-footer">Ver Todas as Mensagens</a>
                </div>
            </li>

            {{-- Quick Upload Icon --}}
            <li class="nav-item me-1">
                <a class="nav-link" href="#" onclick="event.preventDefault(); window.openQuickUploadModal()" title="Registrar Fotos de Evento">
                    <i class="fas fa-camera"></i>
                </a>
            </li>

            {{-- Bell Icon (Notifications) --}}
            <li class="nav-item dropdown me-1">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false" title="Notificações">
                    <i class="far fa-bell"></i>
                    @if(isset($totalNotificationsCount) && $totalNotificationsCount > 0)
                        <span class="navbar-badge badge text-bg-warning">{{ $totalNotificationsCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end" style="max-height: 400px; overflow-y: auto;">
                    <span class="dropdown-item dropdown-header">{{ $totalNotificationsCount ?? 0 }} Notificações</span>

                    {{-- Pending Reviews --}}
                    @if(isset($pendingReviews) && $pendingReviews->isNotEmpty())
                        <div class="dropdown-divider"></div>
                        <span class="dropdown-item dropdown-header text-info py-1">
                            <i class="fas fa-star-half-alt me-1"></i> Avaliações Pendentes ({{ $pendingReviewsCount }})
                        </span>
                        @foreach($pendingReviews->take(3) as $review)
                            <a href="{{ route('admin.reviews.index') }}" class="dropdown-item">
                                <div class="d-flex align-items-start gap-2">
                                    <img src="{{ $review->user->photo ? asset($review->user->photo) : asset('img/default-user.svg') }}"
                                        class="rounded-circle object-fit-cover flex-shrink-0" style="width:38px;height:38px;" alt="" loading="lazy" decoding="async">
                                    <div class="flex-grow-1">
                                        <h3 class="dropdown-item-title mb-0">
                                            {{ \Illuminate\Support\Str::limit($review->user->name ?? 'Usuário', 18) }}
                                        </h3>
                                        <p class="fs-7 text-secondary mb-0">
                                            {{ $review->rating }}/5 estrelas
                                            @if($review->reviewable)
                                                em <strong>{{ \Illuminate\Support\Str::limit($review->reviewable->title ?? '', 20) }}</strong>
                                            @endif
                                        </p>
                                        <p class="fs-7 text-secondary mb-0">
                                            <i class="far fa-clock me-1"></i>{{ $review->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                        @if($pendingReviewsCount > 3)
                            <a href="{{ route('admin.reviews.index') }}" class="dropdown-item dropdown-footer text-info">
                                Ver todas as {{ $pendingReviewsCount }} avaliações
                            </a>
                        @endif
                    @endif

                    {{-- Pending Testimonials --}}
                    @if(isset($pendingTestimonials) && $pendingTestimonials->isNotEmpty())
                        <div class="dropdown-divider"></div>
                        <span class="dropdown-item dropdown-header text-success py-1">
                            <i class="fas fa-quote-left me-1"></i> Depoimentos Pendentes ({{ $pendingTestimonialsCount }})
                        </span>
                        @foreach($pendingTestimonials->take(3) as $testimonial)
                            <a href="{{ route('admin.testimonials.index') }}" class="dropdown-item">
                                <div class="d-flex align-items-start gap-2">
                                    <img src="{{ $testimonial->user && $testimonial->user->photo ? asset($testimonial->user->photo) : asset('img/default-user.svg') }}"
                                        class="rounded-circle object-fit-cover flex-shrink-0" style="width:38px;height:38px;" alt="" loading="lazy" decoding="async">
                                    <div class="flex-grow-1">
                                        <h3 class="dropdown-item-title mb-0">
                                            {{ \Illuminate\Support\Str::limit($testimonial->author_name ?? ($testimonial->user->name ?? 'Anônimo'), 18) }}
                                        </h3>
                                        <p class="fs-7 text-secondary mb-0">
                                            {{ \Illuminate\Support\Str::limit($testimonial->content, 40) }}
                                        </p>
                                        <p class="fs-7 text-secondary mb-0">
                                            <i class="far fa-clock me-1"></i>{{ $testimonial->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                        @if($pendingTestimonialsCount > 3)
                            <a href="{{ route('admin.testimonials.index') }}" class="dropdown-item dropdown-footer text-success">
                                Ver todos os {{ $pendingTestimonialsCount }} depoimentos
                            </a>
                        @endif
                    @endif

                    {{-- Pending Connections --}}
                    @if(isset($pendingConnections) && $pendingConnections->isNotEmpty())
                        <div class="dropdown-divider"></div>
                        <span class="dropdown-item dropdown-header text-warning py-1">
                            <i class="fas fa-user-plus me-1"></i> Conexões Pendentes ({{ $pendingConnectionsCount }})
                        </span>
                        @foreach($pendingConnections as $conn)
                            <div class="dropdown-item">
                                <div class="d-flex align-items-start gap-2">
                                    <img src="{{ $conn->requester->photo ? asset($conn->requester->photo) : asset('img/default-user.svg') }}"
                                        class="rounded-circle object-fit-cover flex-shrink-0" style="width:38px;height:38px;" alt="" loading="lazy" decoding="async">
                                    <div class="flex-grow-1">
                                        <h3 class="dropdown-item-title mb-0">
                                            {{ \Illuminate\Support\Str::limit($conn->requester->name, 18) }}
                                        </h3>
                                        <p class="fs-7 mb-1">Solicitou conexão</p>
                                        <div class="text-end">
                                            <button type="button" onclick="acceptConnection({{ $conn->requester_id }})"
                                                class="btn btn-xs btn-success" title="Aceitar"><i class="fas fa-check"></i></button>
                                            <button type="button" onclick="removeConnection({{ $conn->requester_id }})"
                                                class="btn btn-xs btn-secondary" title="Recusar"><i class="fas fa-times"></i></button>
                                            <button type="button" onclick="blockConnection({{ $conn->requester_id }})"
                                                class="btn btn-xs btn-danger" title="Bloquear"><i class="fas fa-ban"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    {{-- Empty state --}}
                    @if((!isset($pendingReviews) || $pendingReviews->isEmpty()) && (!isset($pendingTestimonials) || $pendingTestimonials->isEmpty()) && (!isset($pendingConnections) || $pendingConnections->isEmpty()))
                        <div class="dropdown-divider"></div>
                        <span class="dropdown-item text-center text-secondary">Sem novas notificações</span>
                    @endif
                </div>
            </li>

            {{-- Painel Rápido (Offcanvas Bootstrap 5) --}}
            <li class="nav-item me-1">
                <a class="nav-link" href="#adminQuickPanelOffcanvas" data-bs-toggle="offcanvas" role="button" aria-controls="adminQuickPanelOffcanvas" title="Painel rápido">
                    <i class="fas fa-sliders-h"></i>
                </a>
            </li>

            {{-- Alternar Tema Dark/Light --}}
            @php($adminCurrentTheme = ($siteTheme ?? ($adminLayoutSettings['site_theme'] ?? 'light')) === 'dark' ? 'dark' : 'light')
            <li class="nav-item me-1 d-flex align-items-center">
                <form method="POST" action="{{ route('admin.settings.update') }}" id="themeToggleForm" class="m-0 p-0">
                    @csrf
                    <input type="hidden" name="current_group" value="appearance">
                    <input type="hidden" name="site_theme" id="site_theme_input" value="{{ $adminCurrentTheme }}">
                    <a class="nav-link" href="#" id="themeToggleBtn" role="button" title="Alternar tema">
                        <i class="fas {{ $adminCurrentTheme === 'dark' ? 'fa-sun text-warning' : 'fa-moon' }}"></i>
                    </a>
                </form>
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
                        <a href="{{ route('admin.profile.edit') }}" class="dropdown-item">
                            <i class="fas fa-id-card me-2 text-primary"></i> Meu Perfil
                        </a>
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
                    text: 'Este usuário não será adicionado às suas conexões.',
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
                    text: 'Ele não poderá mais solicitar conexão.',
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
