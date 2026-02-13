
<nav class="main-header navbar navbar-expand navbar-dark sidebar-dark-primary">
    <ul class="navbar-nav mr-auto align-items-center">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?php echo e(route('home')); ?>" class="nav-link" target="_blank" rel="noopener">Ver site</a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto align-items-center">
        <!-- Chat Icon -->
        <li class="nav-item dropdown mr-2">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-comments"></i>
                <?php if(isset($unreadMessagesCount) && $unreadMessagesCount > 0): ?>
                    <span class="badge badge-danger navbar-badge"><?php echo e($unreadMessagesCount); ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header"><?php echo e($unreadMessagesCount ?? 0); ?> Mensagens</span>
                <div class="dropdown-divider"></div>
                <?php if(isset($unreadMessagesGroups) && $unreadMessagesGroups->isNotEmpty()): ?>
                    <?php $__currentLoopData = $unreadMessagesGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('admin.chat.start', $group->user->id)); ?>" class="dropdown-item">
                            <div class="media">
                                <img src="<?php echo e($group->user->photo ? asset($group->user->photo) : asset('img/default-user.svg')); ?>"
                                    alt="User Avatar" class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        <?php echo e($group->user->name); ?>

                                        <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                    </h3>
                                    <p class="text-sm"><?php echo e($group->count); ?> nova(s) mensagem(ns)</p>
                                    <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i>
                                        <?php echo e($group->latest->created_at->diffForHumans()); ?></p>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <a href="#" class="dropdown-item">Nenhuma mensagem nova</a>
                <?php endif; ?>
                <div class="dropdown-divider"></div>
                <a href="<?php echo e(route('admin.chat.index')); ?>" class="dropdown-item dropdown-footer">Ver Todas as Mensagens</a>
            </div>
        </li>

        <!-- Bell Icon (Notifications) -->
        <li class="nav-item dropdown mr-2">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <?php if(isset($totalNotificationsCount) && $totalNotificationsCount > 0): ?>
                    <span class="badge badge-warning navbar-badge"><?php echo e($totalNotificationsCount); ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="max-height: 400px; overflow-y: auto;">
                <span class="dropdown-item dropdown-header"><?php echo e($totalNotificationsCount ?? 0); ?> Notificações</span>
                
                
                <?php if(isset($pendingReviews) && $pendingReviews->isNotEmpty()): ?>
                    <div class="dropdown-divider"></div>
                    <span class="dropdown-item dropdown-header text-info py-1">
                        <i class="fas fa-star-half-alt mr-1"></i> Avaliações Pendentes (<?php echo e($pendingReviewsCount); ?>)
                    </span>
                    <?php $__currentLoopData = $pendingReviews->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('admin.reviews.index')); ?>" class="dropdown-item">
                            <div class="media">
                                <img src="<?php echo e($review->user->photo ? asset($review->user->photo) : asset('img/default-user.svg')); ?>"
                                    class="img-size-50 mr-3 img-circle" alt="">
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        <?php echo e(\Illuminate\Support\Str::limit($review->user->name ?? 'Usuário', 15)); ?>

                                    </h3>
                                    <p class="text-sm text-muted mb-0">
                                        <?php echo e($review->rating); ?>/5 estrelas
                                        <?php if($review->reviewable): ?>
                                            em <strong><?php echo e(\Illuminate\Support\Str::limit($review->reviewable->title ?? '', 20)); ?></strong>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-muted">
                                        <i class="far fa-clock mr-1"></i><?php echo e($review->created_at->diffForHumans()); ?>

                                    </p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($pendingReviewsCount > 3): ?>
                        <a href="<?php echo e(route('admin.reviews.index')); ?>" class="dropdown-item dropdown-footer text-info">
                            Ver todas as <?php echo e($pendingReviewsCount); ?> avaliações
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                
                <?php if(isset($pendingTestimonials) && $pendingTestimonials->isNotEmpty()): ?>
                    <div class="dropdown-divider"></div>
                    <span class="dropdown-item dropdown-header text-success py-1">
                        <i class="fas fa-quote-left mr-1"></i> Depoimentos Pendentes (<?php echo e($pendingTestimonialsCount); ?>)
                    </span>
                    <?php $__currentLoopData = $pendingTestimonials->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $testimonial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('admin.testimonials.index')); ?>" class="dropdown-item">
                            <div class="media">
                                <img src="<?php echo e($testimonial->user && $testimonial->user->photo ? asset($testimonial->user->photo) : asset('img/default-user.svg')); ?>"
                                    class="img-size-50 mr-3 img-circle" alt="">
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        <?php echo e(\Illuminate\Support\Str::limit($testimonial->author_name ?? ($testimonial->user->name ?? 'Anônimo'), 15)); ?>

                                    </h3>
                                    <p class="text-sm text-muted mb-0">
                                        <?php echo e(\Illuminate\Support\Str::limit($testimonial->content, 40)); ?>

                                    </p>
                                    <p class="text-xs text-muted">
                                        <i class="far fa-clock mr-1"></i><?php echo e($testimonial->created_at->diffForHumans()); ?>

                                    </p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($pendingTestimonialsCount > 3): ?>
                        <a href="<?php echo e(route('admin.testimonials.index')); ?>" class="dropdown-item dropdown-footer text-success">
                            Ver todos os <?php echo e($pendingTestimonialsCount); ?> depoimentos
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                
                <?php if(isset($pendingConnections) && $pendingConnections->isNotEmpty()): ?>
                    <div class="dropdown-divider"></div>
                    <span class="dropdown-item dropdown-header text-warning py-1">
                        <i class="fas fa-user-plus mr-1"></i> Conexões Pendentes (<?php echo e($pendingConnectionsCount); ?>)
                    </span>
                    <?php $__currentLoopData = $pendingConnections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="dropdown-item">
                            <div class="media">
                                <img src="<?php echo e($conn->requester->photo ? asset($conn->requester->photo) : asset('img/default-user.svg')); ?>"
                                    class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        <?php echo e(\Illuminate\Support\Str::limit($conn->requester->name, 15)); ?>

                                    </h3>
                                    <p class="text-sm">Solicitou conexão</p>
                                    <div class="mt-2 text-right">
                                        <button onclick="acceptConnection(<?php echo e($conn->requester_id); ?>)"
                                            class="btn btn-xs btn-success" title="Aceitar"><i class="fas fa-check"></i></button>
                                        <button onclick="removeConnection(<?php echo e($conn->requester_id); ?>)"
                                            class="btn btn-xs btn-secondary" title="Recusar"><i
                                                class="fas fa-times"></i></button>
                                        <button onclick="blockConnection(<?php echo e($conn->requester_id); ?>)"
                                            class="btn btn-xs btn-danger" title="Bloquear"><i class="fas fa-ban"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>

                
                <?php if((!isset($pendingReviews) || $pendingReviews->isEmpty()) && (!isset($pendingTestimonials) || $pendingTestimonials->isEmpty()) && (!isset($pendingConnections) || $pendingConnections->isEmpty())): ?>
                    <div class="dropdown-divider"></div>
                    <span class="dropdown-item text-center text-muted">Sem novas notificações</span>
                <?php endif; ?>
            </div>
        </li>

        <li class="nav-item mr-2">
            <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button" title="Painel rápido">
                <i class="fas fa-sliders-h"></i>
            </a>
        </li>

        <li class="nav-item mr-2 d-flex align-items-center">
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>" id="themeToggleForm" class="m-0 p-0">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="site_theme" id="site_theme_input"
                    value="<?php echo e($settings['site_theme'] ?? 'light'); ?>">
                <button type="button" class="btn btn-sm btn-outline-light d-flex align-items-center" id="themeToggleBtn"
                    title="Alternar tema">
                    <i class="fas <?php echo e(($settings['site_theme'] ?? 'light') === 'dark' ? 'fa-sun' : 'fa-moon'); ?>"></i>
                </button>
            </form>
        </li>
        <?php if(auth()->guard()->check()): ?>
            <li class="nav-item dropdown">
                <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" aria-expanded="false">
                    <?php if(auth()->user()->photo): ?>
                        <img src="<?php echo e(asset(auth()->user()->photo)); ?>" alt="User" class="img-circle mr-2"
                            style="width:30px;height:30px;object-fit:cover;">
                    <?php else: ?>
                        <i class="fas fa-user-circle mr-1" style="font-size: 1.5rem;"></i>
                    <?php endif; ?>
                    <span class="d-none d-md-inline"><?php echo e(auth()->user()->name); ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <span class="dropdown-item-text text-muted text-sm text-center font-weight-bold">
                        <?php echo e((auth()->user()->role ?? 'user') === 'superadmin' ? 'Superadmin' : (auth()->user()->role === 'admin' ? 'Admin' : 'Membro')); ?>

                    </span>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo e(route('admin.profile.edit')); ?>" class="dropdown-item">
                        <i class="fas fa-id-card mr-2 text-primary"></i> Meu Perfil
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="m-0 p-0">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Sair
                        </button>
                    </form>
                </div>
            </li>
        <?php endif; ?>
    </ul>
    <?php $__env->startPush('scripts'); ?>
        <script>
            function acceptConnection(userId) {
                fetch(`/connection/accept/${userId}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Content-Type': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { toastr.success(data.message); location.reload(); }
                        else { toastr.error(data.message); }
                    });
            }
            function removeConnection(userId) {
                Swal.fire({
                    title: 'Recusar conexão?',
                    text: "Este usuário não será adicionado às suas conexões.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, recusar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/connection/remove/${userId}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Content-Type': 'application/json' } })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) { toastr.info(data.message); location.reload(); }
                                else { toastr.error(data.message); }
                            });
                    }
                });
            }
            function blockConnection(userId) {
                Swal.fire({
                    title: 'Bloquear usuário?',
                    text: "Ele não poderá mais solicitar conexão.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, bloquear!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/connection/block/${userId}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Content-Type': 'application/json' } })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) { toastr.warning(data.message); location.reload(); }
                                else { toastr.error(data.message); }
                            });
                    }
                });
            }
        </script>
    <?php $__env->stopPush(); ?>
</nav>
<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\partials\navbar.blade.php ENDPATH**/ ?>