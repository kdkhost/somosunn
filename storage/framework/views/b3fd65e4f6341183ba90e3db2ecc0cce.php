

<?php $__env->startSection('title', 'UNN - Conectando Empreendedores'); ?>

<?php $__env->startSection('content'); ?>
<section id="inicio" class="pt-28 md:pt-40 pb-20 px-6 md:px-12 lg:px-24 bg-gradient-to-b from-slate-50 via-white to-white">
    <div class="max-w-7xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div class="animate-fadeInUp">
                <h1 class="text-5xl lg:text-6xl font-900 leading-tight mb-8">
                    Conectando empreendedores.<br />
                    <span class="text-gradient">Criando oportunidades reais.</span>
                </h1>
                <p class="text-lg text-gray-600 mb-10 leading-relaxed max-w-xl">
                    A UNN é uma comunidade de networking estratégico onde empreendedores compartilham experiências,
                    constroem conexões e crescem juntos.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?php echo e(route('register')); ?>" class="btn-primary text-white px-10 py-4 rounded-lg font-semibold text-lg">
                        Quero fazer parte <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                    <a href="#sobre" class="border-2 border-gray-300 text-gray-700 px-10 py-4 rounded-lg font-semibold hover:border-blue-600 hover:text-blue-600 transition">Conheça a UNN</a>
                </div>
            </div>

            <div class="hidden lg:block animate-float">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-[#2563EB] via-[#D946EF] to-[#5B21B6] rounded-3xl opacity-28 blur-3xl"></div>
                    <img src="https://images.unsplash.com/photo-1675716921224-e087a0cca69a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080" alt="Networking event" class="relative w-full h-96 object-cover rounded-3xl shadow-2xl">
                </div>
            </div>
        </div>
    </div>
</section>

<section id="sobre" class="py-24 px-6 md:px-12 lg:px-24 bg-white">
    <div class="max-w-7xl mx-auto">
        <h2 class="section-title text-4xl lg:text-5xl mb-4 font-900">O que é a UNN</h2>
        <p class="text-gray-600 text-lg mb-20 max-w-2xl mt-8">A UNN nasceu para unir empreendedores que acreditam no crescimento colaborativo.</p>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="card p-8 rounded-2xl">
                <div class="text-4xl text-blue-600 mb-4"><i class="fas fa-handshake"></i></div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Conexões reais</h3>
                <p class="text-gray-600">Networking genuíno com empreendedores que compartilham seus valores</p>
            </div>

            <div class="card p-8 rounded-2xl">
                <div class="text-4xl text-green-600 mb-4"><i class="fas fa-chart-line"></i></div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Crescimento coletivo</h3>
                <p class="text-gray-600">Juntos somos mais fortes e alcançamos resultados maiores</p>
            </div>

            <div class="card p-8 rounded-2xl">
                <div class="text-4xl text-purple-600 mb-4"><i class="fas fa-lightbulb"></i></div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Troca de experiências</h3>
                <p class="text-gray-600">Aprenda com quem já passou pelos desafios que você enfrenta</p>
            </div>

            <div class="card p-8 rounded-2xl">
                <div class="text-4xl text-orange-600 mb-4"><i class="fas fa-briefcase"></i></div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Oportunidades de negócios</h3>
                <p class="text-gray-600">Parcerias estratégicas que geram resultados concretos</p>
            </div>
        </div>
    </div>
</section>

    <section class="py-24 px-6 md:px-12 lg:px-24 bg-slate-50">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-12 ">
                <h2 class="section-title text-4xl lg:text-5xl font-900">Palestras gratuitas que chegam em breve</h2>
                <span class="text-sm uppercase text-gray-500 tracking-wide">Atualizado diariamente</span>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <?php $__empty_1 = true; $__currentLoopData = $freeEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="card p-8 rounded-3xl bg-white border border-gray-200 shadow-sm hover:shadow-lg transition">
                        <p class="text-sm font-semibold uppercase text-blue-600 mb-2">Gratuita</p>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4"><?php echo e($event->title); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo e(Str::limit($event->description, 140)); ?></p>
                        <div class="text-xs text-gray-500 uppercase tracking-wide mb-6">
                            <?php echo e(\Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i')); ?> · <?php echo e($event->location); ?>

                        </div>
                        <a href="#" class="btn-primary text-white px-6 py-3 rounded-full font-semibold inline-flex items-center gap-2">
                            Quero participar <i class="fas fa-arrow-right"></i>
                        </a>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <?php for($i = 0; $i < 3; $i++): ?>
                        <div class="card p-8 rounded-3xl border border-dashed border-gray-300 text-center text-gray-500">
                            <p class="text-sm font-semibold mb-2">Espaço reservado</p>
                            <p>Conecte seu banco de dados para enxergar eventos ao vivo.</p>
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="py-24 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-10">
                <div>
                    <h2 class="section-title text-4xl lg:text-5xl font-900">Mentorias premium</h2>
                    <p class="text-gray-500 text-sm">Conteúdo gravado + acompanhamento dos mentores parceiros</p>
                </div>
                <a href="portal.html" class="text-sm font-semibold text-blue-700 hover:text-blue-900 transition">Ver todas as mentorias <i class="fas fa-arrow-right ml-2"></i></a>
            </div>
            <div class="grid lg:grid-cols-3 gap-8">
                <?php $__empty_1 = true; $__currentLoopData = $paidMentorings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mentorship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="card p-8 rounded-3xl bg-slate-50 border border-gray-100 transition hover:border-blue-200">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs uppercase tracking-wide text-gray-500"><?php echo e(optional($mentorship->mentor)->name ?? 'Mentor UNN'); ?></span>
                            <span class="text-lg font-bold text-purple-600">R$ <?php echo e(number_format($mentorship->price, 2, ',', '.')); ?></span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3"><?php echo e($mentorship->title); ?></h3>
                        <p class="text-gray-600 mb-6"><?php echo e(Str::limit($mentorship->description, 130)); ?></p>
                        <p class="text-sm text-gray-500 mb-6">Vagas: <strong><?php echo e($mentorship->slots); ?></strong></p>
                        <button class="btn-primary text-white px-6 py-3 rounded-full font-semibold">Garantir vaga</button>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-1 lg:col-span-3 text-center text-gray-500 border border-dashed border-gray-300 rounded-3xl p-12">
                        <p>Conecte o backend para cadastrar mentorias pagas.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="py-24 px-6 md:px-12 lg:px-24 bg-slate-50">
        <div class="max-w-6xl mx-auto">
            <h2 class="section-title text-4xl lg:text-5xl font-900 text-center mb-10">Comunidade por níveis</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="card rounded-3xl p-8 bg-white border border-gray-200">
                    <p class="text-sm font-semibold text-gray-500 uppercase mb-2">Empreendedores iniciantes</p>
                    <p class="text-5xl font-bold text-blue-600"><?php echo e($levelSummary['iniciante'] ?? 0); ?></p>
                    <p class="text-gray-500 mt-3">Conectados entre si e acolhidos por quem já percorreu a jornada.</p>
                </div>
                <div class="card rounded-3xl p-8 bg-white border border-gray-200">
                    <p class="text-sm font-semibold text-gray-500 uppercase mb-2">Empresários de sucesso</p>
                    <p class="text-5xl font-bold text-purple-600"><?php echo e($levelSummary['sucesso'] ?? 0); ?></p>
                    <p class="text-gray-500 mt-3">Mentores ativos, parceiros e investidores prontos para novas oportunidades.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="section-title text-4xl lg:text-5xl font-900">Ranking do networking</h2>
                    <p class="text-gray-500 text-sm">Baseado nas avaliações que os empreendedores recebem após cada conexão.</p>
                </div>
                <span class="text-sm uppercase tracking-wider text-gray-500"><?php echo e($topRankings->count()); ?> líderes</span>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__empty_1 = true; $__currentLoopData = $topRankings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="card rounded-3xl p-6 border border-gray-200 shadow-sm hover:shadow-lg transition">
                        <p class="text-xs uppercase text-gray-500"><?php echo e(ucfirst($rank->level)); ?></p>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo e(optional($rank->user)->name ?? 'Empreendedor'); ?></h3>
                        <p class="text-sm text-gray-500 mb-4"><?php echo e($rank->interactions_count); ?> conexões avaliadas · Média <?php echo e(number_format($rank->average_rating, 1, ',', '.')); ?></p>
                        <div class="text-lg font-bold text-blue-600">Score <?php echo e(number_format($rank->score, 2, ',', '.')); ?></div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-1 lg:col-span-3 text-center text-gray-500 border border-dashed border-gray-300 rounded-3xl p-12">
                        <p>Assim que houver avaliações os líderes aparecerÃ£o aqui.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/site/index.blade.php ENDPATH**/ ?>