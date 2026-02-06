@extends('admin.layouts.app')

@section('page_title','Cupons')
@section('breadcrumb')<li class="breadcrumb-item active">Cupons</li>@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap mb-3">
            <h3 class="m-0">Cupons de desconto</h3>
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary" data-pjax>Novo cupom</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Escopo</th>
                        <th>Validade</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td class="font-weight-bold">{{ $coupon->code }}</td>
                            <td>{{ $coupon->discount_type === 'percent' ? 'Percentual' : 'Fixo' }}</td>
                            <td>
                                @if($coupon->discount_type === 'percent')
                                    {{ rtrim(rtrim(number_format($coupon->discount_value, 2, ',', '.'), '0'), ',') }}%
                                @else
                                    R$ {{ number_format($coupon->discount_value, 2, ',', '.') }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $scopeLabel = match($coupon->applies_to) {
                                        'event' => 'Evento',
                                        'course' => 'Curso',
                                        'mentorship' => 'Mentoria',
                                        default => 'Geral',
                                    };
                                @endphp
                                {{ $scopeLabel }}@if($coupon->applies_to_id) #{{ $coupon->applies_to_id }}@endif
                            </td>
                            <td class="text-muted small">
                                @php
                                    $from = $coupon->starts_at ? $coupon->starts_at->format('d/m/Y H:i') : null;
                                    $to = $coupon->ends_at ? $coupon->ends_at->format('d/m/Y H:i') : null;
                                @endphp
                                @if($from || $to)
                                    {{ $from ? 'De '.$from : '' }}{{ ($from && $to) ? ' • ' : '' }}{{ $to ? 'Até '.$to : '' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $coupon->is_active ? 'success' : 'secondary' }}">
                                    {{ $coupon->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-secondary" data-pjax>Editar</a>
                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-action="{{ route('admin.coupons.destroy', $coupon) }}">Excluir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhum cupom cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $coupons->links() }}
    </div>
</div>
@endsection

