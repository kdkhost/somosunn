<?php $__env->startSection('title', 'Pagamentos do Marketplace - UNN'); ?>

<?php $__env->startSection('panel_content'); ?>
    <?php
        $paymentsConfigured = (bool) ($paymentsConfigured ?? false);
        $webhookUrl = (string) ($webhookUrl ?? '');
        $isAdmin = auth()->user() && auth()->user()->isAdmin();
    ?>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Pagamentos</h1>
                <p class="text-slate-600 mt-1">Configuração compartilhada (multi-tenant) para toda a plataforma.</p>
            </div>
            <a href="<?php echo e(route('panel.marketplace.index')); ?>"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mt-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl <?php echo e($paymentsConfigured ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600'); ?> flex items-center justify-center">
                <i class="fas <?php echo e($paymentsConfigured ? 'fa-check-circle' : 'fa-exclamation-triangle'); ?> text-xl"></i>
            </div>
            <div class="flex-1">
                <div class="font-extrabold text-slate-900"><?php echo e($paymentsConfigured ? 'MercadoPago habilitado' : 'MercadoPago não configurado'); ?></div>
                <p class="text-slate-600 mt-1">
                    Este sistema utiliza <strong>uma única configuração</strong> do gateway (multi-tenant) para toda a plataforma.
                    Cada venda é registrada com <strong>vendedor</strong> e <strong>tipo</strong> (curso, mentoria, evento e marketplace).
                </p>

                <?php if($isAdmin): ?>
                    <div class="mt-4">
                        <a href="<?php echo e(route('admin.settings', ['group' => 'gateway'])); ?>"
                            class="inline-flex items-center justify-center rounded-full bg-[#1F5EDB] px-6 py-3 text-sm font-bold text-white hover:brightness-110 transition">
                            <i class="fas fa-cogs mr-2"></i> Abrir configurações do gateway
                        </a>
                    </div>
                <?php else: ?>
                    <div class="mt-4 rounded-2xl bg-slate-50 border border-slate-100 p-4 text-sm text-slate-700">
                        <i class="fas fa-info-circle mr-2 text-slate-500"></i>
                        As credenciais do gateway são gerenciadas pelos administradores da plataforma.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($webhookUrl !== ''): ?>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mt-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-link text-slate-500"></i> URL de notificação (Webhook)
            </h2>
            <p class="text-slate-600 mt-1">Caso precise informar manualmente no painel do MercadoPago, utilize:</p>

            <div class="mt-4 flex flex-col md:flex-row gap-3">
                <input id="webhook-url" type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 bg-white" readonly value="<?php echo e($webhookUrl); ?>">
                <button type="button" id="copy-webhook"
                    class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-800 transition">
                    <i class="fas fa-copy mr-2"></i> Copiar
                </button>
            </div>
            <p class="text-xs text-slate-500 mt-3">O sistema também envia esta URL automaticamente no checkout.</p>
        </div>

        <?php $__env->startPush('scripts'); ?>
            <script>
                document.addEventListener('click', async function (e) {
                    const btn = e.target.closest('#copy-webhook');
                    if (!btn) return;

                    const input = document.getElementById('webhook-url');
                    if (!input) return;

                    try {
                        await navigator.clipboard.writeText(input.value || '');
                        if (typeof toastr !== 'undefined') toastr.success('Copiado!');
                    } catch (err) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Erro', text: 'Não foi possível copiar. Copie manualmente.' });
                        }
                    }
                });
            </script>
        <?php $__env->stopPush(); ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('panel.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\panel\marketplace\payments.blade.php ENDPATH**/ ?>