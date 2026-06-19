@extends('admin.layouts.app')

@section('title', 'Relatorio de vendas por item')
@section('page_title', 'Relatorio de vendas por item')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Vendas</a></li>
    <li class="breadcrumb-item active">Relatorio por item</li>
@endsection

@section('content')
    @php
        $periodValue = $period['period'] ?? request('period', 'monthly');
        $dateFrom = request('date_from', optional($period['from'] ?? null)->format('Y-m-d'));
        $dateTo = request('date_to', optional($period['to'] ?? null)->format('Y-m-d'));
    @endphp

    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-boxes-stacked"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Itens com venda</span>
                    <span class="info-box-number">{{ (int) ($summary['catalog_items_count'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-success elevation-1"><i class="fas fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Unidades vendidas</span>
                    <span class="info-box-number">{{ (int) ($summary['total_units_sold'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-info elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Compradores</span>
                    <span class="info-box-number">{{ (int) ($summary['total_buyers_count'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-warning elevation-1"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Faturamento liquido</span>
                    <span class="info-box-number">R$ {{ number_format((float) ($summary['total_revenue'] ?? 0), 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-filter mr-2 text-primary"></i>Filtros do relatorio</h3>
            <div class="card-tools">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar para vendas
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.sales-report') }}" class="row">
                <div class="col-md-4 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">Busca</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Titulo do item">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">Tipo</label>
                    <select name="sale_type" class="form-control">
                        <option value="">Todos</option>
                        @foreach($saleTypeLabels as $key => $label)
                            <option value="{{ $key }}" {{ $saleType === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">Periodo</label>
                    <select name="period" class="form-control">
                        <option value="monthly" {{ $periodValue === 'monthly' ? 'selected' : '' }}>Mensal</option>
                        <option value="bimonthly" {{ $periodValue === 'bimonthly' ? 'selected' : '' }}>Bimestral</option>
                        <option value="quarterly" {{ $periodValue === 'quarterly' ? 'selected' : '' }}>Trimestral</option>
                        <option value="semiannual" {{ $periodValue === 'semiannual' ? 'selected' : '' }}>Semestral</option>
                        <option value="annual" {{ $periodValue === 'annual' ? 'selected' : '' }}>Anual</option>
                        <option value="custom" {{ $periodValue === 'custom' ? 'selected' : '' }}>Personalizado</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">De</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">Ate</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                </div>
                <div class="col-12 mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-search mr-1"></i> Atualizar relatorio
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-chart-bar mr-2 text-primary"></i>Itens vendidos</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Item</th>
                            <th>Tipo</th>
                            <th class="text-center">Vendidos</th>
                            <th class="text-center">Pedidos</th>
                            <th class="text-center">Compradores</th>
                            <th class="text-right">Faturamento liquido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $row->title }}</div>
                                    <small class="text-muted">ID {{ $row->item_id }}</small>
                                </td>
                                <td><span class="badge badge-primary">{{ $saleTypeLabels[$row->item_type] ?? ucfirst($row->item_type) }}</span></td>
                                <td class="text-center font-weight-bold">{{ (int) $row->units_sold }}</td>
                                <td class="text-center">{{ (int) $row->orders_count }}</td>
                                <td class="text-center">{{ (int) $row->buyers_count }}</td>
                                <td class="text-right font-weight-bold">R$ {{ number_format((float) $row->net_revenue, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    Nenhuma venda encontrada para os filtros informados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rows->hasPages())
            <div class="card-footer">{{ $rows->links() }}</div>
        @endif
    </div>
@endsection
