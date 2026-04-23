@extends('panel.layouts.app')

@section('title', 'Transação SumUp #' . $transaction->id)

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.sumup.index') }}" class="hover:underline transition-all">SumUp</a>
    <span class="mx-2 text-slate-300 dark:text-slate-700">/</span>
    <span class="text-slate-500 dark:text-slate-400">Transação #{{ $transaction->id }}</span>
@endsection

@section('panel_content')
<div class="space-y-6 max-w-4xl">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Transação #{{ $transaction->id }}</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-mono">{{ $transaction->checkout_id }}</p>
        </div>
        @php
            $statusMap = [
                'PAID'     => ['bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400', 'Pago'],
                'PENDING'  => ['bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400', 'Pendente'],
                'FAILED'   => ['bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400', 'Falhou'],
                'REFUNDED' => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400', 'Reembolsado'],
            ];
            [$cls, $label] = $statusMap[$transaction->status] ?? ['bg-slate-100 text-slate-600', $transaction->status];
        @endphp
        <span class="inline-flex px-4 py-1.5 rounded-xl text-sm font-bold {{ $cls }}">{{ $label }}</span>
    </div>

    {{-- Dados da transação --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Checkout ID</p>
            <p class="font-mono text-sm text-slate-700 dark:text-slate-200 break-all">{{ $transaction->checkout_id }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Transaction ID</p>
            <p class="font-mono text-sm text-slate-700 dark:text-slate-200">{{ $transaction->transaction_id ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Valor</p>
            <p class="text-xl font-bold text-slate-800 dark:text-white">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Método de Pagamento</p>
            <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $transaction->payment_type }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Criado em</p>
            <p class="text-slate-700 dark:text-slate-200">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Atualizado em</p>
            <p class="text-slate-700 dark:text-slate-200">{{ $transaction->updated_at->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    {{-- Dados do comprador --}}
    @if($transaction->order?->user)
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
        <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="fas fa-user text-blue-500"></i> Comprador
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nome</p>
                <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $transaction->order->user->name }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">E-mail</p>
                <p class="text-slate-700 dark:text-slate-200">{{ $transaction->order->user->email }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Reembolso --}}
    @if($transaction->status === 'PAID' && $transaction->order)
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
        <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="fas fa-undo text-red-500"></i> Reembolso
        </h3>
        <form action="{{ route('panel.admin.sumup.refund', $transaction->order) }}" method="POST"
              onsubmit="return confirm('Confirmar reembolso?')">
            @csrf
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                        Valor (deixe em branco para reembolso total de R$ {{ number_format($transaction->amount, 2, ',', '.') }})
                    </label>
                    <input type="text" name="amount" placeholder="Ex: 50,00"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-sm dark:text-white focus:border-red-500 focus:ring-2 focus:ring-red-500/20 outline-none px-3 py-2">
                </div>
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg shadow-red-500/20 transition">
                    <i class="fas fa-undo mr-1"></i> Reembolsar
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Logs de Webhook --}}
    @if($transaction->webhookLogs->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
        <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="fas fa-bolt text-yellow-500"></i> Webhooks Recebidos
        </h3>
        <div class="space-y-3">
            @foreach($transaction->webhookLogs as $log)
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700">
                <div>
                    <span class="font-mono text-sm font-bold text-slate-700 dark:text-slate-200">{{ $log->event_type }}</span>
                    <span class="ml-3 text-xs text-slate-400">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    @if($log->is_valid)
                        <span class="text-xs font-bold text-green-600 dark:text-green-400"><i class="fas fa-check-circle"></i> Válido</span>
                    @else
                        <span class="text-xs font-bold text-red-500"><i class="fas fa-times-circle"></i> Inválido</span>
                    @endif
                    @if($log->processed_at)
                        <span class="text-xs text-slate-400">Processado</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
