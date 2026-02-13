

<?php
    $groupLabels = [
        'general' => 'Geral',
        'appearance' => 'Aparência',
        'images' => 'Imagens',
        'player' => 'Player',
        'ads' => 'Anúncios',
        'pwa' => 'PWA',
        'marketplace' => 'Marketplace',
        'gateway' => 'Pagamentos',
        'smtp' => 'SMTP',
        'social' => 'Social Login',
        'seo' => 'SEO',
        'system' => 'Sistema',
    ];
    $currentLabel = $groupLabels[$group] ?? 'Configurações';

    // Helper closure for URLs
    $getUrl = function ($key) use ($settings) {
        $val = $settings[$key] ?? null;
        if (!$val)
            return '';
        if (filter_var($val, FILTER_VALIDATE_URL))
            return $val;

        // Se o valor já começar com 'storage/', usa direto no asset
        if (str_starts_with($val, 'storage/')) {
            return asset($val);
        }

        // Se começar com 'uploads/', é provável que esteja na raiz pública (legacy ou custom)
        if (str_starts_with($val, 'uploads/')) {
            return asset($val);
        }

        // Fallback genérico para storage (padrão Laravel)
        return asset('storage/' . $val);
    };
?>

<?php $__env->startSection('title', 'Configurações - ' . $currentLabel); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')); ?>">
    <style>
        .colorpicker-element .input-group-addon i,
        .colorpicker-element .input-group-append i {
            width: 16px;
            height: 16px;
            display: inline-block;
            cursor: pointer;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Configurações <small class="text-muted">> <?php echo e($currentLabel); ?></small></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Configurações</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            

            <form action="<?php echo e(route('admin.settings.update')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="current_group" value="<?php echo e($group); ?>">

                <div class="card card-outline card-primary">
                    <?php echo $__env->make('admin.settings.partials.' . $group, ['settings' => $settings, 'getUrl' => $getUrl], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Salvar
                            Alterações</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('plugins/inputmask/jquery.inputmask.min.js')); ?>"></script>
    <script src="<?php echo e(asset('plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')); ?>"></script>
    <script>
        $(function () {
            // Initialize Colorpicker
            $('.colorpicker-element').colorpicker();
            $('.colorpicker-element').on('colorpickerChange', function (event) {
                $(this).find('.fa-square').css('color', event.color.toString());
            });

            // Initialize InputMask
            $('.mask-phone').inputmask('(99) 99999-9999');
            $('.mask-cep').inputmask('99999-999');
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\settings\index.blade.php ENDPATH**/ ?>