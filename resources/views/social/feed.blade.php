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
                                <a href="{{ $profileUrl }}"
                                    class="flex items-center gap-2 text-gray-600 hover:text-blue-600 p-2 rounded transition">
                                    <i class="fas fa-user w-6"></i> Meu Perfil
                                </a>
                                <a href="{{ route('courses.index') }}"
                                    class="flex items-center gap-2 text-gray-600 hover:text-blue-600 p-2 rounded transition">
                                    <i class="fas fa-graduation-cap w-6"></i> Cursos
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
                            <form action="{{ route('social.post.store') }}" method="POST" enctype="multipart/form-data">
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
                                <div class="flex flex-col gap-3 mt-3 pt-3 border-t">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex gap-2">
                                            <input type="file" name="media" id="post-media" accept="image/*" class="hidden">
                                            <button type="button"
                                                class="text-gray-500 hover:text-blue-600 p-2 rounded hover:bg-gray-100"
                                                title="Adicionar imagem"
                                                onclick="document.getElementById('post-media').click();">
                                                <i class="fas fa-image"></i>
                                            </button>
                                            <button type="button"
                                                class="text-gray-500 hover:text-blue-600 p-2 rounded hover:bg-gray-100"
                                                title="Inserir emoji"
                                                onclick="window.insertPostEmoji && window.insertPostEmoji();">
                                                <i class="fas fa-smile"></i>
                                            </button>
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
                                <div class="flex items-center gap-2">
                                    @if(Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isAdmin()))
                                        <form action="{{ route('social.post.destroy', $post) }}" method="POST"
                                            onsubmit="return confirm('Excluir esta publicacao?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" class="text-gray-400 hover:text-gray-600" title="Mais opcoes"
                                        onclick="togglePanel('report-{{ $post->id }}')">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
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

                                <button type="button" class="flex items-center gap-2 hover:text-blue-600 transition"
                                    onclick="document.getElementById('comment-{{ $post->id }}').focus();">
                                    <i class="far fa-comment"></i> Comentar
                                </button>

                                <button type="button" class="flex items-center gap-2 hover:text-blue-600 transition"
                                    onclick="togglePanel('share-{{ $post->id }}')">
                                    <i class="fas fa-share"></i> Compartilhar
                                </button>

                                @if(Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isAdmin()))
                                    <form action="{{ route('social.post.destroy', $post) }}" method="POST"
                                        onsubmit="return confirm('Excluir esta publicacao?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center gap-2 text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i> Excluir
                                        </button>
                                    </form>
                                @endif
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
                                                            onsubmit="return confirm('Excluir este comentario?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-xs text-red-500">Excluir</button>
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
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm hover:bg-blue-700 transition">
                                        Enviar
                                    </button>
                                </form>
                            @endauth
                        </div>
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
                            <!-- Placeholder -->
                            <p class="text-xs text-gray-500">Nenhum evento próximo.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

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

        window.insertPostEmoji = function () {
            const textarea = document.getElementById('post-content');
            if (!textarea) {
                return;
            }

            const emoji = '😊';
            const start = textarea.selectionStart || 0;
            const end = textarea.selectionEnd || 0;
            const value = textarea.value || '';

            textarea.value = value.slice(0, start) + emoji + value.slice(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
        };
    </script>
@endpush
