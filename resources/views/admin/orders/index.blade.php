@extends('admin.layouts.app')
@section('title', 'Vendas')
@section('page_title', 'Gerenciamento de Vendas')
@section('breadcrumb')
    <li class="breadcrumb-item active">Vendas</li>
@endsection

@section('content')
    @php
        $paymentScope = $paymentScope ?? request('payment_scope', 'all');
        $reportScope = $reportScope ?? request('report_scope', 'accounted');
        $periodValue = $period['period'] ?? request('period', 'monthly');
        $dateFrom = request('date_from', optional($period['from'] ?? null)->format('Y-m-d'));
        $dateTo = request('date_to', optional($period['to'] ?? null)->format('Y-m-d'));
        $statusValue = $status ?? request('status');

        $exportParams = array_merge(request()->query(), ['report_scope' => $reportScope]);
    @endphp

    {{-- KPI Cards --}}
    <div class="row mb-4">
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-info elevation-1"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Receita Contabilizada</span>
                    <span class="info-box-number text-info">R$ {{ number_format((float) ($accountedRevenue ?? 0), 2, ',', '.') }}</span>
                    <span class="progress-description text-xs">{{ (int) ($accountedCount ?? 0) }} pedidos pagos reais</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-warning elevation-1"><i class="fas fa-handshake"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Aprovações Manuais</span>
                    <span class="info-box-number text-warning">R$ {{ number_format((float) ($manualRevenue ?? 0), 2, ',', '.') }}</span>
                    <span class="progress-description text-xs">{{ (int) ($manualCount ?? 0) }} pedidos por permuta/manual</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-12">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-secondary elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Usuários Manual</span>
                    <span class="info-box-number">{{ (int) ($manualUsersCount ?? 0) }}</span>
                    <span class="progress-description text-xs">Separados do financeiro contabilizado</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtro --}}
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header border-0 py-2">
            <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-filter mr-2 text-primary"></i>Filtro Financeiro e Relatório</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body pt-2">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row">
                <div class="col-md-3 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">Busca</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control border-left-0" placeholder="ID, transação, cliente"
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="paid" {{ $statusValue === 'paid' ? 'selected' : '' }}>Pago</option>
                        <option value="pending" {{ $statusValue === 'pending' ? 'selected' : '' }}>Pendente</option>
                        <option value="refunded" {{ $statusValue === 'refunded' ? 'selected' : '' }}>Reembolsado</option>
                        <option value="failed" {{ $statusValue === 'failed' ? 'selected' : '' }}>Falhou</option>
                        <option value="cancelled" {{ $statusValue === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">Tipo</label>
                    <select name="sale_type" class="form-control">
                        <option value="">Todos</option>
                        @foreach($saleTypeLabels ?? [] as $key => $label)
                            <option value="{{ $key }}" {{ $saleType === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">Escopo da Lista</label>
                    <select name="payment_scope" class="form-control">
                        <option value="all" {{ $paymentScope === 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="accounted" {{ $paymentScope === 'accounted' ? 'selected' : '' }}>Contabilizado</option>
                        <option value="manual" {{ $paymentScope === 'manual' ? 'selected' : '' }}>Manual/permuta</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">Período</label>
                    <select name="period" class="form-control">
                        <option value="monthly" {{ $periodValue === 'monthly' ? 'selected' : '' }}>Mensal</option>
                        <option value="bimonthly" {{ $periodValue === 'bimonthly' ? 'selected' : '' }}>Bimestral</option>
                        <option value="quarterly" {{ $periodValue === 'quarterly' ? 'selected' : '' }}>Trimestral</option>
                        <option value="semiannual" {{ $periodValue === 'semiannual' ? 'selected' : '' }}>Semestral</option>
                        <option value="annual" {{ $periodValue === 'annual' ? 'selected' : '' }}>Anual</option>
                        <option value="custom" {{ $periodValue === 'custom' ? 'selected' : '' }}>Personalizado</option>
                    </select>
                </div>
                <div class="col-md-1 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">De</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-1 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">Até</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1 text-xs font-weight-bold text-muted">Escopo do Relatório</label>
                    <select name="report_scope" class="form-control">
                        <option value="accounted" {{ $reportScope === 'accounted' ? 'selected' : '' }}>Somente contabilizado</option>
                        <option value="manual" {{ $reportScope === 'manual' ? 'selected' : '' }}>Somente manual</option>
                        <option value="all" {{ $reportScope === 'all' ? 'selected' : '' }}>Todos pagos</option>
                    </select>
                </div>
                <div class="col-md-12 mb-2 d-flex align-items-end" style="gap:8px;">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 elevation-1">
                        <i class="fas fa-filter mr-1"></i> Aplicar
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-times mr-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
        <div class="card-footer bg-light d-flex flex-wrap align-items-center" style="gap:8px;">
            <span class="font-weight-bold text-sm mr-2"><i class="fas fa-file-export mr-1 text-muted"></i>Exportar:</span>
            <a href="{{ route('admin.orders.report.export', array_merge(['format' => 'pdf'], $exportParams)) }}"
                class="btn btn-sm btn-danger rounded-pill px-3 elevation-1">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </a>
            <a href="{{ route('admin.orders.report.export', array_merge(['format' => 'xml'], $exportParams)) }}"
                class="btn btn-sm btn-info rounded-pill px-3 elevation-1">
                <i class="fas fa-file-code mr-1"></i> XML
            </a>
            <a href="{{ route('admin.orders.report.export', array_merge(['format' => 'csv'], $exportParams)) }}"
                class="btn btn-sm btn-success rounded-pill px-3 elevation-1">
                <i class="fas fa-file-csv mr-1"></i> CSV
            </a>
            <a href="{{ route('admin.orders.report.export', array_merge(['format' => 'doc'], $exportParams)) }}"
                class="btn btn-sm btn-primary rounded-pill px-3 elevation-1">
                <i class="fas fa-file-word mr-1"></i> DOC
            </a>
            <a href="{{ route('admin.orders.report.export', array_merge(['format' => 'html'], $exportParams)) }}"
                class="btn btn-sm btn-secondary rounded-pill px-3 elevation-1">
                <i class="fas fa-file-code mr-1"></i> HTML
            </a>
            <span class="text-muted ml-auto text-sm">{{ $period['label'] ?? '' }}</span>
        </div>
    </div>

    {{-- Tabela principal --}}
    <div class="card card-outline card-dark shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-shopping-cart mr-2 text-primary"></i>Listagem de Pedidos
            </h3>
            <div class="card-tools">
                <span class="badge badge-light border px-3 py-2">
                    <i class="fas fa-list-ol mr-1"></i> Total de registros carregados
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="orders-table" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="border-0 pl-4">ID</th>
                            <th class="border-0">Tipo</th>
                            <th class="border-0">Cliente</th>
                            <th class="border-0">Telefone</th>
                            <th class="border-0">Endereço</th>
                            <th class="border-0 text-right">Valor</th>
                            <th class="border-0">Pagamento</th>
                            <th class="border-0 text-center">Origem</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0">Data Financeira</th>
                            <th class="border-0 text-center" style="width:80px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            @php
                                $photo = trim((string) optional($order->user)->photo);
                                $avatarUrl = $photo !== ''
                                    ? ((str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) ? $photo : asset($photo))
                                    : asset('img/default-user.svg');
                                $financialDate = $order->paid_at ?? $order->manual_approved_at ?? $order->created_at;
                            @endphp
                            <tr>
                                <td class="pl-4" data-order="{{ (int) $order->id }}">
                                    <span class="font-weight-bold text-primary">#{{ $order->id }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light border px-2 py-1 text-xs">{{ $order->saleTypeLabel() }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $avatarUrl }}" class="img-circle elevation-1 mr-2"
                                            style="width:34px;height:34px;object-fit:cover"
                                            onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                        <div>
                                            <div class="font-weight-bold text-sm">{{ $order->user->name ?? 'Usuário removido' }}</div>
                                            <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-sm">{{ $order->user->phone ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="text-xs text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $order->buyerAddress() }}">
                                        {{ $order->buyerAddress() ?: '-' }}
                                    </div>
                                </td>
                                <td class="text-right" data-order="{{ (float) $order->total_amount }}">
                                    <span class="font-weight-bold" style="font-size:14px;">R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-info px-2 py-1">{{ ucfirst($order->gateway ?: 'manual') }}</span>
                                    <div class="text-xs text-muted mt-1">{{ $order->payment_method ?: 'não informado' }}</div>
                                </td>
                                <td class="text-center">
                                    @if($order->is_manual_approval)
                                        <span class="badge badge-warning px-2 py-1"><i class="fas fa-hand-paper mr-1" style="font-size:9px;"></i>Manual</span>
                                    @else
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1" style="font-size:9px;"></i>Contabilizado</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($order->status == 'paid')
                                        <span class="badge badge-success px-3 py-2" style="font-size:11px;"><i class="fas fa-check-circle mr-1"></i>Pago</span>
                                    @elseif($order->status == 'pending')
                                        <span class="badge badge-warning px-3 py-2" style="font-size:11px;"><i class="fas fa-clock mr-1"></i>Pendente</span>
                                    @elseif($order->status == 'refunded')
                                        <span class="badge badge-danger px-3 py-2" style="font-size:11px;"><i class="fas fa-undo mr-1"></i>Reembolsado</span>
                                    @elseif($order->status == 'cancelled')
                                        <span class="badge badge-secondary px-3 py-2" style="font-size:11px;"><i class="fas fa-ban mr-1"></i>Cancelado</span>
                                    @else
                                        <span class="badge badge-secondary px-3 py-2" style="font-size:11px;">{{ $order->status }}</span>
                                    @endif
                                </td>
                                <td data-order="{{ $financialDate ? $financialDate->timestamp : 0 }}">
                                    <div class="text-sm">{{ $financialDate ? $financialDate->format('d/m/Y') : '-' }}</div>
                                    <small class="text-muted">{{ $financialDate ? $financialDate->format('H:i') : '' }}</small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.orders.show', $order->id) }}"
                                        class="btn btn-sm btn-primary rounded-pill px-3 elevation-1" title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted text-sm">
                        Mostrando <b>{{ $orders->firstItem() }}</b> até <b>{{ $orders->lastItem() }}</b> de <b>{{ $orders->total() }}</b> resultados
                    </div>
                    <div>
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <style>
        .align-middle td { vertical-align: middle !important; }
        #orders-table_wrapper .dataTables_paginate {
            display: flex;
            justify-content: center;
            padding: 0.75rem;
        }
        #orders-table_wrapper .dataTables_info {
            margin: 0.95rem 0 0 0.75rem;
            color: #6c757d;
            font-size: 0.875rem;
        }
        #orders-table_wrapper .pagination {
            margin: 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function() {
            $('#orders-table').DataTable({
                paging: true,
                ordering: true,
                info: true,
                searching: false,
                lengthChange: false,
                pageLength: 10,
                autoWidth: false,
                order: [
                    [0, 'desc']
                ],
                columnDefs: [
                    { targets: 10, orderable: false, searchable: false }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json',
                    emptyTable: 'Nenhuma venda encontrada. Ajuste os filtros ou aguarde novas vendas serem registradas.'
                }
            });
        });
    </script>
@endpush
