<?php $__env->startSection('page_title', $role->exists ? 'Editar papel' : 'Novo papel'); ?>
<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('admin.permissions.index')); ?>">Permissões</a></li>
<li class="breadcrumb-item active"><?php echo e($role->exists ? 'Editar' : 'Novo'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
  <div class="card-body">
    <form method="POST" action="<?php echo e($role->exists ? route('admin.permissions.update',$role) : route('admin.permissions.store')); ?>" class="ajax-form">
        <?php echo csrf_field(); ?>
        <?php if($role->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="form-group mb-3"><label>Nome (slug)</label><input name="name" class="form-control" value="<?php echo e(old('name',$role->name)); ?>" required></div>
        <div class="form-group mb-3"><label>Rótulo</label><input name="label" class="form-control" value="<?php echo e(old('label',$role->label)); ?>"></div>
        <?php
            $desc = [
                'dashboard.view' => 'Ver o painel inicial.',
                'users.view' => 'Listar usuários.',
                'users.create' => 'Criar usuários.',
                'users.edit' => 'Editar usuários.',
                'users.delete' => 'Excluir usuários.',
                'users.impersonate' => 'Assumir a sessão de um usuário.',
                'courses.view' => 'Listar cursos.',
                'courses.create' => 'Criar cursos.',
                'courses.edit' => 'Editar cursos.',
                'courses.delete' => 'Excluir cursos.',
                'courses.publish' => 'Publicar/arquivar cursos.',
                'mentorships.view' => 'Ver mentorias.',
                'mentorships.create' => 'Criar mentorias.',
                'mentorships.edit' => 'Editar mentorias.',
                'mentorships.delete' => 'Excluir mentorias.',
                'mentorships.schedule' => 'Agendar sessão de mentoria.',
                'events.view' => 'Listar eventos.',
                'events.create' => 'Criar eventos.',
                'events.edit' => 'Editar eventos.',
                'events.delete' => 'Excluir eventos.',
                'events.publish' => 'Publicar/encerrar eventos.',
                'events.ticket.manage' => 'Gerenciar ingressos/participações.',
                'plans.view' => 'Listar planos.',
                'plans.create' => 'Criar planos.',
                'plans.edit' => 'Editar planos.',
                'plans.delete' => 'Excluir planos.',
                'plans.feature.toggle' => 'Destacar/ocultar planos.',
                'plans.discount.manage' => 'Gerenciar descontos de planos.',
                'certificates.generate' => 'Gerar certificados.',
                'certificates.view' => 'Listar certificados.',
                'certificates.delete' => 'Excluir certificados.',
                'points.rules.manage' => 'Gerenciar regras de pontuação.',
                'ranking.view' => 'Ver ranking.',
                'ranking.edit' => 'Editar ranking.',
                'mailtemplates.view' => 'Listar templates de e-mail.',
                'mailtemplates.create' => 'Criar templates.',
                'mailtemplates.edit' => 'Editar templates.',
                'mailtemplates.delete' => 'Excluir templates.',
                'mail.sendtest' => 'Enviar e-mail de teste.',
                'uploads.manage' => 'Gerenciar uploads/arquivos.',
                'settings.view' => 'Ver configurações.',
                'settings.update' => 'Atualizar configurações.',
                'settings.smtp.test' => 'Testar SMTP.',
                'settings.pwa.toggle' => 'Ativar/desativar PWA.',
                'settings.branding.update' => 'Atualizar branding (logo/preloader).',
                'permissions.view' => 'Listar permissões.',
                'permissions.assign' => 'Atribuir permissões a papéis/usuários.',
                'permissions.sync' => 'Sincronizar permissões.',
                'roles.manage' => 'Gerenciar papéis (criar/editar/excluir).',
            ];
        ?>
        <div class="form-group mb-3">
            <label>Permissões</label>
            <div class="row">
            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 mb-2">
                    <?php $d = $desc[$p->name] ?? 'Sem descrição'; ?>
                    <label class="mb-0" title="<?php echo e($d); ?>">
                        <input type="checkbox" name="permissions[]" value="<?php echo e($p->id); ?>" <?php echo e($role->permissions->contains($p->id) ? 'checked' : ''); ?>>
                        <?php echo e($p->name); ?>

                        <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="top" title="<?php echo e($d); ?>"></i>
                    </label>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <button class="btn btn-primary">Salvar</button>
        <a href="<?php echo e(route('admin.permissions.index')); ?>" class="btn btn-secondary" data-pjax="true">Voltar</a>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/permissions/form.blade.php ENDPATH**/ ?>