<div>
    <h2 class="text-3xl font-black text-gray-900 mb-8">Informações de Contato</h2>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl p-6 shadow-lg flex flex-col md:flex-row items-center md:items-start gap-4 text-center md:text-left">
            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                <i class="fas fa-envelope text-white text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 mb-1">E-mail</h3>
                <p class="text-gray-600"><?php echo e($companyEmail); ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg flex flex-col md:flex-row items-center md:items-start gap-4 text-center md:text-left">
            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                <i class="fab fa-whatsapp text-white text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 mb-1">WhatsApp</h3>
                <p class="text-gray-600"><?php echo e($companyPhone); ?></p>
                <p class="text-sm text-gray-500">Seg-Sex, 9h às 18h</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg flex flex-col md:flex-row items-center md:items-start gap-4 text-center md:text-left">
            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                <i class="fas fa-map-marker-alt text-white text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 mb-1">Endereço</h3>
                <p class="text-gray-600">
                    <?php echo e($companyAddress); ?><?php echo e($companyNumber ? ', '.$companyNumber : ''); ?><?php if($companyComplement): ?> - <?php echo e($companyComplement); ?><?php endif; ?>
                </p>
                <p class="text-gray-600"><?php echo e($companyDistrict); ?>, <?php echo e($companyCity); ?> - <?php echo e($companyState); ?></p>
                <p class="text-gray-600">CEP: <?php echo e($companyZip); ?></p>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white rounded-2xl p-6 shadow-lg text-center md:text-left">
        <?php if(!empty($socialLinks)): ?>
            <h3 class="font-bold text-gray-900 mb-4">Redes Sociais</h3>
            <div class="flex gap-4 justify-center md:justify-start flex-wrap">
                <?php $__currentLoopData = $socialLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($link['url']); ?>" target="_blank" rel="noopener"
                       class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center text-white hover:shadow-lg transition"
                       aria-label="<?php echo e($link['title']); ?>">
                        <i class="<?php echo e($link['icon']); ?> text-xl"></i>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\site\institucional\partials\contact-info.blade.php ENDPATH**/ ?>