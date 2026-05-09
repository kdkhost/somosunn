@extends('admin.layouts.app')

@section('page_title', 'Resgates UNNBIT')
@section('breadcrumb')
    <li class="breadcrumb-item active">Resgates UNNBIT</li>
@endsection

@php
    $coinName = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
    $unitValue = (float) ($exchangeSettings['unit_value_brl'] ?? $exchangeSettings['point_value'] ?? 0);
@endphp

@section('content')
    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-primary elevation-1">
                <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor {{ $coinName }}</span>
                    <span class="info-box-number">R$ {{ number_format($unitValue, 4, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-warning elevation-1">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendentes</span>
                    <span class="info-box-number">{{ $pendingRedemptions->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-success elevation-1">
                <span class="info-box-icon"><i class="fas fa-gift"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Itens Disponiveis</span>
                    <span class="info-box-number">{{ $items->where('is_active', true)->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-info elevation-1">
                <span class="info-box-icon"><i class="fas fa-exchange-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Lote Referencia</span>
                    <span class="info-box-number">{{ number_format((int) ($exchangeSettings['base_points'] ?? 0), 0, ',', '.') }} = R$ {{ number_format((float) ($exchangeSettings['base_amount'] ?? 0), 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Redemptions --}}
    <div class="card card-outline card-warning shadow-sm mb-4">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-clock text-warning mr-2"></i>Solicitacoes Pendentes
            </h3>
            <div class="card-tools">
                <span class="badge badge-warning">{{ $pendingRedemptions->count() }} pendente(s)</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($pendingRedemptions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Item</th>
                                <th>Tipo</th>
                                <th>{{ $coinName }}</th>
                                <th>Data</th>
                                <th class="text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingRedemptions as $redemption)
                                <tr>
                                    <td><div class="font-weight-bold">{{ $redemption->user->name }}</div></td>
                                    <td>{{ $redemption->item->name }}</td>
                                    <td><span class="badge badge-light border">{{ $redemption->item_type_label }}</span></td>
                                    <td>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-coins mr-1"></i>{{ number_format((int) $redemption->points_spent, 0, ',', '.') }} {{ $coinName }}
                                        </span>
                                    </td>
                                    <td><small>{{ $redemption->created_at->format('d/m/Y H:i') }}</small></td>
                                    <td class="text-right" style="white-space:nowrap">
                                        <form action="{{ route('admin.redemptions.approve', $redemption) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill js-confirm-action" data-text="Deseja confirmar a entrega deste item?">
                                                <i class="fas fa-check mr-1"></i> Aprovar
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.redemptions.cancel', $redemption) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill js-confirm-action" data-text="O saldo em {{ $coinName }} sera devolvido ao usuario. Continuar?">
                                                <i class="fas fa-times mr-1"></i> Cancelar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted mb-0">Nenhuma solicitacao pendente.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Redemption Items --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-gift text-primary mr-2"></i>Itens para Resgate
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.redemptions.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1">
                    <i class="fas fa-plus mr-1"></i> Novo Item
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($items->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Item</th>
                                <th>Tipo</th>
                                <th>Fornecedor</th>
                                <th>{{ $coinName }}</th>
                                <th>Estoque</th>
                                <th>Resgates</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->image)
                                                <img src="{{ \App\Support\UploadStorage::url($item->image) }}" alt="{{ $item->name }}" class="img-circle img-size-32 mr-2">
                                            @else
                                                <div class="img-circle img-size-32 mr-2 bg-secondary d-inline-flex align-items-center justify-content-center">
                                                    <i class="fas fa-gift text-xs text-white"></i>
                                                </div>
                                            @endif
                                            <span class="font-weight-bold">{{ $item->name }}</span>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-light border">{{ $item->item_type_label }}</span></td>
                                    <td>{{ $item->provider_label }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            <i class="fas fa-coins mr-1"></i>{{ number_format((int) $item->points_cost, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->stock < 0)
                                            <span class="badge badge-success"><i class="fas fa-infinity mr-1"></i>Ilimitado</span>
                                        @elseif($item->stock < 5)
                                            <span class="badge badge-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $item->stock }}</span>
                                        @else
                                            <span class="badge badge-light border">{{ $item->stock }}</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-secondary">{{ $item->redemptions_count }}</span></td>
                                    <td class="text-center">
                                        @if($item->is_active)
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Ativo</span>
                                        @else
                                            <span class="badge badge-secondary"><i class="fas fa-pause mr-1"></i>Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.redemptions.edit', $item) }}" class="btn btn-sm btn-outline-primary rounded-pill" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-gift fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-1">Nenhum item de resgate cadastrado.</p>
                    <a href="{{ route('admin.redemptions.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1 mt-2">
                        <i class="fas fa-plus mr-1"></i> Criar primeiro item
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $(document).on('click', '.js-confirm-action', function (e) {
            e.preventDefault();
            const btn = this;
            const form = btn.closest('form');
            const text = btn.dataset.text || 'Confirmar esta acao?';
            Swal.fire({
                title: 'Confirmar',
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745'
            }).then(result => {
                if (result.isConfirmed) window.UNNAjaxGlobal.submitForm(form);
            });
        });
    });
</script>
@endpush
