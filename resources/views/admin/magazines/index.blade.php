@php
    $isPanel = request()->routeIs('panel.*');
    $layout = $isPanel ? 'panel.layouts.app' : 'admin.layouts.app';
    $createRoute = $isPanel ? 'panel.admin.magazines.create' : 'admin.magazines.create';
    $editRoute = $isPanel ? 'panel.admin.magazines.edit' : 'admin.magazines.edit';
    $destroyRoute = $isPanel ? 'panel.admin.magazines.destroy' : 'admin.magazines.destroy';
    $indexRoute = $isPanel ? 'panel.admin.magazines.index' : 'admin.magazines.index';
@endphp
@extends($layout)

@section('title', 'Revistas')
@section('page_title','Revistas')
@section('breadcrumb')<li class="breadcrumb-item active">Revistas</li>@endsection

@section('content')
@if($isPanel)
<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow border border-slate-100 dark:border-slate-800 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                    <i class="fas fa-book-open text-purple-500"></i>
                    Revistas digitais
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Gerencie revistas, manchetes e edicoes em PDF com visualizacao tipo flipbook.</p>
            </div>
            <a href="{{ route($createRoute) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold shadow-sm transition">
                <i class="fas fa-plus"></i> Nova Revista
            </a>
        </div>

        <form method="GET" class="mb-4 flex gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por titulo ou edicao..."
                class="flex-1 rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-purple-500">
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-3">Capa</th>
                        <th class="px-4 py-3">Titulo</th>
                        <th class="px-4 py-3">Categoria</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Visibilidade</th>
                        <th class="px-4 py-3">Visualizacoes</th>
                        <th class="px-4 py-3 text-right">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($magazines as $m)
                        <tr>
                            <td class="px-4 py-3">
                                @if($m->thumbnail_url)
                                    <img src="{{ $m->thumbnail_url }}" alt="" class="w-12 h-16 object-cover rounded-md shadow-sm">
                                @else
                                    <div class="w-12 h-16 rounded-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                        <i class="fas fa-book"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $m->title }}</div>
                                <div class="text-xs text-slate-500">{{ $m->edition }} @if($m->published_at) &middot; {{ $m->published_at->format('d/m/Y') }} @endif</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $m->category ?: '-' }}</td>
                            <td class="px-4 py-3">
                                @if($m->status === 'published')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-green-100 text-green-700">Publicada</span>
                                @elseif($m->status === 'draft')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-yellow-100 text-yellow-700">Rascunho</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-700">Arquivada</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">
                                @if($m->visibility === 'public')
                                    <i class="fas fa-globe text-green-500 mr-1"></i> Publico
                                @elseif($m->visibility === 'members')
                                    <i class="fas fa-users text-blue-500 mr-1"></i> Membros
                                @else
                                    <i class="fas fa-newspaper text-purple-500 mr-1"></i> Interesse "Noticias"
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ number_format($m->views_count, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('magazines.show', $m->slug) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route($editRoute, $m) }}" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-bold" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route($destroyRoute, $m) }}" method="POST" class="inline" onsubmit="return confirm('Remover esta revista?')">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 text-xs font-bold">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                <i class="fas fa-book-open text-4xl mb-3 block opacity-30"></i>
                                Nenhuma revista cadastrada ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $magazines->links() }}</div>
    </div>
</div>
@else
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-book-open mr-2 text-purple"></i>Revistas digitais</h5>
            <a href="{{ route($createRoute) }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Nova Revista</a>
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline mb-3">
                <input type="text" name="q" value="{{ $q }}" placeholder="Buscar..." class="form-control mr-2">
                <button class="btn btn-default"><i class="fas fa-search"></i></button>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Capa</th>
                            <th>Titulo</th>
                            <th>Categoria</th>
                            <th>Status</th>
                            <th>Visibilidade</th>
                            <th>Views</th>
                            <th class="text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($magazines as $m)
                            <tr>
                                <td>
                                    @if($m->thumbnail_url)
                                        <img src="{{ $m->thumbnail_url }}" style="width:40px;height:54px;object-fit:cover;border-radius:4px;">
                                    @else
                                        <i class="fas fa-book text-muted"></i>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $m->title }}</strong><br>
                                    <small class="text-muted">{{ $m->edition }} @if($m->published_at) &middot; {{ $m->published_at->format('d/m/Y') }} @endif</small>
                                </td>
                                <td>{{ $m->category ?: '-' }}</td>
                                <td>
                                    @if($m->status === 'published')
                                        <span class="badge badge-success">Publicada</span>
                                    @elseif($m->status === 'draft')
                                        <span class="badge badge-warning">Rascunho</span>
                                    @else
                                        <span class="badge badge-secondary">Arquivada</span>
                                    @endif
                                </td>
                                <td>{{ $m->visibility }}</td>
                                <td>{{ number_format($m->views_count, 0, ',', '.') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('magazines.show', $m->slug) }}" target="_blank" class="btn btn-sm btn-default"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route($editRoute, $m) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route($destroyRoute, $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Remover?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma revista cadastrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $magazines->links() }}
        </div>
    </div>
@endif
@endsection
