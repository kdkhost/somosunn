@extends('admin.layouts.app')

@section('title', $banner->exists ? 'Editar Banner Patrocinado' : 'Novo Banner Patrocinado')
@section('page_title', $banner->exists ? 'Editar Banner Patrocinado' : 'Novo Banner Patrocinado')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sponsor-banners.index') }}">Banners Patrocinados</a></li>
    <li class="breadcrumb-item active">{{ $banner->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
    @if($banner->exists)
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="info-box mb-0">
                    <span class="info-box-icon bg-primary"><i class="fas fa-eye"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Impressões Reais</span>
                        <span class="info-box-number">{{ number_format((int) ($banner->impressions_count ?? 0), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box mb-0">
                    <span class="info-box-icon bg-success"><i class="fas fa-arrow-pointer"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Cliques Rastreados</span>
                        <span class="info-box-number">{{ number_format((int) ($banner->clicks_count ?? 0), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box mb-0">
                    <span class="info-box-icon bg-info"><i class="fas fa-percent"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Taxa de Cliques (CTR)</span>
                        <span class="info-box-number">{{ number_format($banner->ctr, 2, ',', '.') }}%</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ $banner->exists ? route('admin.sponsor-banners.update', $banner) : route('admin.sponsor-banners.store') }}">
        @csrf
        @if($banner->exists) @method('PUT') @endif

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-images me-2 text-primary"></i>Dados do Banner</h3>
                <a href="{{ route('admin.sponsor-banners.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 form-group">
                        <label for="banner_sponsor_id">Anunciante / Patrocinador</label>
                        <select id="banner_sponsor_id" name="sponsor_id" class="form-select" required>
                            <option value="">Selecione</option>
                            @foreach($sponsors as $sponsor)
                                <option value="{{ $sponsor->id }}" @selected(old('sponsor_id', $banner->sponsor_id) == $sponsor->id)>
                                    {{ $sponsor->company?->name ?: ('Patrocinador #' . $sponsor->id) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="banner_title">Título da Campanha</label>
                        <input type="text" id="banner_title" name="title" class="form-control" value="{{ old('title', $banner->title) }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="banner_position">Posição de Exibição</label>
                        <select id="banner_position" name="position" class="form-select" required>
                            @foreach(\App\Models\SponsorBanner::POSITIONS as $posKey => $posLabel)
                                <option value="{{ $posKey }}" @selected(old('position', $banner->position ?: 'global_banner') === $posKey)>{{ $posLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="banner_approval_status">Status de Aprovação</label>
                        <select id="banner_approval_status" name="approval_status" class="form-select">
                            @php($approvalVal = old('approval_status', $banner->approval_status ?? 'approved'))
                            <option value="approved" @selected($approvalVal === 'approved')>Aprovado (Em veiculação)</option>
                            <option value="pending" @selected($approvalVal === 'pending')>Pendente de revisão</option>
                            <option value="rejected" @selected($approvalVal === 'rejected')>Reprovado</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="banner_url">URL de Destino</label>
                        <input type="url" id="banner_url" name="url" class="form-control" value="{{ old('url', $banner->url) }}" placeholder="https://...">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="banner_starts_at">Início da Veiculação</label>
                        <input type="datetime-local" id="banner_starts_at" name="starts_at" class="form-control" value="{{ old('starts_at', optional($banner->starts_at)->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="banner_ends_at">Fim da Veiculação</label>
                        <input type="datetime-local" id="banner_ends_at" name="ends_at" class="form-control" value="{{ old('ends_at', optional($banner->ends_at)->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label">Arte do Banner</label>
                        <div class="upload-box" data-remove-input="#remove_banner_image" data-existing-url="{{ $banner->image_url }}">
                            <input type="file" name="image" class="d-none" accept="image/*">
                            <input type="hidden" name="remove_image" id="remove_banner_image" value="0">
                            <div class="upload-preview mb-2">
                                @if($banner->image_url)
                                    <img src="{{ $banner->image_url }}" class="img-fluid" style="max-height: 90px;" loading="lazy" decoding="async">
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i class="fas fa-upload me-1"></i> Selecionar</button>
                            <button type="button" class="btn btn-sm btn-outline-danger upload-remove {{ $banner->image_url ? '' : 'd-none' }}"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" class="form-check-input" id="active" name="active" value="1" @checked(old('active', $banner->active ?? true))>
                            <label class="form-check-label fw-bold" for="active">Banner ativo</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Salvar Banner
                </button>
            </div>
        </div>
    </form>
@endsection
