@extends('member.layout')
@section('title', 'Marketplace')
@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Resumo do Marketplace</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600">Total Recebido</div>
            <div class="text-2xl font-bold text-green-700">R$ {{ number_format($paidTotal, 2, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600">Taxas da Plataforma</div>
            <div class="text-2xl font-bold text-red-700">R$ {{ number_format($platformFeeTotal, 2, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600">Líquido</div>
            <div class="text-2xl font-bold text-blue-700">R$ {{ number_format($netTotal, 2, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600">Vendas Pagas</div>
            <div class="text-2xl font-bold">{{ $paidCount }}</div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Pagamentos Configurados?</h2>
        <div class="mb-2">
            @if($paymentsConfigured)
                <span class="text-green-600 font-bold">Sim</span>
            @else
                <span class="text-red-600 font-bold">Não</span>
            @endif
        </div>
        <div class="text-sm text-gray-500">Taxa da plataforma: {{ $platformFeePercent }}%</div>
    </div>
</div>
@endsection
