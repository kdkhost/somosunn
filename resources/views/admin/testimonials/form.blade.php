@extends('admin.layouts.app')

@php $isCreating = !$testimonial->exists; @endphp

@section('page_title', $isCreating ? 'Novo depoimento' : 'Editar depoimento')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.testimonials.index') }}">Depoimentos</a></li>
    <li class="breadcrumb-item active">{{ $isCreating ? 'Novo' : 'Editar' }}</li>
@endsection

@push('styles')
    <style>
        .unn-star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 6px;
        }
        .unn-star-rating input { display: none; }
        .unn-star-rating label {
            cursor: pointer;
            color: #cbd5e1;
            font-size: 22px;
            line-height: 1;
            transition: color 0.15s ease;
            margin: 0;
        }
        .unn-star-rating input:checked ~ label,
        .unn-star-rating label:hover,
        .unn-star-rating label:hover ~ label { color: #f59e0b; }
        .google-badge { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; }
    </style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">

                {{-- Badge de origem (somente edição) --}}
                @if(!$isCreating)
                    <div class="mb-3">
                        @if($testimonial->source === 'google')
                            <span class="badge badge-info px-3 py-2">
                                <i class="fab fa-google mr-1"></i> Importado do Google
                            </span>
                        @else
                            <span class="badge badge-secondary px-3 py-2">
                                <i class="fas fa-pencil-alt mr-1"></i> Depoimento manual
                            </span>
                        @endif
                        @if($testimonial->user_id && $testimonial->user)
                            <span class="ml-2 text-muted small">
                                <i class="fas fa-user-circle mr-1"></i>Membro: <strong>{{ $testimonial->user->name }}</strong>
                            </span>
                        @endif
                    </div>
                @endif

                @php
                    $formAction = $isCreating
                        ? route('admin.testimonials.store')
                        : route('admin.testimonials.update', $testimonial);
                @endphp

                <form method="POST" action="{{ $formAction }}">
                    @csrf
                    @if(!$isCreating) @method('PUT') @endif

                    {{-- Vincular a membro (opcional) --}}
                    <div class="form-group">
                        <label>Membro vinculado <small class="text-muted">(opcional — preenche nome/avatar automaticamente)</small></label>
                        <select name="user_id" class="form-control">
                            <option value="">— Não vincular a membro —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}"
                                    {{ old('user_id', $testimonial->user_id ?? '') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Quando vinculado, o avatar do membro é exibido automaticamente no site.
                        </small>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nome de exibição</label>
                            <input name="author_name" class="form-control"
                                placeholder="Deixe em branco para usar o nome do membro"
                                value="{{ old('author_name', $testimonial->author_name) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Título / cargo</label>
                            <input name="author_title" class="form-control"
                                placeholder="Ex.: CEO, TechStartup"
                                value="{{ old('author_title', $testimonial->author_title) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Depoimento <span class="text-danger">*</span></label>
                        <textarea name="content" rows="5" class="form-control @error('content') is-invalid @enderror"
                            required placeholder="Texto do depoimento…">{{ old('content', $testimonial->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row align-items-center">
                        {{-- Avaliação em estrelas --}}
                        <div class="form-group col-md-5">
                            <label>Avaliação</label>
                            @php
                                $oldRating = old('rating', $testimonial->rating);
                                $oldRating = is_numeric($oldRating) ? (int) $oldRating : null;
                                $oldRating = $oldRating ? max(1, min(5, $oldRating)) : null;
                            @endphp
                            <div class="d-flex align-items-center flex-wrap" style="gap: 14px;">
                                <div class="unn-star-rating" role="radiogroup" aria-label="Avaliação">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="t-rating-{{ $i }}" name="rating"
                                            value="{{ $i }}" {{ (string) $oldRating === (string) $i ? 'checked' : '' }}>
                                        <label for="t-rating-{{ $i }}" title="{{ $i }}/5">
                                            <i class="fas fa-star"></i>
                                        </label>
                                    @endfor
                                </div>
                                <div class="text-muted small">
                                    <input type="radio" id="t-rating-none" name="rating" value=""
                                        {{ $oldRating === null ? 'checked' : '' }} class="d-none">
                                    <label for="t-rating-none" class="mb-0" style="cursor:pointer; text-decoration:underline;">
                                        Sem nota
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Destaque --}}
                        <div class="form-group col-md-3">
                            <label class="d-block">&nbsp;</label>
                            <div class="custom-control custom-switch custom-switch-off-secondary custom-switch-on-primary mt-1">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" class="custom-control-input" id="is_featured"
                                    name="is_featured" value="1"
                                    {{ old('is_featured', $testimonial->is_featured) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_featured">Marcar como destaque</label>
                            </div>
                        </div>

                        {{-- Ativo/inativo --}}
                        <div class="form-group col-md-4">
                            <label class="d-block">&nbsp;</label>
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-1">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="custom-control-input" id="is_active"
                                    name="is_active" value="1"
                                    {{ old('is_active', $testimonial->is_active ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Visível no site</label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.testimonials.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> {{ $isCreating ? 'Criar depoimento' : 'Salvar alterações' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Sidebar informativa --}}
    <div class="col-lg-4">
        @if(!$isCreating)
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Informações</h3></div>
                <div class="card-body small text-muted">
                    <p><strong>ID:</strong> #{{ $testimonial->id }}</p>
                    <p><strong>Criado em:</strong> {{ $testimonial->created_at?->format('d/m/Y H:i') }}</p>
                    @if($testimonial->moderated_at)
                        <p><strong>Moderado em:</strong> {{ $testimonial->moderated_at->format('d/m/Y H:i') }}</p>
                        @if($testimonial->moderator)
                            <p><strong>Por:</strong> {{ $testimonial->moderator->name }}</p>
                        @endif
                    @endif
                    @if($testimonial->source === 'google' && $testimonial->external_id)
                        <hr>
                        <p><strong>ID externo Google:</strong><br>
                            <span class="text-break">{{ $testimonial->external_id }}</span>
                        </p>
                    @endif
                </div>
                @if($testimonial->status !== 'approved')
                    <div class="card-footer">
                        <form method="POST" action="{{ route('admin.testimonials.approve', $testimonial) }}">
                            @csrf
                            <button class="btn btn-success btn-sm btn-block">
                                <i class="fas fa-check mr-1"></i> Aprovar e publicar
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endif

        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Dicas</h3></div>
            <div class="card-body small text-muted">
                <ul class="mb-0 pl-3">
                    <li>Vincule ao membro para exibir o avatar automaticamente.</li>
                    <li>Marque como <strong>Destaque</strong> para aparecer primeiro.</li>
                    <li>Depoimentos <strong>inativos</strong> não aparecem no site, mesmo aprovados.</li>
                    <li>Novos depoimentos criados pelo admin já ficam <em>aprovados</em>.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

