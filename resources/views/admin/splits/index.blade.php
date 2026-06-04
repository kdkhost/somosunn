@extends('admin.layouts.app')
@section('title', 'Extrato de Splits')
@section('page_title', 'Rateio Financeiro')
@section('breadcrumb')
    <li class="breadcrumb-item active">Splits</li>
@endsection

@section('content')
    @php
        $totalPaid = $splits->where('status', 'paid')->sum('amount');
        $totalPending = $splits->where('status', 'pending')->sum('amount');
        $totalAll = $totalPaid + $totalPending;
        $countPending = $splits->where('status', 'pending')->count();
        $countPaid = $splits->where('status', 'paid')->count();

        $typeConfig = [
            'seller'     => ['label' => 'Vendedor',     'icon' => 'fa-store',        'color' => 'primary',   'gradient' => 'from-blue-600 to-indigo-700'],
            'platform'   => ['label' => 'Plataforma',   'icon' => 'fa-building',     'color' => 'info',      'gradient' => 'from-cyan-500 to-blue-600'],
            'traffic'    => ['label' => 'Tráfego Pago', 'icon' => 'fa-bullhorn',     'color' => 'warning',   'gradient' => 'from-amber-500 to-orange-600'],
            'superadmin' => ['label' => 'Administrador','icon' => 'fa-user-shield',  'color' => 'danger',    'gradient' => 'from-rose-500 to-red-700'],
        ];
    @endphp

    {{-- KPI Cards --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Distribuído</span>
                    <span class="info-box-number text-success">R$ {{ number_format($totalPaid, 2, ',', '.') }}</span>
                    <div class="progress progress-xs mt-2">
                        <div class="progress-bar bg-success" style="width: {{ $totalAll > 0 ? round(($totalPaid / $totalAll) * 100) : 0 }}%"></div>
                    </div>
                    <span class="progress-description text-xs">{{ $countPaid }} rateio(s) liquidado(s)</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-warning elevation-1"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Pendente</span>
                    <span class="info-box-number text-warning">R$ {{ number_format($totalPending, 2, ',', '.') }}</span>
                    <div class="progress progress-xs mt-2">
                        <div class="progress-bar bg-warning" style="width: {{ $totalAll > 0 ? round(($totalPending / $totalAll) * 100) : 0 }}%"></div>
                    </div>
                    <span class="progress-description text-xs">{{ $countPending }} aguardando liquidação</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-chart-pie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Volume Total</span>
                    <span class="info-box-number">R$ {{ number_format($totalAll, 2, ',', '.') }}</span>
                    <div class="progress progress-xs mt-2">
                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                    </div>
                    <span class="progress-description text-xs">{{ $splits->total() }} registro(s) no total</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-dark elevation-1"><i class="fas fa-percentage"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Taxa Plataforma</span>
                    <span class="info-box-number">{{ \App\Models\Setting::get('marketplace_platform_fee_percent', 0) }}%</span>
                    <div class="progress progress-xs mt-2">
                        <div class="progress-bar bg-dark" style="width: {{ min(100, (float) \App\Models\Setting::get('marketplace_platform_fee_percent', 0)) }}%"></div>
                    </div>
                    <span class="progress-description text-xs">Configurado nas settings</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtro --}}
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.splits.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2" style="gap:.5rem;">
                <div class="flex-grow-1" style="min-width:250px;">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control border-left-0"
                            placeholder="Buscar por ID do pedido, nome ou e-mail..." value="{{ request('search') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary px-4 elevation-1">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.splits.index') }}" class="btn btn-outline-secondary px-3">
                        <i class="fas fa-times mr-1"></i> Limpar
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Tabela principal --}}
    <div class="card card-outline card-dark shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-stream mr-2 text-primary"></i>Registros de Rateio
            </h3>
            <div class="card-tools">
                <span class="badge badge-light border px-3 py-2">
                    Página {{ $splits->currentPage() }} de {{ $splits->lastPage() }}
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            @if($splits->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th class="border-0 pl-4" style="width:80px;">Pedido</th>
                                <th class="border-0">Recebedor</th>
                                <th class="border-0">Tipo</th>
                                <th class="border-0 text-right">Valor</th>
                                <th class="border-0 text-center">%</th>
                                <th class="border-0">Chave PIX</th>
                                <th class="border-0 text-center">Status</th>
                                <th class="border-0 text-center" style="width:90px;">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($splits as $split)
                                @php
                                    $cfg = $typeConfig[$split->receiver_type] ?? ['label' => ucfirst($split->receiver_type), 'icon' => 'fa-circle', 'color' => 'secondary'];
                                @endphp
                                <tr class="{{ $split->status === 'paid' ? 'bg-light' : '' }}">
                                    <td class="pl-4">
                                        <a href="{{ route('admin.orders.show', $split->order_id) }}"
                                            class="font-weight-bold text-primary">#{{ $split->order_id }}</a>
                                        <div class="text-xs text-muted">{{ $split->created_at->format('d/m/y') }}</div>
                                    </td>
                                    <td>
                                        @if($split->receiver)
                                            <div class="d-flex align-items-center">
                                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-{{ $cfg['color'] }} mr-2"
                                                    style="width:32px; height:32px; flex-shrink:0;">
                                                    <i class="fas {{ $cfg['icon'] }} text-white" style="font-size:12px;"></i>
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold text-sm">{{ $split->receiver->name }}</div>
                                                    <div class="text-xs text-muted">{{ $split->receiver->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-{{ $cfg['color'] }} mr-2"
                                                    style="width:32px; height:32px; flex-shrink:0;">
                                                    <i class="fas {{ $cfg['icon'] }} text-white" style="font-size:12px;"></i>
                                                </div>
                                                <span class="font-weight-bold text-sm">{{ $cfg['label'] }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $cfg['color'] }} px-2 py-1" style="font-size:11px;">
                                            <i class="fas {{ $cfg['icon'] }} mr-1" style="font-size:9px;"></i>{{ $cfg['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <span class="font-weight-bold {{ $split->status === 'paid' ? 'text-success' : 'text-dark' }}" style="font-size:14px;">
                                            R$ {{ number_format($split->amount, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light border font-weight-bold">{{ number_format($split->percentage, 1) }}%</span>
                                    </td>
                                    <td>
                                        @if($split->pix_key)
                                            <div class="d-flex align-items-center">
                                                <code class="bg-light border rounded px-2 py-1 text-xs text-truncate" style="max-width:180px;" title="{{ $split->pix_key }}">
                                                    {{ $split->pix_key }}
                                                </code>
                                                <button type="button" class="btn btn-xs btn-link text-muted ml-1 btn-copy-pix" data-pix="{{ $split->pix_key }}" title="Copiar">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-muted text-xs"><i class="fas fa-exclamation-triangle text-warning mr-1"></i>Não informada</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($split->status === 'paid')
                                            <span class="badge badge-success px-3 py-2" style="font-size:11px;">
                                                <i class="fas fa-check-circle mr-1"></i>Pago
                                            </span>
                                        @else
                                            <span class="badge badge-warning px-3 py-2" style="font-size:11px;">
                                                <i class="fas fa-clock mr-1"></i>Pendente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($split->status === 'pending' && $split->pix_key)
                                            <button type="button"
                                                class="btn btn-sm btn-success rounded-pill px-3 elevation-1 btn-liquidate"
                                                data-id="{{ $split->id }}"
                                                data-url="{{ route('admin.splits.pay', $split->id) }}"
                                                data-amount="R$ {{ number_format($split->amount, 2, ',', '.') }}"
                                                data-receiver="{{ $split->receiver?->name ?? $cfg['label'] }}"
                                                title="Confirmar pagamento">
                                                <i class="fas fa-hand-holding-usd mr-1"></i> Pagar
                                            </button>
                                        @elseif($split->status === 'pending')
                                            <span class="text-danger text-xs font-weight-bold">Cadastre o PIX</span>
                                        @else
                                            <span class="text-success"><i class="fas fa-check-double"></i></span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-hand-holding-usd fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted font-weight-bold">Nenhum rateio encontrado</h5>
                    <p class="text-muted">Os splits são gerados automaticamente quando um pagamento é confirmado.</p>
                </div>
            @endif
        </div>

        @if($splits->hasPages())
            <div class="card-footer d-flex justify-content-center border-top">
                {{ $splits->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    {{-- Legenda --}}
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header border-0 py-2">
            <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-info-circle mr-2 text-info"></i>Como funciona o rateio</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-bold mb-2">Distribuição configurada:</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge badge-primary mr-2 px-2 py-1"><i class="fas fa-store"></i></span>
                            <span>Vendedor: <strong>{{ \App\Models\Setting::get('marketplace_split_seller_percent', 70) }}%</strong></span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge badge-info mr-2 px-2 py-1"><i class="fas fa-building"></i></span>
                            <span>Plataforma: <strong>{{ \App\Models\Setting::get('marketplace_split_platform_percent', 10) }}%</strong></span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge badge-warning mr-2 px-2 py-1"><i class="fas fa-bullhorn"></i></span>
                            <span>Tráfego: <strong>{{ \App\Models\Setting::get('marketplace_split_traffic_percent', 10) }}%</strong></span>
                        </li>
                        <li class="d-flex align-items-center">
                            <span class="badge badge-danger mr-2 px-2 py-1"><i class="fas fa-user-shield"></i></span>
                            <span>Administrador: <strong>{{ \App\Models\Setting::get('marketplace_split_superadmin_percent', 10) }}%</strong></span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold mb-2">Fluxo:</h6>
                    <ol class="text-sm text-muted mb-0 pl-3">
                        <li class="mb-1">Pagamento confirmado pelo gateway (webhook)</li>
                        <li class="mb-1">Sistema calcula splits automaticamente</li>
                        <li class="mb-1">Splits ficam como "Pendente" até confirmação</li>
                        <li class="mb-1">Admin verifica e realiza transferência PIX</li>
                        <li>Clica em "Pagar" para marcar como liquidado</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .align-middle td { vertical-align: middle !important; }
    .gap-2 { gap: 0.5rem; }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    // Copiar chave PIX
    $('.btn-copy-pix').on('click', function () {
        const pix = $(this).data('pix');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(pix).then(() => {
                toastr.success('Chave PIX copiada!');
            });
        } else {
            const temp = $('<input>').val(pix).appendTo('body').select();
            document.execCommand('copy');
            temp.remove();
            toastr.success('Chave PIX copiada!');
        }
    });

    // Liquidar split
    $('.btn-liquidate').on('click', function () {
        const btn = $(this);
        const url = btn.data('url');
        const amount = btn.data('amount');
        const receiver = btn.data('receiver');

        Swal.fire({
            title: 'Confirmar Liquidação',
            html: `<div class="text-left">
                <p>Você confirma que o repasse foi realizado?</p>
                <div class="bg-light rounded p-3 mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Recebedor:</span>
                        <strong>${receiver}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Valor:</span>
                        <strong class="text-success">${amount}</strong>
                    </div>
                </div>
            </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check-double mr-1"></i> Confirmar Pagamento',
            cancelButtonText: 'Cancelar',
            customClass: { popup: 'rounded-lg' }
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (response) {
                        if (response.success) {
                            toastr.success(response.message || 'Rateio liquidado com sucesso!');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Swal.fire('Erro', response.message || 'Falha ao liquidar.', 'error');
                            btn.prop('disabled', false).html('<i class="fas fa-hand-holding-usd mr-1"></i> Pagar');
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Erro ao processar.';
                        Swal.fire('Erro', msg, 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-hand-holding-usd mr-1"></i> Pagar');
                    }
                });
            }
        });
    });
});
</script>
@endpush
