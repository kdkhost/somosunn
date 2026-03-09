<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contabilidade do Membro</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #0f172a; margin: 24px; }
        h1, h2 { margin: 0 0 12px; }
        p { margin: 0; }
        .meta { margin-bottom: 24px; color: #475569; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin: 24px 0; }
        .card { border: 1px solid #e2e8f0; border-radius: 16px; padding: 16px; }
        .label { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: .08em; }
        .value { font-size: 28px; font-weight: 800; margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #e2e8f0; padding: 10px 12px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 12px; text-transform: uppercase; }
        .section { margin-top: 32px; }
        .print-button { margin-bottom: 20px; padding: 10px 16px; border-radius: 999px; background: #2563eb; color: white; border: 0; font-weight: 700; cursor: pointer; }
        @media print {
            .print-button { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    @php
        $money = fn($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
        $orderItemsLabel = fn($order) => ($order->items->pluck('title')->filter()->take(3)->join(', ') ?: '-');
    @endphp

    <button class="print-button" onclick="window.print()">Imprimir</button>

    <h1>Contabilidade do Membro</h1>
    <div class="meta">
        <p>Periodo: {{ $period['label'] ?? 'Mensal' }}</p>
        <p>Gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="grid">
        <div class="card">
            <div class="label">Receita bruta de vendas</div>
            <div class="value">{{ $money($summary['sales_gross'] ?? 0) }}</div>
        </div>
        <div class="card">
            <div class="label">Despesas em compras</div>
            <div class="value">{{ $money($summary['purchase_net'] ?? 0) }}</div>
        </div>
        <div class="card">
            <div class="label">Resultado geral</div>
            <div class="value">{{ $money($summary['overall_net'] ?? 0) }}</div>
        </div>
    </div>

    <div class="section">
        <h2>Vendas detalhadas</h2>
        <table>
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Data</th>
                    <th>Comprador</th>
                    <th>Itens</th>
                    <th>Cobranca</th>
                    <th>Estorno</th>
                    <th>Taxas</th>
                    <th>Liquido</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesOrders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ optional($order->paid_at ?: $order->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->user->name ?? 'Usuario removido' }}</td>
                        <td>{{ $orderItemsLabel($order) }}</td>
                        <td>{{ $money($order->charged_amount) }}</td>
                        <td>{{ $money($order->refunded_amount) }}</td>
                        <td>{{ $money((float) $order->platform_fee_amount + (float) $order->fee_amount) }}</td>
                        <td>{{ $money($order->charged_amount - $order->refunded_amount - ((float) $order->platform_fee_amount + (float) $order->fee_amount)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Nenhuma venda no periodo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Compras detalhadas</h2>
        <table>
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Data</th>
                    <th>Vendedor</th>
                    <th>Itens</th>
                    <th>Cobranca</th>
                    <th>Estorno</th>
                    <th>Despesa liquida</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ optional($order->paid_at ?: $order->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->seller->name ?? 'Plataforma' }}</td>
                        <td>{{ $orderItemsLabel($order) }}</td>
                        <td>{{ $money($order->charged_amount) }}</td>
                        <td>{{ $money($order->refunded_amount) }}</td>
                        <td>{{ $money($order->charged_amount - $order->refunded_amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Nenhuma compra no periodo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 250);
        });
    </script>
</body>
</html>
