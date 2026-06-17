@extends('admin.layouts.app')

@section('page_title', 'Cupons do Evento')

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h3 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-ticket-alt mr-2"></i>Cupons de gratuidade
                </h3>
                <small class="text-muted d-block">{{ $event->title }}</small>
            </div>
            <div class="ml-auto">
                <a href="{{ route($eventsRoutePrefix . '.edit', $event) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
                <a href="{{ route($routePrefix . '.create', $event) }}" class="btn btn-primary btn-sm rounded-pill">
                    <i class="fas fa-plus mr-1"></i> Novo cupom
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Usos</th>
                            <th>Validade</th>
                            <th>Status</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coupon)
                            <tr>
                                <td><span class="badge badge-dark px-3 py-2">{{ $coupon->code }}</span></td>
                                <td>
                                    @if($coupon->type === \App\Models\EventCoupon::TYPE_FREE)
                                        <span class="badge badge-success">Gratuidade total</span>
                                    @elseif($coupon->type === \App\Models\EventCoupon::TYPE_PERCENT)
                                        <span class="badge badge-info">{{ number_format((float) $coupon->discount_value, 2, ',', '.') }}%</span>
                                    @else
                                        <span class="badge badge-warning">R$ {{ number_format((float) $coupon->discount_value, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ (int) $coupon->used_count }}</strong>
                                    <span class="text-muted">/ {{ $coupon->max_uses ? (int) $coupon->max_uses : 'ilimitado' }}</span>
                                </td>
                                <td class="small text-muted">
                                    @if($coupon->starts_at)<div>Inicio: {{ $coupon->starts_at->format('d/m/Y H:i') }}</div>@endif
                                    @if($coupon->expires_at)<div>Fim: {{ $coupon->expires_at->format('d/m/Y H:i') }}</div>@endif
                                    @if(!$coupon->starts_at && !$coupon->expires_at)<span>Sem período definido</span>@endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $coupon->active ? 'success' : 'secondary' }}">
                                        {{ $coupon->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="btn-group btn-group-sm">
                                        <form method="POST" action="{{ route($routePrefix . '.toggle', [$event, $coupon]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-{{ $coupon->active ? 'secondary' : 'success' }}" title="{{ $coupon->active ? 'Desativar' : 'Ativar' }}">
                                                <i class="fas fa-toggle-{{ $coupon->active ? 'on' : 'off' }}"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route($routePrefix . '.edit', [$event, $coupon]) }}" class="btn btn-outline-info" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route($routePrefix . '.destroy', [$event, $coupon]) }}" class="form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger btn-delete" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-ticket-alt fa-2x d-block mb-2"></i>
                                    Nenhum cupom cadastrado para este evento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($coupons->hasPages())
            <div class="card-footer">{{ $coupons->links() }}</div>
        @endif
    </div>
</div>
@endsection
