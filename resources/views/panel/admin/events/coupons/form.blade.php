@extends('panel.layouts.app')

@section('title', $coupon->exists ? 'Editar Cupom do Evento' : 'Novo Cupom do Evento')

@section('panel_content')
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-slate-950 dark:text-white">{{ $coupon->exists ? 'Editar cupom' : 'Novo cupom' }}</h1>
        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $event->title }}</p>
    </div>

    <form method="POST" action="{{ $coupon->exists ? route($routePrefix . '.update', [$event, $coupon]) : route($routePrefix . '.store', $event) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @csrf
        @if($coupon->exists) @method('PUT') @endif

        <div class="grid gap-5 md:grid-cols-2">
            <label class="space-y-2 md:col-span-2">
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Código do cupom</span>
                <input name="code" value="{{ old('code', $coupon->code) }}" maxlength="40" required placeholder="EX: CONVIDADO100" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm font-black uppercase text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                @error('code')<span class="text-xs font-bold text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="space-y-2">
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Tipo</span>
                <select name="type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="free" {{ old('type', $coupon->type ?: 'free') === 'free' ? 'selected' : '' }}>Gratuidade total</option>
                    <option value="percent" {{ old('type', $coupon->type) === 'percent' ? 'selected' : '' }}>Percentual</option>
                    <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Valor fixo</option>
                </select>
                @error('type')<span class="text-xs font-bold text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="space-y-2">
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Valor do desconto</span>
                <input name="discount_value" value="{{ old('discount_value', $coupon->discount_value ? number_format((float) $coupon->discount_value, 2, ',', '.') : '100,00') }}" class="mask-money w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <span class="block text-xs font-semibold text-slate-500">Na gratuidade total este valor é ajustado para 100%.</span>
                @error('discount_value')<span class="text-xs font-bold text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="space-y-2">
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Limite de usos</span>
                <input type="number" min="1" name="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" placeholder="Ilimitado" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                @error('max_uses')<span class="text-xs font-bold text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="space-y-2">
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Status</span>
                <select name="active" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="1" {{ old('active', $coupon->active ?? true) ? 'selected' : '' }}>Ativo</option>
                    <option value="0" {{ !old('active', $coupon->active ?? true) ? 'selected' : '' }}>Inativo</option>
                </select>
            </label>

            <label class="space-y-2">
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Começa em</span>
                <input type="text" name="starts_at" value="{{ old('starts_at', $coupon->starts_at ? $coupon->starts_at->format('Y-m-d H:i') : '') }}" data-datetime-picker placeholder="DD/MM/AAAA HH:MM" autocomplete="off" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                @error('starts_at')<span class="text-xs font-bold text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="space-y-2">
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Expira em</span>
                <input type="text" name="expires_at" value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d H:i') : '') }}" data-datetime-picker placeholder="DD/MM/AAAA HH:MM" autocomplete="off" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                @error('expires_at')<span class="text-xs font-bold text-red-600">{{ $message }}</span>@enderror
            </label>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <a href="{{ route($routePrefix . '.index', $event) }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancelar</a>
            <button class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-black text-white hover:bg-blue-700">
                <i class="fas fa-save mr-1"></i> Salvar cupom
            </button>
        </div>
    </form>
</div>
@endsection
