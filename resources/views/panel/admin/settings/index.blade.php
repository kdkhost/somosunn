@extends('panel.layouts.app')

@section('title', 'Configurações')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.settings', ['group' => 'general']) }}" class="hover:underline">Configurações</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        {{-- Header + Navigation --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">
                    Configurações do Sistema
                </h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                    Gerencie as configurações gerais, integrações e aparência da plataforma.
                </p>
            </div>

            <div class="flex overflow-x-auto no-scrollbar border-b border-slate-100 dark:border-slate-800">
                @php
                    $tabs = [
                        'general' => ['label' => 'Geral', 'icon' => 'fa-cogs'],
                        'appearance' => ['label' => 'Aparência', 'icon' => 'fa-palette'],
                        'images' => ['label' => 'Imagens', 'icon' => 'fa-images'],
                        'player' => ['label' => 'Player', 'icon' => 'fa-play-circle'],
                        'ads' => ['label' => 'Anúncios', 'icon' => 'fa-ad'],
                        'pwa' => ['label' => 'PWA', 'icon' => 'fa-mobile-alt'],
                        'marketplace' => ['label' => 'Marketplace', 'icon' => 'fa-store'],
                        'gateway' => ['label' => 'Pagamentos', 'icon' => 'fa-credit-card'],
                        'smtp' => ['label' => 'SMTP', 'icon' => 'fa-envelope'],
                        'social' => ['label' => 'Social', 'icon' => 'fa-share-alt'],
                        'seo' => ['label' => 'SEO', 'icon' => 'fa-search'],
                        'storage' => ['label' => 'Armazenamento', 'icon' => 'fa-cloud'],
                        'system' => ['label' => 'Sistema', 'icon' => 'fa-server'],
                    ];
                @endphp

                @foreach($tabs as $key => $tab)
                    <a href="{{ route('panel.admin.settings', ['group' => $key]) }}"
                        class="flex items-center gap-2 px-6 py-4 text-sm transition whitespace-nowrap border-b-4
                               {{ $group === $key
                                    ? 'border-blue-600 text-blue-600 font-black bg-blue-50 dark:bg-blue-900/30'
                                    : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-200 font-bold hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <i class="fas {{ $tab['icon'] }}"></i>
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Settings Form (AJAX submit - no page reload) --}}
        <form id="settings-form" action="{{ route('panel.admin.settings.update') }}" method="POST" enctype="multipart/form-data" novalidate autocomplete="off">
            @csrf
            <input type="hidden" name="current_group" value="{{ $group }}">

            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
                @if($errors->any())
                    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 rounded-2xl p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                            <div>
                                <h4 class="text-red-800 dark:text-red-300 font-bold text-sm">Atenção!</h4>
                                <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 mt-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @include('panel.admin.settings.partials.' . $group, ['settings' => $settings, 'getUrl' => $getUrl])

                <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button type="submit" id="btn-save-settings"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02] flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span id="btn-save-label">Salvar Alterações</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    (function() {
        const form = document.getElementById('settings-form');
        const btn  = document.getElementById('btn-save-settings');
        const lbl  = document.getElementById('btn-save-label');
        if (!form) return;

        const showToast = (icon, title) => {
            if (typeof toastr !== 'undefined') {
                if (icon === 'success') toastr.success(title);
                else if (icon === 'error') toastr.error(title);
                else if (icon === 'warning') toastr.warning(title);
                else toastr.info(title);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon, title,
                    toast: true, position: 'top-end',
                    showConfirmButton: false, timer: 3500, timerProgressBar: true
                });
            }
        };

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            btn.disabled = true;
            const origHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Salvando...</span>';

            const formData = new FormData(form);
            const hasFiles = (window.formHasSelectedFiles && window.formHasSelectedFiles(form)) ||
                Array.from(form.querySelectorAll('input[type="file"]')).some(i => i.files && i.files.length > 0) ||
                !!form.querySelector('.filepond--item');

            let progressCard = null;
            let startedAt = Date.now();

            if (hasFiles && window.ensureUploadProgressCard && window.updateUploadProgressCard && window.setUploadCardVisible) {
                progressCard = window.ensureUploadProgressCard();
                window.updateUploadProgressCard(progressCard, {
                    percent: 0,
                    loaded: 0,
                    total: 0,
                    status: 'Preparando envio de arquivos e configurações...',
                    remaining: 'calculando tempo restante...'
                });
                window.setUploadCardVisible(true);
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
            xhr.withCredentials = true;

            if (hasFiles && xhr.upload) {
                xhr.upload.addEventListener('progress', function(ev) {
                    if (!ev.lengthComputable || !progressCard) return;
                    const percent = (ev.loaded / ev.total) * 100;
                    const elapsedSeconds = Math.max((Date.now() - startedAt) / 1000, 0.2);
                    const speed = ev.loaded / elapsedSeconds;
                    const remainingSeconds = speed > 0 ? (ev.total - ev.loaded) / speed : 0;

                    window.updateUploadProgressCard(progressCard, {
                        percent: percent,
                        loaded: ev.loaded,
                        total: ev.total,
                        status: 'Enviando certificado e configurações...',
                        remaining: window.formatRemainingTime ? window.formatRemainingTime(remainingSeconds) : Math.round(remainingSeconds) + 's restantes'
                    });
                });
            }

            xhr.addEventListener('load', function() {
                if (progressCard && window.setUploadCardVisible) {
                    window.updateUploadProgressCard(progressCard, {
                        percent: 100,
                        status: 'Finalizando resposta do servidor...',
                        remaining: 'quase pronto'
                    });
                    setTimeout(() => window.setUploadCardVisible(false), 500);
                }

                btn.disabled = false;
                btn.innerHTML = origHTML;

                let data;
                try {
                    data = JSON.parse(xhr.responseText);
                } catch(e) {
                    data = { message: xhr.responseText };
                }

                if (xhr.status >= 200 && xhr.status < 400) {
                    showToast('success', (data && data.message) ? data.message : 'Configurações salvas com sucesso.');
                    if (hasFiles) {
                        setTimeout(() => window.location.reload(), 1200);
                    }
                } else if (xhr.status === 422) {
                    showToast('error', (data && data.message) ? data.message : 'Erro de validação');
                } else {
                    showToast('error', (data && data.message) ? data.message : 'Erro ao salvar. Tente novamente.');
                }
            });

            xhr.addEventListener('error', function() {
                if (progressCard && window.setUploadCardVisible) {
                    window.setUploadCardVisible(false);
                }
                btn.disabled = false;
                btn.innerHTML = origHTML;
                showToast('error', 'Erro de conexão. Tente novamente.');
            });

            xhr.send(formData);
        });
    })();
    </script>
    @endpush
@endsection
