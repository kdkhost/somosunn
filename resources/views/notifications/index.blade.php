@extends('layouts.app')

@section('title', 'Notificações - Somos UNN')

@section('content')
    <div class="bg-gray-100 min-h-screen pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                <!-- Sidebar Left -->
                <div class="hidden md:block md:col-span-3">
                    <div class="bg-white rounded-lg shadow p-4 sticky top-24">
                        @auth
                            <div class="flex items-center gap-3 mb-6">
                                <div class="rounded-full w-10 h-10 overflow-hidden flex-shrink-0">
                                    <img src="{{ Auth::user()->profile_photo_url }}" alt="Avatar" class="w-10 h-10 object-cover"
                                        onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">Membro</p>
                                </div>
                            </div>
                            <nav class="space-y-2 mb-4">
                                <a href="{{ route('social.feed') }}"
                                    class="w-full flex items-center gap-2 text-gray-600 hover:text-blue-600 p-2 rounded transition">
                                    <i class="fas fa-newspaper w-6"></i>
                                    <span>Feed</span>
                                </a>
                                <a href="{{ route('notifications.index') }}"
                                    class="w-full flex items-center gap-2 text-blue-600 font-medium p-2 bg-blue-50 rounded transition">
                                    <i class="fas fa-bell w-6"></i>
                                    <span>Notificações</span>
                                </a>
                                <a href="{{ route('chat.index') }}"
                                    class="w-full flex items-center gap-2 text-gray-600 hover:text-blue-600 p-2 rounded transition">
                                    <i class="fas fa-comments w-6"></i>
                                    <span>Mensagens</span>
                                </a>
                            </nav>
                        @endauth
                    </div>
                </div>

                <!-- Centro (Notificações) -->
                <div class="md:col-span-6 space-y-6">
                    <div class="bg-white rounded-lg shadow">
                        <div
                            class="p-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-bell text-blue-600"></i> Notificações
                                </h1>
                            </div>
                            <div class="flex items-center gap-2">
                                <button id="mark-all-read"
                                    class="text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full transition">
                                    Marcar tudo como lido
                                </button>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="p-4 bg-gray-50 border-b border-gray-100 overflow-x-auto">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('notifications.index') }}"
                                    class="px-4 py-1.5 rounded-full text-xs font-medium transition {{ !request('filter') ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300' }}">
                                    Todas
                                </a>
                                <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
                                    class="px-4 py-1.5 rounded-full text-xs font-medium transition {{ request('filter') === 'unread' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300' }}">
                                    Não lidas
                                </a>
                            </div>
                        </div>

                        <!-- Lista -->
                        <div id="notifications-container" class="divide-y divide-gray-100">
                            @include('notifications.partials.list', ['notifications' => $notifications])
                        </div>

                        @if($notifications->hasMorePages())
                            <div class="p-4 text-center border-t border-gray-100">
                                <button id="load-more" data-page="{{ $notifications->currentPage() + 1 }}"
                                    class="text-sm text-blue-600 font-medium hover:underline">
                                    Carregar mais
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar Right (Recomendações e Ads) -->
                <div class="hidden md:block md:col-span-3 space-y-6">
                    <!-- Solicitações -->
                    <div class="bg-white rounded-lg shadow p-4 sticky top-24">
                        @if(isset($pendingRequests) && $pendingRequests->isNotEmpty())
                            <div class="mb-6 pb-6 border-b border-gray-100">
                                <h3 class="font-bold text-gray-900 mb-4 flex items-center justify-between">
                                    <span>Solicitações</span>
                                    <span
                                        class="bg-blue-100 text-blue-600 text-[10px] px-2 py-0.5 rounded-full">{{ $pendingRequests->count() }}</span>
                                </h3>
                                <div class="space-y-4">
                                    @foreach($pendingRequests as $request)
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3">
                                                <div class="rounded-full w-8 h-8 overflow-hidden flex-shrink-0">
                                                    <img src="{{ $request->requester->profile_photo_url }}" alt="Avatar"
                                                        class="w-8 h-8 object-cover"
                                                        onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                                </div>
                                                <div class="overflow-hidden">
                                                    <p class="text-xs font-semibold text-gray-800 truncate"
                                                        title="{{ $request->requester->name }}">
                                                        {{Str::limit($request->requester->name, 15)}}</p>
                                                </div>
                                            </div>
                                            <div class="flex gap-1">
                                                <button type="button" class="text-blue-600 hover:text-blue-700"
                                                    onclick="acceptInvite({{ $request->requester_id }})" title="Aceitar">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                                <button type="button" class="text-red-500 hover:text-red-600"
                                                    onclick="refuseInvite({{ $request->requester_id }})" title="Recusar">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Recomendados -->
                        <h3 class="font-bold text-gray-900 mb-4">Recomendados</h3>
                        <div class="space-y-4">
                            @if(!empty($recommendedUsers) && $recommendedUsers->isNotEmpty())
                                @php
                                    $connectionMap = $connectionMap ?? [];
                                    $authUserId = auth()->id();
                                @endphp
                                @foreach($recommendedUsers as $user)
                                    @php
                                        $connection = $connectionMap[$user->id] ?? null;
                                        $isPending = $connection && $connection->status === 'pending';
                                        $isConnected = $connection && $connection->status === 'accepted';
                                        $isRequester = $connection && $authUserId && $connection->requester_id === $authUserId;
                                    @endphp
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <a class="rounded-full w-8 h-8 overflow-hidden flex-shrink-0"
                                                href="{{ route('social.profile', $user->id) }}">
                                                <img src="{{ $user->profile_photo_url }}" alt="Avatar" class="w-8 h-8 object-cover"
                                                    onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                            </a>
                                            <div class="overflow-hidden">
                                                <a href="{{ route('social.profile', $user->id) }}"
                                                    class="text-xs font-semibold text-gray-800 hover:text-blue-600 truncate block"
                                                    title="{{$user->name}}">
                                                    {{ Str::limit($user->name, 15) }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            @if($isConnected)
                                                <span class="text-[10px] text-gray-400">Conectado</span>
                                            @elseif($isPending && $isRequester)
                                                <button type="button" class="text-[10px] text-red-600 hover:text-red-700 font-medium"
                                                    onclick="cancelInvite({{ $user->id }})">
                                                    Cancelar
                                                </button>
                                            @elseif($isPending)
                                                <button type="button"
                                                    class="text-[10px] text-green-600 hover:text-green-700 font-medium"
                                                    onclick="acceptInvite({{ $user->id }})">
                                                    Aceitar
                                                </button>
                                            @else
                                                <button type="button" class="text-[10px] text-blue-600 hover:text-blue-700 font-medium"
                                                    onclick="requestInvite({{ $user->id }})">
                                                    Conectar
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-xs text-gray-500">Sem recomendações.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Ads -->
                    @if(isset($adsEnabled) && $adsEnabled)
                        <div class="bg-white rounded-lg shadow p-4">
                            <span class="text-xs text-gray-400 uppercase font-bold tracking-wider block mb-2">Publicidade</span>
                            @if(isset($adsensePublisherId) && !empty($adsensePublisherId) && isset($adsenseSlotId) && !empty($adsenseSlotId))
                                <ins class="adsbygoogle" style="display:block" data-ad-client="{{ $adsensePublisherId }}"
                                    data-ad-slot="{{ $adsenseSlotId }}" data-ad-format="{{ $adsenseFormat ?? 'auto' }}"
                                    data-full-width-responsive="true"></ins>
                                <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
                            @elseif(isset($adsCode) && !empty($adsCode))
                                {!! $adsCode !!}
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const csrfToken = '{{ csrf_token() }}';

            async function confirmAction(options = {}) {
                if (typeof Swal === 'undefined') {
                    if (window.toastr && typeof window.toastr.warning === 'function') {
                        window.toastr.warning('Confirmação indisponível no momento.');
                    } else {
                        console.warn('SweetAlert2 não disponível para confirmação.');
                    }
                    return false;
                }

                const result = await Swal.fire({
                    icon: options.icon || 'question',
                    title: options.title || 'Confirmar',
                    text: options.text || 'Deseja continuar?',
                    showCancelButton: true,
                    confirmButtonText: options.confirmButtonText || 'Confirmar',
                    cancelButtonText: options.cancelButtonText || 'Cancelar',
                    reverseButtons: true
                });

                return !!result.isConfirmed;
            }

            // Sidebar Functions (same as feed)
            function requestInvite(userId) {
                fetch(`/connect/${userId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.warning(data.message);
                    }
                });
            }

            async function cancelInvite(userId) {
                const confirmed = await confirmAction({
                    title: 'Cancelar solicitação',
                    text: 'Cancelar solicitação?',
                    confirmButtonText: 'Cancelar solicitação'
                });
                if (!confirmed) return;

                fetch(`/connection/remove/${userId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        setTimeout(() => location.reload(), 1000);
                    }
                });
            }

            function acceptInvite(userId) {
                fetch(`/connection/accept/${userId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        setTimeout(() => location.reload(), 1000);
                    }
                });
            }

            async function refuseInvite(userId) {
                const confirmed = await confirmAction({
                    title: 'Recusar solicitação',
                    text: 'Recusar solicitação?',
                    confirmButtonText: 'Recusar'
                });
                if (!confirmed) return;

                fetch(`/connection/remove/${userId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        setTimeout(() => location.reload(), 1000);
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (window.__notificationsPageInit) {
                    return;
                }
                window.__notificationsPageInit = true;

                const container = document.getElementById('notifications-container');
                const loadMoreBtn = document.getElementById('load-more');
                const markAllReadBtn = document.getElementById('mark-all-read');

                // Marcar todas como lidas
                if (markAllReadBtn) {
                    markAllReadBtn.addEventListener('click', function (event) {
                        event.preventDefault();
                        markAllReadBtn.disabled = true;

                        fetch('{{ route('notifications.markRead') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(r => r.ok ? r.json() : Promise.reject(r))
                            .then(data => {
                                if (data.success) {
                                    document.querySelectorAll('.is-new-notification').forEach(el => {
                                        el.classList.remove('is-new-notification', 'bg-blue-50/50', 'border-blue-100');
                                        el.setAttribute('data-read', 'true');
                                        el.querySelector('.unread-dot')?.remove();
                                    });
                                    toastr.success('Todas as notificacoes marcadas como lidas');
                                } else {
                                    toastr.error('Nao foi possivel marcar as notificacoes.');
                                }
                            })
                            .catch(() => {
                                toastr.error('Nao foi possivel marcar as notificacoes como lidas.');
                            })
                            .finally(() => {
                                markAllReadBtn.disabled = false;
                            });
                    });
                }

                // Marcar uma como lida
                if (container) {
                    container.addEventListener('click', function (e) {
                        const row = e.target.closest('[data-notification-id]');
                        if (!row) return;

                        const id = row.getAttribute('data-notification-id');
                        const isRead = row.getAttribute('data-read') === 'true';

                        if (!isRead && !e.target.closest('button')) {
                            fetch(`/notificacoes/read/${id}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            row.setAttribute('data-read', 'true');
                            row.classList.remove('is-new-notification', 'bg-blue-50/50', 'border-blue-100');
                            row.querySelector('.unread-dot')?.remove();
                        }
                    });
                }

                // Excluir notificação
                if (container) {
                    container.addEventListener('click', async function (e) {
                        const deleteBtn = e.target.closest('.delete-notification');
                        if (!deleteBtn) return;

                        const id = deleteBtn.getAttribute('data-id');
                        const row = document.querySelector(`[data-notification-id="${id}"]`);

                        const confirmed = await confirmAction({
                            title: 'Remover notificação',
                            text: 'Deseja remover esta notificação?',
                            confirmButtonText: 'Remover',
                            icon: 'warning'
                        });
                        if (!confirmed) return;

                        fetch(`/notificacoes/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success && row) {
                                    row.style.opacity = '0';
                                    row.style.transform = 'translateX(20px)';
                                    setTimeout(() => row.remove(), 300);
                                }
                            });
                    });
                }

                // Paginação AJAX
                if (loadMoreBtn && container) {
                    loadMoreBtn.addEventListener('click', function () {
                        const page = this.getAttribute('data-page');
                        const url = new URL(window.location.href);
                        url.searchParams.set('page', page);

                        this.disabled = true;
                        this.textContent = 'Carregando...';

                        fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(r => r.text())
                            .then(html => {
                                if (html.trim() === '') {
                                    loadMoreBtn.remove();
                                } else {
                                    container.insertAdjacentHTML('beforeend', html);
                                    this.setAttribute('data-page', parseInt(page, 10) + 1);
                                    this.disabled = false;
                                    this.textContent = 'Carregar mais';
                                }
                            })
                            .catch(() => {
                                this.disabled = false;
                                this.textContent = 'Carregar mais';
                                toastr.error('Nao foi possivel carregar mais notificacoes.');
                            });
                    });
                }
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .is-new-notification {
                position: relative;
            }

            .unread-dot {
                position: absolute;
                top: 50%;
                right: 1.25rem;
                transform: translateY(-50%);
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background-color: #2563eb;
            }
        </style>
    @endpush
@endsection
