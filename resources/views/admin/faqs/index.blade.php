@extends('admin.layouts.app')

@section('page_title','FAQ')
@section('breadcrumb')<li class="breadcrumb-item active">FAQ</li>@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap mb-3">
            <div>
                <h3 class="m-0">Perguntas Frequentes</h3>
                <div class="text-muted small">Controle o FAQ exibido no site (Premium/Contato/Geral).</div>
            </div>
            <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary" data-pjax>Nova pergunta</a>
        </div>

        <form method="GET" action="{{ route('admin.faqs.index') }}" class="mb-3">
            <div class="form-row align-items-end">
                <div class="form-group col-md-3">
                    <label class="mb-1">Contexto</label>
                    <select name="context" class="form-control">
                        <option value="">Todos</option>
                        @foreach($contexts as $key => $label)
                            <option value="{{ $key }}" {{ $context === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label class="mb-1">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label class="mb-1">Buscar</label>
                    <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Pergunta ou resposta">
                </div>
                <div class="form-group col-md-2">
                    <button class="btn btn-secondary btn-block">Filtrar</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Pergunta</th>
                        <th>Contexto</th>
                        <th>Ordem</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $faq)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $faq->question }}</div>
                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit($faq->answer, 120) }}</div>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $contexts[$faq->context] ?? $faq->context }}
                                </span>
                            </td>
                            <td class="text-muted">{{ (int) $faq->sort_order }}</td>
                            <td>
                                <span class="badge badge-{{ $faq->is_active ? 'success' : 'secondary' }}">
                                    {{ $faq->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-sm btn-secondary" data-pjax>Editar</a>
                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-action="{{ route('admin.faqs.destroy', $faq) }}">Excluir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhuma pergunta cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $faqs->links() }}
    </div>
</div>
@endsection

