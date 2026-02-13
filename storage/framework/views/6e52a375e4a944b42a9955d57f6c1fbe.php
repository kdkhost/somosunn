<?php $__env->startSection('page_title', 'Permissões'); ?>
<?php $__env->startSection('breadcrumb'); ?><li class="breadcrumb-item active">Permissões</li><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  

  <?php
    $categoryColors = [
      // Nomes de categoria quando migration foi rodada
      'Dashboard' => 'primary',
      'Usuários' => 'info',
      'Cursos' => 'success',
      'Mentorias' => 'warning',
      'Eventos' => 'danger',
      'Planos' => 'secondary',
      'Vendas' => 'dark',
      'Faturas' => 'primary',
      'Cupons' => 'info',
      'Certificados' => 'success',
      'Pontuação' => 'warning',
      'Comunidade' => 'danger',
      'E-mails' => 'secondary',
      'Depoimentos' => 'dark',
      'FAQ' => 'primary',
      'Uploads' => 'info',
      'Pagamentos' => 'success',
      'Relatórios' => 'warning',
      'Configurações' => 'danger',
      'Fontes' => 'secondary',
      'Permissões' => 'dark',
      'Outros' => 'light',
      // Prefixos quando migration não foi rodada (fallback)
      'dashboard' => 'primary',
      'users' => 'info',
      'courses' => 'success',
      'mentorships' => 'warning',
      'events' => 'danger',
      'plans' => 'secondary',
      'orders' => 'dark',
      'invoices' => 'primary',
      'coupons' => 'info',
      'certificates' => 'success',
      'points' => 'warning',
      'ranking' => 'warning',
      'community' => 'danger',
      'mailtemplates' => 'secondary',
      'mail' => 'secondary',
      'testimonials' => 'dark',
      'faq' => 'primary',
      'uploads' => 'info',
      'gateways' => 'success',
      'reports' => 'warning',
      'settings' => 'danger',
      'fonts' => 'secondary',
      'permissions' => 'dark',
      'roles' => 'dark',
    ];

    // Mapeia nome da permissão para sua categoria
    $permissionCategories = [];
    foreach ($permissions as $p) {
      $permissionCategories[$p->name] = $p->category ?? explode('.', $p->name)[0] ?? 'Outros';
    }
  ?>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="card-title mb-0"><i class="fas fa-user-shield mr-2"></i>Papéis e permissões</h3>
      <div>
        <a href="<?php echo e(route('admin.permissions.create')); ?>" class="btn btn-primary" data-pjax="true"><i
            class="fas fa-plus mr-1"></i>Novo papel</a>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:120px">Nome</th>
              <th style="width:150px">Rótulo</th>
              <th>Permissões</th>
              <th style="width:100px" class="text-right">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><code><?php echo e($role->name); ?></code></td>
                <td><?php echo e($role->label); ?></td>
                <td>
                  <?php
                    // Agrupa as permissões do papel por categoria (com fallback para prefixo)
                    $rolePermsByCategory = $role->permissions->groupBy(function ($p) {
                      return $p->category ?? explode('.', $p->name)[0] ?? 'Outros';
                    });
                  ?>
                  <?php $__currentLoopData = $rolePermsByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $color = $categoryColors[$category] ?? 'secondary'; ?>
                    <div class="mb-1">
                      <small class="text-muted d-block"><strong><?php echo e(ucfirst($category)); ?>:</strong></small>
                      <?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="badge badge-<?php echo e($color); ?> mb-1" title="<?php echo e($p->label); ?>"><?php echo e($p->name); ?></span>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </td>
                <td class="text-right">
                  <a href="<?php echo e(route('admin.permissions.edit', $role)); ?>" class="btn btn-sm btn-outline-secondary"
                    data-pjax="true" title="Editar"><i class="fas fa-edit"></i></a>
                  <?php if(!in_array($role->name, ['superadmin', 'admin', 'membro'])): ?>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                      data-action="<?php echo e(route('admin.permissions.destroy', $role)); ?>" title="Excluir"><i
                        class="fas fa-trash"></i></button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="4" class="text-center text-muted py-4">Nenhum papel cadastrado.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if($roles->hasPages()): ?>
      <div class="card-footer"><?php echo e($roles->links()); ?></div>
    <?php endif; ?>
  </div>

  <div class="card mt-4">
    <div class="card-header">
      <h3 class="card-title mb-0"><i class="fas fa-key mr-2"></i>Legenda de categorias</h3>
    </div>
    <div class="card-body">
      <div class="row">
        <?php $__currentLoopData = $categoryColors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="col-md-3 col-6 mb-2">
            <span class="badge badge-<?php echo e($color); ?>"><?php echo e($cat); ?></span>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\permissions\index.blade.php ENDPATH**/ ?>