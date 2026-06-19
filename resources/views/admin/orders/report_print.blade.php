<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Relatorio Financeiro</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 9mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0f172a;
            font-size: 9px;
            margin: 0;
        }

        h1 {
            margin: 0 0 6px 0;
            font-size: 18px;
        }

        .meta {
            color: #475569;
            margin: 0 0 10px 0;
            line-height: 1.35;
        }

        .cards {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .card {
            display: table-cell;
            width: 33.33%;
            padding: 7px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }

        .card + .card {
            border-left: 0;
        }

        .k {
            color: #475569;
            font-size: 8px;
            margin-bottom: 4px;
        }

        .v {
            font-size: 13px;
            font-weight: 700;
        }

        table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 8px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            text-align: left;
            vertical-align: top;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        th {
            background: #e2e8f0;
            font-weight: 700;
        }

        th:nth-child(1),
        td:nth-child(1) {
            width: 6%;
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 9%;
        }

        th:nth-child(3),
        td:nth-child(3) {
            width: 24%;
        }

        th:nth-child(4),
        td:nth-child(4) {
            width: 10%;
        }

        th:nth-child(5),
        td:nth-child(5) {
            width: 10%;
        }

        th:nth-child(6),
        td:nth-child(6),
        th:nth-child(7),
        td:nth-child(7),
        th:nth-child(8),
        td:nth-child(8),
        th:nth-child(9),
        td:nth-child(9) {
            width: 7%;
        }

        th:nth-child(10),
        td:nth-child(10) {
            width: 6%;
        }

        th:nth-child(11),
        td:nth-child(11) {
            width: 7%;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 999px;
            font-size: 7px;
            font-weight: 700;
        }

        .badge-manual {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-accounted {
            background: #dcfce7;
            color: #166534;
        }
    </style>
</head>

<body>
    @php
        $generatedAt = $summary['generated_at'] ?? now();
    @endphp

    <h1>Relatorio Financeiro de Pedidos</h1>
    <p class="meta">
        Periodo: {{ $summary['period_label'] ?? '-' }}<br>
        Escopo: {{ strtoupper((string) ($summary['scope'] ?? 'accounted')) }}<br>
        Gerado em: {{ $generatedAt ? $generatedAt->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}
    </p>

    <div class="cards">
        <div class="card">
            <div class="k">Total contabilizado</div>
            <div class="v">R$ {{ number_format((float) ($summary['accounted_total'] ?? 0), 2, ',', '.') }}</div>
            <div>{{ (int) ($summary['accounted_count'] ?? 0) }} pedidos</div>
        </div>
        <div class="card">
            <div class="k">Aprovacoes manuais</div>
            <div class="v">R$ {{ number_format((float) ($summary['manual_total'] ?? 0), 2, ',', '.') }}</div>
            <div>{{ (int) ($summary['manual_count'] ?? 0) }} pedidos</div>
        </div>
        <div class="card">
            <div class="k">Usuarios com manual</div>
            <div class="v">{{ (int) ($summary['manual_users_count'] ?? 0) }}</div>
            <div>Separados do contabilizado</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Data financeira</th>
                <th>Cliente</th>
                <th>Pagamento</th>
                <th>Origem</th>
                <th>Valor bruto</th>
                <th>Desconto</th>
                <th>Cupom</th>
                <th>Total liquido</th>
                <th>Fatura</th>
                <th>Aprovado por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                @php
                    $financialDate = $order->paid_at ?? $order->manual_approved_at ?? $order->created_at;
                    $invoiceLabel = '-';
                    if ($order->invoice) {
                        $invoiceLabel = (string) ($order->invoice->number ?: ('#' . $order->invoice->id));
                    }
                    $discountAmount = (float) $order->financial_discount_amount;
                @endphp
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $financialDate ? $financialDate->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        <strong>{{ $order->user->name ?? 'Usuario removido' }}</strong><br>
                        <span>{{ $order->user->email ?? '' }}</span>
                    </td>
                    <td>{{ $order->payment_method ?: $order->gateway ?: '-' }}</td>
                    <td>
                        @if($order->is_manual_approval)
                            <span class="badge badge-manual">Manual</span>
                        @else
                            <span class="badge badge-accounted">Contabilizado</span>
                        @endif
                    </td>
                    <td>R$ {{ number_format((float) $order->gross_amount, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($discountAmount, 2, ',', '.') }}</td>
                    <td>{{ $order->coupon_code ?: '-' }}</td>
                    <td>R$ {{ number_format((float) ($order->total_amount ?? 0), 2, ',', '.') }}</td>
                    <td>{{ $invoiceLabel }}</td>
                    <td>{{ $order->manualApprover->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center;">Nenhum pedido encontrado no periodo.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
