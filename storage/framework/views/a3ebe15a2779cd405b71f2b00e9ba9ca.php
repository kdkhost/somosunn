

<?php $__env->startSection('page_title', 'Teste de E-mail'); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-body">
            
            <form method="POST" action="<?php echo e(route('admin.mailtest.send')); ?>">
                <?php echo csrf_field(); ?>
                <div class="form-group mb-2"><label>Para</label><input type="email" name="to" class="form-control" required>
                </div>
                <div class="form-group mb-2"><label>Assunto</label><input type="text" name="subject" class="form-control"
                        required></div>
                <div class="form-group mb-2"><label>Mensagem</label><textarea name="body" class="form-control" rows="6"
                        required></textarea></div>
                <button class="btn btn-primary">Enviar</button>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\mail_test.blade.php ENDPATH**/ ?>