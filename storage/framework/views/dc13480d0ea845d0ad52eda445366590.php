<?php
    $showNavigation = false;
    $isLegacyRoute = request()->is('backend/*');
    $installRunRoute = $isLegacyRoute ? route('install.run.legacy') : route('install.run');
    $testConnectionRoute = $isLegacyRoute ? route('install.test-connection.legacy') : route('install.test-connection');
    $detectedUrl = $detectedUrl ?? rtrim(request()->root(), '/');
    $appUrlField = old('app_url', $detectedUrl);
?>

<?php $__env->startSection('title','Instalador - UNN'); ?>

<?php $__env->startSection('content'); ?>
<section class="min-h-screen w-full bg-gradient-to-b from-white via-slate-50 to-slate-100 relative overflow-hidden pt-6 md:pt-8">
    <div class="absolute inset-x-0 top-16 h-32 bg-gradient-to-r from-[#3B5AF4]/25 via-transparent to-[#55A0FF]/10 blur-3xl opacity-80 pointer-events-none"></div>
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute h-60 w-60 bg-[#7B4BFF]/30 rounded-full blur-[120px] -top-10 left-1/4 animate-float"></div>
        <div class="absolute h-60 w-60 bg-[#3B5AF4]/30 rounded-full blur-[140px] -bottom-8 right-16 animate-float delay-2000"></div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-8 md:py-10 relative z-10 space-y-8">
        <div class="flex items-center gap-3 w-full max-w-sm mx-auto justify-center">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-[#3B5AF4] to-[#55A0FF] text-white font-black tracking-wide text-xl shadow-lg">UNN</span>
            <div class="flex flex-col leading-tight text-center">
                <span class="text-sm font-semibold text-gray-600">Networking que gera resultados</span>
                <span class="text-xs uppercase tracking-[0.4em] text-gray-400">UNIVERSIDADE DE NEGÓCIOS</span>
            </div>
        </div>
        <div class="text-center space-y-2">
            <p class="text-xs uppercase tracking-[0.4em] text-[#3B5AF4] font-semibold">Instalador guiado</p>
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight my-3">Unifique o portal e o backend em instantes</h1>
            <p class="text-lg text-slate-500 max-w-3xl mx-auto">Siga os passos, valide os requisitos e crie o superadmin enquanto o layout absorve a identidade visual da UNN com toques neon em azul e violeta.</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 items-stretch min-h-[640px]">
            <div class="space-y-6">
                <div class="bg-white/80 border border-white shadow-2xl rounded-3xl p-6 space-y-4 backdrop-blur">
                    <h2 class="text-xl font-semibold text-gray-800">Steps animados</h2>
                    <div class="space-y-4">
                        <?php $__currentLoopData = [
                            ['title'=>'1. Verificar requisitos mínimos','detail'=>'Confirme versão PHP, extensões e permissões.'],
                            ['title'=>'2. Criar superadmin','detail'=>'Informe nome, e-mail e senha para o administrador principal.'],
                            ['title'=>'3. Finalizar e habilitar','detail'=>'Rodar migrations, seeders e registrar ranking para o portal.'],
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="step-card p-4 rounded-2xl border border-transparent bg-gradient-to-br from-white to-slate-50 hover:from-purple-50 hover:border-purple-100 transition-all">
                                <div class="flex items-start gap-3">
                                    <div class="h-10 w-10 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-lg font-semibold shadow-sm">
                                        <?php echo e($index + 1); ?>

                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900"><?php echo e($step['title']); ?></h3>
                                        <p class="text-sm text-gray-500"><?php echo e($step['detail']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <div class="bg-white/90 border border-white shadow-2xl rounded-3xl p-6 space-y-4 backdrop-blur relative overflow-hidden">
                    <div class="absolute -top-14 -right-12 h-32 w-32 rounded-full bg-gradient-to-br from-purple-300 to-blue-400 opacity-30 blur-3xl animate-spin-slow"></div>
                    <h2 class="text-xl font-semibold text-gray-800">Requisitos mínimos</h2>
                    <ul class="space-y-3">
                        <?php $__currentLoopData = $requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center justify-between rounded-2xl px-4 py-3 border border-slate-100 bg-slate-50">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800"><?php echo e($requirement['label']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e($requirement['detail']); ?></p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo e($requirement['ok'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600'); ?>">
                                    <?php echo e($requirement['ok'] ? 'OK' : 'Corrija'); ?>

                                </span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <p class="text-xs text-gray-500">A instalação ainda prossegue mesmo se algum requisito estiver pendente, porém a experiência é melhor com tudo aprovado.</p>
                </div>
            </div>

            <div class="grid gap-6 bg-white/90 border border-white shadow-2xl rounded-3xl p-8 relative overflow-hidden lg:grid-cols-1 h-full">
                <div class="absolute -right-16 -top-6 h-40 w-40 bg-gradient-to-br from-[#3B5AF4]/80 to-[#55A0FF]/50 opacity-60 rounded-full blur-3xl"></div>
                <form method="POST" action="<?php echo e($installRunRoute); ?>" class="space-y-6 w-full h-full flex flex-col">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-6">
                        <div class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-xl backdrop-blur flex flex-col gap-4">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">URL e conexão</h2>
                                <p class="text-sm text-gray-500">URL detectada automaticamente e dados do MySQL antes de criar o superadmin.</p>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="text-xs font-semibold text-gray-600">URL da instalação</label>
                                    <input name="app_url" value="<?php echo e($appUrlField); ?>" required class="mt-1 w-full border border-gray-200 rounded-2xl px-4 py-3 focus:border-[#55A0FF] focus:ring-2 focus:ring-[#3B5AF4]/40" placeholder="https://somosunn.com.br">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600">Host</label>
                                    <input name="db_host" value="<?php echo e(old('db_host', env('DB_HOST'))); ?>" required class="w-full border border-gray-200 rounded-2xl px-4 py-3 focus:border-[#55A0FF] focus:ring-2 focus:ring-[#3B5AF4]/40" placeholder="127.0.0.1">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600">Porta</label>
                                    <input name="db_port" value="<?php echo e(old('db_port', env('DB_PORT', '3306'))); ?>" required class="w-full border border-gray-200 rounded-2xl px-4 py-3 focus:border-[#55A0FF] focus:ring-2 focus:ring-[#3B5AF4]/40" placeholder="3306">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600">Banco</label>
                                    <input name="db_database" value="<?php echo e(old('db_database', env('DB_DATABASE'))); ?>" required class="w-full border border-gray-200 rounded-2xl px-4 py-3 focus:border-[#55A0FF] focus:ring-2 focus:ring-[#3B5AF4]/40" placeholder="unn_db">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600">Usuário</label>
                                    <input name="db_username" value="<?php echo e(old('db_username', env('DB_USERNAME'))); ?>" required class="w-full border border-gray-200 rounded-2xl px-4 py-3 focus:border-[#55A0FF] focus:ring-2 focus:ring-[#3B5AF4]/40" placeholder="root">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs font-semibold text-gray-600">Senha</label>
                                    <input name="db_password" type="password" value="<?php echo e(old('db_password', env('DB_PASSWORD'))); ?>" class="w-full border border-gray-200 rounded-2xl px-4 py-3 focus:border-[#55A0FF] focus:ring-2 focus:ring-[#3B5AF4]/40" placeholder="••••••••">
                                </div>
                                <div class="md:col-span-2">
                                    <button type="button" id="test-connection-btn" class="mt-1 w-full text-[#3B5AF4] border border-[#3B5AF4] rounded-2xl px-4 py-3 font-semibold transition hover:bg-[#3B5AF4]/10">Testar conexão</button>
                                    <p id="test-status" class="text-xs font-semibold text-[#3B5AF4] hidden hidden-status mt-2"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6 flex-1 flex flex-col">
                        <div class="space-y-2">
                            <h2 class="text-2xl font-bold text-gray-900">Crie o superadmin</h2>
                            <p class="text-sm text-gray-500">Esse usuário recebe nível "sucesso" e libera o painel UNN.</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl backdrop-blur space-y-4 flex-1 flex flex-col">
                            <div>
                                <label class="text-xs font-semibold text-gray-600">Nome completo</label>
                                <input name="name" required class="mt-1 w-full border border-gray-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="Nome do administrador">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600">E-mail</label>
                                <input name="email" type="email" required class="mt-1 w-full border border-gray-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="admin@example.com">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600">Senha</label>
                                <input name="password" type="password" required minlength="8" class="mt-1 w-full border border-gray-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="********">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600">Confirmação</label>
                                <input name="password_confirmation" type="password" required minlength="8" class="mt-1 w-full border border-gray-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="********">
                            </div>
                        </div>
                        <input type="hidden" name="level" value="sucesso">
                    </div>
                    <button type="submit" class="w-full text-white px-6 py-3 rounded-full font-semibold text-sm shadow-[0_20px_35px_-20px_rgba(59,90,244,0.8)] bg-gradient-to-r from-[#3B5AF4] via-[#7B4BFF] to-[#55A0FF] hover:shadow-[0_25px_45px_-20px_rgba(59,90,244,0.9)] transition-all duration-300">Instalar agora</button>
                    <p class="text-xs text-gray-500 mt-2">Ao criar o superadmin, o instalador irá rodar key:generate, migrate --force e seed automaticamente antes de finalizar.</p>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
    .animate-fadeInUp {
        animation: fadeInUp 0.9s ease forwards;
    }
    .step-card {
        position: relative;
        overflow: hidden;
    }
    .step-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(91,33,182,0.08), rgba(37,99,235,0));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .step-card:hover::after {
        opacity: 1;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(24px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes spinSlow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin-slow {
        animation: spinSlow 16s linear infinite;
    }
    .animate-float {
        animation: floatGlow 8s ease-in-out infinite;
    }
    .delay-2000 {
        animation-delay: 2s;
    }
    @keyframes floatGlow {
        0% { transform: translateY(0); opacity: 0.4; }
        50% { transform: translateY(-18px); opacity: 0.75; }
        100% { transform: translateY(0); opacity: 0.4; }
    }
    .hidden-status {
        opacity: 0;
    }
</style>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const btn = document.getElementById('test-connection-btn');
            const status = document.getElementById('test-status');
            if(!btn || !status) return;

            btn.addEventListener('click', function(){
                const form = btn.closest('form');
                const data = new FormData(form);
                btn.disabled = true;
                btn.textContent = 'Testando...';
                status.classList.remove('text-rose-500', 'text-emerald-600', 'hidden-status', 'hidden');
                status.textContent = 'Conectando...';

                fetch('<?php echo e($testConnectionRoute); ?>', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name=\"_token\"]').value,
                        'Accept': 'application/json',
                    },
                    body: data,
                }).then(async response => {
                    btn.disabled = false;
                    btn.textContent = 'Testar conexão';
                    if(response.ok){
                        const json = await response.json().catch(()=>null);
                        const message = (json && json.message) ? json.message : 'Conexão bem sucedida.';
                        status.textContent = message;
                        status.classList.add('text-emerald-600');
                        pushToast(message, 'success');
                    } else {
                        const contentType = response.headers.get('content-type') || '';
                        let message = 'Falha ao testar.';
                        if(contentType.includes('application/json')){
                            const json = await response.json().catch(()=>null);
                            if(json && json.message){ message = json.message; }
                            if(json && json.debug && json.debug.exception){
                                message += ` [${json.debug.exception}]`;
                            }
                        } else {
                            const text = await response.text();
                            message = `Falha ao testar: ${text.split('\\n')[0]}`;
                        }
                        status.textContent = message;
                        status.classList.add('text-rose-500');
                        pushToast(message, 'error', true);
                    }
                }).catch(err => {
                    btn.disabled = false;
                    btn.textContent = 'Testar conexão';
                    const message = 'Falha ao testar: '+err.message;
                    status.textContent = message;
                    status.classList.add('text-rose-500');
                    pushToast(message, 'error', true);
                });
            });

            function pushToast(message, type='info', persist=false){
                let container = document.getElementById('toast-container');
                if(!container){
                    container = document.createElement('div');
                    container.id = 'toast-container';
                    container.className = 'fixed top-5 right-5 flex flex-col gap-2 z-50';
                    document.body.appendChild(container);
                }
                const toast = document.createElement('div');
                const styleByType = type === 'error'
                    ? 'border-red-300/70 bg-white/60 text-red-700'
                    : 'border-emerald-300/70 bg-white/60 text-emerald-700';
                toast.className = `px-4 py-3 rounded-2xl border shadow-2xl backdrop-blur-lg ${styleByType}`;
                toast.textContent = message;
                container.appendChild(toast);
                const timeout = persist ? 10000 : 4200;
                let timer = setTimeout(removeToast, timeout);
                toast.addEventListener('mouseenter', ()=> clearTimeout(timer));
                toast.addEventListener('mouseleave', ()=> timer = setTimeout(removeToast, timeout));
                function removeToast(){
                    toast.classList.add('opacity-0');
                    setTimeout(()=>toast.remove(), 300);
                }
            }
        });
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\install\index.blade.php ENDPATH**/ ?>