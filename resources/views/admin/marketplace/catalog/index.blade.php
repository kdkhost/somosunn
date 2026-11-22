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
            <span class="badge badge-light border px-3 py-2 text-muted" style="font-size: 0.875rem;">
                <i class="fas fa-boxes-stacked mr-1.5 text-primary"></i> Moderação e Monitoramento
            </span>
            <span class="text-muted small">Gerencie produtos próprios e de lojas parceiras da plataforma</span>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('admin.marketplace.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-none">
                <i class="fas fa-arrow-left mr-1"></i> Voltar ao Hub
            </a>
            <a href="{{ route('admin.marketplace.stores.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-none">
                <i class="fas fa-store mr-1"></i> Lojas Parceiras
            </a>
            <a href="{{ route('marketplace.index') }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3 text-white shadow-none">
                <i class="fas fa-external-link-alt mr-1"></i> Ver no Site
            </a>
        </div>
    </div>

    {{-- Cards de Métricas e KPIs (Estilo React SaaS) --}}
    <div class="row">
        {{-- Total Geral --}}
        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <a href="{{ route('admin.marketplace.catalog.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm border-0 transition-all hover-lift {{ $currentStatus === '' ? 'ring-active' : '' }}" style="background: linear-gradient(135deg, rgba(31,94,219,0.04) 0%, rgba(255,255,255,1) 100%);">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total de Produtos</span>
                            <h3 class="font-weight-bold mb-0 text-dark" id="kpi-total">{{ $stats['total'] }}</h3>
                            <small class="text-muted">No catálogo geral</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(31,94,219,0.12); color: #1F5EDB;">
                            <i class="fas fa-boxes" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Publicados --}}
        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'published'])) }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm border-0 transition-all hover-lift {{ $currentStatus === 'published' ? 'ring-active' : '' }}" style="background: linear-gradient(135deg, rgba(16,185,129,0.05) 0%, rgba(255,255,255,1) 100%);">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Publicados e Ativos</span>
                            <h3 class="font-weight-bold mb-0 text-success" id="kpi-published">{{ $stats['published'] }}</h3>
                            <small class="text-muted">Visíveis aos clientes</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(16,185,129,0.12); color: #10b981;">
                            <i class="fas fa-check-circle" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Rascunhos --}}
        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'draft'])) }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm border-0 transition-all hover-lift {{ $currentStatus === 'draft' ? 'ring-active' : '' }}" style="background: linear-gradient(135deg, rgba(245,158,11,0.06) 0%, rgba(255,255,255,1) 100%);">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Em Rascunho</span>
                            <h3 class="font-weight-bold mb-0 text-warning" id="kpi-draft">{{ $stats['draft'] }}</h3>
                            <small class="text-muted">Aguardando publicação</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(245,158,11,0.12); color: #f59e0b;">
                            <i class="fas fa-pen-ruler" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Bloqueados --}}
        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'blocked'])) }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm border-0 transition-all hover-lift {{ $currentStatus === 'blocked' ? 'ring-active' : '' }}" style="background: linear-gradient(135deg, rgba(239,68,68,0.05) 0%, rgba(255,255,255,1) 100%);">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Bloqueados</span>
                            <h3 class="font-weight-bold mb-0 text-danger" id="kpi-blocked">{{ $stats['blocked'] }}</h3>
                            <small class="text-muted">Restritos por moderação</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(239,68,68,0.12); color: #ef4444;">
                            <i class="fas fa-shield-halved" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Card Principal do Catálogo --}}
    <div class="card card-outline card-primary shadow-sm border-0 mb-4">
        {{-- Card Header com abas e filtros rápidos --}}
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                {{-- Filtros Rápidos por Status (Pills estilo React) --}}
                <div class="d-flex flex-wrap align-items-center gap-1.5">
                    <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except(['status', 'page']))) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $currentStatus === '' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted' }}">
                        Todos <span class="badge badge-light ml-1 {{ $currentStatus === '' ? 'text-primary' : '' }}">{{ $stats['total'] }}</span>
                    </a>
                    <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'published'])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $currentStatus === 'published' ? 'btn-success text-white shadow-sm' : 'btn-light text-muted' }}">
                        <i class="fas fa-check-circle mr-1"></i> Publicados <span class="badge badge-light ml-1 {{ $currentStatus === 'published' ? 'text-success' : '' }}">{{ $stats['published'] }}</span>
                    </a>
                    <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'draft'])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $currentStatus === 'draft' ? 'btn-warning text-white shadow-sm' : 'btn-light text-muted' }}">
                        <i class="fas fa-pen-ruler mr-1"></i> Rascunhos <span class="badge badge-light ml-1 {{ $currentStatus === 'draft' ? 'text-warning' : '' }}">{{ $stats['draft'] }}</span>
                    </a>
                    <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except('page'), ['status' => 'blocked'])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $currentStatus === 'blocked' ? 'btn-danger text-white shadow-sm' : 'btn-light text-muted' }}">
                        <i class="fas fa-ban mr-1"></i> Bloqueados <span class="badge badge-light ml-1 {{ $currentStatus === 'blocked' ? 'text-danger' : '' }}">{{ $stats['blocked'] }}</span>
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
                        <select name="type" class="custom-select custom-select-sm rounded-pill" style="height: 34px; font-size: 0.8125rem; min-width: 140px;" onchange="this.form.submit()">
                            <option value="">Todos os Tipos</option>
                            <option value="physical" {{ $currentType === 'physical' ? 'selected' : '' }}>Físico</option>
                            <option value="digital" {{ $currentType === 'digital' ? 'selected' : '' }}>Digital</option>
                            <option value="service" {{ $currentType === 'service' ? 'selected' : '' }}>Serviço</option>
                        </select>
                    </form>

                    @if($currentStatus !== '' || $currentType !== '' || $currentSearch !== '')
                        <a href="{{ route('admin.marketplace.catalog.index') }}" class="btn btn-sm btn-light text-danger rounded-pill px-2.5" title="Limpar todos os filtros">
                            <i class="fas fa-times-circle mr-1"></i> Limpar
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Barra de Busca Integrada --}}
        <div class="px-3 pt-3 pb-2 bg-light border-bottom">
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
                            <span class="input-group-text bg-white border-right-0" style="border-radius: 0.625rem 0 0 0.625rem;">
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
                        <a href="{{ route('admin.marketplace.catalog.index', array_merge(request()->except(['q', 'page']))) }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-none" title="Limpar busca">
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
                    <thead class="bg-light">
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
                                    default => ['label' => strtoupper($product->type ?: 'PADRÃO'), 'icon' => 'fa-tag', 'class' => 'badge-light text-muted border'],
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
                                                <div class="rounded d-flex align-items-center justify-content-center text-muted shadow-xs bg-light border" style="width: 52px; height: 52px;">
                                                    <i class="fas fa-image" style="font-size: 1.25rem;"></i>
                                                </div>
                                            @endif
                                            @if($hasDiscount)
                                                <span class="badge badge-danger position-absolute" style="top: -6px; right: -6px; font-size: 0.6rem; padding: 2px 4px;">PROMO</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-weight-bold text-dark text-truncate mb-0.5" style="font-size: 0.9rem;" title="{{ $product->title }}">
                                                {{ $product->title }}
                                            </div>
                                            <div class="d-flex align-items-center flex-wrap gap-1 small text-muted">
                                                @if($product->sku)
                                                    <span class="badge badge-light border text-muted px-1.5 py-0.5" style="font-size: 0.7rem; font-family: monospace;">SKU: {{ $product->sku }}</span>
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
                                        <div class="font-weight-600 text-dark mb-0.5">
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
                                        <div class="font-weight-bold text-success" style="font-size: 0.95rem;">
                                            R$ {{ number_format((float) $product->sale_price, 2, ',', '.') }}
                                        </div>
                                    @else
                                        <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                            R$ {{ number_format((float) ($product->price ?: 0), 2, ',', '.') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Vendas & Clientes --}}
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="font-weight-bold text-dark" style="font-size: 0.95rem;">
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
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-light border mb-3" style="width: 70px; height: 70px;">
                                            <i class="fas fa-boxes text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                        <h5 class="font-weight-bold text-dark mb-1">Nenhum produto encontrado</h5>
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
            <div class="card-footer bg-white border-top py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
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
    /* Estilos e Badges Soft estilo React/Tailwind */
    .gap-1\.5 { gap: 0.375rem !important; }
    .gap-2 { gap: 0.5rem !important; }
    .gap-3 { gap: 0.75rem !important; }
    
    .hover-lift {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
    }

    .ring-active {
        box-shadow: 0 0 0 2px #1F5EDB !important;
    }

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

    /* Dark Mode Adjustments */
    .dark-mode .bg-white {
        background-color: #1e293b !important;
    }
    .dark-mode .text-dark {
        color: #f8fafc !important;
    }
    .dark-mode .hover-lift:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35) !important;
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
                    const badgeContainer = $('#badge-status-{{ $product->id ?? 0 }}'.replace('{{ $product->id ?? 0 }}', productId));
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
