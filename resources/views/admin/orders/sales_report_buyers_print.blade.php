<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Lista de compradores por item</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
            margin: 0;
            background: #ffffff;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 12px;
        }

        .toolbar button {
            border: 0;
            border-radius: 6px;
            background: #1F5EDB;
            color: #ffffff;
            cursor: pointer;
            font-weight: 700;
            padding: 9px 14px;
        }

        h1 {
            font-size: 20px;
            line-height: 1.2;
            margin: 0 0 6px;
        }

        .meta {
            color: #475569;
            line-height: 1.45;
            margin: 0 0 14px;
        }

        .summary {
            display: table;
            width: 100%;
            margin: 0 0 14px;
            table-layout: fixed;
        }

        .summary-item {
            display: table-cell;
            border: 1px solid #cbd5e1;
            border-left: 0;
            padding: 8px;
            background: #f8fafc;
        }

        .summary-item:first-child {
            border-left: 1px solid #cbd5e1;
        }

        .summary-item span {
            display: block;
            color: #475569;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary-item strong {
            display: block;
            font-size: 14px;
            margin-top: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #e2e8f0;
            font-size: 9px;
            text-transform: uppercase;
        }

        .muted {
            color: #64748b;
            display: block;
            font-size: 9px;
            margin-top: 2px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        @media print {
            .toolbar {
                display: none;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    @php
        $item = $report['item'] ?? [];
        $rows = $report['rows'] ?? collect();
        $summary = $report['summary'] ?? [];
        $period = $report['period'] ?? [];
        $generatedAt = $report['generated_at'] ?? now();
    @endphp

    @if(!empty($autoPrint))
        <div class="toolbar">
            <button type="button" onclick="window.print()">Imprimir / salvar PDF</button>
        </div>
    @endif

    <h1>Lista de compradores por item</h1>
    <p class="meta">
        Item: <strong>{{ $item['title'] ?? 'Item' }}</strong><br>
        Tipo do item: {{ $item['type_label'] ?? '-' }} |
        Tipo de compra: {{ $item['purchase_type_label'] ?? '-' }} |
        ID: {{ (int) ($item['id'] ?? 0) }}<br>
        {{ $period['label'] ?? 'Periodo nao informado' }} |
        Gerado em: {{ $generatedAt ? $generatedAt->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}
    </p>

    <div class="summary">
        <div class="summary-item">
            <span>Compradores</span>
            <strong>{{ (int) ($summary['buyers_count'] ?? 0) }}</strong>
        </div>
        <div class="summary-item">
            <span>Pedidos</span>
            <strong>{{ (int) ($summary['orders_count'] ?? 0) }}</strong>
        </div>
        <div class="summary-item">
            <span>Quantidade</span>
            <strong>{{ (int) ($summary['quantity'] ?? 0) }}</strong>
        </div>
        <div class="summary-item">
            <span>Valor total</span>
            <strong>R$ {{ number_format((float) ($summary['total_amount'] ?? 0), 2, ',', '.') }}</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome do membro</th>
                <th>Valor do item</th>
                <th>Data de compra</th>
                <th class="text-center">Qtd.</th>
                <th>Tipo de compra</th>
                <th>Pedido</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $buyer)
                <tr>
                    <td>
                        <strong>{{ $buyer->buyer_name }}</strong>
                        @if($buyer->buyer_email)
                            <span class="muted">{{ $buyer->buyer_email }}</span>
                        @endif
                        @if($buyer->buyer_phone)
                            <span class="muted">{{ $buyer->buyer_phone }}</span>
                        @endif
                    </td>
                    <td class="text-right">R$ {{ number_format((float) $buyer->total_amount, 2, ',', '.') }}</td>
                    <td>{{ $buyer->purchased_at ? $buyer->purchased_at->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-center">{{ (int) $buyer->quantity }}</td>
                    <td>{{ $buyer->purchase_type_label }}</td>
                    <td>
                        #{{ (int) $buyer->order_id }}
                        @if($buyer->payment_method)
                            <span class="muted">{{ $buyer->payment_method }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Nenhum comprador encontrado para este item.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($autoPrint))
        <script>
            window.addEventListener('load', function () {
                window.setTimeout(function () {
                    window.print();
                }, 250);
            });
        </script>
    @endif
</body>

</html>
