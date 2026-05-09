@extends('admin.layouts.app')

@section('page_title','Faturas')
@section('breadcrumb')<li class="breadcrumb-item active">Faturas</li>@endsection

@section('content')
<div class="container-fluid">
    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-info elevation-1">
                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de Faturas</span>
                    <span class="info-box-number">{{ $invoices->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-success elevation-1">
                <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pagas</span>
                    <span class="info-box-number">{{ $invoices->where('status', 'paid')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-warning elevation-1">
                <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Rascunhos</span>
                    <span class="info-box-number">{{ $invoices->where('status', 'draft')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-danger elevation-1">
                <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Canceladas</span>
                    <span class="info-box-number">{{ $invoices->where('status', 'cancelled')->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-2"></i>Faturas (PDF)</h3>
            <div class="card-tools">
                <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1" data-pjax>
                    <i class="fas fa-plus mr-1"></i> Nova fatura
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input name="q" class="form-control" placeholder="Buscar por número, nome ou e-mail" value="{{ $q ?? '' }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary rounded-right">Buscar</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th><i class="fas fa-hashtag text-muted mr-1"></i>Número</th>
                            <th><i class="fas fa-user text-muted mr-1"></i>Cliente</th>
                            <th><i class="fas fa-shopping-cart text-muted mr-1"></i>Pedido</th>
                            <th><i class="fas fa-info-circle text-muted mr-1"></i>Status</th>
                            <th><i class="fas fa-dollar-sign text-muted mr-1"></i>Total</th>
                            <th><i class="fas fa-calendar text-muted mr-1"></i>Emissão</th>
                            <th><i class="fas fa-envelope text-muted mr-1"></i>E-mail</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td class="font-weight-bold">{{ $inv->number ?: ('#'.$inv->id) }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $inv->user?->name ?? '—' }}</div>
                                    <small class="text-muted">{{ $inv->user?->email ?? '' }}</small>
                                </td>
                                <td class="text-muted">
                                    @if($inv->order_id)
                                        <span class="badge badge-light border">#{{ $inv->order_id }}</span>
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
                                        $icon = match($inv->status){
                                            'paid' => 'fa-check-circle',
                                            'draft' => 'fa-pencil-alt',
                                            'cancelled' => 'fa-ban',
                                            default => 'fa-paper-plane',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $badge }}"><i class="fas {{ $icon }} mr-1"></i>{{ $label }}</span>
                                </td>
                                <td class="font-weight-bold">R$ {{ number_format((float) $inv->total_amount, 2, ',', '.') }}</td>
                                <td class="text-muted small">
                                    {{ $inv->issued_at ? $inv->issued_at->format('d/m/Y H:i') : ($inv->created_at?->format('d/m/Y H:i') ?? '—') }}
                                </td>
                                <td class="text-muted small">
                                    @if($inv->email_sent_at)
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Enviado {{ $inv->email_sent_at->format('d/m/Y H:i') }}</span>
                                    @elseif($inv->email_queued_at)
                                        <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Enfileirado {{ $inv->email_queued_at->format('d/m/Y H:i') }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-sm btn-outline-info rounded-pill elevation-1 mr-1" data-pjax title="Ver detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.invoices.pdf', $inv) }}" class="btn btn-sm btn-outline-primary rounded-pill elevation-1 mr-1" target="_blank" title="Baixar PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.invoices.send', $inv) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="force" value="1">
                                            <button class="btn btn-sm btn-outline-success rounded-pill elevation-1 mr-1" type="submit" title="Enviar por e-mail">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </form>
                                        <a href="#" class="btn btn-sm btn-outline-danger rounded-pill elevation-1 btn-delete" data-action="{{ route('admin.invoices.destroy', $inv) }}" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-file-invoice fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-2">Nenhuma fatura encontrada.</p>
                                    <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1">
                                        <i class="fas fa-plus mr-1"></i> Criar primeira fatura
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $invoices->links() }}</div>
        </div>
    </div>
</div>
@endsection
