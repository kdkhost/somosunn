@extends('admin.layouts.app')

@section('title', 'Lojas do marketplace')
@section('page_title', 'Lojas do marketplace')

@section('content')
    @php
        $totalStores = method_exists($stores, 'total') ? $stores->total() : count($stores);
        $publishedCount = 0;
        $blockedCount = 0;
        foreach ($stores as $s) {
            if ($s->is_blocked) $blockedCount++;
            elseif ($s->is_published) $publishedCount++;
        }
    @endphp

    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-primary"><i class="fas fa-store-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de lojas</span>
                    <span class="info-box-number">{{ $totalStores }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Publicadas</span>
                    <span class="info-box-number">{{ $publishedCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-danger"><i class="fas fa-ban"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Bloqueadas</span>
                    <span class="info-box-number">{{ $blockedCount }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main content --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold"><i class="fas fa-store-alt mr-2"></i>Lojas do marketplace</h3>
            <a href="{{ route('admin.marketplace.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill elevation-1">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Modere a publicacao das lojas dos vendedores sem alterar os slugs reservados.</p>

            {{-- Search bar --}}
            <form method="GET" class="mb-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Buscar por marca, slug ou vendedor">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary rounded-pill elevation-1"><i class="fas fa-search mr-1"></i> Buscar</button>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
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
                                <td><code>{{ $store->slug ?: '-' }}</code></td>
                                <td>
                                    <div class="font-weight-bold">{{ $store->user->name ?? '-' }}</div>
                                    @if($store->user->email ?? null)
                                        <div class="text-muted small">{{ $store->user->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusBadge = $store->is_blocked ? 'badge-danger' : ($store->is_published ? 'badge-success' : 'badge-warning');
                                        $statusIcon = $store->is_blocked ? 'fa-ban' : ($store->is_published ? 'fa-check-circle' : 'fa-pen');
                                        $statusLabel = $store->is_blocked ? 'Bloqueada' : ($store->is_published ? 'Publicada' : 'Rascunho');
                                    @endphp
                                    <span class="badge {{ $statusBadge }}"><i class="fas {{ $statusIcon }} mr-1"></i>{{ $statusLabel }}</span>
                                </td>
                                <td class="text-right">
                                    <form action="{{ route('admin.marketplace.stores.toggle', $store) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <input type="hidden" name="is_blocked" value="{{ $store->is_blocked ? 0 : 1 }}">
                                        <button type="submit" class="btn btn-sm {{ $store->is_blocked ? 'btn-outline-success' : 'btn-outline-danger' }} rounded-pill elevation-1">
                                            <i class="fas {{ $store->is_blocked ? 'fa-unlock' : 'fa-lock' }} mr-1"></i>{{ $store->is_blocked ? 'Desbloquear' : 'Bloquear' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-store-alt-slash mb-3" style="font-size: 3rem;"></i>
                                        <h5 class="font-weight-bold">Nenhuma loja encontrada</h5>
                                        <p class="mb-0">Quando vendedores criarem suas lojas, elas aparecerao aqui.</p>
                                    </div>
                                </td>
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
