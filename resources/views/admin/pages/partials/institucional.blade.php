{{-- Partial: institucional --}}
{{-- Usado para Termos de Uso, Privacidade e LGPD --}}

{{-- Hero Section --}}
<div class="col-12 mb-4" id="sec-identity">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-heading mr-2 text-primary"></i> Cabeçalho (Hero)
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 text-wrap">
                    <div class="form-group mb-md-0">
                        <label class="font-weight-bold" for="hero_title">Título Principal</label>
                        <input type="text" name="hero_title" id="hero_title"
                            class="form-control @error('hero_title') is-invalid @enderror"
                            value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                            placeholder="Ex: Termos de Uso">
                        @error('hero_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6 text-wrap">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold" for="hero_subtitle">Subtítulo (Opcional)</label>
                        <input type="text" name="hero_subtitle" id="hero_subtitle"
                            class="form-control @error('hero_subtitle') is-invalid @enderror"
                            value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}"
                            placeholder="Breve descrição abaixo do título...">
                        @error('hero_subtitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Content Section --}}
<div class="col-12" id="sec-content">
    <div class="card card-outline card-info shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-file-alt mr-2 text-info"></i> Conteúdo da Página
            </h3>
        </div>
        <div class="card-body">
            <div class="form-group mb-0">
                <label class="font-weight-bold" for="body_content">Texto da Página (HTML)</label>
                <textarea name="body_content" id="body_content" class="form-control summernote @error('body_content') is-invalid @enderror"
                    rows="15">{{ old('body_content', $data['body_content'] ?? '') }}</textarea>
                @error('body_content')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small class="text-muted mt-2 d-block italic">
                    <i class="fas fa-info-circle mr-1"></i> Use o editor acima para formatar o texto com títulos, negrito, listas e links.
                </small>
            </div>
        </div>
    </div>
</div>
