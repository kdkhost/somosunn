@extends('admin.layouts.app')

@section('page_title','Editar depoimento')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.testimonials.index') }}">Depoimentos</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@push('styles')
    <style>
        .unn-star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 6px;
        }

        .unn-star-rating input {
            display: none;
        }

        .unn-star-rating label {
            cursor: pointer;
            color: #cbd5e1;
            font-size: 22px;
            line-height: 1;
            transition: color 0.15s ease;
            margin: 0;
        }

        .unn-star-rating input:checked~label,
        .unn-star-rating label:hover,
        .unn-star-rating label:hover~label {
            color: #f59e0b;
        }
    </style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Autor (nome)</label>
                    <input name="author_name" class="form-control" value="{{ old('author_name', $testimonial->author_name) }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Autor (título)</label>
                    <input name="author_title" class="form-control" value="{{ old('author_title', $testimonial->author_title) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Avaliação</label>
                    @php
                        $oldRating = old('rating', $testimonial->rating);
                        $oldRating = is_numeric($oldRating) ? (int) $oldRating : null;
                        if ($oldRating !== null) {
                            $oldRating = max(1, min(5, $oldRating));
                        }
                    @endphp

                    <div class="d-flex align-items-center flex-wrap" style="gap: 14px;">
                        <div class="unn-star-rating" role="radiogroup" aria-label="Avaliação por estrelas">
                            @for($i = 5; $i >= 1; $i--)
                                <input type="radio" id="admin-testimonial-rating-{{ $i }}" name="rating"
                                    value="{{ $i }}" {{ (string) $oldRating === (string) $i ? 'checked' : '' }}>
                                <label for="admin-testimonial-rating-{{ $i }}" title="{{ $i }}/5">
                                    <i class="fas fa-star"></i>
                                </label>
                            @endfor
                        </div>
                        <div class="text-muted small">
                            <input type="radio" id="admin-testimonial-rating-none" name="rating" value=""
                                {{ $oldRating === null ? 'checked' : '' }} class="d-none">
                            <label for="admin-testimonial-rating-none" class="mb-0" style="cursor:pointer; text-decoration: underline;">
                                Sem avaliação
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label>Status</label>
                    <input class="form-control" value="{{ $testimonial->status }}" disabled>
                </div>
                <div class="form-group col-md-6">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $testimonial->is_featured) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_featured">Marcar como destaque</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Depoimento</label>
                <textarea name="content" rows="6" class="form-control" required>{{ old('content', $testimonial->content) }}</textarea>
            </div>

            <div class="text-right">
                <a href="{{ route('admin.testimonials.index') }}" class="btn btn-secondary" data-pjax>Voltar</a>
                <button class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
