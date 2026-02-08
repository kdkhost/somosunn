<?php
/**
 * =============================================================================
 * AVISO LEGAL DE DIREITOS AUTORAIS E PROPRIEDADE INTELECTUAL
 * =============================================================================
 *
 * © 2026 Marcelo Brad - Todos os direitos reservados.
 *
 * AUTOR:
 * marcelo-brad rj
 *
 * CONTATO:
 * Tel: +55 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 * WhatsApp: +55 21 98132-5441
 *
 * -----------------------------------------------------------------------------
 * DIREITOS AUTORAIS:
 * Este software, incluindo seu código-fonte, estrutura, banco de dados,
 * layout, funcionalidades, lógica de programação e documentação associada,
 * é protegido pelas leis brasileiras de direitos autorais (Lei nº 9.610/98)
 * e demais legislações internacionais aplicáveis.
 *
 * -----------------------------------------------------------------------------
 * PROPRIEDADE INTELECTUAL:
 * Todo o conteúdo deste sistema é de propriedade exclusiva do autor,
 * sendo proibida a reprodução total ou parcial, modificação,
 * engenharia reversa, redistribuição, sublicenciamento,
 * comercialização ou qualquer forma de exploração sem autorização
 * expressa e formal do titular dos direitos.
 *
 * -----------------------------------------------------------------------------
 * LICENÇA DE USO:
 * Este sistema é licenciado, não vendido.
 * O uso é restrito ao cliente contratante conforme contrato firmado.
 * É vedado o compartilhamento, revenda ou distribuição a terceiros
 * sem autorização prévia e documentada.
 *
 * -----------------------------------------------------------------------------
 * RESPONSABILIDADE:
 * Alterações realizadas por terceiros não autorizados anulam qualquer
 * responsabilidade do autor sobre falhas, vulnerabilidades ou danos
 * decorrentes do uso indevido do sistema.
 *
 * -----------------------------------------------------------------------------
 * SEGURANÇA E MONITORAMENTO:
 * Este software pode conter mecanismos de identificação,
 * rastreamento de licença e validação de integridade para
 * proteção contra uso não autorizado e pirataria.
 *
 * -----------------------------------------------------------------------------
 * PENALIDADES:
 * O uso indevido ou não autorizado poderá resultar em medidas legais
 * cabíveis nas esferas civil e criminal, incluindo indenizações por
 * perdas e danos.
 *
 * =============================================================================
 */
?>

@extends('layouts.app')

@section('title', $user->name . ' - Perfil UNN')

@section('content')
    <div class="bg-gray-100 min-h-screen">
        <!-- Cover & Info -->
        <!-- Cover & Info -->
        <div class="bg-white shadow relative">
            <!-- Capa -->
            <div class="h-64 sm:h-80 w-full bg-gray-200 overflow-hidden relative group">
                @if(isset($user->cover_photo) && $user->cover_photo)
                    <img src="{{ asset($user->cover_photo) }}" alt="Capa" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-r from-[#1F5EDB] to-[#0d3b96]"></div>
                @endif
            </div>

            <!-- Info do Perfil -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative pb-8">
                <div class="flex flex-col md:flex-row items-end -mt-20 sm:-mt-24 mb-4 gap-6 relative">

                    <!-- Avatar -->
                    <div class="flex-shrink-0 relative">
                        <div class="w-40 h-40 sm:w-48 sm:h-48 bg-white rounded-full p-1.5 shadow-xl">
                            <img src="{{ $user->profile_photo_url }}"
                                class="w-full h-full rounded-full object-cover border-4 border-white" alt="Avatar"
                                onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                        </div>
                    </div>

                    <!-- Detalhes -->
                    <div class="flex-1 pb-2 w-full text-center md:text-left pt-16 md:pt-0">
                        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1">{{ $user->name }}</h1>

                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-gray-600 mb-4">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-calendar-alt text-[#1F5EDB]"></i> Membro desde
                                {{ $user->created_at->format('M Y') }}
                            </span>
                            @if(isset($user->city) && $user->city)
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-map-marker-alt text-[#1F5EDB]"></i> {{ $user->city }}
                                </span>
                            @endif
                        </div>

                        <!-- Redes Sociais -->
                        @php
                            $socialLinks = collect([
                                ['key' => 'linkedin', 'icon' => 'fab fa-linkedin', 'color' => '#0A66C2'],
                                ['key' => 'instagram', 'icon' => 'fab fa-instagram', 'color' => '#E4405F'],
                                ['key' => 'facebook', 'icon' => 'fab fa-facebook', 'color' => '#1877F2'],
                                ['key' => 'twitter', 'icon' => 'fab fa-twitter', 'color' => '#1DA1F2'],
                                ['key' => 'youtube', 'icon' => 'fab fa-youtube', 'color' => '#FF0000'],
                                ['key' => 'website', 'icon' => 'fas fa-globe', 'color' => '#10B981'],
                            ])->filter(fn($s) => isset($user->{$s['key']}) && $user->{$s['key']});
                        @endphp

                        @if($socialLinks->isNotEmpty())
                            <div class="flex items-center justify-center md:justify-start gap-3 mt-2">
                                @foreach($socialLinks as $social)
                                    <a href="{{ $user->{$social['key']} }}" target="_blank" rel="noopener"
                                        class="w-10 h-10 flex items-center justify-center bg-gray-50 rounded-full hover:shadow-md transition transform hover:-translate-y-1"
                                        style="color: {{ $social['color'] }}" title="{{ ucfirst($social['key']) }}">
                                        <i class="{{ $social['icon'] }} text-xl"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Ações -->
                    <div class="flex gap-3 pb-4 w-full md:w-auto justify-center md:justify-end">
                        @if(auth()->check() && auth()->id() !== $user->id)
                            @php
                                $isConnected = auth()->user()->isConnectedWith($user->id);
                                $pendingConnection = auth()->user()->hasPendingConnectionWith($user->id);
                                $isRequester = $pendingConnection && $pendingConnection->requester_id === auth()->id();
                                $canMessage = auth()->user()->canMessageUser($user);
                            @endphp

                            @if($isConnected)
                                <button onclick="removeConnection({{ $user->id }})"
                                    class="bg-gray-200 text-gray-700 px-6 py-3 rounded-full font-bold hover:bg-gray-300 transition shadow flex items-center gap-2">
                                    <i class="fas fa-user-check text-green-600"></i> Conectado
                                </button>
                            @endif

                            @if($canMessage)
                                <button
                                    onclick="openChatBox({{ $user->id }}, '{{ $user->name }}', '{{ $user->profile_photo_url }}')"
                                    class="bg-[#1F5EDB] text-white px-8 py-3 rounded-full font-bold hover:bg-blue-700 transition shadow-lg hover:shadow-xl flex items-center gap-2">
                                    <i class="fas fa-comment-dots"></i> Mensagem
                                </button>
                            @endif

                            @if($pendingConnection)
                                @if($isRequester)
                                    <button
                                        class="bg-gray-200 text-gray-500 px-8 py-3 rounded-full font-bold cursor-not-allowed shadow flex items-center gap-2">
                                        <i class="fas fa-clock"></i> Pendente
                                    </button>
                                @else
                                    <button onclick="acceptConnection({{ $user->id }})"
                                        class="bg-green-600 text-white px-8 py-3 rounded-full font-bold hover:bg-green-700 transition shadow-lg flex items-center gap-2">
                                        <i class="fas fa-check"></i> Aceitar
                                    </button>
                                @endif
                            @elseif(!$isConnected)
                                <button onclick="requestConnection({{ $user->id }})"
                                    class="bg-[#1F5EDB] text-white px-8 py-3 rounded-full font-bold hover:bg-blue-700 transition shadow-lg hover:shadow-xl flex items-center gap-2"
                                    id="btn-connect-{{ $user->id }}">
                                    <i class="fas fa-user-plus"></i> Conectar
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @push('scripts')
            <script>
                function requestConnection(userId) {
                    Swal.fire({
                        title: 'Conectar com este usuário?',
                        text: "Você enviará uma solicitação de conexão.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#1F5EDB',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sim, conectar!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const btn = document.getElementById(`btn-connect-${userId}`);
                            const originalContent = btn.innerHTML;
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                            btn.disabled = true;

                            fetch(`/connect/${userId}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            })
                                .then(r => r.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: 'Solicitação enviada!',
                                            text: data.message,
                                            icon: 'success',
                                            timer: 2000,
                                            showConfirmButton: false
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        toastr.error(data.message);
                                        btn.innerHTML = originalContent;
                                        btn.disabled = false;
                                    }
                                })
                                .catch(() => {
                                    toastr.error('Erro ao conectar.');
                                    btn.innerHTML = originalContent;
                                    btn.disabled = false;
                                });
                        }
                    });
                }

                function acceptConnection(userId) {
                    fetch(`/connection/accept/${userId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                toastr.success(data.message);
                                location.reload();
                            } else {
                                toastr.error(data.message);
                            }
                        });
                }

                function removeConnection(userId) {
                    Swal.fire({
                        title: 'Tem certeza que deseja desfazer a conexão?',
                        text: "Esta ação removerá a conexão com este usuário.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sim, desfazer!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/connection/remove/${userId}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            }) \n.then(r => r.json()) \n.then(data => { \n                                if (data.success) { \n                                    toastr.success(data.message); \n                                    location.reload(); \n } else { \n                                    toastr.error(data.message); \n } \n }); \n
                        } \n
                    }); \n
                } \n            </script>
        @endpush

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Sobre</h3>
                    <p class="text-gray-600 text-sm">
                        {{ $user->bio ?? 'Sem descrição.' }}
                    </p>

                    <hr class="my-4">

                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt w-5 text-gray-400"></i>
                            @if($user->city || $user->state)
                                {{ $user->city }}{{ $user->city && $user->state ? ', ' : '' }}{{ $user->state }}
                            @else
                                Localização não informada
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-briefcase w-5 text-gray-400"></i>
                            @if($user->occupation || $user->company)
                                {{ $user->occupation }}{{ $user->occupation && $user->company ? ' em ' : '' }}{{ $user->company }}
                            @else
                                Cargo não informado
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="md:col-span-2 space-y-6">
                <h3 class="font-bold text-xl text-gray-800">Publicações</h3>

                @forelse($posts as $post)
                    <div class="bg-white rounded-lg shadow p-4">
                        @php
                            $viewerId = Auth::id();
                            $hasLiked = $viewerId ? $post->reactions->firstWhere('user_id', $viewerId) : null;
                            $likeCount = $post->reactions->count();
                            $commentCount = $post->comments->count();
                            $postAvatar = $post->user->profile_photo_url ?? asset('img/default-user.svg');
                        @endphp
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                <div class="rounded-full w-10 h-10 overflow-hidden flex-shrink-0">
                                    <img src="{{ $postAvatar }}" alt="Avatar" class="w-10 h-10 object-cover"
                                        onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ $post->user->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="prose max-w-none text-gray-800">
                            {!! nl2br(e($post->content)) !!}
                        </div>

                        <div class="flex items-center justify-between pt-3 mt-3 border-t text-sm text-gray-500">
                            @auth
                                <form action="{{ route('social.post.react', $post) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center gap-2 transition {{ $hasLiked ? 'text-blue-600' : 'hover:text-blue-600' }}">
                                        <i class="{{ $hasLiked ? 'fas' : 'far' }} fa-thumbs-up"></i> Curtir
                                    </button>
                                </form>
                            @else
                                <span class="flex items-center gap-2">
                                    <i class="far fa-thumbs-up"></i> Curtir
                                </span>
                            @endauth

                            <span class="flex items-center gap-2">
                                <i class="far fa-comment"></i> Comentar
                            </span>

                            @auth
                                <form action="{{ route('social.post.share', $post) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2 hover:text-blue-600 transition">
                                        <i class="fas fa-share"></i> Compartilhar
                                    </button>
                                </form>
                            @else
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-share"></i> Compartilhar
                                </span>
                            @endauth
                        </div>

                        <div class="mt-2 text-xs text-gray-400 flex gap-3">
                            <span>{{ $likeCount }} curtida{{ $likeCount === 1 ? '' : 's' }}</span>
                            <span>{{ $commentCount }} comentario{{ $commentCount === 1 ? '' : 's' }}</span>
                        </div>

                        @if($post->comments->isNotEmpty())
                            <div class="mt-4 space-y-3">
                                @foreach($post->comments as $comment)
                                    @php
                                        $commentUser = $comment->user;
                                        $commentAvatar = $commentUser
                                            ? $commentUser->profile_photo_url
                                            : asset('img/default-user.svg');
                                        $commentName = $commentUser ? $commentUser->name : 'Usuario';
                                    @endphp
                                    <div class="flex gap-3">
                                        <div class="rounded-full w-8 h-8 overflow-hidden flex-shrink-0">
                                            <img src="{{ $commentAvatar }}" alt="Avatar"
                                                class="w-8 h-8 object-cover"
                                                onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                        </div>
                                        <div class="bg-gray-50 rounded-lg px-3 py-2 w-full">
                                            <p class="text-xs font-semibold text-gray-700">{{ $commentName }}</p>
                                            <p class="text-sm text-gray-700">{{ $comment->content }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @auth
                            <form action="{{ route('social.post.comment', $post) }}" method="POST"
                                class="mt-3 flex gap-2">
                                @csrf
                                <input type="text" name="content" placeholder="Escreva um comentario..."
                                    class="flex-1 border border-gray-200 rounded-full px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <button type="submit"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm hover:bg-blue-700 transition">
                                    Enviar
                                </button>
                            </form>
                        @endauth
                    </div>
                @empty
                    <div class="text-center py-10 text-gray-500 bg-white rounded-lg shadow">
                        <p>Nenhuma publicação ainda.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Floating Chat Box (Facebook-style) -->
    <div id="chatBox"
        class="fixed bottom-0 right-4 w-full sm:w-96 bg-white rounded-t-xl shadow-2xl border border-gray-200 transition-all duration-300 transform translate-y-full z-50"
        style="display: none;">
        <!-- Chat Header -->
        <div class="bg-[#1F5EDB] text-white px-4 py-3 rounded-t-xl flex items-center justify-between cursor-pointer"
            onclick="toggleMinimizeChat()">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center overflow-hidden">
                    <img id="chatUserAvatar" src="" alt="Avatar" class="w-full h-full object-cover hidden">
                    <span id="chatUserInitial" class="text-[#1F5EDB] font-bold text-lg"></span>
                </div>
                <div>
                    <h3 id="chatUserName" class="font-bold text-sm">Carregando...</h3>
                    <p class="text-xs opacity-80">Online</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="event.stopPropagation(); toggleMinimizeChat();"
                    class="hover:bg-blue-600 p-2 rounded-full transition">
                    <i id="chatMinimizeIcon" class="fas fa-minus text-sm"></i>
                </button>
                <button onclick="event.stopPropagation(); closeChatBox();"
                    class="hover:bg-blue-600 p-2 rounded-full transition">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Chat Body -->
        <div id="chatBody" class="h-96 overflow-y-auto p-4 bg-gray-50 space-y-3">
            <div class="text-center text-gray-500 text-sm py-8">
                <i class="fas fa-comment-dots text-4xl mb-2 opacity-50"></i>
                <p>Inicie uma conversa!</p>
            </div>
        </div>

        <!-- Chat Input -->
        <div id="chatFooter" class="border-t border-gray-200 p-3 bg-white rounded-b-xl">
            <form id="chatForm" onsubmit="sendMessage(event);" class="flex gap-2">
                <input type="hidden" id="chatUserId" value="">
                <input type="text" id="chatInput" placeholder="Digite sua mensagem..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:border-[#1F5EDB] focus:ring-1 focus:ring-[#1F5EDB]">
                <button type="submit"
                    class="bg-[#1F5EDB] text-white px-5 py-2 rounded-full hover:bg-blue-700 transition font-medium">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Floating Chat Script -->
    <script src="{{ asset('js/floating-chat.js') }}"></script>
@endsection