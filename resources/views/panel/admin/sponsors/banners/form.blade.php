@extends('panel.layouts.app')

@section('title', $banner->exists ? 'Editar Banner Patrocinado' : 'Novo Banner Patrocinado')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">{{ $banner->exists ? 'Editar banner patrocinado' : 'Novo banner patrocinado' }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Cadastre criativos por area do portal.</p>
            </div>
            <a href="{{ route('panel.admin.sponsor-banners.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-300">Voltar</a>
        </div>
        <form method="POST" enctype="multipart/form-data" action="{{ $banner->exists ? route('panel.admin.sponsor-banners.update', $banner) : route('panel.admin.sponsor-banners.store') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @csrf
            @if($banner->exists) @method('PUT') @endif
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-semibold">Patrocinador</label><select name="sponsor_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required><option value="">Selecione</option>@foreach($sponsors as $sponsor)<option value="{{ $sponsor->id }}" @selected(old('sponsor_id', $banner->sponsor_id) == $sponsor->id)>{{ $sponsor->company?->name ?: ('Patrocinador #' . $sponsor->id) }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-sm font-semibold">Titulo</label><input type="text" name="title" value="{{ old('title', $banner->title) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required></div>
                <div><label class="mb-2 block text-sm font-semibold">Posicao</label><select name="position" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required>@foreach(['home_top','home_middle','home_bottom','event_sidebar','event_footer','marketplace_top','course_top','member_dashboard'] as $position)<option value="{{ $position }}" @selected(old('position', $banner->position) === $position)>{{ $position }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-sm font-semibold">URL</label><input type="url" name="url" value="{{ old('url', $banner->url) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">Inicio</label><input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($banner->starts_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">Fim</label><input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($banner->ends_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div class="md:col-span-2"><label class="mb-2 block text-sm font-semibold">Imagem</label><input type="file" name="image" class="w-full rounded-2xl border border-dashed border-slate-300 px-4 py-3 dark:border-slate-700"></div>
                <div class="flex items-center gap-3"><input type="checkbox" name="active" value="1" @checked(old('active', $banner->active ?? true))><span class="text-sm font-semibold">Banner ativo</span></div>
            </div>
            <div class="mt-6 flex justify-end"><button type="submit" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Salvar banner</button></div>
        </form>
    </div>
@endsection
