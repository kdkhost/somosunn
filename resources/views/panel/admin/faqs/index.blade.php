@extends('panel.layouts.app')

@section('title', 'Perguntas Frequentes (FAQ)')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">FAQ</h1>
                <p class="text-sm text-slate-500 mt-1">Gerencie as perguntas frequentes exibidas no portal e área de
                    suporte.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.faqs.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-200 transition-all">
                    <i class="fas fa-plus"></i>
                    <span>Nova Pergunta</span>
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-200">
            <form action="{{ route('panel.admin.faqs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2 relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por pergunta ou resposta..."
                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-sm">
                </div>

                <div>
                    <select name="context"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-sm">
                        <option value="">Todos os Contrastes</option>
                        @foreach($contexts as $key => $label)
                            <option value="{{ $key }}" @selected($context === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-2xl transition-all">
                    Filtrar
                </button>
            </form>
        </div>

        {{-- FAQ List --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Ordem</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Pergunta</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Contexto</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($faqs as $faq)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="px-6 py-4 text-sm font-bold text-slate-400">
                                    #{{ $faq->sort_order }}
                                </td>
                                <td class="px-6 py-4 max-w-md">
                                    <p class="text-sm font-bold text-slate-900 line-clamp-1">{{ $faq->question }}</p>
                                    <p class="text-xs text-slate-400 line-clamp-1 mt-0.5">{{ strip_tags($faq->answer) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $faq->context === 'general' ? 'bg-blue-100 text-blue-700' : ($faq->context === 'premium' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $contexts[$faq->context] ?? $faq->context }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($faq->is_active)
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-1"></span>
                                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Ativo</span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-slate-300 inline-block mr-1"></span>
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Inativo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('panel.admin.faqs.edit', $faq) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-blue-600 hover:text-white transition-all">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <form action="{{ route('panel.admin.faqs.destroy', $faq) }}" method="POST"
                                            onsubmit="return confirm('Excluir esta pergunta?')">
                                            @csrf @method('DELETE')
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-400 hover:bg-red-500 hover:text-white transition-all">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">
                                    Nenhuma pergunta encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($faqs->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-200">
                    {{ $faqs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection