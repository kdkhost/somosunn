

<?php $__env->startSection('title', 'Membros - UNN'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-8 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient">
                <span class="unn-title-gradient">Membros</span> UNN
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Conheça os empreendedores que fazem parte da nossa comunidade exclusiva de networking empresarial.
            </p>
        </div>
    </section>

    <?php if(isset($isDemo) && $isDemo): ?>
    <div class="max-w-7xl mx-auto px-6 mb-8">
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 flex items-center gap-3">
            <i class="fas fa-info-circle text-yellow-600 text-xl"></i>
            <p class="text-yellow-800">
                <strong>Dados de Demonstração:</strong> Estes perfis são exemplos. Membros reais aparecerão quando houver cadastros.
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <section class="pb-8 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl font-black" style="color: var(--unn-azul-1)">500+</p>
                    <p class="text-xs sm:text-sm text-gray-500 break-words">Empreendedores</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl font-black" style="color: var(--unn-azul-1)">50+</p>
                    <p class="text-xs sm:text-sm text-gray-500">Mentores</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl font-black" style="color: var(--unn-azul-1)">27</p>
                    <p class="text-xs sm:text-sm text-gray-500">Estados</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl font-black" style="color: var(--unn-azul-1)">1.2k+</p>
                    <p class="text-xs sm:text-sm text-gray-500">Conexões feitas</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Members Grid -->
    <section class="pb-20 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php $__empty_1 = true; $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $isDemo = $member->is_demo ?? false;
                        $initials = collect(explode(' ', $member->name))->take(2)->map(fn($n) => strtoupper(substr($n, 0, 1)))->join('');
                    ?>
                    <article class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 <?php echo e($isDemo ? 'ring-2 ring-yellow-400' : ''); ?>">
                        <!-- Header Azul Sólido -->
                        <div class="h-20 bg-[#1F5EDB] relative">
                            <?php if($isDemo): ?>
                                <span class="absolute top-2 right-2 bg-yellow-100 text-yellow-800 text-[10px] px-2 py-0.5 rounded-full font-semibold">DEMO</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Avatar Centralizado e Sobreposto -->
                        <div class="flex justify-center -mt-12 px-4 relative z-10">
                            <div class="p-1 bg-white rounded-full shadow-sm">
                                <?php if(isset($member->avatar) && $member->avatar): ?>
                                    <img src="<?php echo e($member->avatar); ?>" alt="<?php echo e($member->name); ?>" class="w-24 h-24 rounded-full border-4 border-white object-cover">
                                <?php else: ?>
                                    <div class="w-24 h-24 rounded-full border-4 border-white bg-[#1F5EDB] flex items-center justify-center text-white text-2xl font-bold">
                                        <?php echo e($initials); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="pt-3 pb-5 px-4 text-center">
                            <!-- Nome -->
                            <h3 class="text-lg font-bold text-gray-900 mb-0.5"><?php echo e($member->name); ?></h3>
                            
                            <!-- Localização -->
                            <?php if(isset($member->city) && $member->city): ?>
                            <p class="text-xs text-gray-500 mb-4 flex items-center justify-center gap-1">
                                <i class="fas fa-map-marker-alt text-[#1F5EDB]"></i>
                                <?php echo e($member->city); ?>

                            </p>
                            <?php endif; ?>

                            <!-- Linha Divisória -->
                            <div class="border-t border-gray-100 w-full mb-3"></div>

                            <!-- Conexões -->
                            <div class="mb-4">
                                <p class="text-xl font-bold text-gray-900"><?php echo e($member->connections ?? 0); ?></p>
                                <p class="text-[10px] uppercase tracking-wide text-gray-400 font-bold">Conexões</p>
                            </div>

                            <!-- Botão Ver Perfil -->
                            <?php if(!$isDemo): ?>
                            <a href="<?php echo e(route('social.profile', $member->id)); ?>" class="block w-full bg-[#1F5EDB] hover:bg-blue-700 text-white py-2.5 rounded-lg font-bold text-sm text-center transition shadow hover:shadow-md">
                                Ver Perfil
                            </a>
                            <?php else: ?>
                            <button onclick="Swal.fire({
                                title: 'Perfil Demo',
                                text: 'Este é um perfil de demonstração.',
                                icon: 'info',
                                confirmButtonColor: '#1F5EDB'
                            })" class="block w-full bg-[#1F5EDB] text-white py-2.5 rounded-lg font-bold text-sm opacity-75 cursor-not-allowed">
                                Ver Perfil
                            </button>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-full text-center py-16">
                        <div class="bg-white rounded-3xl p-12 shadow-lg max-w-md mx-auto">
                            <i class="fas fa-users text-6xl text-gray-300 mb-6"></i>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Nenhum membro ainda</h3>
                            <p class="text-gray-500 mb-6">Seja o primeiro a fazer parte da nossa comunidade!</p>
                            <a href="<?php echo e(route('register')); ?>" class="btn-primary text-white px-8 py-3 rounded-full font-semibold inline-flex items-center gap-2">
                                <i class="fas fa-user-plus"></i> Fazer parte
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">Faça parte desta comunidade</h2>
            <p class="text-lg opacity-90 mb-8">Conecte-se com empreendedores de sucesso e expanda sua rede de negócios.</p>
            <a href="<?php echo e(route('register')); ?>" class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                <i class="fas fa-rocket"></i>
                Quero fazer parte
            </a>
        </div>
    </section>
</div>

<style>
.text-gradient {
    background: linear-gradient(135deg, var(--unn-azul-1) 0%, var(--unn-azul-3) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
<style>
        .unn-title-gradient {
            background: linear-gradient(90deg, #2E3192 0%, #0071BC 60%, #29ABE2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\site\membros.blade.php ENDPATH**/ ?>