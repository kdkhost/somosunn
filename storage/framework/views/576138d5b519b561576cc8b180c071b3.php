<?php $__env->startSection('title', $metaTitle ?? 'Marketplace UNN'); ?>
<?php $__env->startSection('meta_title', $metaTitle ?? 'Marketplace UNN'); ?>
<?php $__env->startSection('meta_description', $metaDescription ?? ''); ?>
<?php $__env->startSection('meta_image', $metaImage ?? ''); ?>
<?php $__env->startSection('canonical', $canonical ?? url()->current()); ?>
<?php $__env->startSection('og_type', 'product'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $metaTitle = (string) ($metaTitle ?? 'Marketplace UNN');
    $metaDescription = (string) ($metaDescription ?? '');
    $metaImage = (string) ($metaImage ?? '');
    $canonical = (string) ($canonical ?? url()->current());
    $title = (string) ($title ?? '');
    $label = (string) ($label ?? 'Produto');
    $targetUrl = (string) ($targetUrl ?? url('/'));
?>

<div class="min-h-screen bg-slate-50 pt-24 pb-16 px-4 md:px-12 lg:px-24">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 md:p-10 text-center">
            <div class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-black text-white shadow"
                style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                <i class="fas fa-link"></i> Link do Marketplace
            </div>

            <h1 class="mt-5 text-2xl md:text-3xl font-black text-slate-900">
                <?php echo e($title !== '' ? $title : $metaTitle); ?>

            </h1>

            <?php if($metaDescription !== ''): ?>
                <p class="mt-3 text-slate-600">
                    <?php echo e($metaDescription); ?>

                </p>
            <?php endif; ?>

            <?php if($metaImage !== ''): ?>
                <div class="mt-6">
                    <img src="<?php echo e($metaImage); ?>" alt="<?php echo e($label); ?>" class="w-full max-h-72 object-cover rounded-2xl border border-slate-100">
                </div>
            <?php endif; ?>

            <div class="mt-7 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="<?php echo e($targetUrl); ?>"
                    class="btn-primary text-white px-6 py-3 rounded-2xl font-black shadow-md inline-flex items-center justify-center gap-2">
                    <i class="fas fa-up-right-from-square"></i> Abrir agora
                </a>
                <button type="button"
                    class="px-6 py-3 rounded-2xl font-black border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center gap-2"
                    onclick="navigator.clipboard.writeText(<?php echo json_encode($canonical, 15, 512) ?>); toastr.success('Link copiado!');">
                    <i class="fas fa-copy"></i> Copiar link
                </button>
            </div>

            <p class="mt-6 text-xs text-slate-500">
                Você será redirecionado automaticamente em instantes.
            </p>
        </div>
    </div>
</div>

<script>
    setTimeout(function () {
        window.location.href = <?php echo json_encode($targetUrl, 15, 512) ?>;
    }, 1600);
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\share\product.blade.php ENDPATH**/ ?>