<div class="card-body">
    <h5 class="text-primary mb-3"><i class="fas fa-search-plus mr-2"></i>SEO & Analytics</h5>

    <div class="form-group">
        <label>Descrição do Site (Meta Description)</label>
        <textarea name="site_description" class="form-control" rows="3"
            placeholder="Descrição curta do seu site para o Google...">{{ $settings['site_description'] ?? '' }}</textarea>
    </div>

    <div class="form-group">
        <label>Palavras-Chave (Keywords)</label>
        <input name="site_keywords" class="form-control" value="{{ $settings['site_keywords'] ?? '' }}"
            placeholder="curso, mentoria, comunidade, networking">
        <small class="text-muted">Separe por vírgulas.</small>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label>Imagem OpenGraph (Facebook/WhatsApp)</label>
            <div class="upload-box" data-max-size="{{ 2 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('seo_og_image') }}" data-remove-input="[name='remove_seo_og_image']">
                <input type="hidden" name="remove_seo_og_image" value="0">
                <input type="file" name="seo_og_image" accept="image/*" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
            <small class="text-muted">Recomendado: 1200x630px</small>
        </div>
        <div class="col-md-6 form-group">
            <label>Imagem Twitter Card</label>
            <div class="upload-box" data-max-size="{{ 2 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('seo_twitter_image') }}"
                data-remove-input="[name='remove_seo_twitter_image']">
                <input type="hidden" name="remove_seo_twitter_image" value="0">
                <input type="file" name="seo_twitter_image" accept="image/*" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
            <small class="text-muted">Recomendado: 1200x600px</small>
        </div>
    </div>

    <div class="form-group">
        <label>Scripts Header (GTM, GA4, Pixel)</label>
        <textarea name="tracking_head" class="form-control" rows="4"
            placeholder="<script>...</script>">{{ $settings['tracking_head'] ?? '' }}</textarea>
    </div>
    <div class="form-group">
        <label>Scripts Body (Noscript, Chat)</label>
        <textarea name="tracking_body" class="form-control" rows="4"
            placeholder="<script>...</script>">{{ $settings['tracking_body'] ?? '' }}</textarea>
    </div>
</div>