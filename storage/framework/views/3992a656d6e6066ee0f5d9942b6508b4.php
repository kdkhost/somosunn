

<?php $__env->startSection('title', 'UNN - Conectando Empreendedores'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $heroTitleFallback = \App\Models\Setting::get('home_hero_title', 'Conectando empreendedores.');
        $heroSubtitleFallback = \App\Models\Setting::get('home_hero_subtitle', 'Criando oportunidades reais.');
        $heroTextFallback = \App\Models\Setting::get('home_hero_text', 'A UNN é uma comunidade de networking estratégico onde empreendedores compartilham experiências, constroem conexões e crescem juntos.');

        $heroImagePath = \App\Models\SiteContent::getValue('home', 'hero_image');
        $heroImageFallback = 'https://images.unsplash.com/photo-1552664730-d307ca884978?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800';
        $heroImageUrl = $heroImagePath ? asset('storage/' . ltrim((string) $heroImagePath, '/')) : $heroImageFallback;
    ?>

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <!-- Hero Section -->
        <section class="pt-10 md:pt-24 pb-12 md:pb-16 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-8 md:gap-16 lg:gap-24 items-center">
                    <div>
                        <h1 class="unn-title-gradient unn-title-hero mb-6" style="word-break: keep-all; hyphens: none; max-width: 650px;">
                            <?php echo e((string) \App\Models\SiteContent::resolve('home.hero_title', $heroTitleFallback)); ?> <?php echo e((string) \App\Models\SiteContent::resolve('home.hero_subtitle', $heroSubtitleFallback)); ?>
                        </h1>
                        <p class="text-base sm:text-lg text-gray-600 mb-6 md:mb-8 leading-relaxed max-w-xl">
                            <?php echo e((string) \App\Models\SiteContent::resolve('home.hero_text', $heroTextFallback)); ?>
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                            <a href="<?php echo e(route('register')); ?>"
                                class="btn-primary text-white px-6 py-3 md:px-10 md:py-4 rounded-xl font-bold text-base md:text-lg inline-flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition">
                                Quero fazer parte <i class="fas fa-arrow-right"></i>
                            </a>
                            <a href="<?php echo e(route('sobre')); ?>"
                                class="bg-white text-gray-700 px-6 py-3 md:px-10 md:py-4 rounded-xl font-bold border-2 border-gray-200 hover:border-blue-500 transition inline-flex items-center justify-center gap-2 text-base md:text-lg">
                                <i class="fas fa-play-circle"></i> Conhecer a UNN
                            </a>
                        </div>
                    </div>

                    <div class="hidden lg:block">
                        <div class="relative">
                            <div class="absolute inset-0 btn-primary rounded-3xl opacity-20 blur-3xl"></div>
                            <img src="<?php echo e($heroImageUrl); ?>"
                                alt="Networking" class="relative w-full rounded-3xl shadow-2xl">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Bar -->
        <!-- Stats Bar -->
        <section class="py-6 md:py-8 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                        <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">
                            5.000+</p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Empreendedores</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                        <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">R$
                            50M+</p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Em negócios gerados</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                        <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">
                            200+</p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Eventos realizados</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                        <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">27
                        </p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Estados</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- O que é a UNN -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-black unn-title-gradient mb-4">O que é a UNN</h2>
                    <p class="text-gray-600 text-lg max-w-2xl mx-auto">A UNN nasceu para unir empreendedores que acreditam
                        no crescimento colaborativo.</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="bg-slate-50 rounded-3xl p-8 text-center">
                        <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-handshake text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold unn-title-gradient mb-2">Conexões reais</h3>
                        <p class="text-sm text-gray-600">Networking genuíno com empreendedores que compartilham seus valores
                        </p>
                    </div>
                    <div class="bg-slate-50 rounded-3xl p-8 text-center">
                        <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chart-line text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold unn-title-gradient mb-2">Crescimento coletivo</h3>
                        <p class="text-sm text-gray-600">Juntos somos mais fortes e alcançamos resultados maiores</p>
                    </div>
                    <div class="bg-slate-50 rounded-3xl p-8 text-center">
                        <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-lightbulb text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold unn-title-gradient mb-2">Troca de experiências</h3>
                        <p class="text-sm text-gray-600">Aprenda com quem já passou pelos desafios que você enfrenta</p>
                    </div>
                    <div class="bg-slate-50 rounded-3xl p-8 text-center">
                        <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-briefcase text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold unn-title-gradient mb-2">Oportunidades</h3>
                        <p class="text-sm text-gray-600">Parcerias estratégicas que geram resultados concretos</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Palestras Gratuitas -->
        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black text-gray-900">Palestras gratuitas</h2>
                        <p class="text-gray-500">Eventos que chegam em breve</p>
                    </div>
                    <?php if(isset($isDemo) && $isDemo): ?>
                        <span class="text-sm text-yellow-600 bg-yellow-50 px-3 py-1 rounded-full font-semibold">
                            <i class="fas fa-info-circle mr-1"></i> Dados Demo
                        </span>
                    <?php endif; ?>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <?php $__currentLoopData = $freeEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article
                            class="bg-white rounded-3xl p-8 shadow-lg hover:shadow-xl transition flex flex-col justify-between <?php echo e(($event->is_demo ?? false) ? 'ring-2 ring-yellow-400' : ''); ?>" style="height: 100%">
                            <div>
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-4"
                                    style="background: var(--unn-azul-1); color: white">
                                    GRATUITA
                                </span>
                                <h3 class="text-xl font-bold unn-title-gradient mb-3"><?php echo e($event->title); ?></h3>
                                <p class="text-gray-600 text-sm mb-4"><?php echo e(Str::limit($event->description, 100)); ?></p>
                                <div class="flex items-center gap-4 text-sm text-gray-500 mb-6">
                                    <span><i class="fas fa-calendar mr-1"></i>
                                        <?php echo e(\Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i')); ?></span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo e($event->location); ?>

                                </div>
                            </div>
                            <div class="mt-auto">
                                <?php if($event->is_demo ?? false): ?>
                                    <button onclick="Swal.fire({
                                        title: 'Evento Demo',
                                        text: 'Este é um evento de demonstração.',
                                        icon: 'info',
                                        confirmButtonColor: '#1F5EDB'
                                    })" class="w-full btn-primary text-white py-3 rounded-xl font-semibold opacity-75">
                                    Quero participar
                                </button>
                                <?php else: ?>
                                    <a href="<?php echo e(route('events.show', $event->id)); ?>"
                                        class="block w-full btn-primary text-white py-3 rounded-xl font-semibold text-center">
                                    Quero participar
                                </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="text-center mt-8">
                    <a href="<?php echo e(route('events.index')); ?>"
                        class="inline-flex items-center gap-2 font-semibold hover:underline"
                        style="color: var(--unn-azul-1)">
                        Ver todos os eventos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Mentorias Premium -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black text-gray-900">Mentorias premium</h2>
                        <p class="text-gray-500">Conteúdo gravado + acompanhamento de mentores</p>
                    </div>
                    <a href="<?php echo e(route('mentorships.index')); ?>"
                        class="hidden md:inline-flex items-center gap-2 font-semibold" style="color: var(--unn-azul-1)">
                        Ver todas as mentorias <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <?php $__currentLoopData = $paidMentorings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mentorship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article
                            class="bg-slate-50 rounded-3xl p-8 border border-gray-100 <?php echo e(($mentorship->is_demo ?? false) ? 'ring-2 ring-yellow-400' : ''); ?>">
                            <div class="flex items-center justify-between mb-4">
                                <span
                                    class="text-xs uppercase tracking-wide text-gray-500"><?php echo e(optional($mentorship->mentor)->name ?? 'Mentor UNN'); ?></span>
                                <span class="text-lg font-bold" style="color: var(--unn-azul-1)">R$
                                    <?php echo e(number_format($mentorship->price, 2, ',', '.')); ?></span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3"><?php echo e($mentorship->title); ?></h3>
                            <p class="text-gray-600 text-sm mb-4"><?php echo e(Str::limit($mentorship->description, 100)); ?></p>
                            <p class="text-sm text-gray-500 mb-6">Vagas: <strong><?php echo e($mentorship->slots); ?></strong></p>
                            <?php if(!($mentorship->is_demo ?? false) && isset($mentorship->id)): ?>
                                <a href="<?php echo e(route('mentorships.show', $mentorship->id)); ?>"
                                    class="w-full btn-primary text-white py-3 rounded-xl font-semibold inline-flex items-center justify-center">
                                    Garantir vaga
                                </a>
                            <?php else: ?>
                                <button onclick="Swal.fire({
                                    title: 'Mentoria Demo',
                                    text: 'Esta é uma mentoria de demonstração.',
                                    icon: 'info',
                                    confirmButtonColor: '#1F5EDB'
                                })" class="w-full btn-primary text-white py-3 rounded-xl font-semibold opacity-75">
                                    Garantir vaga (Demo)
                                </button>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        <!-- Comunidade por níveis -->
        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">Comunidade por níveis</h2>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-3xl p-8 shadow-lg text-center">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                            style="background: #3B82F620">
                            <i class="fas fa-seedling text-2xl" style="color: #3B82F6"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-500 uppercase mb-2">Empreendedores iniciantes</p>
                        <p class="text-5xl font-black" style="color: var(--unn-azul-1)">
                            <?php echo e(number_format($levelSummary['iniciante'] ?? 0, 0, '', '.')); ?></p>
                        <p class="text-gray-500 mt-3">Conectados entre si e acolhidos por quem já percorreu a jornada.</p>
                    </div>
                    <div class="bg-white rounded-3xl p-8 shadow-lg text-center">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                            style="background: #8B5CF620">
                            <i class="fas fa-crown text-2xl" style="color: #8B5CF6"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-500 uppercase mb-2">Empresários de sucesso</p>
                        <p class="text-5xl font-black" style="color: #8B5CF6">
                            <?php echo e(number_format($levelSummary['sucesso'] ?? 0, 0, '', '.')); ?></p>
                        <p class="text-gray-500 mt-3">Mentores ativos, parceiros e investidores prontos para novas
                            oportunidades.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ranking -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-black text-gray-900">Ranking do networking</h2>
                        <p class="text-gray-500">Baseado nas avaliações após cada conexão</p>
                    </div>
                    <span class="text-sm uppercase tracking-wider text-gray-500"><?php echo e($topRankings->count()); ?> líderes</span>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Ranking Loop -->
                    <?php $__empty_1 = true; $__currentLoopData = $topRankings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="bg-slate-50 rounded-3xl p-6 hover:shadow-lg transition relative overflow-hidden">
                            <?php if($loop->index == 0): ?>
                                <div
                                    class="absolute top-0 right-0 bg-yellow-400 text-white w-10 h-10 rounded-bl-2xl flex items-center justify-center shadow-sm">
                                    <i class="fas fa-medal"></i>
                                </div>
                            <?php elseif($loop->index == 1): ?>
                                <div
                                    class="absolute top-0 right-0 bg-gray-300 text-white w-10 h-10 rounded-bl-2xl flex items-center justify-center shadow-sm">
                                    <i class="fas fa-medal"></i>
                                </div>
                            <?php elseif($loop->index == 2): ?>
                                <div
                                    class="absolute top-0 right-0 bg-orange-400 text-white w-10 h-10 rounded-bl-2xl flex items-center justify-center shadow-sm">
                                    <i class="fas fa-medal"></i>
                                </div>
                            <?php endif; ?>

                            <div class="flex items-center gap-4 mb-4">
                                <div
                                    class="w-14 h-14 btn-primary rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    <?php echo e(substr(optional($rank->user)->name ?? 'E', 0, 1)); ?>

                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900"><?php echo e(optional($rank->user)->name ?? 'Empreendedor'); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo e(ucfirst($rank->level)); ?></p>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500"><?php echo e($rank->interactions_count); ?> conexões ·
                                    <?php echo e(number_format(optional($rank)->average_rating ?? 5, 1, ',', '.')); ?> <i
                                        class="fas fa-star text-yellow-500"></i></span>
                                <span class="text-lg font-bold"
                                    style="color: var(--unn-azul-1)"><?php echo e(number_format($rank->score, 0, ',', '.')); ?> pts</span>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-span-3 text-center py-12 text-gray-500 text-lg">
                            Nenhum ranking disponível ainda.<br>
                            Participe de conexões e avaliações para aparecer aqui!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Depoimentos -->
        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">O que dizem nossos membros</h2>

                <div class="grid md:grid-cols-3 gap-8">
                    <?php
                        $testimonials = [
                            ['name' => 'Carlos Eduardo', 'role' => 'CEO, Tech Solutions', 'text' => 'A UNN transformou minha forma de fazer negócios. Em 6 meses, fechei parcerias que mudaram minha empresa.', 'rating' => 5],
                            ['name' => 'Ana Paula Lima', 'role' => 'Fundadora, EcoModa', 'text' => 'O networking aqui é diferente. São conexões genuínas com pessoas que realmente querem ajudar.', 'rating' => 5],
                            ['name' => 'Roberto Silva', 'role' => 'Investidor Anjo', 'text' => 'Encontrei projetos incríveis para investir e empreendedores talentosos. A comunidade é de altíssimo nível.', 'rating' => 5],
                        ];
                    ?>

                    <?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $testimonial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white rounded-3xl p-8 shadow-lg">
                            <div class="flex gap-1 mb-4">
                                <?php for($i = 0; $i < $testimonial['rating']; $i++): ?>
                                    <i class="fas fa-star text-yellow-500"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-gray-600 mb-6 italic">"<?php echo e($testimonial['text']); ?>"</p>
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 btn-primary rounded-full flex items-center justify-center text-white font-bold">
                                    <?php echo e(substr($testimonial['name'], 0, 1)); ?>

                                </div>
                                <div>
                                    <p class="font-bold text-gray-900"><?php echo e($testimonial['name']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo e($testimonial['role']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        <!-- CTA Final -->
        <section class="py-16 px-6 md:px-12 lg:px-24"
            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
            <div class="max-w-4xl mx-auto text-center text-white">
                <h2 class="text-3xl lg:text-4xl font-black mb-4">Pronto para transformar sua rede?</h2>
                <p class="text-lg opacity-90 mb-8">Junte-se a milhares de empreendedores que já estão crescendo juntos.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?php echo e(route('register')); ?>"
                        class="inline-flex items-center justify-center gap-2 bg-white px-6 py-3 sm:px-8 sm:py-4 rounded-full font-bold hover:bg-blue-50 transition"
                        style="color: var(--unn-azul-1)">
                        <i class="fas fa-rocket"></i>
                        Começar agora - É grátis
                    </a>
                    <a href="<?php echo e(route('premium')); ?>"
                        class="inline-flex items-center justify-center gap-2 border-2 border-white text-white px-6 py-3 sm:px-8 sm:py-4 rounded-full font-bold hover:bg-white/10 transition">
                        Ver planos Premium
                    </a>
                </div>
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
        .unn-title-gradient {
            background: linear-gradient(90deg, #2E3192 0%, #0071BC 60%, #29ABE2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }
        .unn-title-hero {
            max-width: 650px;
            font-size: 2.1rem;
            line-height: 1.12;
            font-weight: 900;
            letter-spacing: -0.02em;
            margin-left: 0;
            margin-right: 0;
            overflow-wrap: normal;
            word-break: keep-all;
            hyphens: none;
        }
        @media (min-width: 640px) {
            .unn-title-hero {
                font-size: 2.4rem;
            }
        }
        @media (min-width: 1024px) {
            .unn-title-hero {
                font-size: 2.8rem;
                max-width: 700px;
            }
        }
        @media (max-width: 480px) {
            .unn-title-hero {
                font-size: 1.5rem;
                max-width: 98vw;
            }
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\site\index.blade.php ENDPATH**/ ?>