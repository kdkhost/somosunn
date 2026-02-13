<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'context' => 'general',
    'title' => 'Perguntas Frequentes',
    'perPage' => 4,
    'sectionClass' => 'py-16 px-6 md:px-12 lg:px-24',
    'containerClass' => 'max-w-4xl mx-auto',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'context' => 'general',
    'title' => 'Perguntas Frequentes',
    'perPage' => 4,
    'sectionClass' => 'py-16 px-6 md:px-12 lg:px-24',
    'containerClass' => 'max-w-4xl mx-auto',
]); ?>
<?php foreach (array_filter(([
    'context' => 'general',
    'title' => 'Perguntas Frequentes',
    'perPage' => 4,
    'sectionClass' => 'py-16 px-6 md:px-12 lg:px-24',
    'containerClass' => 'max-w-4xl mx-auto',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $context = (string) $context;
    $perPage = max(1, (int) $perPage);

    $faqs = null;
    $resolvedContext = $context;

    try {
        if (view()->shared('unnDbAvailable') && \Illuminate\Support\Facades\Schema::hasTable('faqs')) {
            $pageName = $context . '_faq_page';

            $faqs = \App\Models\Faq::query()
                ->where('is_active', true)
                ->where('context', $context)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate($perPage, ['*'], $pageName)
                ->withQueryString();

            if ($faqs->total() === 0 && $context !== 'general') {
                $resolvedContext = 'general';
                $pageName = 'general_faq_page';

                $faqs = \App\Models\Faq::query()
                    ->where('is_active', true)
                    ->where('context', 'general')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->paginate($perPage, ['*'], $pageName)
                    ->withQueryString();
            }
        }
    } catch (\Throwable $e) {
        $faqs = null;
    }
?>

<?php if($faqs && $faqs->count()): ?>
    <?php
        $useAccordion = $faqs->total() > $perPage;
    ?>
    <section class="<?php echo e($sectionClass); ?>">
        <div class="<?php echo e($containerClass); ?>">
            <h2 class="text-3xl font-black text-gray-900 mb-8 text-center"><?php echo e($title); ?></h2>

            <?php if($useAccordion): ?>
                <style>
                    /* accordion (details/summary) */
                    .unn-faq summary { list-style: none; }
                    .unn-faq summary::-webkit-details-marker { display: none; }
                    .unn-faq details[open] .unn-faq-chevron { transform: rotate(180deg); }
                </style>

                <div class="space-y-4 unn-faq">
                    <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <details class="bg-white rounded-2xl p-6 shadow-lg">
                            <summary class="cursor-pointer select-none flex items-start justify-between gap-4">
                                <span class="font-bold text-gray-900"><?php echo e($faq->question); ?></span>
                                <span class="text-gray-400 pt-1 unn-faq-chevron transition-transform">
                                    <i class="fas fa-chevron-down"></i>
                                </span>
                            </summary>
                            <div class="mt-3 text-gray-600 leading-relaxed">
                                <?php echo nl2br(e($faq->answer)); ?>

                            </div>
                        </details>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white rounded-2xl p-6 shadow-lg">
                            <div class="font-bold text-gray-900"><?php echo e($faq->question); ?></div>
                            <div class="mt-3 text-gray-600 leading-relaxed">
                                <?php echo nl2br(e($faq->answer)); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <?php if($faqs->hasPages()): ?>
                <div class="mt-8">
                    <?php echo e($faqs->onEachSide(1)->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\components\faq-section.blade.php ENDPATH**/ ?>