@extends('panel.layouts.app')

@section('title', 'Extrato de Recebimentos - UNN')

@section('panel_content')
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white">Meus Recebimentos</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">Acompanhe o rateio automático das suas vendas e comissões.</p>
            </div>
            <div class="flex gap-4">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800 p-4 rounded-2xl">
                    <span class="text-xs font-bold text-green-700 dark:text-green-300 uppercase block mb-1">Total Recebido</span>
                    <span class="text-xl font-extrabold text-green-900 dark:text-green-100">R$ {{ number_format($splits->where('status', 'paid')->sum('amount'), 2, ',', '.') }}</span>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 p-4 rounded-2xl">
                    <span class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase block mb-1">Pendente</span>
                    <span class="text-xl font-extrabold text-blue-900 dark:text-blue-100">R$ {{ number_format($splits->where('status', 'pending')->sum('amount'), 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Histórico de Rateios</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Pedido</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Valor</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Meta</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($splits as $split)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 dark:text-white">#{{ $split->order_id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-lg font-extrabold text-slate-900 dark:text-white">R$ {{ number_format($split->amount, 2, ',', '.') }}</span>
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ number_format($split->percentage, 2) }}% do total</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-full text-xs font-bold">
                                    {{ $split->receiver_type === 'seller' ? 'Venda Direta' : 'Participação' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($split->status === 'paid')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Confirmado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> Aguardando
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm text-slate-500 dark:text-slate-400">{{ $split->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-4">
                                        <i class="fas fa-receipt text-slate-300 dark:text-slate-600 text-2xl"></i>
                                    </div>
                                    <p class="text-slate-500 dark:text-slate-400 font-medium">Nenhum recebimento registrado ainda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($splits->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30">
                {{ $splits->links() }}
            </div>
        @endif
    </div>

    @if(!$user->pix_key)
        <div class="mt-6 p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30 rounded-2xl flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-amber-900 dark:text-amber-100">Chave PIX não cadastrada</h4>
                <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">Para receber seus repasses automaticamente, você precisa cadastrar sua chave PIX no <a href="{{ route('panel.profile.edit') }}" class="underline font-bold">seu perfil</a>.</p>
            </div>
        </div>
    @endif
@endsection
