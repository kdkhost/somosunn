<?php $__env->startSection('page_title', 'Usuários'); ?>
<?php $__env->startSection('breadcrumb'); ?><li class="breadcrumb-item active">Usuários</li><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users-cog mr-2"></i>Gerenciar usuários</h3>
            <div class="card-tools">
                <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary btn-sm" data-pjax="true">
                    <i class="fas fa-plus"></i> Novo
                </a>
            </div>
        </div>
        <div class="card-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Papel</th>
                        <th>Nível</th>
                        <th class="text-right" style="width:140px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($user->name); ?></td>
                            <td><?php echo e($user->email); ?></td>
                            <td>
                                <?php if($user->role === 'superadmin'): ?>
                                    <span class="badge badge-danger">Super Admin</span>
                                <?php elseif($user->role === 'admin'): ?>
                                    <span class="badge badge-warning">Admin</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Membro</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(ucfirst($user->level ?? 'Iniciante')); ?></td>
                            <td class="text-right">
                                <?php if(auth()->user()->isAdmin() && $user->id !== auth()->id() && !session()->has('impersonator_id')): ?>
                                    
                                    <?php if(!$user->isAdmin() || auth()->user()->role === 'superadmin'): ?>
                                        <a href="<?php echo e(route('admin.users.impersonate', $user)); ?>" class="btn btn-sm btn-outline-warning"
                                            title="Acessar como usuário" data-pjax="false"><i class="fas fa-user-secret"></i></a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="btn btn-sm btn-info" title="Editar"
                                    data-pjax="true"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-sm btn-danger btn-delete" title="Excluir"
                                    data-action="<?php echo e(route('admin.users.destroy', $user)); ?>"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <!-- DataTables & Plugins -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <script>
        $(function () {
            $("#example1").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "pageLength": 15,
                "order": [[0, "asc"]],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
                },
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\users\index.blade.php ENDPATH**/ ?>