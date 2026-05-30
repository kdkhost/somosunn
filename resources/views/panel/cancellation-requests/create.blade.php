@extends('panel.layouts.app')

@section('title', 'Solicitar Cancelamento - UNN')

@section('panel_content')
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors">Solicitar Cancelamento</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">
                    @if($orderItem)
                        Item: {{ $orderItem->title ?? $orderItem->description }}
                    @else
                        Pedido #{{ $order->id }}
                    @endif
                </p>
            </div>
            <a href="{{ route('panel.purchases.index') }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <form action="{{ route('panel.cancellation-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            @if($orderItem)
                <input type="hidden" name="order_item_id" value="{{ $orderItem->id }}">
            @endif

            <div class="space-y-4">
                <div>
                    <label for="reason" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                        Motivo do Cancelamento
                    </label>
                    <textarea name="reason" id="reason" rows="5"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-slate-900 dark:text-white"
                        placeholder="Descreva o motivo pelo qual deseja cancelar este pedido..." required></textarea>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all">
                        <i class="fas fa-paper-plane mr-2"></i> Enviar Solicitação
                    </button>
                    <a href="{{ route('panel.purchases.index') }}"
                        class="inline-flex items-center justify-center px-6 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl transition-all">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
@endsection
