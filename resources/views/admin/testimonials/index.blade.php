@extends('admin.layouts.app')

@section('page_title','Depoimentos')
@section('breadcrumb')<li class="breadcrumb-item active">Depoimentos</li>@endsection

@push('styles')
<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }
    .avatar-initials {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #4e73df;
        color: #fff;
        font-weight: 700;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stars-display { color: #f59e0b; letter-spacing: 1px; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap align-items-center mb-3">
            <h3 class="m-0">Depoimentos</h3>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.testimonials.import-google') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fab fa-google mr-1"></i> Importar do Google
                </a>
                <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Novo depoimento
                </a>
            </div>
        </div>

        <form method="GET" class="mb-3">
            <div class="form-row">
                <div class="col-md-2 mb-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Status (todos)</option>
                        <option value="pending"  {{ ($status ?? '') === 'pending'  ? 'selected' : '' }}>Pendentes</option>
                        <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Aprovados</option>
                        <option value="rejected" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Recusados</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="source" class="form-control form-control-sm">
                        <option value="">Origem (todos)</option>
                        <option value="manual" {{ ($source ?? '') === 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="google" {{ ($source ?? '') === 'google' ? 'selected' : '' }}>Google</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="active" class="form-control form-control-sm">
                        <option value="">Visibilidade (todos)</option>
                        <option value="1" {{ ($active ?? '') === '1' ? 'selected' : '' }}>Ativos</option>
                        <option value="0" {{ ($active ?? '') === '0' ? 'selected' : '' }}>Inativos</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <input name="q" class="form-control form-control-sm" placeholder="Buscar por nome, título ou texto…" value="{{ $q ?? '' }}">
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary btn-sm btn-block">Filtrar</button>
                </div>
            </div>
        </form>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                {{ session('info') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width:44px"></th>
                        <th>Autor</th>
                        <th>Depoimento</th>
                        <th>Avaliação</th>
                        <th>Origem</th>
                        <th>Status</th>
                        <th>Ativo</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testimonials as $t)
                        @php
                            $st = $t->status;
                            $badge = $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : 'warning');
                            $resolvedAvatar = $t->resolved_avatar;
                            $displayName = !empty($t->author_name) ? $t->author_name : ($t->user->name ?? '—');
                        @endphp
                        <tr>
                            {{-- Avatar --}}
                            <td>
                                @if($resolvedAvatar)
                                    <img src="{{ $resolvedAvatar }}" alt="{{ $displayName }}" class="avatar-circle">
                                @else
                                    <span class="avatar-initials">{{ strtoupper(substr($displayName, 0, 1)) }}</span>
                                @endif
                            </td>

                            {{-- Autor --}}
                            <td>
                                <div class="font-weight-bold">{{ $displayName }}</div>
                                <div class="text-muted small">{{ $t->author_title ?: '—' }}</div>
                                @if($t->user_id)
                                    <div class="text-muted small"><i class="fas fa-user-circle mr-1"></i>Membro vinculado</div>
                                @endif
                            </td>

                            {{-- Depoimento --}}
                            <td class="text-muted">
                                {{ \Illuminate\Support\Str::limit($t->content, 140) }}
                                @if($t->moderation_notes)
                                    <div class="small text-danger mt-1">
                                        <i class="fas fa-comment-dots mr-1"></i>{{ $t->moderation_notes }}
                                    </div>
                                @endif
                                @if($t->is_featured)
                                    <span class="badge badge-primary">Destaque</span>
                                @endif
                            </td>

                            {{-- Avaliação --}}
                            <td>
                                @if($t->rating)
                                    <span class="stars-display">{{ str_repeat('★', $t->rating) }}{{ str_repeat('☆', 5 - $t->rating) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Origem --}}
                            <td>
                                @if($t->source === 'google')
                                    <span class="badge badge-info"><i class="fab fa-google mr-1"></i>Google</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-pencil-alt mr-1"></i>Manual</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="badge badge-{{ $badge }}">
                                    {{ $st === 'approved' ? 'Aprovado' : ($st === 'rejected' ? 'Recusado' : 'Pendente') }}
                                </span>
                            </td>

                            {{-- Toggle ativo --}}
                            <td>
                                <form method="POST" action="{{ route('admin.testimonials.toggle', $t) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="__toggle" value="1">
                                        <input type="checkbox" class="custom-control-input js-toggle-submit"
                                            id="active-{{ $t->id }}"
                                            {{ $t->is_active ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="active-{{ $t->id }}"></label>
                                    </div>
                                </form>
                            </td>

                            {{-- Ações --}}
                            <td class="text-right" style="white-space:nowrap">
                                <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-sm btn-secondary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                @if($t->status !== 'approved')
                                    <form method="POST" action="{{ route('admin.testimonials.approve', $t) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Aprovar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif

                                @if($t->status !== 'rejected')
                                    <button type="button" class="btn btn-sm btn-warning btn-reject-testimonial" title="Recusar"
                                        data-action="{{ route('admin.testimonials.reject', $t) }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif

                                <button type="button" class="btn btn-sm btn-danger btn-delete" title="Excluir"
                                    data-action="{{ route('admin.testimonials.destroy', $t) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Nenhum depoimento encontrado.</td></tr>
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
    // Toggle ativo: submete o form pai ao mudar o checkbox
    $(document).on('change', '.js-toggle-submit', function () {
        $(this).closest('form').submit();
    });

    // Recusar (modal SweetAlert) - usa o global AJAX handler
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
        }).then(function (result) {
            if (!result.isConfirmed) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = action;
            form.innerHTML =
                '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                '<input type="hidden" name="moderation_notes" value="' + (result.value || '').replace(/"/g, '&quot;') + '">';
            document.body.appendChild(form);
            window.UNNAjaxGlobal.submitForm(form);
        });
    });

    // Excluir (confirmação) - usa o global AJAX handler
    $(document).on('click', '.btn-delete', function () {
        const action = $(this).data('action');
        Swal.fire({
            title: 'Excluir depoimento?',
            text: 'Esta acao nao pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            confirmButtonText: 'Excluir',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = action;
            form.innerHTML =
                '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                '<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            window.UNNAjaxGlobal.submitForm(form);
        });
    });
</script>
@endpush

