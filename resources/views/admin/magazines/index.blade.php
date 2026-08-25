@extends('admin.layouts.app')

@section('page_title','Revistas digitais')
@section('breadcrumb')<li class="breadcrumb-item active">Revistas</li>@endsection

@section('content')
<div class="row mb-3">
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-gradient-primary elevation-1">
            <span class="info-box-icon"><i class="fas fa-book-open"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total</span>
                <span class="info-box-number">{{ $magazines->total() }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-gradient-success elevation-1">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Publicadas</span>
                <span class="info-box-number">{{ \App\Models\Magazine::where('status','published')->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-gradient-warning elevation-1">
            <span class="info-box-icon"><i class="fas fa-pencil-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Rascunhos</span>
                <span class="info-box-number">{{ \App\Models\Magazine::where('status','draft')->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-gradient-info elevation-1">
            <span class="info-box-icon"><i class="fas fa-eye"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Visualizações</span>
                <span class="info-box-number">{{ number_format(\App\Models\Magazine::sum('views_count'), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-book-open mr-2"></i>Revistas digitais</h3>
        <a href="{{ route('admin.magazines.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nova Revista
        </a>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
        @endif

        <form method="GET" class="form-inline mb-3">
            <div class="input-group" style="width: 320px;">
                <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por título ou edição..." class="form-control">
                <div class="input-group-append">
                    <button class="btn btn-default"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width: 70px;">Capa</th>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th>Status</th>
                        <th>Visibilidade</th>
                        <th>Visualizações</th>
                        <th class="text-center">Destaque</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($magazines as $m)
                        <tr>
                            <td>
                                @if($m->thumbnail_url)
                                    <img src="{{ $m->thumbnail_url }}" alt=""
                                        style="width: 46px; height: 62px; object-fit: cover; border-radius: 4px;"
                                        class="shadow-sm">
                                @else
                                    <div class="d-flex align-items-center justify-content-center text-muted"
                                        style="width:46px;height:62px;background:#f1f5f9;border-radius:4px;">
                                        <i class="fas fa-book"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">{{ $m->title }}</strong><br>
                                <small class="text-muted">
                                    {{ $m->edition }}
                                    @if($m->published_at) &middot; {{ $m->published_at->format('d/m/Y') }} @endif
                                </small>
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
                            <td>
                                @if($m->visibility === 'public')
                                    <span class="text-success"><i class="fas fa-globe mr-1"></i>Público</span>
                                @elseif($m->visibility === 'members')
                                    <span class="text-info"><i class="fas fa-users mr-1"></i>Membros</span>
                                @else
                                    <span class="text-purple"><i class="fas fa-newspaper mr-1"></i>Interessados</span>
                                @endif
                            </td>
                            <td>{{ number_format($m->views_count, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <button type="button"
                                    class="btn btn-sm js-toggle-magazine-featured {{ $m->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}"
                                    data-url="{{ route('admin.magazines.toggle-featured', $m) }}"
                                    data-featured="{{ $m->is_featured ? '1' : '0' }}"
                                    data-active-classes="btn-warning"
                                    data-inactive-classes="btn-outline-secondary"
                                    aria-pressed="{{ $m->is_featured ? 'true' : 'false' }}"
                                    title="{{ $m->is_featured ? 'Remover dos destaques' : 'Adicionar aos destaques' }}">
                                    <i class="{{ $m->is_featured ? 'fas' : 'far' }} fa-star" aria-hidden="true"></i>
                                    <span class="sr-only">Alterar destaque</span>
                                </button>
                            </td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('magazines.show', $m->slug) }}" target="_blank" class="btn btn-sm btn-default" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.magazines.edit', $m) }}" class="btn btn-sm btn-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.magazines.destroy', $m) }}" method="POST" class="d-inline js-confirm-magazine-delete">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Remover">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-book-open fa-3x mb-2 opacity-25 d-block"></i>
                                Nenhuma revista cadastrada ainda.<br>
                                <a href="{{ route('admin.magazines.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus mr-1"></i> Publicar a primeira edição
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $magazines->links() }}
    </div>
</div>
@endsection

@push('scripts')
    @include('admin.magazines._featured-toggle-script')
@endpush
