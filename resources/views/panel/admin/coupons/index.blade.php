@extends('panel.layouts.app')

@section('title', 'Gerenciar Cupons')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.coupons.index') }}" class="hover:underline">Cupons</a>
@endsection

@section('panel_content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Cupons de Desconto</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Crie e gerencie códigos promocionais para seus produtos.</p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('panel.admin.coupons.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30 transform hover:scale-[1.02]">
                <i class="fas fa-plus"></i>
                Novo Cupom
            </a>
        </div>
    </div>

    {{-- Content --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-950 text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold border-b border-slate-100 dark:border-slate-800 transition-colors">
                        <th class="px-6 py-4">Código</th>
                        <th class="px-6 py-4">Tipo</th>
                        <th class="px-6 py-4 text-right">Valor</th>
                        <th class="px-6 py-4 text-center">Escopo</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Validade</th>
                        <th class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                            {{-- Código --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1 rounded-lg border border-blue-100 dark:border-blue-800/50 uppercase tracking-wider transition-colors">
                                    {{ $coupon->code }}
                                </span>
                                @if($coupon->name)
                                    <span class="block text-xs text-slate-400 dark:text-slate-500 mt-1 font-medium transition-colors">{{ $coupon->name }}</span>
                                @endif
                            </td>

                            {{-- Tipo --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 transition-colors">
                                    @if($coupon->discount_type === 'percent')
                                        <i class="fas fa-percentage text-slate-400 dark:text-slate-500"></i>
                                        <span>Percentual</span>
                                    @else
                                        <i class="fas fa-tag text-slate-400 dark:text-slate-500"></i>
                                        <span>Valor Fixo</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Valor --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="font-bold text-slate-900 dark:text-white text-sm transition-colors">
                                    @if($coupon->discount_type === 'percent')
                                        {{ rtrim(rtrim(number_format($coupon->discount_value, 2, ',', '.'), '0'), ',') }}%
                                    @else
                                        R$ {{ number_format($coupon->discount_value, 2, ',', '.') }}
                                    @endif
                                </div>
                            </td>

                            {{-- Escopo --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $scopeLabel = match($coupon->applies_to) {
                                        'event' => 'Evento',
                                        'course' => 'Curso',
                                        'mentorship' => 'Mentoria',
                                        default => 'Geral',
                                    };
                                    $scopeIcon = match($coupon->applies_to) {
                                        'event' => 'calendar-alt',
                                        'course' => 'book',
                                        'mentorship' => 'users',
                                        default => 'globe',
                                    };
                                @endphp
                                <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-medium border border-slate-100 dark:border-slate-700 transition-colors">
                                    <i class="fas fa-{{ $scopeIcon }} text-[10px] opacity-60"></i>
                                    {{ $scopeLabel }}
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($coupon->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50 transition-colors">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Ativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 transition-colors">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                        Inativo
                                    </span>
                                @endif
                            </td>

                            {{-- Validade --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400 leading-tight transition-colors">
                                @if($coupon->starts_at || $coupon->ends_at)
                                    @if($coupon->starts_at)
                                        <div class="flex items-center gap-1">
                                            <span class="w-2.5 text-center text-[10px] font-bold text-slate-300 dark:text-slate-600">S</span>
                                            {{ $coupon->starts_at->format('d/m/Y') }}
                                        </div>
                                    @endif
                                    @if($coupon->ends_at)
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="w-2.5 text-center text-[10px] font-bold text-slate-300 dark:text-slate-600">E</span>
                                            {{ $coupon->ends_at->format('d/m/Y') }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-300 dark:text-slate-700 transition-colors">Sem expiração</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-1 transition-opacity">
                                    <a href="{{ route('panel.admin.coupons.edit', $coupon) }}" 
                                       class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition-colors border border-transparent" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('panel.admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja remover este cupom?');">
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
                                <i class="fas fa-ticket-alt fa-3x mb-3 text-slate-200 dark:text-slate-800 transition-colors"></i>
                                <p>Nenhum cupom cadastrado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($coupons->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 transition-colors duration-300">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
