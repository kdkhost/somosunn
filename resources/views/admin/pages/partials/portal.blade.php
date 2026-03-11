<div class="row">
    <div class="col-12 mb-4" id="sec-hero">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-0 text-secondary">
                    <i class="fas fa-network-wired mr-2"></i> Hero do Portal
                </h3>
                <div class="card-tools">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                        <input type="checkbox" class="custom-control-input section-toggle" id="toggle-hero"
                            data-section="hero" {{ ($data['hero_enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="toggle-hero">Ativo</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-7">
                        <div class="form-group">
                            <label class="font-weight-bold" for="hero_title">Titulo principal</label>
                            <input type="text" name="hero_title" id="hero_title"
                                class="form-control @error('hero_title') is-invalid @enderror"
                                value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                                placeholder="Ex: Portal de Networking">
                            @error('hero_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold" for="hero_subtitle">Subtitulo / Introducao</label>
                            <textarea name="hero_subtitle" id="hero_subtitle"
                                class="form-control summernote-sm @error('hero_subtitle') is-invalid @enderror" rows="5"
                                placeholder="Explique o valor principal do portal...">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
                            @error('hero_subtitle')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-xl-5 mt-4 mt-xl-0">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="font-weight-bold d-block mb-3" for="hero_image">Imagem do Hero</label>
                            @include('admin.components.upload-global', [
                                'name' => 'hero_image',
                                'path' => $data['hero_image'] ?? null,
                                'preview_url' => !empty($data['hero_image']) ? asset('storage/' . $data['hero_image']) : null,
                                'remove_name' => 'remove_hero_image',
                                'accept' => 'image/*',
                                'max_size' => 6291456,
                                'label' => null,
                                'help' => 'PNG, JPG, WebP, GIF ou SVG - maximo de 6 MB. Esta imagem aparece no cabecalho do portal.',
                            ])
                            @error('hero_image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4" id="sec-stats">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-0 text-info">
                    <i class="fas fa-chart-bar mr-2"></i> Estatisticas em Destaque
                </h3>
                <div class="card-tools">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                        <input type="checkbox" class="custom-control-input section-toggle" id="toggle-stats"
                            data-section="stats" {{ ($data['stats_enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="toggle-stats">Ativo</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach ([1, 2, 3, 4] as $i)
                        <div class="col-lg-6 mb-3">
                            <div class="border rounded p-3 bg-light h-100">
                                <h4 class="font-weight-bold text-info mb-3">Card {{ $i }}</h4>
                                <div class="form-group">
                                    <label class="font-weight-bold" for="stat_{{ $i }}_value">Valor</label>
                                    <input type="text" name="stat_{{ $i }}_value" id="stat_{{ $i }}_value"
                                        class="form-control @error('stat_' . $i . '_value') is-invalid @enderror"
                                        value="{{ old('stat_' . $i . '_value', $data['stat_' . $i . '_value'] ?? '') }}"
                                        placeholder="Ex: 120+">
                                    @error('stat_' . $i . '_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold" for="stat_{{ $i }}_label">Legenda</label>
                                    <input type="text" name="stat_{{ $i }}_label" id="stat_{{ $i }}_label"
                                        class="form-control @error('stat_' . $i . '_label') is-invalid @enderror"
                                        value="{{ old('stat_' . $i . '_label', $data['stat_' . $i . '_label'] ?? '') }}"
                                        placeholder="Ex: Palestras">
                                    @error('stat_' . $i . '_label')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-12" id="sec-cta">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-0 text-warning">
                    <i class="fas fa-bullhorn mr-2"></i> CTA Final
                </h3>
                <div class="card-tools">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                        <input type="checkbox" class="custom-control-input section-toggle" id="toggle-cta"
                            data-section="cta" {{ ($data['cta_enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="toggle-cta">Ativo</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-bold" for="cta_title">Titulo do CTA</label>
                    <input type="text" name="cta_title" id="cta_title"
                        class="form-control @error('cta_title') is-invalid @enderror"
                        value="{{ old('cta_title', $data['cta_title'] ?? '') }}"
                        placeholder="Ex: Pronto para expandir sua rede?">
                    @error('cta_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="font-weight-bold" for="cta_subtitle">Subtitulo / Descricao</label>
                    <textarea name="cta_subtitle" id="cta_subtitle"
                        class="form-control summernote-sm @error('cta_subtitle') is-invalid @enderror" rows="4"
                        placeholder="Explique a chamada final para o portal...">{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}</textarea>
                    @error('cta_subtitle')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold" for="cta_btn">Texto do botao</label>
                    <input type="text" name="cta_btn" id="cta_btn"
                        class="form-control @error('cta_btn') is-invalid @enderror"
                        value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}"
                        placeholder="Ex: Explorar recursos">
                    @error('cta_btn')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>
