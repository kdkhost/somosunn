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
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-primary"><i class="fas fa-box-open"></i></span>
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
                <span class="info-box-icon bg-gradient-warning"><i class="fas fa-pen"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Rascunhos</span>
                    <span class="info-box-number">{{ $draftCount }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h3 class="card-title font-weight-bold"><i class="fas fa-box-open mr-2"></i>Produtos da minha loja</h3>
                <p class="text-muted mb-0 small mt-1">Cadastre produtos fisicos e digitais para vender na loja, trocar por pontos ou encaminhar para um site externo.</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-wrap" style="gap: 8px;">
                <a href="{{ route('admin.marketplace.store.edit') }}" class="btn btn-sm btn-outline-primary rounded-pill elevation-1">
                    <i class="fas fa-store mr-1"></i> {{ $isSuperAdmin ? 'Loja da plataforma' : 'Minha loja' }}
                </a>
                <a href="{{ route('admin.marketplace.products.create') }}" class="btn btn-sm btn-primary rounded-pill elevation-1">
                    <i class="fas fa-plus mr-1"></i> Novo produto
                </a>
            </div>
        </div>
    </div>

    {{-- Product grid --}}
    <div class="row">
        @forelse($products as $product)
            <div class="col-md-6 col-xl-4 d-flex">
                <div class="card card-outline card-primary shadow-sm flex-fill">
                    <div class="card-body d-flex flex-column">
                        <div class="rounded border bg-light d-flex align-items-center justify-content-center overflow-hidden mb-3" style="aspect-ratio: 16 / 10; min-height: 180px;">
                            @if($product->cover_url)
                                <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="w-100 h-100" style="object-fit: cover;">
                            @else
                                <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="font-weight-bold mb-1">{{ $product->title }}</h5>
                                <div class="text-muted small">{{ ucfirst($product->type) }} - {{ $product->salesChannelLabel() }}</div>
                            </div>
                            @php
                                $statusBadge = $product->status === 'published' ? 'badge-success' : 'badge-warning';
                                $statusIcon = $product->status === 'published' ? 'fa-check-circle' : 'fa-pen';
                            @endphp
                            <span class="badge {{ $statusBadge }}">
                                <i class="fas {{ $statusIcon }} mr-1"></i>{{ $product->status === 'published' ? 'Publicado' : 'Rascunho' }}
                            </span>
                        </div>

                        <p class="text-muted mt-3 mb-3">{{ $product->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 110) }}</p>

                        <div class="mb-3 d-flex flex-wrap" style="gap: 6px;">
                            @if($product->supportsInternalCheckout())
                                <span class="badge badge-primary"><i class="fas fa-shopping-cart mr-1"></i>Loja virtual</span>
                            @endif
                            @if($product->supportsPointsRedemption())
                                <span class="badge badge-warning"><i class="fas fa-coins mr-1"></i>{{ number_format((int) optional($product->redeemableItem)->points_cost, 0, ',', '.') ?: '0' }} UNNBIT</span>
                            @endif
                            @if($product->supportsExternalCheckout())
                                <span class="badge badge-secondary"><i class="fas fa-external-link-alt mr-1"></i>Site externo</span>
                            @endif
                        </div>

                        <div class="mt-auto d-flex justify-content-between align-items-end pt-3 border-top">
                            <div>
                                <div class="text-muted small text-uppercase font-weight-bold">Preco</div>
                                <div class="h5 mb-0 font-weight-bold">R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}</div>
                            </div>
                            <div class="btn-group">
                                @if($store->slug)
                                    <a href="{{ route('seller-stores.products.show', [$store->slug, $product->slug]) }}" target="_blank" class="btn btn-outline-secondary rounded-pill elevation-1" title="Ver produto">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.marketplace.products.edit', $product) }}" class="btn btn-primary rounded-pill elevation-1" title="Editar produto">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-box-open text-muted mb-3" style="font-size: 3rem;"></i>
                        <h5 class="font-weight-bold text-muted">Nenhum produto cadastrado</h5>
                        <p class="text-muted mb-3">Comece criando o primeiro produto fisico ou digital da sua loja.</p>
                        <a href="{{ route('admin.marketplace.products.create') }}" class="btn btn-primary rounded-pill elevation-1">
                            <i class="fas fa-plus mr-1"></i> Criar primeiro produto
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if(method_exists($products, 'links'))
        <div class="mt-3">{{ $products->links() }}</div>
    @endif
@endsection
