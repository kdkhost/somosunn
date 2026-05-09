@extends('admin.layouts.app')

@section('title', ($store->isPlatformStore() ? 'Loja da plataforma' : 'Minha loja') . ' - Marketplace')
@section('page_title', $store->isPlatformStore() ? 'Loja da plataforma' : 'Minha loja')

@section('content')
    @php
        $isPlatformStore = $store->isPlatformStore();
        $primaryColorRaw = (string) old('primary_color', $store->primary_color ?: '#1F5EDB');
        $accentColorRaw = (string) old('accent_color', $store->accent_color ?: '#0F172A');
        $primaryColor = preg_match('/^#?[0-9A-Fa-f]{6}$/', $primaryColorRaw) ? '#' . ltrim(strtoupper($primaryColorRaw), '#') : '#1F5EDB';
        $accentColor = preg_match('/^#?[0-9A-Fa-f]{6}$/', $accentColorRaw) ? '#' . ltrim(strtoupper($accentColorRaw), '#') : '#0F172A';
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title font-weight-bold mb-1">
                            <i class="fas fa-store mr-2"></i>{{ $isPlatformStore ? 'Loja institucional da plataforma' : 'Minha loja premium' }}
                        </h3>
                        <p class="text-muted mb-0 small">
                            {{ $isPlatformStore ? 'Esta loja institucional fica vinculada ao superadmin e nao depende da elegibilidade comum do vendedor.' : 'Configure a identidade da sua marca, confirme o slug publico e publique sua vitrine.' }}
                        </p>
                    </div>
                    <div class="mt-3 mt-md-0 d-flex flex-wrap" style="gap: 8px;">
                        @if($storeUrl)
                            <a href="{{ $storeUrl }}" target="_blank" class="btn btn-outline-primary rounded-pill elevation-1">
                                <i class="fas fa-external-link-alt mr-1"></i> Ver loja
                            </a>
                        @endif
                        @if($isPlatformStore)
                            <span class="badge badge-dark px-3 py-2"><i class="fas fa-shield-alt mr-1"></i> Loja da plataforma</span>
                        @endif
                        <span class="badge {{ $store->is_published ? 'badge-success' : 'badge-warning' }} px-3 py-2">
                            <i class="fas {{ $store->is_published ? 'fa-check-circle' : 'fa-pen' }} mr-1"></i>
                            {{ $store->is_published ? 'Loja publicada' : 'Loja em rascunho' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.marketplace.store.update') }}" method="POST" enctype="multipart/form-data" id="admin-marketplace-store-form">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-palette mr-2"></i>Identidade da loja</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="brand_name">Nome da marca</label>
                                <input type="text" name="brand_name" id="brand_name" value="{{ old('brand_name', $store->brand_name) }}" class="form-control @error('brand_name') is-invalid @enderror">
                                @error('brand_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                <label for="tagline">Slogan</label>
                                <input type="text" name="tagline" id="tagline" value="{{ old('tagline', $store->tagline) }}" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="slug">Slug publico</label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug', $store->slug) }}" {{ $store->isSlugLocked() ? 'readonly' : '' }} class="form-control @error('slug') is-invalid @enderror">
                                <small class="form-text text-muted">A loja abre em <strong>/loja/{{ old('slug', $store->slug ?: ($isPlatformStore ? 'loja-oficial' : 'sua-marca')) }}</strong>. Depois da primeira publicacao o slug fica travado.</small>
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6 d-flex align-items-end">
                                <div class="custom-control custom-switch mb-2">
                                    <input type="hidden" name="is_published" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_published" name="is_published" value="1" @checked(old('is_published', $store->is_published))>
                                    <label class="custom-control-label" for="is_published">Publicar loja</label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="store_primary_color_input">Cor primaria</label>
                                <div class="card mb-0 border" id="store_primary_color_card">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center">
                                                <span id="store_primary_color_swatch" class="rounded border mr-3" style="width:48px;height:48px;background-color: {{ $primaryColor }};"></span>
                                                <div>
                                                    <div class="text-muted small text-uppercase font-weight-bold">Cor ativa</div>
                                                    <div id="store_primary_color_label" class="font-weight-bold">{{ $primaryColor }}</div>
                                                </div>
                                            </div>
                                            <input type="color" id="store_primary_color_picker" value="{{ $primaryColor }}" class="form-control form-control-color p-1" style="width:56px;height:44px;">
                                        </div>
                                        <div class="input-group">
                                            <input type="text" id="store_primary_color_input" name="primary_color" value="{{ $primaryColor }}" class="form-control text-uppercase @error('primary_color') is-invalid @enderror">
                                            <div class="input-group-append">
                                                <span id="store_primary_color_chip" class="input-group-text text-white font-weight-bold" style="background-color: {{ $primaryColor }};">Primaria</span>
                                            </div>
                                            @error('primary_color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="store_accent_color_input">Cor de destaque</label>
                                <div class="card mb-0 border" id="store_accent_color_card">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center">
                                                <span id="store_accent_color_swatch" class="rounded border mr-3" style="width:48px;height:48px;background-color: {{ $accentColor }};"></span>
                                                <div>
                                                    <div class="text-muted small text-uppercase font-weight-bold">Cor ativa</div>
                                                    <div id="store_accent_color_label" class="font-weight-bold">{{ $accentColor }}</div>
                                                </div>
                                            </div>
                                            <input type="color" id="store_accent_color_picker" value="{{ $accentColor }}" class="form-control form-control-color p-1" style="width:56px;height:44px;">
                                        </div>
                                        <div class="input-group">
                                            <input type="text" id="store_accent_color_input" name="accent_color" value="{{ $accentColor }}" class="form-control text-uppercase @error('accent_color') is-invalid @enderror">
                                            <div class="input-group-append">
                                                <span id="store_accent_color_chip" class="input-group-text text-white font-weight-bold" style="background-color: {{ $accentColor }};">Destaque</span>
                                            </div>
                                            @error('accent_color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="store_bio">Bio da loja</label>
                                <textarea name="bio" id="store_bio" rows="6" class="form-control store-bio-editor">{{ old('bio', $store->bio) }}</textarea>
                                <small class="form-text text-muted">Use o editor para destacar a historia, diferenciais e servicos da sua marca.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-address-book mr-2"></i>Contato e redes</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="support_email">E-mail de atendimento</label>
                                <input type="email" name="support_email" id="support_email" value="{{ old('support_email', $store->support_email) }}" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="support_phone">Telefone</label>
                                <input type="text" name="support_phone" id="support_phone" value="{{ old('support_phone', $store->support_phone) }}" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="whatsapp">WhatsApp</label>
                                <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $store->whatsapp) }}" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="website_url">Site</label>
                                <input type="url" name="website_url" id="website_url" value="{{ old('website_url', $store->website_url) }}" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="instagram_url">Instagram</label>
                                <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url', $store->instagram_url) }}" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="facebook_url">Facebook</label>
                                <input type="url" name="facebook_url" id="facebook_url" value="{{ old('facebook_url', $store->facebook_url) }}" class="form-control">
                            </div>
                            <div class="form-group col-md-12">
                                <label for="youtube_url">YouTube</label>
                                <input type="url" name="youtube_url" id="youtube_url" value="{{ old('youtube_url', $store->youtube_url) }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-image mr-2"></i>Logo</h3>
                    </div>
                    <div class="card-body">
                        @if($store->logo_url)
                            <img src="{{ $store->logo_url }}" alt="Logo" class="img-fluid rounded border mb-3" style="max-height: 180px;">
                        @endif
                        <div class="form-group mb-2">
                            <input type="file" name="logo" accept="image/*" class="form-control-file">
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="hidden" name="remove_logo" value="0">
                            <input type="checkbox" class="custom-control-input" id="remove_logo" name="remove_logo" value="1">
                            <label class="custom-control-label" for="remove_logo">Remover logo atual</label>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-panorama mr-2"></i>Banner</h3>
                    </div>
                    <div class="card-body">
                        @if($store->banner_url)
                            <img src="{{ $store->banner_url }}" alt="Banner" class="img-fluid rounded border mb-3">
                        @endif
                        <div class="form-group mb-2">
                            <input type="file" name="banner" accept="image/*" class="form-control-file">
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="hidden" name="remove_banner" value="0">
                            <input type="checkbox" class="custom-control-input" id="remove_banner" name="remove_banner" value="1">
                            <label class="custom-control-label" for="remove_banner">Remover banner atual</label>
                        </div>
                    </div>
                </div>

                <div class="card text-white shadow-sm" id="store_live_preview" style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $accentColor }});">
                    <div class="card-body">
                        <div class="text-uppercase small font-weight-bold text-white-50"><i class="fas fa-eye mr-1"></i> Preview</div>
                        <div class="mt-3 d-flex flex-wrap" style="gap: 8px;">
                            <span id="store_preview_primary_badge" class="badge badge-pill px-3 py-2" style="background-color: {{ $primaryColor }};">Primaria</span>
                            <span id="store_preview_accent_badge" class="badge badge-pill px-3 py-2" style="background-color: {{ $accentColor }};">Destaque</span>
                        </div>
                        <h3 id="store_preview_brand_name" class="mt-4 mb-2">{{ old('brand_name', $store->brand_name) ?: 'Sua marca' }}</h3>
                        <p id="store_preview_tagline" class="mb-3 text-white-50">{{ old('tagline', $store->tagline) ?: ($isPlatformStore ? 'Loja oficial da plataforma dentro do ecossistema UNN.' : 'Loja premium dentro do ecossistema UNN.') }}</p>
                        <div class="d-flex flex-wrap" style="gap: 10px;">
                            <span id="store_preview_button_primary" class="badge badge-pill px-4 py-2" style="background-color: {{ $primaryColor }};">Comprar com a marca</span>
                            <span id="store_preview_button_accent" class="badge badge-pill px-4 py-2" style="background-color: {{ $accentColor }};">Destaque visual</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 text-right">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill elevation-1">
                    <i class="fas fa-save mr-1"></i> Salvar loja
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            function initAdminMarketplaceStoreBioEditor() {
                if (!(window.jQuery && $.fn && $.fn.summernote)) {
                    return;
                }

                $('.store-bio-editor').each(function () {
                    const $field = $(this);

                    if ($field.next('.note-editor').length) {
                        return;
                    }

                    $field.summernote({
                        height: 260,
                        lang: 'pt-BR',
                        placeholder: 'Apresente sua empresa, seus diferenciais e o que o cliente encontra na sua loja.',
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture', 'video']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        callbacks: {
                            onChange: function (contents) {
                                $field.val(contents);
                            }
                        }
                    });
                });

                $('#admin-marketplace-store-form').off('submit.storeBioEditor').on('submit.storeBioEditor', function () {
                    $('.store-bio-editor').each(function () {
                        const $field = $(this);
                        if ($field.next('.note-editor').length) {
                            $field.val($field.summernote('code'));
                        }
                    });
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
                const defaultTagline = @json($isPlatformStore ? 'Loja oficial da plataforma dentro do ecossistema UNN.' : 'Loja premium dentro do ecossistema UNN.');

                const colorGroups = {
                    primary: {
                        input: document.getElementById('store_primary_color_input'),
                        picker: document.getElementById('store_primary_color_picker'),
                        swatch: document.getElementById('store_primary_color_swatch'),
                        label: document.getElementById('store_primary_color_label'),
                        chip: document.getElementById('store_primary_color_chip'),
                        card: document.getElementById('store_primary_color_card'),
                        fallback: '#1F5EDB',
                    },
                    accent: {
                        input: document.getElementById('store_accent_color_input'),
                        picker: document.getElementById('store_accent_color_picker'),
                        swatch: document.getElementById('store_accent_color_swatch'),
                        label: document.getElementById('store_accent_color_label'),
                        chip: document.getElementById('store_accent_color_chip'),
                        card: document.getElementById('store_accent_color_card'),
                        fallback: '#0F172A',
                    }
                };

                const normalizeHexColor = (value, fallback) => {
                    const cleaned = String(value || '').trim().toUpperCase().replace(/[^0-9A-F#]/g, '');
                    const withHash = cleaned.startsWith('#') ? cleaned : `#${cleaned}`;
                    return /^#[0-9A-F]{6}$/.test(withHash) ? withHash : fallback;
                };

                const hexToRgb = (hex) => {
                    const normalized = normalizeHexColor(hex, '#000000').slice(1);
                    return {
                        r: parseInt(normalized.slice(0, 2), 16),
                        g: parseInt(normalized.slice(2, 4), 16),
                        b: parseInt(normalized.slice(4, 6), 16),
                    };
                };

                const contrastColor = (hex) => {
                    const { r, g, b } = hexToRgb(hex);
                    return ((0.299 * r) + (0.587 * g) + (0.114 * b)) > 170 ? '#0F172A' : '#FFFFFF';
                };

                const rgba = (hex, alpha) => {
                    const { r, g, b } = hexToRgb(hex);
                    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
                };

                const syncPreviewTexts = () => {
                    if (brandTarget && brandInput) {
                        brandTarget.textContent = brandInput.value.trim() || 'Sua marca';
                    }

                    if (taglineTarget && taglineInput) {
                        taglineTarget.textContent = taglineInput.value.trim() || defaultTagline;
                    }
                };

                const syncColors = () => {
                    const primary = normalizeHexColor(colorGroups.primary.input ? colorGroups.primary.input.value : '', colorGroups.primary.fallback);
                    const accent = normalizeHexColor(colorGroups.accent.input ? colorGroups.accent.input.value : '', colorGroups.accent.fallback);

                    Object.entries(colorGroups).forEach(([key, group]) => {
                        if (!group.input || !group.picker) {
                            return;
                        }

                        const current = key === 'primary' ? primary : accent;
                        const textColor = contrastColor(current);

                        group.input.value = current;
                        group.picker.value = current;
                        if (group.swatch) group.swatch.style.backgroundColor = current;
                        if (group.label) group.label.textContent = current;
                        if (group.chip) {
                            group.chip.style.backgroundColor = current;
                            group.chip.style.color = textColor;
                        }
                        if (group.card) {
                            group.card.style.borderColor = rgba(current, 0.25);
                            group.card.style.boxShadow = `inset 0 0 0 1px ${rgba(current, 0.06)}`;
                        }
                    });

                    if (preview) {
                        preview.style.background = `linear-gradient(135deg, ${primary}, ${accent})`;
                    }
                    if (previewPrimaryBadge) previewPrimaryBadge.style.backgroundColor = primary;
                    if (previewAccentBadge) previewAccentBadge.style.backgroundColor = accent;
                    if (previewPrimaryButton) {
                        previewPrimaryButton.style.backgroundColor = primary;
                        previewPrimaryButton.style.color = contrastColor(primary);
                    }
                    if (previewAccentButton) {
                        previewAccentButton.style.backgroundColor = accent;
                        previewAccentButton.style.color = contrastColor(accent);
                    }
                };

                Object.values(colorGroups).forEach((group) => {
                    if (group.picker && group.input) {
                        group.picker.addEventListener('input', function () {
                            group.input.value = group.picker.value.toUpperCase();
                            syncColors();
                        });

                        group.input.addEventListener('input', syncColors);
                        group.input.addEventListener('blur', syncColors);
                    }
                });

                [brandInput, taglineInput].forEach((field) => {
                    if (!field) {
                        return;
                    }
                    field.addEventListener('input', syncPreviewTexts);
                });

                syncPreviewTexts();
                syncColors();
            }

            $(function () {
                initAdminMarketplaceStoreBioEditor();
                initAdminMarketplaceStoreColorPreview();
            });
        })();
    </script>
@endpush
