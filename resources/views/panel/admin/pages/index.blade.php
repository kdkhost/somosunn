@extends('panel.layouts.app')

@section('title', 'Gestão de Páginas')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.pages.index') }}"
        class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Páginas</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Gestão de Páginas</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Gerencie o conteúdo e a visibilidade das seções do seu
                    site.</p>
            </div>
        </div>

        @if(session('success'))
            <div
                class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-2xl flex items-center gap-3">
                <i class="fas fa-check-circle"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($pages as $page)
                <div
                    class="group bg-white dark:bg-slate-900 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-300">
                    <div class="flex items-start justify-between mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-file-alt text-xl"></i>
                        </div>
                        <span
                            class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider rounded-full">
                            {{ $page->slug }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ $page->title }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 line-clamp-2">
                        {{ $page->data['seo_description'] ?? 'Sem descrição definida para esta página.' }}
                    </p>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-50 dark:border-slate-800/50">
                        <div class="flex -space-x-2">
                            @php
                                $sectionCount = 0;
                                if (isset($page->data)) {
                                    foreach ($page->data as $key => $val) {
                                        if (str_ends_with($key, '_enabled'))
                                            $sectionCount++;
                                    }
                                }
                            @endphp
                            <span class="text-xs font-medium text-slate-400 dark:text-slate-500">
                                {{ $sectionCount }} seções configuráveis
                            </span>
                        </div>

                        <a href="{{ route('panel.admin.pages.edit', $page) }}"
                            class="inline-flex items-center gap-2 text-sm font-bold text-blue-600 dark:text-blue-400 hover:gap-3 transition-all">
                            Editar
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection