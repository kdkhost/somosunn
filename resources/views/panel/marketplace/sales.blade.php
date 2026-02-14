@extends('member.layout')
@section('title', 'Minhas Vendas')
@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Minhas Vendas</h1>
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="text-gray-600">Total Recebido</div>
                <div class="text-lg font-bold text-green-700">R$ {{ number_format($paidTotal, 2, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-gray-600">Taxas</div>
                <div class="text-lg font-bold text-red-700">R$ {{ number_format($platformFeeTotal, 2, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-gray-600">Líquido</div>
                <div class="text-lg font-bold text-blue-700">R$ {{ number_format($netTotal, 2, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-gray-600">Vendas Pagas</div>
                <div class="text-lg font-bold">{{ $paidCount }}</div>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Cliente</th>
                    <th class="px-4 py-2 text-left">Itens</th>
                    <th class="px-4 py-2 text-left">Valor</th>
                    <th class="px-4 py-2 text-left">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td class="px-4 py-2">{{ $order->user->name ?? '-' }}</td>
                    <td class="px-4 py-2">
                        @foreach($order->items as $item)
                            <div>{{ $item->item_type }}: {{ $item->item_id }}</div>
                        @endforeach
                    </td>
                    <td class="px-4 py-2">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</td>
                    <td class="px-4 py-2">{{ ucfirst($order->status) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection
