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
                            <button type="button" class="flex items-center gap-2 hover:text-blue-600 transition"
                                onclick="togglePanel('share-{{ $post->id }}')">
                                <i class="fas fa-share"></i> Compartilhar
                            </button>

                            @if(Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isAdmin()))
                                <form action="{{ route('social.post.destroy', $post) }}" method="POST"
                                        class="js-confirm-delete" data-confirm-title="Remover publicacao?"
                                        data-confirm-text="Esta acao nao pode ser desfeita.">
                                    @csrf
                                    @method('DELETE')
                                        <button type="submit" class="flex items-center gap-2 text-red-500 hover:text-red-700" title="Remover">
                                            <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
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



<?php $__env->startSection('title', $user->name . ' - Perfil UNN'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $shareTargets = $shareTargets ?? collect();
        $adsEnabled = $adsEnabled ?? false;
        $adsCode = $adsCode ?? '';
    ?>
    <div class="bg-gray-100 min-h-screen">
        <!-- Cover & Info -->
        <!-- Cover & Info -->
        <div class="bg-white shadow relative">
            <!-- Capa -->
            <div class="h-64 sm:h-80 w-full bg-gray-200 overflow-hidden relative group">
                <?php if(isset($user->cover_photo) && $user->cover_photo): ?>
                    <img src="<?php echo e(asset($user->cover_photo)); ?>" alt="Capa" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-r from-[#1F5EDB] to-[#0d3b96]"></div>
                <?php endif; ?>
            </div>

            <!-- Info do Perfil -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative pb-8">
                <div class="flex flex-col md:flex-row items-end -mt-20 sm:-mt-24 mb-4 gap-6 relative">

                    <!-- Avatar -->
                    <div class="flex-shrink-0 relative">
                        <div class="w-40 h-40 sm:w-48 sm:h-48 bg-white rounded-full p-1.5 shadow-xl">
                            <img src="<?php echo e($user->profile_photo_url); ?>"
                                class="w-full h-full rounded-full object-cover border-4 border-white" alt="Avatar"
                                onerror="this.onerror=null;this.src='<?php echo e(asset('img/default-user.svg')); ?>';">
                        </div>
                    </div>

                    <!-- Detalhes -->
                    <div class="flex-1 pb-2 w-full text-center md:text-left pt-16 md:pt-0">
                        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1"><?php echo e($user->name); ?></h1>

                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-gray-600 mb-4">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-calendar-alt text-[#1F5EDB]"></i> Membro desde
                                <?php echo e($user->created_at->format('M Y')); ?>

                            </span>
                            <?php if(isset($user->city) && $user->city): ?>
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-map-marker-alt text-[#1F5EDB]"></i> <?php echo e($user->city); ?>

                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Redes Sociais -->
                        <?php
                            $socialLinks = collect([
                                ['key' => 'linkedin', 'icon' => 'fab fa-linkedin', 'color' => '#0A66C2'],
                                ['key' => 'instagram', 'icon' => 'fab fa-instagram', 'color' => '#E4405F'],
                                ['key' => 'facebook', 'icon' => 'fab fa-facebook', 'color' => '#1877F2'],
                                ['key' => 'twitter', 'icon' => 'fab fa-twitter', 'color' => '#1DA1F2'],
                                ['key' => 'youtube', 'icon' => 'fab fa-youtube', 'color' => '#FF0000'],
                                ['key' => 'website', 'icon' => 'fas fa-globe', 'color' => '#10B981'],
                            ])->filter(fn($s) => isset($user->{$s['key']}) && $user->{$s['key']});
                        ?>

                        <?php if($socialLinks->isNotEmpty()): ?>
                            <div class="flex items-center justify-center md:justify-start gap-3 mt-2">
                                <?php $__currentLoopData = $socialLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $social): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e($user->{$social['key']}); ?>" target="_blank" rel="noopener"
                                        class="w-10 h-10 flex items-center justify-center bg-gray-50 rounded-full hover:shadow-md transition transform hover:-translate-y-1"
                                        style="color: <?php echo e($social['color']); ?>" title="<?php echo e(ucfirst($social['key'])); ?>">
                                        <i class="<?php echo e($social['icon']); ?> text-xl"></i>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Ações -->
                    <div class="flex gap-3 pb-4 w-full md:w-auto justify-center md:justify-end">
                        <?php if(auth()->check() && auth()->id() !== $user->id): ?>
                            <?php
                                $isConnected = auth()->user()->isConnectedWith($user->id);
                                $pendingConnection = auth()->user()->hasPendingConnectionWith($user->id);
                                $isRequester = $pendingConnection && $pendingConnection->requester_id === auth()->id();
                                $canMessage = auth()->user()->canMessageUser($user);
                                $showPending = $pendingConnection && !$isConnected;
                            ?>

                            <?php if($isConnected): ?>
                                <button type="button" onclick="removeConnection(<?php echo e($user->id); ?>)"
                                    class="bg-gray-200 text-gray-700 w-12 h-12 rounded-full font-bold hover:bg-gray-300 transition shadow flex items-center justify-center"
                                    title="Conectado" aria-label="Conectado">
                                    <i class="fas fa-user-check text-green-600"></i>
                                </button>
                            <?php endif; ?>

                            <?php if($canMessage): ?>
                                <button type="button"
                                    onclick="openProfileChat(<?php echo e($user->id); ?>, '<?php echo e($user->name); ?>', '<?php echo e($user->profile_photo_url); ?>')"
                                    class="bg-[#1F5EDB] text-white w-12 h-12 rounded-full font-bold hover:bg-blue-700 transition shadow-lg hover:shadow-xl flex items-center justify-center"
                                    title="Mensagem" aria-label="Mensagem">
                                    <i class="fas fa-comment-dots"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" onclick="showMessageBlocked()"
                                    class="bg-[#1F5EDB] text-white w-12 h-12 rounded-full font-bold hover:bg-blue-700 transition shadow-lg hover:shadow-xl flex items-center justify-center"
                                    title="Mensagem" aria-label="Mensagem">
                                    <i class="fas fa-comment-dots"></i>
                                </button>
                            <?php endif; ?>

                            <?php if($showPending): ?>
                                <?php if($isRequester): ?>
                                    <button type="button"
                                        class="bg-gray-200 text-gray-500 w-12 h-12 rounded-full font-bold cursor-not-allowed shadow flex items-center justify-center"
                                        title="Pendente" aria-label="Pendente">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" onclick="acceptConnection(<?php echo e($user->id); ?>)"
                                        class="bg-green-600 text-white w-12 h-12 rounded-full font-bold hover:bg-green-700 transition shadow-lg flex items-center justify-center"
                                        title="Aceitar conexao" aria-label="Aceitar conexao">
                                        <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>
                            <?php elseif(!$isConnected): ?>
                                <button type="button" onclick="requestConnection(<?php echo e($user->id); ?>)"
                                    class="bg-[#1F5EDB] text-white w-12 h-12 rounded-full font-bold hover:bg-blue-700 transition shadow-lg hover:shadow-xl flex items-center justify-center"
                                    id="btn-connect-<?php echo e($user->id); ?>" title="Conectar" aria-label="Conectar">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $__env->startPush('scripts'); ?>
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
                            const originalContent = btn ? btn.innerHTML : '';
                            if (btn) {
                                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                                btn.disabled = true;
                            }

                            fetch(`/connect/${userId}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
                                        if (btn) {
                                            btn.innerHTML = originalContent;
                                            btn.disabled = false;
                                        }
                                    }
                                })
                                .catch(() => {
                                    toastr.error('Erro ao conectar.');
                                    if (btn) {
                                        btn.innerHTML = originalContent;
                                        btn.disabled = false;
                                    }
                                });
                        }
                    });
                }

                function showMessageBlocked() {
                    Swal.fire({
                        title: 'Conexao necessaria',
                        text: 'Conecte-se a este membro para enviar mensagens.',
                        icon: 'info',
                        confirmButtonColor: '#1F5EDB',
                        confirmButtonText: 'Ok'
                    });
                }

                function openProfileChat(userId, userName, userPhoto) {
                    if (typeof window.openChatBox === 'function') {
                        window.openChatBox(userId, userName, userPhoto);
                        return;
                    }

                    window.location.href = '<?php echo e(route('chat.start', $user->id)); ?>';
                }

                function acceptConnection(userId) {
                    fetch(`/connection/accept/${userId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
                                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
                    });
                }
            </script>
        <?php $__env->stopPush(); ?>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Sidebar -->
            <div class="space-y-6">
                <?php if(Auth::check() && Auth::id() === $user->id && !empty($pendingRequests) && $pendingRequests->isNotEmpty()): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Solicitacoes de conexao</h3>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $pendingRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $requester = $request->requester;
                                ?>
                                <?php if($requester): ?>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-10 h-10 overflow-hidden flex-shrink-0">
                                                <img src="<?php echo e($requester->profile_photo_url); ?>" alt="Avatar"
                                                    class="w-10 h-10 object-cover"
                                                    onerror="this.onerror=null;this.src='<?php echo e(asset('img/default-user.svg')); ?>';">
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800"><?php echo e($requester->name); ?></p>
                                                <p class="text-xs text-gray-500">Solicitacao pendente</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="acceptConnection(<?php echo e($requester->id); ?>)"
                                                class="text-xs text-green-600 hover:text-green-700 font-medium" title="Aceitar">
                                                Aceitar
                                            </button>
                                            <button type="button" onclick="removeConnection(<?php echo e($requester->id); ?>)"
                                                class="text-xs text-red-600 hover:text-red-700 font-medium" title="Recusar">
                                                Recusar
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Sobre</h3>
                    <p class="text-gray-600 text-sm">
                        <?php echo e($user->bio ?? 'Sem descrição.'); ?>

                    </p>

                    <hr class="my-4">

                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt w-5 text-gray-400"></i>
                            <?php if($user->city || $user->state): ?>
                                <?php echo e($user->city); ?><?php echo e($user->city && $user->state ? ', ' : ''); ?><?php echo e($user->state); ?>

                            <?php else: ?>
                                Localização não informada
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-briefcase w-5 text-gray-400"></i>
                            <?php if($user->occupation || $user->company): ?>
                                <?php echo e($user->occupation); ?><?php echo e($user->occupation && $user->company ? ' em ' : ''); ?><?php echo e($user->company); ?>

                            <?php else: ?>
                                Cargo não informado
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="md:col-span-2 space-y-6">
                <h3 class="font-bold text-xl text-gray-800">Publicações</h3>

                <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="bg-white rounded-lg shadow p-4" id="post-<?php echo e($post->id); ?>">
                        <?php
                            $viewerId = Auth::id();
                            $hasLiked = $viewerId ? $post->reactions->firstWhere('user_id', $viewerId) : null;
                            $likeCount = $post->reactions->count();
                            $commentCount = $post->comments->count();
                            $postAvatar = $post->user->profile_photo_url ?? asset('img/default-user.svg');
                            $postLink = route('social.post.public', $post);
                        ?>
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                <a href="<?php echo e(route('social.profile', $post->user->email)); ?>" class="rounded-full w-10 h-10 overflow-hidden flex-shrink-0 block">
                                    <img src="<?php echo e($postAvatar); ?>" alt="Avatar" class="w-10 h-10 object-cover hover:opacity-80 transition"
                                        onerror="this.onerror=null;this.src='<?php echo e(asset('img/default-user.svg')); ?>';">
                                </a>
                                <div>
                                    <a href="<?php echo e(route('social.profile', $post->user->email)); ?>" class="font-bold text-gray-900 hover:text-blue-600 transition">
                                        <?php echo e($post->user->name); ?>

                                    </a>
                                    <p class="text-xs text-gray-500"><?php echo e($post->created_at->diffForHumans()); ?></p>
                                </div>
                            </div>
                            <div class="relative">
                                <button type="button" class="text-gray-400 hover:text-gray-600" title="Mais opcoes"
                                    onclick="togglePanel('menu-<?php echo e($post->id); ?>')">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div id="menu-<?php echo e($post->id); ?>"
                                    class="hidden absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-lg shadow z-10">
                                    <div class="py-1 text-sm text-gray-700">
                                        <?php if(Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isAdmin())): ?>
                                            <form action="<?php echo e(route('social.post.destroy', $post)); ?>" method="POST"
                                                class="js-confirm-delete" data-confirm-title="Remover publicacao?"
                                                data-confirm-text="Esta acao nao pode ser desfeita.">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="w-full text-left px-4 py-2 hover:bg-gray-50 text-red-600 flex items-center gap-2">
                                                    <i class="fas fa-trash"></i>
                                                    <span>Remover</span>
                                                </button>
                                            </form>
                                            <form action="<?php echo e(route('social.post.unpublish', $post)); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2">
                                                    <i class="fas fa-eye-slash"></i>
                                                    <span>Despublicar</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if(auth()->guard()->check()): ?>
                                            <form action="<?php echo e(route('social.post.hide', $post)); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2">
                                                    <i class="fas fa-eye"></i>
                                                    <span>Ocultar postagem</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if(auth()->guard()->check()): ?>
                                            <?php if(!(Auth::id() === $post->user_id || Auth::user()->isAdmin())): ?>
                                                <button type="button"
                                                    class="w-full text-left px-4 py-2 hover:bg-gray-50 text-red-600 flex items-center gap-2"
                                                    onclick="togglePanel('report-<?php echo e($post->id); ?>'); togglePanel('menu-<?php echo e($post->id); ?>');">
                                                    <i class="fas fa-flag"></i>
                                                    <span>Denunciar</span>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="prose max-w-none text-gray-800">
                            <?php echo preg_replace(
                                '/(https?:\/\/[^\s<>"]+)/i',
                                '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline break-all">$1</a>',
                                nl2br(e($post->content))
                            ); ?>

                        </div>

                        <?php if($post->media->isNotEmpty()): ?>
                            <div class="mt-3">
                                <?php
                                    $mediaCount = $post->media->count();
                                ?>
                                <?php if($mediaCount === 1): ?>
                                    <img src="<?php echo e(asset($post->media->first()->path)); ?>" alt="Midia do post"
                                        class="w-full rounded-lg object-cover">
                                <?php else: ?>
                                    <div class="relative" data-carousel data-total="<?php echo e($mediaCount); ?>">
                                        <div class="overflow-hidden rounded-lg">
                                            <div class="flex transition-transform duration-300" data-track>
                                                <?php $__currentLoopData = $post->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <img src="<?php echo e(asset($media->path)); ?>" alt="Midia do post"
                                                        class="w-full shrink-0 object-cover">
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                        <div class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded-full" data-counter>
                                            1/<?php echo e($mediaCount); ?>

                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center justify-between pt-3 mt-3 border-t text-sm text-gray-500">
                            <?php if(auth()->guard()->check()): ?>
                                <form action="<?php echo e(route('social.post.react', $post)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" aria-label="Curtir"
                                        class="flex items-center gap-2 transition <?php echo e($hasLiked ? 'text-blue-600' : 'hover:text-blue-600'); ?>">
                                        <i class="<?php echo e($hasLiked ? 'fas' : 'far'); ?> fa-thumbs-up"></i>
                                        <span class="hidden sm:inline">Curtir</span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="flex items-center gap-2" aria-label="Curtir">
                                    <i class="far fa-thumbs-up"></i>
                                    <span class="hidden sm:inline">Curtir</span>
                                </span>
                            <?php endif; ?>

                            <button type="button" class="flex items-center gap-2 hover:text-blue-600 transition"
                                onclick="document.getElementById('comment-<?php echo e($post->id); ?>').focus();" aria-label="Comentar">
                                <i class="far fa-comment"></i>
                                <span class="hidden sm:inline">Comentar</span>
                            </button>

                            <button type="button" class="flex items-center gap-2 hover:text-blue-600 transition"
                                onclick="togglePanel('share-<?php echo e($post->id); ?>')" aria-label="Compartilhar">
                                <i class="fas fa-share"></i>
                                <span class="hidden sm:inline">Compartilhar</span>
                            </button>
                        </div>

                        <div class="mt-2 text-xs text-gray-400 flex gap-3">
                            <span><?php echo e($likeCount); ?> curtida<?php echo e($likeCount === 1 ? '' : 's'); ?></span>
                            <span><?php echo e($commentCount); ?> comentario<?php echo e($commentCount === 1 ? '' : 's'); ?></span>
                        </div>

                        <div id="share-<?php echo e($post->id); ?>" class="hidden mt-3 border-t pt-3 space-y-3">
                            <div class="flex flex-wrap gap-3 text-sm">
                                <button type="button" class="text-blue-600" data-copy="<?php echo e($postLink); ?>"
                                    onclick="copyPostLink(this)">Copiar link</button>
                                <a class="text-green-600" target="_blank" rel="noopener"
                                    href="https://wa.me/?text=<?php echo e(urlencode($postLink)); ?>">WhatsApp</a>
                                <a class="text-blue-500" target="_blank" rel="noopener"
                                    href="https://t.me/share/url?url=<?php echo e(urlencode($postLink)); ?>">Telegram</a>
                                <a class="text-blue-700" target="_blank" rel="noopener"
                                    href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode($postLink)); ?>">Facebook</a>
                            </div>

                            <?php if(auth()->guard()->check()): ?>
                                <form action="<?php echo e(route('social.post.share', $post)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-sm text-gray-600 hover:text-blue-600">
                                        Compartilhar na comunidade
                                    </button>
                                </form>

                                <?php if($shareTargets->isNotEmpty()): ?>
                                    <form action="<?php echo e(route('social.post.share.user', $post)); ?>" method="POST"
                                        class="flex flex-col gap-2">
                                        <?php echo csrf_field(); ?>
                                        <div class="flex flex-wrap gap-2">
                                            <select name="target_user_id" class="border border-gray-200 rounded px-3 py-2 text-sm">
                                                <?php $__currentLoopData = $shareTargets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $target): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($target->id); ?>"><?php echo e($target->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <input type="text" name="message" placeholder="Mensagem opcional"
                                                class="flex-1 border border-gray-200 rounded px-3 py-2 text-sm">
                                            <button type="submit"
                                                class="bg-blue-600 text-white px-3 py-2 rounded text-sm">Enviar</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div id="report-<?php echo e($post->id); ?>" class="hidden mt-3 border-t pt-3">
                            <?php if(auth()->guard()->check()): ?>
                                <form action="<?php echo e(route('social.post.report', $post)); ?>" method="POST"
                                    class="flex flex-col gap-2">
                                    <?php echo csrf_field(); ?>
                                    <textarea name="reason" rows="2" required
                                        placeholder="Descreva o motivo da denuncia"
                                        class="border border-gray-200 rounded px-3 py-2 text-sm"></textarea>
                                    <button type="submit" class="text-sm text-red-600">Denunciar</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <?php if($post->comments->isNotEmpty()): ?>
                            <div class="mt-4 space-y-3">
                                <?php $__currentLoopData = $post->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $commentUser = $comment->user;
                                        $commentAvatar = $commentUser
                                            ? $commentUser->profile_photo_url
                                            : asset('img/default-user.svg');
                                        $commentName = $commentUser ? $commentUser->name : 'Usuario';
                                    ?>
                                    <div class="flex gap-3">
                                        <div class="rounded-full w-8 h-8 overflow-hidden flex-shrink-0">
                                            <img src="<?php echo e($commentAvatar); ?>" alt="Avatar"
                                                class="w-8 h-8 object-cover"
                                                onerror="this.onerror=null;this.src='<?php echo e(asset('img/default-user.svg')); ?>';">
                                        </div>
                                        <div class="bg-gray-50 rounded-lg px-3 py-2 w-full">
                                            <div class="flex justify-between items-center">
                                                <p class="text-xs font-semibold text-gray-700"><?php echo e($commentName); ?></p>
                                                <?php if(Auth::check() && (Auth::id() === $comment->user_id || Auth::id() === $post->user_id || Auth::user()->isAdmin())): ?>
                                                    <form action="<?php echo e(route('social.comment.destroy', $comment)); ?>" method="POST"
                                                            class="js-confirm-delete" data-confirm-title="Remover comentario?"
                                                            data-confirm-text="Esta acao nao pode ser desfeita.">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="text-xs text-red-500" title="Remover">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-sm text-gray-700"><?php echo e($comment->content); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>

                        <?php if(auth()->guard()->check()): ?>
                            <form action="<?php echo e(route('social.post.comment', $post)); ?>" method="POST"
                                class="mt-3 flex gap-2">
                                <?php echo csrf_field(); ?>
                                <input type="text" name="content" id="comment-<?php echo e($post->id); ?>"
                                    placeholder="Escreva um comentario..."
                                    class="flex-1 border border-gray-200 rounded-full px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <div class="relative">
                                    <button type="button" class="comment-emoji-toggle text-gray-500 hover:text-blue-600"
                                        data-picker="comment-emoji-<?php echo e($post->id); ?>" title="Inserir emoji">
                                        <i class="far fa-smile"></i>
                                    </button>
                                    <div id="comment-emoji-<?php echo e($post->id); ?>" data-target="comment-<?php echo e($post->id); ?>"
                                        class="emoji-picker-panel hidden absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg p-3 z-20">
                                        <?php echo $__env->make('social.partials.emoji_tabs', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                        <?php echo $__env->make('social.partials.emoji_grid', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                    </div>
                                </div>
                                <button type="submit"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm hover:bg-blue-700 transition">
                                    Enviar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <?php if(!empty($adsEnabled) && !empty($adsCode) && $loop->iteration % 3 === 0): ?>
                        <div class="bg-white rounded-lg shadow p-4">
                            <?php echo $adsCode; ?>

                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-10 text-gray-500 bg-white rounded-lg shadow">
                        <p>Nenhuma publicação ainda.</p>
                    </div>
                <?php endif; ?>
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
                <div class="relative">
                    <button type="button" class="emoji-toggle text-gray-500 hover:text-blue-600" data-picker="chat-emoji-picker"
                        title="Inserir emoji">
                        <i class="far fa-smile"></i>
                    </button>
                    <div id="chat-emoji-picker" data-target="chatInput"
                        class="emoji-picker-panel hidden absolute right-0 bottom-12 w-64 bg-white border border-gray-200 rounded-lg shadow-lg p-3 z-20">
                        <?php echo $__env->make('social.partials.emoji_tabs', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php echo $__env->make('social.partials.emoji_grid', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                </div>
                <button type="submit"
                    class="bg-[#1F5EDB] text-white px-5 py-2 rounded-full hover:bg-blue-700 transition font-medium">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Floating Chat Script -->
    <script src="<?php echo e(asset('js/floating-chat.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
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

        document.querySelectorAll('.comment-emoji-toggle, .emoji-toggle').forEach((toggle) => {
            const pickerId = toggle.getAttribute('data-picker');
            const picker = pickerId ? document.getElementById(pickerId) : null;
            initEmojiPicker(toggle, picker);
        });

        document.addEventListener('click', (event) => {
            if (event.target.closest('.emoji-picker-panel') || event.target.closest('.comment-emoji-toggle') || event.target.closest('.emoji-toggle')) {
                return;
            }

            closeAllEmojiPickers();
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\social\profile.blade.php ENDPATH**/ ?>