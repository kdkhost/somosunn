@extends('layouts.app')

@section('title', 'Pagamento Confirmado')

@section('content')
@php
    $isPaid = $order->status === 'paid';
    $planLabel = $planName ?: 'Assinatura Premium';
@endphp
<div class="min-h-screen bg-slate-50 pt-32 pb-20 px-4 flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 text-center animate-fadeInUp">
        <div class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 {{ $isPaid ? 'bg-green-100' : 'bg-yellow-100' }}">
            <i class="fas {{ $isPaid ? 'fa-check' : 'fa-hourglass-half' }} text-5xl {{ $isPaid ? 'text-green-500' : 'text-yellow-700' }}"></i>
        </div>
        
        <h1 class="text-3xl font-black text-gray-900 mb-4">
            {{ $isPaid ? 'Pagamento Aprovado!' : 'Pagamento em processamento' }}
        </h1>
        <p class="text-gray-600 mb-8 leading-relaxed">
            @if($isPaid)
                Parabéns! Sua assinatura do plano <strong>{{ $planLabel }}</strong> foi confirmada com sucesso. Seu acesso já está liberado.
            @else
                Seu pagamento ainda não foi confirmado. Assim que o MercadoPago aprovar, seu acesso ao plano <strong>{{ $planLabel }}</strong> será liberado automaticamente.
            @endif
        </p>
        
        <div class="bg-slate-50 rounded-xl p-4 mb-8 text-left border border-gray-100">
            <div class="flex justify-between mb-2">
                <span class="text-gray-500 text-sm">Pedido</span>
                <span class="font-bold text-gray-900">#{{ $order->id }}</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-500 text-sm">Valor</span>
                <span class="font-bold text-gray-900">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 text-sm">Status</span>
                <span class="font-bold text-gray-900">{{ $order->status }}</span>
            </div>
        </div>

        <div class="space-y-4">
            @if($isPaid)
                <a href="{{ route('portal') }}" class="block w-full btn-primary text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition">
                    Acessar Portal de Membros
                </a>
            @else
                <button onclick="window.location.reload()" class="block w-full btn-primary text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition">
                    Atualizar status
                </button>
                <script>
                    setTimeout(() => window.location.reload(), 8000);
                </script>
            @endif
            <a href="{{ route('home') }}" class="block text-gray-500 hover:text-gray-900 font-medium">
                Voltar para o início
            </a>
        </div>
    </div>
</div>
@endsection
