@extends('panel.layouts.app')

@section('title', 'Loja de Resgate de Pontos')

@section('panel_content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    Itens Resgatáveis
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Gerencie produtos e serviços
                    que os usuários podem trocar por pontos.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.redemptions.create') }}"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <span>Novo Item</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Items List --}}
            <div class="lg:col-span-2 space-y-6">
                <div
                    class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-slate-50/50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 transition-colors">
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                        Item</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">
                                        Custo (Pontos)</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">
                                        Estoque</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">
                                        Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 transition-colors">
                                @forelse($items as $item)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30 transition-all group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                @if($item->image)
                                                    <img src="{{ asset('storage/' . $item->image) }}"
                                                        class="w-10 h-10 rounded-lg object-cover">
                                                @else
                                                    <div
                                                        class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                                        <i class="fas fa-gift"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="text-sm font-bold text-slate-900 dark:text-white">
                                                        {{ $item->name }}
                                                    </div>
                                                    <div
                                                        class="text-xs {{ $item->is_active ? 'text-emerald-500' : 'text-slate-400' }}">
                                                        {{ $item->is_active ? 'Ativo' : 'Inativo' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="text-sm font-bold text-blue-600">{{ number_format($item->points_cost, 0, ',', '.') }}
                                                pts</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="text-sm font-medium text-slate-600 dark:text-slate-400">{{ $item->stock }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div
                                                class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a href="{{ route('panel.admin.redemptions.edit', $item) }}"
                                                    class="p-2 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-all">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-6 py-12 text-center text-slate-500 dark:text-slate-400 italic">Nenhum item
                                            cadastrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($items->hasPages())
                        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Pending Redemptions --}}
            <div class="space-y-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest px-2 transition-colors">Solicitações
                    Pendentes</h3>
                @forelse($pendingRedemptions as $redemption)
                    <div
                        class="bg-white dark:bg-slate-900 p-6 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-4 transition-colors">
                        <div class="flex items-center gap-3">
                            <img src="{{ $redemption->user->profile_photo_url }}" class="w-10 h-10 rounded-full">
                            <div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $redemption->user->name }}
                                </div>
                                <div class="text-xs text-slate-500 italic">{{ $redemption->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div
                            class="p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-800 transition-colors">
                            <div class="text-xs font-bold text-slate-400 uppercase mb-1">Item Solicitado</div>
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $redemption->item->name }}
                            </div>
                            <div class="text-xs text-blue-600 font-bold mt-1">-
                                {{ number_format($redemption->points_spent, 0, ',', '.') }} pts
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('panel.admin.redemptions.approve', $redemption) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-500/20 transition-all">
                                    Aprovar
                                </button>
                            </form>
                            <form action="{{ route('panel.admin.redemptions.cancel', $redemption) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold rounded-xl hover:bg-slate-200 transition-all">
                                    Cancelar
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-dashed border-slate-200 dark:border-slate-800 text-center text-sm text-slate-500 dark:text-slate-400">
                        Nenhuma solicitação pendente no momento.
                    </div>
                @endforelse

                @if($pendingRedemptions->hasPages())
                    <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-4 shadow-sm">
                        {{ $pendingRedemptions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
