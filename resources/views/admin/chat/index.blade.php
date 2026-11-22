@extends('admin.layouts.app')

@section('title', 'Mensagens - SOMOS UNN')
@section('page_title', 'Mensagens')

@section('breadcrumb_items')
    <li class="breadcrumb-item active">Chat</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="chat-react-card shadow-sm">
                <div class="chat-react-layout">
                    <!-- Coluna da Esquerda: Lista de Conversas -->
                    <div class="chat-react-sidebar col-12 col-md-4 p-0 d-flex flex-column">
                        <div class="chat-sidebar-header">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="m-0 font-weight-bold text-slate-800">
                                    <i class="far fa-comments text-primary mr-2"></i>Conversas
                                </h5>
                                <span class="badge badge-primary">{{ $conversations->count() }}</span>
                            </div>
                            <div class="chat-search-wrap">
                                <i class="fas fa-search chat-search-icon"></i>
                                <input type="text" id="conversationSearchInput" class="form-control chat-search-input" placeholder="Buscar conversa...">
                            </div>
                        </div>

                        <div class="chat-conversations-scroll flex-grow-1" id="conversationsListContainer">
                            @if($conversations->isEmpty())
                                <div class="p-5 text-center text-muted">
                                    <i class="far fa-comment-dots fa-3x text-slate-300 mb-3 d-block"></i>
                                    <h6 class="font-weight-bold text-slate-700">Nenhuma conversa iniciada</h6>
                                    <p class="small text-muted mb-0">Quando membros ou administradores enviarem mensagens, elas aparecerão aqui.</p>
                                </div>
                            @else
                                <div class="list-group list-group-flush" id="conversationsListGroup">
                                    @foreach($conversations as $conv)
                                        @php
                                            $otherUser = $conv->users->where('id', '!=', Auth::id())->first() ?? $conv->users->first();
                                            $otherUserPhoto = $otherUser?->photo ? asset($otherUser->photo) : asset('img/default-user.svg');
                                            $otherUserName = $otherUser?->name ?? ($conv->title ?? 'Conversa');
                                            $lastMsg = $conv->messages->first();
                                        @endphp
                                        <a href="{{ route('admin.chat.show', $conv->id) }}"
                                            data-conversation-id="{{ $conv->id }}"
                                            data-name="{{ strtolower($otherUserName) }}"
                                            class="chat-conv-item">
                                            <div class="position-relative mr-3 flex-shrink-0">
                                                <img src="{{ $otherUserPhoto }}" alt="{{ $otherUserName }}" class="chat-conv-avatar" loading="lazy" decoding="async">
                                                <span class="chat-status-dot online"></span>
                                            </div>
                                            <div class="chat-conv-details flex-grow-1 min-w-0">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <h6 class="chat-conv-name text-truncate m-0">{{ $otherUserName }}</h6>
                                                    @if($lastMsg)
                                                        <span class="chat-conv-time">{{ $lastMsg->created_at->format('H:i') }}</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <p class="chat-conv-preview text-truncate m-0">
                                                        {{ $lastMsg ? $lastMsg->body : 'Clique para ver a conversa' }}
                                                    </p>
                                                    @if($conv->unread_count > 0)
                                                        <span class="badge badge-primary chat-unread-pill">{{ $conv->unread_count }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Coluna da Direita: Placeholder de Boas-Vindas no Desktop -->
                    <div class="chat-react-main col-md-8 d-none d-md-flex align-items-center justify-content-center flex-column text-center p-5">
                        <div class="chat-welcome-box">
                            <div class="chat-welcome-icon-wrap mb-4">
                                <i class="fas fa-comments text-primary"></i>
                            </div>
                            <h4 class="font-weight-bold text-slate-800 mb-2">Central de Mensagens SOMOS UNN</h4>
                            <p class="text-muted max-w-md mx-auto mb-4" style="max-width: 420px; font-size: 0.9375rem;">
                                Selecione uma conversa na barra lateral para ler o histórico, responder membros ou iniciar um atendimento direto.
                            </p>
                            <span class="badge badge-light border px-3 py-2 text-muted" style="font-size: 0.8125rem;">
                                <i class="fas fa-lock mr-1.5 text-success"></i> Comunicação direta em tempo real
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .chat-react-card {
            background-color: #ffffff;
            border-radius: 1.25rem;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            height: calc(100vh - 175px);
            min-height: 600px;
            display: flex;
            flex-direction: column;
        }

        .dark-mode .chat-react-card {
            background-color: #0f172a;
            border-color: #334155;
        }

        .chat-react-layout {
            display: flex;
            flex: 1;
            height: 100%;
            overflow: hidden;
        }

        .chat-react-sidebar {
            width: 360px;
            min-width: 320px;
            border-right: 1px solid #e2e8f0;
            background-color: #f8fafc;
            height: 100%;
        }

        .dark-mode .chat-react-sidebar {
            background-color: #0b1120;
            border-right-color: #1e293b;
        }

        .chat-sidebar-header {
            padding: 1.15rem 1rem 0.85rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            background-color: #ffffff;
        }

        .dark-mode .chat-sidebar-header {
            background-color: #0f172a;
            border-bottom-color: #1e293b;
        }

        .chat-search-wrap {
            position: relative;
        }

        .chat-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.8125rem;
            pointer-events: none;
        }

        .chat-search-input {
            padding-left: 34px !important;
            height: 38px !important;
            min-height: 38px !important;
            border-radius: 9999px !important;
            font-size: 0.8125rem !important;
            background-color: #f1f5f9 !important;
            border-color: transparent !important;
        }

        .chat-search-input:focus {
            background-color: #ffffff !important;
            border-color: #1F5EDB !important;
        }

        .dark-mode .chat-search-input {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }

        .chat-conversations-scroll {
            overflow-y: auto;
            height: 100%;
        }

        .chat-conv-item {
            display: flex;
            align-items: center;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none !important;
            color: inherit;
            transition: all 0.15s ease;
            border-left: 3px solid transparent;
        }

        .chat-conv-item:hover {
            background-color: #f1f5f9;
        }

        .chat-conv-item.active {
            background-color: rgba(31, 94, 219, 0.08);
            border-left-color: #1F5EDB;
        }

        .dark-mode .chat-conv-item {
            border-bottom-color: #1e293b;
            color: #cbd5e1;
        }

        .dark-mode .chat-conv-item:hover {
            background-color: #1e293b;
        }

        .chat-conv-avatar {
            width: 44px;
            height: 44px;
            border-radius: 9999px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }

        .dark-mode .chat-conv-avatar {
            border-color: #334155;
        }

        .chat-status-dot {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 12px;
            height: 12px;
            border-radius: 9999px;
            border: 2px solid #ffffff;
        }

        .chat-status-dot.online {
            background-color: #10b981;
        }

        .dark-mode .chat-status-dot {
            border-color: #0f172a;
        }

        .chat-conv-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
        }

        .dark-mode .chat-conv-name {
            color: #f8fafc;
        }

        .chat-conv-time {
            font-size: 0.6875rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .chat-conv-preview {
            font-size: 0.78125rem;
            color: #64748b;
            max-width: 200px;
        }

        .dark-mode .chat-conv-preview {
            color: #94a3b8;
        }

        .chat-unread-pill {
            font-size: 0.6875rem !important;
            padding: 0.25em 0.65em !important;
            border-radius: 9999px !important;
            font-weight: 700 !important;
        }

        .chat-react-main {
            background-color: #f8fafc;
            height: 100%;
        }

        .dark-mode .chat-react-main {
            background-color: #070c18;
        }

        .chat-welcome-icon-wrap {
            width: 76px;
            height: 76px;
            border-radius: 1.25rem;
            background: rgba(31, 94, 219, 0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
        }

        @media (max-width: 767.98px) {
            .chat-react-sidebar {
                width: 100%;
                max-width: 100%;
                border-right: none;
            }
            .chat-react-card {
                height: calc(100vh - 140px);
                min-height: 520px;
                border-radius: 1rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Filtragem de conversas em tempo real
            $('#conversationSearchInput').on('input', function () {
                const term = $(this).val().toLowerCase().trim();
                $('.chat-conv-item').each(function () {
                    const name = $(this).data('name') || '';
                    if (name.includes(term)) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });
            });

            // Polling de contadores de conversas não lidas
            let isTabActive = true;
            document.addEventListener('visibilitychange', () => { isTabActive = !document.hidden; });

            setInterval(() => {
                if (!isTabActive) return;
                fetch('{{ route("admin.chat.list") }}')
                    .then(r => r.json())
                    .then(conversations => {
                        conversations.forEach(conv => {
                            const link = document.querySelector(`[data-conversation-id="${conv.id}"]`);
                            if (link) {
                                let badge = link.querySelector('.chat-unread-pill');
                                if (conv.unread_count > 0) {
                                    if (!badge) {
                                        badge = document.createElement('span');
                                        badge.className = 'badge badge-primary chat-unread-pill';
                                        link.querySelector('.d-flex.justify-content-between:last-child').appendChild(badge);
                                    }
                                    badge.textContent = conv.unread_count;
                                } else if (badge) {
                                    badge.remove();
                                }
                            }
                        });
                    })
                    .catch(() => {});
            }, 6000);
        });
    </script>
@endpush
