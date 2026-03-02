@extends('admin.layouts.app')

@section('page_title', 'Editar Página — ' . $page->slug)
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.pages.index') }}">Páginas</a>
    </li>
    <li class="breadcrumb-item active">{{ $page->slug }}</li>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.pages.update', $page) }}">
    @csrf
    @method('PUT')

    <div class="row">

        {{-- Coluna principal --}}
        <div class="col-lg-8">

            {{-- Hero / Conteúdo principal --}}
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-heading mr-1"></i> Hero / Cabeçalho</h3>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label for="hero_title">Título principal</label>
                        <input type="text"
                               id="hero_title"
                               name="hero_title"
                               class="form-control @error('hero_title') is-invalid @enderror"
                               value="{{ old('hero_title', $flat['hero_title']) }}"
                               placeholder="Ex: Bem-vindo à Somos UNN">
                        @error('hero_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="hero_subtitle">Subtítulo / Slogan</label>
                        <input type="text"
                               id="hero_subtitle"
                               name="hero_subtitle"
                               class="form-control @error('hero_subtitle') is-invalid @enderror"
                               value="{{ old('hero_subtitle', $flat['hero_subtitle']) }}"
                               placeholder="Ex: A comunidade que transforma talentos em carreiras.">
                        @error('hero_subtitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="cta_text">Texto do botão (CTA)</label>
                            <input type="text"
                                   id="cta_text"
                                   name="cta_text"
                                   class="form-control @error('cta_text') is-invalid @enderror"
                                   value="{{ old('cta_text', $flat['cta_text']) }}"
                                   placeholder="Ex: Comece agora">
                            @error('cta_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="cta_url">URL do botão (CTA)</label>
                            <input type="text"
                                   id="cta_url"
                                   name="cta_url"
                                   class="form-control @error('cta_url') is-invalid @enderror"
                                   value="{{ old('cta_url', $flat['cta_url']) }}"
                                   placeholder="Ex: /cadastro">
                            @error('cta_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>

            {{-- Body genérico --}}
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-align-left mr-1"></i> Conteúdo / Corpo</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label for="body">Texto principal</label>
                        <textarea id="body"
                                  name="body"
                                  rows="8"
                                  class="form-control @error('body') is-invalid @enderror"
                                  placeholder="Texto livre exibido no corpo da página. Suporta HTML.">{{ old('body', $flat['body']) }}</textarea>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Aceita HTML simples. Use com cuidado.</small>
                    </div>
                </div>
            </div>

        </div>

        {{-- Coluna lateral --}}
        <div class="col-lg-4">

            {{-- Identificação --}}
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tag mr-1"></i> Identificação</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Slug <small class="text-muted">(somente leitura)</small></label>
                        <input type="text" class="form-control" value="{{ $page->slug }}" readonly>
                    </div>
                    <div class="form-group mb-0">
                        <label for="title">Título da página</label>
                        <input type="text"
                               id="title"
                               name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $page->title) }}"
                               placeholder="Nome exibido no painel">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-search mr-1"></i> SEO</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="seo_title">Título SEO <small class="text-muted">(meta title)</small></label>
                        <input type="text"
                               id="seo_title"
                               name="seo_title"
                               class="form-control @error('seo_title') is-invalid @enderror"
                               value="{{ old('seo_title', $flat['seo_title']) }}"
                               placeholder="Ex: Somos UNN — Plataforma de Cursos"
                               maxlength="255">
                        @error('seo_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-0">
                        <label for="seo_description">Descrição SEO <small class="text-muted">(meta description)</small></label>
                        <textarea id="seo_description"
                                  name="seo_description"
                                  rows="3"
                                  maxlength="320"
                                  class="form-control @error('seo_description') is-invalid @enderror"
                                  placeholder="Resumo exibido nos resultados do Google (até 160 caracteres idealmente).">{{ old('seo_description', $flat['seo_description']) }}</textarea>
                        @error('seo_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <span id="seo-desc-count">0</span>/320 caracteres
                        </small>
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="card">
                <div class="card-body d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save mr-1"></i> Salvar alterações
                    </button>
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary btn-block">
                        Cancelar
                    </a>
                </div>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const desc = document.getElementById('seo_description');
    const counter = document.getElementById('seo-desc-count');
    if (desc && counter) {
        const update = () => { counter.textContent = desc.value.length; };
        desc.addEventListener('input', update);
        update();
    }
})();
</script>
@endpush
