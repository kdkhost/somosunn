@extends('panel.layouts.app')

@section('title', 'Minha loja - UNN')

@section('panel_content')
    @php
        $primaryColorRaw = (string) old('primary_color', $store->primary_color ?: '#1F5EDB');
        $accentColorRaw = (string) old('accent_color', $store->accent_color ?: '#0F172A');
        $primaryColor = preg_match('/^#?[0-9A-Fa-f]{6}$/', $primaryColorRaw) ? '#' . ltrim(strtoupper($primaryColorRaw), '#') : '#1F5EDB';
        $accentColor = preg_match('/^#?[0-9A-Fa-f]{6}$/', $accentColorRaw) ? '#' . ltrim(strtoupper($accentColorRaw), '#') : '#0F172A';
    @endphp
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Loja virtual</p>
                    <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Minha loja premium</h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Configure a identidade da sua marca, confirme o slug publico e publique sua vitrine.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if($storeUrl)
                        <a href="{{ $storeUrl }}" target="_blank" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 transition">
                            <i class="fas fa-up-right-from-square text-slate-400"></i> Ver loja
                        </a>
                    @endif
                    <span class="inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-bold {{ $store->is_published ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                        <i class="fas {{ $store->is_published ? 'fa-circle-check' : 'fa-pen-ruler' }}"></i>
                        {{ $store->is_published ? 'Loja publicada' : 'Loja em rascunho' }}
                    </span>
                </div>
            </div>
        </div>

        <form action="{{ route('panel.marketplace.store.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid gap-6 xl:grid-cols-[1.4fr,0.8fr]">
                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nome da marca</label>
                                <input type="text" name="brand_name" value="{{ old('brand_name', $store->brand_name) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @error('brand_name')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Slogan</label>
                                <input type="text" name="tagline" value="{{ old('tagline', $store->tagline) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Slug publico</label>
                                <input type="text" name="slug" value="{{ old('slug', $store->slug) }}" {{ $store->isSlugLocked() ? 'readonly' : '' }} class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Sua loja abrira em <strong>/loja/{{ old('slug', $store->slug ?: 'sua-marca') }}</strong>. Depois da primeira publicacao o slug fica travado.</p>
                                @error('slug')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Publicacao</label>
                                <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-800 px-4 py-3">
                                    <input type="hidden" name="is_published" value="0">
                                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $store->is_published) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Publicar loja</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cor primaria</label>
                                <div class="store-color-card rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/80 p-4" data-color-card="primary" data-fallback="#1F5EDB">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span id="store_primary_color_swatch" class="h-12 w-12 shrink-0 rounded-2xl border border-white/60 shadow-inner shadow-black/5 dark:border-slate-700" style="background-color: {{ $primaryColor }}"></span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Cor ativa</p>
                                                <p id="store_primary_color_label" class="truncate text-sm font-black text-slate-900 dark:text-white">{{ $primaryColor }}</p>
                                            </div>
                                        </div>
                                        <label for="store_primary_color_picker" class="relative inline-flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-blue-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                            <input type="color" id="store_primary_color_picker" value="{{ $primaryColor }}" class="absolute inset-0 cursor-pointer opacity-0">
                                            <i class="fas fa-eye-dropper"></i>
                                        </label>
                                    </div>
                                    <div class="mt-4 flex items-center gap-3">
                                        <input type="text" id="store_primary_color_input" name="primary_color" value="{{ $primaryColor }}" inputmode="text" autocomplete="off" spellcheck="false" class="store-color-input w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-950 px-4 py-3 font-bold uppercase tracking-[0.15em] text-slate-900 dark:text-white">
                                        <span id="store_primary_color_chip" class="inline-flex min-w-[104px] items-center justify-center rounded-2xl px-3 py-3 text-xs font-black uppercase tracking-[0.15em] shadow-sm" style="background-color: {{ $primaryColor }}; color: #ffffff;">Primaria</span>
                                    </div>
                                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">A cor escolhida aparece em tempo real no preview e fica sempre visivel neste painel.</p>
                                </div>
                                @error('primary_color')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cor de destaque</label>
                                <div class="store-color-card rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/80 p-4" data-color-card="accent" data-fallback="#0F172A">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span id="store_accent_color_swatch" class="h-12 w-12 shrink-0 rounded-2xl border border-white/60 shadow-inner shadow-black/5 dark:border-slate-700" style="background-color: {{ $accentColor }}"></span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Cor ativa</p>
                                                <p id="store_accent_color_label" class="truncate text-sm font-black text-slate-900 dark:text-white">{{ $accentColor }}</p>
                                            </div>
                                        </div>
                                        <label for="store_accent_color_picker" class="relative inline-flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-blue-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                            <input type="color" id="store_accent_color_picker" value="{{ $accentColor }}" class="absolute inset-0 cursor-pointer opacity-0">
                                            <i class="fas fa-eye-dropper"></i>
                                        </label>
                                    </div>
                                    <div class="mt-4 flex items-center gap-3">
                                        <input type="text" id="store_accent_color_input" name="accent_color" value="{{ $accentColor }}" inputmode="text" autocomplete="off" spellcheck="false" class="store-color-input w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-950 px-4 py-3 font-bold uppercase tracking-[0.15em] text-slate-900 dark:text-white">
                                        <span id="store_accent_color_chip" class="inline-flex min-w-[104px] items-center justify-center rounded-2xl px-3 py-3 text-xs font-black uppercase tracking-[0.15em] shadow-sm" style="background-color: {{ $accentColor }}; color: #ffffff;">Destaque</span>
                                    </div>
                                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Use a cor de apoio para botoes, detalhes de cards e pontos de destaque da loja.</p>
                                </div>
                                @error('accent_color')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Bio da loja</label>
                                <textarea name="bio" rows="6" class="store-bio-editor w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">{{ old('bio', $store->bio) }}</textarea>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Use o editor para destacar a historia, diferenciais e servicos da sua marca.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Contato e redes</h2>
                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <input type="email" name="support_email" value="{{ old('support_email', $store->support_email) }}" placeholder="E-mail de atendimento" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="text" name="support_phone" value="{{ old('support_phone', $store->support_phone) }}" placeholder="Telefone de atendimento" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="text" name="whatsapp" value="{{ old('whatsapp', $store->whatsapp) }}" placeholder="WhatsApp" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="url" name="website_url" value="{{ old('website_url', $store->website_url) }}" placeholder="Site da marca" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="url" name="instagram_url" value="{{ old('instagram_url', $store->instagram_url) }}" placeholder="Instagram" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="url" name="facebook_url" value="{{ old('facebook_url', $store->facebook_url) }}" placeholder="Facebook" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="url" name="youtube_url" value="{{ old('youtube_url', $store->youtube_url) }}" placeholder="YouTube" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white md:col-span-2">
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Logo</h2>
                        @if($store->logo_url)
                            <img src="{{ $store->logo_url }}" alt="Logo" class="mt-4 h-28 w-28 rounded-3xl object-cover border border-slate-200 dark:border-slate-700">
                        @endif
                        <input type="file" name="logo" accept="image/*" class="mt-4 block w-full text-sm text-slate-500 dark:text-slate-300">
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <input type="hidden" name="remove_logo" value="0">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-red-500"> Remover logo atual
                        </label>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Banner</h2>
                        @if($store->banner_url)
                            <img src="{{ $store->banner_url }}" alt="Banner" class="mt-4 h-40 w-full rounded-3xl object-cover border border-slate-200 dark:border-slate-700">
                        @endif
                        <input type="file" name="banner" accept="image/*" class="mt-4 block w-full text-sm text-slate-500 dark:text-slate-300">
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <input type="hidden" name="remove_banner" value="0">
                            <input type="checkbox" name="remove_banner" value="1" class="rounded border-slate-300 text-red-500"> Remover banner atual
                        </label>
                    </div>

                    <div id="store_live_preview" class="text-white rounded-3xl p-6 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $accentColor }});">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-white/70">Preview</p>
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span id="store_preview_primary_badge" class="inline-flex items-center rounded-full border border-white/15 px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em]" style="background-color: {{ $primaryColor }};">Primaria</span>
                            <span id="store_preview_accent_badge" class="inline-flex items-center rounded-full border border-white/20 px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em]" style="background-color: {{ $accentColor }};">Destaque</span>
                        </div>
                        <h3 id="store_preview_brand_name" class="mt-4 text-2xl font-black">{{ old('brand_name', $store->brand_name) ?: 'Sua marca' }}</h3>
                        <p id="store_preview_tagline" class="mt-2 text-sm text-white/80">{{ old('tagline', $store->tagline) ?: 'Loja premium dentro do ecossistema UNN.' }}</p>
                        <p class="mt-4 text-sm text-white/70">Esse bloco mostra como a sua loja vai aparecer no topo da vitrine publica.</p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <span id="store_preview_button_primary" class="inline-flex items-center rounded-2xl px-4 py-2 text-sm font-black shadow-lg shadow-black/10" style="background-color: {{ $primaryColor }};">Comprar com a marca</span>
                            <span id="store_preview_button_accent" class="inline-flex items-center rounded-2xl border border-white/20 px-4 py-2 text-sm font-black backdrop-blur-sm" style="background-color: {{ $accentColor }};">Destaque visual</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition">
                    <i class="fas fa-save"></i> Salvar loja
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            function initMarketplaceStoreBioEditor() {
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

                    $field.closest('form').off('submit.storeBioEditor').on('submit.storeBioEditor', function () {
                        $field.val($field.summernote('code'));
                    });
                });
            }

            function initMarketplaceStoreColorPreview() {
                const preview = document.getElementById('store_live_preview');
                const brandInput = document.querySelector('input[name="brand_name"]');
                const taglineInput = document.querySelector('input[name="tagline"]');
                const brandTarget = document.getElementById('store_preview_brand_name');
                const taglineTarget = document.getElementById('store_preview_tagline');

                const previewPrimaryBadge = document.getElementById('store_preview_primary_badge');
                const previewAccentBadge = document.getElementById('store_preview_accent_badge');
                const previewPrimaryButton = document.getElementById('store_preview_button_primary');
                const previewAccentButton = document.getElementById('store_preview_button_accent');

                const defaultColors = {
                    primary: '#1F5EDB',
                    accent: '#0F172A',
                };

                const colorGroups = {
                    primary: {
                        card: document.querySelector('[data-color-card="primary"]'),
                        text: document.getElementById('store_primary_color_input'),
                        picker: document.getElementById('store_primary_color_picker'),
                        swatch: document.getElementById('store_primary_color_swatch'),
                        label: document.getElementById('store_primary_color_label'),
                        chip: document.getElementById('store_primary_color_chip'),
                        fallback: defaultColors.primary,
                    },
                    accent: {
                        card: document.querySelector('[data-color-card="accent"]'),
                        text: document.getElementById('store_accent_color_input'),
                        picker: document.getElementById('store_accent_color_picker'),
                        swatch: document.getElementById('store_accent_color_swatch'),
                        label: document.getElementById('store_accent_color_label'),
                        chip: document.getElementById('store_accent_color_chip'),
                        fallback: defaultColors.accent,
                    },
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
                    const luminance = (0.299 * r) + (0.587 * g) + (0.114 * b);
                    return luminance > 170 ? '#0F172A' : '#FFFFFF';
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
                        taglineTarget.textContent = taglineInput.value.trim() || 'Loja premium dentro do ecossistema UNN.';
                    }
                };

                const syncColors = () => {
                    const primary = normalizeHexColor(colorGroups.primary.text ? colorGroups.primary.text.value : '', colorGroups.primary.fallback);
                    const accent = normalizeHexColor(colorGroups.accent.text ? colorGroups.accent.text.value : '', colorGroups.accent.fallback);

                    Object.values(colorGroups).forEach((group) => {
                        if (!group.text || !group.picker) {
                            return;
                        }

                        const current = group === colorGroups.primary ? primary : accent;
                        const textColor = contrastColor(current);

                        group.text.value = current;
                        group.picker.value = current;

                        if (group.swatch) {
                            group.swatch.style.backgroundColor = current;
                        }

                        if (group.label) {
                            group.label.textContent = current;
                        }

                        if (group.chip) {
                            group.chip.style.backgroundColor = current;
                            group.chip.style.color = textColor;
                            group.chip.style.boxShadow = `0 10px 25px ${rgba(current, 0.22)}`;
                        }

                        if (group.card) {
                            group.card.style.borderColor = rgba(current, 0.25);
                            group.card.style.boxShadow = `inset 0 0 0 1px ${rgba(current, 0.08)}`;
                            group.card.style.background = `linear-gradient(180deg, ${rgba(current, 0.16)}, ${rgba(current, 0.04)})`;
                        }
                    });

                    if (preview) {
                        preview.style.background = `linear-gradient(135deg, ${primary}, ${accent})`;
                        preview.style.boxShadow = `0 20px 45px ${rgba(primary, 0.22)}`;
                    }

                    if (previewPrimaryBadge) {
                        previewPrimaryBadge.style.backgroundColor = primary;
                        previewPrimaryBadge.style.color = contrastColor(primary);
                    }

                    if (previewAccentBadge) {
                        previewAccentBadge.style.backgroundColor = accent;
                        previewAccentBadge.style.color = contrastColor(accent);
                    }

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
                    if (!group.text || !group.picker) {
                        return;
                    }

                    group.picker.addEventListener('input', function () {
                        group.text.value = this.value.toUpperCase();
                        syncColors();
                    });

                    group.text.addEventListener('input', syncColors);
                    group.text.addEventListener('blur', syncColors);
                });

                if (brandInput) {
                    brandInput.addEventListener('input', syncPreviewTexts);
                }

                if (taglineInput) {
                    taglineInput.addEventListener('input', syncPreviewTexts);
                }

                syncPreviewTexts();
                syncColors();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    initMarketplaceStoreBioEditor();
                    initMarketplaceStoreColorPreview();
                });
            } else {
                initMarketplaceStoreBioEditor();
                initMarketplaceStoreColorPreview();
            }
        })();
    </script>
@endpush
