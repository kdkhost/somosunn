<?php $__env->startSection('title', 'Minhas Vendas - UNN'); ?>

<?php $__env->startSection('panel_content'); ?>
    <?php
        $orders = $orders ?? null;
        $paidTotal = (float) ($paidTotal ?? 0);
        $platformFeeTotal = (float) ($platformFeeTotal ?? 0);
        $netTotal = (float) ($netTotal ?? 0);
        $paidCount = (int) ($paidCount ?? 0);
    ?>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Minhas vendas</h1>
                <p class="text-slate-600 mt-1">Pedidos do marketplace vinculados ao seu usuário.</p>
            </div>
            <a href="<?php echo e(route('panel.marketplace.index')); ?>"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="text-sm font-bold text-slate-500">Vendas pagas</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1"><?php echo e($paidCount); ?></div>
        </div>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="text-sm font-bold text-slate-500">Total líquido (pagos)</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">R$ <?php echo e(number_format($netTotal, 2, ',', '.')); ?></div>
            <div class="text-xs text-slate-500 mt-2">
                Bruto: R$ <?php echo e(number_format($paidTotal, 2, ',', '.')); ?> • Comissão: R$ <?php echo e(number_format($platformFeeTotal, 2, ',', '.')); ?>

            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-0 mt-6 overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-receipt text-slate-500"></i> Pedidos
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left font-bold px-6 py-4 w-28">Pedido</th>
                        <th class="text-left font-bold px-6 py-4">Comprador</th>
                        <th class="text-left font-bold px-6 py-4">Itens</th>
                        <th class="text-left font-bold px-6 py-4 w-40">Total</th>
                        <th class="text-left font-bold px-6 py-4 w-32">Status</th>
                        <th class="text-left font-bold px-6 py-4 w-44">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = ($orders ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $items = $order->items ?? collect();
                            $itemsLabel = $items->pluck('title')->filter()->take(3)->join(', ');
                            $itemsCount = $items->count();
                            if ($itemsCount > 3) {
                                $itemsLabel .= '…';
                            }

                            $status = (string) ($order->status ?? '');
                            $statusLabel = match ($status) {
                                'paid' => 'Pago',
                                'pending' => 'Pendente',
                                'failed' => 'Falhou',
                                'refunded' => 'Reembolsado',
                                default => $status ?: '—',
                            };
                            $statusClass = match ($status) {
                                'paid' => 'bg-emerald-500/10 text-emerald-700',
                                'pending' => 'bg-amber-500/10 text-amber-700',
                                'failed' => 'bg-rose-500/10 text-rose-700',
                                'refunded' => 'bg-slate-500/10 text-slate-700',
                                default => 'bg-slate-500/10 text-slate-700',
                            };
                        ?>
                        <tr>
                            <td class="px-6 py-4 font-extrabold text-slate-900">#<?php echo e($order->id); ?></td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900"><?php echo e($order->user->name ?? '—'); ?></div>
                                <div class="text-xs text-slate-500"><?php echo e($order->user->email ?? ''); ?></div>
                            </td>
                            <td class="px-6 py-4 text-slate-700"><?php echo e($itemsLabel !== '' ? $itemsLabel : '—'); ?></td>
                            <td class="px-6 py-4 font-extrabold text-slate-900">R$ <?php echo e(number_format((float) ($order->total_amount ?? 0), 2, ',', '.')); ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold <?php echo e($statusClass); ?>">
                                    <?php echo e($statusLabel); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500"><?php echo e(optional($order->created_at)->format('d/m/Y H:i')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">Nenhuma venda encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(method_exists($orders, 'links')): ?>
            <div class="p-6 border-t border-slate-100">
                <?php echo e($orders->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('panel.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\panel\marketplace\sales.blade.php ENDPATH**/ ?>