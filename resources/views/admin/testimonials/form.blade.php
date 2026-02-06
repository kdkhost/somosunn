@extends('admin.layouts.app')

@section('page_title','Editar depoimento')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.testimonials.index') }}">Depoimentos</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

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
                    <select name="rating" class="form-control">
                        <option value="">—</option>
                        @for($i=1;$i<=5;$i++)
                            <option value="{{ $i }}" {{ (string) old('rating', $testimonial->rating) === (string) $i ? 'selected' : '' }}>{{ $i }}/5</option>
                        @endfor
                    </select>
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

