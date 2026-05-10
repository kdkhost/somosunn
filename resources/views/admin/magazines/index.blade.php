@extends('panel.layouts.app')

@section('title', 'Revistas')

@section('content')
<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-100 dark:border-slate-800 overflow-hidden">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-2xl">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-white">Revistas digitais</h1>
                        <p class="text-purple-100 text-sm">Manchetes, moda, noticias e edicoes em PDF com flipbook</p>
                    </div>
                </div>
                <a href="{{ route('panel.admin.magazines.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-purple-700 font-black text-sm shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    <i class="fas fa-plus"></i> Nova Revista
                </a>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            @if(session('success'))
                <div class="rounded-xl bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 text-sm mb-4">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            {{-- Busca --}}
            <form method="GET" class="mb-6 flex gap-2">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por titulo ou edicao..."
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 dark:text-white">
                </div>
                <button class="px-5 py-3 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 text-white text-sm font-bold">Buscar</button>
            </form>

            {{-- Tabela --}}
            <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-4 py-3">Capa</th>
                            <th class="px-4 py-3">Titulo</th>
                            <th class="px-4 py-3">Categoria</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Visibilidade</th>
                            <th class="px-4 py-3">Views</th>
                            <th class="px-4 py-3 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($magazines as $m)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-4 py-3">
                                    @if($m->thumbnail_url)
                                        <img src="{{ $m->thumbnail_url }}" alt="" class="w-12 h-16 object-cover rounded-md shadow">
                                    @else
                                        <div class="w-12 h-16 rounded-md bg-gradient-to-br from-purple-100 to-indigo-100 dark:from-purple-900/40 dark:to-indigo-900/40 flex items-center justify-center text-purple-400">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $m->title }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $m->edition }}
                                        @if($m->published_at) &middot; {{ $m->published_at->format('d/m/Y') }} @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $m->category ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($m->status === 'published')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">Publicada</span>
                                    @elseif($m->status === 'draft')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300">Rascunho</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300">Arquivada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                                    @if($m->visibility === 'public')
                                        <i class="fas fa-globe text-green-500 mr-1"></i> Publico
                                    @elseif($m->visibility === 'members')
                                        <i class="fas fa-users text-blue-500 mr-1"></i> Membros
                                    @else
                                        <i class="fas fa-newspaper text-purple-500 mr-1"></i> Interesse "Noticias"
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ number_format($m->views_count, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('magazines.show', $m->slug) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition" title="Visualizar">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('panel.admin.magazines.edit', $m) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 transition" title="Editar">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('panel.admin.magazines.destroy', $m) }}" method="POST" class="inline" onsubmit="return confirm('Remover esta revista?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 dark:bg-red-900/40 dark:text-red-300 transition" title="Remover">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-slate-400">
                                        <i class="fas fa-book-open text-5xl opacity-40"></i>
                                        <div class="text-base font-bold text-slate-600 dark:text-slate-400">Nenhuma revista cadastrada ainda</div>
                                        <div class="text-xs">Clique em "Nova Revista" para publicar a primeira edicao</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $magazines->links() }}</div>
        </div>
    </div>
</div>
@endsection
