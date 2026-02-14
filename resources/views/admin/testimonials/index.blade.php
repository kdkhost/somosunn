@extends('admin.layouts.app')

@section('page_title','Depoimentos')
@section('breadcrumb')<li class="breadcrumb-item active">Depoimentos</li>@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap mb-3">
            <h3 class="m-0">Depoimentos (moderação)</h3>
        </div>

        <form method="GET" class="mb-3">
            <div class="form-row">
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pendentes</option>
                        <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Aprovados</option>
                        <option value="rejected" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Recusados</option>
                    </select>
                </div>
                <div class="col-md-6 mb-2">
                    <input name="q" class="form-control" placeholder="Buscar por nome, título ou texto" value="{{ $q ?? '' }}">
                </div>
                <div class="col-md-3 mb-2 text-right">
                    <button class="btn btn-primary btn-block">Filtrar</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Autor</th>
                        <th>Depoimento</th>
                        <th>Avaliação</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testimonials as $t)
                        @php
                            $status = $t->status;
                            $badge = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');
                        @endphp
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $t->author_name ?: ($t->user->name ?? '—') }}</div>
                                <div class="text-muted small">{{ $t->author_title ?: '—' }}</div>
                            </td>
                            <td class="text-muted">
                                {{ \Illuminate\Support\Str::limit($t->content, 140) }}
                                @if($t->moderation_notes)
                                    <div class="small text-danger mt-1"><i class="fas fa-comment-dots mr-1"></i>{{ $t->moderation_notes }}</div>
                                @endif
                            </td>
                            <td>
                                @if($t->rating)
                                    <span class="font-weight-bold">{{ $t->rating }}/5</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                                @if($t->is_featured)
                                    <span class="badge badge-primary ml-2">Destaque</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $badge }}">
                                    {{ $status === 'approved' ? 'Aprovado' : ($status === 'rejected' ? 'Recusado' : 'Pendente') }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-sm btn-secondary" data-pjax>Editar</a>

                                @if($t->status !== 'approved')
                                    <form method="POST" action="{{ route('admin.testimonials.approve', $t) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Aprovar</button>
                                    </form>
                                @endif

                                @if($t->status !== 'rejected')
                                    <button type="button" class="btn btn-sm btn-warning btn-reject-testimonial"
                                        data-action="{{ route('admin.testimonials.reject', $t) }}">
                                        Recusar
                                    </button>
                                @endif

                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-action="{{ route('admin.testimonials.destroy', $t) }}">Excluir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Nenhum depoimento encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $testimonials->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.btn-reject-testimonial', function () {
        const action = $(this).data('action');
        Swal.fire({
            title: 'Recusar depoimento?',
            input: 'textarea',
            inputLabel: 'Motivo (opcional)',
            inputPlaceholder: 'Ex.: Linguagem agressiva, spam, fora do contexto...',
            showCancelButton: true,
            confirmButtonText: 'Recusar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f0ad4e'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = action;
            form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="moderation_notes" value="${(result.value || '').replace(/\"/g,'&quot;')}">
            `;
            document.body.appendChild(form);
            form.submit();
        });
    });
</script>
@endpush

