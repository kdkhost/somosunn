@extends('admin.layouts.app')

@section('title', 'Minhas Vendas')
@section('page_title', 'Minhas Vendas')

@section('content')
    @php
        $orders = $orders ?? collect();
        $paidTotal = (float) ($paidTotal ?? 0);
        $discountTotal = (float) ($discountTotal ?? 0);
        $chargedTotal = (float) ($chargedTotal ?? 0);
        $platformFeeTotal = (float) ($platformFeeTotal ?? 0);
        $netTotal = (float) ($netTotal ?? 0);
        $paidCount = (int) ($paidCount ?? 0);
        $pendingCount = $orders instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? 0
            : $orders->where('status', 'pending')->count();
    @endphp

    {{-- KPI --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success shadow-sm">
                <div class="inner">
                    <h3>{{ $paidCount }}</h3>
                    <p>Vendas Confirmadas</p>
                </div>
                <div class="icon"><i class="fas fa-check-double"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-primary shadow-sm">
                <div class="inner">
                    <h3>R$ {{ number_format($paidTotal, 0, ',', '.') }}</h3>
                    <p>Faturamento Bruto</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-danger shadow-sm">
                <div class="inner">
                    <h3>R$ {{ number_format($platformFeeTotal, 0, ',', '.') }}</h3>
                    <p>Comissão Plataforma</p>
                </div>
                <div class="icon"><i class="fas fa-percentage"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info shadow-sm">
                <div class="inner">
                    <h3>R$ {{ number_format($netTotal, 0, ',', '.') }}</h3>
                    <p>Líquido Recebido</p>
                </div>
                <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
            </div>
        </div>
    </div>

    {{-- Resumo financeiro --}}
    <div class="callout callout-info shadow-sm mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h6 class="font-weight-bold mb-1"><i class="fas fa-chart-pie mr-2"></i>Resumo Financeiro</h6>
                <p class="mb-0 text-muted">
                    Bruto <strong>R$ {{ number_format($paidTotal, 2, ',', '.') }}</strong>
                    &minus; Descontos <strong>R$ {{ number_format($discountTotal, 2, ',', '.') }}</strong>
                    = Cobrado <strong>R$ {{ number_format($chargedTotal, 2, ',', '.') }}</strong>
                    &minus; Comissão <strong>R$ {{ number_format($platformFeeTotal, 2, ',', '.') }}</strong>
                    = Líquido <strong class="text-success">R$ {{ number_format($netTotal, 2, ',', '.') }}</strong>
                </p>
            </div>
            <a href="{{ route('admin.marketplace.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 mt-2 mt-md-0">
                <i class="fas fa-arrow-left mr-1"></i> Voltar ao Marketplace
            </a>
        </div>
    </div>

    {{-- Lista de vendas --}}
    <div class="card card-outline card-dark shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-receipt mr-2 text-primary"></i>Histórico de Vendas
            </h3>
            <div class="card-tools">
                <span class="badge badge-light border px-3 py-2">
                    {{ method_exists($orders, 'total') ? $orders->total() : count($orders) }} venda(s)
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            @forelse($orders as $order)
                @php
                    $status = (string) ($order->status ?? '');
                    $statusColor = match ($status) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'secondary',
                        default => 'secondary',
                    };
                    $statusLabel = match ($status) {
                        'paid' => 'Pago',
                        'pending' => 'Pendente',
                        'failed' => 'Falhou',
                        'refunded' => 'Reembolsado',
                        'cancelled' => 'Cancelado',
                        default => $status ?: '—',
                    };
                    $items = $order->items ?? collect();
                    $gateway = ucfirst($order->gateway ?? 'manual');
                    $grossAmount = (float) $order->gross_amount;
                    $discountAmount = (float) $order->financial_discount_amount;
                    $couponCode = $order->coupon_code;
                @endphp

                <div class="border-bottom px-4 py-3">
                    <div class="row align-items-center">
                        {{-- ID + Data --}}
                        <div class="col-md-1 col-3">
                            <div class="font-weight-black text-primary" style="font-size:16px;">#{{ $order->id }}</div>
                            <div class="text-muted" style="font-size:10px;">{{ $order->created_at?->format('d/m/y') }}</div>
                        </div>

                        {{-- Comprador --}}
                        <div class="col-md-3 col-9">
                            <div class="font-weight-bold">{{ $order->user->name ?? '—' }}</div>
                            <div class="text-muted small">{{ $order->user->email ?? '' }}</div>
                        </div>

                        {{-- Itens --}}
                        <div class="col-md-3 col-12 mt-1 mt-md-0">
                            @foreach($items->take(2) as $item)
                                <span class="badge badge-light border mr-1" style="font-size:10px;">{{ \Illuminate\Support\Str::limit($item->title, 22) }}</span>
                            @endforeach
                            @if($items->count() > 2)
                                <span class="badge badge-light border" style="font-size:10px;">+{{ $items->count() - 2 }}</span>
                            @endif
                            @if($items->isEmpty())
                                <span class="text-muted small">—</span>
                            @endif
                        </div>

                        {{-- Valor --}}
                        <div class="col-md-2 col-4 mt-1 mt-md-0">
                            <div class="font-weight-bold" style="font-size:15px;">R$ {{ number_format($grossAmount, 2, ',', '.') }}</div>
                            @if($discountAmount > 0)
                                <div class="text-success font-weight-bold" style="font-size:10px;">
                                    Cupom {{ $couponCode ?: '-' }}: - R$ {{ number_format($discountAmount, 2, ',', '.') }}
                                </div>
                                <div class="text-muted" style="font-size:10px;">Liquido: R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</div>
                            @else
                                <div class="text-muted" style="font-size:10px;">{{ $gateway }}</div>
                            @endif
                        </div>

                        {{-- Status --}}
                        <div class="col-md-2 col-4 mt-1 mt-md-0">
                            <span class="badge badge-{{ $statusColor }} px-2 py-1">
                                <i class="fas fa-circle mr-1" style="font-size:6px;"></i>{{ $statusLabel }}
                            </span>
                        </div>

                        {{-- Hora --}}
                        <div class="col-md-1 col-4 mt-1 mt-md-0 text-right">
                            <span class="text-muted" style="font-size:11px;">{{ $order->created_at?->format('H:i') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-receipt fa-3x text-muted"></i>
                    </div>
                    <h5 class="font-weight-bold text-muted">Nenhuma venda registrada</h5>
                    <p class="text-muted mb-3">Suas vendas aparecerão aqui quando forem realizadas pelos compradores.</p>
                    <a href="{{ route('admin.marketplace.products.index') }}" class="btn btn-primary rounded-pill px-4 elevation-1">
                        <i class="fas fa-box-open mr-1"></i> Ver meus produtos
                    </a>
                </div>
            @endforelse
        </div>

        @if(method_exists($orders, 'hasPages') && $orders->hasPages())
            <div class="card-footer d-flex justify-content-center border-top">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .font-weight-black { font-weight: 900; }
</style>
@endpush
