@extends('panel.layouts.app')

@section('title', 'Meus Cursos')

@section('panel_breadcrumb')
    <div class="flex items-center gap-2">
        <i class="fas fa-graduation-cap text-slate-400"></i>
        <span>Meus Cursos</span>
    </div>
@endsection

@section('panel_content')
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors">Meus Cursos
                </h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">Gerencie seus treinamentos e materiais
                    de estudo.</p>
            </div>
            <a href="{{ route('panel.courses.create') }}"
                class="inline-flex items-center justify-center rounded-full bg-blue-600 text-white px-5 py-2.5 text-sm font-bold hover:brightness-110 transition-all shadow-lg shadow-blue-500/20">
                <i class="fas fa-plus mr-2"></i> Novo Curso
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        @forelse($courses as $course)
            <div
                class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col hover:shadow-md transition-all duration-300 group">
                <div class="flex items-center gap-4 mb-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 transition-colors">
                        <i class="fas fa-graduation-cap text-xl"></i>
                    </div>
                    <span class="font-bold text-lg text-slate-900 dark:text-white transition-colors">{{ $course->title }}</span>
                </div>
                <div class="text-slate-600 dark:text-slate-400 mb-4 line-clamp-2 transition-colors">
                    {{ $course->short_description }}
                </div>
                <div class="flex-1"></div>
                <div
                    class="flex items-center justify-between mt-4 pt-4 border-t border-slate-50 dark:border-slate-800 transition-colors">
                    <div class="flex gap-4">
                        <a href="{{ route('panel.courses.edit', $course) }}"
                            class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                        <form action="{{ route('panel.courses.destroy', $course) }}" method="POST"
                            onsubmit="return confirmAction(event, 'Excluir curso?', 'Tem certeza que deseja excluir este curso?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-sm font-bold text-rose-600 dark:text-rose-400 hover:underline">Excluir</button>
                        </form>
                    </div>
                    <i
                        class="fas fa-arrow-right text-slate-300 dark:text-slate-700 transition-colors group-hover:translate-x-1 duration-300"></i>
                </div>
            </div>
        @empty
            <div class="col-span-2 py-12 text-center text-slate-400 dark:text-slate-600 transition-colors">
                <i class="fas fa-inbox text-5xl mb-4 opacity-20"></i>
                <p class="font-medium text-lg">Nenhum curso encontrado.</p>
            </div>
        @endforelse
    </div>
@endsection