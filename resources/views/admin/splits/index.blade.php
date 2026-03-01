@extends('admin.layouts.app')
@section('title', 'Extrato de Splits')
@section('page_title', 'Extrato de Rateio (Marketplace)')
@section('breadcrumb')
    <li class="breadcrumb-item active">Extrato de Splits</li>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>R$ {{ number_format($splits->where('status', 'paid')->sum('amount'), 2, ',', '.') }}</h3>
                    <p>Total Distribuído (Pago)</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>R$ {{ number_format($splits->where('status', 'pending')->sum('amount'), 2, ',', '.') }}</h3>
                    <p>Total Pendente</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.splits.index') }}" method="GET" class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" name="search" class="form-control"
                        placeholder="ID do Pedido, Nome ou E-mail do recebedor" value="{{ request('search') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary btn-block"><i
                            class="fas fa-search mr-1"></i>Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registros de Rateio</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0" id="splits-table">
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Recebedor</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Percentual</th>
                        <th>Chave PIX</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($splits as $split)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $split->order_id) }}">#{{ $split->order_id }}</a>
                            </td>
                            <td>
                                @if(in_array($split->receiver_type, ['seller', 'superadmin']) && $split->receiver)
                                    <strong>{{ $split->receiver->name }}</strong><br>
                                    <small>{{ $split->receiver->email }}</small>
                                @else
                                    <span class="text-muted">{{ ucfirst($split->receiver_type) }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeLabels = [
                                        'seller' => 'Vendedor',
                                        'platform' => 'Plataforma',
                                        'traffic' => 'Tráfego Pago',
                                        'superadmin' => 'Administrador'
                                    ];
                                @endphp
                                <span
                                    class="badge badge-info">{{ $typeLabels[$split->receiver_type] ?? $split->receiver_type }}</span>
                            </td>
                            <td class="font-weight-bold">R$ {{ number_format($split->amount, 2, ',', '.') }}</td>
                            <td>{{ number_format($split->percentage, 2) }}%</td>
                            <td>
                                <code class="bg-light p-1 rounded">{{ $split->pix_key ?: 'Não informada' }}</code>
                            </td>
                            <td>
                                @if($split->status === 'paid')
                                    <span class="badge badge-success">Confirmado</span>
                                @else
                                    <span class="badge badge-warning">Pendente</span>
                                @endif
                            </td>
                            <td>{{ $split->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                @if($split->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-success btn-liquidate" data-id="{{ $split->id }}"
                                        data-url="{{ route('admin.splits.pay', $split->id) }}" title="Liquidar Rateio">
                                        <i class="fas fa-check-double text-xs"></i>
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-light border" disabled>
                                        <i class="fas fa-check text-muted text-xs"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Ainda não há registros de rateio.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $splits->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('.btn-liquidate').on('click', function () {
                const id = $(this).data('id');
                const url = $(this).data('url');

                Swal.fire({
                    title: 'Liquidar Rateio?',
                    text: "Você confirma que o repasse via PIX para este recebedor já foi realizado?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sim, Liquidar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Sucesso!',
                                        text: response.message,
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Erro!', response.message, 'error');
                                }
                            },
                            error: function (xhr) {
                                let msg = 'Ocorreu um erro ao processar.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Erro!', msg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush