@extends('panel.layouts.app')

@section('title', 'Depoimentos / Testemunhos')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Depoimentos</h1>
                <p class="text-sm text-slate-500 mt-1">Modere os depoimentos enviados pelos usuários e destaque os melhores
                    no portal.</p>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm transition-colors">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center transition-colors">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div>
                        <p
                            class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider transition-colors">
                            Aguardando</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white transition-colors">
                            {{ \App\Models\Testimonial::where('status', 'pending')->count() }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm transition-colors">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center transition-colors">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div>
                        <p
                            class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider transition-colors">
                            Aprovados</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white transition-colors">
                            {{ \App\Models\Testimonial::where('status', 'approved')->count() }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm transition-colors">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center transition-colors">
                        <i class="fas fa-star text-xl"></i>
                    </div>
                    <div>
                        <p
                            class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider transition-colors">
                            Em Destaque</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white transition-colors">
                            {{ \App\Models\Testimonial::where('is_featured', true)->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-200">
            <form action="{{ route('panel.admin.testimonials.index') }}" method="GET"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2 relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por autor ou conteúdo..."
                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-sm">
                </div>

                <div>
                    <select name="status"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-sm">
                        <option value="">Todos os Status</option>
                        <option value="pending" @selected($status === 'pending')>Pendente</option>
                        <option value="approved" @selected($status === 'approved')>Aprovado</option>
                        <option value="rejected" @selected($status === 'rejected')>Recusado</option>
                    </select>
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-2xl transition-all">
                    Filtrar
                </button>
            </form>
        </div>

        {{-- Testimonials Table --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr
                            class="bg-slate-50/50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 transition-colors">
                            <th
                                class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Autor</th>
                            <th
                                class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Depoimento</th>
                            <th
                                class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Avaliação</th>
                            <th
                                class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-right transition-colors">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($testimonials as $test)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold overflow-hidden">
                                            @if($test->user && $test->user->profile_photo_url)
                                                <img src="{{ $test->user->profile_photo_url }}" class="w-full h-full object-cover">
                                            @else
                                                {{ substr($test->author_name ?: '?', 0, 1) }}
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">{{ $test->author_name }}</p>
                                            <p class="text-xs text-slate-400">{{ $test->author_title }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <p class="text-sm text-slate-600 line-clamp-2 italic">"{{ $test->content }}"</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex text-amber-400 text-[10px] gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="{{ $i <= $test->rating ? 'fas' : 'far' }} fa-star"></i>
                                        @endfor
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($test->status === 'pending')
                                        <span
                                            class="px-2 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold uppercase rounded-full">Pendente</span>
                                    @elseif($test->status === 'approved')
                                        <span
                                            class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase rounded-full">Aprovado</span>
                                    @else
                                        <span
                                            class="px-2 py-1 bg-red-100 text-red-700 text-[10px] font-bold uppercase rounded-full">Recusado</span>
                                    @endif

                                    @if($test->is_featured)
                                        <div class="mt-1 flex items-center gap-1 text-[10px] font-bold text-blue-600 uppercase">
                                            <i class="fas fa-star"></i>
                                            <span>Destaque</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($test->status === 'pending')
                                            <form action="{{ route('panel.admin.testimonials.approve', $test) }}" method="POST">
                                                @csrf
                                                <button
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm shadow-emerald-100"
                                                    title="Aprovar">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('panel.admin.testimonials.edit', $test) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-blue-600 hover:text-white transition-all">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>

                                        <form action="{{ route('panel.admin.testimonials.destroy', $test) }}" method="POST"
                                            onsubmit="return confirm('Excluir este depoimento?')">
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
                                    Nenhum depoimento encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($testimonials->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-200">
                    {{ $testimonials->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection