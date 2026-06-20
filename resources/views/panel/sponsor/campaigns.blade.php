@extends('panel.layouts.app')

@section('title', 'Campanhas do Patrocinador')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Campanhas</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Banners e acoes publicitarias vinculadas ao patrocinio.</p>
        </div>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($sponsor->banners as $banner)
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ $banner->position }}</div>
                    <div class="mt-2 text-lg font-black text-slate-900 dark:text-white">{{ $banner->title }}</div>
                    <div class="mt-2 text-sm text-slate-500">{{ $banner->url ?: 'Sem URL vinculada' }}</div>
                    <div class="mt-4 text-xs font-semibold {{ $banner->active ? 'text-emerald-600' : 'text-slate-500' }}">{{ $banner->active ? 'Ativo' : 'Inativo' }}</div>
                </div>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-900">Nenhum banner vinculado ao patrocinio.</div>
            @endforelse
        </div>
    </div>
@endsection
