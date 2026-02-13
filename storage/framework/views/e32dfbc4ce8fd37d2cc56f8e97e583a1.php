

<?php $__env->startSection('page_title', 'Cursos'); ?>
<?php $__env->startSection('breadcrumb_items'); ?>
    <li class="breadcrumb-item active">Cursos</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listagem de Cursos</h3>
            <div class="card-tools">
                <?php if(auth()->user()->isAdmin() || auth()->user()->hasPermission('courses.create')): ?>
                <a href="<?php echo e(route('admin.courses.create')); ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Novo curso
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Capa</th>
                        <th>Título</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th style="width: 150px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <?php if($c->thumbnail): ?>
                                    <img src="<?php echo e(asset($c->thumbnail)); ?>" alt="Capa" class="img-circle elevation-2"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="img-circle elevation-2 d-flex align-items-center justify-content-center bg-secondary"
                                        style="width: 50px; height: 50px;">
                                        <i class="fas fa-image text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($c->title); ?></td>
                            <td><?php echo e(number_format($c->price, 2, ',', '.')); ?></td>
                            <td>
                                <?php if($c->status === 'published'): ?>
                                    <span class="badge badge-success">Publicado</span>
                                <?php elseif($c->status === 'draft'): ?>
                                    <span class="badge badge-warning">Rascunho</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?php echo e(ucfirst($c->status)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(auth()->user()->isAdmin() || (auth()->user()->hasPermission('courses.edit') && $c->user_id === auth()->id())): ?>
                                <a href="<?php echo e(route('admin.courses.edit', $c)); ?>" class="btn btn-sm btn-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if(auth()->user()->isAdmin() || (auth()->user()->hasPermission('courses.delete') && $c->user_id === auth()->id())): ?>
                                <form method="POST" action="<?php echo e(route('admin.courses.destroy', $c)); ?>" style="display:inline"
                                    class="delete-course-form">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
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
                "pageLength": 20,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
                },
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

            // SweetAlert2 for delete course confirmation
            $('.delete-course-form').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);

                Swal.fire({
                    title: 'Tem certeza que deseja excluir?',
                    text: "Este curso será removido permanentemente!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.off('submit').submit();
                    }
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\courses\index.blade.php ENDPATH**/ ?>