{{-- Partial: institucional --}}
{{-- $data = $page->data ?? [] --}}

<div class="tab-pane fade" id="sec-identity">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title text-capitalize"><i class="fas fa-heading mr-1"></i> {{ str_replace('-', ' ', $page->slug) }} - Cabeçalho</h3>
        </div>
        <div class="card-body text-center py-4 bg-light">
             <div class="form-group mb-0 mx-auto" style="max-width: 600px;">
                <label>Título da Página (Heading H1)</label>
                <input type="text" name="hero_title" class="form-control form-control-lg text-center" 
                    value="{{ old('hero_title', $data['hero_title'] ?? $page->title) }}">
                <small class="text-muted">Este é o título principal que aparece no topo da página.</small>
             </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-content">
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Conteúdo da Página</h3>
        </div>
        <div class="card-body p-0">
            <textarea name="content" class="summernote" id="editor-institucional">{{ old('content', $data['content'] ?? '') }}</textarea>
        </div>
    </div>
</div>
