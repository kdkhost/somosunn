<!doctype html>
@php
    use Illuminate\Support\Str;

    try {
        $adminLayoutSettings = \App\Models\Setting::allValues();
    } catch (\Throwable $e) {
        $adminLayoutSettings = [];
    }

    $userThemePref = auth()->check() ? (auth()->user()->theme_pref ?? null) : null;
    $siteTheme = ($userThemePref ?: ($adminLayoutSettings['site_theme'] ?? 'light')) === 'dark' ? 'dark' : 'light';

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
        return file_exists(public_path($value)) ? asset($value) : null;
    };

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
@endphp
<html lang="pt-BR" data-bs-theme="{{ $siteTheme }}" class="{{ $siteTheme === 'dark' ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel Administrativo') - {{ $adminLayoutSettings['app_name'] ?? 'SOMOS UNN' }}</title>

    <link rel="icon" href="{{ $faviconUrl }}" type="{{ $faviconType }}">
    <link rel="shortcut icon" href="{{ $faviconUrl }}" type="{{ $faviconType }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- AdminLTE 4.0 + Bootstrap 5.3 + OverlayScrollbars v2 + FontAwesome 6 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/css/adminlte.min.css">

    {{-- Compatibilidade Utilitária Tailwind Sem Preflight (preserva 100% das 66 views panel/admin/*) --}}
    <link rel="stylesheet" href="{{ unn_asset('css/unn-tailwind-nopreflight.css') }}">

    {{-- Plugins das views panel/admin/* --}}
    <link href="{{ asset('vendor/filepond/filepond.css') }}?v=3" rel="stylesheet">
    <link href="{{ asset('vendor/filepond/plugins/filepond-plugin-image-preview.css') }}?v=3" rel="stylesheet">
    <link href="{{ asset('vendor/filepond/plugins/filepond-plugin-file-validate-size.css') }}?v=3" rel="stylesheet">
    <link href="{{ asset('vendor/filepond/plugins/filepond-plugin-file-validate-type.css') }}?v=3" rel="stylesheet">
    <link href="{{ asset('vendor/flatpickr/css/flatpickr.min.css') }}?v=3" rel="stylesheet">
    <link href="https://unpkg.com/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css">

    {{-- Tema Oficial UNN AdminLTE 4 (Light & Dark isolados + Header/Sidebar 100% fixos) --}}
    <link rel="stylesheet" href="{{ unn_asset('css/adminlte4-unn.css') }}">

    <style>
        .filepond--credits { display: none !important; }
        .panel-theme-shell { min-height: auto; background: transparent !important; }
        .panel-theme-shell .filepond--panel-root {
            border-radius: 1rem !important;
            border: 1px dashed var(--bs-border-color) !important;
            background: var(--bs-tertiary-bg) !important;
        }
        [data-bs-theme="dark"] .panel-theme-shell .filepond--drop-label { color: #cbd5e1; }
        [data-bs-theme="dark"] .panel-theme-shell .filepond--label-action { color: #60a5fa; }
        .panel-upload-progress {
            position: fixed;
            top: 4.25rem;
            right: 1.5rem;
            z-index: 1080;
            width: min(26rem, calc(100vw - 2rem));
            display: none;
        }
        .panel-upload-progress.is-visible { display: block; }
        .panel-upload-progress__card {
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            background: rgba(15, 23, 42, 0.95);
            color: #e2e8f0;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
            padding: 1rem 1.1rem;
        }
        .panel-upload-progress__bar {
            width: 100%;
            height: 0.55rem;
            border-radius: 9999px;
            background: rgba(148, 163, 184, 0.2);
            overflow: hidden;
        }
        .panel-upload-progress__bar > span {
            display: block;
            height: 100%;
            width: 0;
            border-radius: inherit;
            background: linear-gradient(90deg, #38bdf8, #1F5EDB);
            transition: width 0.18s ease;
        }
    </style>

    @stack('styles')
</head>

<body class="layout-fixed fixed-header sidebar-expand-lg bg-body-tertiary {{ $siteTheme === 'dark' ? 'dark-mode' : '' }}" data-bs-theme="{{ $siteTheme }}">
    @include('partials.modern-preloader')
    @include('partials.help-drawer')

    <div class="app-wrapper">
        @include('panel.partials.adminlte4-navbar')
        @include('panel.partials.adminlte4-sidebar')

        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <h3 class="mb-0 fw-bold fs-4">@yield('title', 'Painel Administrativo')</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('panel.admin.dashboard') }}">Admin</a></li>
                                @hasSection('panel_breadcrumb')
                                    <li class="breadcrumb-item">@yield('panel_breadcrumb')</li>
                                @endif
                                <li class="breadcrumb-item active" aria-current="page">@yield('title', 'Painel')</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid panel-theme-shell" id="pjax-container">
                    @yield('panel_content')
                </div>
            </div>
        </main>

        @include('admin.partials.footer')
    </div>

    @include('panel.partials.quick-upload-modal')

    {{-- Flash Messages --}}
    @if(session('success') || session('error') || session('warning') || session('info') || session('toastr_success') || session('toastr_error') || session('toastr_info'))
        <div id="flash-messages"
            data-success="{{ session('success') }}"
            data-error="{{ session('error') }}"
            data-warning="{{ session('warning') }}"
            data-info="{{ session('info') }}"
            data-toastr-success="{{ session('toastr_success') }}"
            data-toastr-error="{{ session('toastr_error') }}"
            data-toastr-info="{{ session('toastr_info') }}"
            class="d-none"></div>
    @endif

    {{-- Core JS: jQuery 3.7 + Popper 2 + Bootstrap 5.3 + OverlayScrollbars v2 + AdminLTE 4 --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/js/adminlte.min.js"></script>
    <script src="{{ unn_asset('js/adminlte4-unn.js') }}"></script>

    {{-- Máscaras & Plugins --}}
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js"></script>
    <script src="{{ unn_asset('js/unn-masks.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>

    <script src="{{ asset('vendor/filepond/plugins/filepond-plugin-image-preview.js') }}?v=3"></script>
    <script src="{{ asset('vendor/filepond/plugins/filepond-plugin-file-validate-size.js') }}?v=3"></script>
    <script src="{{ asset('vendor/filepond/plugins/filepond-plugin-file-validate-type.js') }}?v=3"></script>
    <script src="{{ asset('vendor/filepond/filepond.js') }}?v=3"></script>
    <script src="{{ asset('vendor/flatpickr/js/flatpickr.min.js') }}?v=3"></script>
    <script src="{{ asset('vendor/flatpickr/js/l10n/pt.js') }}?v=3"></script>
    <script src="https://unpkg.com/@yaireo/tagify"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-pt-BR.min.js"></script>

    <script>
        (function () {
            // Toastr global + helpers
            if (typeof toastr !== 'undefined') {
                toastr.options = { positionClass: 'toast-top-right', timeOut: 4000, progressBar: true };
            }
            window.showSuccess = function (msg) { if (typeof toastr !== 'undefined') toastr.success(msg || 'Sucesso'); };
            window.showError = function (msg) { if (typeof toastr !== 'undefined') toastr.error(msg || 'Erro na operação'); };

            // Flash messages
            document.addEventListener('DOMContentLoaded', function () {
                const flash = document.getElementById('flash-messages');
                if (flash && typeof toastr !== 'undefined') {
                    [
                        { type: 'success', msg: flash.dataset.success },
                        { type: 'error', msg: flash.dataset.error },
                        { type: 'warning', msg: flash.dataset.warning },
                        { type: 'info', msg: flash.dataset.info },
                        { type: 'success', msg: flash.dataset.toastrSuccess },
                        { type: 'error', msg: flash.dataset.toastrError },
                        { type: 'info', msg: flash.dataset.toastrInfo }
                    ].forEach(function (m) {
                        if (m.msg && m.msg.trim() !== '') toastr[m.type](m.msg);
                    });
                }

                // Toggle Dark/Light Mode sincronizado (Bootstrap 5 + Tailwind .dark + servidor)
                const themeBtn = document.getElementById('panelAdminThemeToggleBtn');
                const themeIcon = document.getElementById('panelAdminThemeIcon');
                if (themeBtn) {
                    themeBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        const current = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
                        const next = current === 'dark' ? 'light' : 'dark';
                        if (window.UNNAdminTheme && typeof window.UNNAdminTheme.apply === 'function') {
                            window.UNNAdminTheme.apply(next);
                        } else {
                            document.documentElement.setAttribute('data-bs-theme', next);
                            document.body.setAttribute('data-bs-theme', next);
                            document.documentElement.classList.toggle('dark', next === 'dark');
                            document.body.classList.toggle('dark-mode', next === 'dark');
                        }
                        if (themeIcon) {
                            themeIcon.className = next === 'dark' ? 'fas fa-sun text-warning' : 'fas fa-moon';
                        }
                        fetch(@json(\Illuminate\Support\Facades\Route::has('panel.theme.update') ? route('panel.theme.update') : route('admin.settings.update')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ theme: next, site_theme: next, current_group: 'appearance' })
                        }).catch(function () {});
                    });
                }
            });

            // FilePond auto-inicialização (compatível com todas as views panel/admin/*)
            window.initializePanelFileUploads = function (root = document) {
                if (typeof FilePond === 'undefined') return;
                try {
                    if (typeof FilePondPluginImagePreview !== 'undefined') FilePond.registerPlugin(FilePondPluginImagePreview);
                    if (typeof FilePondPluginFileValidateSize !== 'undefined') FilePond.registerPlugin(FilePondPluginFileValidateSize);
                    if (typeof FilePondPluginFileValidateType !== 'undefined') FilePond.registerPlugin(FilePondPluginFileValidateType);
                } catch (e) {}

                root.querySelectorAll('input.filepond[type="file"]').forEach(function (input) {
                    if (input._pond || input.closest('.filepond--root')) return;
                    const pond = FilePond.create(input, {
                        storeAsFile: true,
                        credits: false,
                        labelIdle: 'Arraste e solte o arquivo ou <span class="filepond--label-action">procure</span>'
                    });
                    input._pond = pond;
                });
            };

            // Flatpickr auto-inicialização
            window.initializePanelDateTimePickers = function (root = document) {
                if (!window.flatpickr) return;
                try {
                    if (flatpickr.l10ns && flatpickr.l10ns.pt) flatpickr.localize(flatpickr.l10ns.pt);
                } catch (e) {}

                root.querySelectorAll('[data-datetime-picker]').forEach(function (input) {
                    if (input._flatpickr) return;
                    flatpickr(input, {
                        enableTime: true,
                        time_24hr: true,
                        allowInput: true,
                        disableMobile: true,
                        altInput: true,
                        altFormat: 'd/m/Y H:i',
                        dateFormat: 'Y-m-d H:i'
                    });
                });
            };

            window.initPanelScripts = function () {
                window.initializePanelFileUploads(document);
                window.initializePanelDateTimePickers(document);
                if (typeof window.initUnnMasks === 'function') {
                    window.initUnnMasks(document);
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', window.initPanelScripts);
            } else {
                window.initPanelScripts();
            }
        })();
    </script>

    @include('partials.form-draft-autosave')
    @stack('scripts')
</body>

</html>
