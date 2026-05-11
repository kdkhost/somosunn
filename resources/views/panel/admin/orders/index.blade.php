@extends('panel.layouts.app')

@section('title', 'Gerenciar Vendas')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.orders.index') }}" class="hover:underline">Vendas</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Vendas</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">
                    Gerencie todos os pedidos e transacoes da plataforma.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-all shadow-sm">
                        <i class="fas fa-filter text-slate-400 dark:text-slate-500"></i>
                        <span>Status: {{ request('status') ? ucfirst(request('status')) : 'Todos' }}</span>
                        <i class="fas fa-chevron-down text-xs text-slate-400 dark:text-slate-500"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 py-2 z-50 origin-top-right focus:outline-none"
                        style="display: none;">
                        <a href="{{ route('panel.admin.orders.index', array_merge(request()->except('status'), ['status' => null])) }}"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span> Todos
                        </a>
                        <a href="{{ route('panel.admin.orders.index', array_merge(request()->except('status'), ['status' => 'paid'])) }}"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Pago
                        </a>
                        <a href="{{ route('panel.admin.orders.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Pendente
                        </a>
                        <a href="{{ route('panel.admin.orders.index', array_merge(request()->except('status'), ['status' => 'failed'])) }}"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> Falha
                        </a>
                        <a href="{{ route('panel.admin.orders.index', array_merge(request()->except('status'), ['status' => 'refunded'])) }}"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-slate-600"></span> Reembolsado
                        </a>
                    </div>
                </div>

                <form action="{{ route('panel.admin.orders.index') }}" method="GET" class="relative">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Buscar ID ou cliente..."
                            class="pl-10 pr-4 py-2 w-64 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm hover:border-slate-300 dark:hover:border-slate-600">
                    </div>
                </form>
            </div>
        </div>

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
                            <th class="px-6 py-4 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="font-mono text-xs font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-md border border-slate-200 dark:border-slate-700">
                                        #{{ $order->id }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0 transition-colors">
                                            @if($order->user && $order->user->profile_photo_url && !str_contains($order->user->profile_photo_url, 'default-user.svg'))
                                                <img src="{{ $order->user->profile_photo_url }}" alt=""
                                                    class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-user text-slate-400 dark:text-slate-500 text-[10px]"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-slate-900 dark:text-white text-sm truncate max-w-[150px]"
                                                title="{{ $order->user->name ?? 'Usuario removido' }}">
                                                {{ $order->user->name ?? 'Usuario removido' }}
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[150px]"
                                                title="{{ $order->user->email ?? '' }}">
                                                {{ $order->user->email ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($order->gateway === 'sumup')
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50 transition-colors">
                                            <i class="fas fa-credit-card text-[10px]"></i>
                                            SumUp
                                        </div>
                                    @elseif($order->gateway === 'mercadopago')
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50 transition-colors">
                                            <i class="fas fa-handshake text-[10px]"></i>
                                            MercadoPago
                                        </div>
                                    @else
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 transition-colors">
                                            <i class="fas fa-wallet text-[10px]"></i>
                                            {{ ucfirst($order->gateway ?? 'N/A') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($order->is_partially_refunded)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50 transition-colors">
                                            <i class="fas fa-coins text-[10px]"></i>
                                            Parcial
                                        </span>
                                        <span class="block text-[11px] text-amber-600 dark:text-amber-400 mt-1">
                                            R$ {{ number_format($order->refunded_amount, 2, ',', '.') }} de
                                            R$ {{ number_format($order->charged_amount, 2, ',', '.') }}
                                        </span>
                                    @elseif($order->status === 'paid')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50 transition-colors">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Pago
                                        </span>
                                    @elseif($order->status === 'pending')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50 transition-colors">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Pendente
                                        </span>
                                    @elseif($order->status === 'refunded')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-100 dark:border-red-800/50 transition-colors">
                                            <i class="fas fa-undo text-[10px]"></i>
                                            Reembolsado
                                        </span>
                                    @elseif($order->status === 'failed')
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

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900 dark:text-white text-sm transition-colors">
                                        R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}
                                    </div>
                                    @if($order->refunded_amount > 0)
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            Estornado: R$ {{ number_format($order->refunded_amount, 2, ',', '.') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 transition-colors">
                                    {{ $order->created_at->format('d/m/Y') }}
                                    <span class="text-xs text-slate-400 dark:text-slate-500 block transition-colors">
                                        {{ $order->created_at->format('H:i') }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2 text-slate-400 dark:text-slate-500">
                                        <a href="{{ route('panel.admin.orders.show', $order->id) }}"
                                            class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition-colors border border-transparent hover:border-blue-100 dark:hover:border-blue-800/50"
                                            title="Ver detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($order->status === 'paid' && $order->remaining_refundable_amount > 0)
                                            @if($order->supportsPartialRefund())
                                                <a href="{{ route('panel.admin.orders.show', $order->id) }}#refund-actions"
                                                    class="p-2 hover:bg-amber-50 dark:hover:bg-amber-900/20 hover:text-amber-600 dark:hover:text-amber-400 rounded-lg transition-colors border border-transparent hover:border-amber-100 dark:hover:border-amber-800/50"
                                                    title="Estorno parcial">
                                                    <i class="fas fa-coins"></i>
                                                </a>
                                            @endif

                                            <form action="{{ route('panel.admin.orders.refund', $order->id) }}" method="POST"
                                                onsubmit="return confirmAction(event, 'Estorno total?', 'Deseja devolver todo o valor restante deste pedido?');">
                                                @csrf
                                                <button type="submit"
                                                    class="p-2 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors border border-transparent hover:border-red-100 dark:hover:border-red-800/50"
                                                    title="Estorno total">
                                                    <i class="fas fa-undo-alt"></i>
                                                </button>
                                            </form>
                                        @endif
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
                                    <h3 class="text-slate-900 dark:text-white font-medium mb-1 transition-colors">
                                        Nenhuma venda encontrada
                                    </h3>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm transition-colors">
                                        Tente ajustar seus filtros de busca.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div
                    class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 transition-colors duration-300">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
