@extends('admin.layouts.app')
@section('title', 'Controle de Repasses')
@section('page_title', 'Controle de Repasses')
@section('breadcrumb')
    <li class="breadcrumb-item active">Splits</li>
@endsection

@section('content')
    @php
        $typeConfig = [
            'seller' => ['label' => 'Vendedor', 'icon' => 'fa-store', 'color' => 'primary'],
            'platform' => ['label' => 'Plataforma', 'icon' => 'fa-building', 'color' => 'info'],
            'traffic' => ['label' => 'Marketing', 'icon' => 'fa-bullhorn', 'color' => 'warning'],
            'superadmin' => ['label' => 'Superadmin', 'icon' => 'fa-user-shield', 'color' => 'danger'],
        ];
    @endphp

    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-success"><i class="fas fa-circle-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Liquidado</span>
                    <span class="info-box-number text-success">R$ {{ number_format($summary['paid'], 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-warning"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Aguardando repasse</span>
                    <span class="info-box-number text-warning">R$ {{ number_format($summary['pending'], 2, ',', '.') }}</span>
                    <span class="progress-description text-xs">{{ $summary['pending_count'] }} pendente(s)</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Falha registrada</span>
                    <span class="info-box-number text-danger">R$ {{ number_format($summary['failed'], 2, ',', '.') }}</span>
                    <span class="progress-description text-xs">{{ $summary['failed_count'] }} com erro</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-primary"><i class="fas fa-chart-pie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Volume total</span>
                    <span class="info-box-number">R$ {{ number_format($summary['total'], 2, ',', '.') }}</span>
                    <span class="progress-description text-xs">{{ $splits->total() }} registro(s)</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.splits.index') }}" method="GET" class="row">
                <div class="col-lg-4 mb-2">
                    <input type="text" name="search" class="form-control" value="{{ $search }}"
                        placeholder="Buscar pedido, nome ou e-mail">
                </div>
                <div class="col-lg-2 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Todos os repasses</option>
                        <option value="pending" @selected($status === 'pending')>Pendentes</option>
                        <option value="paid" @selected($status === 'paid')>Liquidados</option>
                        <option value="failed" @selected($status === 'failed')>Com falha</option>
                    </select>
                </div>
                <div class="col-lg-2 mb-2">
                    <select name="receiver_type" class="form-control">
                        <option value="">Todos os destinatarios</option>
                        @foreach($typeConfig as $value => $cfg)
                            <option value="{{ $value }}" @selected($receiverType === $value)>{{ $cfg['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 mb-2 d-flex">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.splits.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-left mr-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-dark shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-money-bill-wave mr-2 text-primary"></i>Repasse por rateio
            </h3>
        </div>
        <div class="card-body p-0">
            @if($splits->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th class="pl-4">Pedido</th>
                                <th>Recebedor</th>
                                <th>Tipo</th>
                                <th class="text-right">Valor</th>
                                <th class="text-center">%</th>
                                <th>Chave PIX</th>
                                <th>Repasse</th>
                                <th>Operacao</th>
                                <th class="text-center">Acao</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($splits as $split)
                                @php
                                    $cfg = $typeConfig[$split->receiver_type] ?? ['label' => ucfirst($split->receiver_type), 'icon' => 'fa-circle', 'color' => 'secondary'];
                                    $payout = $split->payout;
                                    $payoutStatus = $payout?->status ?? 'pending';
                                    $provider = $payout?->provider ?? 'manual';
                                @endphp
                                <tr>
                                    <td class="pl-4 align-middle">
                                        <a href="{{ route('admin.orders.show', $split->order_id) }}" class="font-weight-bold text-primary">#{{ $split->order_id }}</a>
                                        <div class="text-xs text-muted">{{ $split->created_at?->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold">{{ $split->receiver?->name ?? 'Nao vinculado' }}</div>
                                        <div class="text-xs text-muted">{{ $split->receiver?->email }}</div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-{{ $cfg['color'] }}">
                                            <i class="fas {{ $cfg['icon'] }} mr-1"></i>{{ $cfg['label'] }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-right font-weight-bold">R$ {{ number_format((float) $split->amount, 2, ',', '.') }}</td>
                                    <td class="align-middle text-center">{{ number_format((float) $split->percentage, 2, ',', '.') }}%</td>
                                    <td class="align-middle">
                                        @if($split->pix_key)
                                            <code class="bg-light border rounded px-2 py-1 d-inline-block">{{ $split->pix_key }}</code>
                                        @else
                                            <span class="text-danger text-xs font-weight-bold">PIX ausente</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($payoutStatus === 'paid')
                                            <span class="badge badge-success px-2 py-1">Liquidado</span>
                                        @elseif($payoutStatus === 'failed')
                                            <span class="badge badge-danger px-2 py-1">Falhou</span>
                                        @else
                                            <span class="badge badge-warning px-2 py-1">Pendente</span>
                                        @endif
                                        <div class="text-xs text-muted mt-1">
                                            {{ $provider === 'internal' ? 'Interno' : 'Manual' }}
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="text-xs">
                                            <div><strong>Tentativas:</strong> {{ (int) ($payout?->attempts ?? 0) }}</div>
                                            @if(!empty($payout?->last_error))
                                                <div class="text-danger mt-1">{{ $payout->last_error }}</div>
                                            @elseif($payout?->processed_at)
                                                <div class="text-success mt-1">Confirmado em {{ $payout->processed_at->format('d/m/Y H:i') }}</div>
                                            @else
                                                <div class="text-muted mt-1">Aguardando tratamento</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($payoutStatus !== 'paid' && $split->pix_key)
                                            <div class="btn-group">
                                                <button type="button"
                                                    class="btn btn-sm btn-success js-confirm-payout"
                                                    data-url="{{ route('admin.splits.pay', $split) }}"
                                                    data-receiver="{{ $split->receiver?->name ?? $cfg['label'] }}"
                                                    data-amount="R$ {{ number_format((float) $split->amount, 2, ',', '.') }}">
                                                    <i class="fas fa-check mr-1"></i>Confirmar
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger js-fail-payout"
                                                    data-url="{{ route('admin.splits.fail', $split) }}">
                                                    <i class="fas fa-triangle-exclamation"></i>
                                                </button>
                                            </div>
                                        @elseif($payoutStatus !== 'paid')
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
                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted font-weight-bold">Nenhum repasse encontrado</h5>
                    <p class="text-muted mb-0">Os repasses sao preparados automaticamente quando um pedido pago gera rateios.</p>
                </div>
            @endif
        </div>

        @if($splits->hasPages())
            <div class="card-footer">
                {{ $splits->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload || {})
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Nao foi possivel concluir a operacao.');
        }

        return data;
    }

    document.querySelectorAll('.js-confirm-payout').forEach(function (button) {
        button.addEventListener('click', async function () {
            const confirmed = await Swal.fire({
                title: 'Confirmar repasse?',
                text: 'Use esta acao somente depois de realizar a transferencia para ' + button.dataset.receiver + ' no valor de ' + button.dataset.amount + '.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, confirmar',
                cancelButtonText: 'Cancelar'
            });

            if (!confirmed.isConfirmed) {
                return;
            }

            try {
                const data = await postJson(button.dataset.url);
                await Swal.fire('Concluido', data.message, 'success');
                window.location.reload();
            } catch (error) {
                await Swal.fire('Erro', error.message, 'error');
            }
        });
    });

    document.querySelectorAll('.js-fail-payout').forEach(function (button) {
        button.addEventListener('click', async function () {
            const result = await Swal.fire({
                title: 'Registrar falha',
                input: 'text',
                inputLabel: 'Motivo da falha do repasse',
                inputPlaceholder: 'Ex.: chave PIX recusada, conta indisponivel, divergencia de valor',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Registrar falha',
                cancelButtonText: 'Cancelar',
                inputValidator: function (value) {
                    if (!value) {
                        return 'Informe o motivo da falha.';
                    }
                }
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const data = await postJson(button.dataset.url, { message: result.value });
                await Swal.fire('Registrado', data.message, 'success');
                window.location.reload();
            } catch (error) {
                await Swal.fire('Erro', error.message, 'error');
            }
        });
    });
</script>
@endpush
