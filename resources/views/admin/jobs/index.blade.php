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
                                <a>{{ $vacancy->title }}</a>
                                <br />
                                <small>Publicado em {{ $vacancy->created_at->format('d/m/Y') }}</small>
                            </td>
                            <td>{{ $vacancy->company_name ?: 'Confidencial' }}</td>
                            <td>{{ $vacancy->type }}</td>
                            <td>
                                <span class="badge badge-info">{{ $vacancy->applications_count }} inscritos</span>
                            </td>
                            <td class="project-state text-center">
                                @if($vacancy->is_active)
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                                @if($vacancy->expires_at && $vacancy->expires_at->isPast())
                                    <span class="badge badge-danger">Expirada</span>
                                @endif
                            </td>
                            <td class="project-actions text-right">
                                <a class="btn btn-info btn-sm" href="{{ route('admin.jobs.edit', $vacancy) }}">
                                    <i class="fas fa-pencil-alt"></i> Editar
                                </a>
                                <form action="{{ route('admin.jobs.destroy', $vacancy) }}" method="POST"
                                    style="display:inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-delete" title="Excluir">
                                        <i class="fas fa-trash"></i> Excluir
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