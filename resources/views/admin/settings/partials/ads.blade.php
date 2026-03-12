<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-ad mr-2"></i> Configure a monetizacao do seu conteudo. Voce pode usar Google AdSense ou codigos
        de publicidade personalizados.
    </div>

    <div class="form-group mb-4">
        <div class="custom-control custom-switch custom-switch-lg custom-switch-off-danger custom-switch-on-success">
            <input type="hidden" name="ads_enabled" value="0">
            <input type="checkbox" class="custom-control-input" id="ads_enabled" name="ads_enabled" value="1" {{ ($settings['ads_enabled'] ?? 0) ? 'checked' : '' }}>
            <label class="custom-control-label font-weight-bold" for="ads_enabled">Ativar exibicao de anuncios</label>
        </div>
        <small class="form-text text-muted ml-5">Quando desativado, nenhum anuncio sera carregado, independentemente das
            configuracoes abaixo.</small>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fab fa-google mr-2"></i> Google AdSense</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Publisher ID (Pub-ID)</label>
                        <input type="text" name="adsense_publisher_id" class="form-control"
                            value="{{ $settings['adsense_publisher_id'] ?? '' }}" placeholder="ca-pub-000000000000">
                        <small class="form-text text-muted">
                            Pegue isso no AdSense em <strong>Conta &gt; Configuracoes &gt; Informacoes da conta</strong>.
                            <a href="https://support.google.com/adsense/answer/105516?hl=pt-BR" target="_blank" rel="noopener noreferrer">Ajuda oficial</a>
                            ou
                            <a href="https://www.google.com/adsense/start/" target="_blank" rel="noopener noreferrer">abrir AdSense</a>.
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Slot ID</label>
                        <input type="text" name="adsense_slot_id" class="form-control"
                            value="{{ $settings['adsense_slot_id'] ?? '' }}" placeholder="1234567890">
                        <small class="form-text text-muted">
                            Crie ou abra uma unidade de anuncio no AdSense e copie o numero de
                            <code>data-ad-slot</code> do codigo gerado. Exemplo:
                            <code>data-ad-slot="1234567890"</code>.
                            <a href="https://support.google.com/adsense/answer/9190028?hl=pt-BR" target="_blank" rel="noopener noreferrer">Ajuda oficial</a>
                            ou
                            <a href="https://www.google.com/adsense/start/" target="_blank" rel="noopener noreferrer">abrir AdSense</a>.
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Formato do anuncio</label>
                        <select name="adsense_format" class="form-control">
                            @php($adsFormat = $settings['adsense_format'] ?? 'auto')
                            <option value="auto" {{ $adsFormat === 'auto' ? 'selected' : '' }}>Automatico (Responsivo)</option>
                            <option value="fluid" {{ $adsFormat === 'fluid' ? 'selected' : '' }}>Fluxo (In-feed)</option>
                            <option value="rectangle" {{ $adsFormat === 'rectangle' ? 'selected' : '' }}>Retangulo</option>
                            <option value="horizontal" {{ $adsFormat === 'horizontal' ? 'selected' : '' }}>Horizontal (Banner)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-secondary h-100">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-code mr-2"></i> Codigo personalizado / outras redes</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>HTML/Javascript (Global)</label>
                        <textarea class="form-control code-editor js-encoded-setting-source" rows="9"
                            data-encoded-target="ads_code_html_encoded"
                            placeholder="<!-- Cole aqui o script do seu ad network -->">{{ $settings['ads_code_html'] ?? '' }}</textarea>
                        <input type="hidden" name="ads_code_html_encoded" value="">
                        <small class="text-muted">Use este campo se nao estiver usando a integracao nativa do AdSense.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-stream mr-2"></i> Anuncios no feed (Comunidade)</h5>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group mb-3">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                    <input type="hidden" name="ads_inter_feed_enabled" value="0">
                    <input type="checkbox" class="custom-control-input" id="ads_inter_feed_enabled"
                        name="ads_inter_feed_enabled" value="1" {{ ($settings['ads_inter_feed_enabled'] ?? 0) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="ads_inter_feed_enabled">Habilitar anuncios entre postagens</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label>Frequencia (a cada X posts)</label>
            <select name="adsense_frequency" class="form-control">
                @php($adsFreq = (int) ($settings['adsense_frequency'] ?? 5))
                <option value="3" {{ $adsFreq === 3 ? 'selected' : '' }}>A cada 3 posts</option>
                <option value="5" {{ $adsFreq === 5 ? 'selected' : '' }}>A cada 5 posts</option>
                <option value="10" {{ $adsFreq === 10 ? 'selected' : '' }}>A cada 10 posts</option>
                <option value="15" {{ $adsFreq === 15 ? 'selected' : '' }}>A cada 15 posts</option>
            </select>
        </div>
        <div class="col-md-8 form-group">
            <label>Codigo especifico para o feed (Opcional)</label>
            <textarea class="form-control js-encoded-setting-source" rows="2"
                data-encoded-target="ads_inter_feed_code_encoded"
                placeholder="Se vazio, usara o global/AdSense configurado acima.">{{ $settings['ads_inter_feed_code'] ?? '' }}</textarea>
            <input type="hidden" name="ads_inter_feed_code_encoded" value="">
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                function encodeBase64Url(value) {
                    if (!value) {
                        return '';
                    }

                    if (window.TextEncoder) {
                        const bytes = new TextEncoder().encode(value);
                        let binary = '';

                        bytes.forEach(function (byte) {
                            binary += String.fromCharCode(byte);
                        });

                        return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
                    }

                    return btoa(unescape(encodeURIComponent(value))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
                }

                function syncEncodedFields(root) {
                    (root || document).querySelectorAll('.js-encoded-setting-source[data-encoded-target]').forEach(function (source) {
                        const targetName = source.dataset.encodedTarget;
                        const form = source.closest('form');

                        if (!targetName || !form) {
                            return;
                        }

                        const hidden = form.querySelector('input[name="' + targetName + '"]');
                        if (!hidden) {
                            return;
                        }

                        hidden.value = encodeBase64Url(source.value || '');
                    });
                }

                document.addEventListener('input', function (event) {
                    if (event.target.matches('.js-encoded-setting-source[data-encoded-target]')) {
                        syncEncodedFields(document);
                    }
                });

                document.addEventListener('DOMContentLoaded', function () {
                    syncEncodedFields(document);

                    document.querySelectorAll('form[action$="/settings"]').forEach(function (form) {
                        form.addEventListener('submit', function () {
                            syncEncodedFields(form);
                        });
                    });
                });
            })();
        </script>
    @endpush
@endonce
