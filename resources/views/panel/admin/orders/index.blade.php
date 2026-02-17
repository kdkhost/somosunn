@extends('panel.layouts.app')

@section('title', 'Gerenciar Vendas')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Vendas</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Gerencie todos os pedidos e
                    transações da plataforma.</p>
            </div>

            <div class="flex items-center gap-2">
                {{-- Status Filter --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-all shadow-sm">
                        <i class="fas fa-filter text-slate-400 dark:text-slate-500"></i>
                        <span>Status: {{ request('status') ? ucfirst(request('status')) : 'Todos' }}</span>
                        <i class="fas fa-chevron-down text-xs text-slate-400 dark:text-slate-500"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-100 dark:border-slate-800 py-1 z-50 origin-top-right focus:outline-none"
                        style="display: none;">
                        <a href="{{ route('panel.admin.orders.index', array_merge(request()->except('status'), ['status' => null])) }}"
                            class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            Todos
                        </a>
                        <a href="{{ route('panel.admin.orders.index', array_merge(request()->except('status'), ['status' => 'paid'])) }}"
                            class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            Pago
                        </a>
                        <a href="{{ route('panel.admin.orders.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}"
                            class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            Pendente
                        </a>
                        <a href="{{ route('panel.admin.orders.index', array_merge(request()->except('status'), ['status' => 'failed'])) }}"
                            class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            Falha
                        </a>
                        <a href="{{ route('panel.admin.orders.index', array_merge(request()->except('status'), ['status' => 'refunded'])) }}"
                            class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            Reembolsado
                        </a>
                    </div>
                </div>

                {{-- Search --}}
                <form action="{{ route('panel.admin.orders.index') }}" method="GET" class="relative">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="relative">
                        <i
                            class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Buscar ID ou Cliente..."
                            class="pl-10 pr-4 py-2 w-64 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm hover:border-slate-300 dark:hover:border-slate-600">
                    </div>
                </form>
            </div>
        </div>

        {{-- Content --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50/50 dark:bg-slate-950 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-4">Pedido</th>
                            <th class="px-6 py-4">Cliente</th>
                            <th class="px-6 py-4">Gateway</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Valor</th>
                            <th class="px-6 py-4">Data</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                                {{-- ID --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="font-mono text-xs font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-md border border-slate-200 dark:border-slate-700">
                                        #{{ $order->id }}
                                    </span>
                                </td>

                                {{-- Cliente --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700">
                                            @if($order->user && $order->user->profile_photo_url)
                                                <img src="{{ $order->user->profile_photo_url }}" alt="{{ $order->user->name }}"
                                                    class="w-full h-full object-cover">
                                            @else
                                                <span
                                                    class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ mb_substr($order->user->name ?? '?', 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-slate-900 dark:text-white text-sm truncate max-w-[150px]"
                                                title="{{ $order->user->name ?? 'Usuário Removido' }}">
                                                {{ $order->user->name ?? 'Usuário Removido' }}
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[150px]"
                                                title="{{ $order->user->email ?? '' }}">
                                                {{ $order->user->email ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Gateway --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($order->gateway == 'mercadopago')
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50 transition-colors">
                                            <i class="fas fa-handshake text-[10px]"></i>
                                            MercadoPago
                                        </div>
                                    @elseif($order->gateway == 'pagseguro')
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-100 dark:border-green-800/50 transition-colors">
                                            <i class="fas fa-credit-card text-[10px]"></i>
                                            PagSeguro
                                        </div>
                                    @else
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 transition-colors">
                                            <i class="fas fa-wallet text-[10px]"></i>
                                            {{ ucfirst($order->gateway ?? 'N/A') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($order->status == 'paid')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50 transition-colors">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Pago
                                        </span>
                                    @elseif($order->status == 'pending')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50 transition-colors">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Pendente
                                        </span>
                                    @elseif($order->status == 'refunded')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-100 dark:border-red-800/50 transition-colors">
                                            <i class="fas fa-undo text-[10px]"></i>
                                            Reembolsado
                                        </span>
                                    @elseif($order->status == 'failed')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 transition-colors">
                                            <i class="fas fa-times-circle text-[10px]"></i>
                                            Falha
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 transition-colors">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Valor --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900 dark:text-white text-sm transition-colors">
                                        R$ {{ number_format($order->total_amount, 2, ',', '.') }}
                                    </div>
                                </td>

                                {{-- Data --}}
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 transition-colors">
                                    {{ $order->created_at->format('d/m/Y') }}
                                    <span
                                        class="text-xs text-slate-400 dark:text-slate-500 block transition-colors">{{ $order->created_at->format('H:i') }}</span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div
                                        class="flex items-center justify-end gap-2 text-slate-400 dark:text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                        @if($order->status === 'paid')
                                            <form action="{{ route('panel.admin.orders.refund', $order->id) }}" method="POST"
                                                onsubmit="return confirm('Tem certeza que deseja reembolsar este pedido?');">
                                                @csrf
                                                <button type="submit"
                                                    class="p-2 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors border border-transparent hover:border-red-100 dark:hover:border-red-800/50"
                                                    title="Reembolsar">
                                                    <i class="fas fa-undo-alt"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- TODO: Implement Show View --}}
                                        {{-- <a href="{{ route('panel.admin.orders.show', $order->id) }}"
                                            class="p-2 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors border border-transparent hover:border-blue-100"
                                            title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a> --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div
                                        class="w-16 h-16 bg-slate-50 dark:bg-slate-950 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-shopping-cart text-slate-300 dark:text-slate-700 text-xl"></i>
                                    </div>
                                    <h3 class="text-slate-900 dark:text-white font-medium mb-1 transition-colors">Nenhuma venda
                                        encontrada</h3>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm transition-colors">Tente ajustar seus
                                        filtros de busca.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($orders->hasPages())
                <div
                    class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 transition-colors duration-300">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection