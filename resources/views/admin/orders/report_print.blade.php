<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Relatorio Financeiro</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0f172a;
            font-size: 12px;
            margin: 24px;
        }

        h1 {
            margin: 0 0 6px 0;
            font-size: 24px;
        }

        .meta {
            color: #475569;
            margin: 0 0 18px 0;
        }

        .cards {
            display: table;
            width: 100%;
            margin-bottom: 18px;
        }

        .card {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }

        .card + .card {
            border-left: 0;
        }

        .k {
            color: #475569;
            font-size: 11px;
            margin-bottom: 4px;
        }

        .v {
            font-size: 18px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #e2e8f0;
            font-weight: 700;
        }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 10px;
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
