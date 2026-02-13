<?php $__env->startSection('page_title', 'Modelos de E-mail'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Templates</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <h3 class="m-0">Modelos de E-mail</h3>
                <a href="<?php echo e(route('admin.mailtemplates.create')); ?>" class="btn btn-primary">Novo Modelo</a>
            </div>

            

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th>Categoria</th>
                        <th>Assunto</th>
                        <th>Ativo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($t->name); ?></td>
                            <td><?php echo e($t->slug); ?></td>
                            <td><?php echo e(ucfirst($t->category)); ?></td>
                            <td><?php echo e($t->subject); ?></td>
                            <td><?php echo e($t->is_active ? 'Sim' : 'Não'); ?></td>
                            <td>
                                <a href="<?php echo e(route('admin.mailtemplates.edit', $t)); ?>"
                                    class="btn btn-sm btn-secondary">Editar</a>
                                <a href="#" onclick="preview(<?php echo e($t->id); ?>)" class="btn btn-sm btn-info">Pré-visualizar</a>
                                <form action="<?php echo e(route('admin.mailtemplates.destroy', $t)); ?>" method="POST"
                                    style="display:inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-danger" data-confirm-delete data-confirm-title="Remover?"
                                        data-confirm-text="Esta ação não pode ser desfeita.">Remover</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

            <?php echo e($templates->links()); ?>

        </div>
    </div>

    <!-- preview modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pré-visualização</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="previewBody"></div>
                <div class="modal-footer">
                    <input type="email" id="previewEmail" class="form-control" placeholder="Enviar para e-mail de teste">
                    <button class="btn btn-primary" id="sendPreviewBtn">Enviar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function preview(id) {
            fetch('/admin/mailtemplates/' + id + '/preview')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('previewBody').innerHTML = data.html;
                    window.previewId = id;
                    $('#previewModal').modal('show');
                });
        }

        document.getElementById('sendPreviewBtn').addEventListener('click', function () {
            var to = document.getElementById('previewEmail').value;
            fetch('/admin/mailtemplates/' + window.previewId + '/send-preview', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                body: JSON.stringify({ email: to })
            }).then(r => r.json()).then(data => {
                Swal.fire({ title: 'Sucesso', text: data.message || 'Enviado', icon: 'success' });
            }).catch(e => { Swal.fire({ title: 'Erro', text: 'Não foi possível enviar o e-mail de teste.', icon: 'error' }); });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\mailtemplates\index.blade.php ENDPATH**/ ?>