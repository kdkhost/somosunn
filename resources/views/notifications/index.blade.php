@extends('layouts.app')

@section('title', 'Notificações - Somos UNN')

@section('content')
    <div class="min-h-screen bg-slate-50 pb-20">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 py-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Notificações</h1>
                        <p class="text-gray-500 mt-1">Fique por dentro das atualizações da sua rede.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button id="mark-all-read"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-full transition">
                            Marcar tudo como lido
                        </button>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="flex items-center gap-2 mt-8 overflow-x-auto pb-2 no-scrollbar">
                    <a href="{{ route('notifications.index') }}"
                        class="px-5 py-2 rounded-full text-sm font-medium transition {{ !request('filter') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300' }}">
                        Todas
                    </a>
                    <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
                        class="px-5 py-2 rounded-full text-sm font-medium transition {{ request('filter') === 'unread' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300' }}">
                        Não lidas
                    </a>
                </div>
            </div>
        </div>

        <!-- Lista de Notificações -->
        <div class="max-w-4xl mx-auto px-4 mt-8">
            <div id="notifications-container" class="space-y-4">
                @include('notifications.partials.list', ['notifications' => $notifications])
            </div>

            @if($notifications->hasMorePages())
                <div class="mt-8 text-center">
                    <button id="load-more" data-page="{{ $notifications->currentPage() + 1 }}"
                        class="bg-white border border-gray-200 text-gray-700 px-8 py-3 rounded-xl font-medium hover:bg-gray-50 transition shadow-sm">
                        Carregar mais notificações
                    </button>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('notifications-container');
                const loadMoreBtn = document.getElementById('load-more');
                const markAllReadBtn = document.getElementById('mark-all-read');

                // Marcar todas como lidas
                if (markAllReadBtn) {
                    markAllReadBtn.addEventListener('click', function () {
                        fetch('{{ route('notifications.markRead') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    document.querySelectorAll('.is-new-notification').forEach(el => {
                                        el.classList.remove('is-new-notification', 'bg-blue-50/50', 'border-blue-100');
                                        el.querySelector('.unread-dot')?.remove();
                                    });
                                    toastr.success('Todas as notificações marcadas como lidas');
                                }
                            });
                    });
                }

                // Marcar uma como lida e redirecionar (se aplicável)
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
                                'Accept': 'application/json'
                            }
                        });
                    }

                    // Ações de cada notificação podem ser implementadas aqui se necessário
                });

                // Excluir notificação
                container.addEventListener('click', function (e) {
                    const deleteBtn = e.target.closest('.delete-notification');
                    if (!deleteBtn) return;

                    const id = deleteBtn.getAttribute('data-id');
                    const row = document.querySelector(`[data-notification-id="${id}"]`);

                    if (confirm('Deseja remover esta notificação?')) {
                        fetch(`/notificacoes/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    row.style.opacity = '0';
                                    row.style.transform = 'translateX(20px)';
                                    setTimeout(() => row.remove(), 300);
                                }
                            });
                    }
                });

                // Paginação AJAX
                if (loadMoreBtn) {
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
                                    this.setAttribute('data-page', parseInt(page) + 1);
                                    this.disabled = false;
                                    this.textContent = 'Carregando mais notificações...';
                                }
                            });
                    });
                }
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .is-new-notification {
                position: relative;
            }

            .unread-dot {
                position: absolute;
                top: 1.25rem;
                right: 1.25rem;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background-color: #2563eb;
                box-shadow: 0 0 0 2px #fff;
            }
        </style>
    @endpush
@endsection