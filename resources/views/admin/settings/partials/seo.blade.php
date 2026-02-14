<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-search-plus mr-2"></i> Otimize seu site para buscadores (Google, Bing) e configure scripts de
        rastreamento (Analytics, Pixel).
    </div>

    <h5 class="text-primary mb-3"><i class="fas fa-globe mr-2"></i> Metadados (Google & Redes Sociais)</h5>

    <div class="form-group">
        <label>Descrição do Site (Meta Description)</label>
        <textarea name="site_description" class="form-control" rows="3"
            placeholder="Breve descrição do seu negócio para aparecer nos resultados de busca...">{{ $settings['site_description'] ?? '' }}</textarea>
        <small class="form-text text-muted">Recomendado: entre 150 e 160 caracteres.</small>
    </div>

    <div class="form-group">
        <label>Palavras-Chave (Meta Keywords)</label>
        <input type="text" name="site_keywords" class="form-control" value="{{ $settings['site_keywords'] ?? '' }}"
            placeholder="curso, mentoria, comunidade, networking">
        <small class="form-text text-muted">Separe as palavras por vírgula.</small>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-share-alt mr-2"></i> Imagens de Compartilhamento (Social Media)</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label class="font-weight-bold">OpenGraph (Facebook/WhatsApp/LinkedIn)</label>
            <div class="upload-box" data-remove-input="#remove_seo_og_image"
                data-existing-url="{{ $getUrl('seo_og_image') }}">
                <input type="file" name="seo_og_image" class="d-none" accept="image/*">
                <input type="hidden" name="remove_seo_og_image" id="remove_seo_og_image" value="0">
                <div class="upload-preview mb-2 text-center">
                    @if($url = $getUrl('seo_og_image'))
                        <img src="{{ $url }}" class="img-fluid rounded border"
                            style="max-height: 200px; width: 100%; object-fit: cover;">
                    @else
                        <div class="text-muted p-5 border rounded bg-light">
                            <i class="fab fa-facebook fa-3x mb-2"></i><br>1200x630px
                        </div>
                    @endif
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                            class="fas fa-upload"></i> Selecionar Imagem</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger upload-remove {{ $getUrl('seo_og_image') ? '' : 'd-none' }}"><i
                            class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
        <div class="col-md-6 form-group">
            <label class="font-weight-bold">Twitter Card</label>
            <div class="upload-box" data-remove-input="#remove_seo_twitter_image"
                data-existing-url="{{ $getUrl('seo_twitter_image') }}">
                <input type="file" name="seo_twitter_image" class="d-none" accept="image/*">
                <input type="hidden" name="remove_seo_twitter_image" id="remove_seo_twitter_image" value="0">
                <div class="upload-preview mb-2 text-center">
                    @if($url = $getUrl('seo_twitter_image'))
                        <img src="{{ $url }}" class="img-fluid rounded border"
                            style="max-height: 200px; width: 100%; object-fit: cover;">
                    @else
                        <div class="text-muted p-5 border rounded bg-light">
                            <i class="fab fa-twitter fa-3x mb-2"></i><br>1200x600px
                        </div>
                    @endif
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                            class="fas fa-upload"></i> Selecionar Imagem</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger upload-remove {{ $getUrl('seo_twitter_image') ? '' : 'd-none' }}"><i
                            class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-code mr-2"></i> Scripts de Rastreamento</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Scripts do Header (&lt;head&gt;)</label>
            <textarea name="tracking_head" class="form-control code-editor" rows="6"
                placeholder="<!-- Google Tag Manager / Analytics / Facebook Pixel -->">{{ $settings['tracking_head'] ?? '' }}</textarea>
            <small class="form-text text-muted">Insira scripts que precisam ser carregados no início da página.</small>
        </div>
        <div class="col-md-6 form-group">
            <label>Scripts do Body (&lt;body&gt;)</label>
            <textarea name="tracking_body" class="form-control code-editor" rows="6"
                placeholder="<!-- Chat Scripts / NoScript Tags -->">{{ $settings['tracking_body'] ?? '' }}</textarea>
            <small class="form-text text-muted">Insira scripts que podem ser carregados no final da página.</small>
        </div>
    </div>
</div>