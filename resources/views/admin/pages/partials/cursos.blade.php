{{-- Partial: cursos --}}
{{-- $data = $page->data ?? [] --}}

<div class="tab-pane fade" id="sec-hero">
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-graduation-cap mr-1"></i> Hero</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Texto do badge</label>
                <input type="text" name="hero_badge" class="form-control"
                    value="{{ old('hero_badge', $data['hero_badge'] ?? '') }}">
            </div>
            <div class="form-group">
                <label>Título principal</label>
                <input type="text" name="hero_title" class="form-control"
                    value="{{ old('hero_title', $data['hero_title'] ?? '') }}">
            </div>
            <div class="form-group">
                <label>Subtítulo / descrição</label>
                <textarea name="hero_subtitle" rows="2" class="form-control summernote-sm">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
            </div>
            <div class="form-group mb-0">
                <label class="font-weight-bold">Imagem de destaque</label>
                @include('admin.components.upload-global', ['name' => 'hero_image', 'accept' => 'image/*'])
            </div>
        </div>
    </div>
</div>