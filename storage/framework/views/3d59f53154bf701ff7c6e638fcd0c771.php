<?php
    $cmsSlug = 'institucional_quem_somos';

    $metaImagePath = (string) \App\Models\SiteContent::getValue($cmsSlug, 'meta_image', '');
    $metaImageUrl = '';
    if (trim($metaImagePath) !== '') {
        $metaImageUrl = (str_starts_with($metaImagePath, 'http://') || str_starts_with($metaImagePath, 'https://'))
            ? $metaImagePath
            : asset('storage/' . ltrim($metaImagePath, '/'));
    }

    $twitterImagePath = (string) \App\Models\SiteContent::getValue($cmsSlug, 'twitter_image', '');
    $twitterImageUrl = '';
    if (trim($twitterImagePath) !== '') {
        $twitterImageUrl = (str_starts_with($twitterImagePath, 'http://') || str_starts_with($twitterImagePath, 'https://'))
            ? $twitterImagePath
            : asset('storage/' . ltrim($twitterImagePath, '/'));
    } elseif ($metaImageUrl !== '') {
        $twitterImageUrl = $metaImageUrl;
    }
?>

<?php $__env->startSection('title', \App\Models\SiteContent::getValue($cmsSlug, 'title', 'Quem Somos - Equipe UNN')); ?>
<?php $__env->startSection('meta_title', \App\Models\SiteContent::getValue($cmsSlug, 'meta_title', '')); ?>
<?php $__env->startSection('meta_description', \App\Models\SiteContent::getValue($cmsSlug, 'meta_description', '')); ?>
<?php $__env->startSection('meta_keywords', \App\Models\SiteContent::getValue($cmsSlug, 'meta_keywords', '')); ?>
<?php $__env->startSection('meta_robots', \App\Models\SiteContent::getValue($cmsSlug, 'meta_robots', '')); ?>
<?php $__env->startSection('canonical', \App\Models\SiteContent::getValue($cmsSlug, 'canonical', '')); ?>
<?php $__env->startSection('og_type', \App\Models\SiteContent::getValue($cmsSlug, 'og_type', '')); ?>
<?php $__env->startSection('twitter_card', \App\Models\SiteContent::getValue($cmsSlug, 'twitter_card', '')); ?>
<?php $__env->startSection('meta_image', $metaImageUrl); ?>
<?php $__env->startSection('twitter_image', $twitterImageUrl); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $decodeJson = function (?string $raw, array $fallback = []) {
            $raw = trim((string) $raw);
            if ($raw === '') return $fallback;
            $val = json_decode($raw, true);
            return is_array($val) ? $val : $fallback;
        };

        $heroHighlight = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title_highlight', 'Quem');
        $heroTitle = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title', 'Somos');
        $heroSubtitle = \App\Models\SiteContent::getValue($cmsSlug, 'hero_subtitle', 'Conheça as pessoas por trás da maior comunidade de networking do Brasil.');

        $foundersTitle = \App\Models\SiteContent::getValue($cmsSlug, 'founders_title', 'Fundadores');
        $foundersFallback = [
            ['name' => 'Ricardo Andrade', 'role' => 'CEO & Co-Fundador', 'bio' => 'Empreendedor serial com exits em 3 startups. Acredita no poder transformador das conexões humanas.', 'initials' => 'RA'],
            ['name' => 'Patrícia Lima', 'role' => 'COO & Co-Fundadora', 'bio' => 'Especialista em operações e escalabilidade. Ex-executiva de grandes corporações.', 'initials' => 'PL'],
            ['name' => 'Marcos Teixeira', 'role' => 'CTO & Co-Fundador', 'bio' => 'Engenheiro de software com 20 anos de experiência. Apaixonado por tecnologia e inovação.', 'initials' => 'MT'],
        ];
        $foundersItems = $decodeJson(\App\Models\SiteContent::getValue($cmsSlug, 'founders_items'), $foundersFallback);

        $teamTitle = \App\Models\SiteContent::getValue($cmsSlug, 'team_title', 'Nossa Equipe');
        $teamFallback = [
            ['name' => 'Camila Rocha', 'role' => 'Head de Comunidade', 'initials' => 'CR'],
            ['name' => 'Bruno Dias', 'role' => 'Head de Eventos', 'initials' => 'BD'],
            ['name' => 'Larissa Costa', 'role' => 'Head de Marketing', 'initials' => 'LC'],
            ['name' => 'Gabriel Santos', 'role' => 'Head de Parcerias', 'initials' => 'GS'],
            ['name' => 'Fernanda Alves', 'role' => 'Head de Conteúdo', 'initials' => 'FA'],
            ['name' => 'Lucas Pereira', 'role' => 'Head de Tecnologia', 'initials' => 'LP'],
        ];
        $teamItems = $decodeJson(\App\Models\SiteContent::getValue($cmsSlug, 'team_items'), $teamFallback);

        $numbersTitle = \App\Models\SiteContent::getValue($cmsSlug, 'numbers_title', 'UNN em Números');
        $numbersFallback = [
            ['value' => '15', 'label' => 'Colaboradores'],
            ['value' => '4', 'label' => 'Anos de história'],
            ['value' => '5k+', 'label' => 'Membros atendidos'],
            ['value' => '100%', 'label' => 'Dedicação'],
        ];
        $numbersItems = $decodeJson(\App\Models\SiteContent::getValue($cmsSlug, 'numbers_items'), $numbersFallback);

        $ctaTitle = \App\Models\SiteContent::getValue($cmsSlug, 'cta_title', 'Quer fazer parte do time?');
        $ctaSubtitle = \App\Models\SiteContent::getValue($cmsSlug, 'cta_subtitle', 'Estamos sempre em busca de talentos que compartilham nossa visão.');
        $ctaButtonText = \App\Models\SiteContent::getValue($cmsSlug, 'cta_button_text', 'Entre em contato');
        $ctaButtonUrl = \App\Models\SiteContent::getValue($cmsSlug, 'cta_button_url', route('contato'));
    ?>

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <!-- Hero Section -->
        <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                    <span class="unn-title-gradient"><?php echo e($heroHighlight); ?></span> <?php echo e($heroTitle); ?>

                </h1>
                <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    <?php echo e($heroSubtitle); ?>

                </p>
            </div>
        </section>

        <!-- Fundadores -->
        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-12 text-center"><?php echo e($foundersTitle); ?></h2>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php $__currentLoopData = $foundersItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $founder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white rounded-3xl shadow-lg overflow-hidden text-center">
                            <div class="h-24 btn-primary"></div>
                            <div class="flex justify-center -mt-12">
                                <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg btn-primary flex items-center justify-center text-white text-2xl font-bold">
                                    <?php echo e($founder['initials'] ?? ''); ?>

                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-1"><?php echo e($founder['name'] ?? ''); ?></h3>
                                <p class="text-sm mb-3" style="color: var(--unn-azul-1)"><?php echo e($founder['role'] ?? ''); ?></p>
                                <p class="text-gray-600 text-sm"><?php echo e($founder['bio'] ?? ''); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        <!-- Equipe -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-12 text-center"><?php echo e($teamTitle); ?></h2>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    <?php $__currentLoopData = $teamItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="text-center">
                            <div class="w-20 h-20 rounded-full btn-primary flex items-center justify-center text-white text-xl font-bold mx-auto mb-3">
                                <?php echo e($member['initials'] ?? ''); ?>

                            </div>
                            <h4 class="font-bold text-gray-900 text-sm"><?php echo e($member['name'] ?? ''); ?></h4>
                            <p class="text-xs text-gray-500"><?php echo e($member['role'] ?? ''); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        <!-- Números -->
        <section class="py-16 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-8 md:mb-12 text-center"><?php echo e($numbersTitle); ?></h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                    <?php $__currentLoopData = $numbersItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white rounded-2xl p-4 md:p-8 text-center shadow-lg">
                            <p class="text-3xl md:text-5xl font-black truncate" style="color: var(--unn-azul-1)">
                                <?php echo e($item['value'] ?? ''); ?>

                            </p>
                            <p class="text-xs md:text-base text-gray-500 mt-2"><?php echo e($item['label'] ?? ''); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-16 px-6 md:px-12 lg:px-24"
            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
            <div class="max-w-4xl mx-auto text-center text-white">
                <h2 class="text-3xl lg:text-4xl font-black mb-4"><?php echo e($ctaTitle); ?></h2>
                <p class="text-lg opacity-90 mb-8"><?php echo e($ctaSubtitle); ?></p>
                <a href="<?php echo e($ctaButtonUrl); ?>"
                    class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition"
                    style="color: var(--unn-azul-1)">
                    <i class="fas fa-envelope"></i>
                    <?php echo e($ctaButtonText); ?>

                </a>
            </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <?php echo $__env->make('site.institucional.partials.common-styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\site\institucional\quem-somos.blade.php ENDPATH**/ ?>