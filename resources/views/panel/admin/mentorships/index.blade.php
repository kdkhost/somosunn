@extends('panel.layouts.app')

@section('title', 'Gestao de Mentorias')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.mentorships.index') }}" class="hover:underline">Mentorias</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Mentorias</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Crie e gerencie seus programas de mentoria, vagas e vendas.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.mentorships.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-200 transition-all">
                    <i class="fas fa-plus"></i>
                    <span>Nova Mentoria</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center transition-colors">
                        <i class="fas fa-chalkboard-teacher text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 transition-colors">Total de mentorias</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white transition-colors">{{ $mentorships->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center transition-colors">
                        <i class="fas fa-shopping-cart text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 transition-colors">Vendas na pagina</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white transition-colors">{{ $mentorships->getCollection()->sum(fn ($item) => (int) ($item->sales_count ?? 0)) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center transition-colors">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 transition-colors">Compradores na pagina</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white transition-colors">{{ $mentorships->getCollection()->sum(fn ($item) => (int) ($item->buyers_count ?? 0)) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 transition-colors">
                <form action="{{ route('panel.admin.mentorships.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative group">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors"></i>
                        <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por titulo ou descricao..."
                            class="w-full pl-11 pr-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-medium">
                    </div>
                    <button type="submit"
                        class="px-6 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                        Filtrar
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-950 transition-colors">
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800">Mentoria</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800">Mentor</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800">Tipo / Vagas</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800">Preco</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800">Vendas</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($mentorships as $mentorship)
                            <tr class="group hover:bg-slate-50/70 dark:hover:bg-slate-800/70 transition-all">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        @if($mentorship->image)
                                            <img src="{{ $mentorship->image_url }}" class="w-12 h-12 rounded-lg object-cover border border-slate-100 dark:border-slate-800 shadow-sm transition-colors">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-600 transition-colors">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $mentorship->title }}</p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 uppercase tracking-wider transition-colors">ID: #{{ $mentorship->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0 transition-colors">
                                            @if($mentorship->mentor && $mentorship->mentor->profile_photo_url && !str_contains($mentorship->mentor->profile_photo_url, 'default-user.svg'))
                                                <img src="{{ $mentorship->mentor->profile_photo_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-user text-slate-400 dark:text-slate-500 text-[10px]"></i>
                                            @endif
                                        </div>
                                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 transition-colors">{{ $mentorship->mentor->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $mentorship->type == 'online' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50' }} transition-colors">
                                            {{ $mentorship->type }}
                                        </span>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 font-medium transition-colors">{{ $mentorship->slots ?? '∞' }} vagas</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-0.5">
                                        <p class="text-sm font-bold text-slate-900 dark:text-white transition-colors">R$ {{ number_format($mentorship->price, 2, ',', '.') }}</p>
                                        @if($mentorship->flash_sale_price)
                                            <p class="text-[10px] text-emerald-500 dark:text-emerald-400 font-bold uppercase tracking-tight flex items-center gap-1 transition-colors">
                                                <i class="fas fa-bolt"></i>
                                                Flash: R$ {{ number_format($mentorship->flash_sale_price, 2, ',', '.') }}
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-0.5">
                                        <p class="text-sm font-bold text-slate-900 dark:text-white transition-colors">{{ (int) ($mentorship->sales_count ?? 0) }} venda(s)</p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-tight transition-colors">{{ (int) ($mentorship->buyers_count ?? 0) }} comprador(es)</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('panel.admin.mentorships.edit', $mentorship) }}"
                                            class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-300 rounded-xl transition-all border border-transparent hover:border-blue-100 dark:hover:border-blue-800/50 text-slate-500 dark:text-slate-300"
                                            title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('panel.admin.mentorships.destroy', $mentorship) }}" method="POST"
                                            onsubmit="return confirmAction(event, 'Excluir mentoria?', 'Tem certeza que deseja excluir esta mentoria?')"
                                            class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-700 dark:hover:text-red-300 rounded-xl transition-all border border-transparent hover:border-red-100 dark:hover:border-red-800/50 text-slate-500 dark:text-slate-300"
                                                title="Excluir">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-950 flex items-center justify-center text-slate-300 dark:text-slate-700 mb-4 transition-colors">
                                            <i class="fas fa-chalkboard-teacher text-2xl"></i>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white transition-colors">Nenhuma mentoria encontrada</h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 transition-colors">Tente ajustar seus filtros ou crie uma nova mentoria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($mentorships->hasPages())
                <div class="p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 transition-colors duration-300">
                    {{ $mentorships->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
