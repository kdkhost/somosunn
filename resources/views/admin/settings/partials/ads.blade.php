<div class="card-body">
    <h5 class="text-primary mb-3"><i class="fas fa-ad mr-2"></i>Monetização e Anúncios</h5>
    <div class="alert alert-info">
        Configure aqui os anúncios que aparecem na comunidade e entre lições.
        <br><b>Global:</b> Exibido em todas as páginas (ex: rodapé ou lateral).
        <br><b>Inter-feed:</b> Exibido entre postagens da comunidade (feed).
    </div>

    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
        <input type="hidden" name="ads_enabled" value="0">
        <input type="checkbox" class="custom-control-input" id="ads_enabled" name="ads_enabled" value="1" {{ ($settings['ads_enabled'] ?? 0) ? 'checked' : '' }}>
        <label class="custom-control-label" for="ads_enabled">Ativar Anúncios Globais</label>
    </div>

    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">Google AdSense</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Publisher ID (ca-pub-...)</label>
                    <input name="adsense_publisher_id" class="form-control"
                        value="{{ $settings['adsense_publisher_id'] ?? '' }}" placeholder="ca-pub-000000000000">
                </div>
                <div class="col-md-6 form-group">
                    <label>Slot ID</label>
                    <input name="adsense_slot_id" class="form-control" value="{{ $settings['adsense_slot_id'] ?? '' }}"
                        placeholder="1234567890">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Formato</label>
                    <select name="adsense_format" class="form-control">
                        @php($adsFormat = $settings['adsense_format'] ?? 'auto')
                        <option value="auto" {{ $adsFormat === 'auto' ? 'selected' : '' }}>Automático
                        </option>
                        <option value="fluid" {{ $adsFormat === 'fluid' ? 'selected' : '' }}>In-feed
                            (Fluido)</option>
                        <option value="rectangle" {{ $adsFormat === 'rectangle' ? 'selected' : '' }}>
                            Retângulo</option>
                        <option value="horizontal" {{ $adsFormat === 'horizontal' ? 'selected' : '' }}>
                            Horizontal</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Frequência (Inter-feed)</label>
                    <select name="adsense_frequency" class="form-control">
                        @php($adsFreq = (int) ($settings['adsense_frequency'] ?? 5))
                        <option value="3" {{ $adsFreq === 3 ? 'selected' : '' }}>A cada 3 posts
                        </option>
                        <option value="5" {{ $adsFreq === 5 ? 'selected' : '' }}>A cada 5 posts
                        </option>
                        <option value="10" {{ $adsFreq === 10 ? 'selected' : '' }}>A cada 10 posts
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <label>HTML/JS Personalizado (Global)</label>
        <textarea name="ads_code_html" class="form-control" rows="4"
            placeholder="Cole aqui o código de embed">{{ $settings['ads_code_html'] ?? '' }}</textarea>
        <small class="text-muted">Se usar AdSense acima, este campo pode ficar vazio.</small>
    </div>

    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-3 mb-2">
        <input type="hidden" name="ads_inter_feed_enabled" value="0">
        <input type="checkbox" class="custom-control-input" id="ads_inter_feed_enabled" name="ads_inter_feed_enabled"
            value="1" {{ ($settings['ads_inter_feed_enabled'] ?? 0) ? 'checked' : '' }}>
        <label class="custom-control-label" for="ads_inter_feed_enabled">Exibir anúncios entre
            postagens
            do feed</label>
    </div>

    <div class="form-group">
        <label>HTML/JS Personalizado (Inter-feed)</label>
        <textarea name="ads_inter_feed_code" class="form-control" rows="4"
            placeholder="Código específico para o feed (opcional)">{{ $settings['ads_inter_feed_code'] ?? '' }}</textarea>
    </div>
</div>