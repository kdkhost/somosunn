@extends('admin.layouts.app')

@section('title', 'Produtos do marketplace')
@section('page_title', 'Produtos do marketplace')

@section('content')
    @php
        $totalProducts = method_exists($products, 'total') ? $products->total() : count($products);
        $publishedCount = 0;
        $draftCount = 0;
        $blockedCount = 0;
        foreach ($products as $p) {
            match ($p->status) {
                'published' => $publishedCount++,
                'draft' => $draftCount++,
                'blocked' => $blockedCount++,
                default => null,
            };
        }
    @endphp

    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-primary"><i class="fas fa-boxes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de produtos</span>
                    <span class="info-box-number">{{ $totalProducts }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Publicados</span>
                    <span class="info-box-number">{{ $publishedCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-danger"><i class="fas fa-ban"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Bloqueados</span>
                    <span class="info-box-number">{{ $blockedCount }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main content --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold"><i class="fas fa-boxes-stacked mr-2"></i>Produtos das lojas</h3>
            <a href="{{ route('admin.marketplace.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill elevation-1">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Monitore o catalogo proprio dos vendedores e modere o status de publicacao.</p>

            {{-- Search bar --}}
            <form method="GET" class="mb-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Buscar por titulo, SKU ou loja">
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
                            <th>Produto</th>
                            <th>Loja</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th class="text-center">Vendas</th>
                            <th class="text-right">Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="font-weight-bold">{{ $product->title }}</td>
                                <td>{{ $product->store->brand_name ?? '-' }}</td>
                                <td><span class="badge badge-light border">{{ strtoupper($product->type) }}</span></td>
                                <td>
                                    @php
                                        $statusBadge = match ($product->status) {
                                            'published' => 'badge-success',
                                            'draft' => 'badge-warning',
                                            'blocked' => 'badge-danger',
                                            default => 'badge-secondary',
                                        };
                                        $statusIcon = match ($product->status) {
                                            'published' => 'fa-check-circle',
                                            'draft' => 'fa-pen',
                                            'blocked' => 'fa-ban',
                                            default => 'fa-circle',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadge }}"><i class="fas {{ $statusIcon }} mr-1"></i>{{ strtoupper($product->status) }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold">{{ (int) ($product->sales_count ?? 0) }}</span>
                                        <small class="text-muted">{{ (int) ($product->buyers_count ?? 0) }} clientes</small>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <form action="{{ route('admin.marketplace.catalog.toggle', $product) }}" method="POST" class="form-inline justify-content-end">
                                        @csrf
                                        <select name="status" class="form-control form-control-sm mr-2">
                                            @foreach(['draft' => 'Rascunho', 'published' => 'Publicado', 'blocked' => 'Bloqueado'] as $value => $label)
                                                <option value="{{ $value }}" {{ $product->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill elevation-1">Salvar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-box-open mb-3" style="font-size: 3rem;"></i>
                                        <h5 class="font-weight-bold">Nenhum produto encontrado</h5>
                                        <p class="mb-0">Tente ajustar os filtros de busca.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($products, 'links'))
            <div class="card-footer">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
