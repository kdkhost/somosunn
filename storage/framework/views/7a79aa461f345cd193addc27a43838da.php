<?php
    $cmsSlug = 'institucional_valores';

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

<?php $__env->startSection('title', \App\Models\SiteContent::getValue($cmsSlug, 'title', 'Nossos Valores - UNN')); ?>
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

        $heroTitle = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title', 'Nossos');
        $heroHighlight = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title_highlight', 'Valores');
        $heroSubtitle = \App\Models\SiteContent::getValue($cmsSlug, 'hero_subtitle', 'Os princípios que guiam tudo o que fazemos na UNN.');

        $valuesFallback = [
            [
                'icon' => 'fas fa-heart',
                'title' => 'Confiança',
                'text' => 'A base de qualquer relacionamento duradouro. Cultivamos um ambiente onde a palavra tem valor e os compromissos são honrados. Confiança não se exige, se constrói.',
                'quote' => '"Confiança é a cola invisível que mantém as parcerias unidas."',
            ],
            [
                'icon' => 'fas fa-hands-helping',
                'title' => 'Generosidade',
                'text' => 'O verdadeiro networking começa quando você se pergunta: "Como posso ajudar?". Acreditamos que dar sem esperar nada em troca cria as conexões mais poderosas.',
                'quote' => '"Quem planta conexões, colhe oportunidades."',
            ],
            [
                'icon' => 'fas fa-lightbulb',
                'title' => 'Inovação',
                'text' => 'Nunca paramos de evoluir. Buscamos constantemente novas formas de conectar pessoas e gerar valor. A zona de conforto não é lugar para empreendedores.',
                'quote' => '"Inovar é ver o que todos veem e pensar o que ninguém pensou."',
            ],
            [
                'icon' => 'fas fa-trophy',
                'title' => 'Excelência',
                'text' => 'Buscamos sempre entregar mais do que prometemos. Excelência está no cuidado com os detalhes, no respeito com o tempo do outro e na dedicação aos nossos membros.',
                'quote' => '"Excelência não é um ato, é um hábito."',
            ],
            [
                'icon' => 'fas fa-user-shield',
                'title' => 'Integridade',
                'text' => 'Fazemos o que é certo, mesmo quando ninguém está olhando. A ética nos negócios não é opcional, é fundamental. Nossos membros são selecionados por seu caráter.',
                'quote' => '"O caráter se revela nas pequenas decisões do dia a dia."',
            ],
            [
                'icon' => 'fas fa-users',
                'title' => 'Comunidade',
                'text' => 'Somos mais fortes juntos. A UNN não é apenas uma plataforma, é uma família de empreendedores que se apoiam mutuamente nos desafios e celebram as vitórias um do outro.',
                'quote' => '"Sozinhos vamos mais rápido. Juntos vamos mais longe."',
            ],
        ];
        $valuesItems = $decodeJson(\App\Models\SiteContent::getValue($cmsSlug, 'values_items'), $valuesFallback);

        $quoteText = \App\Models\SiteContent::getValue(
            $cmsSlug,
            'quote_text',
            '“Valores não são apenas palavras bonitas na parede. São os critérios pelos quais tomamos cada decisão, grandes ou pequenas, todos os dias.”'
        );
        $quoteAuthor = \App\Models\SiteContent::getValue($cmsSlug, 'quote_author', '— Equipe Fundadora UNN');

        $ctaTitle = \App\Models\SiteContent::getValue($cmsSlug, 'cta_title', 'Compartilha desses valores?');
        $ctaSubtitle = \App\Models\SiteContent::getValue($cmsSlug, 'cta_subtitle', 'Você está no lugar certo. Faça parte da nossa comunidade.');
        $ctaButtonText = \App\Models\SiteContent::getValue($cmsSlug, 'cta_button_text', 'Fazer parte');
        $ctaButtonUrl = \App\Models\SiteContent::getValue($cmsSlug, 'cta_button_url', route('register'));
    ?>

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <!-- Hero Section -->
        <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                    <?php echo e($heroTitle); ?> <span class="unn-title-gradient"><?php echo e($heroHighlight); ?></span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    <?php echo e($heroSubtitle); ?>

                </p>
            </div>
        </section>

        <!-- Values Grid -->
        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="grid md:grid-cols-2 gap-8">
                    <?php $__currentLoopData = $valuesItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-lg">
                            <div class="flex flex-col md:flex-row items-center md:items-start gap-4 md:gap-6 text-center md:text-left">
                                <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center shrink-0">
                                    <i class="<?php echo e($item['icon'] ?? 'fas fa-star'); ?> text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900 mb-3"><?php echo e($item['title'] ?? ''); ?></h3>
                                    <p class="text-gray-600 mb-4"><?php echo e($item['text'] ?? ''); ?></p>
                                    <?php if(!empty($item['quote'])): ?>
                                        <p class="text-sm italic" style="color: var(--unn-azul-1)">
                                            <?php echo e($item['quote']); ?>

                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        <!-- Quote Section -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-4xl mx-auto text-center">
                <i class="fas fa-quote-left text-6xl mb-8" style="color: var(--unn-azul-1); opacity: 0.3"></i>
                <blockquote class="text-3xl font-bold text-gray-900 mb-6">
                    <?php echo e($quoteText); ?>

                </blockquote>
                <p class="text-gray-500"><?php echo e($quoteAuthor); ?></p>
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
                    <i class="fas fa-handshake"></i>
                    <?php echo e($ctaButtonText); ?>

                </a>
            </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <?php echo $__env->make('site.institucional.partials.common-styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\site\institucional\valores.blade.php ENDPATH**/ ?>