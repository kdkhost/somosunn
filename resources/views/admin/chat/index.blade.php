@extends('admin.layouts.app')

@section('title', 'Mensagens - SOMOS UNN')
@section('page_title', 'Chat')

@section('breadcrumb_items')
    <li class="breadcrumb-item active">Conversas</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm direct-chat direct-chat-primary h-100" style="min-height: 600px;">
                <div class="card-header border-bottom py-2.5 px-3">
                    <h3 class="card-title fw-bold m-0" style="font-size: 1rem;">
                        <i class="far fa-comments text-primary mr-1.5"></i> Central de Mensagens
                    </h3>
                    <div class="card-tools">
                        <span class="badge text-bg-primary rounded-pill px-2.5 py-1">{{ $conversations->count() }} conversas</span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="row g-0 h-100" style="min-height: 540px;">
                        <!-- Coluna da Esquerda: Lista de Conversas -->
                        <div class="col-md-4 col-12 border-end d-flex flex-column bg-light" style="max-height: 540px;">
                            <div class="p-2 border-bottom bg-white">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" id="conversationSearchInput" class="form-control border-start-0" placeholder="Buscar conversa...">
                                </div>
                            </div>

                            <div class="flex-grow-1 overflow-auto" id="conversations-list-container" style="height: 480px;">
                                @if($conversations->isEmpty())
                                    <div class="p-4 text-center text-muted">
                                        <i class="far fa-comment-dots fa-3x text-muted mb-3 d-block opacity-50"></i>
                                        <p class="small mb-0">Nenhuma conversa iniciada.</p>
                                    </div>
                                @else
                                    <div class="list-group list-group-flush">
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
                                                class="list-group-item list-group-item-action py-2.5 px-3 border-bottom">
                                                <div class="d-flex w-100 align-items-center">
                                                    <img src="{{ $otherUserPhoto }}" alt="{{ $otherUserName }}" class="img-circle mr-2.5 flex-shrink-0"
                                                        style="width: 38px; height: 38px; object-fit: cover;" loading="lazy" decoding="async">
                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="d-flex align-items-center justify-content-between mb-0.5">
                                                            <strong class="text-truncate d-block" style="font-size: 0.875rem;">{{ $otherUserName }}</strong>
                                                            @if($lastMsg)
                                                                <small class="text-muted" style="font-size: 0.6875rem;">{{ $lastMsg->created_at->format('H:i') }}</small>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <small class="text-truncate d-block text-muted" style="max-width: 170px;">
                                                                {{ $lastMsg ? $lastMsg->body : 'Clique para ver a conversa' }}
                                                            </small>
                                                            @if($conv->unread_count > 0)
                                                                <span class="badge text-bg-primary rounded-pill px-2">{{ $conv->unread_count }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Coluna da Direita: Placeholder de Boas-Vindas no Desktop -->
                        <div class="col-md-8 d-none d-md-flex align-items-center justify-content-center flex-column text-center p-5 bg-white">
                            <div class="text-center text-muted" style="max-width: 380px;">
                                <div class="mb-3" style="font-size: 3.5rem; color: #cbd5e1;">
                                    <i class="far fa-comments"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Selecione uma conversa</h5>
                                <p class="small text-muted mb-3">Escolha um membro ou atendimento na lista ao lado para iniciar a conversa.</p>
                                <span class="badge text-bg-light border px-3 py-2 text-muted" style="font-size: 0.75rem;">
                                    <i class="fas fa-lock text-success me-1"></i> Comunicação direta em tempo real
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Filtragem de conversas em tempo real
            $('#conversationSearchInput').on('input', function () {
                const term = $(this).val().toLowerCase().trim();
                $('#conversations-list-container .list-group-item').each(function () {
                    const name = $(this).data('name') || '';
                    if (name.includes(term)) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });
            });

            // Polling de contadores
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
                                let badge = link.querySelector('.badge');
                                if (conv.unread_count > 0) {
                                    if (!badge) {
                                        badge = document.createElement('span');
                                        badge.className = 'badge text-bg-primary rounded-pill px-2';
                                        link.querySelector('.d-flex.align-items-center.justify-content-between:last-child').appendChild(badge);
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
