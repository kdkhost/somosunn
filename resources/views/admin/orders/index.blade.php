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

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>R$ {{ number_format((float) ($accountedRevenue ?? 0), 2, ',', '.') }}</h3>
                    <p>Receita contabilizada</p>
                    <small>{{ (int) ($accountedCount ?? 0) }} pedidos pagos reais</small>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>R$ {{ number_format((float) ($manualRevenue ?? 0), 2, ',', '.') }}</h3>
                    <p>Aprovacoes manuais</p>
                    <small>{{ (int) ($manualCount ?? 0) }} pedidos por permuta/manual</small>
                </div>
                <div class="icon"><i class="fas fa-handshake"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ (int) ($manualUsersCount ?? 0) }}</h3>
                    <p>Usuarios com aprovacao manual</p>
                    <small>Separados do financeiro contabilizado</small>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtro financeiro e relatorio</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row">
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Busca</label>
                    <input type="text" name="search" class="form-control" placeholder="ID, transacao, cliente"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1">Status</label>
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
                    <label class="mb-1">Escopo da lista</label>
                    <select name="payment_scope" class="form-control">
                        <option value="all" {{ $paymentScope === 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="accounted" {{ $paymentScope === 'accounted' ? 'selected' : '' }}>Contabilizado</option>
                        <option value="manual" {{ $paymentScope === 'manual' ? 'selected' : '' }}>Manual/permuta</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1">Periodo</label>
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
                    <label class="mb-1">De</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-1 mb-2">
                    <label class="mb-1">Ate</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1">Escopo do relatorio</label>
                    <select name="report_scope" class="form-control">
                        <option value="accounted" {{ $reportScope === 'accounted' ? 'selected' : '' }}>Somente contabilizado</option>
                        <option value="manual" {{ $reportScope === 'manual' ? 'selected' : '' }}>Somente manual</option>
                        <option value="all" {{ $reportScope === 'all' ? 'selected' : '' }}>Todos pagos</option>
                    </select>
                </div>
                <div class="col-md-10 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-filter mr-1"></i>Aplicar</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times mr-1"></i>Limpar
                    </a>
                </div>
            </form>
        </div>
        <div class="card-footer bg-light d-flex flex-wrap" style="gap:8px;">
            <span class="mr-2"><strong>Exportar relatorio:</strong></span>
            <a href="{{ route('admin.orders.report.export', array_merge(['format' => 'pdf'], $exportParams)) }}"
                class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </a>
            <a href="{{ route('admin.orders.report.export', array_merge(['format' => 'xml'], $exportParams)) }}"
                class="btn btn-sm btn-info">
                <i class="fas fa-file-code mr-1"></i> XML
            </a>
            <a href="{{ route('admin.orders.report.export', array_merge(['format' => 'csv'], $exportParams)) }}"
                class="btn btn-sm btn-success">
                <i class="fas fa-file-csv mr-1"></i> CSV
            </a>
            <a href="{{ route('admin.orders.report.export', array_merge(['format' => 'doc'], $exportParams)) }}"
                class="btn btn-sm btn-primary">
                <i class="fas fa-file-word mr-1"></i> DOC
            </a>
            <a href="{{ route('admin.orders.report.export', array_merge(['format' => 'html'], $exportParams)) }}"
                class="btn btn-sm btn-secondary">
                <i class="fas fa-file-code mr-1"></i> HTML
            </a>
            <span class="text-muted ml-2 align-self-center">{{ $period['label'] ?? '' }}</span>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listagem de pedidos</h3>
        </div>
        <div class="card-body p-0">
            <table id="orders-table" class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Pagamento</th>
                        <th>Origem financeira</th>
                        <th>Status</th>
                        <th>Data financeira</th>
                        <th class="text-right">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $photo = trim((string) optional($order->user)->photo);
                            $avatarUrl = $photo !== ''
                                ? ((str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) ? $photo : asset($photo))
                                : asset('img/default-user.svg');
                            $financialDate = $order->paid_at ?? $order->manual_approved_at ?? $order->created_at;
                        @endphp
                        <tr>
                            <td data-order="{{ (int) $order->id }}">#{{ $order->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $avatarUrl }}" class="img-circle elevation-1 mr-2"
                                        style="width:30px;height:30px;object-fit:cover"
                                        onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                    <div>
                                        <div>{{ $order->user->name ?? 'Usuario removido' }}</div>
                                        <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="font-weight-bold" data-order="{{ (float) $order->total_amount }}">R$
                                {{ number_format((float) $order->total_amount, 2, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge badge-info mb-1">{{ ucfirst($order->gateway ?: 'manual') }}</span><br>
                                <small class="text-muted">{{ $order->payment_method ?: 'nao informado' }}</small>
                            </td>
                            <td>
                                @if($order->is_manual_approval)
                                    <span class="badge badge-warning">Manual / Permuta</span>
                                @else
                                    <span class="badge badge-success">Contabilizado</span>
                                @endif
                            </td>
                            <td>
                                @if($order->status == 'paid')
                                    <span class="badge badge-pill badge-success">Pago</span>
                                @elseif($order->status == 'pending')
                                    <span class="badge badge-pill badge-warning">Pendente</span>
                                @elseif($order->status == 'refunded')
                                    <span class="badge badge-pill badge-danger">Reembolsado</span>
                                @elseif($order->status == 'cancelled')
                                    <span class="badge badge-pill badge-secondary">Cancelado</span>
                                @else
                                    <span class="badge badge-pill badge-secondary">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td data-order="{{ $financialDate ? $financialDate->timestamp : 0 }}">
                                {{ $financialDate ? $financialDate->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary"
                                    title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhuma venda encontrada.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <style>
        #orders-table_wrapper .dataTables_paginate {
            margin: 0.75rem 0.75rem 0 0;
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
                    { targets: 7, orderable: false, searchable: false }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                }
            });
        });
    </script>
@endpush
