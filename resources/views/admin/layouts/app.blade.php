<!doctype html>
<html lang="pt-BR">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        use Illuminate\Support\Str;

        try {
            $adminLayoutSettings = \App\Models\Setting::allValues();
        } catch (\Throwable $e) {
            $adminLayoutSettings = [];
        }

        if (isset($settings) && is_array($settings)) {
            $adminLayoutSettings = array_replace($adminLayoutSettings, array_intersect_key($settings, array_flip([
                'site_theme',
                'preloader_image',
                'preloader_enabled',
                'favicon_image',
            ])));
        }

        $siteTheme = ($adminLayoutSettings['site_theme'] ?? 'light') === 'dark' ? 'dark' : 'light';

        $isAdmin = false;
        try {
            if (auth()->check()) {
                $u = auth()->user();
                $isAdmin = method_exists($u, 'isAdmin') ? $u->isAdmin() : false;
            }
        } catch (\Throwable $e) {
            $isAdmin = false;
        }

        $resolvePublicAsset = function (?string $value) {
            if (!$value) {
                return null;
            }

            $value = trim((string) $value);
            if ($value === '') {
                return null;
            }

            if (Str::startsWith($value, ['http://', 'https://'])) {
                return $value;
            }

            $value = str_replace('\\', '/', $value);
            $value = preg_replace('/[?#].*$/', '', $value);

            $publicRoot = str_replace('\\', '/', public_path());
            if (Str::startsWith($value, $publicRoot)) {
                $value = ltrim(substr($value, strlen($publicRoot)), '/');
            }

            $value = ltrim($value, '/');

            if (Str::startsWith($value, 'public/')) {
                $value = substr($value, strlen('public/'));
            }

            if (Str::startsWith($value, 'storage/app/public/')) {
                $value = 'storage/' . substr($value, strlen('storage/app/public/'));
            }

            $candidate = public_path($value);
            if (file_exists($candidate)) {
                return asset($value);
            }

            return null;
        };

        $preloaderImage = $resolvePublicAsset($adminLayoutSettings['preloader_image'] ?? null) ?? asset('img/logo.svg');
        $preloaderEnabled = (bool) ($adminLayoutSettings['preloader_enabled'] ?? 1);
        $faviconUrl = $resolvePublicAsset($adminLayoutSettings['favicon_image'] ?? null) ?? asset('favicon.ico');
        $faviconPath = parse_url($faviconUrl, PHP_URL_PATH) ?: '';
        $faviconExtension = strtolower(pathinfo($faviconPath, PATHINFO_EXTENSION));
        $faviconType = match ($faviconExtension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/x-icon',
        };

        $needsChartsAssets = request()->routeIs('admin.dashboard*', 'admin.waf.*', 'admin.analytics.*', 'admin.reports*', 'admin.referrals*', 'admin.orders.sales-report*') || View::hasSection('needs_charts');
        $needsEditorAssets = request()->routeIs('admin.*.create', 'admin.*.edit', 'admin.cms*', 'admin.pages*', 'admin.settings*', 'admin.marketplace.store*', 'admin.mailtemplates*', 'admin.events.calendar', 'admin.invoices.editor*') || View::hasSection('needs_editor');
        $needsFlatpickrAssets = $needsEditorAssets || request()->routeIs('admin.events.*', 'admin.coupons*', 'admin.orders*');
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - UNN')</title>
    <link rel="icon" href="{{ $faviconUrl }}" type="{{ $faviconType }}">
    <link rel="shortcut icon" href="{{ $faviconUrl }}" type="{{ $faviconType }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- AdminLTE 4.0 + Bootstrap 5.3 + OverlayScrollbars v2 + FontAwesome 6 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/css/adminlte.min.css">

    @if($needsChartsAssets)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jqvmap.min.css">
    @endif
    @if($needsEditorAssets)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/css/bootstrap-colorpicker.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">
    @endif
    @if($needsFlatpickrAssets)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css">

    @if(!$isAdmin)
        <link rel="stylesheet" href="{{ unn_asset('css/unn-tailwind-nopreflight.css') }}">
    @endif

    {{-- Tema Limpo UNN para AdminLTE 4 / Bootstrap 5 --}}
    <link rel="stylesheet" href="{{ unn_asset('css/adminlte4-unn.css') }}">

    @stack('styles')
</head>

<body class="{{ $isAdmin ? 'layout-fixed sidebar-expand-lg bg-body-tertiary' : 'bg-slate-50 min-h-screen' }} {{ $siteTheme === 'dark' ? 'dark-mode' : '' }}" data-bs-theme="{{ $siteTheme }}">
    @include('partials.modern-preloader')
    @include('partials.help-drawer')

    {{-- Badge de Impersonation - Flutuante discreto --}}
    @if(session()->has('impersonator_id'))
        <div id="impersonation-badge"
            class="position-fixed bg-warning text-dark px-3 py-2 rounded shadow fw-bold d-flex align-items-center"
            style="bottom: 1rem; left: 1rem; z-index: 9999; font-size: 0.8rem; max-width: 260px;">
            <i class="fas fa-user-secret me-2"></i>
            <span class="text-truncate me-2">
                Supervisor: {{ session('impersonator_name', 'Administrador') }}<br>
                Conta: {{ session('impersonated_user_name', auth()->user()?->name ?? 'Conta supervisionada') }}
            </span>
            <a href="{{ route('admin.impersonate.stop') }}" class="btn btn-xs btn-danger">Sair</a>
            <button type="button" onclick="document.getElementById('impersonation-badge').style.display='none'"
                class="btn btn-xs btn-link text-dark ms-1 p-0" title="Minimizar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if($isAdmin)
        <div class="app-wrapper">
            @include('admin.partials.navbar')
            @include('admin.partials.sidebar')

            <main class="app-main">
                @if(View::hasSection('page_title') || View::hasSection('breadcrumb') || View::hasSection('breadcrumb_items'))
                    <div class="app-content-header">
                        <div class="container-fluid">
                            <div class="row align-items-center">
                                <div class="col-sm-6">
                                    <h3 class="mb-0 fw-bold fs-4">@yield('page_title')</h3>
                                </div>
                                <div class="col-sm-6">
                                    <ol class="breadcrumb float-sm-end mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" data-pjax>Home</a></li>
                                        @hasSection('breadcrumb_items')
                                            @yield('breadcrumb_items')
                                        @else
                                            @hasSection('breadcrumb')
                                                @yield('breadcrumb')
                                            @else
                                                <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
                                            @endif
                                        @endif
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="app-content">
                    <div class="container-fluid" id="pjax-container">
                        @yield('content')
                    </div>
                </div>
            </main>

            @include('admin.partials.footer')
        </div>

        @include('admin.partials.control-sidebar')
        @include('admin.partials.quick-upload-modal')
    @else
        @include('partials.header')

        <main class="pt-20 lg:pt-24 min-h-[calc(100vh-80px)]">
            <div class="max-w-7xl mx-auto px-4 md:px-10 lg:px-16 py-8">
                <div class="flex flex-col lg:flex-row gap-6">
                    <aside class="w-full lg:w-80">
                        @include('panel.partials.sidebar')
                    </aside>

                    <div class="flex-1">
                        @if(View::hasSection('page_title'))
                            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-6">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">@yield('page_title')</h1>
                                        @hasSection('breadcrumb')
                                            <ol class="breadcrumb mb-0 mt-2 text-sm">
                                                @yield('breadcrumb')
                                            </ol>
                                        @endif
                                    </div>
                                    <a href="{{ route('panel.dashboard') }}"
                                        class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                                        <i class="fas fa-arrow-left me-2"></i> Voltar ao painel
                                    </a>
                                </div>
                            </div>
                        @endif

                        <div id="pjax-container">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </main>

        @includeWhen(true, 'partials.footer')
    @endif

    {{-- Core JS: jQuery 3.7 + Popper 2 + Bootstrap 5.3 + OverlayScrollbars v2 + AdminLTE 4 --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/js/adminlte.min.js"></script>
    <script src="{{ unn_asset('js/adminlte4-unn.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js"></script>
    <script src="{{ unn_asset('js/unn-masks.js') }}"></script>

    @if($needsChartsAssets)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-knob-chif@1.2.13/dist/jquery.knob.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jquery.vmap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/maps/jquery.vmap.world.js"></script>
    @endif
    @if($needsEditorAssets)
        <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-pt-BR.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/js/bootstrap-colorpicker.min.js"></script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/jquery-pjax@2.0.1/jquery.pjax.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
    @if($needsFlatpickrAssets)
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    @endif

    <script>
        window.UNNLoadAsset = (function () {
            const loaded = new Set();
            return {
                css(href) {
                    if (!href || loaded.has(href) || document.querySelector('link[href="' + href + '"]')) return Promise.resolve();
                    loaded.add(href);
                    return new Promise((resolve) => {
                        const l = document.createElement('link');
                        l.rel = 'stylesheet';
                        l.href = href;
                        l.onload = resolve;
                        l.onerror = resolve;
                        document.head.appendChild(l);
                    });
                },
                js(src) {
                    if (!src || loaded.has(src) || document.querySelector('script[src="' + src + '"]')) return Promise.resolve();
                    loaded.add(src);
                    return new Promise((resolve) => {
                        const s = document.createElement('script');
                        s.src = src;
                        s.onload = resolve;
                        s.onerror = resolve;
                        document.body.appendChild(s);
                    });
                }
            };
        })();
    </script>

    <script>
        (function () {
            const themeSafeColors = new Set([
                '', '#000', '#000000', 'black', 'rgb(0,0,0)', 'rgb(0, 0, 0)',
                '#212529', 'rgb(33,37,41)', 'rgb(33, 37, 41)',
                '#fff', '#ffffff', 'white', 'rgb(255,255,255)', 'rgb(255, 255, 255)',
                '#e5e7eb', '#f8fafc', 'rgb(229,231,235)', 'rgb(229, 231, 235)',
                'rgb(248,250,252)', 'rgb(248, 250, 252)',
            ]);

            function isDarkTheme() {
                return document.body.getAttribute('data-bs-theme') === 'dark' || document.body.classList.contains('dark-mode');
            }

            function currentColorButtonConfig() {
                return isDarkTheme()
                    ? { foreColor: '#E5E7EB', backColor: '#111827' }
                    : { foreColor: '#212529', backColor: '#FFFFFF' };
            }

            function normalizeColorValue(value) {
                return String(value || '').trim().toLowerCase().replace(/\s+/g, '');
            }

            function shouldReplaceThemeColor(value) {
                return themeSafeColors.has(normalizeColorValue(value));
            }

            function refreshSummernoteColorButtons() {
                const themeColors = currentColorButtonConfig();
                document.querySelectorAll('.note-editor .note-current-color-button').forEach((button) => {
                    const currentFore = button.getAttribute('data-foreColor') || '';
                    const currentBack = button.getAttribute('data-backColor') || '';
                    const nextFore = shouldReplaceThemeColor(currentFore) ? themeColors.foreColor : currentFore;
                    const nextBack = shouldReplaceThemeColor(currentBack) ? themeColors.backColor : currentBack;
                    const preview = button.querySelector('.note-recent-color');

                    button.setAttribute('data-foreColor', nextFore);
                    button.setAttribute('data-backColor', nextBack);

                    if (preview) {
                        preview.style.color = nextFore;
                        preview.style.backgroundColor = nextBack;
                    }
                });
            }

            function refreshSummernotePaletteButtons() {
                document.querySelectorAll('.note-editor .note-color-btn[data-value]').forEach((button) => {
                    const swatchColor = button.getAttribute('data-value');
                    if (!swatchColor) return;
                    button.style.setProperty('background-color', swatchColor, 'important');
                    button.style.setProperty('background-image', 'none', 'important');
                });
            }

            function syncSummernoteThemeDefaults() {
                if (!(window.jQuery && $.summernote && $.summernote.options)) {
                    return;
                }
                $.summernote.options.colorButton = {
                    ...($.summernote.options.colorButton || {}),
                    ...currentColorButtonConfig(),
                };
                refreshSummernoteColorButtons();
                refreshSummernotePaletteButtons();
            }

            window.syncSummernoteThemeDefaults = syncSummernoteThemeDefaults;

            $(function () {
                syncSummernoteThemeDefaults();
            });

            if (!window.__unnAdminSummernoteThemeObserver) {
                let rafId = null;
                const scheduleSync = function () {
                    if (rafId !== null) cancelAnimationFrame(rafId);
                    rafId = requestAnimationFrame(function () {
                        rafId = null;
                        syncSummernoteThemeDefaults();
                    });
                };

                const observer = new MutationObserver(function (mutations) {
                    const shouldRefresh = mutations.some(function (mutation) {
                        if (mutation.type === 'attributes') return true;
                        return Array.from(mutation.addedNodes || []).some(function (node) {
                            return node.nodeType === 1 && (
                                node.matches('.note-editor, .note-current-color-button') ||
                                node.querySelector('.note-editor, .note-current-color-button')
                            );
                        });
                    });
                    if (shouldRefresh) scheduleSync();
                });

                observer.observe(document.body, { attributes: true, attributeFilter: ['class', 'data-bs-theme'] });
                observer.observe(document.body, { childList: true, subtree: true });
                window.__unnAdminSummernoteThemeObserver = observer;
            }
        })();
    </script>

    @include('partials.global-sweetalert-confirm')
    @php($unnAjaxAutoBind = false)
    @php($unnAjaxPreferPjax = true)
    @php($unnAjaxPjaxContainer = '#pjax-container')
    @include('partials.global-ajax-crud')
    @include('admin.partials.notifications')
    @include('admin.partials.chat-widget')

    <style>
        .admin-back-to-top {
            position: fixed;
            right: 1rem;
            bottom: 6.3rem;
            width: 44px;
            height: 44px;
            border: 0;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            cursor: pointer;
            z-index: 1055;
            opacity: 0;
            transform: translateY(8px);
            pointer-events: none;
            transition: opacity .2s ease, transform .2s ease, box-shadow .2s ease;
            background: linear-gradient(135deg, #1F5EDB, #177FD6, #1D3FC4);
            box-shadow: 0 12px 26px rgba(31, 94, 219, .32);
        }
        .admin-back-to-top:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(31, 94, 219, .38);
        }
        .admin-back-to-top.is-visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        @media (max-width: 768px) {
            .admin-back-to-top {
                right: .85rem;
                bottom: 5.8rem;
                width: 40px;
                height: 40px;
            }
        }
        .admin-upload-progress {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1070;
            width: min(26rem, calc(100vw - 2rem));
            display: none;
        }
        .admin-upload-progress.is-visible { display: block; }
        .admin-upload-progress__card {
            padding: 1rem 1.05rem;
            border-radius: 1rem;
            background: rgba(15, 23, 42, 0.95);
            color: #e2e8f0;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.32);
            backdrop-filter: blur(18px);
        }
        .admin-upload-progress__header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
        .admin-upload-progress__eyebrow { margin: 0 0 .3rem; font-size: .72rem; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; color: #7dd3fc; }
        .admin-upload-progress__title { margin: 0; font-size: 1rem; font-weight: 700; color: #fff; }
        .admin-upload-progress__metrics { text-align: right; }
        .admin-upload-progress__percent { font-size: .95rem; font-weight: 800; color: #fff; }
        .admin-upload-progress__size { font-size: .72rem; color: #cbd5e1; }
        .admin-upload-progress__bar { width: 100%; height: .62rem; margin-top: .95rem; border-radius: 999px; overflow: hidden; background: rgba(148, 163, 184, 0.18); }
        .admin-upload-progress__fill { display: block; width: 0; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #38bdf8, #2563eb); transition: width .18s ease; }
        .admin-upload-progress__footer { display: flex; align-items: center; justify-content: space-between; gap: .8rem; margin-top: .75rem; font-size: .72rem; color: #cbd5e1; }
    </style>

    <button type="button" id="adminBackToTop" class="admin-back-to-top" aria-label="Voltar ao topo" title="Voltar ao topo">
        <i class="fas fa-chevron-up"></i>
    </button>
    <script>
        (function () {
            const button = document.getElementById('adminBackToTop');
            if (!button) return;
            const onScroll = function () {
                button.classList.toggle('is-visible', window.scrollY > 420);
            };
            button.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();
    </script>

    @include('partials.global-placeholder-fix')
    @stack('scripts')
    @include('partials.form-draft-autosave')

    <script>
        $(document).ready(function () {
            @if(session('toastr_success'))
                toastr.success("{{ session('toastr_success') }}");
            @endif
            @if(session('toastr_error'))
                toastr.error("{{ session('toastr_error') }}");
            @endif
            @if(session('toastr_info'))
                toastr.info("{{ session('toastr_info') }}");
            @endif
        });

        window.showSuccess = function (msg) {
            toastr.success(msg || 'Sucesso');
        };

        window.showError = function (msg) {
            toastr.error(msg || 'Erro na operação');
        };

        window.showConfirm = function (text, onConfirm) {
            Swal.fire({
                title: 'Confirmar ação',
                text: text || 'Confirme para continuar.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed && typeof onConfirm === 'function') {
                    onConfirm();
                }
            });
        };

        window.UNN_ADMIN_UPLOAD_MAX_BYTES = @json(\App\Support\UploadStorage::effectiveUploadLimitBytes());

        $(function () {
            const container = '#pjax-container';
            $(document).pjax('a[data-pjax="true"]', container, { timeout: 8000 });

            function shouldDisablePjax(href) {
                if (!href) return false;
                const fullReloadPrefixes = ['/admin/events', '/admin/courses', '/admin/marketplace', '/admin/cron'];
                try {
                    const url = new URL(href, window.location.origin);
                    return fullReloadPrefixes.some(function (prefix) {
                        return url.pathname === prefix || url.pathname.startsWith(prefix + '/');
                    });
                } catch (e) {
                    return fullReloadPrefixes.some(function (prefix) {
                        return href.indexOf(prefix) !== -1;
                    });
                }
            }

            $('.sidebar-menu a, .app-header a').each(function () {
                const $a = $(this);
                const h = $a.attr('href') || '';
                if (h.startsWith('http') || h === '#' || $a.attr('target')) return;

                if (shouldDisablePjax(h)) {
                    $a.attr('data-pjax', 'false');
                    return;
                }

                if ($a.is('[data-pjax]') || $a.is('[data-no-pjax]') || $a.hasClass('no-pjax')) return;

                $a.attr('data-pjax', 'true');
            });

            function initLazyAdminAssets(callback) {
                const tasks = [];

                if ($('.summernote').length && typeof $.fn.summernote !== 'function') {
                    tasks.push(Promise.all([
                        window.UNNLoadAsset.css('https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css'),
                        window.UNNLoadAsset.js('https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js')
                            .then(() => window.UNNLoadAsset.js('https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-pt-BR.min.js'))
                    ]));
                }

                if ($('.colorpicker-element').length && typeof $.fn.colorpicker !== 'function') {
                    tasks.push(Promise.all([
                        window.UNNLoadAsset.css('https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/css/bootstrap-colorpicker.min.css'),
                        window.UNNLoadAsset.js('https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/js/bootstrap-colorpicker.min.js')
                    ]));
                }

                if ($('[data-datetime-picker], .flatpickr').length && typeof window.flatpickr === 'undefined') {
                    tasks.push(Promise.all([
                        window.UNNLoadAsset.css('https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css'),
                        window.UNNLoadAsset.js('https://cdn.jsdelivr.net/npm/flatpickr')
                            .then(() => window.UNNLoadAsset.js('https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js'))
                    ]));
                }

                if ($('.upload-box[data-crop="1"], .upload-box[data-crop="true"]').length && typeof window.Cropper === 'undefined') {
                    tasks.push(Promise.all([
                        window.UNNLoadAsset.css('https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css'),
                        window.UNNLoadAsset.js('https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js')
                    ]));
                }

                Promise.all(tasks).then(() => {
                    if ($('.summernote').length && typeof $.fn.summernote === 'function') {
                        $('.summernote').each(function () {
                            if (!$(this).next().hasClass('note-editor')) {
                                $(this).summernote({ height: 180, lang: 'pt-BR' });
                            }
                        });
                    }
                    if (typeof callback === 'function') callback();
                });
            }

            function bindDynamicAdminUI() {
                initLazyAdminAssets(function () {
                    initColorPickers();
                    initDateTimePickers();
                });
                initUploadWidgets();
                initFormUploadProgress(document);
                initMasks();
                initCouponFormEnhancements();
                if (typeof window.initializeFormDraftAutosave === 'function') {
                    window.initializeFormDraftAutosave(document);
                }
            }

            $(document).on('pjax:end', bindDynamicAdminUI);
            bindDynamicAdminUI();
        });

        function ensureAdminUploadProgressCard() {
            let card = document.getElementById('admin-upload-progress');
            if (card) return card;

            card = document.createElement('div');
            card.id = 'admin-upload-progress';
            card.className = 'admin-upload-progress';
            card.innerHTML = '' +
                '<div class="admin-upload-progress__card">' +
                '   <div class="admin-upload-progress__header">' +
                '       <div>' +
                '           <p class="admin-upload-progress__eyebrow">Upload</p>' +
                '           <h3 class="admin-upload-progress__title">Enviando arquivos</h3>' +
                '       </div>' +
                '       <div class="admin-upload-progress__metrics">' +
                '           <div class="admin-upload-progress__percent" data-upload-percent>0%</div>' +
                '           <div class="admin-upload-progress__size" data-upload-size>0 B / 0 B</div>' +
                '       </div>' +
                '   </div>' +
                '   <div class="admin-upload-progress__bar"><span class="admin-upload-progress__fill" data-upload-fill></span></div>' +
                '   <div class="admin-upload-progress__footer">' +
                '       <span data-upload-status>Preparando envio...</span>' +
                '       <span data-upload-remaining>calculando tempo restante...</span>' +
                '   </div>' +
                '</div>';

            document.body.appendChild(card);
            return card;
        }

        function setAdminUploadProgressVisible(isVisible) {
            const card = ensureAdminUploadProgressCard();
            card.classList.toggle('is-visible', !!isVisible);
        }

        function updateAdminUploadProgressCard(state) {
            const card = ensureAdminUploadProgressCard();
            const percent = card.querySelector('[data-upload-percent]');
            const size = card.querySelector('[data-upload-size]');
            const fill = card.querySelector('[data-upload-fill]');
            const status = card.querySelector('[data-upload-status]');
            const remaining = card.querySelector('[data-upload-remaining]');

            if (typeof state.percent === 'number') {
                const safePercent = Math.max(0, Math.min(100, state.percent));
                percent.textContent = Math.round(safePercent) + '%';
                fill.style.width = safePercent + '%';
            }
            if (state.loaded !== undefined && state.total !== undefined) {
                size.textContent = formatUploadBytes(state.loaded) + ' / ' + formatUploadBytes(state.total);
            }
            if (state.status) status.textContent = state.status;
            if (state.remaining) remaining.textContent = state.remaining;
        }

        function formatUploadBytes(bytes) {
            if (!bytes || bytes <= 0) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            const value = bytes / Math.pow(1024, exponent);
            return value.toFixed(value >= 100 || exponent === 0 ? 0 : 1) + ' ' + units[exponent];
        }

        function formatUploadRemainingTime(seconds) {
            if (!Number.isFinite(seconds) || seconds <= 0) return 'calculando tempo restante...';
            const rounded = Math.round(seconds);
            if (rounded < 60) return rounded + 's restantes';
            const minutes = Math.floor(rounded / 60);
            const remainingSeconds = rounded % 60;
            if (minutes < 60) return minutes + 'min ' + remainingSeconds + 's restantes';
            const hours = Math.floor(minutes / 60);
            const remainingMinutes = minutes % 60;
            return hours + 'h ' + remainingMinutes + 'min restantes';
        }

        function normalizeUploadUrl(url) {
            try { return new URL(url, window.location.href).href.replace(/\/$/, ''); }
            catch (error) { return url; }
        }

        function formHasFilesSelected(form) {
            return Array.from(form.querySelectorAll('input[type="file"]')).some(function (input) {
                return !!(input.files && input.files.length);
            });
        }

        function initFormUploadProgress(root) {
            const scope = root || document;
            scope.querySelectorAll('form').forEach(function (form) {
                if (form.dataset.uploadProgressBound === 'true' || form.dataset.uploadProgress === 'false') return;
                if (!form.querySelector('input[type="file"]')) return;
                form.dataset.uploadProgressBound = 'true';

                form.addEventListener('submit', function (event) {
                    if (form.dataset.uploadSubmitting === 'true' || !formHasFilesSelected(form)) return;
                    event.preventDefault();
                    form.dataset.uploadSubmitting = 'true';

                    const startedAt = Date.now();
                    updateAdminUploadProgressCard({
                        percent: 0, loaded: 0, total: 0,
                        status: 'Preparando envio...', remaining: 'calculando tempo restante...'
                    });
                    setAdminUploadProgressVisible(true);

                    const formData = new FormData(form);
                    const submitter = event.submitter;
                    if (submitter && submitter.name && !formData.has(submitter.name)) {
                        formData.append(submitter.name, submitter.value || '1');
                    }

                    const xhr = new XMLHttpRequest();
                    xhr.open((form.getAttribute('method') || 'POST').toUpperCase(), form.getAttribute('action') || window.location.href, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                    xhr.upload.addEventListener('progress', function (uploadEvent) {
                        if (!uploadEvent.lengthComputable) return;
                        const percent = (uploadEvent.loaded / uploadEvent.total) * 100;
                        const elapsedSeconds = Math.max((Date.now() - startedAt) / 1000, 0.2);
                        const speed = uploadEvent.loaded / elapsedSeconds;
                        const remainingSeconds = speed > 0 ? (uploadEvent.total - uploadEvent.loaded) / speed : 0;

                        updateAdminUploadProgressCard({
                            percent: percent, loaded: uploadEvent.loaded, total: uploadEvent.total,
                            status: 'Enviando arquivos do formulário...',
                            remaining: formatUploadRemainingTime(remainingSeconds)
                        });
                    });

                    xhr.addEventListener('load', function () {
                        form.dataset.uploadSubmitting = 'false';
                        const contentType = xhr.getResponseHeader('Content-Type') || '';
                        const currentUrl = normalizeUploadUrl(window.location.href);
                        const responseUrl = normalizeUploadUrl(xhr.responseURL || form.action || window.location.href);

                        if (xhr.status >= 200 && xhr.status < 400) {
                            updateAdminUploadProgressCard({ percent: 100, status: 'Finalizando resposta do servidor...', remaining: 'quase pronto' });

                            if (contentType.includes('application/json') && xhr.responseText && window.UNNAjaxGlobal) {
                                try {
                                    const json = JSON.parse(xhr.responseText);
                                    setAdminUploadProgressVisible(false);
                                    window.UNNAjaxGlobal.handleJsonResponse
                                        ? window.UNNAjaxGlobal.handleJsonResponse(form, json, { preferPjax: true })
                                        : null;
                                    return;
                                } catch (e) { }
                            }
                            if (responseUrl && responseUrl !== currentUrl) {
                                if (window.UNNAjaxGlobal) {
                                    window.UNNAjaxGlobal.navigate(responseUrl, { preferPjax: true, replaceHistory: true });
                                } else {
                                    window.location.assign(responseUrl);
                                }
                                return;
                            }
                            if (contentType.includes('text/html') && xhr.responseText) {
                                if (window.UNNAjaxGlobal) {
                                    window.UNNAjaxGlobal.replaceDocument(xhr.responseText, responseUrl || currentUrl, true);
                                } else {
                                    document.open();
                                    document.write(xhr.responseText);
                                    document.close();
                                }
                                return;
                            }
                            if (window.UNNAjaxGlobal) {
                                window.UNNAjaxGlobal.navigate(window.location.href, { preferPjax: true, replaceHistory: true });
                            } else {
                                window.location.reload();
                            }
                            return;
                        }

                        setAdminUploadProgressVisible(false);
                        let errorMessage = 'Falha ao enviar arquivo. Verifique o tamanho ou o formato e tente novamente.';
                        const serverLimit = parseInt(window.UNN_ADMIN_UPLOAD_MAX_BYTES || 0, 10);

                        if (xhr.status === 413) {
                            errorMessage = 'O servidor recusou o upload porque o arquivo excede o limite permitido'
                                + (serverLimit > 0 ? ' (' + formatUploadBytes(serverLimit) + ')' : '')
                                + '. Envie uma imagem menor e tente novamente.';
                        } else if (xhr.status === 419) {
                            errorMessage = 'Sua sessão expirou. Recarregue a página e tente novamente.';
                        } else if (xhr.status === 403) {
                            errorMessage = 'O servidor bloqueou o upload. Verifique permissões ou regras de segurança.';
                        } else if (xhr.status >= 500) {
                            errorMessage = 'O servidor encontrou um erro interno ao processar o upload. Verifique os logs da aplicação.';
                        }
                        if (contentType.includes('application/json')) {
                            try {
                                const json = JSON.parse(xhr.responseText);
                                errorMessage = json.message || errorMessage;
                                if (json.errors) {
                                    const details = Object.values(json.errors).flat().join('<br>');
                                    errorMessage += '<br><br><span class="text-sm rounded">' + details + '</span>';
                                }
                            } catch (e) { }
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Upload recusado', html: errorMessage, confirmButtonText: 'Entendi' });
                        } else if (typeof toastr !== 'undefined') {
                            toastr.error('Erro no upload: ' + errorMessage.replace(/<br>/g, ' ').replace(/<[^>]+>/g, ''));
                        }
                    });

                    xhr.addEventListener('error', function () {
                        form.dataset.uploadSubmitting = 'false';
                        setAdminUploadProgressVisible(false);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Erro de conexão', text: 'O upload falhou devido a um problema de rede.', confirmButtonText: 'Tentar novamente' });
                        }
                    });

                    xhr.addEventListener('abort', function () {
                        form.dataset.uploadSubmitting = 'false';
                        setAdminUploadProgressVisible(false);
                    });

                    xhr.send(formData);
                });
            });
        }

        toastr.options = { positionClass: 'toast-top-right', timeOut: 3500, progressBar: true };

        $(document).on('submit', 'form', function (e) {
            if ($(this).hasClass('ajax-form') || e.isDefaultPrevented() || !window.UNNAjaxGlobal || !window.UNNAjaxGlobal.shouldHandleForm(this)) {
                return;
            }
            e.preventDefault();
            window.UNNAjaxGlobal.submitForm(this, {
                submitter: (e.originalEvent && e.originalEvent.submitter) || this.__unnAjaxSubmitter || null,
                preferPjax: true,
                forceAjaxHeaders: false,
            });
        });

        $(document).on('submit', '.ajax-form', function (e) {
            if (e.isDefaultPrevented() || !window.UNNAjaxGlobal || !window.UNNAjaxGlobal.shouldHandleForm(this)) {
                return;
            }
            e.preventDefault();
            window.UNNAjaxGlobal.submitForm(this, {
                submitter: (e.originalEvent && e.originalEvent.submitter) || this.__unnAjaxSubmitter || null,
                preferPjax: true,
                forceAjaxHeaders: true,
                successMessage: 'Salvo com sucesso',
            });
        });

        $(document).on('click', '.btn-delete, [data-confirm-delete]', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const url = $btn.data('action') || $btn.attr('href');
            const redirect = $btn.data('redirect') || null;
            const $form = $btn.closest('form');

            showConfirm('Confirme para continuar.', function () {
                if ($btn.is('[data-confirm-delete]') || (!url && $form.length)) {
                    if ($form.length) {
                        if ($form.hasClass('ajax-form')) $form.trigger('submit');
                        else $form.get(0).submit();
                        return;
                    }
                }
                if (!url) {
                    showError('Ação inválida: URL não encontrada.');
                    return;
                }
                $.post(url, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
                    .done(function (resp) {
                        showSuccess('Excluído');
                        if (redirect) { window.location.href = redirect; return; }
                        if (resp && typeof resp === 'object' && resp.redirect) { $.pjax({ url: resp.redirect, container: '#pjax-container' }); return; }
                        $.pjax.reload('#pjax-container');
                    })
                    .fail(function (xhr) {
                        let msg = 'Erro ao excluir';
                        if (xhr && xhr.status === 419) msg = 'Sessão expirada. Recarregue a página e tente novamente.';
                        else if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showError(msg);
                    });
            });
        });

        window.confirmAction = function (event, title, text, onConfirm) {
            if (event && typeof event.preventDefault === 'function') event.preventDefault();
            return Swal.fire({
                title: title || 'Confirmar ação',
                text: text || 'Confirme para continuar.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return false;
                if (typeof onConfirm === 'function') { onConfirm(); return true; }
                if (event) {
                    const target = event.currentTarget || event.target;
                    const form = target && target.closest ? target.closest('form') : null;
                    if (form) { form.submit(); return true; }
                    const link = target && target.getAttribute ? target.getAttribute('href') : null;
                    if (link && link !== '#') { window.location.href = link; return true; }
                }
                return true;
            });
        };

        let adminThemeSaving = false;

        function applyAdminTheme(theme) {
            if (window.UNNAdminTheme && typeof window.UNNAdminTheme.apply === 'function') {
                window.UNNAdminTheme.apply(theme);
            } else {
                const isDark = theme === 'dark';
                document.body.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
                document.body.classList.toggle('dark-mode', isDark);
            }

            const isDark = theme === 'dark';
            $('#site_theme_input').val(theme);
            $('#themeToggleBtn i')
                .toggleClass('fa-moon', !isDark)
                .toggleClass('fa-sun text-warning', isDark);

            if (typeof window.syncSummernoteThemeDefaults === 'function') {
                window.syncSummernoteThemeDefaults();
            }
        }

        $('#themeToggleBtn').on('click', function (event) {
            event.preventDefault();
            if (adminThemeSaving) return;

            const form = document.getElementById('themeToggleForm');
            const input = document.getElementById('site_theme_input');
            if (!form || !input || typeof window.fetch !== 'function') {
                $('#themeToggleForm').submit();
                return;
            }

            const previousTheme = input.value === 'dark' ? 'dark' : 'light';
            const nextTheme = previousTheme === 'dark' ? 'light' : 'dark';
            const formData = new FormData(form);
            formData.set('site_theme', nextTheme);
            formData.set('current_group', 'appearance');
            applyAdminTheme(nextTheme);

            adminThemeSaving = true;
            fetch(form.action, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('Não foi possível salvar o tema.');
                    return response.json().catch(function () { return {}; });
                })
                .catch(function () {
                    applyAdminTheme(previousTheme);
                    if (typeof toastr !== 'undefined') toastr.error('Não foi possível salvar o tema. Tente novamente.');
                })
                .finally(function () {
                    adminThemeSaving = false;
                });
        });

        // Persistência de aba ativa (Bootstrap 5 nav-tabs / nav-pills)
        const tabKey = 'admin-active-tab-' + (location.pathname || 'root');

        $(document).on('shown.bs.tab', 'a[data-bs-toggle="pill"], a[data-bs-toggle="tab"], button[data-bs-toggle="pill"], button[data-bs-toggle="tab"]', function (e) {
            const target = $(e.target).attr('data-bs-target') || $(e.target).attr('href');
            if (target) localStorage.setItem(tabKey, target);
        });

        function restoreActiveTab() {
            if (new URLSearchParams(location.search).get('tab')) return;
            const savedTab = localStorage.getItem(tabKey);
            if (!savedTab) return;
            const tabEl = document.querySelector('a[href="' + savedTab + '"], button[data-bs-target="' + savedTab + '"]');
            if (tabEl && window.bootstrap && window.bootstrap.Tab) {
                window.bootstrap.Tab.getOrCreateInstance(tabEl).show();
            }
        }

        restoreActiveTab();
        $(document).on('pjax:end', restoreActiveTab);

        function initColorPickers() {
            if (typeof $.fn.colorpicker === 'function') {
                $('.colorpicker-element').colorpicker();
            }
        }

        function initMasks() {
            if (typeof window.initUnnMasks === 'function') {
                window.initUnnMasks(document);
            }
        }

        function initDateTimePickers() {
            if (typeof flatpickr === 'undefined') return;
            try {
                if (flatpickr.l10ns && flatpickr.l10ns.pt) flatpickr.localize(flatpickr.l10ns.pt);
            } catch (e) { }

            $('[data-datetime-picker]').each(function () {
                if (this._flatpickr) return;
                flatpickr(this, {
                    enableTime: true, time_24hr: true, allowInput: true, disableMobile: true,
                    altInput: true, altFormat: 'd/m/Y H:i', dateFormat: 'Y-m-d H:i'
                });
            });
        }

        function initCouponFormEnhancements() {
            const form = $('form.ajax-form').filter(function () {
                return String($(this).attr('action') || '').includes('/admin/coupons');
            }).first();
            if (!form.length) return;

            const btnGen = document.getElementById('btnGenCode');
            if (btnGen && !btnGen.dataset.bound) {
                btnGen.dataset.bound = '1';
                btnGen.addEventListener('click', function () {
                    const input = form.find('input[name="code"]').get(0);
                    if (!input) return;
                    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                    let out = '';
                    for (let i = 0; i < 12; i++) out += chars[Math.floor(Math.random() * chars.length)];
                    input.value = out;
                });
            }

            const scopeSelect = form.find('select[name="applies_to"]');
            const itemWrap = form.find('[data-coupon-item-wrap]');
            const itemSelect = form.find('select[name="applies_to_id"]');
            const itemHelp = form.find('[data-coupon-item-help]');
            if (!scopeSelect.length || !itemSelect.length || form.data('couponUiInit')) return;
            form.data('couponUiInit', true);

            function filterItemOptions(scope) {
                itemSelect.find('option').each(function () {
                    const optScope = $(this).data('scope');
                    $(this).prop('hidden', optScope ? optScope !== scope : false);
                });
            }

            function applyScope() {
                const scope = String(scopeSelect.val() || 'all');
                if (scope === 'all') {
                    itemSelect.val('');
                    itemSelect.prop('disabled', true);
                    itemWrap.addClass('d-none');
                    return;
                }
                itemWrap.removeClass('d-none');
                itemSelect.prop('disabled', false);
                filterItemOptions(scope);
                const selected = itemSelect.find('option:selected');
                if (selected.length && selected.prop('hidden')) itemSelect.val('');
                if (itemHelp.length) {
                    const label = scope === 'event' ? 'evento' : (scope === 'course' ? 'curso' : 'mentoria');
                    itemHelp.text('Selecione o ' + label + ' para criar uma promoção direcionada (opcional).');
                }
            }

            scopeSelect.on('change.couponui', applyScope);
            applyScope();
        }

        function initUploadWidgets() {
            $('.upload-box').each(function () {
                const box = $(this);
                if (box.data('uploadInit')) return;
                box.data('uploadInit', true);
                box.attr('tabindex', '0');

                const input = box.find('input[type=file]');
                const preview = box.find('.upload-preview');
                const meta = box.find('.upload-meta');
                const help = box.find('.upload-help');
                const removeBtn = box.find('.upload-remove');
                const progress = box.find('.upload-progress');
                const bar = progress.find('.progress-bar');
                const configuredMaxSize = parseInt(box.data('max-size') || 0, 10);
                const serverMaxSize = parseInt(window.UNN_ADMIN_UPLOAD_MAX_BYTES || 0, 10);
                const maxSizeCandidates = [configuredMaxSize, serverMaxSize].filter(v => Number.isFinite(v) && v > 0);
                const maxSize = maxSizeCandidates.length ? Math.min.apply(Math, maxSizeCandidates) : (5 * 1024 * 1024);
                const crop = box.data('crop') === 1 || box.data('crop') === '1';
                const existingUrl = box.data('existing-url');
                const removeInputSelector = box.data('remove-input');
                const accept = (input.attr('accept') || 'image/*').replace(/\./g, '');
                const acceptLower = String(input.attr('accept') || '').toLowerCase();
                const sizeMb = (maxSize / 1024 / 1024).toFixed(2) + ' MB';

                const previewMaxHeight = box.data('preview-max-height');
                const previewMaxWidth = box.data('preview-max-width');
                const previewStyle = (previewMaxHeight ? 'max-height:' + previewMaxHeight + 'px;' : '') +
                    (previewMaxWidth ? 'max-width:' + previewMaxWidth + 'px;' : '');
                const previewImageStyle = String(box.data('preview-image-style') || previewStyle || '').trim();
                const previewImageClass = String(box.data('preview-image-class') || 'img-fluid').trim();

                if (help.length) help.text('Aceita: ' + accept + ' • Até ' + sizeMb + (crop ? ' • Possível recorte' : ''));

                function kindFromAccept() {
                    if (acceptLower.includes('video/')) return 'video';
                    if (acceptLower.includes('audio/')) return 'audio';
                    if (acceptLower.includes('image/') || acceptLower.trim() === '') return 'image';
                    return 'file';
                }
                const defaultKind = kindFromAccept();

                function kindFromUrl(url) {
                    const clean = String(url || '').split('?')[0].split('#')[0].toLowerCase();
                    if (/\.(png|jpe?g|gif|webp|svg)$/.test(clean)) return 'image';
                    if (/\.(mp4|webm|ogg|mov|m4v)$/.test(clean)) return 'video';
                    if (/\.(mp3|wav|ogg|m4a|aac|flac)$/.test(clean)) return 'audio';
                    return defaultKind;
                }

                function previewHtml(kind, url, label) {
                    const safeLabel = String(label || 'arquivo');
                    if (kind === 'video') return '<video src="' + url + '" controls style="width:100%; max-height: 240px; border-radius: 10px;"></video><div class="text-muted small mt-2">' + safeLabel + '</div>';
                    if (kind === 'audio') return '<audio src="' + url + '" controls style="width:100%;"></audio><div class="text-muted small mt-2">' + safeLabel + '</div>';
                    if (kind === 'file') return '<i class="upload-icon fas fa-file-alt"></i><div class="text-muted small">Arquivo selecionado</div><div class="fw-bold small mt-1" style="word-break: break-word;">' + safeLabel + '</div>' + (url ? '<div class="mt-2"><a href="' + url + '" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary">Abrir</a></div>' : '');
                    return '<img src="' + url + '" alt="preview"' + (previewImageClass ? ' class="' + previewImageClass + '"' : '') + (previewImageStyle ? ' style="' + previewImageStyle + '"' : '') + ' loading="lazy" decoding="async">';
                }

                function renderEmpty() {
                    preview.html('<i class="upload-icon fas fa-cloud-upload-alt"></i><div class="text-muted small">Clique ou arraste para enviar</div>');
                    meta.text('');
                    removeBtn.addClass('d-none');
                }
                function renderExisting(url) {
                    preview.html(previewHtml(kindFromUrl(url), url, 'Arquivo atual'));
                    meta.text('Arquivo atual');
                    removeBtn.removeClass('d-none');
                }

                renderEmpty();
                if (existingUrl) renderExisting(existingUrl);

                function setPreview(blobOrFile, name, url) {
                    const sizeMB = (blobOrFile.size / 1024 / 1024).toFixed(2);
                    const type = String(blobOrFile.type || '').toLowerCase();
                    const kind = type.startsWith('image/') ? 'image' : (type.startsWith('video/') ? 'video' : (type.startsWith('audio/') ? 'audio' : defaultKind));
                    preview.html(previewHtml(kind, url, name || 'arquivo'));
                    meta.text((name || 'arquivo') + ' • ' + sizeMB + ' MB • ' + (blobOrFile.type || ''));
                    removeBtn.removeClass('d-none');
                }

                function bindFileToInput(file) {
                    if (!input.length || !input[0] || typeof DataTransfer === 'undefined') return false;
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.off('change.upload');
                    input[0].files = dataTransfer.files;
                    input.on('change.upload', onInputChange);
                    return true;
                }

                function handleFile(file) {
                    if (!file) return;
                    if (file.size > maxSize) {
                        Swal.fire({ icon: 'error', title: 'Arquivo muito grande', text: 'Este arquivo excede o limite permitido.', confirmButtonColor: '#ef4444', confirmButtonText: 'Entendi' });
                        return;
                    }
                    const reader = new FileReader();
                    bar.css('width', '0%');
                    progress.removeClass('d-none');
                    const start = Date.now();
                    reader.onprogress = function (e) { if (e.lengthComputable) bar.css('width', ((e.loaded / e.total) * 100) + '%'); };
                    reader.onload = function (e) {
                        bar.css('width', '100%');
                        setTimeout(() => progress.addClass('d-none'), 400);
                        const url = e.target.result;
                        const elapsed = (Date.now() - start) / 1000;
                        const speed = file.size / Math.max(elapsed, 0.1);
                        const eta = speed > 0 ? Math.max((file.size - speed * elapsed) / speed, 0) : 0;
                        const extra = ' • ' + (file.size / 1024 / 1024).toFixed(2) + ' MB • ' + (file.type || 'tipo desconhecido') + ' • ~' + eta.toFixed(1) + 's';
                        if (crop) {
                            openCropper(url, { outputType: 'image/jpeg', quality: 0.92, maxSize: maxSize }, function (croppedBlob, croppedUrl) {
                                const fileExt = (croppedBlob.type && croppedBlob.type.split('/')[1]) ? croppedBlob.type.split('/')[1] : 'png';
                                const normalizedExt = fileExt === 'jpeg' ? 'jpg' : fileExt;
                                const croppedFile = new File([croppedBlob], file.name.replace(/\.[^/.]+$/, '') + '.' + normalizedExt, { type: croppedBlob.type });
                                bindFileToInput(croppedFile);
                                setPreview(croppedFile, croppedFile.name + extra, croppedUrl);
                            });
                        } else {
                            setPreview(file, file.name + extra, url);
                        }
                    };
                    reader.readAsDataURL(file);
                }

                function openFileDialog() {
                    if (box.data('opening')) return;
                    if (!input.length || !input[0]) {
                        Swal.fire({ icon: 'error', title: 'Erro no sistema', text: 'Campo de upload não encontrado.', confirmButtonColor: '#ef4444', confirmButtonText: 'Entendi' });
                        return;
                    }
                    box.data('opening', true);
                    const reset = () => { box.data('opening', false); $(window).off('focus.upload', reset); };
                    $(window).one('focus.upload', reset);
                    input[0].click();
                }

                function onInputChange() {
                    const file = this.files && this.files[0] ? this.files[0] : null;
                    if (removeInputSelector) $(removeInputSelector).val('0');
                    handleFile(file);
                }

                box.off('.upload'); input.off('.upload'); removeBtn.off('.upload'); box.find('.upload-btn').off('.upload');
                box.on('click.upload', function (e) { if (!$(e.target).closest('.upload-btn, .upload-remove, input').length) openFileDialog(); });
                box.on('keydown.upload', function (e) { if (['Enter', ' '].includes(e.key)) { e.preventDefault(); openFileDialog(); } });
                box.on('dragover.upload', function (e) { e.preventDefault(); box.addClass('dragover'); });
                box.on('dragleave.upload drop.upload', function (e) { e.preventDefault(); box.removeClass('dragover'); });
                box.on('drop.upload', function (e) {
                    e.preventDefault();
                    const f = e.originalEvent.dataTransfer.files[0];
                    if (!f) return;
                    if (removeInputSelector) $(removeInputSelector).val('0');
                    bindFileToInput(f);
                    handleFile(f);
                });
                box.find('.upload-btn').on('click.upload', function (e) { e.preventDefault(); e.stopPropagation(); openFileDialog(); });
                input.on('click.upload', function (e) { e.stopPropagation(); });
                input.on('change.upload', onInputChange);
                removeBtn.on('click.upload', function (e) {
                    e.preventDefault(); e.stopPropagation();
                    input.val(''); renderEmpty();
                    if (removeInputSelector) $(removeInputSelector).val('1');
                });
            });
        }

        function openCropper(imageUrl, options, callback) {
            if (typeof options === 'function') { callback = options; options = {}; }
            options = options || {};
            const outputType = options.outputType || 'image/jpeg';
            const quality = typeof options.quality === 'number' ? options.quality : 0.92;
            const maxSize = options.maxSize || null;

            const modalId = 'cropperModal';
            let modal = $('#' + modalId);
            if (!modal.length) {
                $('body').append(
                    '<div class="modal fade" id="' + modalId + '" tabindex="-1">' +
                    '<div class="modal-dialog modal-lg"><div class="modal-content">' +
                    '<div class="modal-header"><h5 class="modal-title">Cortar imagem</h5>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>' +
                    '<div class="modal-body"><div style="max-height:500px;">' +
                    '<img id="' + modalId + '-img" style="max-width:100%;" loading="lazy" decoding="async"></div></div>' +
                    '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
                    '<button type="button" class="btn btn-primary" id="' + modalId + '-apply">Aplicar</button>' +
                    '</div></div></div></div>'
                );
                modal = $('#' + modalId);
            }
            const img = $('#' + modalId + '-img');
            let cropper;
            modal.off('shown.bs.modal hidden.bs.modal')
                .on('shown.bs.modal', function () {
                    img.attr('src', imageUrl);
                    cropper = new Cropper(img[0], { aspectRatio: NaN, viewMode: 1 });
                })
                .on('hidden.bs.modal', function () {
                    cropper && cropper.destroy();
                    cropper = null;
                });

            $('#' + modalId + '-apply').off('click').on('click', function () {
                if (!cropper) return;
                cropper.getCroppedCanvas({ fillColor: '#fff' }).toBlob(function (blob) {
                    if (!blob) {
                        Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha ao gerar a imagem recortada.' });
                        return;
                    }
                    if (maxSize && blob.size > maxSize) {
                        Swal.fire({ icon: 'error', title: 'Erro', text: 'A imagem recortada excede o limite de ' + (maxSize / 1024 / 1024).toFixed(2) + ' MB.' });
                        return;
                    }
                    callback(blob, URL.createObjectURL(blob));
                    modal.modal('hide');
                }, outputType, quality);
            });
            modal.modal('show');
        }
    </script>
</body>

</html>
