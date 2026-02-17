@extends('panel.layouts.app')

@section('title', 'Gerenciar Cupons')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Cupons de Desconto</h1>
            <p class="text-sm text-slate-500 mt-1">Crie e gerencie códigos promocionais para seus produtos.</p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('panel.admin.coupons.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-sm shadow-blue-200">
                <i class="fas fa-plus"></i>
                Novo Cupom
            </a>
        </div>
    </div>

    {{-- Content --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-semibold">
                        <th class="px-6 py-4">Código</th>
                        <th class="px-6 py-4">Tipo</th>
                        <th class="px-6 py-4 text-right">Valor</th>
                        <th class="px-6 py-4 text-center">Escopo</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Validade</th>
                        <th class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            {{-- Código --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100 uppercase tracking-wider">
                                    {{ $coupon->code }}
                                </span>
                                @if($coupon->name)
                                    <span class="block text-xs text-slate-400 mt-1 font-medium">{{ $coupon->name }}</span>
                                @endif
                            </td>

                            {{-- Tipo --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    @if($coupon->discount_type === 'percent')
                                        <i class="fas fa-percentage text-slate-400"></i>
                                        <span>Percentual</span>
                                    @else
                                        <i class="fas fa-tag text-slate-400"></i>
                                        <span>Valor Fixo</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Valor --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="font-bold text-slate-900 text-sm">
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
                                <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-50 text-slate-600 text-xs font-medium border border-slate-100">
                                    <i class="fas fa-{{ $scopeIcon }} text-[10px] opacity-60"></i>
                                    {{ $scopeLabel }}
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($coupon->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Ativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 text-slate-500 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                        Inativo
                                    </span>
                                @endif
                            </td>

                            {{-- Validade --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 leading-tight">
                                @if($coupon->starts_at || $coupon->ends_at)
                                    @if($coupon->starts_at)
                                        <div class="flex items-center gap-1">
                                            <span class="w-2.5 text-center text-[10px] font-bold text-slate-300">S</span>
                                            {{ $coupon->starts_at->format('d/m/Y') }}
                                        </div>
                                    @endif
                                    @if($coupon->ends_at)
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="w-2.5 text-center text-[10px] font-bold text-slate-300">E</span>
                                            {{ $coupon->ends_at->format('d/m/Y') }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-300">Sem expiração</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('panel.admin.coupons.edit', $coupon) }}" 
                                       class="p-2 hover:bg-slate-100 text-slate-500 hover:text-blue-600 rounded-lg transition-colors border border-transparent" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('panel.admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja remover este cupom?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 hover:bg-red-50 text-slate-400 hover:text-red-500 rounded-lg transition-colors border border-transparent" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <i class="fas fa-ticket-alt fa-3x mb-3 text-slate-200"></i>
                                <p>Nenhum cupom cadastrado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($coupons->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
