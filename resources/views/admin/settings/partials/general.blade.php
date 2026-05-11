<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle mr-2"></i> Configurações principais do sistema. Essas informações são exibidas no
        rodapé e em e-mails transacionais.
    </div>

    <h5 class="text-primary mb-3"><i class="fas fa-building mr-2"></i> Informações da Empresa</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Nome do Site (App Name)</label>
                <input type="text" name="app_name" class="form-control"
                    value="{{ $settings['app_name'] ?? config('app.name') }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Razão Social / Nome da Empresa</label>
                <input type="text" name="company_name" class="form-control"
                    value="{{ $settings['company_name'] ?? '' }}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Telefone / WhatsApp</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    </div>
                    <input type="text" name="company_phone" class="form-control mask-phone"
                        value="{{ $settings['company_phone'] ?? '' }}">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>E-mail de Contato</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input type="email" name="company_email" class="form-control"
                        value="{{ $settings['company_email'] ?? '' }}">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>CEP</label>
                <input type="text" id="company_zip" name="company_zip" class="form-control mask-cep"
                    value="{{ $settings['company_zip'] ?? '' }}">
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-map-marker-alt mr-2"></i> Endereço</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Endereço (Rua/Av)</label>
                <input type="text" name="company_address" class="form-control"
                    value="{{ $settings['company_address'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>Número</label>
                <input type="text" id="company_number" name="company_number" class="form-control"
                    value="{{ $settings['company_number'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Complemento</label>
                <input type="text" name="company_complement" class="form-control"
                    value="{{ $settings['company_complement'] ?? '' }}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Bairro</label>
                <input type="text" name="company_district" class="form-control"
                    value="{{ $settings['company_district'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Cidade</label>
                <input type="text" name="company_city" class="form-control"
                    value="{{ $settings['company_city'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Estado (UF)</label>
                <input type="text" name="company_state" class="form-control"
                    value="{{ $settings['company_state'] ?? '' }}">
            </div>
        </div>
    </div>

    <hr class="my-4">

    {{-- APIs de Localização (busca de estabelecimentos em eventos) --}}
    <h5 class="text-primary mb-3"><i class="fas fa-map-marked-alt mr-2"></i> APIs de Localização</h5>
    <div class="alert alert-light border mb-3">
        <small class="text-muted">
            <i class="fas fa-info-circle mr-1"></i>
            Usadas na busca de estabelecimentos ao cadastrar eventos. Prioridade automatica: <strong>TomTom</strong> (melhor cobertura de empresas no Brasil) &gt; <strong>Google Places</strong> &gt; <strong>LocationIQ</strong> (fallback).
        </small>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label class="text-uppercase text-muted small font-weight-bold">TomTom API Key</label>
                <input type="text" name="tomtom_api_key" class="form-control"
                    value="{{ $settings['tomtom_api_key'] ?? '' }}"
                    placeholder="q1Jg... (recomendado)">
                <small class="text-muted">Obtenha em developer.tomtom.com (2.500 req/dia gratuito). Melhor cobertura de SMBs no Brasil.</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="text-uppercase text-muted small font-weight-bold">Google Places API Key</label>
                <input type="text" name="google_places_api_key" class="form-control"
                    value="{{ $settings['google_places_api_key'] ?? '' }}"
                    placeholder="AIzaSy... (opcional)">
                <small class="text-muted">Obtenha em console.cloud.google.com (ative Places API + Geocoding API)</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="text-uppercase text-muted small font-weight-bold">LocationIQ API Key</label>
                <input type="text" name="locationiq_api_key" class="form-control"
                    value="{{ $settings['locationiq_api_key'] ?? '' }}"
                    placeholder="pk.xxxx... (gratuito)">
                <small class="text-muted">Obtenha em locationiq.com (5.000 req/dia gratuito)</small>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label class="text-uppercase text-muted small font-weight-bold">Raio de busca (km)</label>
                <input type="number" min="10" max="500" name="venue_search_radius_km" class="form-control"
                    value="{{ $settings['venue_search_radius_km'] ?? '150' }}">
                <small class="text-muted">Distancia maxima para considerar "proximo"</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="text-uppercase text-muted small font-weight-bold">Provedor preferido</label>
                <select name="venue_search_provider" class="form-control">
                    <option value="auto" {{ ($settings['venue_search_provider'] ?? 'auto') === 'auto' ? 'selected' : '' }}>Automatico (TomTom &gt; Google &gt; LocationIQ)</option>
                    <option value="tomtom" {{ ($settings['venue_search_provider'] ?? '') === 'tomtom' ? 'selected' : '' }}>Apenas TomTom</option>
                    <option value="google" {{ ($settings['venue_search_provider'] ?? '') === 'google' ? 'selected' : '' }}>Apenas Google Places</option>
                    <option value="locationiq" {{ ($settings['venue_search_provider'] ?? '') === 'locationiq' ? 'selected' : '' }}>Apenas LocationIQ</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="text-uppercase text-muted small font-weight-bold">Limite de resultados</label>
                <input type="number" min="5" max="40" name="venue_search_limit" class="form-control"
                    value="{{ $settings['venue_search_limit'] ?? '20' }}">
            </div>
        </div>
    </div>

    <hr class="my-4">

    {{-- Revistas / Flipbook --}}
    <h5 class="text-primary mb-3"><i class="fas fa-book-open mr-2"></i> Revistas (Flipbook)</h5>
    <div class="alert alert-light border mb-3">
        <small class="text-muted">
            <i class="fas fa-info-circle mr-1"></i>
            Escolha o plugin de visualizacao das revistas digitais. <strong>DearFlip</strong> e mais leve e rapido. <strong>3D FlipBook</strong> tem efeito 3D mais realista com dobra de pagina e sombras avancadas.
        </small>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="text-uppercase text-muted small font-weight-bold">Plugin de visualizacao</label>
                <select name="magazine_flipbook_engine" class="form-control">
                    <option value="dearflip" {{ ($settings['magazine_flipbook_engine'] ?? 'dearflip') === 'dearflip' ? 'selected' : '' }}>DearFlip (leve, rapido, controles nativos)</option>
                    <option value="3dflipbook" {{ ($settings['magazine_flipbook_engine'] ?? '') === '3dflipbook' ? 'selected' : '' }}>3D FlipBook (efeito 3D realista, dobra de pagina)</option>
                </select>
            </div>
        </div>
    </div>
</div>
