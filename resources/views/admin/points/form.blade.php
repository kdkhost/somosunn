@extends('admin.layouts.app')

@section('page_title', $rule->id ? 'Editar Regra' : 'Nova Regra')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.points-rules.index') }}">Pontuação</a></li>
<li class="breadcrumb-item active">{{ $rule->id ? 'Editar' : 'Nova' }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-star mr-2"></i>
                    {{ $rule->id ? 'Editar Regra de Pontuação' : 'Nova Regra de Pontuação' }}
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ $rule->id ? route('admin.points-rules.update', $rule) : route('admin.points-rules.store') }}">
                    @csrf
                    @if($rule->id)
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="key">Chave (identificador) <span class="text-danger">*</span></label>
                                <input type="text" name="key" id="key" class="form-control @error('key') is-invalid @enderror" 
                                    value="{{ old('key', $rule->key) }}" 
                                    {{ $rule->id ? 'readonly' : '' }}
                                    placeholder="ex: complete_course" required>
                                <small class="text-muted">Use apenas letras, números e underscores</small>
                                @error('key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @if($hasCategory ?? false)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Categoria</label>
                                <select name="category" id="category" class="form-control @error('category') is-invalid @enderror">
                                    <option value="">Selecione...</option>
                                    @foreach($categories as $key => $cat)
                                        <option value="{{ $key }}" {{ old('category', $rule->category ?? '') == $key ? 'selected' : '' }}>
                                            {{ $cat['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @else
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="points">Pontos <span class="text-danger">*</span></label>
                                <input type="number" name="points" id="points" class="form-control @error('points') is-invalid @enderror" 
                                    value="{{ old('points', $rule->points ?? 10) }}" required>
                                @error('points')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="label">Rótulo <span class="text-danger">*</span></label>
                        <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" 
                            value="{{ old('label', $rule->label) }}" 
                            placeholder="ex: Concluir um curso" required>
                        @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($hasCategory ?? false)
                    <div class="form-group">
                        <label for="description">Descrição</label>
                        <input type="text" name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                            value="{{ old('description', $rule->description ?? '') }}" 
                            placeholder="Descrição detalhada da ação que concede os pontos">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="points">Pontos <span class="text-danger">*</span></label>
                                <input type="number" name="points" id="points" class="form-control @error('points') is-invalid @enderror" 
                                    value="{{ old('points', $rule->points ?? 10) }}" required>
                                <small class="text-muted">Use valores negativos para punições</small>
                                @error('points')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="icon">Ícone (FontAwesome)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i id="iconPreview" class="{{ $rule->icon ?? 'fas fa-star' }}"></i></span>
                                    </div>
                                    <input type="text" name="icon" id="icon" class="form-control" 
                                        value="{{ old('icon', $rule->icon ?? '') }}" 
                                        placeholder="fas fa-star">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="max_daily">Limite diário</label>
                                <input type="number" name="max_daily" id="max_daily" class="form-control" 
                                    value="{{ old('max_daily', $rule->max_daily ?? '') }}" 
                                    min="1" placeholder="Sem limite">
                                <small class="text-muted">Deixe vazio para ilimitado</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="active" id="active" class="custom-control-input" 
                                        {{ old('active', $rule->active ?? true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="active">
                                        <strong>Regra ativa</strong>
                                        <br><small class="text-muted">Desative para pausar temporariamente</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="repeatable" id="repeatable" class="custom-control-input" 
                                        {{ old('repeatable', $rule->repeatable ?? false) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="repeatable">
                                        <strong>Repetível</strong>
                                        <br><small class="text-muted">Pode ser ganho múltiplas vezes</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    {{-- Versão simples sem campos avançados --}}
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="active" id="active" class="custom-control-input" 
                                {{ old('active', $rule->active ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="active">
                                <strong>Regra ativa</strong>
                            </label>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.points-rules.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>Voltar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>{{ $rule->id ? 'Salvar alterações' : 'Criar regra' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title mb-0"><i class="fas fa-lightbulb mr-2"></i>Dicas</h3>
            </div>
            <div class="card-body">
                <h6><i class="fas fa-key mr-2"></i>Chaves sugeridas:</h6>
                <ul class="small text-muted mb-3">
                    <li><code>signup</code> - Cadastro na plataforma</li>
                    <li><code>daily_login</code> - Login diário</li>
                    <li><code>complete_course</code> - Concluir curso</li>
                    <li><code>complete_lesson</code> - Concluir aula</li>
                    <li><code>attend_event</code> - Participar de evento</li>
                    <li><code>comment</code> - Comentar</li>
                    <li><code>like</code> - Curtir</li>
                    <li><code>share</code> - Compartilhar</li>
                    <li><code>referral</code> - Indicar amigo</li>
                    <li><code>review</code> - Avaliar conteúdo</li>
                </ul>

                <h6><i class="fas fa-palette mr-2"></i>Ícones populares:</h6>
                <div class="small text-muted">
                    <code>fas fa-star</code><br>
                    <code>fas fa-trophy</code><br>
                    <code>fas fa-medal</code><br>
                    <code>fas fa-crown</code><br>
                    <code>fas fa-gift</code><br>
                    <code>fas fa-heart</code><br>
                    <code>fas fa-fire</code>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('icon').addEventListener('input', function() {
    document.getElementById('iconPreview').className = this.value || 'fas fa-star';
});
</script>
@endpush