@php
    $adsEnabledVal = (string) ($settings['ads_enabled'] ?? '0') === '1';
    $adsOwnNetworkVal = (string) ($settings['ads_own_network_enabled'] ?? '1') !== '0';
    $adsSponsorRequireApprovalVal = (string) ($settings['ads_sponsor_require_approval'] ?? '1') !== '0';
    $adsSplitEnabledVal = (string) ($settings['ads_split_enabled'] ?? '0') === '1';
    $adsSplitSaasPercent = (float) ($settings['ads_split_saas_percent'] ?? 50);
    $adsSplitAdminPercent = round(100 - max(0, min(100, $adsSplitSaasPercent)), 2);
    $adsGlobalBannerVal = (string) ($settings['ads_global_banner_enabled'] ?? '1') !== '0';
    $adsInterFeedVal = (string) ($settings['ads_inter_feed_enabled'] ?? '0') === '1';
    $adsPubId = trim((string) ($settings['adsense_publisher_id'] ?? ''));
    $adsSlotId = trim((string) ($settings['adsense_slot_id'] ?? ''));
    $adsHtml = trim((string) ($settings['ads_code_html'] ?? ''));
    $adsFeedHtml = trim((string) ($settings['ads_inter_feed_code'] ?? ''));
    $activeOwnBannersCount = \App\Models\SponsorBanner::tableAvailable()
        ? \App\Models\SponsorBanner::query()->where('active', true)->count()
        : 0;
    $hasValidAdSource = ($adsPubId !== '' && $adsSlotId !== '') || $adsHtml !== '' || ($adsOwnNetworkVal && $activeOwnBannersCount > 0);
    $adsFormat = $settings['adsense_format'] ?? 'auto';
    $adsFreq = (int) ($settings['adsense_frequency'] ?? 5);
@endphp

<div class="card-body">
    {{-- Barra de Status Real da Monetização --}}
    <div class="unn-section-box mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="badge rounded-pill {{ $adsEnabledVal && $hasValidAdSource ? 'text-bg-success' : ($adsEnabledVal ? 'text-bg-warning' : 'text-bg-secondary') }}">
                        <i class="fas {{ $adsEnabledVal && $hasValidAdSource ? 'fa-check-circle' : ($adsEnabledVal ? 'fa-exclamation-triangle' : 'fa-pause-circle') }} me-1"></i>
                        @if($adsEnabledVal && $hasValidAdSource)
                            Anúncios Ativos e Operando
                        @elseif($adsEnabledVal)
                            Ativo — Falta configurar AdSense ou HTML
                        @else
                            Anúncios Desativados
                        @endif
                    </span>
                    @if($adsPubId !== '')
                        <a href="{{ route('ads.txt') }}" target="_blank" rel="noopener noreferrer" class="badge rounded-pill text-bg-primary text-decoration-none">
                            <i class="fas fa-file-lines me-1"></i> Verificar /ads.txt
                        </a>
                    @endif
                </div>
                <small class="text-secondary d-block">
                    Exibe blocos automaticamente nas páginas públicas e no feed da comunidade (nunca em checkout, login ou /admin).
                </small>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="ads_enabled" value="0">
                    <input type="checkbox" class="form-check-input" id="ads_enabled" name="ads_enabled" value="1" {{ $adsEnabledVal ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="ads_enabled">Ativar exibição de anúncios</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="ads_global_banner_enabled" value="0">
                    <input type="checkbox" class="form-check-input" id="ads_global_banner_enabled" name="ads_global_banner_enabled" value="1" {{ $adsGlobalBannerVal ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="ads_global_banner_enabled">Banner global nas páginas públicas</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card card-outline card-primary h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title fw-bold"><i class="fab fa-google me-2"></i> Google AdSense</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="adsense_publisher_id">Publisher ID (Pub-ID)</label>
                        <input type="text" id="adsense_publisher_id" name="adsense_publisher_id" class="form-control"
                            value="{{ $adsPubId }}" placeholder="ca-pub-0000000000000000">
                        <small class="form-text text-muted">
                            Pegue em <strong>Conta &gt; Configurações &gt; Informações da conta</strong>. Também alimenta automaticamente o arquivo <code>/ads.txt</code>.
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="adsense_slot_id">Slot ID (data-ad-slot)</label>
                        <input type="text" id="adsense_slot_id" name="adsense_slot_id" class="form-control"
                            value="{{ $adsSlotId }}" placeholder="1234567890">
                        <small class="form-text text-muted">
                            Número do atributo <code>data-ad-slot</code> da unidade de anúncio criada no Google AdSense.
                        </small>
                    </div>
                    <div class="form-group mb-0">
                        <label for="adsense_format">Formato do anúncio</label>
                        <select id="adsense_format" name="adsense_format" class="form-select">
                            <option value="auto" {{ $adsFormat === 'auto' ? 'selected' : '' }}>Automático (Responsivo)</option>
                            <option value="fluid" {{ $adsFormat === 'fluid' ? 'selected' : '' }}>Fluxo (In-feed)</option>
                            <option value="rectangle" {{ $adsFormat === 'rectangle' ? 'selected' : '' }}>Retângulo</option>
                            <option value="horizontal" {{ $adsFormat === 'horizontal' ? 'selected' : '' }}>Horizontal (Banner)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-secondary h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title fw-bold"><i class="fas fa-code me-2"></i> Código personalizado / outras redes</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="ads_code_html_source">HTML / Javascript (Banner Global)</label>
                        <textarea id="ads_code_html_source" class="form-control font-monospace js-encoded-setting-source" rows="6"
                            data-encoded-target="ads_code_html_encoded"
                            placeholder="<!-- Cole aqui o banner HTML ou script de outra rede de anúncios -->">{{ $adsHtml }}</textarea>
                        <input type="hidden" name="ads_code_html_encoded" value="">
                        <small class="text-muted">Renderizado nas páginas públicas (e no feed como fallback) quando o AdSense nativo não estiver preenchido.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label for="ads_txt_custom">Linhas extras para <code>/ads.txt</code> (Opcional)</label>
                        <textarea id="ads_txt_custom" name="ads_txt_custom" class="form-control font-monospace" rows="2"
                            placeholder="Ex: pubmatic.com, 123456, RESELLER, 5d62403b186f2ace">{{ $settings['ads_txt_custom'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-bullhorn me-2"></i> Sistema Próprio de Publicidade (Anúncios de Terceiros por Período)</h5>
    <div class="unn-section-box mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <div class="fw-bold">Monetização direta com anunciantes (7, 14, 21, 30, 90, 120 dias...)</div>
                <small class="text-secondary">Quando houver anúncio próprio ativo na posição, ele tem prioridade de exibição com contagem real de impressões e cliques; sem anúncio próprio, exibe AdSense/HTML como fallback.</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.sponsor-plans.index') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-tags me-1"></i> Pacotes por Período
                </a>
                <a href="{{ route('admin.sponsor-banners.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-images me-1"></i> Banners &amp; Aprovações
                    @if($activeOwnBannersCount > 0)
                        <span class="badge rounded-pill text-bg-light ms-1">{{ $activeOwnBannersCount }}</span>
                    @endif
                </a>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input type="hidden" name="ads_own_network_enabled" value="0">
                    <input type="checkbox" class="form-check-input" id="ads_own_network_enabled" name="ads_own_network_enabled" value="1" {{ $adsOwnNetworkVal ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="ads_own_network_enabled">Ativar rede própria de anúncios (prioritária)</label>
                </div>
                <small class="text-secondary d-block">Exibe banners pagos de terceiros dentro da vigência configurada.</small>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input type="hidden" name="ads_sponsor_require_approval" value="0">
                    <input type="checkbox" class="form-check-input" id="ads_sponsor_require_approval" name="ads_sponsor_require_approval" value="1" {{ $adsSponsorRequireApprovalVal ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="ads_sponsor_require_approval">Exigir aprovação manual antes de publicar</label>
                </div>
                <small class="text-secondary d-block">Ativo (Sim): admin aprova antes de ir ao ar. Desativado (Não): publica automaticamente após pagamento.</small>
            </div>
        </div>

        <hr class="my-3">

        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="ads_split_enabled" value="0">
                    <input type="checkbox" class="form-check-input" id="ads_split_enabled" name="ads_split_enabled" value="1" {{ $adsSplitEnabledVal ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="ads_split_enabled">Dividir lucros (Dono do SaaS &times; Admin)</label>
                </div>
                <small class="text-secondary d-block">Aplica divisão automática sobre vendas de publicidade/anúncios.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="ads_split_saas_percent">Percentual Dono do SaaS (%)</label>
                <div class="input-group">
                    <input type="number" step="0.5" min="0" max="100" id="ads_split_saas_percent" name="ads_split_saas_percent" class="form-control" value="{{ $adsSplitSaasPercent }}">
                    <span class="input-group-text">%</span>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="ads_split_admin_percent">Percentual Administrador (%)</label>
                <div class="input-group">
                    <input type="number" step="0.5" min="0" max="100" id="ads_split_admin_percent" name="ads_split_admin_percent" class="form-control" value="{{ $adsSplitAdminPercent }}" readonly>
                    <span class="input-group-text">%</span>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-stream me-2"></i> Anúncios no Feed (Comunidade)</h5>
    <div class="unn-section-box">
        <div class="form-group mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="ads_inter_feed_enabled" value="0">
                <input type="checkbox" class="form-check-input" id="ads_inter_feed_enabled"
                    name="ads_inter_feed_enabled" value="1" {{ $adsInterFeedVal ? 'checked' : '' }}>
                <label class="form-check-label fw-bold" for="ads_inter_feed_enabled">Habilitar anúncios entre postagens no feed</label>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4 form-group mb-0">
                <label for="adsense_frequency">Frequência (a cada X posts)</label>
                <select id="adsense_frequency" name="adsense_frequency" class="form-select">
                    <option value="3" {{ $adsFreq === 3 ? 'selected' : '' }}>A cada 3 posts</option>
                    <option value="5" {{ $adsFreq === 5 ? 'selected' : '' }}>A cada 5 posts</option>
                    <option value="10" {{ $adsFreq === 10 ? 'selected' : '' }}>A cada 10 posts</option>
                    <option value="15" {{ $adsFreq === 15 ? 'selected' : '' }}>A cada 15 posts</option>
                </select>
            </div>
            <div class="col-md-8 form-group mb-0">
                <label for="ads_inter_feed_code_source">Código específico para o feed (Opcional)</label>
                <textarea id="ads_inter_feed_code_source" class="form-control font-monospace js-encoded-setting-source" rows="2"
                    data-encoded-target="ads_inter_feed_code_encoded"
                    placeholder="Se vazio, usará o AdSense ou HTML global configurado acima.">{{ $adsFeedHtml }}</textarea>
                <input type="hidden" name="ads_inter_feed_code_encoded" value="">
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                function encodeBase64Url(value) {
                    if (!value) return '';
                    if (window.TextEncoder) {
                        const bytes = new TextEncoder().encode(value);
                        let binary = '';
                        bytes.forEach(function (byte) { binary += String.fromCharCode(byte); });
                        return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
                    }
                    return btoa(unescape(encodeURIComponent(value))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
                }

                function syncEncodedFields(root) {
                    (root || document).querySelectorAll('.js-encoded-setting-source[data-encoded-target]').forEach(function (source) {
                        const targetName = source.dataset.encodedTarget;
                        const form = source.closest('form');
                        if (!targetName || !form) return;
                        const hidden = form.querySelector('input[name="' + targetName + '"]');
                        if (!hidden) return;
                        hidden.value = encodeBase64Url(source.value || '');
                    });
                }

                document.addEventListener('input', function (event) {
                    if (event.target && event.target.matches && event.target.matches('.js-encoded-setting-source[data-encoded-target]')) {
                        syncEncodedFields(document);
                    }
                });

                // Fase de captura: garante sincronização antes de qualquer submit AJAX/jQuery
                document.addEventListener('submit', function (event) {
                    if (event.target && event.target.querySelector && event.target.querySelector('.js-encoded-setting-source')) {
                        syncEncodedFields(event.target);
                    }
                }, true);

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function () { syncEncodedFields(document); });
                } else {
                    syncEncodedFields(document);
                }

                if (window.jQuery) {
                    window.jQuery(document).on('pjax:end', function () { syncEncodedFields(document); });
                }

                // Sincroniza soma 100% entre Dono do SaaS e Administrador
                document.addEventListener('input', function (event) {
                    if (event.target && event.target.id === 'ads_split_saas_percent') {
                        const saasVal = Math.max(0, Math.min(100, parseFloat(event.target.value || '0') || 0));
                        const adminInput = document.getElementById('ads_split_admin_percent');
                        if (adminInput) adminInput.value = (100 - saasVal).toFixed(saasVal % 1 === 0 ? 0 : 1);
                    }
                });
            })();
        </script>
    @endpush
@endonce
