@extends('admin.layouts.app')

@section('title', 'Catálogo de Produtos - Marketplace')
@section('page_title', 'Catálogo de Produtos')

@section('content')
    @php
        $stats = $stats ?? [
            'total' => method_exists($products, 'total') ? $products->total() : count($products),
            'published' => 0,
            'draft' => 0,
            'blocked' => 0,
        ];
        $currentStatus = $status ?? request('status', '');
        $currentType = $type ?? request('type', '');
        $currentSearch = $search ?? request('q', '');
    @endphp

    {{-- Cabeçalho de Ações e Navegação --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="badge badge-catalog-header px-3 py-2" style="font-size: 0.875rem;">
                <i class="fas fa-boxes-stacked mr-1.5 text-primary"></i> Moderação e Monitoramento
            </span>
            <span class="text-muted small">Gerencie produtos próprios e de lojas parceiras da plataforma</span>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('admin.marketplace.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-none btn-top-nav">
                <i class="fas fa-arrow-left mr-1"></i> Voltar ao Hub
            </a>
            <a href="{{ route('admin.marketplace.stores.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-none btn-top-nav">
                <i class="fas fa-store mr-1"></i> Lojas Parceiras
            </a>
            <a href="{{ route('marketplace.index') }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3 text-white shadow-none">
                <i class="fas fa-external-link-alt mr-1"></i> Ver no Site
            </a>
        </div>
    </div>

    {{-- Cards de Métricas e KPIs (100% Compatível com Light & Dark Mode) --}}
    <div class="row">
        {{-- Total Geral --}}
        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <a href="{{ route('admin.marketplace.catalog.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm kpi-card kpi-card-total transition-all hover-lift {{ $currentStatus === '' ? 'ring-active' : '' }}">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase kpi-title font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total de Produtos</span>
                            <h3 class="font-weight-bold mb-0 kpi-number kpi-number-total" id="kpi-total">{{ $stats['total'] }}</h3>
                            <small class="kpi-subtitle">No catálogo geral</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon kpi-icon-total" style="width: 48px; height: 48px;">
                            <i class="fas fa-boxes" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Publicados --}}
        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'published'])) }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm kpi-card kpi-card-published transition-all hover-lift {{ $currentStatus === 'published' ? 'ring-active' : '' }}">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase kpi-title font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Publicados e Ativos</span>
                            <h3 class="font-weight-bold mb-0 kpi-number kpi-number-published" id="kpi-published">{{ $stats['published'] }}</h3>
                            <small class="kpi-subtitle">Visíveis aos clientes</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon kpi-icon-published" style="width: 48px; height: 48px;">
                            <i class="fas fa-check-circle" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Rascunhos --}}
        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'draft'])) }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm kpi-card kpi-card-draft transition-all hover-lift {{ $currentStatus === 'draft' ? 'ring-active' : '' }}">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase kpi-title font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Em Rascunho</span>
                            <h3 class="font-weight-bold mb-0 kpi-number kpi-number-draft" id="kpi-draft">{{ $stats['draft'] }}</h3>
                            <small class="kpi-subtitle">Aguardando publicação</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon kpi-icon-draft" style="width: 48px; height: 48px;">
                            <i class="fas fa-pen-ruler" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Bloqueados --}}
        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'blocked'])) }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm kpi-card kpi-card-blocked transition-all hover-lift {{ $currentStatus === 'blocked' ? 'ring-active' : '' }}">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase kpi-title font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Bloqueados</span>
                            <h3 class="font-weight-bold mb-0 kpi-number kpi-number-blocked" id="kpi-blocked">{{ $stats['blocked'] }}</h3>
                            <small class="kpi-subtitle">Restritos por moderação</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon kpi-icon-blocked" style="width: 48px; height: 48px;">
                            <i class="fas fa-shield-halved" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Card Principal do Catálogo --}}
    <div class="card card-outline card-primary shadow-sm border-0 mb-4 main-catalog-card">
        {{-- Card Header com abas e filtros rápidos --}}
        <div class="card-header border-bottom py-3 catalog-card-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                {{-- Filtros Rápidos por Status (Pills estilo React) --}}
                <div class="d-flex flex-wrap align-items-center gap-1.5">
                    <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except(['status', 'page']))) }}" 
                       class="btn btn-sm rounded-pill px-3 filter-pill {{ $currentStatus === '' ? 'filter-pill-active-total' : '' }}">
                        Todos <span class="badge filter-pill-badge ml-1">{{ $stats['total'] }}</span>
                    </a>
                    <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'published'])) }}" 
                       class="btn btn-sm rounded-pill px-3 filter-pill {{ $currentStatus === 'published' ? 'filter-pill-active-published' : '' }}">
                        <i class="fas fa-check-circle mr-1"></i> Publicados <span class="badge filter-pill-badge ml-1">{{ $stats['published'] }}</span>
                    </a>
                    <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'draft'])) }}" 
                       class="btn btn-sm rounded-pill px-3 filter-pill {{ $currentStatus === 'draft' ? 'filter-pill-active-draft' : '' }}">
                        <i class="fas fa-pen-ruler mr-1"></i> Rascunhos <span class="badge filter-pill-badge ml-1">{{ $stats['draft'] }}</span>
                    </a>
                    <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'blocked'])) }}" 
                       class="btn btn-sm rounded-pill px-3 filter-pill {{ $currentStatus === 'blocked' ? 'filter-pill-active-blocked' : '' }}">
                        <i class="fas fa-ban mr-1"></i> Bloqueados <span class="badge filter-pill-badge ml-1">{{ $stats['blocked'] }}</span>
                    </a>
                </div>

                {{-- Filtro por Tipo e Reset --}}
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" class="d-flex align-items-center gap-2" id="filter-type-form">
                        @if($currentStatus !== '')
                            <input type="hidden" name="status" value="{{ $currentStatus }}">
                        @endif
                        @if($currentSearch !== '')
                            <input type="hidden" name="q" value="{{ $currentSearch }}">
                        @endif
                        <select name="type" class="custom-select custom-select-sm rounded-pill type-selector" style="height: 36px; font-size: 0.8125rem; min-width: 145px;" onchange="this.form.submit()">
                            <option value="">Todos os Tipos</option>
                            <option value="physical" {{ $currentType === 'physical' ? 'selected' : '' }}>Físico</option>
                            <option value="digital" {{ $currentType === 'digital' ? 'selected' : '' }}>Digital</option>
                            <option value="service" {{ $currentType === 'service' ? 'selected' : '' }}>Serviço</option>
                        </select>
                    </form>

                    @if($currentStatus !== '' || $currentType !== '' || $currentSearch !== '')
                        <a href="{{ route('admin.marketplace.catalog.index') }}" class="btn btn-sm btn-clear-filters rounded-pill px-2.5" title="Limpar todos os filtros">
                            <i class="fas fa-times-circle mr-1"></i> Limpar
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Barra de Busca Integrada --}}
        <div class="px-3 pt-3 pb-2 border-bottom catalog-search-bar">
            <form method="GET" class="row align-items-center g-2">
                @if($currentStatus !== '')
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                @if($currentType !== '')
                    <input type="hidden" name="type" value="{{ $currentType }}">
                @endif
                <div class="col-md-9 col-sm-8 mb-2 mb-sm-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text border-right-0" style="border-radius: 0.625rem 0 0 0.625rem;">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text" name="q" value="{{ $currentSearch }}" class="form-control border-left-0 pl-0" placeholder="Buscar por título do produto, código SKU, nome ou slug da loja..." style="border-radius: 0 0.625rem 0.625rem 0;">
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 w-100 text-white font-weight-600 shadow-none">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                    @if($currentSearch !== '')
                        <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except(['q', 'page']))) }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-none btn-top-nav" title="Limpar busca">
                            <i class="fas fa-eraser"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabela de Produtos --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="catalog-table">
                    <thead>
                        <tr class="text-uppercase text-muted" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                            <th style="min-width: 280px;">Produto</th>
                            <th style="min-width: 170px;">Loja / Vendedor</th>
                            <th style="min-width: 130px;">Tipo & Canal</th>
                            <th style="min-width: 130px;" class="text-center">Preço</th>
                            <th style="min-width: 120px;" class="text-center">Vendas</th>
                            <th style="min-width: 140px;">Status</th>
                            <th style="min-width: 190px;" class="text-right">Moderar Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($products as $product)
                            @php
                                $statusBadgeClass = match ($product->status) {
                                    'published' => 'badge-success-soft text-success',
                                    'draft' => 'badge-warning-soft text-warning',
                                    'blocked' => 'badge-danger-soft text-danger',
                                    default => 'badge-secondary-soft text-secondary',
                                };
                                $statusDotClass = match ($product->status) {
                                    'published' => 'bg-success status-pulse',
                                    'draft' => 'bg-warning',
                                    'blocked' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                                $statusLabel = match ($product->status) {
                                    'published' => 'Publicado',
                                    'draft' => 'Rascunho',
                                    'blocked' => 'Bloqueado',
                                    default => ucfirst($product->status),
                                };
                                $typeBadge = match ($product->type) {
                                    'physical' => ['label' => 'Físico', 'icon' => 'fa-box', 'class' => 'badge-info-soft text-info'],
                                    'digital' => ['label' => 'Digital', 'icon' => 'fa-cloud-arrow-down', 'class' => 'badge-purple-soft text-purple'],
                                    'service' => ['label' => 'Serviço', 'icon' => 'fa-handshake', 'class' => 'badge-teal-soft text-teal'],
                                    default => ['label' => strtoupper($product->type ?: 'PADRÃO'), 'icon' => 'fa-tag', 'class' => 'badge-secondary-soft text-muted'],
                                };
                                $hasDiscount = (float) $product->sale_price > 0 && (float) $product->sale_price < (float) $product->price;
                            @endphp
                            <tr id="product-row-{{ $product->id }}" class="product-row transition-all">
                                {{-- Produto / Foto / SKU --}}
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="product-thumbnail mr-3 position-relative flex-shrink-0">
                                            @if($product->cover_url)
                                                <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="rounded shadow-xs object-cover" style="width: 52px; height: 52px; object-fit: cover;">
                                            @else
                                                <div class="rounded d-flex align-items-center justify-content-center text-muted shadow-xs product-placeholder" style="width: 52px; height: 52px;">
                                                    <i class="fas fa-image" style="font-size: 1.25rem;"></i>
                                                </div>
                                            @endif
                                            @if($hasDiscount)
                                                <span class="badge badge-danger position-absolute" style="top: -6px; right: -6px; font-size: 0.6rem; padding: 2px 4px;">PROMO</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-weight-bold product-title-text text-truncate mb-0.5" style="font-size: 0.9rem;" title="{{ $product->title }}">
                                                {{ $product->title }}
                                            </div>
                                            <div class="d-flex align-items-center flex-wrap gap-1 small text-muted">
                                                @if($product->sku)
                                                    <span class="badge sku-tag px-1.5 py-0.5" style="font-size: 0.7rem; font-family: monospace;">SKU: {{ $product->sku }}</span>
                                                @endif
                                                @if($product->isPhysical())
                                                    <span class="text-xs {{ (int) $product->stock <= 5 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                                        <i class="fas fa-warehouse mr-0.5"></i> Estoque: {{ $product->stock ?? 0 }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Loja & Vendedor --}}
                                <td>
                                    @if($product->store)
                                        <div class="font-weight-600 store-name-text mb-0.5">
                                            <i class="fas fa-store text-primary mr-1"></i> {{ $product->store->brand_name }}
                                        </div>
                                    @else
                                        <span class="text-muted font-italic">Loja não vinculada</span>
                                    @endif
                                    @if($product->user)
                                        <div class="small text-muted text-truncate" title="{{ $product->user->email }}">
                                            <i class="fas fa-user mr-1 text-secondary"></i> {{ $product->user->name }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Tipo & Canal --}}
                                <td>
                                    <span class="badge {{ $typeBadge['class'] }} mb-1">
                                        <i class="fas {{ $typeBadge['icon'] }} mr-1"></i> {{ $typeBadge['label'] }}
                                    </span>
                                    <div class="small text-muted">
                                        {{ $product->salesChannelLabel() }}
                                    </div>
                                </td>

                                {{-- Preço --}}
                                <td class="text-center">
                                    @if($hasDiscount)
                                        <div class="text-muted small text-decoration-line-through">
                                            R$ {{ number_format((float) $product->price, 2, ',', '.') }}
                                        </div>
                                        <div class="font-weight-bold text-success price-value" style="font-size: 0.95rem;">
                                            R$ {{ number_format((float) $product->sale_price, 2, ',', '.') }}
                                        </div>
                                    @else
                                        <div class="font-weight-bold price-value" style="font-size: 0.95rem;">
                                            R$ {{ number_format((float) ($product->price ?: 0), 2, ',', '.') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Vendas & Clientes --}}
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="font-weight-bold sales-count-text" style="font-size: 0.95rem;">
                                            <i class="fas fa-bag-shopping text-primary mr-1"></i> {{ (int) ($product->sales_count ?? 0) }}
                                        </span>
                                        <small class="text-muted">{{ (int) ($product->buyers_count ?? 0) }} clientes</small>
                                    </div>
                                </td>

                                {{-- Status Atual --}}
                                <td>
                                    <div class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill {{ $statusBadgeClass }}" id="badge-status-{{ $product->id }}">
                                        <span class="rounded-circle mr-1.5 {{ $statusDotClass }}" style="width: 7px; height: 7px;"></span>
                                        <span class="font-weight-600 status-text" style="font-size: 0.78rem;">{{ $statusLabel }}</span>
                                    </div>
                                </td>

                                {{-- Ações e Moderação Instantânea (AJAX) --}}
                                <td class="text-right">
                                    <div class="d-flex align-items-center justify-content-end gap-1.5">
                                        <select class="custom-select custom-select-sm status-selector rounded-pill shadow-none" 
                                                data-product-id="{{ $product->id }}" 
                                                data-product-title="{{ $product->title }}"
                                                data-current-status="{{ $product->status }}"
                                                data-action-url="{{ route('admin.marketplace.catalog.toggle', $product) }}"
                                                style="width: 135px; height: 34px; font-size: 0.8125rem;">
                                            <option value="draft" {{ $product->status === 'draft' ? 'selected' : '' }}>Rascunho</option>
                                            <option value="published" {{ $product->status === 'published' ? 'selected' : '' }}>Publicado</option>
                                            <option value="blocked" {{ $product->status === 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                                        </select>
                                        <span class="spinner-border spinner-border-sm text-primary d-none" id="spinner-{{ $product->id }}" role="status" aria-hidden="true"></span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4 text-muted">
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center border mb-3 product-placeholder" style="width: 70px; height: 70px;">
                                            <i class="fas fa-boxes text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                        <h5 class="font-weight-bold empty-title mb-1">Nenhum produto encontrado</h5>
                                        <p class="text-muted small mb-3">Tente ajustar os filtros ou a palavra-chave pesquisada.</p>
                                        @if($currentStatus !== '' || $currentType !== '' || $currentSearch !== '')
                                            <a href="{{ route('admin.marketplace.catalog.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="fas fa-eraser mr-1"></i> Limpar filtros aplicados
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Rodapé com Paginação e Totalizador --}}
        @if(method_exists($products, 'links'))
            <div class="card-footer border-top py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 catalog-card-footer">
                <div class="text-muted small">
                    Exibindo <strong>{{ $products->firstItem() ?? 0 }}</strong> a <strong>{{ $products->lastItem() ?? 0 }}</strong> de <strong>{{ $products->total() }}</strong> produtos
                </div>
                <div>
                    {{ $products->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    /* Utilidades */
    .gap-1\.5 { gap: 0.375rem !important; }
    .gap-2 { gap: 0.5rem !important; }
    .gap-3 { gap: 0.75rem !important; }

    /* Header e Botões de Topo */
    .badge-catalog-header {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .dark-mode .badge-catalog-header {
        background-color: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    .dark-mode .btn-top-nav {
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }
    .dark-mode .btn-top-nav:hover {
        background-color: #334155 !important;
        color: #ffffff !important;
    }

    /* ─── Cards de Métricas e KPIs (Universal Light / Dark) ─── */
    .kpi-card {
        border-radius: 1rem !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
    }

    .kpi-card-total {
        background: linear-gradient(135deg, rgba(31, 94, 219, 0.04) 0%, #ffffff 100%) !important;
    }
    .kpi-card-published {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, #ffffff 100%) !important;
    }
    .kpi-card-draft {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.06) 0%, #ffffff 100%) !important;
    }
    .kpi-card-blocked {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, #ffffff 100%) !important;
    }

    .kpi-title {
        color: #64748b;
    }
    .kpi-subtitle {
        color: #94a3b8;
    }
    .kpi-number-total {
        color: #0f172a;
    }
    .kpi-number-published {
        color: #10b981;
    }
    .kpi-number-draft {
        color: #f59e0b;
    }
    .kpi-number-blocked {
        color: #ef4444;
    }

    .kpi-icon-total {
        background-color: rgba(31, 94, 219, 0.12);
        color: #1F5EDB;
    }
    .kpi-icon-published {
        background-color: rgba(16, 185, 129, 0.12);
        color: #10b981;
    }
    .kpi-icon-draft {
        background-color: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .kpi-icon-blocked {
        background-color: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }

    .ring-active {
        box-shadow: 0 0 0 2px #1F5EDB !important;
    }

    /* ─── DARK MODE: KPIs Harmonizados com o Painel ─── */
    .dark-mode .kpi-card {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    .dark-mode .kpi-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.45) !important;
    }

    .dark-mode .kpi-card-total {
        background: linear-gradient(135deg, rgba(31, 94, 219, 0.18) 0%, #1e293b 100%) !important;
    }
    .dark-mode .kpi-card-published {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.16) 0%, #1e293b 100%) !important;
    }
    .dark-mode .kpi-card-draft {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.16) 0%, #1e293b 100%) !important;
    }
    .dark-mode .kpi-card-blocked {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.16) 0%, #1e293b 100%) !important;
    }

    .dark-mode .kpi-title {
        color: #94a3b8 !important;
    }
    .dark-mode .kpi-subtitle {
        color: #64748b !important;
    }
    .dark-mode .kpi-number-total {
        color: #ffffff !important;
    }
    .dark-mode .kpi-number-published {
        color: #34d399 !important;
    }
    .dark-mode .kpi-number-draft {
        color: #fbbf24 !important;
    }
    .dark-mode .kpi-number-blocked {
        color: #f87171 !important;
    }

    .dark-mode .kpi-icon-total {
        background-color: rgba(31, 94, 219, 0.28) !important;
        color: #60a5fa !important;
    }
    .dark-mode .kpi-icon-published {
        background-color: rgba(16, 185, 129, 0.25) !important;
        color: #34d399 !important;
    }
    .dark-mode .kpi-icon-draft {
        background-color: rgba(245, 158, 11, 0.25) !important;
        color: #fbbf24 !important;
    }
    .dark-mode .kpi-icon-blocked {
        background-color: rgba(239, 68, 68, 0.25) !important;
        color: #f87171 !important;
    }

    /* ─── Pílulas de Filtro (Filter Pills) ─── */
    .filter-pill {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        transition: all 0.15s ease;
    }
    .filter-pill:hover {
        background-color: #e2e8f0;
        color: #1e293b;
    }
    .filter-pill-badge {
        background-color: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
    }

    .filter-pill-active-total {
        background-color: #1F5EDB !important;
        color: #ffffff !important;
        border-color: #1F5EDB !important;
        box-shadow: 0 2px 6px rgba(31, 94, 219, 0.28) !important;
    }
    .filter-pill-active-total .filter-pill-badge {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        border-color: transparent !important;
    }

    .filter-pill-active-published {
        background-color: #10b981 !important;
        color: #ffffff !important;
        border-color: #10b981 !important;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.28) !important;
    }
    .filter-pill-active-published .filter-pill-badge {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        border-color: transparent !important;
    }

    .filter-pill-active-draft {
        background-color: #f59e0b !important;
        color: #ffffff !important;
        border-color: #f59e0b !important;
        box-shadow: 0 2px 6px rgba(245, 158, 11, 0.28) !important;
    }
    .filter-pill-active-draft .filter-pill-badge {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        border-color: transparent !important;
    }

    .filter-pill-active-blocked {
        background-color: #ef4444 !important;
        color: #ffffff !important;
        border-color: #ef4444 !important;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.28) !important;
    }
    .filter-pill-active-blocked .filter-pill-badge {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        border-color: transparent !important;
    }

    /* Dark Mode Filter Pills */
    .dark-mode .filter-pill {
        background-color: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }
    .dark-mode .filter-pill:hover {
        background-color: #334155 !important;
        color: #ffffff !important;
    }
    .dark-mode .filter-pill-badge {
        background-color: #0f172a !important;
        color: #94a3b8 !important;
        border-color: #334155 !important;
    }

    /* Card Principal e Barras */
    .catalog-card-header {
        background-color: #ffffff;
    }
    .catalog-search-bar {
        background-color: #f8fafc;
    }
    .catalog-card-footer {
        background-color: #ffffff;
    }

    .dark-mode .catalog-card-header {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    .dark-mode .catalog-search-bar {
        background-color: #0f172a !important;
        border-color: #334155 !important;
    }
    .dark-mode .catalog-card-footer {
        background-color: #162032 !important;
        border-color: #334155 !important;
    }

    /* Botão limpar filtros */
    .btn-clear-filters {
        background-color: #f1f5f9;
        color: #ef4444;
        border: 1px solid #e2e8f0;
    }
    .dark-mode .btn-clear-filters {
        background-color: #1e293b !important;
        color: #fca5a5 !important;
        border-color: #334155 !important;
    }

    /* Placeholders e elementos da tabela no dark */
    .product-placeholder {
        background-color: #f1f5f9;
        border-color: #e2e8f0;
    }
    .dark-mode .product-placeholder {
        background-color: #0f172a !important;
        border-color: #334155 !important;
    }

    .sku-tag {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .dark-mode .sku-tag {
        background-color: #0f172a !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    .product-title-text {
        color: #0f172a;
    }
    .store-name-text {
        color: #1e293b;
    }
    .price-value {
        color: #0f172a;
    }
    .sales-count-text {
        color: #0f172a;
    }
    .empty-title {
        color: #0f172a;
    }

    .dark-mode .product-title-text {
        color: #f8fafc !important;
    }
    .dark-mode .store-name-text {
        color: #f1f5f9 !important;
    }
    .dark-mode .price-value {
        color: #f8fafc !important;
    }
    .dark-mode .sales-count-text {
        color: #f8fafc !important;
    }
    .dark-mode .empty-title {
        color: #f8fafc !important;
    }

    /* Badges Soft */
    .badge-success-soft {
        background-color: rgba(16, 185, 129, 0.12) !important;
    }
    .badge-warning-soft {
        background-color: rgba(245, 158, 11, 0.14) !important;
    }
    .badge-danger-soft {
        background-color: rgba(239, 68, 68, 0.12) !important;
    }
    .badge-secondary-soft {
        background-color: rgba(100, 116, 139, 0.12) !important;
    }
    .badge-info-soft {
        background-color: rgba(14, 165, 233, 0.12) !important;
    }
    .badge-purple-soft {
        background-color: rgba(147, 51, 234, 0.12) !important;
        color: #9333ea !important;
    }
    .badge-teal-soft {
        background-color: rgba(20, 184, 166, 0.12) !important;
        color: #0d9488 !important;
    }

    .dark-mode .badge-success-soft {
        background-color: rgba(16, 185, 129, 0.22) !important;
    }
    .dark-mode .badge-warning-soft {
        background-color: rgba(245, 158, 11, 0.22) !important;
    }
    .dark-mode .badge-danger-soft {
        background-color: rgba(239, 68, 68, 0.22) !important;
    }
    .dark-mode .badge-secondary-soft {
        background-color: rgba(100, 116, 139, 0.22) !important;
    }
    .dark-mode .badge-info-soft {
        background-color: rgba(14, 165, 233, 0.22) !important;
    }
    .dark-mode .badge-purple-soft {
        background-color: rgba(147, 51, 234, 0.22) !important;
        color: #c084fc !important;
    }
    .dark-mode .badge-teal-soft {
        background-color: rgba(20, 184, 166, 0.22) !important;
        color: #2dd4bf !important;
    }

    /* Animação pulsante para status ativo */
    .status-pulse {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {
        0% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        70% {
            box-shadow: 0 0 0 5px rgba(16, 185, 129, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Manipulação com AJAX global e SweetAlert2 para moderação de status de produtos
    $('.status-selector').on('change', function() {
        const select = $(this);
        const productId = select.data('product-id');
        const productTitle = select.data('product-title');
        const previousStatus = select.data('current-status');
        const newStatus = select.val();
        const actionUrl = select.data('action-url');
        const spinner = $('#spinner-' + productId);

        if (newStatus === previousStatus) {
            return;
        }

        const executeUpdate = function() {
            select.prop('disabled', true);
            spinner.removeClass('d-none');

            $.ajax({
                url: actionUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                data: {
                    status: newStatus
                },
                success: function(response) {
                    select.prop('disabled', false);
                    spinner.addClass('d-none');
                    select.data('current-status', newStatus);

                    // Atualizar Badge de Status Dinâmico
                    const badgeContainer = $('#badge-status-' + productId);
                    badgeContainer.removeClass('badge-success-soft badge-warning-soft badge-danger-soft badge-secondary-soft text-success text-warning text-danger text-secondary');
                    
                    const dot = badgeContainer.find('span.rounded-circle');
                    dot.removeClass('bg-success bg-warning bg-danger bg-secondary status-pulse');

                    let label = 'Rascunho';
                    if (newStatus === 'published') {
                        badgeContainer.addClass('badge-success-soft text-success');
                        dot.addClass('bg-success status-pulse');
                        label = 'Publicado';
                    } else if (newStatus === 'draft') {
                        badgeContainer.addClass('badge-warning-soft text-warning');
                        dot.addClass('bg-warning');
                        label = 'Rascunho';
                    } else if (newStatus === 'blocked') {
                        badgeContainer.addClass('badge-danger-soft text-danger');
                        dot.addClass('bg-danger');
                        label = 'Bloqueado';
                    }

                    badgeContainer.find('.status-text').text(label);

                    // Notificação Toastr global
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Status atualizado com sucesso!', 'Marketplace');
                    }

                    // Atualizar KPIs no topo dinamicamente se presentes
                    if (previousStatus === 'published') {
                        let count = parseInt($('#kpi-published').text()) || 0;
                        $('#kpi-published').text(Math.max(0, count - 1));
                    } else if (previousStatus === 'draft') {
                        let count = parseInt($('#kpi-draft').text()) || 0;
                        $('#kpi-draft').text(Math.max(0, count - 1));
                    } else if (previousStatus === 'blocked') {
                        let count = parseInt($('#kpi-blocked').text()) || 0;
                        $('#kpi-blocked').text(Math.max(0, count - 1));
                    }

                    if (newStatus === 'published') {
                        let count = parseInt($('#kpi-published').text()) || 0;
                        $('#kpi-published').text(count + 1);
                    } else if (newStatus === 'draft') {
                        let count = parseInt($('#kpi-draft').text()) || 0;
                        $('#kpi-draft').text(count + 1);
                    } else if (newStatus === 'blocked') {
                        let count = parseInt($('#kpi-blocked').text()) || 0;
                        $('#kpi-blocked').text(count + 1);
                    }
                },
                error: function(xhr) {
                    select.prop('disabled', false);
                    spinner.addClass('d-none');
                    select.val(previousStatus); // Reverter select

                    const errorMessage = xhr.responseJSON && xhr.responseJSON.message 
                        ? xhr.responseJSON.message 
                        : 'Não foi possível alterar o status do produto.';

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao atualizar',
                            text: errorMessage,
                            confirmButtonColor: '#1F5EDB'
                        });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage, 'Erro');
                    }
                }
            });
        };

        // Confirmação com SweetAlert2 se for bloquear
        if (newStatus === 'blocked') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Bloquear produto?',
                    text: `O produto "${productTitle}" deixará de aparecer para os clientes na loja e no catálogo público.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-ban mr-1"></i> Sim, bloquear',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        executeUpdate();
                    } else {
                        select.val(previousStatus);
                    }
                });
            } else {
                executeUpdate();
            }
        } else {
            executeUpdate();
        }
    });
});
</script>
@endpush
