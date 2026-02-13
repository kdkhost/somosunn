<?php $__env->startSection('page_title', $user->exists ? 'Editar usuário' : 'Novo usuário'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.users.index')); ?>">Usuários</a></li>
    <li class="breadcrumb-item active"><?php echo e($user->exists ? 'Editar' : 'Novo'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-body">
            <form method="POST"
                action="<?php echo e($user->exists ? route('admin.users.update', $user) : route('admin.users.store')); ?>"
                class="ajax-form">
                <?php echo csrf_field(); ?>
                <?php if($user->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
                <div class="form-group mb-3"><label>Nome</label><input name="name" class="form-control"
                        value="<?php echo e(old('name', $user->name)); ?>" required></div>
                <div class="form-group mb-3"><label>E-mail</label><input name="email" type="email" class="form-control"
                        value="<?php echo e(old('email', $user->email)); ?>" required></div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Senha <?php if($user->exists): ?><small class="text-muted">(deixe em branco para não alterar)</small><?php endif; ?></label>
                        <input name="password" type="password" class="form-control">
                    </div>
                    
                    <?php if(auth()->id() !== $user->id): ?>
                        <div class="form-group col-md-3">
                            <label>Papel</label>
                            <select name="role" class="form-control">
                                <option value="member" <?php echo e((old('role', $user->role) ?? 'member') == 'member' ? 'selected' : ''); ?>>Membro</option>
                                <option value="admin" <?php echo e(old('role', $user->role) == 'admin' ? 'selected' : ''); ?>>Administrador</option>
                                <?php if(auth()->user()->role === 'superadmin'): ?>
                                <option value="superadmin" <?php echo e(old('role', $user->role) == 'superadmin' ? 'selected' : ''); ?>>Super Admin</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Nível</label>
                            <select name="level" class="form-control">
                                <?php
                                    $levels = ['iniciante', 'intermediario', 'avancado', 'sucesso'];
                                    // Apenas superadmin pode ver/atribuir o nível superadmin
                                    if (auth()->user()->role === 'superadmin') {
                                        $levels[] = 'superadmin';
                                    }
                                ?>
                                <?php $__currentLoopData = $levels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lvl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($lvl); ?>" <?php echo e((old('level', $user->level) ?? 'iniciante') == $lvl ? 'selected' : ''); ?>><?php echo e(ucfirst($lvl)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="form-group col-md-6 d-flex align-items-center mt-4">
                            <p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> Papel e Nível não podem ser alterados no próprio perfil.</p>
                        </div>
                    <?php endif; ?>
                </div>
        </div>

        <?php if(auth()->user()->isAdmin()): ?>
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title"><i class="fas fa-crown mr-1"></i> Gestão de Plano Manual</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Plano Atribuído</label>
                            <select name="plan_id" class="form-control">
                                <option value="">Nenhum plano (Usar assinaturas)</option>
                                <?php $__currentLoopData = \App\Models\Plan::orderBy('price')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($p->id); ?>" <?php echo e(old('plan_id', $user->plan_id) == $p->id ? 'selected' : ''); ?>>
                                        <?php echo e($p->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Expiração do Acesso</label>
                            <input type="date" name="plan_expires_at" class="form-control"
                                value="<?php echo e(old('plan_expires_at', $user->plan_expires_at ? $user->plan_expires_at->format('Y-m-d') : '')); ?>">
                            <small class="text-muted">Deixe vazio para acesso ilimitado.</small>
                        </div>
                    </div>
                </div>
            </div>

            <?php
                $userFeatures = $userFeatures ?? [];
                $selectedFeatures = old('extra_features', $user->extra_features ?? []);
                if (!is_array($selectedFeatures)) {
                    $selectedFeatures = [];
                }
            ?>

            <div class="card mt-4 border-success">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title"><i class="fas fa-unlock-alt mr-1"></i> Recursos Individuais (Extras)</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Libere recursos específicos para este usuário, independente do plano atribuído.
                        Esses recursos se somam aos do plano.
                    </p>
                    
                    <?php
                        // Agrupar features por categoria
                        $featureGroups = [
                            'Acesso Básico' => ['community', 'chat', 'connections', 'connections.unlimited'],
                            'Cursos' => ['courses', 'courses.certificates', 'courses.downloads'],
                            'Eventos' => ['events', 'events.recordings', 'events.vip'],
                            'Mentorias' => ['mentorships', 'mentorships.group', 'mentorships.individual'],
                            'Extras' => ['rankings', 'support.priority', 'early.access'],
                        ];
                    ?>

                    <?php $__currentLoopData = $featureGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $groupKeys): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <h6 class="font-weight-bold text-success mb-2 <?php echo e(!$loop->first ? 'mt-3' : ''); ?>">
                            <i class="fas fa-folder mr-1"></i> <?php echo e($groupName); ?>

                        </h6>
                        <div class="row mb-2">
                            <?php $__currentLoopData = $groupKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featureKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(isset($userFeatures[$featureKey])): ?>
                                    <div class="col-md-4 col-lg-3">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="extra-feature-<?php echo e($featureKey); ?>"
                                                name="extra_features[]" value="<?php echo e($featureKey); ?>"
                                                <?php echo e(in_array($featureKey, $selectedFeatures, true) ? 'checked' : ''); ?>>
                                            <label class="custom-control-label" for="extra-feature-<?php echo e($featureKey); ?>"><?php echo e($userFeatures[$featureKey]); ?></label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <button class="btn btn-primary btn-lg px-5">Salvar Alterações</button>
            <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-secondary btn-lg" data-pjax="true">Cancelar</a>
        </div>
        </form>
    </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\users\form.blade.php ENDPATH**/ ?>