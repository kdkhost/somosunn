@extends('admin.layouts.app')

@section('page_title', 'Páginas')
@section('breadcrumb')
    <li class="breadcrumb-item active">Páginas do Site</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="m-0">Páginas do Site</h3>
                <small class="text-muted">Edite os textos principais de cada página.</small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Slug</th>
                        <th>Título</th>
                        <th>Última atualização</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                        <tr>
                            <td>
                                <code>{{ $page->slug }}</code>
                            </td>
                            <td>{{ $page->title ?? '—' }}</td>
                            <td>{{ $page->updated_at ? $page->updated_at->format('d/m/Y H:i') : '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Nenhuma página cadastrada.<br>
                                <small>Execute <code>php artisan db:seed --class=PageSeeder</code> para criar as páginas padrão.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
