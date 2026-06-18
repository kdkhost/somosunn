@php
    $entity = $entity ?? null;
    $formId = $formId ?? 'resourceForm';
    $previewUrl = $previewUrl ?? null;
    $defaultTags = $defaultTags ?? [];
    $tagLabels = $tagLabels ?? [];
    $rawCertificateSettings = $entity && $entity->certificate_settings ? $entity->certificate_settings : new \stdClass();
    $logoAuth = \App\Models\Setting::get('logo_auth') ?: \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
    $logoAuthUrl = $logoAuth ? asset(ltrim($logoAuth, '/')) : asset('img/logo.svg');
    $signatureUrl = $entity && $entity->instructor_signature ? \App\Support\UploadStorage::url($entity->instructor_signature) : '';
@endphp

    $(document).ready(function () {
        const formId = @json($formId);
        const $form = $('#' + formId);
        const $root = $('#certificate-editor-root');

        if (!$form.length || !$root.length) {
            return;
        }

        const rawCertSettings = {!! json_encode($rawCertificateSettings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!} || {};
        const defaultTags = @json($defaultTags);
        const tagLabels = @json($tagLabels);
        const platformLogoUrl = @json($logoAuthUrl);
        const previewUrl = @json($previewUrl);
        const fontsApiUrl = @json(
            app('router')->has('panel.admin.fonts.api.active')
                ? route('panel.admin.fonts.api.active')
                : (app('router')->has('admin.fonts.api.active') ? route('admin.fonts.api.active') : null)
        );

        const isSchemaV2 = rawCertSettings && rawCertSettings.schemaVersion === 2 && rawCertSettings.elements;
        let certDoc = isSchemaV2 ? rawCertSettings : { schemaVersion: 2, meta: {}, elements: {} };
        certDoc.meta = certDoc.meta || {};
        certDoc.elements = certDoc.elements || {};
        certDoc.schemaVersion = 2;

        if (!isSchemaV2 && rawCertSettings && typeof rawCertSettings === 'object') {
            if (typeof rawCertSettings.backgroundFit === 'string') {
                certDoc.meta.backgroundFit = rawCertSettings.backgroundFit;
            }
            if (typeof rawCertSettings.custom_title === 'string') {
                certDoc.meta.titleText = rawCertSettings.custom_title;
            } else if (typeof rawCertSettings.title === 'string') {
                certDoc.meta.titleText = rawCertSettings.title;
            }
            if (typeof rawCertSettings.custom_presentation_text === 'string') {
                certDoc.meta.presentationText = rawCertSettings.custom_presentation_text;
            } else if (typeof rawCertSettings.presentation_text === 'string') {
                certDoc.meta.presentationText = rawCertSettings.presentation_text;
            }

            Object.keys(rawCertSettings).forEach((key) => {
                const value = rawCertSettings[key];
                if (value && typeof value === 'object' && value.x !== undefined && value.y !== undefined) {
                    certDoc.elements[key] = value;
                }
            });
        }

        certDoc.meta.backgroundFit = certDoc.meta.backgroundFit || 'cover';

        const certSettings = certDoc.elements;
        let instructorSignaturePreviewUrl = @json($signatureUrl);
        let activeElementId = null;
        const BASE_W = 842;
        const BASE_H = 595;
        const $canvas = $('#cert-elements-layer');

        $.each(defaultTags, function (key, value) {
            if (!certSettings[key]) {
                certSettings[key] = value;
            }
        });

        $.each(certSettings, function (key, data) {
            if (!data || typeof data !== 'object') {
                return;
            }
            if (data.visible === undefined) data.visible = true;
            if (data.locked === undefined) data.locked = false;
            if (data.zIndex === undefined) data.zIndex = key === 'platform_logo' ? 20 : 10;
        });

        if (certSettings.platform_logo) {
            certSettings.platform_logo.mandatory = true;
            certSettings.platform_logo.visible = true;
            certSettings.platform_logo.width = certSettings.platform_logo.width || 120;
            certSettings.platform_logo.height = certSettings.platform_logo.height || 60;
        }

        if (certSettings.instructor_signature) {
            certSettings.instructor_signature.width = certSettings.instructor_signature.width || 200;
            certSettings.instructor_signature.height = certSettings.instructor_signature.height || 60;
        }

        if (certSettings.title && $('#certificate_title').length) {
            certSettings.title.text = $('#certificate_title').val() || certSettings.title.text || '';
        }
        if (certSettings.presentation_text && $('#presentation_text').length) {
            certSettings.presentation_text.text = $('#presentation_text').val() || certSettings.presentation_text.text || '';
        }

        function applyZoom(zoom) {
            const normalized = Math.max(0.25, Math.min(parseFloat(zoom || 1), 3));
            $('#cert-canvas').css({
                width: (BASE_W * normalized) + 'px',
                height: (BASE_H * normalized) + 'px'
            });
        }

        function fitCanvas() {
            const $stage = $('#cert-canvas-stage');
            if (!$stage.length || !$root.is(':visible')) {
                return;
            }

            const availableWidth = Math.max(0, $stage.innerWidth() - 32);
            const availableHeight = Math.max(0, $stage.innerHeight() - 32);
            const target = Math.max(0.25, Math.min(availableWidth / BASE_W, availableHeight / BASE_H, 3));
            const options = $('#cert-zoom option').map(function () {
                return parseFloat($(this).val());
            }).get();

            let nearest = options[0] || 1;
            options.forEach(function (option) {
                if (Math.abs(option - target) < Math.abs(nearest - target)) {
                    nearest = option;
                }
            });

            $('#cert-zoom').val(String(nearest));
            applyZoom(nearest);
        }

        function scheduleFitCanvas() {
            setTimeout(fitCanvas, 40);
            setTimeout(fitCanvas, 180);
        }

        function applyBackgroundFit() {
            const mode = ($('#cert-bg-fit').val() || 'cover') === 'stretch' ? 'fill' : 'cover';
            $('#cert-bg-img').css('object-fit', mode);
        }

        function hasCertificateBackground() {
            return $('#cert-bg-img').length > 0 && !!$('#cert-bg-img').attr('src');
        }

        function isLightColor(color) {
            if (!color) return false;
            const value = String(color).trim().toLowerCase();
            if (value === 'white' || value === '#fff' || value === '#ffffff') {
                return true;
            }

            const hexMatch = value.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i);
            if (!hexMatch) {
                return false;
            }

            let hex = hexMatch[1];
            if (hex.length === 3) {
                hex = hex.split('').map(function (char) { return char + char; }).join('');
            }

            const r = parseInt(hex.slice(0, 2), 16);
            const g = parseInt(hex.slice(2, 4), 16);
            const b = parseInt(hex.slice(4, 6), 16);
            const luminance = (0.299 * r) + (0.587 * g) + (0.114 * b);

            return luminance >= 200;
        }

        function updateEditorContrastMode() {
            const noBackground = !hasCertificateBackground();
            $('#cert-canvas').toggleClass('cert-editor-no-bg', noBackground);
        }

        function updateGridOverlay() {
            const enabled = $('#cert-grid-enabled').is(':checked');
            const step = parseFloat($('#cert-grid-step').val()) || 5;
            const $grid = $('#cert-grid-overlay');

            if (!enabled) {
                $grid.hide();
                return;
            }

            $grid.show().css({
                backgroundImage:
                    'linear-gradient(to right, rgba(37,99,235,0.25) 1px, transparent 1px), ' +
                    'linear-gradient(to bottom, rgba(37,99,235,0.25) 1px, transparent 1px)',
                backgroundSize: step + '% ' + step + '%'
            });
        }

        function buildLayerBadge(text, classes) {
            return $('<span>').addClass('rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-[0.18em] ' + classes).text(text);
        }

        function updateLayersList() {
            const $list = $('#cert-layers');
            $list.empty();

            Object.keys(certSettings)
                .filter((key) => certSettings[key] && typeof certSettings[key] === 'object' && certSettings[key].x !== undefined && certSettings[key].y !== undefined)
                .map((key) => {
                    const zIndex = certSettings[key].zIndex !== undefined ? parseInt(certSettings[key].zIndex, 10) : (key === 'platform_logo' ? 20 : 10);
                    return {
                        key,
                        zIndex: isNaN(zIndex) ? 10 : zIndex,
                        visible: key === 'platform_logo' ? true : certSettings[key].visible !== false,
                        locked: !!certSettings[key].locked
                    };
                })
                .sort((left, right) => right.zIndex - left.zIndex)
                .forEach((item) => {
                    const isActive = activeElementId === item.key;
                    const $button = $('<button type="button">')
                        .addClass(
                            'flex w-full items-center justify-between gap-3 rounded-2xl border px-4 py-3 text-left transition-all ' +
                            (isActive
                                ? 'border-blue-500 bg-blue-600 text-white shadow-lg shadow-blue-500/20'
                                : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-blue-200 hover:text-blue-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 dark:hover:border-blue-900 dark:hover:text-blue-400')
                        );

                    const $left = $('<span>').addClass('min-w-0 truncate text-sm font-bold').text(tagLabels[item.key] || item.key);
                    const $right = $('<span>').addClass('flex shrink-0 items-center gap-2');

                    if (item.key !== 'platform_logo' && !item.visible) {
                        $right.append(buildLayerBadge('Oculto', 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-300'));
                    }
                    if (item.locked) {
                        $right.append(buildLayerBadge('Lock', 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'));
                    }
                    $right.append(buildLayerBadge('z:' + item.zIndex, 'bg-white text-slate-500 dark:bg-slate-900 dark:text-slate-300'));

                    $button.append($left).append($right);
                    $button.on('click', function () {
                        $('#el-' + item.key).trigger('mousedown');
                    });

                    $list.append($button);
                });
        }

        function showStyleControls(key, data) {
            $('#selected-elem-name').text(tagLabels[key] || key);
            $('#style-x').val(parseFloat(data.x ?? 0).toFixed(2));
            $('#style-y').val(parseFloat(data.y ?? 0).toFixed(2));
            $('#style-locked').prop('checked', !!data.locked);
            $('#style-font-size').val(data.fontSize || 16);
            $('#style-z-index').val(data.zIndex || 10);
            $('#style-color').val(data.color || '#000000');
            $('#style-font-weight').val(data.fontWeight || 'normal');
            $('#style-font-family').val(data.fontFamily || 'Arial, sans-serif');
            $('#cert-style-controls').show();
        }

        function clearSelection() {
            activeElementId = null;
            $('.cert-element').css('border-color', 'transparent');
            $('#cert-style-controls').hide();
            updateLayersList();
        }

        function renderElements() {
            $canvas.empty();
            updateEditorContrastMode();

            $.each(certSettings, function (key, data) {
                if (!data || typeof data !== 'object' || data.x === undefined || data.y === undefined) {
                    return;
                }

                const $element = $('<div>')
                    .addClass('cert-element')
                    .attr('id', 'el-' + key)
                    .attr('data-tag', key)
                    .css({
                        position: 'absolute',
                        left: data.x + '%',
                        top: data.y + '%',
                        fontSize: (data.fontSize || 16) + 'px',
                        color: data.color || '#000000',
                        fontWeight: data.fontWeight || 'normal',
                        fontFamily: data.fontFamily || 'Arial, sans-serif',
                        cursor: data.locked ? 'not-allowed' : 'move',
                        display: (key !== 'platform_logo' && data.visible === false) ? 'none' : 'block',
                        whiteSpace: data.multiline ? 'pre-line' : 'nowrap',
                        width: (data.multiline && data.maxWidth) ? (data.maxWidth + 'px') : 'auto',
                        textAlign: data.textAlign || 'left',
                        border: '1px dashed transparent',
                        padding: '5px',
                        zIndex: data.zIndex || 10
                    });

                if (key === 'platform_logo') {
                    $element.css({
                        width: (data.width || 120) + 'px',
                        height: (data.height || 60) + 'px',
                        padding: '0px',
                        backgroundImage: 'url("' + platformLogoUrl + '")',
                        backgroundSize: '100% 100%',
                        backgroundRepeat: 'no-repeat',
                        backgroundPosition: 'center'
                    }).text('');
                } else if (key === 'instructor_signature') {
                    const width = data.width || 200;
                    const height = data.height || 60;
                    const url = instructorSignaturePreviewUrl || '';
                    const hidden = data.visible === false;
                    const displayMode = hidden ? 'none' : (url ? 'block' : 'flex');

                    $element.css({
                        width: width + 'px',
                        height: height + 'px',
                        padding: '0px',
                        backgroundImage: url ? ('url("' + url + '")') : 'none',
                        backgroundSize: 'contain',
                        backgroundRepeat: 'no-repeat',
                        backgroundPosition: 'center',
                        backgroundColor: url ? 'transparent' : '#f8fafc',
                        color: url ? 'transparent' : '#64748b',
                        borderColor: url ? 'transparent' : '#cbd5e1',
                        display: displayMode,
                        alignItems: 'center',
                        justifyContent: 'center'
                    }).text(url ? '' : 'Assinatura');
                } else {
                    $element.text(data.text);
                }

                $element.removeClass('cert-editor-contrast');
                if (!hasCertificateBackground() && key !== 'platform_logo' && key !== 'instructor_signature' && isLightColor(data.color || '#000000')) {
                    $element.addClass('cert-editor-contrast');
                }

                $element.on('mousedown', function (event) {
                    $('.cert-element').css('border-color', 'transparent');
                    $(this).css('border-color', '#2563eb');
                    activeElementId = key;
                    showStyleControls(key, data);
                    updateLayersList();
                    event.stopPropagation();
                });

                $canvas.append($element);

                if (key === 'platform_logo' || key === 'instructor_signature') {
                    $element.resizable({
                        aspectRatio: false,
                        disabled: !!data.locked,
                        handles: 'n, e, s, w, ne, se, sw, nw',
                        stop: function (event, ui) {
                            certSettings[key].width = ui.size.width;
                            certSettings[key].height = ui.size.height;
                            if (key === 'platform_logo') {
                                $('#logo-width').val(Math.round(ui.size.width));
                                $('#logo-height').val(Math.round(ui.size.height));
                            }
                        }
                    });
                }
            });

            $('.cert-element').draggable({
                containment: false,
                scroll: false,
                start: function () {
                    const key = $(this).data('tag');
                    if (certSettings[key] && certSettings[key].locked) {
                        return false;
                    }
                },
                stop: function (event, ui) {
                    const key = $(this).data('tag');
                    const parentWidth = $('#cert-canvas').width();
                    const parentHeight = $('#cert-canvas').height();
                    let x = (ui.position.left / parentWidth) * 100;
                    let y = (ui.position.top / parentHeight) * 100;

                    if ($('#cert-snap-enabled').is(':checked')) {
                        const step = parseFloat($('#cert-snap-step').val()) || 1;
                        x = Math.round(x / step) * step;
                        y = Math.round(y / step) * step;
                        $(this).css({ left: x + '%', top: y + '%' });
                    }

                    certSettings[key].x = x;
                    certSettings[key].y = y;

                    if (activeElementId === key) {
                        $('#style-x').val(parseFloat(x).toFixed(2));
                        $('#style-y').val(parseFloat(y).toFixed(2));
                    }

                    updateLayersList();
                }
            });

            $('.cert-toggle').each(function () {
                const key = $(this).data('tag');
                if (key === 'platform_logo') {
                    $(this).prop('checked', true);
                    $('#el-platform_logo').show();
                    return;
                }

                const visible = certSettings[key] ? (certSettings[key].visible !== false) : true;
                $(this).prop('checked', visible);
            });

            if (certSettings.platform_logo) {
                $('#logo-width').val(certSettings.platform_logo.width || 120);
                $('#logo-height').val(certSettings.platform_logo.height || 60);
                $('#logo-width, #logo-height').prop('disabled', !!certSettings.platform_logo.locked);
            }

            updateLayersList();
        }

        function clampPercent(value) {
            return Math.max(0, Math.min(100, value));
        }

        function nudgeSelected(dx, dy) {
            if (!activeElementId) {
                return;
            }

            const data = certSettings[activeElementId];
            if (!data || data.locked) {
                return;
            }

            let x = parseFloat(data.x);
            let y = parseFloat(data.y);
            x = isNaN(x) ? 0 : x;
            y = isNaN(y) ? 0 : y;

            x = clampPercent(x + dx);
            y = clampPercent(y + dy);

            if ($('#cert-snap-enabled').is(':checked')) {
                const step = parseFloat($('#cert-snap-step').val()) || 1;
                x = Math.round(x / step) * step;
                y = Math.round(y / step) * step;
            }

            x = Math.round(x * 10000) / 10000;
            y = Math.round(y * 10000) / 10000;

            data.x = x;
            data.y = y;

            $('#el-' + activeElementId).css({ left: x + '%', top: y + '%' });
            $('#style-x').val(parseFloat(x).toFixed(2));
            $('#style-y').val(parseFloat(y).toFixed(2));
        }

        function syncDocumentBeforeSubmit() {
            certDoc.meta = certDoc.meta || {};
            certDoc.meta.backgroundFit = $('#cert-bg-fit').val() || 'cover';
            certDoc.meta.titleText = $('#certificate_title').val() || '';
            certDoc.meta.presentationText = $('#presentation_text').val() || '';

            if (certSettings.platform_logo) {
                certSettings.platform_logo.visible = true;
                certSettings.platform_logo.mandatory = true;
            }

            $('#certificate_settings_input').val(JSON.stringify(certDoc));
        }

        renderElements();
        $('#cert-bg-fit').val(certDoc.meta.backgroundFit || 'cover');
        applyBackgroundFit();
        updateGridOverlay();
        applyZoom(parseFloat($('#cert-zoom').val()) || 1);

        $('#cert-zoom').on('change', function () {
            applyZoom(parseFloat($(this).val()) || 1);
        });
        $('#cert-fit').on('click', fitCanvas);
        $('#cert-grid-enabled').on('change', updateGridOverlay);
        $('#cert-grid-step').on('change', updateGridOverlay);
        $('#cert-bg-fit').on('change', function () {
            certDoc.meta.backgroundFit = $(this).val() || 'cover';
            applyBackgroundFit();
        });

        const observer = new MutationObserver(scheduleFitCanvas);
        const rootElement = document.getElementById('certificate-editor-root');
        if (rootElement) {
            observer.observe(rootElement, { attributes: true, attributeFilter: ['style', 'class'] });
        }
        $(window).on('resize', scheduleFitCanvas);
        scheduleFitCanvas();

        $('#cert-canvas, #cert-elements-layer, #cert-grid-overlay').on('mousedown', function (event) {
            if (event.target.id === 'cert-canvas' || event.target.id === 'cert-elements-layer' || event.target.id === 'cert-grid-overlay') {
                clearSelection();
            }
        });

        if (fontsApiUrl) {
            $.ajax({
                url: fontsApiUrl,
                type: 'GET'
            }).done(function (fonts) {
                (fonts || []).forEach(function (font) {
                    $('#style-font-family').append($('<option>').val(font.font_family).text(font.name));
                    if (font.type === 'google_link' && font.google_font_url) {
                        $('head').append('<link href="' + font.google_font_url + '" rel="stylesheet">');
                    } else if (font.type === 'file' && font.file_path) {
                        $('head').append('<style>@font-face { font-family: "' + font.font_family + '"; src: url("' + '{{ asset('') }}' + font.file_path + '"); }</style>');
                    }
                });
            });
        }

        $('#certificate_title').on('input', function () {
            const value = $(this).val() || '';
            certDoc.meta.titleText = value;
            if (certSettings.title) {
                certSettings.title.text = value;
                $('#el-title').text(value);
            }
        });

        $('#presentation_text').on('input', function () {
            const value = $(this).val() || '';
            certDoc.meta.presentationText = value;
            if (certSettings.presentation_text) {
                certSettings.presentation_text.text = value;
                $('#el-presentation_text').text(value);
            }
        });

        $('#certificate_bg').on('change', function () {
            const file = this.files && this.files[0] ? this.files[0] : null;
            $('#certificate_bg_label').text(file ? file.name : 'Selecionar arquivo');
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                $('#cert-bg-img').attr('src', event.target.result).removeClass('hidden');
                $('#cert-bg-placeholder').addClass('hidden');
                applyBackgroundFit();
                updateEditorContrastMode();
                renderElements();
            };
            reader.readAsDataURL(file);
        });

        $('#instructor_signature').on('change', function () {
            const file = this.files && this.files[0] ? this.files[0] : null;
            $('#instructor_signature_label').text(file ? file.name : 'Substituir assinatura');
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                instructorSignaturePreviewUrl = event.target.result;
                $('#signaturePreview').attr('src', instructorSignaturePreviewUrl);
                $('#signaturePreviewWrapper').removeClass('hidden');
                $('#signatureEmptyState').addClass('hidden');

                if (certSettings.instructor_signature) {
                    certSettings.instructor_signature.visible = true;
                }
                $('.cert-toggle[data-tag="instructor_signature"]').prop('checked', true);

                const $signatureElement = $('#el-instructor_signature');
                if ($signatureElement.length) {
                    const hidden = certSettings.instructor_signature && certSettings.instructor_signature.visible === false;
                    $signatureElement.css({
                        backgroundImage: 'url("' + instructorSignaturePreviewUrl + '")',
                        backgroundSize: 'contain',
                        backgroundRepeat: 'no-repeat',
                        backgroundPosition: 'center',
                        backgroundColor: 'transparent',
                        borderColor: 'transparent',
                        color: 'transparent',
                        display: hidden ? 'none' : 'block'
                    }).text('');
                }

                updateLayersList();
            };
            reader.readAsDataURL(file);
        });

        $('#style-font-size').on('input', function () {
            if (!activeElementId) return;
            const value = $(this).val();
            certSettings[activeElementId].fontSize = value;
            $('#el-' + activeElementId).css('font-size', value + 'px');
        });
        $('#style-z-index').on('input', function () {
            if (!activeElementId) return;
            const value = $(this).val();
            certSettings[activeElementId].zIndex = value;
            $('#el-' + activeElementId).css('z-index', value);
            updateLayersList();
        });
        $('#style-color').on('input', function () {
            if (!activeElementId) return;
            const value = $(this).val();
            certSettings[activeElementId].color = value;
            $('#el-' + activeElementId).css('color', value);
        });
        $('#style-font-weight').on('change', function () {
            if (!activeElementId) return;
            const value = $(this).val();
            certSettings[activeElementId].fontWeight = value;
            $('#el-' + activeElementId).css('font-weight', value);
        });
        $('#style-font-family').on('change', function () {
            if (!activeElementId) return;
            const value = $(this).val();
            certSettings[activeElementId].fontFamily = value;
            $('#el-' + activeElementId).css('font-family', value);
        });
        $('#style-x').on('input', function () {
            if (!activeElementId) return;
            const value = parseFloat($(this).val());
            if (isNaN(value)) return;
            certSettings[activeElementId].x = value;
            $('#el-' + activeElementId).css('left', value + '%');
        });
        $('#style-y').on('input', function () {
            if (!activeElementId) return;
            const value = parseFloat($(this).val());
            if (isNaN(value)) return;
            certSettings[activeElementId].y = value;
            $('#el-' + activeElementId).css('top', value + '%');
        });
        $('#style-locked').on('change', function () {
            if (!activeElementId) return;
            const locked = $(this).is(':checked');
            certSettings[activeElementId].locked = locked;
            const $element = $('#el-' + activeElementId);
            $element.css('cursor', locked ? 'not-allowed' : 'move');
            try { locked ? $element.draggable('disable') : $element.draggable('enable'); } catch (error) {}
            try { locked ? $element.resizable('disable') : $element.resizable('enable'); } catch (error) {}
            if (activeElementId === 'platform_logo') {
                $('#logo-width, #logo-height').prop('disabled', locked);
            }
            updateLayersList();
        });

        $(document).on('keydown.certNudgePanel', function (event) {
            if (!activeElementId || !$root.is(':visible')) {
                return;
            }

            const $target = $(event.target);
            if ($target.is('input, textarea, select') || $target.closest('input, textarea, select').length) {
                return;
            }
            if (event.ctrlKey || event.metaKey || event.altKey) {
                return;
            }

            let step = parseFloat($('#cert-nudge-step').val());
            if (isNaN(step) || step <= 0) step = 0.5;
            if (event.shiftKey) step = step * 5;

            let dx = 0;
            let dy = 0;
            if (event.key === 'ArrowLeft') dx = -step;
            else if (event.key === 'ArrowRight') dx = step;
            else if (event.key === 'ArrowUp') dy = -step;
            else if (event.key === 'ArrowDown') dy = step;
            else return;

            event.preventDefault();
            nudgeSelected(dx, dy);
        });

        $('#logo-width').on('input', function () {
            const value = Math.max(50, Math.min(400, parseInt($(this).val(), 10) || 120));
            if (certSettings.platform_logo && certSettings.platform_logo.locked) {
                $(this).val(certSettings.platform_logo.width || 120);
                return;
            }
            certSettings.platform_logo.width = value;
            $('#el-platform_logo').css('width', value + 'px');
        });

        $('#logo-height').on('input', function () {
            const value = Math.max(30, Math.min(200, parseInt($(this).val(), 10) || 60));
            if (certSettings.platform_logo && certSettings.platform_logo.locked) {
                $(this).val(certSettings.platform_logo.height || 60);
                return;
            }
            certSettings.platform_logo.height = value;
            $('#el-platform_logo').css('height', value + 'px');
        });

        $('.cert-toggle').on('change', function () {
            const key = $(this).data('tag');
            if (key === 'platform_logo') {
                $(this).prop('checked', true);
                toastr.warning('A logo da plataforma e obrigatoria e nao pode ser removida.');
                return;
            }

            if ($(this).is(':checked')) {
                certSettings[key].visible = true;
                if (key === 'instructor_signature') {
                    const hasUrl = !!instructorSignaturePreviewUrl;
                    $('#el-' + key).css('display', hasUrl ? 'block' : 'flex');
                } else {
                    $('#el-' + key).show();
                }
            } else {
                certSettings[key].visible = false;
                $('#el-' + key).hide();
            }

            updateLayersList();
        });

        $form.on('submit', function () {
            syncDocumentBeforeSubmit();
        });

        if (previewUrl) {
            window.previewCertificate = function () {
                syncDocumentBeforeSubmit();
                const form = $form[0];
                const originalAction = form.action;
                const originalTarget = form.target;
                form.action = previewUrl;
                form.target = '_blank';
                form.submit();
                form.action = originalAction;
                form.target = originalTarget;
                return false;
            };
        }
    });
