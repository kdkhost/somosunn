@extends('admin.layouts.app')

@section('page_title','Faturas')
@section('breadcrumb')<li class="breadcrumb-item active">Faturas</li>@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap mb-3">
            <h3 class="m-0">Faturas (PDF)</h3>
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary" data-pjax>Nova fatura</a>
        </div>

        <form method="GET" class="mb-3">
            <div class="input-group">
                <input name="q" class="form-control" placeholder="Buscar por número, nome ou e-mail" value="{{ $q ?? '' }}">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary">Buscar</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Pedido</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Emissão</th>
                        <th>E-mail</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td class="font-weight-bold">{{ $inv->number ?: ('#'.$inv->id) }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $inv->user?->name ?? '—' }}</div>
                                <div class="text-muted small">{{ $inv->user?->email ?? '' }}</div>
                            </td>
                            <td class="text-muted">
                                @if($inv->order_id)
                                    #{{ $inv->order_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @php
                                    $badge = match($inv->status){
                                        'paid' => 'success',
                                        'draft' => 'secondary',
                                        'cancelled' => 'danger',
                                        default => 'info',
                                    };
                                    $label = match($inv->status){
                                        'paid' => 'Paga',
                                        'draft' => 'Rascunho',
                                        'cancelled' => 'Cancelada',
                                        default => 'Emitida',
                                    };
                                @endphp
                                <span class="badge badge-{{ $badge }}">{{ $label }}</span>
                            </td>
                            <td>R$ {{ number_format((float) $inv->total_amount, 2, ',', '.') }}</td>
                            <td class="text-muted small">
                                {{ $inv->issued_at ? $inv->issued_at->format('d/m/Y H:i') : ($inv->created_at?->format('d/m/Y H:i') ?? '—') }}
                            </td>
                            <td class="text-muted small">
                                @if($inv->email_sent_at)
                                    Enviado em {{ $inv->email_sent_at->format('d/m/Y H:i') }}
                                @elseif($inv->email_queued_at)
                                    Enfileirado em {{ $inv->email_queued_at->format('d/m/Y H:i') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-sm btn-secondary" data-pjax>Ver</a>
                                <a href="{{ route('admin.invoices.pdf', $inv) }}" class="btn btn-sm btn-outline-primary" target="_blank">PDF</a>
                                <form method="POST" action="{{ route('admin.invoices.send', $inv) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="force" value="1">
                                    <button class="btn btn-sm btn-outline-success" type="submit">Enviar</button>
                                </form>
                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-action="{{ route('admin.invoices.destroy', $inv) }}">Excluir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma fatura encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $invoices->links() }}
    </div>
</div>
@endsection

