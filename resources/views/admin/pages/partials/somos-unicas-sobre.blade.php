{{-- Partial: somos-unicas-sobre --}}
{{-- $data = $page->data ?? [] --}}

<div class="tab-pane fade" id="sec-identity">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-palette mr-1"></i> Identidade Visual</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título principal (Hero)</label>
                <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $data['hero_title'] ?? '') }}">
            </div>
            <div class="form-group mb-0">
                <label>Subtítulo descrição</label>
                <textarea name="hero_subtitle" rows="3" class="form-control">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-content">
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Conteúdo Rico</h3>
        </div>
        <div class="card-body p-0">
            <textarea name="content" class="summernote">{{ old('content', $data['content'] ?? '') }}</textarea>
        </div>
    </div>
</div>
