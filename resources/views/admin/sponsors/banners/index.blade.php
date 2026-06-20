@extends('admin.layouts.app')

@section('title', 'Banners Patrocinados')

@section('content')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">Banners patrocinados</h1>
            <a href="{{ route('admin.sponsor-banners.create') }}" class="btn btn-primary">Novo banner</a>
        </div>
    </div>
    <section class="content"><div class="container-fluid"><div class="card"><div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Titulo</th><th>Empresa</th><th>Posicao</th><th>Status</th><th class="text-right">Acoes</th></tr></thead>
            <tbody>
            @forelse($banners as $banner)
                <tr>
                    <td>{{ $banner->title }}</td>
                    <td>{{ $banner->sponsor?->company?->name ?: '-' }}</td>
                    <td>{{ $banner->position }}</td>
                    <td><span class="badge badge-{{ $banner->active ? 'success' : 'secondary' }}">{{ $banner->active ? 'Ativo' : 'Inativo' }}</span></td>
                    <td class="text-right"><a href="{{ route('admin.sponsor-banners.edit', $banner) }}" class="btn btn-sm btn-outline-primary">Editar</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Nenhum banner cadastrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div><div class="card-footer">{{ $banners->links() }}</div></div></div></section>
@endsection
