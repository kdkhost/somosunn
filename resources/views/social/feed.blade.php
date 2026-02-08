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

@extends($extends ?? 'layouts.app')

@section('title', 'Comunidade - UNN')

@section('content')
    @php
        $isAdminContext = ($extends ?? 'layouts.app') === 'admin.layouts.app';

        $feedUrl = $isAdminContext ? route('admin.social.feed.internal') : route('social.feed');
        $chatUrl = $isAdminContext ? route('admin.chat.index') : route('chat.index');
        $profileUrl = $isAdminContext ? route('admin.profile.edit') : route('social.profile', Auth::id());
        $authUser = Auth::user();
        $authAvatar = $authUser ? $authUser->profile_photo_url : null;
        $shareTargets = $shareTargets ?? collect();
        $recommendedUsers = $recommendedUsers ?? collect();
        $adsEnabled = $adsEnabled ?? false;
        $adsCode = $adsCode ?? '';
    @endphp

    <div class="bg-gray-100 min-h-screen pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Sidebar Left -->
                <div class="hidden md:block">
                    <div class="bg-white rounded-lg shadow p-4 sticky top-24">
                        @auth
                            <div class="flex items-center gap-3 mb-6">
                                <div class="rounded-full w-10 h-10 overflow-hidden flex-shrink-0">
                                    <img src="{{ $authAvatar }}" alt="Avatar" class="w-10 h-10 object-cover"
                                        onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">Membro</p>
                                </div>
                            </div>
                            <nav class="space-y-2">
                                <a href="{{ $feedUrl }}"
                                    class="flex items-center gap-2 text-blue-600 font-medium p-2 bg-blue-50 rounded">
                                    <i class="fas fa-newspaper w-6"></i> Feed
                                </a>
                                <a href="{{ $chatUrl }}"
                                    class="flex items-center gap-2 text-gray-600 hover:text-blue-600 p-2 rounded transition">
                                    <i class="fas fa-comments w-6"></i> Mensagens
                                </a>
                            </nav>
                        @else
                            <div class="text-center py-4">
                                <p class="text-gray-600 mb-2">Faça login para participar</p>
                                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Entrar</a>
                            </div>
                        @endauth
                    </div>
                </div>

                <!-- Main Feed -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Composer -->
                    @auth
                        <div class="bg-white rounded-lg shadow p-4">
                            <form id="post-form" action="{{ route('social.post.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="flex gap-3">
                                    <div class="rounded-full w-10 h-10 overflow-hidden flex-shrink-0">
                                        <img src="{{ $authAvatar }}" alt="Avatar" class="w-10 h-10 object-cover"
                                            onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                    </div>
                                    <div class="flex-1">
                                        <textarea name="content" rows="3" id="post-content"
                                            class="w-full border-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-gray-50 p-3"
                                            placeholder="No que você está pensando?"></textarea>
                                    </div>
                                </div>
                                <div id="post-preview-top" class="hidden mt-3">
                                    <div class="relative inline-flex">
                                        <img id="post-preview-top-img" src="" alt="Preview"
                                            class="max-h-40 rounded-lg border border-gray-200 object-cover">
                                        <button type="button" id="post-preview-remove"
                                            class="absolute -top-2 -right-2 bg-white text-gray-500 border border-gray-200 rounded-full w-7 h-7 flex items-center justify-center hover:text-red-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <input type="file" name="media" id="post-media" accept="image/*" class="hidden">
                                    <div id="post-dropzone"
                                        class="post-dropzone rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 flex flex-col gap-2">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <div class="flex items-center gap-2 text-gray-600">
                                                <span
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200">
                                                    <i class="fas fa-image"></i>
                                                </span>
                                                <span class="text-sm font-medium">Arraste e solte uma imagem</span>
                                            </div>
                                            <button type="button" id="post-select-file"
                                                class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                                Selecionar imagem
                                            </button>
                                        </div>
                                        <div class="text-xs text-gray-500" id="post-upload-name">Nenhuma imagem selecionada</div>
                                        <div id="post-preview-inline" class="hidden items-center gap-3">
                                            <img id="post-preview-inline-img" src="" alt="Preview"
                                                class="w-12 h-12 rounded border border-gray-200 object-cover">
                                            <span class="text-xs text-gray-600" id="post-preview-inline-name"></span>
                                        </div>
                                    </div>
                                    <div id="post-upload-progress" class="hidden mt-3">
                                        <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
                                            <div id="post-upload-bar" class="post-progress-bar h-2 w-0 rounded-full"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1" id="post-upload-text">0%</div>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-3 mt-3 pt-3 border-t">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="relative">
                                            <button type="button" id="emoji-toggle" data-picker="emoji-picker"
                                                class="text-gray-500 hover:text-blue-600 p-2 rounded hover:bg-gray-100"
                                                title="Inserir emoji">
                                                <i class="fas fa-smile"></i>
                                            </button>
                                            <div id="emoji-picker" data-target="post-content"
                                                class="emoji-picker-panel hidden absolute left-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg p-3 z-20">
                                                @include('social.partials.emoji_tabs')
                                                @include('social.partials.emoji_grid')
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <label for="visibility" class="text-sm text-gray-500">Visibilidade</label>
                                            <select name="visibility" id="visibility"
                                                class="border border-gray-200 rounded-full px-3 py-1 text-sm">
                                                <option value="public">Publico</option>
                                                <option value="connections">Somente seguidores</option>
                                                <option value="community" selected>Somente comunidade</option>
                                            </select>
                                        </div>

                                        <button type="submit"
                                            class="bg-blue-600 text-white px-6 py-2 rounded-full font-medium hover:bg-blue-700 transition">
                                            Publicar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endauth

                    <!-- Posts -->
                    @forelse($posts as $post)
                        <div class="bg-white rounded-lg shadow p-4" id="post-{{ $post->id }}">
                            @php
                                $viewerId = Auth::id();
                                $hasLiked = $viewerId ? $post->reactions->firstWhere('user_id', $viewerId) : null;
                                $likeCount = $post->reactions->count();
                                $commentCount = $post->comments->count();
                                $postAvatar = $post->user->profile_photo_url ?? asset('img/default-user.svg');
                                $postLink = route('social.post.public', $post);
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
                                <div class="relative">
                                    <button type="button" class="text-gray-400 hover:text-gray-600" title="Mais opcoes"
                                        onclick="togglePanel('menu-{{ $post->id }}')">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <div id="menu-{{ $post->id }}"
                                        class="hidden absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-lg shadow z-10">
                                        <div class="py-1 text-sm text-gray-700">
                                            @if(Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isAdmin()))
                                                <form action="{{ route('social.post.destroy', $post) }}" method="POST"
                                                    class="js-confirm-delete" data-confirm-title="Remover publicacao?"
                                                    data-confirm-text="Esta acao nao pode ser desfeita.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-full text-left px-4 py-2 hover:bg-gray-50 text-red-600 flex items-center gap-2">
                                                        <i class="fas fa-trash"></i>
                                                        <span>Remover</span>
                                                    </button>
                                                </form>
                                                <form action="{{ route('social.post.unpublish', $post) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2">
                                                        <i class="fas fa-eye-slash"></i>
                                                        <span>Despublicar</span>
                                                    </button>
                                                </form>
                                            @endif
                                            @auth
                                                <form action="{{ route('social.post.hide', $post) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2">
                                                        <i class="fas fa-eye"></i>
                                                        <span>Ocultar postagem</span>
                                                    </button>
                                                </form>
                                            @endauth
                                            @auth
                                                @if(!(Auth::id() === $post->user_id || Auth::user()->isAdmin()))
                                                    <button type="button"
                                                        class="w-full text-left px-4 py-2 hover:bg-gray-50 text-red-600 flex items-center gap-2"
                                                        onclick="togglePanel('report-{{ $post->id }}'); togglePanel('menu-{{ $post->id }}');">
                                                        <i class="fas fa-flag"></i>
                                                        <span>Denunciar</span>
                                                    </button>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="prose max-w-none text-gray-800 mb-4">
                                {!! nl2br(e($post->content)) !!}
                            </div>

                            @if($post->media->isNotEmpty())
                                <div class="mb-4">
                                    <img src="{{ asset($post->media->first()->path) }}" alt="Midia do post"
                                        class="w-full rounded-lg object-cover">
                                </div>
                            @endif

                            <!-- Reactions / Actions -->
                            <div class="flex items-center justify-between pt-3 border-t text-sm text-gray-500">
                                @auth
                                    <form action="{{ route('social.post.react', $post) }}" method="POST">
                                        @csrf
                                        <button type="submit" aria-label="Curtir"
                                            class="flex items-center gap-2 transition {{ $hasLiked ? 'text-blue-600' : 'hover:text-blue-600' }}">
                                            <i class="{{ $hasLiked ? 'fas' : 'far' }} fa-thumbs-up"></i>
                                            <span class="hidden sm:inline">Curtir</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="flex items-center gap-2" aria-label="Curtir">
                                        <i class="far fa-thumbs-up"></i>
                                        <span class="hidden sm:inline">Curtir</span>
                                    </span>
                                @endauth

                                <button type="button" class="flex items-center gap-2 hover:text-blue-600 transition"
                                    onclick="document.getElementById('comment-{{ $post->id }}').focus();" aria-label="Comentar">
                                    <i class="far fa-comment"></i>
                                    <span class="hidden sm:inline">Comentar</span>
                                </button>

                                <button type="button" class="flex items-center gap-2 hover:text-blue-600 transition"
                                    onclick="togglePanel('share-{{ $post->id }}')" aria-label="Compartilhar">
                                    <i class="fas fa-share"></i>
                                    <span class="hidden sm:inline">Compartilhar</span>
                                </button>
                            </div>
                            <div id="share-{{ $post->id }}" class="hidden mt-3 border-t pt-3 space-y-3">
                                <div class="flex flex-wrap gap-3 text-sm">
                                    <button type="button" class="text-blue-600" data-copy="{{ $postLink }}"
                                        onclick="copyPostLink(this)">Copiar link</button>
                                    <a class="text-green-600" target="_blank" rel="noopener"
                                        href="https://wa.me/?text={{ urlencode($postLink) }}">WhatsApp</a>
                                    <a class="text-blue-500" target="_blank" rel="noopener"
                                        href="https://t.me/share/url?url={{ urlencode($postLink) }}">Telegram</a>
                                    <a class="text-blue-700" target="_blank" rel="noopener"
                                        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($postLink) }}">Facebook</a>
                                </div>

                                @auth
                                    <form action="{{ route('social.post.share', $post) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm text-gray-600 hover:text-blue-600">
                                            Compartilhar na comunidade
                                        </button>
                                    </form>

                                    @if($shareTargets->isNotEmpty())
                                        <form action="{{ route('social.post.share.user', $post) }}" method="POST"
                                            class="flex flex-col gap-2">
                                            @csrf
                                            <div class="flex flex-wrap gap-2">
                                                <select name="target_user_id" class="border border-gray-200 rounded px-3 py-2 text-sm">
                                                    @foreach($shareTargets as $target)
                                                        <option value="{{ $target->id }}">{{ $target->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="message" placeholder="Mensagem opcional"
                                                    class="flex-1 border border-gray-200 rounded px-3 py-2 text-sm">
                                                <button type="submit"
                                                    class="bg-blue-600 text-white px-3 py-2 rounded text-sm">Enviar</button>
                                            </div>
                                        </form>
                                    @endif
                                @endauth
                            </div>

                            <div id="report-{{ $post->id }}" class="hidden mt-3 border-t pt-3">
                                @auth
                                    <form action="{{ route('social.post.report', $post) }}" method="POST"
                                        class="flex flex-col gap-2">
                                        @csrf
                                        <textarea name="reason" rows="2" required
                                            placeholder="Descreva o motivo da denuncia"
                                            class="border border-gray-200 rounded px-3 py-2 text-sm"></textarea>
                                        <button type="submit" class="text-sm text-red-600">Denunciar</button>
                                    </form>
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
                                                <div class="flex justify-between items-center">
                                                    <p class="text-xs font-semibold text-gray-700">{{ $commentName }}</p>
                                                    @if(Auth::check() && (Auth::id() === $comment->user_id || Auth::id() === $post->user_id || Auth::user()->isAdmin()))
                                                        <form action="{{ route('social.comment.destroy', $comment) }}" method="POST"
                                                            class="js-confirm-delete" data-confirm-title="Remover comentario?"
                                                            data-confirm-text="Esta acao nao pode ser desfeita.">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-xs text-red-500" title="Remover">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
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
                                    <input type="text" name="content" id="comment-{{ $post->id }}"
                                        placeholder="Escreva um comentario..."
                                        class="flex-1 border border-gray-200 rounded-full px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <div class="relative">
                                        <button type="button" class="comment-emoji-toggle text-gray-500 hover:text-blue-600"
                                            data-picker="comment-emoji-{{ $post->id }}" title="Inserir emoji">
                                            <i class="far fa-smile"></i>
                                        </button>
                                        <div id="comment-emoji-{{ $post->id }}" data-target="comment-{{ $post->id }}"
                                            class="emoji-picker-panel hidden absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg p-3 z-20">
                                            @include('social.partials.emoji_tabs')
                                            @include('social.partials.emoji_grid')
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm hover:bg-blue-700 transition">
                                        Enviar
                                    </button>
                                </form>
                            @endauth
                        </div>
                        @if(!empty($adsEnabled) && !empty($adsCode) && $loop->iteration % 3 === 0)
                            <div class="bg-white rounded-lg shadow p-4">
                                {!! $adsCode !!}
                            </div>
                        @endif
                    @empty
                        <div class="text-center py-10 text-gray-500">
                            <p>Nenhum post ainda. Seja o primeiro a publicar!</p>
                        </div>
                    @endforelse

                    {{ $posts->links() }}
                </div>

                <!-- Sidebar Right (Suggestions) -->
                <div class="hidden md:block">
                    <div class="bg-white rounded-lg shadow p-4 sticky top-24">
                        <h3 class="font-bold text-gray-900 mb-4">Recomendados</h3>
                        <div class="space-y-4">
                            @if(!empty($recommendedUsers) && $recommendedUsers->isNotEmpty())
                                @foreach($recommendedUsers as $user)
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-10 h-10 overflow-hidden flex-shrink-0">
                                                <img src="{{ $user->profile_photo_url }}" alt="Avatar"
                                                    class="w-10 h-10 object-cover"
                                                    onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">{{ $user->name }}</p>
                                                <p class="text-xs text-gray-500">Membro</p>
                                            </div>
                                        </div>
                                        <form action="{{ route('connection.connect', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                                Conectar
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-xs text-gray-500">Sem recomendacoes no momento.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .post-dropzone.is-dragover {
            border-color: #2563eb;
            background-color: #eff6ff;
        }

        .post-progress-bar {
            background: linear-gradient(90deg, #1f5edb 0%, #3b82f6 50%, #1f5edb 100%);
            background-size: 200% 100%;
            animation: progress-flow 1.2s linear infinite;
            transition: width 0.2s ease;
        }

        .emoji-item {
            width: 100%;
            padding: 0.25rem 0;
            border-radius: 0.5rem;
            transition: transform 0.15s ease;
            animation: emoji-float 1.6s ease-in-out infinite;
        }

        .emoji-item:hover {
            transform: scale(1.2);
            background-color: #f3f4f6;
        }

        .emoji-item:nth-child(3n) {
            animation-delay: 0.2s;
        }

        .emoji-item:nth-child(4n) {
            animation-delay: 0.35s;
        }

        .emoji-tab {
            padding: 0.25rem 0.45rem;
            border-radius: 9999px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            transition: all 0.15s ease;
        }

        .emoji-tab:hover {
            background-color: #f3f4f6;
        }

        .emoji-tab.is-active {
            border-color: #2563eb;
            background-color: #eff6ff;
        }

        @keyframes progress-flow {
            0% {
                background-position: 0% 50%;
            }
            100% {
                background-position: 100% 50%;
            }
        }

        @keyframes emoji-float {
            0%,
            100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-3px);
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function togglePanel(id) {
            const panel = document.getElementById(id);
            if (!panel) {
                return;
            }

            panel.classList.toggle('hidden');
        }

        function copyPostLink(button) {
            const link = button.getAttribute('data-copy');
            if (!link) {
                return;
            }

            navigator.clipboard.writeText(link).then(() => {
                button.textContent = 'Link copiado';
                setTimeout(() => {
                    button.textContent = 'Copiar link';
                }, 1500);
            });
        }

        document.querySelectorAll('.js-confirm-delete').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();

                Swal.fire({
                    title: form.dataset.confirmTitle || 'Remover?'
                    ,
                    text: form.dataset.confirmText || 'Esta acao nao pode ser desfeita.'
                    ,
                    icon: 'warning'
                    ,
                    showCancelButton: true
                    ,
                    confirmButtonColor: '#d33'
                    ,
                    cancelButtonColor: '#6c757d'
                    ,
                    confirmButtonText: 'Sim, remover'
                    ,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        const postForm = document.getElementById('post-form');
        const postMedia = document.getElementById('post-media');
        const dropzone = document.getElementById('post-dropzone');
        const selectFile = document.getElementById('post-select-file');
        const progressWrap = document.getElementById('post-upload-progress');
        const progressBar = document.getElementById('post-upload-bar');
        const progressText = document.getElementById('post-upload-text');
        const uploadName = document.getElementById('post-upload-name');
        const previewTop = document.getElementById('post-preview-top');
        const previewTopImg = document.getElementById('post-preview-top-img');
        const previewInline = document.getElementById('post-preview-inline');
        const previewInlineImg = document.getElementById('post-preview-inline-img');
        const previewInlineName = document.getElementById('post-preview-inline-name');
        const previewRemove = document.getElementById('post-preview-remove');
        const emojiToggle = document.getElementById('emoji-toggle');
        const emojiPicker = document.getElementById('emoji-picker');

        const updateUploadName = (file) => {
            if (!uploadName) {
                return;
            }

            uploadName.textContent = file ? file.name : 'Nenhuma imagem selecionada';
        };

        const showPreview = (file) => {
            if (!file || !previewTopImg || !previewInlineImg) {
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                const src = event.target ? event.target.result : null;
                if (!src) {
                    return;
                }

                previewTopImg.src = src;
                previewInlineImg.src = src;
                if (previewInlineName) {
                    previewInlineName.textContent = file.name;
                }
                if (previewTop) {
                    previewTop.classList.remove('hidden');
                }
                if (previewInline) {
                    previewInline.classList.remove('hidden');
                    previewInline.classList.add('flex');
                }
            };
            reader.readAsDataURL(file);
        };

        const clearPreview = () => {
            if (previewTop) {
                previewTop.classList.add('hidden');
            }
            if (previewInline) {
                previewInline.classList.add('hidden');
                previewInline.classList.remove('flex');
            }
            if (previewTopImg) {
                previewTopImg.src = '';
            }
            if (previewInlineImg) {
                previewInlineImg.src = '';
            }
            if (previewInlineName) {
                previewInlineName.textContent = '';
            }
        };

        if (selectFile && postMedia) {
            selectFile.addEventListener('click', () => postMedia.click());
        }

        if (postMedia) {
            postMedia.addEventListener('change', () => {
                const file = postMedia.files[0];
                updateUploadName(file);
                if (file) {
                    showPreview(file);
                } else {
                    clearPreview();
                }
            });
        }

        if (previewRemove && postMedia) {
            previewRemove.addEventListener('click', () => {
                postMedia.value = '';
                updateUploadName(null);
                clearPreview();
            });
        }

        if (dropzone && postMedia) {
            dropzone.addEventListener('dragover', (event) => {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });

            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('is-dragover');
            });

            dropzone.addEventListener('drop', (event) => {
                event.preventDefault();
                dropzone.classList.remove('is-dragover');

                const files = event.dataTransfer ? event.dataTransfer.files : [];
                if (!files || !files.length) {
                    return;
                }

                const file = files[0];
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                postMedia.files = dataTransfer.files;
                updateUploadName(file);
                showPreview(file);
            });
        }

        if (postForm) {
            postForm.addEventListener('submit', (event) => {
                event.preventDefault();

                const formData = new FormData(postForm);
                const xhr = new XMLHttpRequest();
                xhr.open('POST', postForm.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                if (progressWrap && progressBar && progressText) {
                    progressWrap.classList.remove('hidden');
                    progressBar.style.width = '0%';
                    progressText.textContent = '0%';
                }

                xhr.upload.addEventListener('progress', (event) => {
                    if (!event.lengthComputable || !progressBar || !progressText) {
                        return;
                    }

                    const percent = Math.min(100, Math.round((event.loaded / event.total) * 100));
                    progressBar.style.width = percent + '%';
                    progressText.textContent = percent + '%';
                });

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 400) {
                        window.location.href = xhr.responseURL || window.location.href;
                        return;
                    }

                    window.location.reload();
                };

                xhr.onerror = () => {
                    window.location.reload();
                };

                xhr.send(formData);
            });
        }

        const closeAllEmojiPickers = () => {
            document.querySelectorAll('.emoji-picker-panel').forEach((panel) => {
                panel.classList.add('hidden');
            });
        };

        const initEmojiPicker = (toggle, picker) => {
            if (!toggle || !picker) {
                return;
            }

            const emojiTabs = picker.querySelectorAll('.emoji-tab');
            const emojiItems = picker.querySelectorAll('.emoji-item');
            const targetId = picker.getAttribute('data-target');

            const applyEmojiCategory = (category) => {
                emojiItems.forEach((item) => {
                    const itemCategory = item.getAttribute('data-category');
                    const show = category === 'all' || itemCategory === category;
                    item.classList.toggle('hidden', !show);
                });

                emojiTabs.forEach((tab) => {
                    tab.classList.toggle('is-active', tab.getAttribute('data-category') === category);
                });
            };

            if (emojiTabs.length) {
                emojiTabs.forEach((tab) => {
                    tab.addEventListener('click', () => {
                        const category = tab.getAttribute('data-category') || 'all';
                        applyEmojiCategory(category);
                    });
                });

                const initial = picker.querySelector('.emoji-tab.is-active') || emojiTabs[0];
                const initialCategory = initial ? initial.getAttribute('data-category') : 'all';
                applyEmojiCategory(initialCategory || 'all');
            }

            toggle.addEventListener('click', (event) => {
                event.stopPropagation();
                const isHidden = picker.classList.contains('hidden');
                closeAllEmojiPickers();
                picker.classList.toggle('hidden', !isHidden);
            });

            emojiItems.forEach((button) => {
                button.addEventListener('click', () => {
                    const emoji = button.getAttribute('data-emoji') || '';
                    const target = targetId ? document.getElementById(targetId) : null;
                    if (!target || emoji === '') {
                        return;
                    }

                    const start = target.selectionStart || 0;
                    const end = target.selectionEnd || 0;
                    const value = target.value || '';
                    target.value = value.slice(0, start) + emoji + value.slice(end);
                    target.focus();
                    target.selectionStart = target.selectionEnd = start + emoji.length;
                });
            });
        };

        if (emojiToggle && emojiPicker) {
            initEmojiPicker(emojiToggle, emojiPicker);
        }

        document.querySelectorAll('.comment-emoji-toggle').forEach((toggle) => {
            const pickerId = toggle.getAttribute('data-picker');
            const picker = pickerId ? document.getElementById(pickerId) : null;
            initEmojiPicker(toggle, picker);
        });

        document.addEventListener('click', (event) => {
            if (event.target.closest('.emoji-picker-panel') || event.target.closest('.comment-emoji-toggle') || event.target.closest('#emoji-toggle')) {
                return;
            }

            closeAllEmojiPickers();
        });
    </script>
@endpush
