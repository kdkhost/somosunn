@extends('panel.layouts.app')

@section('title', 'Gerenciar Cursos')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.courses.index') }}" class="hover:underline">Cursos</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Cursos</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Gerencie seu catálogo de cursos e aulas.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Search --}}
                <form action="{{ route('panel.admin.courses.index') }}" method="GET" class="relative group">
                    <i
                        class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors"></i>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Buscar cursos..."
                        class="pl-10 pr-4 py-2 w-64 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-sm">
                </form>

                <a href="{{ route('panel.admin.courses.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-semibold text-white hover:bg-blue-700 transition-all shadow-sm shadow-blue-200">
                    <i class="fas fa-plus"></i>
                    <span>Novo Curso</span>
                </a>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50/50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold transition-colors">
                            <th class="px-6 py-4">Curso</th>
                            <th class="px-6 py-4">Instrutor</th>
                            <th class="px-6 py-4">Preço</th>
                            <th class="px-6 py-4">Aulas/Alunos</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($courses as $course)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-16 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0 transition-colors">
                                            @if($course->thumbnail)
                                                <img src="{{ asset($course->thumbnail) }}" alt="{{ $course->title }}"
                                                    class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-slate-50 dark:bg-slate-800 transition-colors">
                                                    <i class="fas fa-book text-slate-300 dark:text-slate-600"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="max-w-xs truncate font-medium text-slate-900 dark:text-white transition-colors" title="{{ $course->title }}">
                                            {{ $course->title }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @php $creatorName = $course->author_name ?: ($course->creator->name ?? 'N/A'); @endphp
                                        <div class="w-7 h-7 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0 transition-colors">
                                            @if($course->creator && $course->creator->profile_photo_url && !str_contains($course->creator->profile_photo_url, 'default-user.svg'))
                                                <img src="{{ $course->creator->profile_photo_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-user text-slate-400 dark:text-slate-500 text-[10px]"></i>
                                            @endif
                                        </div>
                                        <span class="text-sm text-slate-600 dark:text-slate-400 font-medium transition-colors">{{ $creatorName }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white transition-colors">
                                        R$ {{ number_format($course->price, 2, ',', '.') }}
                                    </div>
                                    @if($course->isFlashSaleActive())
                                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-tight transition-colors">
                                            Oferta Ativa: R$ {{ number_format($course->flash_sale_price, 2, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                            <i class="fas fa-play-circle text-blue-400"></i>
                                            <span>{{ $course->lessons_count }} aulas</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                            <i class="fas fa-users text-purple-400"></i>
                                            <span>{{ $course->enrollments_count }} alunos</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClasses = [
                                            'published' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/50',
                                            'draft' => 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                            'archived' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-100 dark:border-red-800/50',
                                            'paused' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border-amber-100 dark:border-amber-800/50',
                                        ];
                                        $statusLabels = [
                                            'published' => 'Publicado',
                                            'draft' => 'Rascunho',
                                            'archived' => 'Arquivado',
                                            'paused' => 'Pausado',
                                        ];
                                        $currentStatus = $course->status ?? 'draft';
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClasses[$currentStatus] ?? 'bg-slate-50 text-slate-600 border-slate-200' }} transition-colors">
                                        {{ $statusLabels[$currentStatus] ?? ucfirst($currentStatus) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div
                                        class="flex items-center justify-end gap-2 text-slate-400 dark:text-slate-500 transition-opacity">
                                        <a href="{{ route('panel.admin.courses.edit', $course) }}"
                                            class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition-colors border border-transparent hover:border-blue-100 dark:hover:border-blue-800/50"
                                            title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('panel.admin.courses.destroy', $course) }}" method="POST"
                                              onsubmit="return confirmAction(event, 'Excluir curso?', 'Tem certeza que deseja excluir este curso? Todas as aulas relacionadas serão removidas.');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-700 dark:hover:text-red-400 rounded-lg transition-colors border border-transparent hover:border-red-100 dark:hover:border-red-800/50 text-slate-400 dark:text-slate-500 hover:text-red-700 dark:hover:text-red-400"
                                                title="Excluir">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <div
                                        class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-graduation-cap text-slate-300 text-xl"></i>
                                    </div>
                                    <p class="text-sm">Nenhum curso encontrado.</p>
                                    <a href="{{ route('panel.admin.courses.create') }}"
                                        class="text-blue-600 hover:underline mt-2 inline-block">Criar seu primeiro curso</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($courses->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 transition-colors duration-300">
                    {{ $courses->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection