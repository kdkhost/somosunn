@extends('panel.layouts.app')

@section('title', 'Gerenciar Faturas')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Faturas</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Gerencie faturas manuais e automáticas da plataforma (PDF).</p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('panel.admin.invoices.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30 transform hover:scale-[1.02]">
                <i class="fas fa-plus"></i>
                Nova Fatura
            </a>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="flex flex-col md:flex-row gap-4">
        <form action="{{ route('panel.admin.invoices.index') }}" method="GET" class="flex-1">
            <div class="relative group">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors"></i>
                <input type="text" name="q" value="{{ $q ?? '' }}" 
                    placeholder="Buscar por número, nome ou e-mail..." 
                    class="pl-10 pr-4 py-2 w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-sm">
            </div>
        </form>
    </div>

    {{-- Content --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-950 text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold border-b border-slate-100 dark:border-slate-800 transition-colors">
                        <th class="px-6 py-4">Fatura</th>
                        <th class="px-6 py-4">Cliente</th>
                        <th class="px-6 py-4">Pedido</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Total</th>
                        <th class="px-6 py-4">Emissão</th>
                        <th class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                            {{-- Número --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-md border border-slate-200 dark:border-slate-700">
                                    {{ $inv->number ?: ('#'.$inv->id) }}
                                </span>
                            </td>

                            {{-- Cliente --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-900 dark:text-white text-sm transition-colors">
                                            {{ $inv->user->name ?? '—' }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 truncate transition-colors" title="{{ $inv->user->email ?? '' }}">
                                            {{ $inv->user->email ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Pedido --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($inv->order_id)
                                    <a href="{{ route('panel.admin.orders.show', $inv->order_id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium text-sm transition-colors">
                                        #{{ $inv->order_id }}
                                    </a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 text-sm transition-colors">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $statusClasses = match($inv->status){
                                        'paid' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/50',
                                        'draft' => 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                        'cancelled' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-100 dark:border-red-800/50',
                                        default => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800/50',
                                    };
                                    $label = match($inv->status){
                                        'paid' => 'Paga',
                                        'draft' => 'Rascunho',
                                        'cancelled' => 'Cancelada',
                                        default => 'Emitida',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClasses }} transition-colors">
                                    {{ $label }}
                                </span>
                            </td>

                            {{-- Total --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900 dark:text-white text-sm text-right transition-colors">
                                    R$ {{ number_format((float) $inv->total_amount, 2, ',', '.') }}
                                </div>
                            </td>

                            {{-- Emissão --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 transition-colors">
                                {{ $inv->issued_at ? $inv->issued_at->format('d/m/Y') : ($inv->created_at?->format('d/m/Y') ?? '—') }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('panel.admin.invoices.show', $inv) }}" 
                                       class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white rounded-lg transition-colors border border-transparent" 
                                       title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('panel.admin.invoices.pdf', $inv) }}" target="_blank"
                                       class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition-colors border border-transparent" 
                                       title="Visualizar PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <form action="{{ route('panel.admin.invoices.send', $inv) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="force" value="1">
                                        <button type="submit" class="p-2 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-slate-500 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 rounded-lg transition-colors border border-transparent" title="Enviar por e-mail">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('panel.admin.invoices.destroy', $inv) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja remover esta fatura?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400 rounded-lg transition-colors border border-transparent" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400 transition-colors">
                                <i class="fas fa-file-invoice fa-3x mb-3 text-slate-200 dark:text-slate-800 transition-colors"></i>
                                <p>Nenhuma fatura encontrada.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($invoices->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 transition-colors duration-300">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
