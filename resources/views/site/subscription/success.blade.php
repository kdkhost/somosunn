@extends('layouts.app')

@section('title', 'Pagamento Confirmado')

@section('content')
<div class="min-h-screen bg-slate-50 pt-32 pb-20 px-4 flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 text-center animate-fadeInUp">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check text-5xl text-green-500 animate-bounce"></i>
        </div>
        
        <h1 class="text-3xl font-black text-gray-900 mb-4">Pagamento Aprovado!</h1>
        <p class="text-gray-600 mb-8 leading-relaxed">
            Parabéns! Sua assinatura do plano <strong>Assinatura Premium</strong> foi confirmada com sucesso. Seu acesso já está liberado.
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
                <span class="text-gray-500 text-sm">Data</span>
                <span class="font-bold text-gray-900">{{ $order->paid_at ? $order->paid_at->format('d/m/Y H:i') : now()->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="space-y-4">
            <a href="{{ route('portal') }}" class="block w-full btn-primary text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition">
                Acessar Portal de Membros
            </a>
            <a href="{{ route('home') }}" class="block text-gray-500 hover:text-gray-900 font-medium">
                Voltar para o início
            </a>
        </div>
    </div>
</div>
@endsection
