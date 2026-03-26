@extends('admin.layouts.app')

@section('title', 'Lojas do marketplace')
@section('page_title', 'Lojas do marketplace')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-store-alt mr-2"></i>Lojas do marketplace</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Modere a publicacao das lojas dos vendedores sem alterar os slugs reservados.</p>
            <form method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Buscar por marca, slug ou vendedor">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search mr-1"></i> Buscar</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Marca</th>
                            <th>Slug</th>
                            <th>Vendedor</th>
                            <th>Status</th>
                            <th class="text-right">Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stores as $store)
                            <tr>
                                <td class="font-weight-bold">{{ $store->brand_name }}</td>
                                <td>{{ $store->slug ?: '-' }}</td>
                                <td>{{ $store->user->name ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $store->is_blocked ? 'badge-danger' : ($store->is_published ? 'badge-success' : 'badge-warning') }}">
                                        {{ $store->is_blocked ? 'Bloqueada' : ($store->is_published ? 'Publicada' : 'Rascunho') }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <form action="{{ route('admin.marketplace.stores.toggle', $store) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <input type="hidden" name="is_blocked" value="{{ $store->is_blocked ? 0 : 1 }}">
                                        <button type="submit" class="btn btn-sm {{ $store->is_blocked ? 'btn-outline-success' : 'btn-outline-danger' }}">
                                            {{ $store->is_blocked ? 'Desbloquear' : 'Bloquear' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Nenhuma loja encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($stores, 'links'))
            <div class="card-footer">{{ $stores->links() }}</div>
        @endif
    </div>
@endsection
