@php
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
@endphp

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Fatura {{ $number }}</title>
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
                {{ $primaryColor }}
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
                {{ $primaryColor }}
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
            width: 40%;
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
        }

        .grand-total .total-value {
            color:
                {{ $primaryColor }}
            ;
            font-weight: 800;
            font-size: 20px;
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
                @if($logo && file_exists($logo))
                    <img src="{{ $logo }}" class="logo">
                @else
                    <div style="font-size: 24px; font-weight: 800; color: {{ $primaryColor }};">
                        {{ $company['name'] ?? 'SOMOS UNN' }}</div>
                @endif
            </div>
            <div class="header-right">
                <h1 class="invoice-title">FATURA</h1>
                <div class="invoice-number">{{ $number }}</div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="info-grid">
            <div class="info-col">
                <div class="info-label">Emitente</div>
                <div class="info-value" style="font-weight: 700;">{{ $company['name'] }}</div>
                @if(!empty($company['address']))
                    <div class="info-sub">{{ $company['address'] }}</div>
                @endif
                @if(!empty($company['phone']))
                    <div class="info-sub">{{ $company['phone'] }}</div>
                @endif
            </div>
            <div class="info-col">
                <div class="info-label">Cliente</div>
                <div class="info-value" style="font-weight: 700;">{{ $invoice->user->name ?? 'N/A' }}</div>
                @if(!empty($invoice->user->email))
                    <div class="info-sub">{{ $invoice->user->email }}</div>
                @endif
            </div>
            <div class="info-col" style="text-align: right;">
                <div class="info-label">Detalhes</div>
                <div class="info-value"><span style="color: #9ca3af;">Data:</span> {{ $issuedAt }}</div>
                @if($dueAt)
                    <div class="info-value"><span style="color: #9ca3af;">Vencimento:</span> {{ $dueAt }}</div>
                @endif
                <div style="margin-top: 12px;">
                    @php
                        $statusClass = $status === 'paid' ? 'status-paid' : ($status === 'cancelled' ? 'status-cancelled' : 'status-issued');
                        $statusText = $status === 'paid' ? 'Paga' : ($status === 'cancelled' ? 'Cancelada' : 'Pendente');
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
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
                    @foreach($invoice->items->sortBy('sort_order') as $item)
                        <tr>
                            <td>
                                <div class="item-desc">{{ $item->description }}</div>
                                @if(!empty($item->item_type))
                                    <div class="item-sub">{{ ucfirst($item->item_type) }} @if(!empty($item->item_id))
                                    #{{ $item->item_id }} @endif</div>
                                @endif
                            </td>
                            <td class="text-right" style="color: #6b7280;">{{ (int) $item->quantity }}</td>
                            <td class="text-right">{{ $fmtMoney($item->unit_price) }}</td>
                            <td class="text-right" style="font-weight: 600;">{{ $fmtMoney($item->total_price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="summary-container">
            <div class="summary-notes">
                @if(!empty($invoice->notes))
                    <div class="info-label" style="margin-bottom: 10px;">Observações</div>
                    <div class="notes-content">
                        {!! nl2br(e($invoice->notes)) !!}
                    </div>
                @endif
            </div>
            <div class="summary-totals">
                <div class="total-row">
                    <div class="total-label">Subtotal</div>
                    <div class="total-value">{{ $fmtMoney($invoice->subtotal) }}</div>
                </div>
                @if((float) $invoice->discount_amount > 0)
                    <div class="total-row">
                        <div class="total-label">Desconto</div>
                        <div class="total-value">- {{ $fmtMoney($invoice->discount_amount) }}</div>
                    </div>
                @endif
                <div class="total-row grand-total">
                    <div class="total-label">Total da Fatura</div>
                    <div class="total-value">{{ $fmtMoney($invoice->total_amount) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Obrigado pela sua preferência!<br>
        {{ $company['site'] }} • {{ $company['email'] }}
    </div>
</body>

</html>