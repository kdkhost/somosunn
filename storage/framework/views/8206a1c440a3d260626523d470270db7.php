<?php
    $company = $company ?? [];
    $primaryColor = $company['primary_color'] ?? '#1F5EDB';
    $invoice->loadMissing(['items', 'user', 'order']);

    $number = $invoice->number ?: ('#' . $invoice->id);
    $issuedAt = $invoice->issued_at ? $invoice->issued_at->format('d/m/Y') : now()->format('d/m/Y');
    $dueAt = $invoice->due_at ? $invoice->due_at->format('d/m/Y') : null;
    $status = (string) ($invoice->status ?? 'issued');

    $fmtMoney = function ($v) {
        return 'R$ ' . number_format((float) $v, 2, ',', '.');
    };

    $logo = $company['logo'] ?? null;
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Fatura <?php echo e($number); ?></title>
    <style>
        @page {
            margin: 0;
        }

        * {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            box-sizing: border-box;
        }

        body {
            font-size: 13px;
            color: #1f2937;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            background: #fff;
        }

        .header {
            background: #f9fafb;
            padding: 40px 50px;
            border-bottom: 2px solid
                <?php echo e($primaryColor); ?>

            ;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .logo {
            max-height: 60px;
            max-width: 200px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: 800;
            color:
                <?php echo e($primaryColor); ?>

            ;
            margin: 0;
            letter-spacing: -1px;
        }

        .invoice-number {
            font-size: 14px;
            color: #6b7280;
            margin-top: 5px;
        }

        .content {
            padding: 40px 50px;
        }

        .info-grid {
            display: table;
            width: 100%;
            border-spacing: 0;
            margin-bottom: 40px;
        }

        .info-col {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
        }

        .info-label {
            font-size: 11px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 13px;
            color: #111827;
        }

        .info-sub {
            color: #6b7280;
            font-size: 12px;
            margin-top: 2px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-paid {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .status-issued {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .status-cancelled {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .table-container {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f9fafb;
            padding: 12px 15px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
            border-top: 1px solid #e5e7eb;
        }

        td {
            padding: 15px;
            vertical-align: top;
            border-bottom: 1px solid #f3f4f6;
        }

        .text-right {
            text-align: right;
        }

        .item-desc {
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .item-sub {
            font-size: 11px;
            color: #6b7280;
        }

        .summary-container {
            margin-top: 30px;
            display: table;
            width: 100%;
        }

        .summary-notes {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-right: 40px;
        }

        .summary-totals {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .total-label {
            display: table-cell;
            font-size: 13px;
            color: #6b7280;
            text-align: right;
            padding-right: 15px;
        }

        .total-value {
            display: table-cell;
            font-size: 13px;
            color: #111827;
            text-align: right;
            font-weight: 500;
            white-space: nowrap;
        }

        .grand-total {
            border-top: 2px solid #f3f4f6;
            padding-top: 12px;
            margin-top: 12px;
        }

        .grand-total .total-label {
            color: #111827;
            font-weight: 700;
            font-size: 16px;
            vertical-align: middle;
        }

        .grand-total .total-value {
            color:
                <?php echo e($primaryColor); ?>

            ;
            font-weight: 800;
            font-size: 20px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .notes-content {
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            font-size: 12px;
            color: #4b5563;
            border: 1px dashed #e5e7eb;
        }

        .footer {
            position: fixed;
            bottom: 40px;
            left: 50px;
            right: 50px;
            border-top: 1px solid #f3f4f6;
            padding-top: 20px;
            color: #9ca3af;
            font-size: 11px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <?php if($logo): ?>
                    <img src="<?php echo e($logo); ?>" class="logo">
                <?php else: ?>
                    <div style="font-size: 24px; font-weight: 800; color: <?php echo e($primaryColor); ?>;">
                        <?php echo e($company['name'] ?? 'SOMOS UNN'); ?>

                    </div>
                <?php endif; ?>
            </div>
            <div class="header-right">
                <h1 class="invoice-title">FATURA</h1>
                <div class="invoice-number"><?php echo e($number); ?></div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="info-grid">
            <div class="info-col">
                <div class="info-label">Emitente</div>
                <div class="info-value" style="font-weight: 700;"><?php echo e($company['name']); ?></div>
                <?php if(!empty($company['address'])): ?>
                    <div class="info-sub"><?php echo e($company['address']); ?></div>
                <?php endif; ?>
                <?php if(!empty($company['phone'])): ?>
                    <div class="info-sub"><?php echo e($company['phone']); ?></div>
                <?php endif; ?>
            </div>
            <div class="info-col">
                <div class="info-label">Cliente</div>
                <div class="info-value" style="font-weight: 700;"><?php echo e($invoice->user->name ?? 'N/A'); ?></div>
                <?php if(!empty($invoice->user->email)): ?>
                    <div class="info-sub"><?php echo e($invoice->user->email); ?></div>
                <?php endif; ?>
            </div>
            <div class="info-col" style="text-align: right;">
                <div class="info-label">Detalhes</div>
                <div class="info-value"><span style="color: #9ca3af;">Data:</span> <?php echo e($issuedAt); ?></div>
                <?php if($dueAt): ?>
                    <div class="info-value"><span style="color: #9ca3af;">Vencimento:</span> <?php echo e($dueAt); ?></div>
                <?php endif; ?>
                <div style="margin-top: 12px;">
                    <?php
                        $statusClass = $status === 'paid' ? 'status-paid' : ($status === 'cancelled' ? 'status-cancelled' : 'status-issued');
                        $statusText = $status === 'paid' ? 'Paga' : ($status === 'cancelled' ? 'Cancelada' : 'Pendente');
                    ?>
                    <span class="status-badge <?php echo e($statusClass); ?>"><?php echo e($statusText); ?></span>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>DESCRIÇÃO</th>
                        <th class="text-right" style="width: 60px;">QTD</th>
                        <th class="text-right" style="width: 110px;">PREÇO</th>
                        <th class="text-right" style="width: 120px;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $invoice->items->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <div class="item-desc"><?php echo e($item->description); ?></div>
                                <?php if(!empty($item->item_type)): ?>
                                    <div class="item-sub"><?php echo e(ucfirst($item->item_type)); ?> <?php if(!empty($item->item_id)): ?>
                                    #<?php echo e($item->item_id); ?> <?php endif; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-right" style="color: #6b7280;"><?php echo e((int) $item->quantity); ?></td>
                            <td class="text-right"><?php echo e($fmtMoney($item->unit_price)); ?></td>
                            <td class="text-right" style="font-weight: 600;"><?php echo e($fmtMoney($item->total_price)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="summary-container">
            <div class="summary-notes">
                <?php if(!empty($invoice->notes)): ?>
                    <div class="info-label" style="margin-bottom: 10px;">Observações</div>
                    <div class="notes-content">
                        <?php echo nl2br(e($invoice->notes)); ?>

                    </div>
                <?php endif; ?>
            </div>
            <div class="summary-totals">
                <div class="total-row">
                    <div class="total-label">Subtotal</div>
                    <div class="total-value"><?php echo e($fmtMoney($invoice->subtotal)); ?></div>
                </div>
                <?php if((float) $invoice->discount_amount > 0): ?>
                    <div class="total-row">
                        <div class="total-label">Desconto</div>
                        <div class="total-value">- <?php echo e($fmtMoney($invoice->discount_amount)); ?></div>
                    </div>
                <?php endif; ?>
                <div class="total-row grand-total">
                    <div class="total-label">Total da Fatura</div>
                    <div class="total-value"><?php echo e($fmtMoney($invoice->total_amount)); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Obrigado pela sua preferência!<br>
        <?php echo e($company['site']); ?> • <?php echo e($company['email']); ?>

    </div>
</body>

</html><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\pdf\invoice.blade.php ENDPATH**/ ?>