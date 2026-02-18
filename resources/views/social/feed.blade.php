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

        // AdSense config
        $adsConfig = $adsConfig ?? [];
        $adsensePublisherId = $adsConfig['publisherId'] ?? '';
        $adsenseSlotId = $adsConfig['slotId'] ?? '';
        $adsenseFormat = $adsConfig['format'] ?? 'auto';
        $adsenseFrequency = (int) ($adsConfig['frequency'] ?? 5);
        $hasAdsense = !empty($adsensePublisherId) && !empty($adsenseSlotId);
    @endphp

    <div class="bg-gray-100 min-h-screen {{ $isAdminContext ? 'pt-0' : 'pt-4' }}">
        <div class="{{ $isAdminContext ? 'mx-auto px-0' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' }}">
            <div class="{{ $isAdminContext ? 'grid grid-cols-1 gap-6' : 'grid grid-cols-1 md:grid-cols-12 gap-6' }}">
                @unless($isAdminContext)
                    <!-- Sidebar Left -->
                    <div class="hidden md:block md:col-span-3">
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
                                <nav class="space-y-2 mb-4">
                                    <button type="button" data-panel="feed"
                                        class="w-full flex items-center gap-2 text-blue-600 font-medium p-2 bg-blue-50 rounded transition">
                                        <i class="fas fa-newspaper w-6"></i>
                                        <span>Feed</span>
                                    </button>
                                    <button type="button" data-panel="chat"
                                        class="w-full flex items-center gap-2 text-gray-600 hover:text-blue-600 p-2 rounded">
                                        <i class="fas fa-comments w-6"></i>
                                        <span>Mensagens</span>
                                    </button>
                                </nav>
                                <div class="border-t border-gray-100 pt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-bold text-gray-900">Conversas</h3>
                                        <button type="button" class="text-gray-400 hover:text-blue-600 transition"
                                            onclick="showRecommendedForNewChat()" title="Nova conversa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                    <div class="relative mb-3">
                                        <i class="fas fa-search text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 text-xs"></i>
                                        <input type="text" id="conversation-search" placeholder="Pesquisar no Messenger"
                                            class="w-full border border-gray-200 rounded-full pl-9 pr-3 py-2 text-xs focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div id="conversation-filters" class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                                        <button type="button" data-filter="all"
                                            class="filter-btn px-3 py-1 rounded-full bg-blue-50 text-blue-600">Tudo</button>
                                        <button type="button" data-filter="unread"
                                            class="filter-btn px-3 py-1 rounded-full hover:bg-gray-100">Não lidas</button>
                                        <button type="button" data-filter="groups"
                                            class="filter-btn px-3 py-1 rounded-full hover:bg-gray-100">Grupos</button>
                                    </div>
                                    <div id="conversation-list" class="space-y-1">
                                        <p class="text-xs text-gray-500">Carregando conversas...</p>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-gray-600 mb-2">Faça login para participar</p>
                                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Entrar</a>
                                </div>
                            @endauth
                        </div>
                    </div>
                @endunless

                <!-- Centro -->
                <div class="{{ $isAdminContext ? 'space-y-6' : 'md:col-span-6 space-y-6' }}">
                    <div id="feed-panel" class="space-y-6">
                        @auth
                            <div class="bg-white rounded-lg shadow p-4">
                                <form id="post-form" action="{{ route('social.post.store') }}" method="POST"
                                    enctype="multipart/form-data">
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
                                        <input type="file" name="media[]" id="post-media" accept="image/*" class="hidden"
                                            multiple>
                                        <div id="post-dropzone"
                                            class="post-dropzone rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 flex flex-col gap-2">
                                            <div class="flex flex-wrap items-center gap-3">
                                                <div class="flex items-center gap-2 text-gray-600">
                                                    <span
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200">
                                                        <i class="fas fa-image"></i>
                                                    </span>
                                                    <span class="text-sm font-medium">Arraste e solte imagens</span>
                                                </div>
                                                <button type="button" id="post-select-file"
                                                    class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                                    Selecionar imagens
                                                </button>
                                            </div>
                                            <div class="text-xs text-gray-500" id="post-upload-name">Nenhuma imagem selecionada
                                            </div>
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
                                                        <a href="{{ route('social.profile', $post->user->email) }}"
                                                            class="rounded-full w-10 h-10 overflow-hidden flex-shrink-0 block">
                                                            <img src="{{ $postAvatar }}" alt="Avatar"
                                                                class="w-10 h-10 object-cover hover:opacity-80 transition"
                                                                onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                                        </a>
                                                        <div>
                                                            <a href="{{ route('social.profile', $post->user->email) }}"
                                                                class="font-bold text-gray-900 hover:text-blue-600 transition">
                                                                {{ $post->user->name }}
                                                            </a>
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
                                                                            {!! preg_replace(
                                '/(https?:\/\/[^\s<>"]+)/i',
                                '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline break-all">$1</a>',
                                nl2br(e($post->content))
                            ) !!}
                                                                        </div>

                                                                        @if($post->media->isNotEmpty())
                                                                            <div class="mb-4">
                                                                                @php
                                                                                    $mediaCount = $post->media->count();
                                                                                @endphp
                                                                                @if($mediaCount === 1)
                                                                                    <img src="{{ asset($post->media->first()->path) }}" alt="Midia do post"
                                                                                        class="w-full rounded-lg object-cover">
                                                                                @else
                                                                                    <div class="relative" data-carousel data-total="{{ $mediaCount }}">
                                                                                        <div class="overflow-hidden rounded-lg">
                                                                                            <div class="flex transition-transform duration-300" data-track>
                                                                                                @foreach($post->media as $media)
                                                                                                    <img src="{{ asset($media->path) }}" alt="Midia do post"
                                                                                                        class="w-full shrink-0 object-cover">
                                                                                                @endforeach
                                                                                            </div>
                                                                                        </div>
                                                                                        <button type="button" data-prev
                                                                                            class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow">
                                                                                            <i class="fas fa-chevron-left"></i>
                                                                                        </button>
                                                                                        <button type="button" data-next
                                                                                            class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow">
                                                                                            <i class="fas fa-chevron-right"></i>
                                                                                        </button>
                                                                                        <div class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded-full"
                                                                                            data-counter>
                                                                                            1/{{ $mediaCount }}
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @endif

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
                                                                                onclick="document.getElementById('comment-{{ $post->id }}').focus();"
                                                                                aria-label="Comentar">
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
                                                                                            <select name="target_user_id"
                                                                                                class="border border-gray-200 rounded px-3 py-2 text-sm">
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
                                                                                    <textarea name="reason" rows="2" required placeholder="Descreva o motivo da denuncia"
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
                                                                                            <img src="{{ $commentAvatar }}" alt="Avatar" class="w-8 h-8 object-cover"
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
                                                                            <form action="{{ route('social.post.comment', $post) }}" method="POST" class="mt-3 flex gap-2">
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
                                                                    @if($adsEnabled && $loop->iteration % $adsenseFrequency === 0)
                                                                        <div class="bg-white rounded-lg shadow p-4 ad-container">
                                                                            @if($hasAdsense)
                                                                                <ins class="adsbygoogle" style="display:block" data-ad-client="{{ $adsensePublisherId }}"
                                                                                    data-ad-slot="{{ $adsenseSlotId }}" data-ad-format="{{ $adsenseFormat }}"
                                                                                    data-full-width-responsive="true"></ins>
                                                                                <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
                                                                            @elseif(!empty($adsCode))
                                                                                {!! $adsCode !!}
                                                                            @endif
                                                                        </div>
                                                                    @endif
                        @empty
                                <div class="text-center py-10 text-gray-500">
                                    <p>Nenhum post ainda. Seja o primeiro a publicar!</p>
                                </div>
                            @endforelse

                            {{ $posts->links() }}
                        </div>

                        <div id="chat-panel" class="space-y-6" hidden>
                            <div class="bg-white rounded-lg shadow p-4">
                                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-100"
                                            id="conversation-avatar-wrap">
                                            <img id="conversation-avatar" src="{{ asset('img/default-user.svg') }}" alt="Avatar"
                                                class="w-10 h-10 object-cover">
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900" id="conversation-name">Selecione uma conversa
                                            </p>
                                            <p class="text-xs text-gray-500" id="conversation-meta">Mensagens recentes</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="toggleConversationInfo()"
                                            class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-blue-600 rounded-full hover:bg-gray-100 transition"
                                            title="Informações da conversa">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                        <div class="relative">
                                            <button type="button" class="text-gray-400 hover:text-gray-600" title="Mais opções"
                                                onclick="togglePanel('conversation-menu-main')">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <div id="conversation-menu-main"
                                                class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10 py-1">
                                                <button type="button"
                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2">
                                                    <i class="fas fa-bell-slash"></i> Silenciar
                                                </button>
                                                <button type="button"
                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2 text-red-600">
                                                    <i class="fas fa-trash"></i> Excluir conversa
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="conversation-messages" class="h-[520px] overflow-y-auto space-y-3 pr-2">
                                    <p class="text-sm text-gray-500">Selecione um membro para ver a conversa.</p>
                                </div>
                                <form id="conversation-form" class="mt-4 flex items-center gap-2">
                                    <div class="relative">
                                        <button type="button" id="conversation-emoji-toggle"
                                            class="text-gray-500 hover:text-blue-600 p-2 rounded-full hover:bg-gray-100"
                                            title="Inserir emoji">
                                            <i class="far fa-smile"></i>
                                        </button>
                                        <div id="conversation-emoji-picker" data-target="conversation-input"
                                            class="emoji-picker-panel hidden absolute left-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg p-3 z-20">
                                            @include('social.partials.emoji_tabs')
                                            @include('social.partials.emoji_grid')
                                        </div>
                                    </div>
                                    <input type="text" id="conversation-input"
                                        class="flex-1 border border-gray-200 rounded-full px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Digite uma mensagem...">
                                    <button type="submit"
                                        class="bg-blue-600 text-white rounded-full w-10 h-10 flex items-center justify-center">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    @unless($isAdminContext)
                        <!-- Sidebar Right (Info) -->
                        <div class="hidden md:block md:col-span-3">
                            <div id="right-recommendations" class="bg-white rounded-lg shadow p-4 sticky top-24">
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
                                                $pendingTime = $connection ? $connection->created_at->diffForHumans() : '';
                                            @endphp
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <a class="rounded-full w-10 h-10 overflow-hidden flex-shrink-0"
                                                        href="{{ route('social.profile', $user->id) }}">
                                                        <img src="{{ $user->profile_photo_url }}" alt="Avatar"
                                                            class="w-10 h-10 object-cover"
                                                            onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                                    </a>
                                                    <div>
                                                        <a href="{{ route('social.profile', $user->id) }}"
                                                            class="text-sm font-semibold text-gray-800 hover:text-blue-600">
                                                            {{ $user->name }}
                                                        </a>
                                                        <p class="text-xs text-gray-500">Membro</p>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    @if($isConnected)
                                                        <span class="text-xs text-gray-400">Conectado</span>
                                                    @elseif($isPending && $isRequester)
                                                        <div class="text-[11px] text-gray-400">Pendente {{ $pendingTime }}</div>
                                                        <button type="button" class="text-xs text-red-600 hover:text-red-700 font-medium"
                                                            onclick="cancelInvite({{ $user->id }})">
                                                            Cancelar
                                                        </button>
                                                    @elseif($isPending)
                                                        <div class="text-[11px] text-gray-400">Solicitacao recebida</div>
                                                        <button type="button" class="text-xs text-green-600 hover:text-green-700 font-medium"
                                                            onclick="acceptInvite({{ $user->id }})">
                                                            Aceitar
                                                        </button>
                                                    @else
                                                        <button type="button" class="text-xs text-blue-600 hover:text-blue-700 font-medium"
                                                            onclick="requestInvite({{ $user->id }})">
                                                            Conectar
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-xs text-gray-500">Sem recomendacoes no momento.</p>
                                    @endif
                                </div>
                            </div>
                            <div id="right-conversation-info" class="bg-white rounded-lg shadow p-4 sticky top-24 hidden">
                                <div class="flex flex-col items-center text-center mb-4">
                                    <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-100 mb-3">
                                        <img id="conversation-info-avatar" src="{{ asset('img/default-user.svg') }}" alt="Avatar"
                                            class="w-20 h-20 object-cover">
                                    </div>
                                    <p class="font-semibold text-gray-900" id="conversation-info-name">Selecione uma conversa</p>
                                    <p class="text-xs text-gray-500" id="conversation-info-status">Online recentemente</p>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center text-xs text-gray-500 mb-4">
                                    <button type="button" class="py-2 rounded-lg hover:bg-gray-100">
                                        <i class="fas fa-user text-gray-400"></i>
                                        <span class="block mt-1">Perfil</span>
                                    </button>
                                    <button type="button" class="py-2 rounded-lg hover:bg-gray-100">
                                        <i class="fas fa-bell text-gray-400"></i>
                                        <span class="block mt-1">Silenciar</span>
                                    </button>
                                    <button type="button" class="py-2 rounded-lg hover:bg-gray-100">
                                        <i class="fas fa-search text-gray-400"></i>
                                        <span class="block mt-1">Pesquisar</span>
                                    </button>
                                </div>
                                <div class="border-t border-gray-100 pt-3">
                                    <button type="button"
                                        class="w-full text-left text-sm font-semibold text-gray-700 flex items-center justify-between">
                                        Informacoes da conversa
                                        <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                                    </button>
                                </div>
                                <div class="border-t border-gray-100 pt-3">
                                    <button type="button"
                                        class="w-full text-left text-sm font-semibold text-gray-700 flex items-center justify-between">
                                        Personalizar conversa
                                        <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                                    </button>
                                </div>
                                <div class="border-t border-gray-100 pt-3">
                                    <button type="button"
                                        class="w-full text-left text-sm font-semibold text-gray-700 flex items-center justify-between">
                                        Midia e arquivos
                                        <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                                    </button>
                                    <div id="conversation-shared" class="mt-3 space-y-2 text-xs text-gray-500">
                                        <p>Sem itens compartilhados.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endunless
                </div>
            </div>
        </div>

        @unless($isAdminContext)
            <div id="floating-chat-dock" class="fixed bottom-4 right-4 z-50 flex items-end gap-3 flex-row-reverse">
                <div id="chat-overflow" class="relative hidden">
                    <button id="chat-overflow-toggle"
                        class="bg-white border border-gray-200 text-gray-700 rounded-full px-3 py-2 text-xs shadow hover:bg-gray-50">
                        +0
                    </button>
                    <div id="chat-overflow-list"
                        class="hidden absolute bottom-12 right-0 bg-white border border-gray-200 rounded-lg shadow p-2 w-56"></div>
                </div>
                <div id="chat-boxes" class="flex items-end gap-3 flex-row-reverse"></div>
            </div>
        @endunless
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
        const authUserId = {{ Auth::id() ?? 'null' }};
        const csrfToken = '{{ csrf_token() }}';

        const conversationList = document.getElementById('conversation-list');
        const conversationName = document.getElementById('conversation-name');
        const conversationMeta = document.getElementById('conversation-meta');
        const conversationAvatar = document.getElementById('conversation-avatar');
        const conversationMessages = document.getElementById('conversation-messages');
        const conversationForm = document.getElementById('conversation-form');
        const conversationInput = document.getElementById('conversation-input');
        const conversationShared = document.getElementById('conversation-shared');
        const conversationSearch = document.getElementById('conversation-search');
        const conversationInfoAvatar = document.getElementById('conversation-info-avatar');
        const conversationInfoName = document.getElementById('conversation-info-name');
        const conversationInfoStatus = document.getElementById('conversation-info-status');
        const feedPanel = document.getElementById('feed-panel');
        const chatPanel = document.getElementById('chat-panel');
        const sidebarButtons = document.querySelectorAll('nav [data-panel]');
        const conversationEmojiToggle = document.getElementById('conversation-emoji-toggle');
        const conversationEmojiPicker = document.getElementById('conversation-emoji-picker');
        const rightRecommendations = document.getElementById('right-recommendations');
        const rightConversationInfo = document.getElementById('right-conversation-info');
        const chatBoxes = document.getElementById('chat-boxes');
        const chatOverflow = document.getElementById('chat-overflow');
        const chatOverflowToggle = document.getElementById('chat-overflow-toggle');
        const chatOverflowList = document.getElementById('chat-overflow-list');
        const openChats = new Map();
        const chatOrder = [];
        const maxVisibleChats = 5;
        let chatPollTimer = null;
        let activeConversationUserId = null;
        let conversationPollTimer = null;
        const notifiedUsers = new Set();
        let hasActiveConversation = false;

        const startConversationPolling = () => {
            if (conversationPollTimer) {
                return;
            }

            conversationPollTimer = setInterval(() => {
                loadConversations();
                refreshActiveConversation();
            }, 8000);
        };

        const setConversationHeader = (name, photo) => {
            if (conversationName) {
                conversationName.textContent = name || 'Selecione uma conversa';
            }
            if (conversationMeta) {
                conversationMeta.textContent = name ? 'Conversando agora' : 'Mensagens recentes';
            }
            if (conversationAvatar) {
                conversationAvatar.src = photo || '/img/default-user.svg';
            }
            if (conversationInfoAvatar) {
                conversationInfoAvatar.src = photo || '/img/default-user.svg';
            }
            if (conversationInfoName) {
                conversationInfoName.textContent = name || 'Selecione uma conversa';
            }
            if (conversationInfoStatus) {
                conversationInfoStatus.textContent = name ? 'Online recentemente' : 'Sem conversa ativa';
            }
        };

        const setActivePanel = (panel) => {
            if (!feedPanel || !chatPanel) {
                return;
            }

            const showFeed = panel === 'feed';
            feedPanel.classList.toggle('hidden', !showFeed);
            chatPanel.classList.toggle('hidden', showFeed);
            feedPanel.toggleAttribute('hidden', !showFeed);
            chatPanel.toggleAttribute('hidden', showFeed);

            sidebarButtons.forEach((btn) => {
                const isActive = btn.getAttribute('data-panel') === panel;
                btn.classList.toggle('text-blue-600', isActive);
                btn.classList.toggle('font-medium', isActive);
                btn.classList.toggle('bg-blue-50', isActive);
                btn.classList.toggle('text-gray-600', !isActive);
            });

            if (rightRecommendations) {
                rightRecommendations.classList.toggle('hidden', panel !== 'feed');
            }
            if (rightConversationInfo) {
                const shouldShowInfo = panel === 'chat' && hasActiveConversation;
                rightConversationInfo.classList.toggle('hidden', !shouldShowInfo);
            }
        };

        sidebarButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const panel = btn.getAttribute('data-panel') || 'feed';
                setActivePanel(panel);

                // Se o painel for chat e não houver conversa ativa, tenta carregar as conversas e abrir a primeira
                if (panel === 'chat' && !activeConversationUserId) {
                    loadConversations(true);
                }
            });
        });

        const renderConversationMessages = (messages) => {
            if (!conversationMessages) {
                return;
            }

            if (!Array.isArray(messages) || messages.length === 0) {
                conversationMessages.innerHTML = '<p class="text-sm text-gray-500">Nenhuma mensagem ainda.</p>';
                return;
            }

            conversationMessages.innerHTML = '';
            const isImagePath = (path, type) => {
                if (type === 'image') {
                    return true;
                }
                return !!path && /\.(png|jpe?g|gif|webp)$/i.test(path);
            };

            messages.slice().reverse().forEach((msg) => {
                const wrap = document.createElement('div');
                wrap.className = `flex ${msg.is_mine ? 'justify-end' : 'justify-start'}`;
                const bubble = document.createElement('div');
                bubble.className = `text-sm px-3 py-2 rounded-2xl shadow ${msg.is_mine ? 'bg-blue-600 text-white' : 'bg-white text-gray-800 border border-gray-100'}`;
                bubble.style.maxWidth = '80%';

                if (msg.media_path) {
                    const mediaUrl = `/${msg.media_path.replace(/^\/+/, '')}`;
                    if (isImagePath(msg.media_path, msg.type)) {
                        const img = document.createElement('img');
                        img.src = mediaUrl;
                        img.alt = 'Compartilhado';
                        img.className = 'w-full rounded-lg mb-2';
                        bubble.appendChild(img);
                    } else {
                        const link = document.createElement('a');
                        link.href = mediaUrl;
                        link.target = '_blank';
                        link.rel = 'noopener';
                        link.className = 'block text-sm text-blue-600 underline mb-2';
                        link.textContent = 'Arquivo compartilhado';
                        bubble.appendChild(link);
                    }
                }

                const text = document.createElement('div');
                text.textContent = msg.content || '';
                bubble.appendChild(text);
                wrap.appendChild(bubble);
                conversationMessages.appendChild(wrap);
            });
            conversationMessages.scrollTop = conversationMessages.scrollHeight;
        };

        const renderSharedItems = (messages) => {
            if (!conversationShared) {
                return;
            }

            const shared = (Array.isArray(messages) ? messages : []).filter((msg) => msg.media_path);
            if (!shared.length) {
                conversationShared.innerHTML = '<p>Sem itens compartilhados.</p>';
                return;
            }

            conversationShared.innerHTML = '';
            shared.slice(0, 6).forEach((msg) => {
                const mediaUrl = `/${msg.media_path.replace(/^\/+/, '')}`;
                if (msg.type === 'image' || /\.(png|jpe?g|gif|webp)$/i.test(msg.media_path)) {
                    const item = document.createElement('img');
                    item.src = mediaUrl;
                    item.alt = 'Compartilhado';
                    item.className = 'w-full rounded border border-gray-200';
                    conversationShared.appendChild(item);
                } else {
                    const link = document.createElement('a');
                    link.href = mediaUrl;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.className = 'block text-xs text-blue-600 underline';
                    link.textContent = 'Arquivo compartilhado';
                    conversationShared.appendChild(link);
                }
            });
        };

        const refreshActiveConversation = () => {
            if (!activeConversationUserId) {
                return;
            }

            fetch(`/chat/with/${activeConversationUserId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then((r) => {
                    if (!r.ok) throw new Error('Erro');
                    return r.json();
                })
                .then((data) => {
                    renderConversationMessages(data.messages || []);
                    renderSharedItems(data.messages || []);
                })
                .catch(() => {
                    return;
                });
        };

        const openConversation = (userId, userName, userPhoto) => {
            if (!userId) {
                return;
            }

            activeConversationUserId = userId;
            hasActiveConversation = true;
            setConversationHeader(userName, userPhoto);
            setActivePanel('chat');
            if (conversationMessages) {
                conversationMessages.innerHTML = '<p class="text-sm text-gray-500">Carregando mensagens...</p>';
            }

            fetch(`/chat/with/${userId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then((r) => {
                    if (!r.ok) throw new Error('Erro');
                    return r.json();
                })
                .then((data) => {
                    renderConversationMessages(data.messages || []);
                    renderSharedItems(data.messages || []);
                })
                .catch(() => {
                    if (conversationMessages) {
                        conversationMessages.innerHTML = '<p class="text-sm text-red-500">Erro ao carregar conversa.</p>';
                    }
                });
        };

        if (conversationForm) {
            conversationForm.addEventListener('submit', (event) => {
                event.preventDefault();
                if (!conversationInput || !activeConversationUserId) {
                    return;
                }

                const message = conversationInput.value.trim();
                if (!message) {
                    return;
                }

                fetch(`/chat/with/${activeConversationUserId}/message`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ message })
                })
                    .then((r) => r.json())
                    .then((data) => {
                        if (data.success) {
                            conversationInput.value = '';
                            openConversation(activeConversationUserId, conversationName?.textContent || '', conversationAvatar?.src || '');
                        } else {
                            toastr.error(data.message || 'Erro ao enviar mensagem');
                        }
                    })
                    .catch(() => {
                        toastr.error('Erro ao enviar mensagem');
                    });
            });
        }

        if (conversationSearch) {
            const escapeHtml = (value) => {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            };

            const escapeRegex = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

            const highlightText = (text, term) => {
                if (!term) {
                    return escapeHtml(text);
                }
                const safe = escapeRegex(term);
                return escapeHtml(text).replace(new RegExp(`(${safe})`, 'gi'), '<mark class="bg-blue-100 text-blue-900 px-0.5 rounded">$1</mark>');
            };

            const applyConversationFilter = () => {
                const term = conversationSearch.value.trim().toLowerCase();
                const activeFilter = document.querySelector('#conversation-filters .filter-btn.bg-blue-50')?.getAttribute('data-filter') || 'all';

                document.querySelectorAll('[data-conversation-row]').forEach((row) => {
                    const rawName = row.getAttribute('data-name') || '';
                    const rawMessage = row.getAttribute('data-message') || '';
                    const name = rawName.toLowerCase();
                    const message = rawMessage.toLowerCase();
                    const isUnread = row.getAttribute('data-unread') === 'true';
                    const isGroup = row.getAttribute('data-is-group') === 'true';

                    const matchesSearch = !term || name.includes(term) || message.includes(term);
                    let matchesFilter = true;

                    if (activeFilter === 'unread') {
                        matchesFilter = isUnread;
                    } else if (activeFilter === 'groups') {
                        matchesFilter = isGroup;
                    }

                    const matches = matchesSearch && matchesFilter;
                    row.classList.toggle('hidden', !matches);

                    const nameNode = row.querySelector('[data-role="conversation-name"]');
                    const messageNode = row.querySelector('[data-role="conversation-message"]');
                    if (nameNode) {
                        nameNode.innerHTML = highlightText(rawName, term);
                    }
                    if (messageNode) {
                        messageNode.innerHTML = highlightText(rawMessage, term);
                    }
                });
            };

            // Filter buttons logic
            const filterContainer = document.getElementById('conversation-filters');
            if (filterContainer) {
                filterContainer.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        filterContainer.querySelectorAll('.filter-btn').forEach(b => {
                            b.classList.remove('bg-blue-50', 'text-blue-600');
                            b.classList.add('hover:bg-gray-100');
                        });
                        btn.classList.add('bg-blue-50', 'text-blue-600');
                        btn.classList.remove('hover:bg-gray-100');
                        applyConversationFilter();
                    });
                });
            }

            conversationSearch.addEventListener('input', applyConversationFilter);
            conversationSearch.addEventListener('search', applyConversationFilter);
            conversationSearch.addEventListener('keyup', (event) => {
                if (event.key === 'Escape') {
                    conversationSearch.value = '';
                    applyConversationFilter();
                }
            });

            conversationSearch.dataset.boundFilter = 'true';
            window.applyConversationFilter = applyConversationFilter;
        }

        if (conversationList && authUserId) {
            loadConversations();
            startConversationPolling();
        }

        function loadConversations(autoOpenFirst = false) {
            if (!conversationList || !authUserId) {
                return;
            }

            if (conversationList.innerHTML === '') {
                conversationList.innerHTML = '<p class="text-xs text-gray-500 p-2">Carregando conversas...</p>';
            }

            fetch('{{ route('chat.list') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then((r) => {
                    if (!r.ok) throw new Error('Erro ao carregar');
                    return r.json();
                })
                .then((conversations) => {
                    const activeUserIdString = activeConversationUserId ? String(activeConversationUserId) : null;

                    if (!Array.isArray(conversations) || conversations.length === 0) {
                        conversationList.innerHTML = '<p class="text-xs text-gray-500">Nenhuma conversa iniciada.</p>';
                        return;
                    }

                    // Patching DOM to avoid flickering
                    const currentRows = Array.from(conversationList.querySelectorAll('[data-conversation-row]'));
                    const currentIds = currentRows.map(r => r.getAttribute('data-user-id'));
                    const newIds = conversations.map(conv => {
                        const other = (conv.users || []).find((u) => u.id !== authUserId) || (conv.users || [])[0];
                        return other ? String(other.id) : null;
                    }).filter(id => id);

                    // Remove items not in new list
                    currentRows.forEach(row => {
                        if (!newIds.includes(row.getAttribute('data-user-id'))) {
                            row.remove();
                        }
                    });

                    let firstUser = null;
                    conversations.forEach((conv, index) => {
                        const otherUser = (conv.users || []).find((u) => u.id !== authUserId) || (conv.users || [])[0];
                        if (!otherUser) return;

                        const userIdStr = String(otherUser.id);
                        const lastMessage = conv.messages && conv.messages.length ? conv.messages[0].body : 'Nova conversa';
                        const avatar = otherUser.profile_photo_url || otherUser.photo || null;

                        if (!firstUser) {
                            firstUser = { id: otherUser.id, name: otherUser.name, avatar };
                        }

                        let row = conversationList.querySelector(`[data-user-id="${userIdStr}"]`);
                        const isActive = userIdStr === activeUserIdString;

                        if (!row) {
                            row = document.createElement('button');
                            row.type = 'button';
                            row.setAttribute('data-conversation-row', 'true');
                            row.setAttribute('data-user-id', userIdStr);
                            conversationList.appendChild(row);
                        }

                        // Update attributes
                        row.className = `w-full text-left flex items-center gap-3 p-2 rounded transition ${isActive ? 'bg-blue-50' : 'hover:bg-blue-50'}`;
                        row.setAttribute('data-name', otherUser.name || '');
                        row.setAttribute('data-message', lastMessage || '');
                        row.setAttribute('data-unread', (conv.unread_count || 0) > 0 ? 'true' : 'false');
                        row.setAttribute('data-is-group', conv.type === 'group' ? 'true' : 'false');

                        // Update inner HTML only if content changed or if it was empty
                        const unreadHtml = (conv.unread_count || 0) > 0 ? `<span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">${conv.unread_count}</span>` : '';
                        const contentHtml = `
                                    <img src="${avatar || '/img/default-user.svg'}" class="w-10 h-10 rounded-full object-cover" onerror="this.onerror=null;this.src='/img/default-user.svg';" />
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-gray-800 truncate" data-role="conversation-name">${otherUser.name}</p>
                                            ${unreadHtml}
                                        </div>
                                        <p class="text-xs text-gray-500 truncate" data-role="conversation-message">${lastMessage}</p>
                                    </div>
                                `;

                        if (row.innerHTML !== contentHtml) {
                            row.innerHTML = contentHtml;
                        }

                        // Re-bind click if new or ensure correct data
                        row.onclick = () => openConversation(otherUser.id, otherUser.name, avatar);

                        if (conv.unread_count === 0) {
                            notifiedUsers.delete(otherUser.id);
                        }

                        if ((conv.unread_count || 0) > 0 && userIdStr !== activeUserIdString && !openChats.has(otherUser.id) && !notifiedUsers.has(otherUser.id)) {
                            notifiedUsers.add(otherUser.id);
                            openMultiChat(otherUser.id, otherUser.name, avatar);
                        }
                    });

                    if (!activeConversationUserId && firstUser && autoOpenFirst) {
                        openConversation(firstUser.id, firstUser.name, firstUser.avatar);
                    }

                    if (window.applyConversationFilter && conversationSearch && conversationSearch.value.trim() !== '') {
                        window.applyConversationFilter();
                    }
                })
                .catch(() => {
                    conversationList.innerHTML = '<p class="text-xs text-red-500">Erro ao carregar conversas.</p>';
                });
        }

        const startChatPolling = () => {
            if (chatPollTimer) {
                return;
            }

            chatPollTimer = setInterval(() => {
                openChats.forEach((chat) => {
                    loadChatMessages(chat.userId, chat, false);
                });
            }, 5000);
        };

        const stopChatPolling = () => {
            if (chatPollTimer && openChats.size === 0) {
                clearInterval(chatPollTimer);
                chatPollTimer = null;
            }
        };

        const refreshChatDock = () => {
            if (!chatBoxes) {
                return;
            }

            const visibleIds = chatOrder.slice(-maxVisibleChats);
            const hiddenIds = chatOrder.slice(0, Math.max(0, chatOrder.length - maxVisibleChats));

            openChats.forEach((chat, userId) => {
                chat.element.classList.toggle('hidden', !visibleIds.includes(userId));
            });

            if (chatOverflow && chatOverflowToggle && chatOverflowList) {
                if (hiddenIds.length > 0) {
                    chatOverflow.classList.remove('hidden');
                    chatOverflowToggle.textContent = `+${hiddenIds.length}`;
                    chatOverflowList.innerHTML = '';
                    hiddenIds.forEach((userId) => {
                        const chat = openChats.get(userId);
                        if (!chat) {
                            return;
                        }
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'w-full text-left text-sm text-gray-700 px-2 py-1 rounded hover:bg-gray-100';
                        item.textContent = chat.userName;
                        item.addEventListener('click', () => {
                            bringChatToFront(userId);
                            chatOverflowList.classList.add('hidden');
                        });
                        chatOverflowList.appendChild(item);
                    });
                } else {
                    chatOverflow.classList.add('hidden');
                    chatOverflowList.innerHTML = '';
                }
            }
        };

        if (chatOverflowToggle && chatOverflowList) {
            chatOverflowToggle.addEventListener('click', () => {
                chatOverflowList.classList.toggle('hidden');
            });
        }

        document.addEventListener('click', (event) => {
            if (!chatOverflowList || chatOverflowList.classList.contains('hidden')) {
                return;
            }

            const isOverflow = chatOverflow && chatOverflow.contains(event.target);
            if (!isOverflow) {
                chatOverflowList.classList.add('hidden');
            }
        });

        const bringChatToFront = (userId) => {
            const index = chatOrder.indexOf(userId);
            if (index !== -1) {
                chatOrder.splice(index, 1);
            }
            chatOrder.push(userId);
            refreshChatDock();
        };

        const closeChat = (userId) => {
            const chat = openChats.get(userId);
            if (!chat) {
                return;
            }

            chat.element.remove();
            openChats.delete(userId);
            const index = chatOrder.indexOf(userId);
            if (index !== -1) {
                chatOrder.splice(index, 1);
            }
            refreshChatDock();
            stopChatPolling();
        };

        const toggleChatMinimize = (userId) => {
            const chat = openChats.get(userId);
            if (!chat) {
                return;
            }
            chat.body.classList.toggle('hidden');
            chat.footer.classList.toggle('hidden');
        };

        const createChatBox = (userId, userName, userPhoto) => {
            if (!chatBoxes) {
                return null;
            }

            const chat = document.createElement('div');
            chat.className = 'bg-white w-72 rounded-xl shadow-xl border border-gray-200 flex flex-col';
            chat.innerHTML = `
                            <div class="bg-[#1F5EDB] text-white px-3 py-2 rounded-t-xl flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <img src="${userPhoto || '/img/default-user.svg'}" class="w-7 h-7 rounded-full object-cover" onerror="this.onerror=null;this.src='/img/default-user.svg';" />
                                    <span class="text-sm font-semibold truncate" style="max-width: 140px;">${userName}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="text-white text-xs" data-action="minimize"><i class="fas fa-minus"></i></button>
                                    <button type="button" class="text-white text-xs" data-action="close"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <div class="p-3 bg-slate-50 space-y-2 overflow-y-auto" style="height: 220px;"></div>
                            <div class="border-t border-gray-200 p-2 bg-white rounded-b-xl">
                                <form class="flex items-center gap-2">
                                    <input type="text" class="flex-1 border border-gray-200 rounded-full px-3 py-1 text-sm" placeholder="Digite...">
                                    <button type="submit" class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                        <i class="fas fa-paper-plane text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        `;

            const body = chat.children[1];
            const footer = chat.children[2];
            const form = footer.querySelector('form');
            const input = footer.querySelector('input');
            chat.querySelector('[data-action="close"]').addEventListener('click', () => closeChat(userId));
            chat.querySelector('[data-action="minimize"]').addEventListener('click', () => toggleChatMinimize(userId));

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const message = input.value.trim();
                if (!message) {
                    return;
                }
                appendChatMessage(body, message, true);
                input.value = '';

                fetch(`/chat/with/${userId}/message`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ message })
                }).then((r) => r.json())
                    .then((data) => {
                        if (!data.success) {
                            toastr.error(data.message || 'Erro ao enviar mensagem');
                        }
                    })
                    .catch(() => {
                        toastr.error('Erro ao enviar mensagem');
                    });
            });

            chatBoxes.appendChild(chat);
            return { element: chat, body, footer, userId, userName };
        };

        const appendChatMessage = (container, content, isMine) => {
            const bubble = document.createElement('div');
            bubble.className = `text-sm px-3 py-2 rounded-2xl shadow ${isMine ? 'bg-blue-600 text-white ml-auto' : 'bg-white text-gray-800 border border-gray-100'}`;
            bubble.style.maxWidth = '80%';
            bubble.textContent = content;
            const wrap = document.createElement('div');
            wrap.className = `flex ${isMine ? 'justify-end' : 'justify-start'}`;
            wrap.appendChild(bubble);
            container.appendChild(wrap);
            container.scrollTop = container.scrollHeight;
        };

        const loadChatMessages = (userId, chat, scroll = true) => {
            fetch(`/chat/with/${userId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then((r) => r.json())
                .then((data) => {
                    if (!data || !Array.isArray(data.messages)) {
                        return;
                    }

                    chat.body.innerHTML = '';
                    data.messages.reverse().forEach((msg) => {
                        appendChatMessage(chat.body, msg.content, msg.is_mine);
                    });
                    if (scroll) {
                        chat.body.scrollTop = chat.body.scrollHeight;
                    }
                });
        };

        const toggleConversationInfo = () => {
            if (!rightRecommendations || !rightConversationInfo) return;

            if (rightConversationInfo.classList.contains('hidden')) {
                rightRecommendations.classList.add('hidden');
                rightConversationInfo.classList.remove('hidden');
            } else {
                rightConversationInfo.classList.add('hidden');
                rightRecommendations.classList.remove('hidden');
            }
        };

        const showRecommendedForNewChat = () => {
            setActivePanel('chat'); // Garantir que está na aba de mensagens
            if (!rightRecommendations || !rightConversationInfo) return;

            rightConversationInfo.classList.add('hidden');
            rightRecommendations.classList.remove('hidden');

            // Focar na busca se houver
            if (conversationSearch) {
                conversationSearch.focus();
            }
        };

        window.toggleConversationInfo = toggleConversationInfo;
        window.showRecommendedForNewChat = showRecommendedForNewChat;

        function openMultiChat(userId, userName, userPhoto) {
            if (!userId) {
                return;
            }

            const existing = openChats.get(userId);
            if (existing) {
                bringChatToFront(userId);
                return;
            }

            const chat = createChatBox(userId, userName, userPhoto);
            if (!chat) {
                return;
            }

            openChats.set(userId, chat);
            chatOrder.push(userId);
            bringChatToFront(userId);
            loadChatMessages(userId, chat, true);
            startChatPolling();
        }

        function requestInvite(userId) {
            Swal.fire({
                title: 'Conectar com este membro?',
                text: 'Voce enviara uma solicitacao de conexao.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1F5EDB',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, conectar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                fetch(`/connect/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Enviado!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Ops!', data.message, 'warning');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Ops!', 'Erro ao conectar.', 'error');
                    });
            });
        }

        function cancelInvite(userId) {
            Swal.fire({
                title: 'Cancelar solicitacao?',
                text: 'Voce deseja cancelar o convite enviado?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, cancelar',
                cancelButtonText: 'Voltar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                fetch(`/connection/remove/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Cancelado!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Ops!', data.message, 'warning');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Ops!', 'Erro ao cancelar.', 'error');
                    });
            });
        }

        function acceptInvite(userId) {
            fetch(`/connection/accept/${userId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Conexao aceita!', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Ops!', data.message, 'warning');
                    }
                })
                .catch(() => {
                    Swal.fire('Ops!', 'Erro ao aceitar.', 'error');
                });
        }

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
        const maxPostImages = 5;

        const showUploadLimitAlert = (message) => {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Atenção', message, 'warning');
            } else if (typeof toastr !== 'undefined') {
                toastr.warning(message);
            } else {
                console.warn(message);
            }
        };

        const validatePostImages = (files) => {
            const count = files ? files.length : 0;
            if (count < 1) {
                showUploadLimitAlert('Selecione pelo menos 1 imagem para publicar.');
                return false;
            }
            if (count > maxPostImages) {
                showUploadLimitAlert(`Você pode enviar no máximo ${maxPostImages} imagens.`);
                return false;
            }
            return true;
        };

        const formatUploadName = (files) => {
            if (!files || !files.length) {
                return 'Nenhuma imagem selecionada';
            }

            if (files.length === 1) {
                return files[0].name;
            }

            return `${files.length} imagens selecionadas`;
        };

        const updateUploadName = (files) => {
            if (!uploadName) {
                return;
            }

            uploadName.textContent = formatUploadName(files);
        };

        const showPreview = (file, total) => {
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
                    const extra = total && total > 1 ? ` (+${total - 1})` : '';
                    previewInlineName.textContent = `${file.name}${extra}`;
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
                const files = postMedia.files;
                if (files && files.length > maxPostImages) {
                    postMedia.value = '';
                    updateUploadName(null);
                    clearPreview();
                    showUploadLimitAlert(`Você pode selecionar no máximo ${maxPostImages} imagens.`);
                    return;
                }
                const file = files && files.length ? files[0] : null;
                updateUploadName(files);
                if (file) {
                    showPreview(file, files.length);
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

                if (files.length > maxPostImages) {
                    showUploadLimitAlert(`Você pode enviar no máximo ${maxPostImages} imagens.`);
                    return;
                }

                const dataTransfer = new DataTransfer();
                Array.from(files).forEach((file) => dataTransfer.items.add(file));
                postMedia.files = dataTransfer.files;
                const file = postMedia.files[0];
                updateUploadName(postMedia.files);
                showPreview(file, postMedia.files.length);
            });
        }

        const initCarousels = () => {
            document.querySelectorAll('[data-carousel]').forEach((carousel) => {
                const track = carousel.querySelector('[data-track]');
                if (!track) {
                    return;
                }

                const total = parseInt(carousel.getAttribute('data-total') || track.children.length, 10);
                let index = parseInt(carousel.getAttribute('data-index') || '0', 10);
                const counter = carousel.querySelector('[data-counter]');
                const prev = carousel.querySelector('[data-prev]');
                const next = carousel.querySelector('[data-next]');

                const update = () => {
                    if (total <= 1) {
                        return;
                    }
                    if (index < 0) {
                        index = total - 1;
                    }
                    if (index >= total) {
                        index = 0;
                    }
                    track.style.transform = `translateX(-${index * 100}%)`;
                    if (counter) {
                        counter.textContent = `${index + 1}/${total}`;
                    }
                    carousel.setAttribute('data-index', String(index));
                };

                if (prev) {
                    prev.addEventListener('click', () => {
                        index -= 1;
                        update();
                    });
                }

                if (next) {
                    next.addEventListener('click', () => {
                        index += 1;
                        update();
                    });
                }

                update();
            });
        };

        initCarousels();

        if (postForm) {
            postForm.addEventListener('submit', (event) => {
                if (!postMedia || !validatePostImages(postMedia.files)) {
                    event.preventDefault();
                    return;
                }
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

        if (conversationEmojiToggle && conversationEmojiPicker) {
            initEmojiPicker(conversationEmojiToggle, conversationEmojiPicker);
        }

        document.querySelectorAll('.comment-emoji-toggle').forEach((toggle) => {
            const pickerId = toggle.getAttribute('data-picker');
            const picker = pickerId ? document.getElementById(pickerId) : null;
            initEmojiPicker(toggle, picker);
        });

        document.addEventListener('click', (event) => {
            if (event.target.closest('.emoji-picker-panel') || event.target.closest('.comment-emoji-toggle') || event.target.closest('#emoji-toggle') || event.target.closest('#conversation-emoji-toggle')) {
                return;
            }

            closeAllEmojiPickers();
        });

        setActivePanel('feed');
    </script>
@endpush