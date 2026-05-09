@extends('admin.layouts.app')

@section('page_title', 'Mural de Vagas')
@section('breadcrumb')<li class="breadcrumb-item active">Mural de Vagas</li>@endsection

@section('content')
    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-primary elevation-1">
                <span class="info-box-icon"><i class="fas fa-briefcase"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de Vagas</span>
                    <span class="info-box-number">{{ $vacancies->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-success elevation-1">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ativas</span>
                    <span class="info-box-number">{{ $vacancies->where('is_active', true)->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-warning elevation-1">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Candidaturas</span>
                    <span class="info-box-number">{{ $vacancies->sum('applications_count') }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-danger elevation-1">
                <span class="info-box-icon"><i class="fas fa-calendar-times"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Expiradas</span>
                    <span class="info-box-number">{{ $vacancies->filter(fn($v) => $v->expires_at && $v->expires_at->isPast())->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-briefcase text-primary mr-2"></i>Vagas de Emprego
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1">
                    <i class="fas fa-plus mr-1"></i> Nova Vaga
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($vacancies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 25%">Titulo</th>
                                <th style="width: 20%">Empresa</th>
                                <th>Tipo</th>
                                <th class="text-center">Candidatos</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vacancies as $vacancy)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($vacancy->image)
                                                <img src="{{ $vacancy->image_url }}" class="img-circle img-size-32 mr-2"
                                                    style="object-fit: contain; background: #f8f9fa; border: 1px solid #dee2e6;">
                                            @else
                                                <div class="img-circle img-size-32 mr-2 bg-light d-flex align-items-center justify-content-center text-muted"
                                                    style="border: 1px solid #dee2e6;">
                                                    <i class="fas fa-briefcase"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ route('jobs.public.show', $vacancy->id) }}" target="_blank"
                                                    class="font-weight-bold">{{ $vacancy->title }}</a>
                                                <br>
                                                <small class="text-muted">Publicado em {{ $vacancy->created_at->format('d/m/Y') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $vacancy->company_name ?: 'Confidencial' }}
                                        @if($vacancy->is_demo)
                                            <br><span class="badge badge-warning"><i class="fas fa-flask mr-1"></i>DEMO</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light border">{{ $vacancy->level ?? 'N/A' }}</span>
                                        <br><small class="text-muted">{{ $vacancy->type }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info"><i class="fas fa-user mr-1"></i>{{ $vacancy->applications_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($vacancy->is_active)
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Ativa</span>
                                        @else
                                            <span class="badge badge-secondary"><i class="fas fa-pause mr-1"></i>Inativa</span>
                                        @endif
                                        @if($vacancy->expires_at && $vacancy->expires_at->isPast())
                                            <br><span class="badge badge-danger mt-1"><i class="fas fa-calendar-times mr-1"></i>Expirada</span>
                                        @endif
                                    </td>
                                    <td class="text-right" style="white-space:nowrap">
                                        <a class="btn btn-sm btn-outline-primary rounded-pill" href="{{ route('admin.jobs.edit', $vacancy) }}" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.jobs.destroy', $vacancy) }}" method="POST"
                                            class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill btn-delete" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-1">Nenhuma vaga cadastrada.</p>
                    <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1 mt-2">
                        <i class="fas fa-plus mr-1"></i> Cadastrar primeira vaga
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
