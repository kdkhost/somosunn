@extends('member.layout')
@section('title', 'Pagamentos Marketplace')
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Pagamentos Marketplace</h1>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4">
            <span class="font-semibold">Pagamentos configurados?</span>
            @if($paymentsConfigured)
                <span class="text-green-600 font-bold ml-2">Sim</span>
            @else
                <span class="text-red-600 font-bold ml-2">Não</span>
            @endif
        </div>
        <div class="mb-2 text-sm text-gray-500">Webhook MercadoPago: <span class="font-mono">{{ $webhookUrl }}</span></div>
    </div>
</div>
@endsection
