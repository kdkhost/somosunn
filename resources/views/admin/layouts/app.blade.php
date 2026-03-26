<!doctype html>
<html lang="pt-BR">

<head>
    @php
        use Illuminate\Support\Str;
        if (!isset($settings)) {
            try {
                $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                $settings = [];
            }
        }
        $siteTheme = $settings['site_theme'] ?? 'light';

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

        $preloaderImage = $resolvePublicAsset($settings['preloader_image'] ?? null) ?? asset('img/logo.svg');
        $preloaderEnabled = (bool) ($settings['preloader_enabled'] ?? 1);
        $faviconUrl = $resolvePublicAsset($settings['favicon_image'] ?? null) ?? asset('favicon.ico');
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - UNN')</title>
    <link rel="icon" href="{{ $faviconUrl }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jqvmap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/css/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if(!$isAdmin)
        <script>
            window.tailwind = window.tailwind || {};
            window.tailwind.config = { corePlugins: { preflight: false } };
        </script>
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        /* Layout helpers only — do not override AdminLTE colors here */
        :root {
            --main-header-height: 3.5rem;
            --unn-azul-1: #1F5EDB;
            --unn-azul-2: #177FD6;
            --unn-azul-3: #1D3FC4;
            --summernote-surface: #ffffff;
            --summernote-surface-soft: #f8fafc;
            --summernote-surface-muted: #eff6ff;
            --summernote-border: #dee2e6;
            --summernote-text: #212529;
            --summernote-muted: #6c757d;
            --summernote-toolbar: #f8f9fa;
            --summernote-toolbar-button: #ffffff;
            --summernote-toolbar-button-border: #ced4da;
            --summernote-toolbar-button-text: #212529;
            --summernote-dropdown-bg: #ffffff;
            --summernote-dropdown-hover: #e9ecef;
            --summernote-dropdown-text: #212529;
            --summernote-dropdown-separator: #dee2e6;
            --summernote-link: #0d6efd;
        }

        .dark-mode {
            --summernote-surface: #0f172a;
            --summernote-surface-soft: #111827;
            --summernote-surface-muted: #1f2937;
            --summernote-border: #334155;
            --summernote-text: #e5e7eb;
            --summernote-muted: #94a3b8;
            --summernote-toolbar: #111827;
            --summernote-toolbar-button: #1f2937;
            --summernote-toolbar-button-border: #475569;
            --summernote-toolbar-button-text: #f8fafc;
            --summernote-dropdown-bg: #111827;
            --summernote-dropdown-hover: #1f2937;
            --summernote-dropdown-text: #e5e7eb;
            --summernote-dropdown-separator: #334155;
            --summernote-link: #60a5fa;
        }

        .note-editor.note-frame {
            border-color: var(--summernote-border) !important;
            background-color: var(--summernote-surface) !important;
            overflow: visible !important;
            isolation: isolate;
        }

        .note-editor .note-editing-area {
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .note-editor .note-editing-area .note-editable,
        .note-editor .note-editing-area .note-codable {
            background-color: var(--summernote-surface) !important;
            color: var(--summernote-text) !important;
        }

        .note-editor .note-editing-area .note-editable {
            caret-color: var(--summernote-text);
        }

        .note-editor .note-editing-area .note-editable :where(p, div, li, ul, ol, blockquote, h1, h2, h3, h4, h5, h6, small, strong, em, u, s, code, pre) {
            color: inherit;
        }

        .note-editor .note-editing-area .note-editable a {
            color: var(--summernote-link);
        }

        .note-editor .note-placeholder {
            color: var(--summernote-muted) !important;
        }

        .note-editor .note-toolbar,
        .note-editor .note-statusbar {
            position: relative;
        }

        .note-editor .note-toolbar {
            z-index: 4;
        }

        .note-editor .note-toolbar {
            background-color: var(--summernote-toolbar) !important;
            border-bottom-color: var(--summernote-border) !important;
        }

        .note-editor .note-statusbar {
            z-index: 0;
            background-color: var(--summernote-toolbar) !important;
            border-top-color: var(--summernote-border) !important;
        }

        .note-editor .note-btn {
            background-color: var(--summernote-toolbar-button) !important;
            border-color: var(--summernote-toolbar-button-border) !important;
            color: var(--summernote-toolbar-button-text) !important;
        }

        .note-editor .note-btn:hover,
        .note-editor .note-btn:focus,
        .note-editor .note-btn.active {
            background-color: var(--summernote-surface-muted) !important;
            border-color: var(--summernote-toolbar-button-border) !important;
            color: var(--summernote-toolbar-button-text) !important;
        }

        .note-editor .note-toolbar .note-dropdown-menu {
            background-color: var(--summernote-dropdown-bg) !important;
            border-color: var(--summernote-border) !important;
            color: var(--summernote-dropdown-text) !important;
            z-index: 1200;
        }

        .note-editor .note-toolbar .note-color .note-dropdown-menu,
        .note-editor .note-toolbar .note-color-all .note-dropdown-menu {
            min-width: 337px;
            padding: 0.5rem;
        }

        .note-editor .note-toolbar .note-color .note-dropdown-menu .note-palette,
        .note-editor .note-toolbar .note-color-all .note-dropdown-menu .note-palette {
            display: inline-block;
            vertical-align: top;
            width: 160px;
        }

        .note-editor .note-toolbar .note-color-palette {
            line-height: 1;
        }

        .note-editor .note-toolbar .note-color-palette .note-color-row {
            display: flex;
            height: 20px;
        }

        .note-editor .note-toolbar .note-color-palette .note-color-btn {
            width: 20px !important;
            min-width: 20px !important;
            height: 20px !important;
            padding: 0 !important;
            border: 1px solid var(--summernote-dropdown-separator) !important;
            border-radius: 0.375rem !important;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.04);
        }

        .note-editor .note-toolbar .note-color-palette .note-color-btn:hover,
        .note-editor .note-toolbar .note-color-palette .note-color-btn:focus {
            transform: scale(1.08);
            transition: transform 0.15s ease;
            box-shadow: 0 0 0 1px var(--summernote-link);
        }

        .note-editor .note-current-color-button .note-recent-color {
            color: var(--summernote-toolbar-button-text);
        }

        .note-editor .note-dropdown-item {
            color: var(--summernote-dropdown-text) !important;
        }

        .note-editor .note-dropdown-item:hover,
        .note-editor .note-dropdown-item:focus {
            background-color: var(--summernote-dropdown-hover) !important;
        }

        .note-editor .note-palette-title {
            color: var(--summernote-dropdown-text) !important;
            border-bottom-color: var(--summernote-dropdown-separator) !important;
        }

        .note-editor .note-color-reset,
        .note-editor .note-color-select {
            color: var(--summernote-dropdown-text) !important;
        }

        .note-editor .note-color-reset:hover,
        .note-editor .note-color-select:hover {
            background-color: var(--summernote-dropdown-hover) !important;
        }

        .note-editor .note-holder-custom .note-color-btn {
            border-color: var(--summernote-dropdown-separator) !important;
        }

        body.dark-mode .note-editor .note-editing-area .note-editable :is(
            [style*="color:#000"],
            [style*="color: #000"],
            [style*="color:#000000"],
            [style*="color: #000000"],
            [style*="color:black"],
            [style*="color: black"],
            [style*="color:rgb(0,0,0)"],
            [style*="color: rgb(0, 0, 0)"],
            [style*="color:#212529"],
            [style*="color: #212529"],
            [style*="color:rgb(33,37,41)"],
            [style*="color: rgb(33, 37, 41)"],
            font[color="#000"],
            font[color="#000000"],
            font[color="black"],
            font[color="#212529"]
        ) {
            color: var(--summernote-text) !important;
        }

        body:not(.dark-mode) .note-editor .note-editing-area .note-editable :is(
            [style*="color:#fff"],
            [style*="color: #fff"],
            [style*="color:#ffffff"],
            [style*="color: #ffffff"],
            [style*="color:white"],
            [style*="color: white"],
            [style*="color:rgb(255,255,255)"],
            [style*="color: rgb(255, 255, 255)"],
            [style*="color:#f8fafc"],
            [style*="color: #f8fafc"],
            [style*="color:#e5e7eb"],
            [style*="color: #e5e7eb"],
            [style*="color:rgb(248,250,252)"],
            [style*="color: rgb(248, 250, 252)"],
            [style*="color:rgb(229,231,235)"],
            [style*="color: rgb(229, 231, 235)"],
            font[color="#fff"],
            font[color="#ffffff"],
            font[color="white"],
            font[color="#f8fafc"],
            font[color="#e5e7eb"]
        ) {
            color: var(--summernote-text) !important;
        }

        .note-editor .note-editing-area .note-editable table td,
        .note-editor .note-editing-area .note-editable table th,
        .note-editor .note-editing-area .note-editable blockquote {
            border-color: var(--summernote-border);
        }

        /* Tooltip customizada (Premium) */
        .ui-tooltip {
            position: relative;
        }

        .ui-tooltip::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 50%;
            bottom: calc(100% + 10px);
            transform: translateX(-50%);
            background: var(--unn-azul-1, #1F5EDB);
            color: #fff;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s ease, transform 0.15s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            z-index: 1060;
            text-transform: none;
            letter-spacing: normal;
        }

        .ui-tooltip::before {
            content: '';
            position: absolute;
            left: 50%;
            bottom: calc(100% + 4px);
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: var(--unn-azul-1, #1F5EDB) transparent transparent transparent;
            opacity: 0;
            transition: opacity 0.15s ease;
            z-index: 1060;
        }

        .ui-tooltip:hover::after,
        .ui-tooltip:focus-visible::after,
        .ui-tooltip:hover::before,
        .ui-tooltip:focus-visible::before {
            opacity: 1;
            transform: translateX(-50%) translateY(-2px);
        }

        .content-wrapper {
            transition: margin-left .3s ease-in-out;
        }

        .content-wrapper>.content {
            padding: 0 1rem 1rem 1rem;
        }

        .content-header {
            padding: 6px 12px;
            margin: 0;
            border-bottom: 0;
        }

        @media (max-width:575px) {
            .content-header .container-fluid>div {
                flex-direction: column;
                align-items: flex-start !important;
                gap: .25rem
            }
        }

        /* Keep any cosmetic colors to AdminLTE CSS; minimal helpers only */
        /* Global Table Responsiveness Override */
        /* Premium Upload Box & Drop Zone (AdminLTE Optimized) */
        .premium-upload-box {
            position: relative;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px dashed #d1d5db;
            border-radius: 1.5rem;
            background: #f9fafb;
            padding: 2rem;
            text-align: center;
        }

        .premium-upload-box:hover,
        .premium-upload-box.dragover {
            border-color: #3b82f6;
            background: #eff6ff;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1);
        }

        .drop-zone-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 150px;
        }

        .upload-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* SweetAlert2 Premium Customizations */
        .swal2-popup.rounded-[32px] {
            border-radius: 2rem !important;
            padding: 1.5rem !important;
        }

        .swal2-title {
            font-weight: 800 !important;
            letter-spacing: -0.025em !important;
        }

        .swal2-confirm.rounded-pill {
            border-radius: 9999px !important;
            padding: 12px 30px !important;
            font-weight: 700 !important;
        }

        @media (max-width: 768px) {
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .card-body.p-0 .table tbody>tr>td:first-of-type,
            .card-body.p-0 .table tbody>tr>th:first-of-type,
            .card-body.p-0 .table tfoot>tr>td:first-of-type,
            .card-body.p-0 .table tfoot>tr>th:first-of-type,
            .card-body.p-0 .table thead>tr>td:first-of-type,
            .card-body.p-0 .table thead>tr>th:first-of-type {
                padding-left: 0.75rem;
            }

            .card-body.p-0 .table tbody>tr>td:last-of-type,
            .card-body.p-0 .table tbody>tr>th:last-of-type,
            .card-body.p-0 .table tfoot>tr>td:last-of-type,
            .card-body.p-0 .table tfoot>tr>th:last-of-type,
            .card-body.p-0 .table thead>tr>td:last-of-type,
            .card-body.p-0 .table thead>tr>th:last-of-type {
                padding-right: 0.75rem;
            }
        }

        /* Failsafe para o preloader - garante que ele desapareça mesmo se o JS falhar */
        @keyframes preloader-failsafe {
            from {
                opacity: 1;
                pointer-events: auto;
            }

            to {
                opacity: 0;
                pointer-events: none;
                visibility: hidden;
            }
        }

        .preloader {
            animation: preloader-failsafe 0.5s forwards;
            animation-delay: 5s;
            /* Desaparece automaticamente após 5s */
        }

        /* Small neutral utilities to restore layout helpers used across admin views */
        .bg-white {
            background-color: #ffffff !important;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        .rounded-xl {
            border-radius: .75rem !important;
        }

        .shadow-sm {
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
        }

        .text-blue-900 {
            color: #1e3a8a !important;
        }

        .font-bold {
            font-weight: 700 !important;
        }

        .border-b {
            border-bottom: 1px solid rgba(0, 0, 0, .06) !important;
        }

        .border-gray-200 {
            border-color: #e5e7eb !important;
        }

        .transition-all {
            transition: all .2s ease-in-out !important;
        }

        .duration-300 {
            transition-duration: .3s !important;
        }

        .mx-2 {
            margin-left: .5rem !important;
            margin-right: .5rem !important;
        }

        .px-2 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .text-wrap {
            white-space: normal !important;
        }

        .bg-gradient-soft {
            background: linear-gradient(90deg, #f1f5f9 0%, #ffffff 100%) !important;
        }

        /* Constrain upload previews and modal images to avoid overflow */
        .upload-preview img,
        .upload-preview img[alt],
        .upload-preview .img-fluid {
            max-width: 100%;
            height: auto;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .upload-preview {
            overflow: hidden;
        }

        /* Upload widget (drag & drop) */
        .upload-box {
            border: 2px dashed rgba(0, 0, 0, .22);
            padding: 18px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            position: relative;
            transition: background-color .2s ease, border-color .2s ease, box-shadow .2s ease;
            background: #ffffff;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .upload-box .upload-preview {
            width: 100%;
        }

        .upload-box:hover,
        .upload-box:focus {
            border-color: #007bff;
            background: #f8f9fa;
            outline: none;
        }

        .upload-box.dragover {
            border-color: #007bff;
            background: rgba(0, 123, 255, .06);
            box-shadow: inset 0 0 0 2px rgba(0, 123, 255, .08);
        }

        .upload-icon {
            font-size: 28px;
            color: rgba(0, 0, 0, .45);
            margin-bottom: 8px;
        }

        .upload-meta {
            font-size: 0.9rem;
        }

        .upload-preview video,
        .upload-preview audio {
            max-width: 100%;
            width: 100%;
        }

        .border-dashed {
            border-style: dashed !important;
        }

        .dark-mode .upload-box {
            background: rgba(255, 255, 255, .02);
            border-color: rgba(255, 255, 255, .22);
        }

        .dark-mode .upload-box:hover,
        .dark-mode .upload-box:focus {
            background: rgba(255, 255, 255, .04);
            border-color: rgba(0, 123, 255, .85);
        }

        /* Estilos Premium para Upload Widgets */
        .premium-upload-box {
            transition: all 0.3s ease;
            position: relative;
        }

        .drop-zone-area {
            border: 2px dashed #dee2e6;
            background: #f8f9fa;
            border-radius: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
            overflow: hidden;
            position: relative;
        }

        .drop-zone-area:hover {
            border-color: #007bff;
            background: #f1f7ff;
        }

        .premium-upload-box.dragover .drop-zone-area {
            border-color: #28a745;
            background: #e9f7ef;
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .x-small {
            font-size: 10px;
        }

        .opacity-50 {
            opacity: 0.5;
        }

        .opacity-75 {
            opacity: 0.75;
        }

        .rounded-pill {
            border-radius: 50rem !important;
        }

        .border-2 {
            border-width: 2px !important;
        }
    </style>
    @stack('styles')
</head>

<body
    class="{{ $isAdmin ? 'hold-transition sidebar-mini layout-fixed layout-navbar-fixed' : 'bg-slate-50 min-h-screen' }} {{ $siteTheme === 'dark' ? 'dark-mode' : '' }}">
    {{-- Badge de Impersonation - Flutuante discreto --}}
    @if(session()->has('impersonator_id'))
        <div id="impersonation-badge"
            class="position-fixed bg-warning text-dark px-3 py-2 rounded shadow font-weight-bold d-flex align-items-center"
            style="bottom: 1rem; left: 1rem; z-index: 9999; font-size: 0.8rem; max-width: 250px;">
            <i class="fas fa-user-secret mr-2"></i>
            <span class="text-truncate mr-2">{{ auth()->user()->name }}</span>
            <a href="{{ route('admin.impersonate.stop') }}" class="btn btn-xs btn-danger">Sair</a>
            <button onclick="document.getElementById('impersonation-badge').style.display='none'"
                class="btn btn-xs btn-link text-dark ml-1 p-0" title="Minimizar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if($isAdmin)
        <div class="wrapper">
            @include('admin.partials.navbar')
            @include('admin.partials.sidebar')

            <div class="content-wrapper">
                @if(View::hasSection('page_title') || View::hasSection('breadcrumb'))
                    <div class="content-header">
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <h1 class="m-0 h4">@yield('page_title')</h1>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" data-pjax>Home</a></li>
                                @hasSection('breadcrumb_items')
                                    @yield('breadcrumb_items')
                                @else
                                    <li class="breadcrumb-item active">@yield('page_title')</li>
                                @endif
                            </ol>
                        </div>
                    </div>
                @endif
                <section class="content">
                    <div class="container-fluid pb-4" id="pjax-container">
                        @yield('content')
                    </div>
                </section>
            </div>

            @include('admin.partials.control-sidebar')
            @include('admin.partials.footer')
            @include('admin.partials.quick-upload-modal')
        </div>
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
                                        <i class="fas fa-arrow-left mr-2"></i> Voltar ao painel
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

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-knob-chif@1.2.13/dist/jquery.knob.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jquery.vmap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/maps/jquery.vmap.world.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-pt-BR.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-pjax@2.0.1/jquery.pjax.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/js/bootstrap-colorpicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script>
        (function () {
            const themeSafeColors = new Set([
                '',
                '#000',
                '#000000',
                'black',
                'rgb(0,0,0)',
                'rgb(0, 0, 0)',
                '#212529',
                'rgb(33,37,41)',
                'rgb(33, 37, 41)',
                '#fff',
                '#ffffff',
                'white',
                'rgb(255,255,255)',
                'rgb(255, 255, 255)',
                '#e5e7eb',
                '#f8fafc',
                'rgb(229,231,235)',
                'rgb(229, 231, 235)',
                'rgb(248,250,252)',
                'rgb(248, 250, 252)',
            ]);

            function isDarkTheme() {
                return document.body.classList.contains('dark-mode');
            }

            function currentColorButtonConfig() {
                return isDarkTheme()
                    ? { foreColor: '#E5E7EB', backColor: '#111827' }
                    : { foreColor: '#212529', backColor: '#FFFFFF' };
            }

            function normalizeColorValue(value) {
                return String(value || '')
                    .trim()
                    .toLowerCase()
                    .replace(/\s+/g, '');
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

                    if (!swatchColor) {
                        return;
                    }

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
                    if (rafId !== null) {
                        cancelAnimationFrame(rafId);
                    }

                    rafId = requestAnimationFrame(function () {
                        rafId = null;
                        syncSummernoteThemeDefaults();
                    });
                };

                const observer = new MutationObserver(function (mutations) {
                    const shouldRefresh = mutations.some(function (mutation) {
                        if (mutation.type === 'attributes') {
                            return true;
                        }

                        return Array.from(mutation.addedNodes || []).some(function (node) {
                            return node.nodeType === 1 && (
                                node.matches('.note-editor, .note-current-color-button') ||
                                node.querySelector('.note-editor, .note-current-color-button')
                            );
                        });
                    });

                    if (shouldRefresh) {
                        scheduleSync();
                    }
                });

                observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
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

        .admin-upload-progress.is-visible {
            display: block;
        }

        .admin-upload-progress__card {
            padding: 1rem 1.05rem;
            border-radius: 1rem;
            background: rgba(15, 23, 42, 0.95);
            color: #e2e8f0;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.32);
            backdrop-filter: blur(18px);
        }

        .admin-upload-progress__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .admin-upload-progress__eyebrow {
            margin: 0 0 .3rem;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .22em;
            text-transform: uppercase;
            color: #7dd3fc;
        }

        .admin-upload-progress__title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
        }

        .admin-upload-progress__metrics {
            text-align: right;
        }

        .admin-upload-progress__percent {
            font-size: .95rem;
            font-weight: 800;
            color: #fff;
        }

        .admin-upload-progress__size {
            font-size: .72rem;
            color: #cbd5e1;
        }

        .admin-upload-progress__bar {
            width: 100%;
            height: .62rem;
            margin-top: .95rem;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(148, 163, 184, 0.18);
        }

        .admin-upload-progress__fill {
            display: block;
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #38bdf8, #2563eb);
            transition: width .18s ease;
        }

        .admin-upload-progress__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .8rem;
            margin-top: .75rem;
            font-size: .72rem;
            color: #cbd5e1;
        }
    </style>
    <button type="button" id="adminBackToTop" class="admin-back-to-top" aria-label="Voltar ao topo"
        title="Voltar ao topo">
        <i class="fas fa-chevron-up"></i>
    </button>
    <script>
        (function () {
            const button = document.getElementById('adminBackToTop');
            if (!button) return;

            const onScroll = function () {
                if (window.scrollY > 420) {
                    button.classList.add('is-visible');
                } else {
                    button.classList.remove('is-visible');
                }
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
            // Notificações Globais SweetAlert2 (Laravel Flash Messages)
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#3b82f6',
                    timer: 4000,
                    timerProgressBar: true,
                    customClass: { popup: 'rounded-[32px]' }
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Ops!',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#ef4444',
                    customClass: { popup: 'rounded-[32px]' }
                });
            @endif

            @if(session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Informação',
                    text: "{{ session('info') }}",
                    confirmButtonColor: '#3b82f6',
                    customClass: { popup: 'rounded-[32px]' }
                });
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
                const fullReloadPrefixes = ['/admin/events', '/admin/courses', '/admin/marketplace'];
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
            $('.nav-sidebar a, .navbar a').each(function () {
                const $a = $(this);
                const h = $a.attr('href') || '';
                if (h.startsWith('http') || h === '#' || $a.attr('target')) return;

                // FullCalendar requires a full page load (scripts are stacked in layout).
                if (shouldDisablePjax(h)) {
                    $a.attr('data-pjax', 'false');
                    return;
                }

                // Respect explicit opt-out (for pages that require full reload for JS stacks)
                if ($a.is('[data-pjax]') || $a.is('[data-no-pjax]') || $a.hasClass('no-pjax')) return;

                $a.attr('data-pjax', 'true');
            });
            $(document).on('pjax:end', function () {
                $('.summernote').summernote({ height: 180, lang: 'pt-BR' });
                initUploadWidgets();
                initFormUploadProgress(document);
                initMasks();
                initColorPickers();
                initDateTimePickers();
                initCouponFormEnhancements();
                initTooltips();
                if (typeof window.initializeFormDraftAutosave === 'function') {
                    window.initializeFormDraftAutosave(document);
                }
            });
            $('.summernote').summernote({ height: 180, lang: 'pt-BR' });
            initUploadWidgets();
            initFormUploadProgress(document);
            initMasks();
            initColorPickers();
            initDateTimePickers();
            initCouponFormEnhancements();
            initTooltips();
            if (typeof window.initializeFormDraftAutosave === 'function') {
                window.initializeFormDraftAutosave(document);
            }
        });

        function initTooltips() {
            $('[title]').each(function () {
                var $el = $(this);
                var text = $el.attr('title');
                if (!text) return;
                $el.attr('data-tooltip', text);
                $el.addClass('ui-tooltip');
                $el.removeAttr('title');
            });
        }

        function ensureAdminUploadProgressCard() {
            let card = document.getElementById('admin-upload-progress');

            if (card) {
                return card;
            }

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

            if (state.status) {
                status.textContent = state.status;
            }

            if (state.remaining) {
                remaining.textContent = state.remaining;
            }
        }

        function formatUploadBytes(bytes) {
            if (!bytes || bytes <= 0) {
                return '0 B';
            }

            const units = ['B', 'KB', 'MB', 'GB'];
            const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            const value = bytes / Math.pow(1024, exponent);

            return value.toFixed(value >= 100 || exponent === 0 ? 0 : 1) + ' ' + units[exponent];
        }

        function formatUploadRemainingTime(seconds) {
            if (!Number.isFinite(seconds) || seconds <= 0) {
                return 'calculando tempo restante...';
            }

            const rounded = Math.round(seconds);

            if (rounded < 60) {
                return rounded + 's restantes';
            }

            const minutes = Math.floor(rounded / 60);
            const remainingSeconds = rounded % 60;

            if (minutes < 60) {
                return minutes + 'min ' + remainingSeconds + 's restantes';
            }

            const hours = Math.floor(minutes / 60);
            const remainingMinutes = minutes % 60;

            return hours + 'h ' + remainingMinutes + 'min restantes';
        }

        function normalizeUploadUrl(url) {
            try {
                return new URL(url, window.location.href).href.replace(/\/$/, '');
            } catch (error) {
                return url;
            }
        }

        function formHasFilesSelected(form) {
            return Array.from(form.querySelectorAll('input[type="file"]')).some(function (input) {
                return !!(input.files && input.files.length);
            });
        }

        function initFormUploadProgress(root) {
            const scope = root || document;

            scope.querySelectorAll('form').forEach(function (form) {
                if (form.dataset.uploadProgressBound === 'true' || form.dataset.uploadProgress === 'false') {
                    return;
                }

                if (!form.querySelector('input[type="file"]')) {
                    return;
                }

                form.dataset.uploadProgressBound = 'true';

                form.addEventListener('submit', function (event) {
                    if (form.dataset.uploadSubmitting === 'true' || !formHasFilesSelected(form)) {
                        return;
                    }

                    event.preventDefault();
                    form.dataset.uploadSubmitting = 'true';

                    const startedAt = Date.now();
                    updateAdminUploadProgressCard({
                        percent: 0,
                        loaded: 0,
                        total: 0,
                        status: 'Preparando envio...',
                        remaining: 'calculando tempo restante...'
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
                        if (!uploadEvent.lengthComputable) {
                            return;
                        }

                        const percent = (uploadEvent.loaded / uploadEvent.total) * 100;
                        const elapsedSeconds = Math.max((Date.now() - startedAt) / 1000, 0.2);
                        const speed = uploadEvent.loaded / elapsedSeconds;
                        const remainingSeconds = speed > 0 ? (uploadEvent.total - uploadEvent.loaded) / speed : 0;

                        updateAdminUploadProgressCard({
                            percent: percent,
                            loaded: uploadEvent.loaded,
                            total: uploadEvent.total,
                            status: 'Enviando arquivos do formulario...',
                            remaining: formatUploadRemainingTime(remainingSeconds)
                        });
                    });

                    xhr.addEventListener('load', function () {
                        form.dataset.uploadSubmitting = 'false';

                        const contentType = xhr.getResponseHeader('Content-Type') || '';
                        const currentUrl = normalizeUploadUrl(window.location.href);
                        const responseUrl = normalizeUploadUrl(xhr.responseURL || form.action || window.location.href);

                        if (xhr.status >= 200 && xhr.status < 400) {
                            updateAdminUploadProgressCard({
                                percent: 100,
                                status: 'Finalizando resposta do servidor...',
                                remaining: 'quase pronto'
                            });

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
                            errorMessage = 'Sua sessao expirou. Recarregue a pagina e tente novamente.';
                        } else if (xhr.status === 403) {
                            errorMessage = 'O servidor bloqueou o upload. Verifique permissoes ou regras de seguranca.';
                        } else if (xhr.status >= 500) {
                            errorMessage = 'O servidor encontrou um erro interno ao processar o upload. Verifique os logs da aplicacao.';
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
                            Swal.fire({
                                icon: 'error',
                                title: 'Upload recusado',
                                html: errorMessage,
                                confirmButtonText: 'Entendi'
                            });
                        } else {
                            alert('Erro no upload: ' + errorMessage.replace(/<br>/g, '\n').replace(/<[^>]+>/g, ''));
                        }
                    });

                    xhr.addEventListener('error', function () {
                        form.dataset.uploadSubmitting = 'false';
                        setAdminUploadProgressVisible(false);

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro de conexao',
                                text: 'O upload falhou devido a um problema de rede.',
                                confirmButtonText: 'Tentar novamente'
                            });
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
            if ($(this).hasClass('ajax-form')
                || e.isDefaultPrevented()
                || !window.UNNAjaxGlobal
                || !window.UNNAjaxGlobal.shouldHandleForm(this)) {
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
            return;

            const form = $(this);
            $.ajax({
                url: form.attr('action'),
                method: form.attr('method') || 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (resp) {
                    const msg = (resp && resp.message) ? resp.message : 'Salvo com sucesso';
                    showSuccess(msg);
                    if (resp && resp.redirect) { $.pjax({ url: resp.redirect, container: container }); }
                },
                error: function (xhr) {
                    let msg = 'Erro ao salvar';
                    if (xhr && xhr.status === 419) { msg = 'Sessão expirada. Recarregue a página e tente novamente.'; }
                    else if (xhr && xhr.status === 413) { msg = 'Arquivo muito grande para enviar.'; }
                    else if (xhr && xhr.responseJSON) {
                        if (xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                        const errors = xhr.responseJSON.errors || null;
                        if (errors && typeof errors === 'object') {
                            const firstKey = Object.keys(errors)[0];
                            const firstVal = firstKey ? errors[firstKey] : null;
                            if (Array.isArray(firstVal) && firstVal[0]) { msg = firstVal[0]; }
                        }
                    }
                    showError(msg);
                }
            });
        });

        $(document).on('click', '.btn-delete, [data-confirm-delete]', function (e) {
            e.preventDefault();

            const $btn = $(this);
            const url = $btn.data('action') || $btn.attr('href');
            const redirect = $btn.data('redirect') || null;
            const $form = $btn.closest('form');

            showConfirm('Confirme para continuar.', function () {
                // Prefer form submission quando explicitamente solicitado
                if ($btn.is('[data-confirm-delete]') || (!url && $form.length)) {
                    if ($form.length) {
                        if ($form.hasClass('ajax-form')) {
                            $form.trigger('submit');
                        } else {
                            $form.get(0).submit();
                        }
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

                        if (redirect) {
                            window.location.href = redirect;
                            return;
                        }

                        if (resp && typeof resp === 'object' && resp.redirect) {
                            $.pjax({ url: resp.redirect, container: container });
                            return;
                        }

                        $.pjax.reload(container);
                    })
                    .fail(function (xhr) {
                        let msg = 'Erro ao excluir';
                        if (xhr && xhr.status === 419) { msg = 'Sessão expirada. Recarregue a página e tente novamente.'; }
                        else if (xhr && xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                        showError(msg);
                    });
            });
        });

        window.confirmAction = function (event, title, text, onConfirm) {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            return Swal.fire({
                title: title || 'Confirmar acao',
                text: text || 'Confirme para continuar.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return false;
                }

                if (typeof onConfirm === 'function') {
                    onConfirm();
                    return true;
                }

                if (event) {
                    const target = event.currentTarget || event.target;
                    const form = target && target.closest ? target.closest('form') : null;

                    if (form) {
                        form.submit();
                        return true;
                    }

                    const link = target && target.getAttribute ? target.getAttribute('href') : null;
                    if (link && link !== '#') {
                        window.location.href = link;
                        return true;
                    }
                }

                return true;
            });
        };

        $('#themeToggleBtn').on('click', function () {
            const input = $('#site_theme_input');
            input.val(input.val() === 'dark' ? 'light' : 'dark');
            $('#themeToggleForm').submit();
        });

        const logo = $('.brand-logo-img');
        const favicon = $('.brand-favicon-img');
        $(document).on('collapsed.lte.pushmenu', function () { logo.addClass('d-none'); favicon.removeClass('d-none'); });
        $(document).on('expanded.lte.pushmenu', function () { favicon.addClass('d-none'); logo.removeClass('d-none'); });

        // Persistência de aba ativa (nav-tabs)
        const tabKey = 'admin-active-tab-' + (location.pathname || 'root');

        // Use delegation to support PJAX/dynamic content
        $(document).on('shown.bs.tab', 'a[data-toggle="pill"], a[data-toggle="tab"]', function (e) {
            localStorage.setItem(tabKey, $(e.target).attr('href'));
        });

        function restoreActiveTab() {
            const savedTab = localStorage.getItem(tabKey);
            if (savedTab) {
                const $tab = $('a[href="' + savedTab + '"]');
                if ($tab.length) {
                    // Use bootstrap tab show
                    $tab.tab('show');
                }
            }
        }

        // Restore on load
        restoreActiveTab();

        // Restore on PJAX end
        $(document).on('pjax:end', function () {
            restoreActiveTab();
        });

        function initColorPickers() {
            $('.colorpicker-element').colorpicker();
        }

        function initMasks() {
            function lookupCep($input) {
                const cep = $input.val().replace(/\D/g, '');
                if (cep.length !== 8) return;
                if ($input.data('lastCep') === cep) return;
                $input.data('lastCep', cep);
                const targetNumber = $input.data('target-number');
                const targetComplement = $input.data('target-complement');
                const targetDistrict = $input.data('target-district');
                Swal.fire({ icon: 'info', title: 'Buscando CEP...', showConfirmButton: false, timer: 1500 });
                fetch('https://viacep.com.br/ws/' + cep + '/json/')
                    .then(r => r.json())
                    .then(data => {
                        if (data.erro) {
                            Swal.fire({ icon: 'error', title: 'Erro', text: 'CEP não encontrado', timer: 2000, showConfirmButton: false });
                            return;
                        }
                        $('[name="company_address"]').val(data.logradouro || '');
                        if (targetDistrict) { $(targetDistrict).val(data.bairro || ''); } else { $('[name="company_district"]').val(data.bairro || ''); }
                        $('[name="company_city"]').val(data.localidade || '');
                        $('[name="company_state"]').val(data.uf || '');

                        Swal.fire({ icon: 'success', title: 'Sucesso', text: 'Endereço preenchido pelo CEP', timer: 2000, showConfirmButton: false });

                        if (targetNumber) { $(targetNumber).focus(); }
                        else if (targetComplement) { $(targetComplement).focus(); }
                    })
                    .catch(() => {
                        Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha ao buscar CEP', timer: 2000, showConfirmButton: false });
                    });
            }

            // CEP com viacep + feedback
            $('.mask-cep').inputmask('99999-999', {
                oncomplete: function () {
                    lookupCep($(this));
                }
            });
            $('.mask-cep').off('input.cep').on('input.cep', function () {
                const $input = $(this);
                const cep = $input.val().replace(/\D/g, '');
                if (cep.length === 8) {
                    clearTimeout($input.data('cepTimer'));
                    $input.data('cepTimer', setTimeout(() => lookupCep($input), 250));
                }
            });
            $('.mask-cep').off('blur.cep').on('blur.cep', function () {
                lookupCep($(this));
            });
            $('.mask-cpf').inputmask('999.999.999-99');
            $('.mask-cnpj').inputmask('99.999.999/9999-99');
            $('.mask-date').inputmask('99/99/9999');
            $('.mask-datetime').inputmask('99/99/9999 99:99');
            $('.mask-time').inputmask('99:99');
            $('.mask-phone').inputmask('(99) 9999[9]-9999');
            $('.mask-money').inputmask('currency', {
                prefix: 'R$ ',
                radixPoint: ',',
                groupSeparator: '.',
                autoGroup: true,
                digits: 2,
                rightAlign: false,
                substituteRadixPoint: true,
                onBeforeMask: function (value) {
                    if (value === null || value === undefined) return value;
                    value = String(value);
                    // Eloquent decimal casts usually return "97.00" (dot decimal). Inputmask here expects comma.
                    if (value.includes(',') || !value.includes('.')) return value;
                    if (/^\\d+\\.\\d{1,2}$/.test(value)) return value.replace('.', ',');
                    return value;
                }
            });
            $('.mask-cpf-cnpj').inputmask({ mask: ['999.999.999-99', '99.999.999/9999-99'], keepStatic: true, placeholder: '_' });
        }

        function initDateTimePickers() {
            if (typeof flatpickr === 'undefined') return;

            try {
                if (flatpickr.l10ns && flatpickr.l10ns.pt) {
                    flatpickr.localize(flatpickr.l10ns.pt);
                }
            } catch (e) { /* ignore */ }

            $('[data-datetime-picker]').each(function () {
                if (this._flatpickr) return;
                flatpickr(this, {
                    enableTime: true,
                    time_24hr: true,
                    allowInput: true,
                    dateFormat: 'Y-m-d H:i'
                });
            });
        }

        function initCouponFormEnhancements() {
            const form = $('form.ajax-form').filter(function () {
                return String($(this).attr('action') || '').includes('/admin/coupons');
            }).first();

            if (!form.length) return;

            // Gerar código (fallback global para PJAX)
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

            if (!scopeSelect.length || !itemSelect.length) return;

            if (form.data('couponUiInit')) return;
            form.data('couponUiInit', true);

            function filterItemOptions(scope) {
                itemSelect.find('option').each(function () {
                    const optScope = $(this).data('scope');
                    if (!optScope) {
                        $(this).prop('hidden', false);
                        return;
                    }
                    $(this).prop('hidden', optScope !== scope);
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
                if (selected.length && selected.prop('hidden')) {
                    itemSelect.val('');
                }

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
                const maxSizeCandidates = [configuredMaxSize, serverMaxSize].filter(function (value) {
                    return Number.isFinite(value) && value > 0;
                });
                const maxSize = maxSizeCandidates.length ? Math.min.apply(Math, maxSizeCandidates) : (5 * 1024 * 1024);
                const crop = box.data('crop') === 1 || box.data('crop') === '1';
                const existingUrl = box.data('existing-url');
                const removeInputSelector = box.data('remove-input');
                const accept = (input.attr('accept') || 'image/*').replace(/\./g, '');
                const acceptLower = String(input.attr('accept') || '').toLowerCase();
                const sizeMb = (maxSize / 1024 / 1024).toFixed(2) + ' MB';

                // Custom preview constraints
                const previewMaxHeight = box.data('preview-max-height');
                const previewMaxWidth = box.data('preview-max-width');
                const previewStyle = (previewMaxHeight ? 'max-height:' + previewMaxHeight + 'px;' : '') +
                    (previewMaxWidth ? 'max-width:' + previewMaxWidth + 'px;' : '');
                const previewImageStyle = String(box.data('preview-image-style') || previewStyle || '').trim();
                const previewImageClass = String(box.data('preview-image-class') || 'img-fluid').trim();

                if (help.length) {
                    help.text('Aceita: ' + accept + ' • Até ' + sizeMb + (crop ? ' • Possível recorte' : ''));
                }

                function kindFromAccept() {
                    if (acceptLower.includes('video/')) return 'video';
                    if (acceptLower.includes('audio/')) return 'audio';
                    if (acceptLower.includes('image/')) return 'image';
                    if (acceptLower.trim() === '') return 'image';
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

                    if (kind === 'video') {
                        return '<video src="' + url + '" controls style="width:100%; max-height: 240px; border-radius: 10px;"></video>' +
                            '<div class="text-muted small mt-2">' + safeLabel + '</div>';
                    }

                    if (kind === 'audio') {
                        return '<audio src="' + url + '" controls style="width:100%;"></audio>' +
                            '<div class="text-muted small mt-2">' + safeLabel + '</div>';
                    }

                    if (kind === 'file') {
                        return '<i class="upload-icon fas fa-file-alt"></i>' +
                            '<div class="text-muted small">Arquivo selecionado</div>' +
                            '<div class="font-weight-bold small mt-1" style="word-break: break-word;">' + safeLabel + '</div>' +
                            (url ? '<div class="mt-2"><a href="' + url + '" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary">Abrir</a></div>' : '');
                    }

                    const imageClassAttr = previewImageClass !== '' ? ' class="' + previewImageClass + '"' : '';
                    const imageStyleAttr = previewImageStyle !== '' ? ' style="' + previewImageStyle + '"' : '';

                    return '<img src="' + url + '" alt="preview"' + imageClassAttr + imageStyleAttr + '>';
                }

                function renderEmpty() {
                    preview.html('<i class="upload-icon fas fa-cloud-upload-alt"></i><div class="text-muted small">Clique ou arraste para enviar</div>');
                    meta.text('');
                    removeBtn.addClass('d-none');
                }

                function renderExisting(url) {
                    const kind = kindFromUrl(url);
                    preview.html(previewHtml(kind, url, 'Arquivo atual'));
                    meta.text('Arquivo atual');
                    removeBtn.removeClass('d-none');
                }

                renderEmpty();
                if (existingUrl) {
                    renderExisting(existingUrl);
                }

                function setPreview(blobOrFile, name, url) {
                    const sizeMB = (blobOrFile.size / 1024 / 1024).toFixed(2);
                    const type = String(blobOrFile.type || '').toLowerCase();
                    const kind = type.startsWith('image/') ? 'image' : (type.startsWith('video/') ? 'video' : (type.startsWith('audio/') ? 'audio' : defaultKind));
                    preview.html(previewHtml(kind, url, name || 'arquivo'));
                    meta.text((name || 'arquivo') + ' • ' + sizeMB + ' MB • ' + (blobOrFile.type || ''));
                    removeBtn.removeClass('d-none');
                }

                function bindFileToInput(file) {
                    if (!input.length || !input[0]) {
                        return false;
                    }

                    if (typeof DataTransfer === 'undefined') {
                        return false;
                    }

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
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Arquivo muito grande',
                                text: 'Este arquivo excede o limite permitido.',
                                confirmButtonColor: '#ef4444',
                                confirmButtonText: 'Entendi'
                            });
                        } else {
                            alert('Arquivo excede o limite');
                        }
                        return;
                    }
                    const reader = new FileReader();
                    bar.css('width', '0%');
                    progress.removeClass('d-none');
                    const start = Date.now();

                    reader.onprogress = function (e) {
                        if (e.lengthComputable) {
                            const pct = (e.loaded / e.total) * 100;
                            bar.css('width', pct + '%');
                        }
                    };
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
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro no sistema',
                                text: 'Campo de upload não encontrado.',
                                confirmButtonColor: '#ef4444',
                                confirmButtonText: 'Entendi'
                            });
                        } else {
                            alert('Campo de upload não encontrado.');
                        }
                        return;
                    }
                    box.data('opening', true);
                    const reset = () => {
                        box.data('opening', false);
                        $(window).off('focus.upload', reset);
                    };
                    $(window).one('focus.upload', reset);
                    input[0].click();
                }

                function onInputChange() {
                    const file = this.files && this.files[0] ? this.files[0] : null;
                    if (removeInputSelector) { $(removeInputSelector).val('0'); }
                    handleFile(file);
                }

                box.off('.upload');
                input.off('.upload');
                removeBtn.off('.upload');
                box.find('.upload-btn').off('.upload');

                box.on('click.upload', function (e) {
                    if ($(e.target).closest('.upload-btn, .upload-remove, input').length) return;
                    openFileDialog();
                });
                box.on('keydown.upload', function (e) {
                    if (!['Enter', ' '].includes(e.key)) return;
                    e.preventDefault();
                    openFileDialog();
                });
                box.on('dragover.upload', function (e) { e.preventDefault(); box.addClass('dragover'); });
                box.on('dragleave.upload drop.upload', function (e) { e.preventDefault(); box.removeClass('dragover'); });
                box.on('drop.upload', function (e) {
                    e.preventDefault();
                    const f = e.originalEvent.dataTransfer.files[0];
                    if (!f) return;
                    if (removeInputSelector) { $(removeInputSelector).val('0'); }
                    bindFileToInput(f);
                    handleFile(f);
                });

                box.find('.upload-btn').on('click.upload', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openFileDialog();
                });
                input.on('click.upload', function (e) { e.stopPropagation(); });
                input.on('change.upload', onInputChange);

                removeBtn.on('click.upload', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    input.val('');
                    renderEmpty();
                    if (removeInputSelector) { $(removeInputSelector).val('1'); }
                });
            });
        }
        function openCropper(imageUrl, options, callback) {
            if (typeof options === 'function') {
                callback = options;
                options = {};
            }
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
                    '<button type="button" class="close" data-dismiss="modal">&times;</button></div>' +
                    '<div class="modal-body"><div style="max-height:500px;">' +
                    '<img id="' + modalId + '-img" style="max-width:100%;"></div></div>' +
                    '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
                    '<button type="button" class="btn btn-primary" id="' + modalId + '-apply">Aplicar</button>' +
                    '</div></div></div></div>'
                );
                modal = $('#' + modalId);
            }
            const img = $('#' + modalId + '-img');
            let cropper;
            modal.on('shown.bs.modal', function () {
                img.attr('src', imageUrl);
                cropper = new Cropper(img[0], { aspectRatio: NaN, viewMode: 1 });
            }).on('hidden.bs.modal', function () {
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
                        const mb = (maxSize / 1024 / 1024).toFixed(2);
                        Swal.fire({ icon: 'error', title: 'Erro', text: 'A imagem recortada excede o limite de ' + mb + ' MB.' });
                        return;
                    }
                    const url = URL.createObjectURL(blob);
                    callback(blob, url);
                    modal.modal('hide');
                }, outputType, quality);
            });
            modal.modal('show');
        }

    </script>
</body>

</html>
