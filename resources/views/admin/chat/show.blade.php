@extends('admin.layouts.app')

@section('title', 'Chat - ' . ($conversation->users->where('id', '!=', Auth::id())->first()?->name ?? 'Conversa') . ' - SOMOS UNN')
@section('page_title', 'Chat')

@section('breadcrumb_items')
    <li class="breadcrumb-item"><a href="{{ route('admin.chat.index') }}">Chat</a></li>
    <li class="breadcrumb-item active">{{ $conversation->users->where('id', '!=', Auth::id())->first()?->name ?? 'Conversa' }}</li>
@endsection

@section('content')
    @php
        $chatOtherUser = $conversation->users->where('id', '!=', Auth::id())->first() ?? $conversation->users->first();
        $chatOtherUserPhoto = $chatOtherUser?->photo ? asset($chatOtherUser->photo) : asset('img/default-user.svg');
        $chatOtherUserName = $chatOtherUser?->name ?? ($conversation->title ?? 'Conversa');
        $chatOtherUserRole = $chatOtherUser?->role === 'superadmin' ? 'Superadmin' : ($chatOtherUser?->role === 'admin' ? 'Administrador' : 'Membro');
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm direct-chat direct-chat-primary h-100" style="min-height: 620px;">
                {{-- Header Padrão do Card AdminLTE --}}
                <div class="card-header border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('admin.chat.index') }}" class="btn btn-sm btn-outline-secondary d-md-none mr-2 rounded-circle" style="width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Voltar às conversas">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <img src="{{ $chatOtherUserPhoto }}" class="img-circle mr-2 shadow-xs" style="width: 36px; height: 36px; object-fit: cover;" alt="{{ $chatOtherUserName }}" loading="lazy" decoding="async">
                        <div>
                            <h3 class="card-title font-weight-bold d-inline-block m-0" style="font-size: 1rem;">{{ $chatOtherUserName }}</h3>
                            <div class="d-flex align-items-center gap-2 mt-0.5">
                                <span class="badge badge-light border text-muted" style="font-size: 0.6875rem;">{{ $chatOtherUserRole }}</span>
                                <span class="text-success small font-weight-bold ml-1"><i class="fas fa-circle mr-1" style="font-size: 0.45rem;"></i>Online</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-tools d-flex align-items-center" style="gap: 6px;">
                        @if($chatOtherUser && Route::has('admin.users.edit'))
                            <a href="{{ route('admin.users.edit', $chatOtherUser->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 d-none d-sm-inline-flex align-items-center" title="Ver Perfil">
                                <i class="fas fa-user-circle mr-1"></i> Perfil
                            </a>
                        @endif
                        <button type="button" class="btn btn-tool" onclick="refreshMessages()" title="Atualizar mensagens">
                            <i class="fas fa-sync-alt" id="refreshIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Corpo do Chat: Lista de Conversas + Área de Mensagens --}}
                <div class="card-body p-0">
                    <div class="row no-gutters h-100" style="min-height: 540px;">
                        <!-- Coluna da Esquerda: Lista de Conversas (Escondida no Mobile na tela da conversa) -->
                        <div class="col-md-4 border-right d-none d-md-flex flex-column bg-light" style="max-height: 540px;">
                            <div class="p-2 border-bottom bg-white">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" id="conversationSearchInput" class="form-control border-left-0" placeholder="Buscar conversa...">
                                </div>
                            </div>
                            <div class="flex-grow-1 overflow-auto" id="conversations-list-sidebar" style="height: 480px;">
                                <div class="list-group list-group-flush">
                                    @foreach($conversations as $conv)
                                        @php
                                            $cUser = $conv->users->where('id', '!=', Auth::id())->first() ?? $conv->users->first();
                                            $cName = $cUser?->name ?? ($conv->title ?? 'Conversa');
                                            $cPhoto = $cUser?->photo ? asset($cUser->photo) : asset('img/default-user.svg');
                                            $isActive = $conversation->id == $conv->id;
                                            $lastMsg = $conv->messages->first();
                                        @endphp
                                        <a href="{{ route('admin.chat.show', $conv->id) }}"
                                            class="list-group-item list-group-item-action py-2.5 px-3 border-bottom {{ $isActive ? 'active' : '' }}"
                                            data-name="{{ strtolower($cName) }}">
                                            <div class="d-flex w-100 align-items-center">
                                                <img src="{{ $cPhoto }}" class="img-circle mr-2.5 flex-shrink-0"
                                                    style="width: 38px; height: 38px; object-fit: cover;" loading="lazy" decoding="async">
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex align-items-center justify-content-between mb-0.5">
                                                        <strong class="text-truncate d-block" style="font-size: 0.875rem;">{{ $cName }}</strong>
                                                        @if($lastMsg)
                                                            <small class="{{ $isActive ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.6875rem;">{{ $lastMsg->created_at->format('H:i') }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <small class="text-truncate d-block {{ $isActive ? 'text-white' : 'text-muted' }}" style="max-width: 170px;">
                                                            {{ $lastMsg ? $lastMsg->body : 'Clique para ver a conversa' }}
                                                        </small>
                                                        @if($conv->unread_count > 0)
                                                            <span class="badge badge-danger rounded-pill px-2">{{ $conv->unread_count }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Coluna da Direita: Mensagens -->
                        <div class="col-md-8 col-12 d-flex flex-column h-100" style="max-height: 540px;">
                            <div class="direct-chat-messages flex-grow-1 p-3" id="messages-container" style="height: 460px; overflow-y: auto;">
                                @foreach($messages->reverse() as $msg)
                                    @php
                                        $isMe = $msg->user_id == Auth::id();
                                        $msgTime = $msg->created_at->format('d/m H:i');
                                        $senderPhoto = $isMe ? (Auth::user()->photo ? asset(Auth::user()->photo) : asset('img/default-user.svg')) : $chatOtherUserPhoto;
                                        $senderName = $isMe ? 'Você' : $chatOtherUserName;
                                    @endphp
                                    <div class="direct-chat-msg {{ $isMe ? 'right' : '' }} mb-3">
                                        <div class="direct-chat-infos clearfix mb-1">
                                            <span class="direct-chat-name float-{{ $isMe ? 'right' : 'left' }} font-weight-bold small">{{ $senderName }}</span>
                                            <span class="direct-chat-timestamp float-{{ $isMe ? 'left' : 'right' }} text-muted small">{{ $msgTime }}</span>
                                        </div>
                                        <img class="direct-chat-img" src="{{ $senderPhoto }}" alt="{{ $senderName }}" loading="lazy" decoding="async">
                                        <div class="direct-chat-text {{ $isMe ? 'bg-primary text-white border-0 shadow-xs' : 'bg-light text-dark' }}">
                                            {!! nl2br(e($msg->body)) !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Footer / Barra de Envio Padronizada -->
                            <div class="card-footer bg-white border-top p-2.5">
                                <form id="chat-form" class="m-0">
                                    <div class="input-group">
                                        <input type="text" id="message-input" name="message" placeholder="Digite sua mensagem para {{ $chatOtherUserName }}..." class="form-control" autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" id="btnSendMessage" class="btn btn-primary px-3">
                                                <i class="fas fa-paper-plane mr-1"></i> Enviar
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
    </div>
@endsection

@push('styles')
    <style>
        .direct-chat-messages {
            height: 460px !important;
            overflow-y: auto !important;
            background-color: #f8fafc;
        }

        .dark-mode .direct-chat-messages {
            background-color: #0b1120 !important;
        }

        .direct-chat-text {
            border-radius: 0.875rem !important;
            font-size: 0.875rem !important;
            padding: 0.65rem 0.95rem !important;
            line-height: 1.4 !important;
        }

        .direct-chat-msg.right .direct-chat-text {
            background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%) !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 2px 8px rgba(31, 94, 219, 0.25) !important;
        }

        .dark-mode .direct-chat-msg:not(.right) .direct-chat-text {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
        }

        .list-group-item.active {
            background-color: #1F5EDB !important;
            border-color: #1F5EDB !important;
        }

        @media (max-width: 767.98px) {
            .direct-chat-messages {
                height: calc(100vh - 280px) !important;
                min-height: 380px !important;
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
                $('#conversations-list-sidebar .list-group-item').each(function () {
                    const name = $(this).data('name') || '';
                    if (name.includes(term)) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });
            });

            // Envio com Optimistic UI
            $('#chat-form').on('submit', function (e) {
                e.preventDefault();
                const input = $('#message-input');
                const body = input.val().trim();
                if (!body) return;

                const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const safeBody = body.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br>");
                const myPhoto = '{{ Auth::user()->photo ? asset(Auth::user()->photo) : asset("img/default-user.svg") }}';

                const html = `
                    <div class="direct-chat-msg right mb-3">
                        <div class="direct-chat-infos clearfix mb-1">
                            <span class="direct-chat-name float-right font-weight-bold small">Você</span>
                            <span class="direct-chat-timestamp float-left text-muted small">${time}</span>
                        </div>
                        <img class="direct-chat-img" src="${myPhoto}" alt="Você" loading="lazy" decoding="async">
                        <div class="direct-chat-text bg-primary text-white border-0 shadow-xs">
                            ${safeBody}
                        </div>
                    </div>
                `;

                $(container).append(html);
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

            // Enviar no Enter
            $('#message-input').on('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    $('#chat-form').submit();
                }
            });

            // Atualização manual
            window.refreshMessages = function () {
                const icon = $('#refreshIcon');
                icon.addClass('fa-spin');
                fetch('{{ route("admin.chat.messages", $conversation->id) }}')
                    .then(r => r.json())
                    .then(() => {
                        icon.removeClass('fa-spin');
                    })
                    .catch(() => {
                        icon.removeClass('fa-spin');
                    });
            };

            // Polling
            let isTabActive = true;
            document.addEventListener('visibilitychange', () => { isTabActive = !document.hidden; });

            setInterval(() => {
                if (!isTabActive) return;
                fetch('{{ route("admin.chat.messages", $conversation->id) }}')
                    .then(r => r.json())
                    .then(() => {})
                    .catch(() => {});
            }, 5000);
        });
    </script>
@endpush
