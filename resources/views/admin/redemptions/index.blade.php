@extends('admin.layouts.app')

@section('page_title', 'Resgates UNNBIT')
@section('breadcrumb')
    <li class="breadcrumb-item active">Resgates UNNBIT</li>
@endsection

@php
    $coinName = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
@endphp

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-coins mr-2"></i>{{ $coinName }} - Resumo</h3>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ number_format((int) ($exchangeSettings['base_points'] ?? 0), 0, ',', '.') }} {{ $coinName }}</strong> = <strong>R$ {{ number_format((float) ($exchangeSettings['base_amount'] ?? 0), 2, ',', '.') }}</strong></p>
                    <p class="mb-0 text-muted">Cada {{ $coinName }} vale R$ {{ number_format((float) ($exchangeSettings['unit_value_brl'] ?? $exchangeSettings['point_value'] ?? 0), 4, ',', '.') }}.</p>
                </div>
            </div>

            <div class="card card-warning card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-clock mr-2"></i>Solicitacoes Pendentes</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
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
                            @forelse($pendingRedemptions as $redemption)
                                <tr>
                                    <td>{{ $redemption->user->name }}</td>
                                    <td>{{ $redemption->item->name }}</td>
                                    <td>{{ $redemption->item_type_label }}</td>
                                    <td>{{ number_format((int) $redemption->points_spent, 0, ',', '.') }} {{ $coinName }}</td>
                                    <td>{{ $redemption->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-right">
                                        <form action="{{ route('admin.redemptions.approve', $redemption) }}" method="POST" style="display:inline-block">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success mr-1 js-confirm-action" data-text="Deseja confirmar a entrega deste item?">
                                                <i class="fas fa-check mr-1"></i> Aprovar
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.redemptions.cancel', $redemption) }}" method="POST" style="display:inline-block">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger js-confirm-action" data-text="O saldo em {{ $coinName }} sera devolvido ao usuario. Continuar?">
                                                <i class="fas fa-times mr-1"></i> Cancelar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Nenhuma solicitacao pendente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-gift mr-2"></i>Itens para Resgate</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.redemptions.create') }}" class="btn btn-sm btn-primary font-weight-bold shadow-sm px-3">
                            <i class="fas fa-plus mr-1"></i> Novo Item
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Tipo</th>
                                <th>Fornecedor</th>
                                <th>{{ $coinName }}</th>
                                <th>Estoque</th>
                                <th>Resgates</th>
                                <th>Status</th>
                                <th class="text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>
                                        @if($item->image)
                                            <img src="{{ \App\Support\UploadStorage::url($item->image) }}" alt="{{ $item->name }}" class="img-circle img-size-32 mr-2">
                                        @else
                                            <div class="img-circle img-size-32 mr-2 bg-secondary d-inline-flex align-items-center justify-content-center">
                                                <i class="fas fa-gift text-xs"></i>
                                            </div>
                                        @endif
                                        {{ $item->name }}
                                    </td>
                                    <td>{{ $item->item_type_label }}</td>
                                    <td>{{ $item->provider_label }}</td>
                                    <td>{{ number_format((int) $item->points_cost, 0, ',', '.') }} {{ $coinName }}</td>
                                    <td>
                                        @if($item->stock < 0)
                                            Ilimitado
                                        @elseif($item->stock < 5)
                                            <span class="text-danger font-weight-bold">{{ $item->stock }}</span>
                                        @else
                                            {{ $item->stock }}
                                        @endif
                                    </td>
                                    <td>{{ $item->redemptions_count }}</td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge badge-success">Ativo</span>
                                        @else
                                            <span class="badge badge-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.redemptions.edit', $item) }}" class="text-muted mr-2">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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
