@extends('layouts.app')

@section('title', 'Revistas digitais')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-purple-50 dark:from-slate-950 dark:via-slate-900 dark:to-purple-950/30 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Hero --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-black uppercase tracking-widest mb-4">
                <i class="fas fa-newspaper"></i> Banca digital
            </div>
            <h1 class="text-4xl sm:text-5xl font-black text-slate-900 dark:text-white">Revistas &amp; Manchetes</h1>
            <p class="mt-3 text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">Folheie edicoes com efeito de pagina real, som imersivo e leitura em tela cheia.</p>
            @if(!$hasNewsInterest && auth()->check())
                <div class="mt-5 inline-flex items-center gap-3 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200/70 dark:border-amber-500/30 px-4 py-3 text-sm text-amber-900 dark:text-amber-200">
                    <i class="fas fa-info-circle text-amber-500"></i>
                    <div>
                        Para ver todas as edicoes, marque <strong>Noticias</strong> como interesse no seu perfil.
                        <a href="{{ route('panel.profile.edit') }}" class="underline font-bold ml-1">Editar perfil</a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Filtros --}}
        <form method="GET" class="mb-8 flex flex-wrap gap-3 items-center justify-center">
            <div class="relative flex-1 min-w-[240px] max-w-md">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="q" value="{{ $q }}" placeholder="Buscar edicao..."
                    class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/90 text-sm focus:border-purple-500 focus:ring-purple-500">
            </div>
            @if($categories->count())
                <select name="category" onchange="this.form.submit()" class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/90 text-sm">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected($category === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            @endif
            <button class="px-6 py-3 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold shadow-lg shadow-purple-500/30 transition">Filtrar</button>
        </form>

        {{-- Grid --}}
        @if($magazines->count())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                @foreach($magazines as $m)
                    <a href="{{ route('magazines.show', $m->slug) }}" class="group relative rounded-2xl overflow-hidden bg-white dark:bg-slate-900 shadow-lg shadow-slate-200/40 dark:shadow-black/20 border border-slate-100 dark:border-slate-800 hover:-translate-y-1 hover:shadow-2xl hover:shadow-purple-500/20 transition-all duration-300">
                        <div class="relative aspect-[3/4] overflow-hidden bg-gradient-to-br from-purple-100 to-indigo-100 dark:from-purple-900/40 dark:to-indigo-900/40">
                            @if($m->thumbnail_url)
                                <img src="{{ $m->thumbnail_url }}" alt="{{ $m->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-6xl text-purple-300 dark:text-purple-700"><i class="fas fa-book-open"></i></div>
                            @endif
                            @if($m->is_featured)
                                <div class="absolute top-3 right-3 px-2 py-1 rounded-lg bg-amber-400 text-amber-900 text-[10px] font-black uppercase shadow"><i class="fas fa-star"></i> Destaque</div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/95 text-purple-700 text-xs font-black shadow"><i class="fas fa-book-open"></i> Abrir revista</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="text-[10px] uppercase tracking-widest font-black text-purple-600 dark:text-purple-400">{{ $m->category ?: 'Revista' }}</div>
                            <h3 class="mt-1 font-black text-sm text-slate-900 dark:text-white line-clamp-2">{{ $m->title }}</h3>
                            <div class="mt-2 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                                <span>{{ $m->edition }}</span>
                                @if($m->published_at)<span>{{ $m->published_at->format('M/Y') }}</span>@endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">{{ $magazines->links() }}</div>
        @else
            <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800">
                <i class="fas fa-book-open text-6xl text-slate-300 dark:text-slate-700 mb-4"></i>
                <h3 class="text-xl font-black text-slate-700 dark:text-slate-300">Nenhuma revista disponivel ainda</h3>
                <p class="text-sm text-slate-500 mt-2">Em breve novas edicoes serao publicadas aqui.</p>
            </div>
        @endif
    </div>
</div>
@endsection
