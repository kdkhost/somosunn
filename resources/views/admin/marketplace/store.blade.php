@extends('admin.layouts.app')

@section('title', ($store->isPlatformStore() ? 'Loja da Plataforma' : 'Minha Loja') . ' - Marketplace')
@section('page_title', $store->isPlatformStore() ? 'Loja da Plataforma' : 'Minha Loja')

@section('content')
    @php
        $isPlatformStore = $store->isPlatformStore();
        $primaryColorRaw = (string) old('primary_color', $store->primary_color ?: '#1F5EDB');
        $accentColorRaw = (string) old('accent_color', $store->accent_color ?: '#0F172A');
        $primaryColor = preg_match('/^#?[0-9A-Fa-f]{6}$/', $primaryColorRaw) ? '#' . ltrim(strtoupper($primaryColorRaw), '#') : '#1F5EDB';
        $accentColor = preg_match('/^#?[0-9A-Fa-f]{6}$/', $accentColorRaw) ? '#' . ltrim(strtoupper($accentColorRaw), '#') : '#0F172A';
    @endphp

    {{-- Hero Header --}}
    <div class="card bg-gradient-navy shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-white mr-3" style="width:50px;height:50px;">
                        @if($store->logo_url)
                            <img src="{{ $store->logo_url }}" alt="Logo" class="rounded-circle" style="width:44px;height:44px;object-fit:cover;">
                        @else
                            <i class="fas fa-store text-primary" style="font-size:20px;"></i>
                        @endif
                    </div>
                    <div>
                        <h4 class="font-weight-bold text-white mb-0">{{ $store->brand_name ?: ($isPlatformStore ? 'Loja Oficial' : 'Minha Loja') }}</h4>
                        <p class="text-light mb-0 small opacity-75">
                            {{ $isPlatformStore ? 'Loja institucional vinculada à plataforma' : 'Configure identidade, slug e publique sua vitrine' }}
                        </p>
                    </div>
                </div>
                <div class="d-flex flex-wrap mt-3 mt-md-0" style="gap:8px;">
                    @if($storeUrl)
                        <a href="{{ $storeUrl }}" target="_blank" class="btn btn-light btn-sm rounded-pill px-3 elevation-1">
                            <i class="fas fa-external-link-alt mr-1"></i> Ver loja
                        </a>
                    @endif
                    <span class="badge {{ $store->is_published ? 'badge-success' : 'badge-warning' }} px-3 py-2 align-self-center">
                        <i class="fas {{ $store->is_published ? 'fa-check-circle' : 'fa-pen' }} mr-1"></i>
                        {{ $store->is_published ? 'Publicada' : 'Rascunho' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.marketplace.store.update') }}" method="POST" enctype="multipart/form-data" id="admin-marketplace-store-form">
        @csrf

        <div class="row">
            {{-- Coluna principal --}}
            <div class="col-lg-8">
                {{-- Identidade --}}
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-palette mr-2 text-primary"></i>Identidade da Marca</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label class="font-weight-bold"><i class="fas fa-tag mr-1 text-muted"></i> Nome da marca</label>
                                <input type="text" name="brand_name" id="brand_name" value="{{ old('brand_name', $store->brand_name) }}" class="form-control @error('brand_name') is-invalid @enderror" placeholder="Ex: Minha Marca Premium">
                                @error('brand_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4 d-flex align-items-end">
                                <div class="custom-control custom-switch mb-2 w-100">
                                    <input type="hidden" name="is_published" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_published" name="is_published" value="1" @checked(old('is_published', $store->is_published))>
                                    <label class="custom-control-label font-weight-bold" for="is_published">Publicar loja</label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold"><i class="fas fa-link mr-1 text-muted"></i> Slug público</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">/loja/</span>
                                    </div>
                                    <input type="text" name="slug" id="slug" value="{{ old('slug', $store->slug) }}" {{ $store->isSlugLocked() ? 'readonly' : '' }} class="form-control @error('slug') is-invalid @enderror">
                                </div>
                                <small class="form-text text-muted">{{ $store->isSlugLocked() ? 'Slug travado após primeira publicação.' : 'Defina antes de publicar. Não poderá ser alterado depois.' }}</small>
                                @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold"><i class="fas fa-quote-left mr-1 text-muted"></i> Slogan</label>
                                <input type="text" name="tagline" id="tagline" value="{{ old('tagline', $store->tagline) }}" class="form-control" placeholder="Frase de impacto da sua marca">
                            </div>
                        </div>

                        {{-- Cores --}}
                        <hr class="my-3">
                        <h6 class="font-weight-bold mb-3"><i class="fas fa-swatchbook mr-1 text-muted"></i> Paleta de cores</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="d-flex align-items-center p-3 rounded border" id="store_primary_color_card">
                                    <span id="store_primary_color_swatch" class="rounded mr-3 border" style="width:40px;height:40px;background-color:{{ $primaryColor }};flex-shrink:0;"></span>
                                    <div class="flex-grow-1">
                                        <div class="text-muted small font-weight-bold">PRIMÁRIA</div>
                                        <div class="input-group input-group-sm mt-1">
                                            <input type="text" id="store_primary_color_input" name="primary_color" value="{{ $primaryColor }}" class="form-control text-uppercase font-weight-bold">
                                            <div class="input-group-append">
                                                <span id="store_primary_color_chip" class="input-group-text text-white px-3" style="background-color:{{ $primaryColor }};">
                                                    <i class="fas fa-circle"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="color" id="store_primary_color_picker" value="{{ $primaryColor }}" class="ml-2 border-0" style="width:36px;height:36px;cursor:pointer;">
                                </div>
                                <div id="store_primary_color_label" class="d-none">{{ $primaryColor }}</div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="d-flex align-items-center p-3 rounded border" id="store_accent_color_card">
                                    <span id="store_accent_color_swatch" class="rounded mr-3 border" style="width:40px;height:40px;background-color:{{ $accentColor }};flex-shrink:0;"></span>
                                    <div class="flex-grow-1">
                                        <div class="text-muted small font-weight-bold">DESTAQUE</div>
                                        <div class="input-group input-group-sm mt-1">
                                            <input type="text" id="store_accent_color_input" name="accent_color" value="{{ $accentColor }}" class="form-control text-uppercase font-weight-bold">
                                            <div class="input-group-append">
                                                <span id="store_accent_color_chip" class="input-group-text text-white px-3" style="background-color:{{ $accentColor }};">
                                                    <i class="fas fa-circle"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="color" id="store_accent_color_picker" value="{{ $accentColor }}" class="ml-2 border-0" style="width:36px;height:36px;cursor:pointer;">
                                </div>
                                <div id="store_accent_color_label" class="d-none">{{ $accentColor }}</div>
                            </div>
                        </div>

                        {{-- Bio --}}
                        <hr class="my-3">
                        <h6 class="font-weight-bold mb-3"><i class="fas fa-align-left mr-1 text-muted"></i> Sobre a loja</h6>
                        <textarea name="bio" id="store_bio" rows="6" class="form-control store-bio-editor">{{ old('bio', $store->bio) }}</textarea>
                        <small class="form-text text-muted">Apresente sua marca, diferenciais e o que o cliente encontra na loja.</small>
                    </div>
                </div>

                {{-- Contato --}}
                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-address-book mr-2 text-secondary"></i>Contato e Redes Sociais</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label><i class="fas fa-envelope mr-1 text-muted"></i> E-mail</label>
                                <input type="email" name="support_email" value="{{ old('support_email', $store->support_email) }}" class="form-control" placeholder="contato@suamarca.com">
                            </div>
                            <div class="form-group col-md-6">
                                <label><i class="fas fa-phone mr-1 text-muted"></i> Telefone</label>
                                <input type="text" name="support_phone" value="{{ old('support_phone', $store->support_phone) }}" class="form-control" placeholder="(11) 99999-9999">
                            </div>
                            <div class="form-group col-md-6">
                                <label><i class="fab fa-whatsapp mr-1 text-success"></i> WhatsApp</label>
                                <input type="text" name="whatsapp" value="{{ old('whatsapp', $store->whatsapp) }}" class="form-control" placeholder="5511999999999">
                            </div>
                            <div class="form-group col-md-6">
                                <label><i class="fas fa-globe mr-1 text-muted"></i> Site</label>
                                <input type="url" name="website_url" value="{{ old('website_url', $store->website_url) }}" class="form-control" placeholder="https://suamarca.com">
                            </div>
                            <div class="form-group col-md-4">
                                <label><i class="fab fa-instagram mr-1 text-danger"></i> Instagram</label>
                                <input type="url" name="instagram_url" value="{{ old('instagram_url', $store->instagram_url) }}" class="form-control" placeholder="https://instagram.com/...">
                            </div>
                            <div class="form-group col-md-4">
                                <label><i class="fab fa-facebook mr-1 text-primary"></i> Facebook</label>
                                <input type="url" name="facebook_url" value="{{ old('facebook_url', $store->facebook_url) }}" class="form-control" placeholder="https://facebook.com/...">
                            </div>
                            <div class="form-group col-md-4">
                                <label><i class="fab fa-youtube mr-1 text-danger"></i> YouTube</label>
                                <input type="url" name="youtube_url" value="{{ old('youtube_url', $store->youtube_url) }}" class="form-control" placeholder="https://youtube.com/...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coluna lateral --}}
            <div class="col-lg-4">
                {{-- Logo com drag-drop --}}
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-image mr-2 text-primary"></i>Logo</h3>
                    </div>
                    <div class="card-body">
                        @if($store->logo_url)
                            <div class="text-center mb-3">
                                <img src="{{ $store->logo_url }}" alt="Logo" class="rounded border" style="max-height:120px; max-width:100%;">
                            </div>
                        @endif
                        <div class="store-dropzone" data-target="logo-input">
                            <input type="file" name="logo" accept="image/*" class="store-dropzone-input" id="logo-input">
                            <div class="store-dropzone-placeholder">
                                <i class="fas fa-cloud-upload-alt text-primary" style="font-size:1.8rem;"></i>
                                <p class="font-weight-bold mb-0 mt-2" style="font-size:12px;">Arraste a logo aqui</p>
                                <p class="text-muted mb-0" style="font-size:10px;">PNG, JPG ou SVG (máx. 2MB)</p>
                            </div>
                            <div class="store-dropzone-preview d-none">
                                <img src="" alt="Preview" class="rounded border" style="max-height:100px; max-width:100%;">
                                <p class="store-dropzone-filename text-muted mt-1 mb-0" style="font-size:10px;"></p>
                            </div>
                        </div>
                        @if($store->logo_url)
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="hidden" name="remove_logo" value="0">
                                <input type="checkbox" class="custom-control-input" id="remove_logo" name="remove_logo" value="1">
                                <label class="custom-control-label small" for="remove_logo">Remover logo atual</label>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Banner com drag-drop --}}
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-panorama mr-2 text-primary"></i>Banner</h3>
                    </div>
                    <div class="card-body">
                        @if($store->banner_url)
                            <div class="mb-3">
                                <img src="{{ $store->banner_url }}" alt="Banner" class="img-fluid rounded border" style="max-height:120px; width:100%; object-fit:cover;">
                            </div>
                        @endif
                        <div class="store-dropzone" data-target="banner-input">
                            <input type="file" name="banner" accept="image/*" class="store-dropzone-input" id="banner-input">
                            <div class="store-dropzone-placeholder">
                                <i class="fas fa-image text-info" style="font-size:1.8rem;"></i>
                                <p class="font-weight-bold mb-0 mt-2" style="font-size:12px;">Arraste o banner aqui</p>
                                <p class="text-muted mb-0" style="font-size:10px;">Recomendado: 1200x400px</p>
                            </div>
                            <div class="store-dropzone-preview d-none">
                                <img src="" alt="Preview" class="rounded border" style="max-height:80px; width:100%; object-fit:cover;">
                                <p class="store-dropzone-filename text-muted mt-1 mb-0" style="font-size:10px;"></p>
                            </div>
                        </div>
                        @if($store->banner_url)
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="hidden" name="remove_banner" value="0">
                                <input type="checkbox" class="custom-control-input" id="remove_banner" name="remove_banner" value="1">
                                <label class="custom-control-label small" for="remove_banner">Remover banner atual</label>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Preview ao vivo --}}
                <div class="card shadow-sm border-0 overflow-hidden" id="store_live_preview" style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $accentColor }});">
                    <div class="card-body text-white">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-eye mr-2 opacity-50"></i>
                            <span class="text-uppercase font-weight-bold" style="font-size:10px; letter-spacing:1px; opacity:.6;">Preview ao vivo</span>
                        </div>
                        <h4 id="store_preview_brand_name" class="font-weight-bold mb-1">{{ old('brand_name', $store->brand_name) ?: 'Sua marca' }}</h4>
                        <p id="store_preview_tagline" class="mb-3 small" style="opacity:.7;">{{ old('tagline', $store->tagline) ?: 'Slogan da sua loja' }}</p>
                        <div class="d-flex flex-wrap" style="gap:6px;">
                            <span id="store_preview_primary_badge" class="badge badge-pill px-3 py-2 text-white" style="background-color:{{ $primaryColor }};">Primária</span>
                            <span id="store_preview_accent_badge" class="badge badge-pill px-3 py-2 text-white" style="background-color:{{ $accentColor }};">Destaque</span>
                        </div>
                        <div class="mt-3 d-flex flex-wrap" style="gap:6px;">
                            <span id="store_preview_button_primary" class="badge badge-pill px-3 py-2" style="background-color:{{ $primaryColor }};">Botão primário</span>
                            <span id="store_preview_button_accent" class="badge badge-pill px-3 py-2" style="background-color:{{ $accentColor }};">Botão destaque</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botão salvar --}}
        <div class="row mt-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.marketplace.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 elevation-2">
                    <i class="fas fa-save mr-2"></i> Salvar Loja
                </button>
            </div>
        </div>
    </form>
@endsection

@push('styles')
<style>
    .store-dropzone {
        position: relative;
        border: 2px dashed #dee2e6;
        border-radius: 0.75rem;
        padding: 1.25rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8f9fa;
    }
    .store-dropzone:hover,
    .store-dropzone.dragover {
        border-color: #007bff;
        background: #eff6ff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,123,255,.1);
    }
    .store-dropzone .store-dropzone-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    function initAdminMarketplaceStoreBioEditor() {
        if (!(window.jQuery && $.fn && $.fn.summernote)) return;

        $('.store-bio-editor').each(function () {
            const $field = $(this);
            if ($field.next('.note-editor').length) return;

            $field.summernote({
                height: 220,
                lang: 'pt-BR',
                placeholder: 'Apresente sua empresa, diferenciais e o que o cliente encontra na loja.',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview']]
                ],
                callbacks: { onChange: function (contents) { $field.val(contents); } }
            });
        });

        $('#admin-marketplace-store-form').off('submit.storeBioEditor').on('submit.storeBioEditor', function () {
            $('.store-bio-editor').each(function () {
                const $field = $(this);
                if ($field.next('.note-editor').length) $field.val($field.summernote('code'));
            });
        });
    }

    function initStoreDropzones() {
        document.querySelectorAll('.store-dropzone').forEach(function(zone) {
            const input = zone.querySelector('.store-dropzone-input');
            const placeholder = zone.querySelector('.store-dropzone-placeholder');
            const preview = zone.querySelector('.store-dropzone-preview');
            const previewImg = preview ? preview.querySelector('img') : null;
            const filename = preview ? preview.querySelector('.store-dropzone-filename') : null;

            ['dragenter', 'dragover'].forEach(evt => {
                zone.addEventListener(evt, function(e) { e.preventDefault(); zone.classList.add('dragover'); });
            });
            ['dragleave', 'drop'].forEach(evt => {
                zone.addEventListener(evt, function(e) { e.preventDefault(); zone.classList.remove('dragover'); });
            });

            if (input) {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (!file) return;
                    if (placeholder) placeholder.classList.add('d-none');
                    if (preview) preview.classList.remove('d-none');
                    if (previewImg && file.type.startsWith('image/')) {
                        previewImg.src = URL.createObjectURL(file);
                    }
                    if (filename) filename.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
                });
            }
        });
    }

    function initAdminMarketplaceStoreColorPreview() {
        const preview = document.getElementById('store_live_preview');
        const brandInput = document.getElementById('brand_name');
        const taglineInput = document.getElementById('tagline');
        const brandTarget = document.getElementById('store_preview_brand_name');
        const taglineTarget = document.getElementById('store_preview_tagline');
        const previewPrimaryBadge = document.getElementById('store_preview_primary_badge');
        const previewAccentBadge = document.getElementById('store_preview_accent_badge');
        const previewPrimaryButton = document.getElementById('store_preview_button_primary');
        const previewAccentButton = document.getElementById('store_preview_button_accent');

        const colorGroups = {
            primary: {
                input: document.getElementById('store_primary_color_input'),
                picker: document.getElementById('store_primary_color_picker'),
                swatch: document.getElementById('store_primary_color_swatch'),
                chip: document.getElementById('store_primary_color_chip'),
                fallback: '#1F5EDB',
            },
            accent: {
                input: document.getElementById('store_accent_color_input'),
                picker: document.getElementById('store_accent_color_picker'),
                swatch: document.getElementById('store_accent_color_swatch'),
                chip: document.getElementById('store_accent_color_chip'),
                fallback: '#0F172A',
            }
        };

        const normalize = (v, fb) => {
            const c = String(v||'').trim().toUpperCase().replace(/[^0-9A-F#]/g,'');
            const h = c.startsWith('#') ? c : '#'+c;
            return /^#[0-9A-F]{6}$/.test(h) ? h : fb;
        };
        const contrast = (hex) => {
            const n = normalize(hex,'#000000').slice(1);
            const r=parseInt(n.slice(0,2),16), g=parseInt(n.slice(2,4),16), b=parseInt(n.slice(4,6),16);
            return (0.299*r+0.587*g+0.114*b)>170?'#0F172A':'#FFFFFF';
        };

        const sync = () => {
            const p = normalize(colorGroups.primary.input?.value, colorGroups.primary.fallback);
            const a = normalize(colorGroups.accent.input?.value, colorGroups.accent.fallback);

            Object.entries(colorGroups).forEach(([key, g]) => {
                const cur = key==='primary'?p:a;
                if(g.input) g.input.value = cur;
                if(g.picker) g.picker.value = cur;
                if(g.swatch) g.swatch.style.backgroundColor = cur;
                if(g.chip) { g.chip.style.backgroundColor = cur; g.chip.style.color = contrast(cur); }
            });

            if(preview) preview.style.background = `linear-gradient(135deg, ${p}, ${a})`;
            if(previewPrimaryBadge) previewPrimaryBadge.style.backgroundColor = p;
            if(previewAccentBadge) previewAccentBadge.style.backgroundColor = a;
            if(previewPrimaryButton) { previewPrimaryButton.style.backgroundColor = p; previewPrimaryButton.style.color = contrast(p); }
            if(previewAccentButton) { previewAccentButton.style.backgroundColor = a; previewAccentButton.style.color = contrast(a); }
        };

        const syncText = () => {
            if(brandTarget && brandInput) brandTarget.textContent = brandInput.value.trim() || 'Sua marca';
            if(taglineTarget && taglineInput) taglineTarget.textContent = taglineInput.value.trim() || 'Slogan da sua loja';
        };

        Object.values(colorGroups).forEach(g => {
            if(g.picker && g.input) {
                g.picker.addEventListener('input', function(){ g.input.value = g.picker.value.toUpperCase(); sync(); });
                g.input.addEventListener('input', sync);
                g.input.addEventListener('blur', sync);
            }
        });
        [brandInput, taglineInput].forEach(f => { if(f) f.addEventListener('input', syncText); });
        syncText();
        sync();
    }

    $(function () {
        initAdminMarketplaceStoreBioEditor();
        initStoreDropzones();
        initAdminMarketplaceStoreColorPreview();
    });
})();
</script>
@endpush
