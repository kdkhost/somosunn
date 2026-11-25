@extends('admin.layouts.app')

@section('title', 'Pacotes de Publicidade - Admin')
@section('page_title', 'Pacotes de Publicidade')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.settings', ['group' => 'ads']) }}">Anúncios</a></li>
    <li class="breadcrumb-item active" aria-current="page">Pacotes por Período</li>
@endsection

@push('styles')
<style>
    .ads-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.1rem;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #1F5EDB 100%);
        color: #ffffff;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 18px 40px -12px rgba(15, 23, 42, 0.35);
    }
    .ads-hero::after {
        content: '';
        position: absolute;
        right: -50px;
        top: -50px;
        width: 220px;
        height: 220px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, rgba(56, 189, 248, 0) 70%);
        pointer-events: none;
    }
    .plan-grid-card {
        position: relative;
        border-radius: 1.05rem;
        border: 1.5px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        padding: 1.35rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }
    .plan-grid-card:hover {
        transform: translateY(-4px);
        border-color: rgba(31, 94, 219, 0.55);
        box-shadow: 0 16px 32px -10px rgba(31, 94, 219, 0.22);
    }
    .plan-grid-card.is-popular {
        border-color: #1F5EDB;
        box-shadow: 0 14px 30px -10px rgba(31, 94, 219, 0.28);
    }
    [data-bs-theme="dark"] .plan-grid-card {
        background: #131d31;
    }
    .plan-price-main {
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--unn-azul-1);
    }
    [data-bs-theme="dark"] .plan-price-main {
        color: #60a5fa;
    }
    .plan-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 600;
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
    }
</style>
@endpush

@section('content')
    @php
        $plansCollection = $plans->getCollection();
        $activeCount = $plansCollection->where('active', true)->count();
        $minPrice = $plansCollection->min('price') ?? 0;
        $maxPrice = $plansCollection->max('price') ?? 0;
    @endphp

    <div class="ads-hero mb-4">
        <div class="row align-items-center g-3 position-relative" style="z-index: 2;">
            <div class="col-lg-7">
                <div class="text-uppercase fw-bold mb-1" style="font-size: 0.72rem; letter-spacing: 0.14em; color: #93c5fd;">
                    <i class="fas fa-bullhorn me-1"></i> Rede Própria de Publicidade • Monetização
                </div>
                <h2 class="h4 fw-bold text-white mb-2">Pacotes de Publicidade por Período</h2>
                <p class="mb-0 text-white-50" style="font-size: 0.9rem;">
                    Defina períodos (7, 14, 21, 30, 60, 90, 120 e 365 dias), valores progressivos, limites de banners e prioridade de rotação.
                </p>
            </div>
            <div class="col-lg-5 d-flex flex-wrap justify-content-lg-end gap-2">
                <a href="{{ route('admin.sponsors.index') }}" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="fas fa-handshake me-1"></i> Patrocinadores
                </a>
                <a href="{{ route('admin.sponsor-banners.index') }}" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="fas fa-images me-1"></i> Banners
                </a>
                <a href="{{ route('admin.settings', ['group' => 'ads']) }}" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="fas fa-sliders-h me-1"></i> Split &amp; Regras
                </a>
                <a href="{{ route('admin.sponsor-plans.create') }}" class="btn btn-sm btn-light fw-bold text-primary rounded-pill px-3">
                    <i class="fas fa-plus me-1"></i> Novo Pacote
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="small-box bg-primary mb-0">
                <div class="inner">
                    <h3>{{ $plans->total() }}</h3>
                    <p>Pacotes Cadastrados</p>
                </div>
                <div class="icon"><i class="fas fa-tags"></i></div>
                <div class="small-box-footer"><span>{{ $activeCount }} ativo(s) nesta página</span></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-teal mb-0">
                <div class="inner">
                    <h3>R$ {{ number_format((float) $minPrice, 2, ',', '.') }}</h3>
                    <p>Ticket Inicial</p>
                </div>
                <div class="icon"><i class="fas fa-bolt"></i></div>
                <div class="small-box-footer"><span>Porta de entrada para anunciantes</span></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-purple mb-0">
                <div class="inner">
                    <h3>R$ {{ number_format((float) $maxPrice, 2, ',', '.') }}</h3>
                    <p>Maior Cota</p>
                </div>
                <div class="icon"><i class="fas fa-crown"></i></div>
                <div class="small-box-footer"><span>Planos recorrentes e anuais VIP</span></div>
            </div>
        </div>
    </div>

    @if($plans->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-tags fa-2x text-secondary mb-3"></i>
                <h5 class="fw-bold">Nenhum pacote cadastrado</h5>
                <p class="text-secondary small mb-3">Crie pacotes por período para comercializar espaços de publicidade na plataforma.</p>
                <a href="{{ route('admin.sponsor-plans.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                    <i class="fas fa-plus me-1"></i> Criar Primeiro Pacote
                </a>
            </div>
        </div>
    @else
        <div class="row g-3 mb-4">
            @foreach($plans as $plan)
                @php
                    $days = max(1, (int) ($plan->duration_days ?? 30));
                    $perDay = (float) $plan->price / $days;
                    $isPopular = $days === 30 || $days === 90;
                @endphp
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="plan-grid-card {{ $isPopular ? 'is-popular' : '' }}">
                        <div>
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <span class="badge rounded-pill {{ $plan->active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $plan->active ? 'Ativo' : 'Inativo' }}
                                </span>
                                @if($days === 30)
                                    <span class="badge rounded-pill text-bg-primary"><i class="fas fa-fire me-1"></i>Mais Popular</span>
                                @elseif($days >= 365)
                                    <span class="badge rounded-pill text-bg-warning"><i class="fas fa-crown me-1"></i>VIP</span>
                                @else
                                    <span class="badge rounded-pill text-bg-light border">Prioridade {{ $plan->priority }}</span>
                                @endif
                            </div>

                            <h5 class="fw-bold mb-1">{{ $plan->name }}</h5>
                            <div class="plan-price-main">R$ {{ number_format((float) $plan->price, 2, ',', '.') }}</div>
                            <div class="text-secondary small mb-3">
                                <i class="far fa-calendar-alt me-1"></i><strong>{{ $days }} dias</strong>
                                &bull; R$ {{ number_format($perDay, 2, ',', '.') }}/dia
                            </div>

                            @if($plan->description)
                                <p class="text-secondary small mb-3" style="line-height: 1.45;">{{ $plan->description }}</p>
                            @endif

                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="plan-meta-pill"><i class="fas fa-images text-primary"></i> {{ $plan->max_banners }} banner(s)</span>
                                <span class="plan-meta-pill"><i class="fas fa-calendar text-info"></i> {{ $plan->max_events }} evento(s)</span>
                                <span class="plan-meta-pill"><i class="fas fa-users text-success"></i> {{ number_format((int) $plan->max_leads, 0, ',', '.') }} leads</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-2 border-top">
                            <a href="{{ route('admin.sponsor-plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary flex-grow-1 rounded-pill">
                                <i class="fas fa-edit me-1"></i> Editar
                            </a>
                            <form method="POST" action="{{ route('admin.sponsor-plans.destroy', $plan) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill btn-delete" data-confirm-delete title="Remover pacote">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($plans->hasPages())
            <div class="d-flex justify-content-center">{{ $plans->links() }}</div>
        @endif
    @endif
@endsection
