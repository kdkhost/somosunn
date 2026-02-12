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

        $isSuperAdmin = false;
        try {
            if (auth()->check()) {
                $u = auth()->user();
                $role = (string) ($u->role ?? '');
                $level = (string) ($u->level ?? '');
                $isSuperAdmin = ($role === 'superadmin') || in_array($level, ['superadmin', 'sucesso'], true);
            }
        } catch (\Throwable $e) {
            $isSuperAdmin = false;
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if(!$isSuperAdmin)
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
    </style>
    <style>
        /* Small neutral utilities to restore layout helpers used across admin views
           These are intentionally minimal and do NOT override AdminLTE theme colors. */
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

        .card .nav-tabs {
            margin-bottom: .5rem;
        }
    </style>
    @stack('styles')
</head>

<body
    class="{{ $isSuperAdmin ? 'hold-transition sidebar-mini layout-fixed layout-navbar-fixed' : 'bg-slate-50 min-h-screen' }} {{ $siteTheme === 'dark' ? 'dark-mode' : '' }}">
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

    @if($isSuperAdmin)
        <div class="wrapper">
            @if($preloaderEnabled && (!auth()->check() || !auth()->user()->isAdmin()))
                <div class="preloader flex-column justify-content-center align-items-center">
                    <img class="animation__shake" src="{{ $preloaderImage }}" alt="UNN" height="80" width="80">
                </div>
            @endif

            @include('admin.partials.navbar')
            @include('admin.partials.sidebar')

            <div class="content-wrapper">
                @if(View::hasSection('page_title') || View::hasSection('breadcrumb'))
                    <div class="content-header">
                        <div class="container-fluid">
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
    <script src="https://cdn.jsdelivr.net/npm/jquery-pjax@2.0.1/jquery.pjax.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/js/bootstrap-colorpicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    @include('admin.partials.notifications')
    @include('admin.partials.chat-widget')
    @stack('scripts')
    <script>
        $(function () {
            const container = '#pjax-container';
            $(document).pjax('a[data-pjax="true"]', container, { timeout: 8000 });

            function shouldDisablePjax(href) {
                if (!href) return false;
                try {
                    const url = new URL(href, window.location.origin);
                    return url.pathname.endsWith('/admin/events');
                } catch (e) {
                    return href.indexOf('/admin/events') !== -1;
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
                $('.summernote').summernote({ height: 180 });
                $('.summernote-lg').summernote({ height: 420 });
                initUploadWidgets();
                initMasks();
                initColorPickers();
                initDateTimePickers();
                initCouponFormEnhancements();
            });
            $('.summernote').summernote({ height: 180 });
            $('.summernote-lg').summernote({ height: 420 });
            initUploadWidgets();
            initMasks();
            initColorPickers();
            initDateTimePickers();
            initCouponFormEnhancements();

            toastr.options = { positionClass: 'toast-top-right', timeOut: 3500, progressBar: true };

            $(document).on('submit', '.ajax-form', function (e) {
                e.preventDefault();
                const form = $(this);
                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method') || 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (resp) {
                        const msg = (resp && resp.message) ? resp.message : 'Salvo com sucesso';
                        toastr.success(msg);
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
                        toastr.error(msg);
                    }
                });
            });

            $(document).on('click', '.btn-delete, [data-confirm-delete]', function (e) {
                e.preventDefault();

                const $btn = $(this);
                const url = $btn.data('action') || $btn.attr('href');
                const redirect = $btn.data('redirect') || null;
                const $form = $btn.closest('form');

                Swal.fire({
                    title: 'Confirmar ação',
                    text: 'Confirme para continuar.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    // Prefer form submission when explicitly requested (ex.: reembolso)
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
                        toastr.error('Ação inválida: URL não encontrada.');
                        return;
                    }

                    $.post(url, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
                        .done(function (resp) {
                            toastr.success('Excluído');

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
                            toastr.error(msg);
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
                    toastr.info('Buscando CEP...');
                    fetch('https://viacep.com.br/ws/' + cep + '/json/')
                        .then(r => r.json())
                        .then(data => {
                            if (data.erro) { toastr.error('CEP não encontrado'); return; }
                            $('[name="company_address"]').val(data.logradouro || '');
                            if (targetDistrict) { $(targetDistrict).val(data.bairro || ''); } else { $('[name="company_district"]').val(data.bairro || ''); }
                            $('[name="company_city"]').val(data.localidade || '');
                            $('[name="company_state"]').val(data.uf || '');
                            toastr.success('Endereço preenchido pelo CEP');
                            if (targetNumber) { $(targetNumber).focus(); }
                            else if (targetComplement) { $(targetComplement).focus(); }
                        })
                        .catch(() => { toastr.error('Falha ao buscar CEP'); });
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
                $('.mask-phone').inputmask({ 'mask': ['(99) 9999-9999', '(99) 9 9999-9999'], keepStatic: true });
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
                    const maxSize = parseInt(box.data('max-size') || (5 * 1024 * 1024));
                    const crop = box.data('crop') === 1 || box.data('crop') === '1';
                    const existingUrl = box.data('existing-url');
                    const removeInputSelector = box.data('remove-input');
                    const accept = (input.attr('accept') || 'image/*').replace(/\./g, '');
                    const acceptLower = String(input.attr('accept') || '').toLowerCase();
                    const sizeMb = (maxSize / 1024 / 1024).toFixed(2) + ' MB';

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

                        return '<img src="' + url + '" alt="preview" class="img-fluid">';
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
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        input.off('change.upload');
                        input[0].files = dataTransfer.files;
                        input.on('change.upload', onInputChange);
                    }

                    function handleFile(file) {
                        if (!file) return;
                        if (file.size > maxSize) { toastr.error('Arquivo excede o limite'); return; }
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
                        box.data('opening', true);
                        const reset = () => {
                            box.data('opening', false);
                            $(window).off('focus.upload', reset);
                        };
                        $(window).one('focus.upload', reset);
                        input.trigger('click');
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
                    box.on('drop.upload', function (e) { e.preventDefault(); const f = e.originalEvent.dataTransfer.files[0]; handleFile(f); });

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
                            toastr.error('Falha ao gerar a imagem recortada.');
                            return;
                        }
                        if (maxSize && blob.size > maxSize) {
                            const mb = (maxSize / 1024 / 1024).toFixed(2);
                            toastr.error('A imagem recortada excede o limite de ' + mb + ' MB.');
                            return;
                        }
                        const url = URL.createObjectURL(blob);
                        callback(blob, url);
                        modal.modal('hide');
                    }, outputType, quality);
                });
                modal.modal('show');
            }

            $('[title]').each(function () {
                var $el = $(this);
                var text = $el.attr('title');
                if (!text) return;
                $el.attr('data-tooltip', text);
                $el.addClass('ui-tooltip');
                $el.removeAttr('title');
            });
        });
    </script>
</body>

</html>
