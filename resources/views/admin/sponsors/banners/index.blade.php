@extends('admin.layouts.app')

@section('title', 'Banners Patrocinados - Admin')
@section('page_title', 'Banners & Campanhas de Publicidade')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.settings', ['group' => 'ads']) }}">Anúncios</a></li>
    <li class="breadcrumb-item active" aria-current="page">Banners</li>
@endsection

@section('content')
    @php
        $items = $banners->getCollection();
        $totalImpressions = (int) $items->sum('impressions_count');
        $totalClicks = (int) $items->sum('clicks_count');
        $avgCtr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;
        $pendingApprovals = $items->where('approval_status', \App\Models\SponsorBanner::STATUS_PENDING)->count();
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-primary mb-0">
                <div class="inner">
                    <h3>{{ number_format($totalImpressions, 0, ',', '.') }}</h3>
                    <p>Impressões Reais</p>
                </div>
                <div class="icon"><i class="fas fa-eye"></i></div>
                <div class="small-box-footer"><span>Total exibido nos slots</span></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-success mb-0">
                <div class="inner">
                    <h3>{{ number_format($totalClicks, 0, ',', '.') }}</h3>
                    <p>Cliques Rastreados</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-pointer"></i></div>
                <div class="small-box-footer"><span>Via rota rastreada /anuncio/clique</span></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-info mb-0">
                <div class="inner">
                    <h3>{{ number_format($avgCtr, 2, ',', '.') }}%</h3>
                    <p>CTR Médio</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <div class="small-box-footer"><span>Taxa média de cliques</span></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-warning mb-0">
                <div class="inner">
                    <h3>{{ $pendingApprovals }}</h3>
                    <p>Aguardando Aprovação</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="small-box-footer"><span>Banners pendentes de revisão</span></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-images me-2 text-primary"></i>Campanhas &amp; Criativos</h3>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.sponsor-plans.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-tags me-1"></i> Pacotes
                </a>
                <a href="{{ route('admin.sponsors.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-handshake me-1"></i> Patrocinadores
                </a>
                <a href="{{ route('admin.sponsor-banners.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="fas fa-plus me-1"></i> Novo Banner
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Criativo &amp; Campanha</th>
                            <th>Posição</th>
                            <th class="text-center">Performance</th>
                            <th class="text-center">Aprovação</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banners as $banner)
                            @php
                                $posLabel = \App\Models\SponsorBanner::POSITIONS[$banner->position] ?? $banner->position;
                                $approval = $banner->approval_status ?? 'approved';
                                $approvalBadge = match ($approval) {
                                    'approved' => ['Aprovado', 'text-bg-success'],
                                    'pending' => ['Pendente', 'text-bg-warning'],
                                    'rejected' => ['Reprovado', 'text-bg-danger'],
                                    default => [ucfirst($approval), 'text-bg-secondary'],
                                };
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($banner->image_url)
                                            <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}"
                                                 class="rounded-3 border flex-shrink-0"
                                                 style="width: 112px; height: 42px; object-fit: cover;" loading="lazy" decoding="async">
                                        @else
                                            <div class="rounded-3 border d-flex align-items-center justify-content-center text-secondary flex-shrink-0"
                                                 style="width: 112px; height: 42px;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $banner->title }}</div>
                                            <small class="text-secondary">{{ $banner->sponsor?->company?->name ?: 'Sem empresa' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill text-bg-light border">{{ $posLabel }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="small fw-semibold">
                                        <i class="fas fa-eye text-primary me-1"></i>{{ number_format((int) ($banner->impressions_count ?? 0), 0, ',', '.') }}
                                        <span class="mx-1 text-secondary">|</span>
                                        <i class="fas fa-arrow-pointer text-success me-1"></i>{{ number_format((int) ($banner->clicks_count ?? 0), 0, ',', '.') }}
                                    </div>
                                    <small class="text-secondary">CTR: {{ number_format($banner->ctr, 2, ',', '.') }}%</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill {{ $approvalBadge[1] }}">{{ $approvalBadge[0] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill {{ $banner->active ? 'text-bg-primary' : 'text-bg-secondary' }}">
                                        {{ $banner->active ? 'Ativo' : 'Pausado' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.sponsor-banners.edit', $banner) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-edit me-1"></i> Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-5">Nenhum banner cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($banners->hasPages())
            <div class="card-footer">{{ $banners->links() }}</div>
        @endif
    </div>
@endsection
