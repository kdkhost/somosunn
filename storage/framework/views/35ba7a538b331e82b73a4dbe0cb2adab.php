

<?php $__env->startSection('title', 'Comunidade'); ?>
<?php $__env->startSection('page_title', 'Comunidade'); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title">Nova publicacao</h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-tool" title="Opcoes" onclick="togglePanel('admin-post-options')">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div id="admin-post-options" class="d-none position-absolute bg-white border rounded shadow p-3" style="right: 1rem; z-index: 20; min-width: 220px;">
                            <label class="mb-1 text-muted small">Visibilidade</label>
                            <select id="admin-visibility-select" class="form-control form-control-sm">
                                <option value="public">Publico</option>
                                <option value="connections">Somente seguidores</option>
                                <option value="community" selected>Somente comunidade</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="admin-post-form" action="<?php echo e(route('social.post.store')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="visibility" id="admin-visibility-input" value="community">
                        <div class="form-group">
                            <textarea name="content" rows="3" class="form-control" placeholder="No que voce esta pensando?"></textarea>
                        </div>
                        <input type="file" name="media[]" class="d-none" id="admin-post-media" accept="image/*" multiple>
                        <div class="form-group" id="admin-dropzone">
                            <div class="border rounded p-3 bg-light d-flex flex-column gap-2">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="text-muted">
                                        <i class="fas fa-image mr-1"></i> Arraste e solte imagens
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="admin-post-select">
                                        Selecionar imagens
                                    </button>
                                </div>
                                <div class="text-muted small" id="admin-upload-name">Nenhuma imagem selecionada</div>
                                <div id="admin-preview-inline" class="d-none align-items-center">
                                    <img id="admin-preview-inline-img" src="" alt="Preview" class="mr-2 rounded border" style="width:44px;height:44px;object-fit:cover;">
                                    <span class="text-muted small" id="admin-preview-inline-name"></span>
                                </div>
                            </div>
                        </div>
                        <div id="admin-preview-top" class="d-none mb-3">
                            <div class="position-relative d-inline-block">
                                <img id="admin-preview-top-img" src="" alt="Preview" class="rounded border" style="max-height:160px;">
                                <button type="button" id="admin-preview-remove"
                                    class="btn btn-sm btn-light border position-absolute" style="top:-10px;right:-10px;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2 position-relative">
                                <button type="button" id="admin-emoji-toggle" class="btn btn-light" title="Inserir emoji">
                                    <i class="far fa-smile"></i>
                                </button>
                                <div id="admin-emoji-picker" class="d-none position-absolute bg-white border rounded shadow" style="z-index: 20; width: 280px; top: 46px; left: 0;">
                                    <div class="d-flex align-items-center justify-content-between px-2 pt-2">
                                        <span class="text-muted text-xs">Emojis</span>
                                        <button type="button" class="btn btn-tool btn-xs" id="admin-emoji-close" title="Fechar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="px-2 pb-2">
                                        <?php echo $__env->make('social.partials.emoji_tabs', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                        <?php echo $__env->make('social.partials.emoji_grid', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Publicar</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <img src="<?php echo e($post->user?->profile_photo_url ?? asset('img/default-user.svg')); ?>"
                                    alt="Avatar" class="img-circle" style="width:40px;height:40px;object-fit:cover;">
                            </div>
                            <div>
                                <strong><?php echo e($post->user->name ?? 'Anonimo'); ?></strong>
                                <div class="text-muted text-sm"><?php echo e($post->created_at->diffForHumans()); ?></div>
                            </div>
                        </div>
                        <div class="card-tools ml-auto">
                            <div class="dropdown">
                                <button type="button" class="btn btn-tool" data-toggle="dropdown" aria-expanded="false" title="Mais opções">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <?php if(auth()->user()->isAdmin() || $post->user_id === auth()->id()): ?>
                                        <form action="<?php echo e(route('social.post.destroy', $post)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="button" class="dropdown-item text-danger" data-confirm-delete>
                                                <i class="fas fa-trash mr-2"></i> Remover
                                            </button>
                                        </form>
                                        <form action="<?php echo e(route('social.post.unpublish', $post)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-eye-slash mr-2"></i> Despublicar
                                            </button>
                                        </form>
                                        <div class="dropdown-divider"></div>
                                    <?php endif; ?>
                                    <form action="<?php echo e(route('social.post.hide', $post)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-eye mr-2"></i> Ocultar postagem
                                        </button>
                                    </form>
                                    <?php if(!(auth()->user()->isAdmin() || $post->user_id === auth()->id())): ?>
                                        <button type="button" class="dropdown-item text-danger" data-toggle="modal" data-target="#reportModal-<?php echo e($post->id); ?>">
                                            <i class="fas fa-flag mr-2"></i> Denunciar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="mb-3"><?php echo nl2br(e($post->content)); ?></p>
                        <?php if($post->media->isNotEmpty()): ?>
                            <?php
                                $mediaCount = $post->media->count();
                            ?>
                            <?php if($mediaCount === 1): ?>
                                <img src="<?php echo e(asset($post->media->first()->path)); ?>" alt="Midia do post" class="img-fluid rounded">
                            <?php else: ?>
                                <div class="position-relative" data-carousel data-total="<?php echo e($mediaCount); ?>">
                                    <div class="overflow-hidden rounded">
                                        <div class="d-flex" data-track style="transition: transform 0.3s ease;">
                                            <?php $__currentLoopData = $post->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <img src="<?php echo e(asset($media->path)); ?>" alt="Midia do post" class="w-100 flex-shrink-0" style="object-fit: cover;">
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-light btn-sm position-absolute" style="top:50%;left:8px;transform:translateY(-50%);" data-prev>
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm position-absolute" style="top:50%;right:8px;transform:translateY(-50%);" data-next>
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                    <span class="badge badge-dark position-absolute" style="right:8px;bottom:8px;" data-counter>1/<?php echo e($mediaCount); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <?php echo e($post->reactions->count()); ?> curtida<?php echo e($post->reactions->count() === 1 ? '' : 's'); ?> ·
                            <?php echo e($post->comments->count()); ?> comentario<?php echo e($post->comments->count() === 1 ? '' : 's'); ?>

                        </small>
                    </div>
                </div>

                
                <?php if(!(auth()->user()->isAdmin() || $post->user_id === auth()->id())): ?>
                    <div class="modal fade" id="reportModal-<?php echo e($post->id); ?>" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel-<?php echo e($post->id); ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="reportModalLabel-<?php echo e($post->id); ?>">Denunciar publicação</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="<?php echo e(route('social.post.report', $post)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="reason-<?php echo e($post->id); ?>">Motivo da denúncia</label>
                                            <textarea name="reason" id="reason-<?php echo e($post->id); ?>" rows="4" class="form-control" required placeholder="Descreva o motivo da denúncia..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-danger">Denunciar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(!empty($adsEnabled) && !empty($adsCode) && $loop->iteration % 3 === 0): ?>
                    <div class="card">
                        <div class="card-body">
                            <?php echo $adsCode; ?>

                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="card">
                    <div class="card-body text-center text-muted">Nenhuma publicacao ainda.</div>
                </div>
            <?php endif; ?>

            <div class="mt-3">
                <?php echo e($posts->links()); ?>

            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Resumo</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Sugestoes de membros para voce:</p>
                    <?php if(!empty($recommendedUsers) && $recommendedUsers->isNotEmpty()): ?>
                        <?php
                            $connectionMap = $connectionMap ?? [];
                            $authUserId = auth()->id();
                        ?>
                        <ul class="list-unstyled mb-0">
                            <?php $__currentLoopData = $recommendedUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $connection = $connectionMap[$user->id] ?? null;
                                    $isPending = $connection && $connection->status === 'pending';
                                    $isConnected = $connection && $connection->status === 'accepted';
                                    $isRequester = $connection && $authUserId && $connection->requester_id === $authUserId;
                                    $pendingTime = $connection ? $connection->created_at->diffForHumans() : '';
                                ?>
                                <li class="d-flex align-items-center justify-content-between py-2">
                                    <div class="d-flex align-items-center">
                                        <a class="mr-2" href="<?php echo e(route('social.profile', $user->id)); ?>">
                                            <img src="<?php echo e($user->profile_photo_url); ?>" alt="Avatar" class="img-circle"
                                                style="width:36px;height:36px;object-fit:cover;"
                                                onerror="this.onerror=null;this.src='<?php echo e(asset('img/default-user.svg')); ?>';">
                                        </a>
                                        <div>
                                            <a href="<?php echo e(route('social.profile', $user->id)); ?>" class="text-sm font-weight-bold text-dark">
                                                <?php echo e($user->name); ?>

                                            </a>
                                            <div class="text-muted text-xs">
                                                <?php if(!empty($user->segment)): ?>
                                                    <?php echo e($user->segment); ?>

                                                <?php elseif(!empty($user->occupation)): ?>
                                                    <?php echo e($user->occupation); ?>

                                                <?php elseif(!empty($user->company)): ?>
                                                    <?php echo e($user->company); ?>

                                                <?php elseif(!empty($user->interests)): ?>
                                                    <?php echo e(\Illuminate\Support\Str::limit($user->interests, 40)); ?>

                                                <?php elseif(!empty($user->city)): ?>
                                                    <?php echo e($user->city); ?><?php if(!empty($user->state)): ?>, <?php echo e($user->state); ?><?php endif; ?>
                                                <?php else: ?>
                                                    Membro
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <?php if($isConnected): ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                Conectado
                                            </button>
                                        <?php elseif($isPending && $isRequester): ?>
                                            <div class="text-muted text-xs">Pendente <?php echo e($pendingTime); ?></div>
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-1"
                                                onclick="cancelInvite(<?php echo e($user->id); ?>)">
                                                Cancelar
                                            </button>
                                        <?php elseif($isPending): ?>
                                            <div class="text-muted text-xs">Solicitacao recebida</div>
                                            <button type="button" class="btn btn-sm btn-outline-success mt-1"
                                                onclick="acceptInvite(<?php echo e($user->id); ?>)">
                                                Aceitar
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="requestInvite(<?php echo e($user->id); ?>)">
                                                Conectar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">Sem sugestoes no momento.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        #admin-dropzone.is-dragover .border {
            border-color: #1f5edb;
            background-color: #eef4ff;
        }

        #admin-emoji-picker {
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
            max-height: 360px;
            overflow: hidden;
        }

        #admin-emoji-picker .emoji-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 6px;
            margin-top: 6px;
            max-height: 260px;
            overflow-y: auto;
            padding-right: 2px;
        }

        #admin-emoji-picker .emoji-item {
            border: 0;
            background: transparent;
            padding: 0;
            font-size: 20px;
            line-height: 1;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            transition: transform 0.15s ease, background-color 0.15s ease;
        }

        #admin-emoji-picker .emoji-item:hover {
            background-color: #f1f5f9;
            transform: scale(1.08);
        }

        #admin-emoji-picker .emoji-tab {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 14px;
            line-height: 1;
            transition: all 0.15s ease;
        }

        #admin-emoji-picker .emoji-tab.is-active,
        #admin-emoji-picker .emoji-tab:hover {
            border-color: #1f5edb;
            background-color: #eef4ff;
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
            panel.classList.toggle('d-none');
        }

        const adminMedia = document.getElementById('admin-post-media');
        const adminSelect = document.getElementById('admin-post-select');
        const adminDropzone = document.getElementById('admin-dropzone');
        const adminUploadName = document.getElementById('admin-upload-name');
        const adminPreviewTop = document.getElementById('admin-preview-top');
        const adminPreviewTopImg = document.getElementById('admin-preview-top-img');
        const adminPreviewInline = document.getElementById('admin-preview-inline');
        const adminPreviewInlineImg = document.getElementById('admin-preview-inline-img');
        const adminPreviewInlineName = document.getElementById('admin-preview-inline-name');
        const adminPreviewRemove = document.getElementById('admin-preview-remove');
        const adminEmojiToggle = document.getElementById('admin-emoji-toggle');
        const adminEmojiPicker = document.getElementById('admin-emoji-picker');
        const adminEmojiClose = document.getElementById('admin-emoji-close');
        const adminVisibilitySelect = document.getElementById('admin-visibility-select');
        const adminVisibilityInput = document.getElementById('admin-visibility-input');

        const formatAdminUploadName = (files) => {
            if (!files || !files.length) {
                return 'Nenhuma imagem selecionada';
            }

            if (files.length === 1) {
                return files[0].name;
            }

            return `${files.length} imagens selecionadas`;
        };

        const setAdminUploadName = (files) => {
            if (adminUploadName) {
                adminUploadName.textContent = formatAdminUploadName(files);
            }
        };

        const showAdminPreview = (file, total) => {
            if (!file || !adminPreviewTopImg || !adminPreviewInlineImg) {
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                const src = event.target ? event.target.result : null;
                if (!src) {
                    return;
                }
                adminPreviewTopImg.src = src;
                adminPreviewInlineImg.src = src;
                if (adminPreviewInlineName) {
                    const extra = total && total > 1 ? ` (+${total - 1})` : '';
                    adminPreviewInlineName.textContent = `${file.name}${extra}`;
                }
                if (adminPreviewTop) {
                    adminPreviewTop.classList.remove('d-none');
                }
                if (adminPreviewInline) {
                    adminPreviewInline.classList.remove('d-none');
                    adminPreviewInline.classList.add('d-flex');
                }
            };
            reader.readAsDataURL(file);
        };

        const clearAdminPreview = () => {
            if (adminPreviewTop) {
                adminPreviewTop.classList.add('d-none');
            }
            if (adminPreviewInline) {
                adminPreviewInline.classList.add('d-none');
                adminPreviewInline.classList.remove('d-flex');
            }
            if (adminPreviewTopImg) {
                adminPreviewTopImg.src = '';
            }
            if (adminPreviewInlineImg) {
                adminPreviewInlineImg.src = '';
            }
            if (adminPreviewInlineName) {
                adminPreviewInlineName.textContent = '';
            }
        };

        if (adminSelect && adminMedia) {
            adminSelect.addEventListener('click', () => adminMedia.click());
        }

        if (adminMedia) {
            adminMedia.addEventListener('change', () => {
                const files = adminMedia.files;
                const file = files && files.length ? files[0] : null;
                setAdminUploadName(files);
                if (file) {
                    showAdminPreview(file, files.length);
                } else {
                    clearAdminPreview();
                }
            });
        }

        if (adminPreviewRemove && adminMedia) {
            adminPreviewRemove.addEventListener('click', () => {
                adminMedia.value = '';
                setAdminUploadName(null);
                clearAdminPreview();
            });
        }

        if (adminDropzone && adminMedia) {
            adminDropzone.addEventListener('dragover', (event) => {
                event.preventDefault();
                adminDropzone.classList.add('is-dragover');
            });

            adminDropzone.addEventListener('dragleave', () => {
                adminDropzone.classList.remove('is-dragover');
            });

            adminDropzone.addEventListener('drop', (event) => {
                event.preventDefault();
                adminDropzone.classList.remove('is-dragover');

                const files = event.dataTransfer ? event.dataTransfer.files : [];
                if (!files || !files.length) {
                    return;
                }

                const dataTransfer = new DataTransfer();
                Array.from(files).forEach((file) => dataTransfer.items.add(file));
                adminMedia.files = dataTransfer.files;
                const file = adminMedia.files[0];
                setAdminUploadName(adminMedia.files);
                showAdminPreview(file, adminMedia.files.length);
            });
        }

        if (adminVisibilitySelect && adminVisibilityInput) {
            const syncAdminVisibility = () => {
                adminVisibilityInput.value = adminVisibilitySelect.value;
            };
            adminVisibilitySelect.addEventListener('change', syncAdminVisibility);
            syncAdminVisibility();
        }

        if (adminEmojiToggle && adminEmojiPicker) {
            adminEmojiToggle.addEventListener('click', (event) => {
                event.preventDefault();
                adminEmojiPicker.classList.toggle('d-none');
            });
        }

        if (adminEmojiClose && adminEmojiPicker) {
            adminEmojiClose.addEventListener('click', (event) => {
                event.preventDefault();
                adminEmojiPicker.classList.add('d-none');
            });
        }

        document.addEventListener('click', (event) => {
            if (!adminEmojiPicker || adminEmojiPicker.classList.contains('d-none')) {
                return;
            }

            const isInsidePicker = adminEmojiPicker.contains(event.target);
            const isToggle = adminEmojiToggle && adminEmojiToggle.contains(event.target);

            if (!isInsidePicker && !isToggle) {
                adminEmojiPicker.classList.add('d-none');
            }
        });

        const applyAdminEmojiCategory = (category) => {
            const grid = document.querySelector('#admin-emoji-picker .emoji-grid');
            if (!grid) {
                return;
            }

            grid.querySelectorAll('.emoji-item').forEach((item) => {
                const itemCategory = item.getAttribute('data-category');
                const shouldShow = category === 'all' || itemCategory === category;
                item.style.display = shouldShow ? '' : 'none';
            });
        };

        document.querySelectorAll('#admin-emoji-picker .emoji-tab').forEach((tab) => {
            tab.addEventListener('click', () => {
                const category = tab.getAttribute('data-category') || 'all';
                document.querySelectorAll('#admin-emoji-picker .emoji-tab').forEach((t) => t.classList.remove('is-active'));
                tab.classList.add('is-active');
                applyAdminEmojiCategory(category);
            });
        });

        applyAdminEmojiCategory('faces');

        document.querySelectorAll('#admin-emoji-picker .emoji-item').forEach((item) => {
            item.addEventListener('click', () => {
                const target = document.querySelector('#admin-post-form textarea[name="content"]');
                const emoji = item.getAttribute('data-emoji');
                if (!target || !emoji) {
                    return;
                }
                target.value = `${target.value}${emoji}`;
                target.focus();
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
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        const csrfToken = '<?php echo e(csrf_token()); ?>';

        function requestInvite(userId) {
            Swal.fire({
                title: 'Conectar com este usuario?',
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
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\community\feed.blade.php ENDPATH**/ ?>