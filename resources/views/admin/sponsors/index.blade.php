@extends('admin.layouts.app')

@section('title', 'Patrocinadores & Anunciantes - Admin')
@section('page_title', 'Patrocinadores & Anunciantes')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.settings', ['group' => 'ads']) }}">Anúncios</a></li>
    <li class="breadcrumb-item active" aria-current="page">Patrocinadores</li>
@endsection

@section('content')
    @php
        $items = $sponsors->getCollection();
        $activeSponsors = $items->where('status', \App\Models\Sponsor::STATUS_ACTIVE)->count();
        $pendingSponsors = $items->where('status', \App\Models\Sponsor::STATUS_PENDING)->count();
    @endphp

    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <div>
                <h3 class="card-title"><i class="fas fa-handshake me-2 text-primary"></i>Anunciantes &amp; Contratos Ativos</h3>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.sponsor-plans.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-tags me-1"></i> Pacotes por Período
                </a>
                <a href="{{ route('admin.sponsor-banners.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-images me-1"></i> Banners
                </a>
                <a href="{{ route('admin.sponsors.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="fas fa-plus me-1"></i> Novo Patrocinador
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Empresa / Anunciante</th>
                            <th>Pacote Contratado</th>
                            <th>Vigência</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sponsors as $sponsor)
                            @php
                                $companyName = $sponsor->company?->name ?: ('Patrocinador #' . $sponsor->id);
                                $initial = mb_strtoupper(mb_substr($companyName, 0, 1));
                                $statusMap = [
                                    'active' => ['Ativo', 'text-bg-success'],
                                    'pending' => ['Pendente', 'text-bg-warning'],
                                    'expired' => ['Expirado', 'text-bg-secondary'],
                                    'cancelled' => ['Cancelado', 'text-bg-danger'],
                                ];
                                [$statusLabel, $statusClass] = $statusMap[$sponsor->status] ?? [ucfirst((string) $sponsor->status), 'text-bg-info'];
                                $daysLeft = $sponsor->ends_at ? (int) now()->diffInDays($sponsor->ends_at, false) : null;
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold text-white flex-shrink-0"
                                              style="width: 38px; height: 38px; background: linear-gradient(135deg, #1F5EDB, #177FD6);">
                                            {{ $initial }}
                                        </span>
                                        <div>
                                            <div class="fw-bold">{{ $companyName }}</div>
                                            @if($sponsor->company?->email)
                                                <small class="text-secondary">{{ $sponsor->company->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($sponsor->plan)
                                        <div class="fw-semibold">{{ $sponsor->plan->name }}</div>
                                        <small class="text-secondary">
                                            R$ {{ number_format((float) $sponsor->plan->price, 2, ',', '.') }}
                                            &bull; {{ $sponsor->plan->duration_days ?? 30 }} dias
                                        </small>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">
                                        {{ optional($sponsor->starts_at)->format('d/m/Y') ?: '—' }}
                                        &rarr;
                                        {{ optional($sponsor->ends_at)->format('d/m/Y') ?: '—' }}
                                    </div>
                                    @if($daysLeft !== null)
                                        <small class="{{ $daysLeft >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $daysLeft >= 0 ? ($daysLeft . ' dia(s) restante(s)') : 'Vencido há ' . abs($daysLeft) . ' dia(s)' }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill {{ $statusClass }} px-3 py-1">{{ $statusLabel }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.sponsors.edit', $sponsor) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-edit me-1"></i> Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">Nenhum patrocinador cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sponsors->hasPages())
            <div class="card-footer">{{ $sponsors->links() }}</div>
        @endif
    </div>
@endsection
