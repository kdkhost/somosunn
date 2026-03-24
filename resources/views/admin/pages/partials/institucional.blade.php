{{-- Partial: institucional --}}
{{-- Uso atual: paginas legais editaveis via body_content --}}

@php
    $contentField = 'body_content';
    $contentValue = old($contentField, $data[$contentField] ?? $data['content'] ?? '');
@endphp

<div class="tab-pane fade" id="sec-identity">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title text-capitalize"><i class="fas fa-heading mr-1"></i> {{ str_replace('-', ' ', $page->slug) }} - Cabecalho</h3>
        </div>
        <div class="card-body py-4 bg-light">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-group">
                        <label>Titulo da Pagina (Heading H1)</label>
                        <input type="text" name="hero_title" class="form-control form-control-lg text-center"
                            value="{{ old('hero_title', $data['hero_title'] ?? $page->title) }}">
                        <small class="text-muted">Este e o titulo principal que aparece no topo da pagina.</small>
                    </div>

                    <div class="form-group mb-0">
                        <label>Subtitulo da Pagina</label>
                        <input type="text" name="hero_subtitle" class="form-control text-center"
                            value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}">
                        <small class="text-muted">Texto de apoio exibido logo abaixo do titulo principal.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-content">
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Conteudo da Pagina</h3>
        </div>
        <div class="card-body p-0">
            <textarea name="{{ $contentField }}" class="summernote" id="editor-institucional">{{ $contentValue }}</textarea>
        </div>
    </div>
</div>
