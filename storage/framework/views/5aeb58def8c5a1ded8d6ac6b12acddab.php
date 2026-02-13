

<?php $__env->startSection('title', 'Mensagens - UNN'); ?>
<?php $__env->startSection('page_title', 'Mensagens'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Chat</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline direct-chat direct-chat-primary h-100" style="min-height: 600px;">
                <div class="card-header p-2">
                    <h3 class="card-title">Chat</h3>
                </div>
                <div class="card-body p-0">
                    <div class="row h-100 m-0">
                        <!-- Conversations List -->
                        <div class="col-md-4 col-12 border-right p-0 d-flex flex-column h-100"
                            style="background-color: #f8f9fa;">
                            <div class="p-3 border-bottom">
                                <h5 class="mb-0">Conversas</h5>
                            </div>
                            <div class="flex-grow-1 overflow-auto" id="conversations-list" style="height: 550px;">
                                <?php if($conversations->isEmpty()): ?>
                                    <div class="p-4 text-center text-muted">
                                        Nenhuma conversa iniciada.
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $otherUser = $conv->users->where('id', '!=', Auth::id())->first() ?? $conv->users->first();
                                                $otherUserPhoto = $otherUser?->profile_photo_url ?? asset('img/default-user.svg');
                                                $otherUserName = $otherUser?->name ?? ($conv->title ?? 'Conversa');
                                                $isActive = isset($conversation) && $conversation->id == $conv->id;
                                            ?>
                                            <a href="<?php echo e(route('admin.chat.show', $conv->id)); ?>"
                                                data-conversation-id="<?php echo e($conv->id); ?>"
                                                class="list-group-item list-group-item-action <?php echo e($isActive ? 'active' : ''); ?>">
                                                <div class="d-flex w-100 align-items-center">
                                                    <img src="<?php echo e($otherUserPhoto); ?>" alt="User Image" class="img-circle mr-3"
                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                    <div class="flex-grow-1" style="min-width: 0;">
                                                        <div class="d-flex w-100 justify-content-between">
                                                            <h6 class="mb-1 text-truncate font-weight-bold"
                                                                style="max-width: 150px;"><?php echo e($otherUserName); ?></h6>
                                                            <?php if($conv->unread_count > 0): ?>
                                                                <span
                                                                    class="badge badge-primary badge-pill"><?php echo e($conv->unread_count); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <small class="<?php echo e($isActive ? 'text-light' : 'text-muted'); ?>">Ver
                                                            conversa...</small>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Chat Area (Placeholder for Index) -->
                        <div class="col-md-8 d-none d-md-flex align-items-center justify-content-center bg-light">
                            <div class="text-center text-muted">
                                <i class="fas fa-comments fa-4x mb-3"></i>
                                <p class="lead">Selecione uma conversa para começar</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        setInterval(() => {
            fetch('<?php echo e(route("admin.chat.list")); ?>')
                .then(r => r.json())
                .then(conversations => {
                    conversations.forEach(conv => {
                        const link = document.querySelector(`[data-conversation-id="${conv.id}"]`);
                        if (link) {
                            let badge = link.querySelector('.badge');
                            const container = link.querySelector('.d-flex.justify-content-between');
                            if (conv.unread_count > 0) {
                                if (!badge) {
                                    badge = document.createElement('span');
                                    badge.className = 'badge badge-primary badge-pill';
                                    container.appendChild(badge);
                                }
                                badge.textContent = conv.unread_count;
                            } else if (badge) {
                                badge.remove();
                            }
                        }
                    });
                });
        }, 5000);
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\chat\index.blade.php ENDPATH**/ ?>