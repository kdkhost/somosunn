@php
    $company = $company ?? [];
    $invoice->loadMissing(['items', 'user', 'order']);

    $number = $invoice->number ?: ('#' . $invoice->id);
    $issuedAt = $invoice->issued_at ? $invoice->issued_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
    $dueAt = $invoice->due_at ? $invoice->due_at->format('d/m/Y') : null;
    $status = (string) ($invoice->status ?? 'issued');

    $fmtMoney = function ($v) {
        return 'R$ ' . number_format((float) $v, 2, ',', '.');
    };
@endphp

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Fatura {{ $number }}</title>
    <style>
        * { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; }
        body { font-size: 12px; color: #111827; margin: 0; padding: 0; }
        .wrap { padding: 28px; }
        .topbar { background: #1F5EDB; color: #fff; padding: 18px 20px; border-radius: 10px; }
        .topbar h1 { margin: 0; font-size: 18px; }
        .muted { color: #6b7280; }
        .card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; margin-top: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 8px; vertical-align: top; }
        th { text-align: left; font-size: 11px; color: #374151; border-bottom: 1px solid #e5e7eb; }
        td { border-bottom: 1px solid #f3f4f6; }
        .right { text-align: right; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-issued { background: #dbeafe; color: #1e40af; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .no-border td { border-bottom: 0; }
        .totals td { border-bottom: 0; padding: 6px 8px; }
        .totals .label { color: #6b7280; }
        .footer { margin-top: 18px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topbar">
            <h1>Fatura {{ $number }}</h1>
            <div style="margin-top:6px;font-size:11px;opacity:.95;">
                {{ $company['name'] ?? 'UNN' }}
            </div>
        </div>

        <div class="card">
            <table class="no-border">
                <tr>
                    <td style="width:55%;">
                        <div style="font-weight:700;">Emitente</div>
                        <div>{{ $company['name'] ?? '' }}</div>
                        @if(!empty($company['address']))
                            <div class="muted">{{ $company['address'] }}</div>
                        @endif
                        @if(!empty($company['email']))
                            <div class="muted">{{ $company['email'] }}</div>
                        @endif
                        @if(!empty($company['phone']))
                            <div class="muted">{{ $company['phone'] }}</div>
                        @endif
                    </td>
                    <td style="width:45%;">
                        <div style="font-weight:700;">Cliente</div>
                        <div>{{ $invoice->user?->name ?? '' }}</div>
                        @if(!empty($invoice->user?->email))
                            <div class="muted">{{ $invoice->user->email }}</div>
                        @endif

                        <div style="margin-top:10px;">
                            <div><span class="muted">Emissão:</span> {{ $issuedAt }}</div>
                            @if($dueAt)
                                <div><span class="muted">Vencimento:</span> {{ $dueAt }}</div>
                            @endif
                            <div style="margin-top:6px;">
                                @php
                                    $badgeClass = $status === 'paid' ? 'badge-paid' : ($status === 'cancelled' ? 'badge-cancelled' : 'badge-issued');
                                    $badgeText = $status === 'paid' ? 'PAGA' : ($status === 'cancelled' ? 'CANCELADA' : 'EMITIDA');
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="card">
            <div style="font-weight:700;margin-bottom:10px;">Itens</div>
            <table>
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th class="right" style="width:70px;">Qtd</th>
                        <th class="right" style="width:120px;">Valor</th>
                        <th class="right" style="width:120px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items->sortBy('sort_order') as $item)
                        <tr>
                            <td>
                                <div style="font-weight:700;">{{ $item->description }}</div>
                                @if(!empty($item->item_type))
                                    <div class="muted">Tipo: {{ $item->item_type }}@if(!empty($item->item_id)) #{{ $item->item_id }}@endif</div>
                                @endif
                            </td>
                            <td class="right">{{ (int) $item->quantity }}</td>
                            <td class="right">{{ $fmtMoney($item->unit_price) }}</td>
                            <td class="right">{{ $fmtMoney($item->total_price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="totals" style="margin-top:10px;">
                <tr>
                    <td></td>
                    <td class="right label" style="width:160px;">Subtotal</td>
                    <td class="right" style="width:120px;">{{ $fmtMoney($invoice->subtotal) }}</td>
                </tr>
                @if((float) $invoice->discount_amount > 0)
                    <tr>
                        <td></td>
                        <td class="right label">Desconto</td>
                        <td class="right">- {{ $fmtMoney($invoice->discount_amount) }}</td>
                    </tr>
                @endif
                <tr>
                    <td></td>
                    <td class="right label" style="font-weight:700;color:#111827;">Total</td>
                    <td class="right" style="font-weight:700;">{{ $fmtMoney($invoice->total_amount) }}</td>
                </tr>
            </table>
        </div>

        @if(!empty($invoice->notes))
            <div class="card">
                <div style="font-weight:700;margin-bottom:8px;">Observações</div>
                <div class="muted">{!! nl2br(e($invoice->notes)) !!}</div>
            </div>
        @endif

        <div class="footer">
            Documento gerado automaticamente pelo sistema. {{ $company['site'] ?? '' }}
        </div>
    </div>
</body>
</html>

