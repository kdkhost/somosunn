@extends('admin.layouts.app')

@section('page_title', 'Avaliações')
@section('breadcrumb')<li class="breadcrumb-item active">Avaliações</li>@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap mb-3">
                <h3 class="m-0">Avaliações (cursos e mentorias)</h3>
            </div>

            <form method="GET" class="mb-3">
                <div class="form-row">
                    <div class="col-md-3 mb-2">
                        <select name="status" class="form-control">
                            <option value="">Todos os status</option>
                            <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pendentes</option>
                            <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Aprovadas</option>
                            <option value="rejected" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Recusadas</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="type" class="form-control">
                            <option value="">Todos os tipos</option>
                            <option value="course" {{ ($type ?? '') === 'course' ? 'selected' : '' }}>Cursos</option>
                            <option value="mentorship" {{ ($type ?? '') === 'mentorship' ? 'selected' : '' }}>Mentorias</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input name="q" class="form-control" value="{{ $q ?? '' }}"
                            placeholder="Buscar por item, usuário ou comentário">
                    </div>
                    <div class="col-md-2 mb-2">
                        <button class="btn btn-primary btn-block">Filtrar</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Usuário</th>
                            <th>Avaliação</th>
                            <th>Comentário</th>
                            <th>Status</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $review)
                            @php
                                $reviewType = $review->reviewable_type === \App\Models\Course::class ? 'Curso' : 'Mentoria';
                                $reviewTitle = $review->reviewable->title ?? 'Item removido';
                                $statusName = $review->status === 'approved' ? 'Aprovada' : ($review->status === 'rejected' ? 'Recusada' : 'Pendente');
                                $statusClass = $review->status === 'approved' ? 'success' : ($review->status === 'rejected' ? 'danger' : 'warning');
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $reviewTitle }}</div>
                                    <span class="badge badge-light">{{ $reviewType }}</span>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $review->user->name ?? 'Usuário removido' }}</div>
                                    <div class="text-muted small">{{ $review->user->email ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $review->rating }}/5</div>
                                    <div class="text-warning" style="letter-spacing: 1px;">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                        @endfor
                                    </div>
                                </td>
                                <td class="text-muted">
                                    {{ \Illuminate\Support\Str::limit($review->comment, 140) }}
                                    @if($review->moderation_notes)
                                        <div class="small text-danger mt-1">
                                            <i class="fas fa-comment-dots mr-1"></i>{{ $review->moderation_notes }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $statusClass }}">{{ $statusName }}</span>
                                    @if($review->moderator)
                                        <div class="text-muted small mt-1">por {{ $review->moderator->name }}</div>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($review->status !== 'approved')
                                        <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Aprovar</button>
                                        </form>
                                    @endif

                                    @if($review->status !== 'rejected')
                                        <button type="button" class="btn btn-sm btn-warning btn-reject-review"
                                            data-action="{{ route('admin.reviews.reject', $review) }}">
                                            Recusar
                                        </button>
                                    @endif

                                    <a href="#" class="btn btn-sm btn-danger btn-delete"
                                        data-action="{{ route('admin.reviews.destroy', $review) }}">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhuma avaliação encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $items->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.btn-reject-review', function () {
            const action = $(this).data('action');
            Swal.fire({
                title: 'Recusar avaliação?',
                input: 'textarea',
                inputLabel: 'Motivo (opcional)',
                inputPlaceholder: 'Ex.: comentário fora de contexto...',
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
