@extends('admin.layouts.app')

@section('page_title', $coupon->exists ? 'Editar Cupom do Evento' : 'Novo Cupom do Evento')

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h3 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-ticket-alt mr-2"></i>{{ $coupon->exists ? 'Editar cupom' : 'Novo cupom' }}
                </h3>
                <small class="text-muted d-block">{{ $event->title }}</small>
            </div>
            <div class="ml-auto">
                <a href="{{ route($eventsRoutePrefix . '.edit', $event) }}" class="btn btn-outline-secondary btn-sm rounded-pill" data-pjax>
                    <i class="fas fa-calendar-check mr-1"></i> Evento
                </a>
                <a href="{{ route($eventsRoutePrefix . '.edit', ['event' => $event, 'tab' => 'coupons']) }}" class="btn btn-outline-primary btn-sm rounded-pill" data-pjax>
                    <i class="fas fa-arrow-left mr-1"></i> Cupons
                </a>
            </div>
        </div>
        <form class="ajax-form" method="POST" action="{{ $coupon->exists ? route($routePrefix . '.update', [$event, $coupon]) : route($routePrefix . '.store', $event) }}">
            @csrf
            @if($coupon->exists) @method('PUT') @endif
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Código do cupom</label>
                            <input name="code" value="{{ old('code', $coupon->code) }}" class="form-control text-uppercase" maxlength="40" required placeholder="EX: CONVIDADO100">
                            @error('code')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipo</label>
                            <select name="type" class="form-control js-event-coupon-type">
                                <option value="free" {{ old('type', $coupon->type ?: 'free') === 'free' ? 'selected' : '' }}>Gratuidade total</option>
                                <option value="percent" {{ old('type', $coupon->type) === 'percent' ? 'selected' : '' }}>Percentual</option>
                                <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Valor fixo</option>
                            </select>
                            @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group js-discount-value-wrap">
                            <label>Valor do desconto</label>
                            <input name="discount_value" value="{{ old('discount_value', $coupon->discount_value ? number_format((float) $coupon->discount_value, 2, ',', '.') : '100,00') }}" class="form-control mask-money">
                            <small class="text-muted">Para gratuidade total o sistema usa 100% automaticamente.</small>
                            @error('discount_value')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Limite de usos</label>
                            <input type="number" min="1" name="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" class="form-control" placeholder="Ilimitado">
                            @error('max_uses')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="active" class="form-control">
                                <option value="1" {{ old('active', $coupon->active ?? true) ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ !old('active', $coupon->active ?? true) ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-md-0">
                            <label>Começa em</label>
                            <input type="text" name="starts_at" value="{{ old('starts_at', $coupon->starts_at ? $coupon->starts_at->format('Y-m-d H:i') : '') }}" class="form-control" data-datetime-picker placeholder="DD/MM/AAAA HH:MM" autocomplete="off">
                            @error('starts_at')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label>Expira em</label>
                            <input type="text" name="expires_at" value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d H:i') : '') }}" class="form-control" data-datetime-picker placeholder="DD/MM/AAAA HH:MM" autocomplete="off">
                            <small class="text-muted">Se deixar em branco, o cupom vale ate o encerramento do evento.</small>
                            @error('expires_at')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route($eventsRoutePrefix . '.edit', ['event' => $event, 'tab' => 'coupons']) }}" class="btn btn-outline-secondary rounded-pill" data-pjax>Cancelar</a>
                <button class="btn btn-primary rounded-pill"><i class="fas fa-save mr-1"></i> Salvar cupom</button>
            </div>
        </form>
    </div>
</div>
@endsection
