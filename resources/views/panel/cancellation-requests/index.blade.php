@extends('panel.layouts.app')

@section('title', 'Solicitações de Cancelamento - UNN')

@section('panel_content')
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors">Solicitações de Cancelamento</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">Acompanhe suas solicitações de cancelamento de pedidos.</p>
            </div>
        </div>
    </div>

    <div class="mt-6 space-y-4">
        @forelse($requests as $request)
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($request->status === 'pending') bg-amber-50 text-amber-700 border border-amber-100
                                @elseif($request->status === 'approved') bg-green-50 text-green-700 border border-green-100
                                @else bg-red-50 text-red-700 border border-red-100 @endif">
                                @if($request->status === 'pending') Pendente
                                @elseif($request->status === 'approved') Aprovado
                                @else Rejeitado @endif
                            </span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">
                                Pedido #{{ $request->order_id }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-700 dark:text-slate-300 mb-2">{{ $request->reason }}</p>
                        @if($request->admin_response)
                            <p class="text-sm text-slate-600 dark:text-slate-400 italic">Resposta: {{ $request->admin_response }}</p>
                        @endif
                    </div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $request->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-12 text-center transition-colors duration-300">
                <div class="text-slate-400 dark:text-slate-500 mb-4">
                    <i class="fas fa-inbox text-4xl"></i>
                </div>
                <p class="text-slate-600 dark:text-slate-400">Nenhuma solicitação de cancelamento encontrada.</p>
            </div>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div class="mt-6">
            {{ $requests->links() }}
        </div>
    @endif
@endsection
