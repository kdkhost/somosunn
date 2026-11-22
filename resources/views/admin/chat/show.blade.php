@extends('admin.layouts.app')

@section('title', 'Chat com ' . ($conversation->users->where('id', '!=', Auth::id())->first()?->name ?? 'Usuário') . ' - SOMOS UNN')
@section('page_title', 'Mensagens')

@section('breadcrumb_items')
    <li class="breadcrumb-item"><a href="{{ route('admin.chat.index') }}">Chat</a></li>
    <li class="breadcrumb-item active">Conversa</li>
@endsection

@section('content')
    @php
        $chatOtherUser = $conversation->users->where('id', '!=', Auth::id())->first() ?? $conversation->users->first();
        $chatOtherUserPhoto = $chatOtherUser?->photo ? asset($chatOtherUser->photo) : asset('img/default-user.svg');
        $chatOtherUserName = $chatOtherUser?->name ?? ($conversation->title ?? 'Conversa');
        $chatOtherUserRole = $chatOtherUser?->role === 'superadmin' ? 'Superadmin' : ($chatOtherUser?->role === 'admin' ? 'Administrador' : 'Membro UNN');
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="chat-react-card shadow-sm">
                <div class="chat-react-layout">
                    <!-- Coluna da Esquerda: Lista de Conversas (Escondida no Mobile na tela de conversa) -->
                    <div class="chat-react-sidebar d-none d-md-flex flex-column">
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
                            <div class="list-group list-group-flush" id="conversationsListGroup">
                                @forelse($conversations as $conv)
                                    @php
                                        $cUser = $conv->users->where('id', '!=', Auth::id())->first() ?? $conv->users->first();
                                        $cName = $cUser?->name ?? ($conv->title ?? 'Conversa');
                                        $cPhoto = $cUser?->photo ? asset($cUser->photo) : asset('img/default-user.svg');
                                        $isActive = $conversation->id == $conv->id;
                                        $lastMsg = $conv->messages->first();
                                    @endphp
                                    <a href="{{ route('admin.chat.show', $conv->id) }}"
                                        class="chat-conv-item {{ $isActive ? 'active' : '' }}"
                                        data-name="{{ strtolower($cName) }}">
                                        <div class="position-relative mr-3 flex-shrink-0">
                                            <img src="{{ $cPhoto }}" class="chat-conv-avatar" alt="{{ $cName }}" loading="lazy" decoding="async">
                                            <span class="chat-status-dot online"></span>
                                        </div>
                                        <div class="chat-conv-details flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <h6 class="chat-conv-name text-truncate m-0">{{ $cName }}</h6>
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
                                @empty
                                    <div class="p-4 text-center text-muted">
                                        <p class="small mb-0">Nenhuma conversa encontrada</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Coluna da Direita: Área de Mensagens -->
                    <div class="chat-react-main flex-grow-1 d-flex flex-column">
                        <!-- Topo do Chat Ativo -->
                        <div class="chat-main-header">
                            <div class="d-flex align-items-center">
                                <a href="{{ route('admin.chat.index') }}" class="btn btn-sm btn-outline-secondary d-md-none mr-2 rounded-circle" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Voltar às conversas">
                                    <i class="fas fa-arrow-left"></i>
                                </a>
                                <div class="position-relative mr-3 flex-shrink-0">
                                    <img src="{{ $chatOtherUserPhoto }}" class="chat-header-avatar" alt="{{ $chatOtherUserName }}" loading="lazy" decoding="async">
                                    <span class="chat-status-dot online"></span>
                                </div>
                                <div>
                                    <h5 class="chat-header-title m-0">{{ $chatOtherUserName }}</h5>
                                    <div class="d-flex align-items-center gap-2 mt-0.5">
                                        <span class="badge badge-light border text-muted" style="font-size: 0.6875rem;">{{ $chatOtherUserRole }}</span>
                                        <span class="chat-online-badge text-success"><i class="fas fa-circle mr-1" style="font-size: 0.5rem;"></i>Online</span>
                                    </div>
                                </div>
                            </div>
                            <div class="chat-header-actions d-flex align-items-center" style="gap: 8px;">
                                @if($chatOtherUser && Route::has('admin.users.edit'))
                                    <a href="{{ route('admin.users.edit', $chatOtherUser->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 d-none d-sm-inline-flex align-items-center" title="Ver Cadastro do Usuário">
                                        <i class="fas fa-user-circle mr-1.5"></i> Perfil
                                    </a>
                                @endif
                                <button type="button" class="btn btn-sm btn-light rounded-circle border" style="width: 36px; height: 36px; padding: 0;" onclick="refreshMessages()" title="Atualizar mensagens">
                                    <i class="fas fa-sync-alt text-muted" id="refreshIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Área de Mensagens (Scroll) -->
                        <div class="chat-messages-body flex-grow-1" id="messages-container">
                            <div class="chat-messages-content">
                                <div class="chat-date-separator">
                                    <span>Conversa segura criptografada</span>
                                </div>

                                @foreach($messages->reverse() as $msg)
                                    @php
                                        $isMe = $msg->user_id == Auth::id();
                                        $msgTime = $msg->created_at->format('H:i');
                                        $msgDate = $msg->created_at->format('d/m/Y');
                                    @endphp
                                    <div class="chat-bubble-row {{ $isMe ? 'outgoing' : 'incoming' }}">
                                        @if(!$isMe)
                                            <img src="{{ $chatOtherUserPhoto }}" class="chat-bubble-avatar flex-shrink-0" alt="{{ $chatOtherUserName }}" loading="lazy" decoding="async">
                                        @endif
                                        <div class="chat-bubble-wrap">
                                            <div class="chat-bubble {{ $isMe ? 'bubble-me' : 'bubble-other' }}">
                                                <div class="chat-bubble-text">
                                                    {!! nl2br(e($msg->body)) !!}
                                                </div>
                                                <div class="chat-bubble-meta">
                                                    <span class="chat-bubble-time">{{ $msgTime }}</span>
                                                    @if($isMe)
                                                        <i class="fas fa-check-double chat-bubble-check {{ $msg->read_at ? 'text-cyan' : '' }}"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Barra Inferior de Envio de Mensagem -->
                        <div class="chat-input-bar">
                            <form id="chat-form" class="m-0 w-100">
                                <div class="input-group chat-input-group">
                                    <input type="text" id="message-input" name="message" class="form-control chat-message-input" placeholder="Escreva uma mensagem para {{ $chatOtherUserName }}..." autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="submit" id="btnSendMessage" class="btn btn-primary chat-send-btn">
                                            <span class="d-none d-sm-inline mr-1">Enviar</span>
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* ═══════════════════════════════════════════════════════════════════════
           CHAT SAAS MODERNO ESTILO REACT (DESIGN SYSTEM SOMOS UNN)
           ═══════════════════════════════════════════════════════════════════════ */
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

        /* ─── Coluna Esquerda: Lista de Conversas ─── */
        .chat-react-sidebar {
            width: 350px;
            min-width: 320px;
            max-width: 380px;
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

        .dark-mode .chat-conv-item.active {
            background-color: rgba(96, 165, 250, 0.12);
            border-left-color: #60a5fa;
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
            max-width: 180px;
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

        /* ─── Coluna Direita: Área de Mensagens ─── */
        .chat-react-main {
            background-color: #f8fafc;
            height: 100%;
        }

        .dark-mode .chat-react-main {
            background-color: #070c18;
        }

        .chat-main-header {
            padding: 0.85rem 1.25rem;
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 66px;
        }

        .dark-mode .chat-main-header {
            background-color: #0f172a;
            border-bottom-color: #1e293b;
        }

        .chat-header-avatar {
            width: 44px;
            height: 44px;
            border-radius: 9999px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }

        .chat-header-title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .dark-mode .chat-header-title {
            color: #f8fafc;
        }

        .chat-online-badge {
            font-size: 0.6875rem;
            font-weight: 600;
        }

        /* ─── Mensagens (Scroll & Balões) ─── */
        .chat-messages-body {
            overflow-y: auto;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
        }

        .chat-messages-content {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .chat-date-separator {
            text-align: center;
            margin: 0.5rem 0 1rem 0;
            position: relative;
        }

        .chat-date-separator span {
            background-color: #e2e8f0;
            color: #475569;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            letter-spacing: 0.02em;
        }

        .dark-mode .chat-date-separator span {
            background-color: #1e293b;
            color: #94a3b8;
        }

        .chat-bubble-row {
            display: flex;
            align-items: flex-end;
            gap: 0.5rem;
            width: 100%;
        }

        .chat-bubble-row.outgoing {
            justify-content: flex-end;
        }

        .chat-bubble-row.incoming {
            justify-content: flex-start;
        }

        .chat-bubble-avatar {
            width: 32px;
            height: 32px;
            border-radius: 9999px;
            object-fit: cover;
            margin-bottom: 2px;
        }

        .chat-bubble-wrap {
            max-width: 75%;
            display: flex;
            flex-direction: column;
        }

        .chat-bubble {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            line-height: 1.45;
            position: relative;
            word-break: break-word;
        }

        /* Balão de Mensagem Enviada (Eu) */
        .chat-bubble.bubble-me {
            background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%);
            color: #ffffff;
            border-radius: 1.15rem 1.15rem 0.25rem 1.15rem;
            box-shadow: 0 4px 12px rgba(31, 94, 219, 0.22);
        }

        /* Balão de Mensagem Recebida (Outro) */
        .chat-bubble.bubble-other {
            background-color: #ffffff;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            border-radius: 1.15rem 1.15rem 1.15rem 0.25rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        }

        .dark-mode .chat-bubble.bubble-other {
            background-color: #1e293b;
            color: #f1f5f9;
            border-color: #334155;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .chat-bubble-meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.25rem;
            margin-top: 0.35rem;
            font-size: 0.65rem;
        }

        .bubble-me .chat-bubble-time {
            color: rgba(255, 255, 255, 0.85);
        }

        .bubble-other .chat-bubble-time {
            color: #94a3b8;
        }

        .chat-bubble-check {
            font-size: 0.625rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .text-cyan {
            color: #67e8f9 !important;
        }

        /* ─── Barra Inferior de Envio ─── */
        .chat-input-bar {
            padding: 0.85rem 1.25rem;
            background-color: #ffffff;
            border-top: 1px solid #e2e8f0;
        }

        .dark-mode .chat-input-bar {
            background-color: #0f172a;
            border-top-color: #1e293b;
        }

        .chat-input-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .chat-message-input {
            height: 44px !important;
            min-height: 44px !important;
            border-radius: 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            padding: 0.65rem 1rem !important;
            font-size: 0.875rem !important;
            background-color: #f8fafc !important;
            transition: all 0.15s ease !important;
        }

        .chat-message-input:focus {
            background-color: #ffffff !important;
            border-color: #1F5EDB !important;
            box-shadow: 0 0 0 3px rgba(31, 94, 219, 0.18) !important;
        }

        .dark-mode .chat-message-input {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        .dark-mode .chat-message-input:focus {
            background-color: #0f172a !important;
            border-color: #60a5fa !important;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2) !important;
        }

        .chat-send-btn {
            height: 44px !important;
            border-radius: 0.75rem !important;
            padding: 0 1.25rem !important;
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.4rem !important;
            background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%) !important;
            box-shadow: 0 2px 8px rgba(31, 94, 219, 0.28) !important;
            border: none !important;
            transition: all 0.15s ease !important;
        }

        .chat-send-btn:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 14px rgba(31, 94, 219, 0.38) !important;
        }

        @media (max-width: 767.98px) {
            .chat-react-card {
                height: calc(100vh - 140px);
                min-height: 520px;
                border-radius: 1rem;
            }
            .chat-bubble-wrap {
                max-width: 86%;
            }
            .chat-main-header {
                padding: 0.65rem 0.85rem;
            }
            .chat-input-bar {
                padding: 0.65rem 0.85rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            const container = document.getElementById('messages-container');
            function scrollToBottom() {
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }
            scrollToBottom();

            // Busca de conversas na lista lateral
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

            // Envio de Mensagem com Optimistic UI
            $('#chat-form').on('submit', function (e) {
                e.preventDefault();
                const input = $('#message-input');
                const body = input.val().trim();
                if (!body) return;

                const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const safeBody = body.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br>");

                const bubbleHtml = `
                    <div class="chat-bubble-row outgoing">
                        <div class="chat-bubble-wrap">
                            <div class="chat-bubble bubble-me">
                                <div class="chat-bubble-text">${safeBody}</div>
                                <div class="chat-bubble-meta">
                                    <span class="chat-bubble-time">${time}</span>
                                    <i class="fas fa-check chat-bubble-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('.chat-messages-content').append(bubbleHtml);
                scrollToBottom();
                input.val('').focus();

                fetch('{{ route("admin.chat.message.store", $conversation->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ body: body })
                }).catch(err => {
                    console.error('Erro ao enviar mensagem:', err);
                });
            });

            // Enviar ao pressionar Enter sem Shift
            $('#message-input').on('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    $('#chat-form').submit();
                }
            });

            // Função para recarregar mensagens
            window.refreshMessages = function () {
                const icon = $('#refreshIcon');
                icon.addClass('fa-spin');

                fetch('{{ route("admin.chat.messages", $conversation->id) }}')
                    .then(r => r.json())
                    .then(messages => {
                        // Se houver novas mensagens, podemos atualizar
                        icon.removeClass('fa-spin');
                    })
                    .catch(() => {
                        icon.removeClass('fa-spin');
                    });
            };

            // Polling de novas mensagens
            let isTabActive = true;
            document.addEventListener('visibilitychange', () => { isTabActive = !document.hidden; });

            setInterval(() => {
                if (!isTabActive) return;
                fetch('{{ route("admin.chat.messages", $conversation->id) }}')
                    .then(r => r.json())
                    .then(messages => {
                        // Polling silencioso
                    })
                    .catch(() => {});
            }, 5000);
        });
    </script>
@endpush
