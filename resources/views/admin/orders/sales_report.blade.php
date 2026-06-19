@extends('admin.layouts.app')

@section('title', 'Relatorio de vendas por item')
@section('page_title', 'Relatorio de vendas por item')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Vendas</a></li>
    <li class="breadcrumb-item active">Relatorio por item</li>
@endsection

@push('styles')
    <style>
        .sales-buyers-modal .modal-dialog {
            max-width: min(1120px, calc(100vw - 2rem));
        }

        .sales-buyers-panel {
            color: #0f172a;
        }

        .sales-buyers-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .sales-buyers-k {
            color: #64748b;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .sales-buyers-title {
            font-size: 1.1rem;
            font-weight: 800;
        }

        .sales-buyers-meta {
            color: #64748b;
            font-size: .85rem;
            margin-top: .2rem;
        }

        .sales-buyers-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .sales-buyers-summary > div {
            border: 1px solid #dbe3ef;
            border-radius: .65rem;
            background: #f8fafc;
            padding: .75rem;
        }

        .sales-buyers-summary span,
        .sales-buyers-table small {
            display: block;
            color: #64748b;
            font-size: .75rem;
        }

        .sales-buyers-summary strong {
            display: block;
            font-size: 1rem;
        }

        .sales-buyers-table thead th {
            white-space: nowrap;
        }

        .sales-buyers-empty {
            color: #64748b;
            padding: 2rem !important;
        }

        body.dark-mode .sales-buyers-panel {
            color: #e2e8f0;
        }

        body.dark-mode .sales-buyers-meta,
        body.dark-mode .sales-buyers-k,
        body.dark-mode .sales-buyers-summary span,
        body.dark-mode .sales-buyers-table small,
        body.dark-mode .sales-buyers-empty {
            color: #94a3b8;
        }

        body.dark-mode .sales-buyers-summary > div {
            background: #111827;
            border-color: #334155;
        }

        @media (max-width: 768px) {
            .sales-buyers-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endpush

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
                            <th class="text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php
                                $buyerRouteParams = array_merge(request()->query(), [
                                    'item_type' => (string) $row->item_type,
                                    'item_id' => (int) $row->item_id,
                                ]);
                            @endphp
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
                                <td class="text-right">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary rounded-pill js-sales-report-buyers"
                                            data-url="{{ route('admin.orders.sales-report.buyers', $buyerRouteParams) }}"
                                            data-print-url="{{ route('admin.orders.sales-report.buyers.print', $buyerRouteParams) }}"
                                            data-pdf-url="{{ route('admin.orders.sales-report.buyers.pdf', $buyerRouteParams) }}">
                                        <i class="fas fa-users mr-1"></i> Lista
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
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

    <div class="modal fade sales-buyers-modal" id="salesReportBuyersModal" tabindex="-1" role="dialog" aria-labelledby="salesReportBuyersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="salesReportBuyersModalLabel">
                        <i class="fas fa-users mr-2 text-primary"></i>Compradores do item
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="salesReportBuyersBody">
                    <div class="text-center text-muted py-4">Carregando...</div>
                </div>
                <div class="modal-footer">
                    <a href="#" target="_blank" rel="noopener" class="btn btn-outline-secondary rounded-pill disabled" id="salesReportBuyersPrint" data-no-ajax="true">
                        <i class="fas fa-print mr-1"></i> Imprimir A4
                    </a>
                    <a href="#" target="_blank" rel="noopener" class="btn btn-primary rounded-pill disabled" id="salesReportBuyersPdf" data-no-ajax="true">
                        <i class="fas fa-file-pdf mr-1"></i> PDF
                    </a>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            if (!window.jQuery) {
                return;
            }

            $('.js-sales-report-buyers').on('click', function () {
                var button = $(this);
                var modal = $('#salesReportBuyersModal');
                var body = $('#salesReportBuyersBody');
                var printLink = $('#salesReportBuyersPrint');
                var pdfLink = $('#salesReportBuyersPdf');

                body.html('<div class="text-center text-muted py-4">Carregando...</div>');
                printLink.attr('href', button.data('print-url')).removeClass('disabled');
                pdfLink.attr('href', button.data('pdf-url')).removeClass('disabled');
                modal.modal('show');

                $.ajax({
                    url: button.data('url'),
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function (html) {
                    body.html(html);
                }).fail(function () {
                    body.html('<div class="alert alert-danger mb-0">Nao foi possivel carregar a lista de compradores.</div>');
                });
            });
        })();
    </script>
@endpush
