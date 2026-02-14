@extends('admin.layouts.app')

@section('title', 'Marketplace')
@section('page_title', 'Marketplace')

@section('content')
    @php
        $paidTotal = (float) ($paidTotal ?? 0);
        $platformFeeTotal = (float) ($platformFeeTotal ?? 0);
        $netTotal = (float) ($netTotal ?? 0);
        $paidCount = (int) ($paidCount ?? 0);
        $pendingCount = (int) ($pendingCount ?? 0);
        $paymentsConfigured = (bool) ($paymentsConfigured ?? false);
        $platformFeePercent = (float) ($platformFeePercent ?? 0);
    @endphp

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $paidCount }}</h3>
                    <p>Vendas pagas</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <a href="{{ route('admin.marketplace.sales') }}" class="small-box-footer">Ver vendas <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>R$ {{ number_format($netTotal, 2, ',', '.') }}</h3>
                    <p>Total líquido (pagos)</p>
                    <div class="text-white-50" style="font-size: 0.9rem;">
                        Bruto: R$ {{ number_format($paidTotal, 2, ',', '.') }}<br>
                        Comissão da plataforma{{ $platformFeePercent > 0 ? (' (' . rtrim(rtrim(number_format($platformFeePercent, 2, '.', ''), '0'), '.') . '%)') : '' }}:
                        R$ {{ number_format($platformFeeTotal, 2, ',', '.') }}
                    </div>
                </div>
                <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
                <a href="{{ route('admin.marketplace.sales') }}" class="small-box-footer">Ver detalhes <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pendingCount }}</h3>
                    <p>Pedidos pendentes</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                <a href="{{ route('admin.marketplace.sales') }}" class="small-box-footer">Acompanhar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-store mr-2"></i>Configurações do vendedor</h3>
        </div>
        <div class="card-body">
            @if($paymentsConfigured)
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i> Pagamentos configurados e habilitados na plataforma.
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Pagamento indisponível: o MercadoPago ainda não foi configurado na plataforma.
                </div>
            @endif

            <a href="{{ route('admin.marketplace.payments') }}" class="btn btn-primary">
                <i class="fas fa-credit-card mr-1"></i> Ver pagamentos
            </a>
        </div>
    </div>
@endsection
