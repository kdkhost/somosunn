@extends('admin.layouts.app')

@section('page_title', 'Regras de Pontuacao')
@section('breadcrumb_items')
    <li class="breadcrumb-item active">Pontuacao</li>
@endsection

@php
    $coinName = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
    $unitValue = (float) ($exchangeSettings['unit_value_brl'] ?? $exchangeSettings['point_value'] ?? 0.01);
    $valuationTable = $exchangeSettings['valuation_table'] ?? [];
@endphp

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-coins mr-2"></i>{{ $coinName }} - Tabela de Valores</h3>
            <div class="card-tools">
                <a href="{{ route('admin.points-rules.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i>Nova Regra
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-7">
                    <p class="text-muted mb-3">
                        Defina quanto vale cada {{ $coinName }} em reais. A referencia em dolar pode ser atualizada manualmente pelo admin para acompanhar o mercado.
                    </p>
                    <form action="{{ route('admin.points-rules.exchange-settings') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Lote de referencia</label>
                                <input type="number" name="base_points" min="1" class="form-control"
                                    value="{{ old('base_points', (int) ($exchangeSettings['base_points'] ?? 100)) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Valor de 1 {{ $coinName }} (R$)</label>
                                <input type="text" name="unit_value" class="form-control"
                                    value="{{ old('unit_value', number_format($unitValue, 4, ',', '.')) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Cotacao USD/BRL</label>
                                <input type="text" name="usd_reference_rate" class="form-control"
                                    value="{{ old('usd_reference_rate', number_format((float) ($exchangeSettings['usd_reference_rate'] ?? 1), 4, ',', '.')) }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Observacao de mercado</label>
                            <textarea name="market_note" rows="3" class="form-control">{{ old('market_note', $exchangeSettings['market_note'] ?? '') }}</textarea>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <small class="text-muted">
                                @if(!empty($exchangeSettings['last_repriced_at']))
                                    Ultima revisao: {{ \Carbon\Carbon::parse($exchangeSettings['last_repriced_at'])->format('d/m/Y H:i') }}
                                @else
                                    Ainda sem revisao registrada.
                                @endif
                            </small>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Atualizar cotacao
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-lg-5">
                    <div class="callout callout-info">
                        <h5 class="mb-3">Resumo atual</h5>
                        <p class="mb-2"><strong>{{ number_format((int) ($exchangeSettings['base_points'] ?? 0), 0, ',', '.') }} {{ $coinName }}</strong> = <strong>R$ {{ number_format((float) ($exchangeSettings['base_amount'] ?? 0), 2, ',', '.') }}</strong></p>
                        <p class="mb-0">Cada {{ $coinName }} vale <strong>R$ {{ number_format($unitValue, 4, ',', '.') }}</strong>.</p>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>{{ $coinName }}</th>
                                    <th class="text-right">Valor em reais</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($valuationTable as $row)
                                    <tr>
                                        <td>{{ number_format((int) $row['units'], 0, ',', '.') }} {{ $coinName }}</td>
                                        <td class="text-right font-weight-bold">R$ {{ number_format((float) $row['amount'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($categories as $catKey => $cat)
        @php $rules = $rulesGrouped->get($catKey, collect()); @endphp
        @if($rules->count() > 0)
            <div class="card mb-3">
                <div class="card-header bg-{{ $cat['color'] }} {{ in_array($cat['color'], ['warning', 'light']) ? 'text-dark' : 'text-white' }} py-2">
                    <h3 class="card-title mb-0" style="font-size: 1rem;">
                        <i class="{{ $cat['icon'] }} mr-2"></i>{{ $cat['label'] }}
                        <span class="badge badge-light ml-1">{{ $rules->count() }}</span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Regra</th>
                                    <th class="text-center">Saldo</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right">Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rules->sortBy('sort_order') as $r)
                                    <tr class="{{ !$r->active ? 'table-secondary' : '' }}">
                                        <td>
                                            <strong>{{ $r->label }}</strong><br>
                                            <code class="small">{{ $r->key }}</code>
                                            @if($r->description ?? null)
                                                <div class="small text-muted mt-1">{{ $r->description }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $r->points > 0 ? 'success' : 'danger' }} px-2 py-1">
                                                {{ $r->points > 0 ? '+' : '' }}{{ (int) $r->points }} {{ $coinName }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($r->active)
                                                <span class="badge badge-success">Ativa</span>
                                            @else
                                                <span class="badge badge-secondary">Inativa</span>
                                            @endif
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <a href="{{ route('admin.points-rules.edit', $r) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.points-rules.destroy', $r) }}" method="POST" class="d-inline js-confirm-delete" data-confirm="Remover esta regra de pontuacao?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @if($rulesGrouped->flatten(1)->count() == 0)
        <div class="card">
            <div class="card-body text-center py-4">
                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhuma regra cadastrada</h5>
                <p class="text-muted mb-3 small">Crie regras para recompensar a participacao dos usuarios com {{ $coinName }}.</p>
                <a href="{{ route('admin.points-rules.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i>Criar primeira regra
                </a>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(function () {
            $(document)
                .off('submit.pointsDelete', 'form.js-confirm-delete')
                .on('submit.pointsDelete', 'form.js-confirm-delete', function (e) {
                    e.preventDefault();
                    const form = this;
                    const message = (form.getAttribute('data-confirm') || 'Confirma a remocao?').toString();

                    if (typeof Swal === 'undefined') {
                        form.submit();
                        return;
                    }

                    Swal.fire({
                        title: 'Confirmar remocao',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Remover',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#d33'
                    }).then((result) => {
                        if (!result.isConfirmed) return;
                        form.submit();
                    });
                });
        });
    </script>
@endpush
