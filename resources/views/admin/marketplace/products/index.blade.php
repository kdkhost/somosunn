@extends('admin.layouts.app')

@section('title', 'Produtos da loja - Marketplace')
@section('page_title', 'Produtos da loja')

@section('content')
    @php($isSuperAdmin = auth()->user()?->isSuperAdmin())

    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 font-weight-bold">Produtos da minha loja</h4>
                <p class="text-muted mb-0">Cadastre produtos fisicos e digitais para vender na loja, trocar por pontos ou encaminhar para um site externo.</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-wrap" style="gap: 8px;">
                <a href="{{ route('admin.marketplace.store.edit') }}" class="btn btn-outline-primary">
                    <i class="fas fa-store mr-1"></i> {{ $isSuperAdmin ? 'Loja da plataforma' : 'Minha loja' }}
                </a>
                <a href="{{ route('admin.marketplace.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Novo produto
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($products as $product)
            <div class="col-md-6 col-xl-4 d-flex">
                <div class="card card-outline card-primary flex-fill">
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
                            <span class="badge {{ $product->status === 'published' ? 'badge-success' : 'badge-warning' }}">
                                {{ $product->status === 'published' ? 'Publicado' : 'Rascunho' }}
                            </span>
                        </div>

                        <p class="text-muted mt-3 mb-3">{{ $product->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 110) }}</p>

                        <div class="mb-3 d-flex flex-wrap" style="gap: 6px;">
                            @if($product->supportsInternalCheckout())
                                <span class="badge badge-primary">Loja virtual</span>
                            @endif
                            @if($product->supportsPointsRedemption())
                                <span class="badge badge-warning">{{ number_format((int) optional($product->redeemableItem)->points_cost, 0, ',', '.') ?: '0' }} UNNBIT</span>
                            @endif
                            @if($product->supportsExternalCheckout())
                                <span class="badge badge-secondary">Site externo</span>
                            @endif
                        </div>

                        <div class="mt-auto d-flex justify-content-between align-items-end pt-3 border-top">
                            <div>
                                <div class="text-muted small text-uppercase font-weight-bold">Preco</div>
                                <div class="h5 mb-0 font-weight-bold">R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}</div>
                            </div>
                            <div class="btn-group">
                                @if($store->slug)
                                    <a href="{{ route('seller-stores.products.show', [$store->slug, $product->slug]) }}" target="_blank" class="btn btn-outline-secondary" title="Ver produto">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.marketplace.products.edit', $product) }}" class="btn btn-primary" title="Editar produto">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-outline card-secondary">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-box-open mb-3" style="font-size: 3rem;"></i>
                        <h5 class="font-weight-bold">Nenhum produto cadastrado</h5>
                        <p class="mb-0">Comece criando o primeiro produto fisico ou digital da sua loja.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if(method_exists($products, 'links'))
        <div class="mt-3">{{ $products->links() }}</div>
    @endif
@endsection
