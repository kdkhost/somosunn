

<?php $__env->startSection('title', 'Configurações de Pagamento - UNN'); ?>

<?php $__env->startSection('panel_content'); ?>
    <?php
        $mercadoPagoAccount = $mercadoPagoAccount ?? null;
        $pagSeguroAccount = $pagSeguroAccount ?? null;
    ?>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-2">Configurações de Pagamento</h1>
        <p class="text-slate-600 mb-4">Configure suas credenciais para receber pagamentos diretamente pelas vendas dos seus cursos e mentorias.</p>
        <form method="POST" action="<?php echo e(route('panel.marketplace.gateway.update')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="font-bold text-slate-800 mb-2">MercadoPago</h2>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Public Key</label>
                    <input type="text" name="mp_public_key" maxlength="255" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="<?php echo e(old('mp_public_key', $mercadoPagoAccount->public_key ?? '')); ?>">
                    <label class="block text-sm font-medium text-slate-700 mt-3 mb-1">Access Token</label>
                    <input type="text" name="mp_access_token" maxlength="255" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="<?php echo e(old('mp_access_token', $mercadoPagoAccount->access_token ?? '')); ?>">
                    <div class="flex items-center mt-3">
                        <input type="checkbox" name="mp_enabled" value="1" id="mp_enabled" <?php echo e(old('mp_enabled', $mercadoPagoAccount->enabled ?? false) ? 'checked' : ''); ?>>
                        <label for="mp_enabled" class="ml-2 text-sm text-slate-700">Ativar MercadoPago</label>
                    </div>
                    <button type="button" class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition" onclick="testarConexao('mercadopago')">
                        <i class="fas fa-plug mr-2"></i> Testar Conexão
                    </button>
                </div>
                <div>
                    <h2 class="font-bold text-slate-800 mb-2">PagSeguro</h2>
                    <label class="block text-sm font-medium text-slate-700 mb-1">E-mail</label>
                    <input type="email" name="ps_email" maxlength="255" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="<?php echo e(old('ps_email', $pagSeguroAccount->client_id ?? '')); ?>">
                    <label class="block text-sm font-medium text-slate-700 mt-3 mb-1">Access Token</label>
                    <input type="text" name="ps_access_token" maxlength="255" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="<?php echo e(old('ps_access_token', $pagSeguroAccount->access_token ?? '')); ?>">
                    <div class="flex items-center mt-3">
                        <input type="checkbox" name="ps_enabled" value="1" id="ps_enabled" <?php echo e(old('ps_enabled', $pagSeguroAccount->enabled ?? false) ? 'checked' : ''); ?>>
                        <label for="ps_enabled" class="ml-2 text-sm text-slate-700">Ativar PagSeguro</label>
                    </div>
                    <button type="button" class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition" onclick="testarConexao('pagseguro')">
                        <i class="fas fa-plug mr-2"></i> Testar Conexão
                    </button>
                </div>
            </div>
            <div class="pt-6">
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white rounded-xl font-bold text-base hover:bg-emerald-700 transition">
                    <i class="fas fa-save mr-2"></i> Salvar configurações
                </button>
            </div>
        </form>
        <div id="gateway-feedback" class="mt-4"></div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function testarConexao(provider) {
    let data = {};
    if (provider === 'mercadopago') {
        data = {
            provider: 'mercadopago',
            access_token: document.querySelector('[name=\'mp_access_token\']').value,
            public_key: document.querySelector('[name=\'mp_public_key\']').value,
            env: 'production'
        };
    } else {
        data = {
            provider: 'pagseguro',
            access_token: document.querySelector('[name=\'ps_access_token\']').value,
            email: document.querySelector('[name=\'ps_email\']').value,
            env: 'production'
        };
    }
    fetch("<?php echo e(route('panel.marketplace.gateway.test')); ?>", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
        },
        body: JSON.stringify(data)
    })
    .then(resp => resp.json())
    .then(json => {
        let el = document.getElementById('gateway-feedback');
        if (json.ok) {
            el.innerHTML = `<div class='rounded-xl bg-emerald-100 text-emerald-800 px-4 py-3 mt-4'><i class='fas fa-check-circle mr-2'></i> ${json.message}</div>`;
        } else {
            el.innerHTML = `<div class='rounded-xl bg-amber-100 text-amber-800 px-4 py-3 mt-4'><i class='fas fa-exclamation-triangle mr-2'></i> ${json.message}</div>`;
        }
    })
    .catch(err => {
        let el = document.getElementById('gateway-feedback');
        el.innerHTML = `<div class='rounded-xl bg-red-100 text-red-800 px-4 py-3 mt-4'><i class='fas fa-times-circle mr-2'></i> Erro ao testar conexão.</div>`;
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('panel.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\panel\marketplace\gateway.blade.php ENDPATH**/ ?>