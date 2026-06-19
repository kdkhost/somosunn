@extends('admin.layouts.app')

@section('title', 'Produtos da loja - Marketplace')
@section('page_title', 'Produtos da loja')

@section('content')
    @php
        $isSuperAdmin = auth()->user()?->isSuperAdmin();
        $totalProducts = method_exists($products, 'total') ? $products->total() : count($products);
        $publishedCount = 0;
        $draftCount = 0;
        foreach ($products as $p) {
            if ($p->status === 'published') $publishedCount++;
            else $draftCount++;
        }
    @endphp

    {{-- KPI Cards --}}
    <div class="row mb-3">
        <div class="col-lg-4 col-md-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-box-open"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Total</span>
                    <span class="info-box-number">{{ $totalProducts }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Publicados</span>
                    <span class="info-box-number">{{ $publishedCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-warning elevation-1"><i class="fas fa-pen"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Rascunhos</span>
                    <span class="info-box-number">{{ $draftCount }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-box-open mr-2 text-primary"></i>Produtos da minha loja
            </h3>
            <div class="card-tools d-flex flex-wrap" style="gap:6px;">
                <a href="{{ route('admin.marketplace.store.edit') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-store mr-1"></i> {{ $isSuperAdmin ? 'Loja' : 'Minha loja' }}
                </a>
                <a href="{{ route('admin.marketplace.products.create') }}" class="btn btn-sm btn-primary rounded-pill px-3 elevation-1">
                    <i class="fas fa-plus mr-1"></i> Novo produto
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            @if(count($products) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th class="border-0 pl-3" style="width:60px;">Foto</th>
                                <th class="border-0">Produto</th>
                                <th class="border-0 text-right">Preço</th>
                                <th class="border-0 text-center">Canal</th>
                                <th class="border-0 text-center">Status</th>
                                <th class="border-0 text-center">Vendas</th>
                                <th class="border-0 text-center" style="width:100px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                @php
                                    $statusBadge = $product->status === 'published' ? 'badge-success' : 'badge-warning';
                                    $statusIcon = $product->status === 'published' ? 'fa-check-circle' : 'fa-pen';
                                    $statusLabel = $product->status === 'published' ? 'Publicado' : 'Rascunho';
                                @endphp
                                <tr>
                                    <td class="pl-3">
                                        @if($product->cover_url)
                                            <img src="{{ $product->cover_url }}" alt="{{ $product->title }}"
                                                class="rounded border" style="width:44px; height:44px; object-fit:cover;">
                                        @else
                                            <div class="rounded border bg-light d-flex align-items-center justify-content-center"
                                                style="width:44px; height:44px;">
                                                <i class="fas fa-image text-muted" style="font-size:14px;"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-sm">{{ $product->title }}</div>
                                        <div class="text-muted" style="font-size:11px;">
                                            {{ ucfirst($product->type) }}
                                            @if($product->excerpt)
                                                — {{ \Illuminate\Support\Str::limit($product->excerpt, 50) }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <span class="font-weight-bold">R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-wrap justify-content-center" style="gap:3px;">
                                            @if($product->supportsInternalCheckout())
                                                <span class="badge badge-primary" style="font-size:9px;" title="Loja virtual"><i class="fas fa-shopping-cart"></i></span>
                                            @endif
                                            @if($product->supportsPointsRedemption())
                                                <span class="badge badge-warning" style="font-size:9px;" title="Pontos"><i class="fas fa-coins"></i></span>
                                            @endif
                                            @if($product->supportsExternalCheckout())
                                                <span class="badge badge-secondary" style="font-size:9px;" title="Externo"><i class="fas fa-external-link-alt"></i></span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $statusBadge }}" style="font-size:10px;">
                                            <i class="fas {{ $statusIcon }} mr-1"></i>{{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold">{{ (int) ($product->sales_count ?? 0) }}</span>
                                            <small class="text-muted">{{ (int) ($product->buyers_count ?? 0) }} clientes</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex" style="gap:4px;">
                                            @if($store->slug)
                                                <a href="{{ route('seller-stores.products.show', [$store->slug, $product->slug]) }}" target="_blank"
                                                    class="btn btn-xs btn-outline-secondary rounded-pill" title="Ver"><i class="fas fa-eye"></i></a>
                                            @endif
                                            <a href="{{ route('admin.marketplace.products.edit', $product) }}"
                                                class="btn btn-xs btn-outline-primary rounded-pill" title="Editar"><i class="fas fa-pen"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="font-weight-bold text-muted">Nenhum produto cadastrado</h5>
                    <p class="text-muted mb-3">Comece criando o primeiro produto da sua loja.</p>
                    <a href="{{ route('admin.marketplace.products.create') }}" class="btn btn-primary rounded-pill elevation-1">
                        <i class="fas fa-plus mr-1"></i> Criar primeiro produto
                    </a>
                </div>
            @endif
        </div>

        @if(method_exists($products, 'hasPages') && $products->hasPages())
            <div class="card-footer d-flex justify-content-center border-top">
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .align-middle td { vertical-align: middle !important; }
</style>
@endpush
