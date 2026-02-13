

<?php $__env->startSection('title', 'Configurar Pagamentos - UNN'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Configuração de Pagamento (Recebimento)</h1>
    <p class="mb-4 text-gray-600">Configure suas credenciais do MercadoPago para vender seus cursos na plataforma.</p>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="<?php echo e(route('settings.payment.update')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Public Key</label>
                <input type="text" name="public_key" value="<?php echo e($gateway->public_key); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                <p class="text-xs text-gray-500 mt-1">Disponível no painel de desenvolvedores do MercadoPago.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Access Token</label>
                <input type="password" name="access_token" value="<?php echo e($gateway->access_token); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                <p class="text-xs text-gray-500 mt-1">Token de produção.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">Salvar Credenciais</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\settings\payment.blade.php ENDPATH**/ ?>