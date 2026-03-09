@extends('admin.layouts.app')

@section('page_title', 'Mural de Vagas')
@section('breadcrumb')<li class="breadcrumb-item active">Mural de Vagas</li>@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-briefcase mr-2"></i>Vagas de Emprego</h3>
            <div class="card-tools">
                <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nova Vaga
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped projects">
                <thead>
                    <tr>
                        <th style="width: 25%">Título</th>
                        <th style="width: 20%">Empresa</th>
                        <th>Tipo</th>
                        <th>Candidatos</th>
                        <th class="text-center">Status</th>
                        <th style="width: 20%">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vacancies as $vacancy)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($vacancy->image)
                                        <img src="{{ $vacancy->image_url }}" class="img-circle img-size-32 mr-2"
                                            style="object-fit: contain; background: #f8f9fa; border: 1px solid #dee2e6;">
                                    @else
                                        <div class="img-circle img-size-32 mr-2 bg-light d-flex align-items-center justify-center text-muted"
                                            style="border: 1px solid #dee2e6;">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('jobs.public.show', $vacancy->id) }}" target="_blank"
                                            class="font-weight-bold">{{ $vacancy->title }}</a>
                                        <br />
                                        <small class="text-muted">Publicado em
                                            {{ $vacancy->created_at->format('d/m/Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $vacancy->company_name ?: 'Confidencial' }}
                                @if($vacancy->is_demo)
                                    <br><span class="badge badge-warning" style="font-size: 10px;"><i
                                            class="fas fa-flask mr-1"></i>DEMO</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-light border">{{ $vacancy->level ?? 'N/A' }}</span>
                                <br><small class="text-muted">{{ $vacancy->type }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $vacancy->applications_count }}</span>
                            </td>
                            <td class="project-state text-center">
                                @if($vacancy->is_active)
                                    <span class="badge badge-success px-2">Ativa</span>
                                @else
                                    <span class="badge badge-secondary px-2">Inativa</span>
                                @endif
                                @if($vacancy->expires_at && $vacancy->expires_at->isPast())
                                    <br><span class="badge badge-danger px-2 mt-1">Expirada</span>
                                @endif
                            </td>
                            <td class="project-actions text-right">
                                <a class="btn btn-info btn-sm" href="{{ route('admin.jobs.edit', $vacancy) }}" title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('admin.jobs.destroy', $vacancy) }}" method="POST"
                                    style="display:inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-delete" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">Nenhuma vaga cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection