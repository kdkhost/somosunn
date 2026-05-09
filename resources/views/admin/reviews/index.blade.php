@extends('admin.layouts.app')

@section('page_title', 'Avaliacoes')
@section('breadcrumb')<li class="breadcrumb-item active">Avaliacoes</li>@endsection

@section('content')
    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-primary elevation-1">
                <span class="info-box-icon"><i class="fas fa-star"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total</span>
                    <span class="info-box-number">{{ $items->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-success elevation-1">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Aprovadas</span>
                    <span class="info-box-number">{{ $items->where('status', 'approved')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-warning elevation-1">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendentes</span>
                    <span class="info-box-number">{{ $items->where('status', 'pending')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-danger elevation-1">
                <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Recusadas</span>
                    <span class="info-box-number">{{ $items->where('status', 'rejected')->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-star text-warning mr-2"></i>Avaliacoes (cursos e mentorias)
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="form-row">
                    <div class="col-md-3 mb-2">
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Todos os status</option>
                            <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pendentes</option>
                            <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Aprovadas</option>
                            <option value="rejected" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Recusadas</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="type" class="form-control form-control-sm">
                            <option value="">Todos os tipos</option>
                            <option value="course" {{ ($type ?? '') === 'course' ? 'selected' : '' }}>Cursos</option>
                            <option value="mentorship" {{ ($type ?? '') === 'mentorship' ? 'selected' : '' }}>Mentorias</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input name="q" class="form-control form-control-sm" value="{{ $q ?? '' }}"
                            placeholder="Buscar por item, usuario ou comentario">
                    </div>
                    <div class="col-md-2 mb-2">
                        <button class="btn btn-primary btn-sm btn-block rounded-pill elevation-1">
                            <i class="fas fa-search mr-1"></i>Filtrar
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Item</th>
                            <th>Usuario</th>
                            <th>Avaliacao</th>
                            <th>Comentario</th>
                            <th>Status</th>
                            <th class="text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $review)
                            @php
                                $reviewType = $review->reviewable_type === \App\Models\Course::class ? 'Curso' : 'Mentoria';
                                $reviewTitle = $review->reviewable->title ?? 'Item removido';
                                $statusName = $review->status === 'approved' ? 'Aprovada' : ($review->status === 'rejected' ? 'Recusada' : 'Pendente');
                                $statusClass = $review->status === 'approved' ? 'success' : ($review->status === 'rejected' ? 'danger' : 'warning');
                                $statusIcon = $review->status === 'approved' ? 'fa-check' : ($review->status === 'rejected' ? 'fa-times' : 'fa-clock');
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $reviewTitle }}</div>
                                    <span class="badge badge-light border"><i class="fas fa-tag mr-1"></i>{{ $reviewType }}</span>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $review->user->name ?? 'Usuario removido' }}</div>
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
                                    <span class="badge badge-{{ $statusClass }}">
                                        <i class="fas {{ $statusIcon }} mr-1"></i>{{ $statusName }}
                                    </span>
                                    @if($review->moderator)
                                        <div class="text-muted small mt-1">por {{ $review->moderator->name }}</div>
                                    @endif
                                </td>
                                <td class="text-right" style="white-space:nowrap">
                                    @if($review->status !== 'approved')
                                        <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill" title="Aprovar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($review->status !== 'rejected')
                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill btn-reject-review"
                                            data-action="{{ route('admin.reviews.reject', $review) }}" title="Recusar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-delete"
                                        data-action="{{ route('admin.reviews.destroy', $review) }}" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-star fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Nenhuma avaliacao encontrada.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.btn-reject-review', function () {
            const action = $(this).data('action');
            Swal.fire({
                title: 'Recusar avaliacao?',
                input: 'textarea',
                inputLabel: 'Motivo (opcional)',
                inputPlaceholder: 'Ex.: comentario fora de contexto...',
                showCancelButton: true,
                confirmButtonText: 'Recusar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#f0ad4e'
            }).then((result) => {
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

        $(document).on('click', '.btn-delete', function () {
            const action = $(this).data('action');
            Swal.fire({
                title: 'Excluir avaliacao?',
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
