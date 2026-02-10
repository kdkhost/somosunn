@extends('admin.layouts.app')

@php
    $company = app(\App\Services\InvoiceService::class)->companyInfo();
@endphp

@section('page_title', 'Fatura ' . ($invoice->number ?: ('#' . $invoice->id)))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}" data-pjax>Faturas</a></li>
    <li class="breadcrumb-item active">{{ $invoice->number ?: ('#' . $invoice->id) }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    @php
                        $badge = match ($invoice->status) {
                            'paid' => 'success',
                            'draft' => 'secondary',
                            'cancelled' => 'danger',
                            default => 'info',
                        };
                        $label = match ($invoice->status) {
                            'paid' => 'Paga',
                            'draft' => 'Rascunho',
                            'cancelled' => 'Cancelada',
                            default => 'Emitida',
                        };
                    @endphp

                    <div class="text-center mb-4">
                        @if($company['logo_url'])
                            <img src="{{ $company['logo_url'] }}" alt="Logo" class="img-fluid mb-2" style="max-height: 50px;">
                        @else
                            <h3 class="text-primary font-weight-bold">{{ $company['name'] }}</h3>
                        @endif
                        <h4 class="mb-2">{{ $invoice->number ?: ('#' . $invoice->id) }}</h4>
                        <span class="badge badge-{{ $badge }}">{{ $label }}</span>
                    </div>

                    <hr>

                    <div class="small text-muted">Cliente</div>
                    <div class="font-weight-bold">{{ $invoice->user?->name ?? '—' }}</div>
                    <div class="text-muted">{{ $invoice->user?->email ?? '' }}</div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Subtotal</span>
                        <span>R$ {{ number_format((float) $invoice->subtotal, 2, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Desconto</span>
                        <span>R$ {{ number_format((float) $invoice->discount_amount, 2, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between font-weight-bold">
                        <span>Total</span>
                        <span>R$ {{ number_format((float) $invoice->total_amount, 2, ',', '.') }}</span>
                    </div>

                    <hr>

                    <div class="text-muted small">Emissão</div>
                    <div>{{ $invoice->issued_at?->format('d/m/Y H:i') ?? $invoice->created_at?->format('d/m/Y H:i') }}</div>

                    @if($invoice->due_at)
                        <div class="text-muted small mt-2">Vencimento</div>
                        <div>{{ $invoice->due_at->format('d/m/Y H:i') }}</div>
                    @endif

                    @if($invoice->email_sent_at)
                        <div class="text-muted small mt-2">E-mail</div>
                        <div>Enviado em {{ $invoice->email_sent_at->format('d/m/Y H:i') }}</div>
                    @elseif($invoice->email_queued_at)
                        <div class="text-muted small mt-2">E-mail</div>
                        <div>Enfileirado em {{ $invoice->email_queued_at->format('d/m/Y H:i') }}</div>
                    @endif

                    <hr>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-sm btn-secondary" data-pjax>
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>
                        <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-primary"
                            target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> PDF
                        </a>
                        <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="force" value="1">
                            <button class="btn btn-sm btn-outline-success" type="submit">
                                <i class="fas fa-paper-plane mr-1"></i> Enviar e-mail
                            </button>
                        </form>
                        <a href="#" class="btn btn-sm btn-danger btn-delete"
                            data-action="{{ route('admin.invoices.destroy', $invoice) }}"
                            data-redirect="{{ route('admin.invoices.index') }}">
                            <i class="fas fa-trash mr-1"></i> Excluir
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Itens</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th class="text-right">Qtd</th>
                                <th class="text-right">Valor</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items->sortBy('sort_order') as $item)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">{{ $item->description }}</div>
                                        @if($item->item_type)
                                            <div class="text-muted small">Tipo: {{ $item->item_type }}@if($item->item_id)
                                            #{{ $item->item_id }}@endif</div>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ (int) $item->quantity }}</td>
                                    <td class="text-right">R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                                    <td class="text-right">R$ {{ number_format((float) $item->total_price, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Nenhum item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(!empty($invoice->notes))
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Observações</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-muted">{!! nl2br(e($invoice->notes)) !!}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection