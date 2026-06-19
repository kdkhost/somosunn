@php
    $item = $report['item'] ?? [];
    $rows = $report['rows'] ?? collect();
    $summary = $report['summary'] ?? [];
    $period = $report['period'] ?? [];
@endphp

<div class="sales-buyers-panel">
    <div class="sales-buyers-head">
        <div>
            <div class="sales-buyers-k">Item</div>
            <div class="sales-buyers-title">{{ $item['title'] ?? 'Item' }}</div>
            <div class="sales-buyers-meta">
                ID {{ (int) ($item['id'] ?? 0) }} |
                {{ $item['type_label'] ?? '-' }} |
                {{ $period['label'] ?? 'Periodo nao informado' }}
            </div>
        </div>
    </div>

    <div class="sales-buyers-summary">
        <div>
            <span>Compradores</span>
            <strong>{{ (int) ($summary['buyers_count'] ?? 0) }}</strong>
        </div>
        <div>
            <span>Pedidos</span>
            <strong>{{ (int) ($summary['orders_count'] ?? 0) }}</strong>
        </div>
        <div>
            <span>Quantidade</span>
            <strong>{{ (int) ($summary['quantity'] ?? 0) }}</strong>
        </div>
        <div>
            <span>Valor total</span>
            <strong>R$ {{ number_format((float) ($summary['total_amount'] ?? 0), 2, ',', '.') }}</strong>
        </div>
    </div>

    <div class="table-responsive sales-buyers-table-wrap">
        <table class="table sales-buyers-table mb-0">
            <thead>
                <tr>
                    <th>Nome do membro</th>
                    <th>Valor do item</th>
                    <th>Data de compra</th>
                    <th class="text-center">Quantidade</th>
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
                                <small>{{ $buyer->buyer_email }}</small>
                            @endif
                            @if($buyer->buyer_phone)
                                <small>{{ $buyer->buyer_phone }}</small>
                            @endif
                        </td>
                        <td>R$ {{ number_format((float) $buyer->total_amount, 2, ',', '.') }}</td>
                        <td>{{ $buyer->purchased_at ? $buyer->purchased_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="text-center">{{ (int) $buyer->quantity }}</td>
                        <td>{{ $buyer->purchase_type_label }}</td>
                        <td>
                            #{{ (int) $buyer->order_id }}
                            @if($buyer->payment_method)
                                <small>{{ $buyer->payment_method }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center sales-buyers-empty">
                            Nenhum comprador encontrado para este item no periodo selecionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
