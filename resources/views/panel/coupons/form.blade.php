@extends('member.layout')
@section('title', isset($coupon) && $coupon->id ? 'Editar Cupom' : 'Novo Cupom')
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">{{ isset($coupon) && $coupon->id ? 'Editar Cupom' : 'Novo Cupom' }}</h1>
    <form method="POST" action="{{ isset($coupon) && $coupon->id ? route('panel.coupons.update', $coupon) : route('panel.coupons.store') }}" class="space-y-6">
        @csrf
        @if(isset($coupon) && $coupon->id)
            @method('PUT')
        @endif
        <div>
            <label class="block text-sm font-semibold mb-1">Código</label>
            <input type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}" required maxlength="50" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Desconto (%)</label>
            <input type="number" name="discount_percent" value="{{ old('discount_percent', $coupon->discount_percent ?? '') }}" min="1" max="100" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Validade</label>
            <input type="date" name="expires_at" value="{{ old('expires_at', isset($coupon->expires_at) ? $coupon->expires_at->format('Y-m-d') : '') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('panel.coupons.index') }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800">Salvar</button>
        </div>
    </form>
</div>
@endsection
