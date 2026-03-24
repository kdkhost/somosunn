@extends('panel.layouts.app')

@section('title', ($template->id ? 'Editar' : 'Novo') . ' Modelo de E-mail')

@push('styles')
    <!-- Summernote Lite CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
@endpush

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.mailtemplates.index') }}" class="hover:underline transition-all">Templates</a>
    <span class="mx-2 text-slate-300 dark:text-slate-700 transition-colors">/</span>
    <span class="text-slate-500 dark:text-slate-400 transition-colors">{{ $template->id ? 'Editar' : 'Novo' }}</span>
@endsection

@section('panel_content')
    <form
        action="{{ $template->id ? route('panel.admin.mailtemplates.update', $template) : route('panel.admin.mailtemplates.store') }}"
        method="POST">
        @csrf
        @if($template->id)
            @method('PUT')
        @endif

        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white transition-colors">
                    {{ $template->id ? 'Editar' : 'Novo' }} Modelo de E-mail
                </h2>
                <div class="flex gap-3">
                    <a href="{{ route('panel.admin.mailtemplates.index') }}"
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-bold py-2 px-6 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded-2xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02]">
                        <i class="fas fa-save mr-2"></i> Salvar
                    </button>
                </div>
            </div>

            @include('panel.admin.mailtemplates.partials.form-content', ['template' => $template])
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function() {
            // High Fidelity Preview Logic
            function renderPreview() {
                const $editor = $('#bodyEditor');
                if (!$editor.length) return;

                try {
                    @php
                        $logo = \App\Models\Setting::get('logo_admin');
                        if(!$logo) $logo = \App\Models\Setting::get('logo_front');
                        if(!$logo) $logo = \App\Models\Setting::get('logo_image');
                        $logoUrl = $logo ? asset($logo) : asset('img/logo.svg');
                        
                        $bgType = \App\Models\Setting::get('email_header_bg_type') ?? 'gradient';
                        $color1 = \App\Models\Setting::get('email_header_color_1') ?? \App\Models\Setting::get('site_color_primary') ?? '#000000';
                        $color2 = \App\Models\Setting::get('email_header_color_2') ?? \App\Models\Setting::get('site_color_secondary') ?? '#333333';
                        $color3 = \App\Models\Setting::get('email_header_color_3');

                        $headerStyle = "";
                        if ($bgType === 'solid') {
                            $headerStyle = "background-color: {$color1};";
                        } else {
                            if ($color3) {
                                $headerStyle = "background: linear-gradient(135deg, {$color1} 0%, {$color2} 50%, {$color3} 100%);";
                            } else {
                                $headerStyle = "background: linear-gradient(135deg, {$color1} 0%, {$color2} 100%);";
                            }
                        }

                        $previewData = [
                            'logo' => $logoUrl,
                            'siteName' => config('app.name'),
                            'siteUrl' => url('/'),
                            'year' => date('Y'),
                            'headerStyle' => $headerStyle,
                            'primaryColor' => $color1
                        ];
                    @endphp

                    const config = {!! json_encode($previewData) !!};
                    const bodyContent = $editor.summernote('code') || '<p style="color: #64728b; text-align: center; padding: 40px; font-weight: 500;">Comece a escrever para ver a magía acontecer em tempo real... ✨</p>';
                    
                    const html = `
                        <div style="background-color: #ffffff; width: 100%; font-family: 'Inter', Helvetica, Arial, sans-serif; box-sizing: border-box; display: flex; flex-direction: column; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 45px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; margin: 0 auto; max-width: 600px; transition: all 0.3s ease;">
                            <div style="${config.headerStyle} padding: 45px 30px; text-align: center; flex-shrink: 0; display: block; border-bottom: 5px solid rgba(0,0,0,0.1);">
                                <img src="${config.logo}" alt="${config.siteName}" style="max-height: 55px; max-width: 85%; width: auto; height: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
                            </div>
                            <div style="padding: 40px 35px; color: #1e293b; line-height: 1.8; word-wrap: break-word; font-size: 16px; min-height: 250px; background-color: #ffffff; display: block; text-align: left;">
                                ${bodyContent}
                            </div>
                            <div style="background-color: #f8fafc; padding: 35px 20px; text-align: center; color: #64748b; font-size: 13px; border-top: 1px solid #f1f5f9; flex-shrink: 0; display: block;">
                                <p style="margin: 5px 0; font-weight: 700; color: #334155;">&copy; ${config.year} ${config.siteName}.</p>
                                <p style="margin: 10px 0;">
                                    <a href="${config.siteUrl}" style="color: ${config.primaryColor}; text-decoration: none; font-weight: 800; display: inline-block; border-bottom: 2px solid ${config.primaryColor}; padding-bottom: 1px;">Visite nosso site</a>
                                </p>
                            </div>
                        </div>
                    `;

                    $('#tpl_preview').html(html).css('opacity', 1);
                } catch (e) {
                    console.error('Erro no renderPreview:', e);
                }
            }

            // Initialization logic (Supports SPA and Modals)
            function initMailTemplateEditor() {
                const $editor = $('#bodyEditor');
                if (!$editor.length || $editor.data('summernote-ready')) return;

                $editor.summernote({
                    placeholder: 'Escreva o conteúdo do e-mail aqui...',
                    tabsize: 2,
                    height: 400,
                    lang: 'pt-BR',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'hr']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    callbacks: {
                        onInit: function() {
                            $editor.data('summernote-ready', true);
                            renderPreview();
                        },
                        onChange: renderPreview,
                        onKeyup: renderPreview,
                        onFocus: renderPreview,
                        onPaste: renderPreview
                    }
                });

                // Extra listeners for ultra-fast real-time response
                $(document).off('input keyup paste', '.note-editable');
                $(document).on('input keyup paste', '.note-editable', renderPreview);
            }

            // Expose for external calls
            window.renderMailPreview = renderPreview;

            // Variables Insertion
            $(document).on('click', '.insert-var', function () {
                var v = $(this).data('var');
                $('#bodyEditor').summernote('pasteHTML', v);
                renderPreview();
            });

            // Handle Slugs
            @if(!$template->id)
                $(document).on('keyup change', '#tpl_name', function () {
                    if ($('#tpl_slug').val().trim() !== '') return;
                    const slug = $(this).val().toString()
                        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                        .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                    $('#tpl_slug').val(slug);
                });
            @endif

            // Test E-mail Logic
            $(document).on('click', '#btnSendTest', function () {
                const url = $(this).data('url');
                const email = $('#test_email_input').val();
                if (!url) return toastr.warning('Salve o template primeiro.');
                if (!email) return toastr.warning('Digite um e-mail.');

                const btn = $(this);
                const originalHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { email: email, _token: '{{ csrf_token() }}' },
                    success: function (res) { toastr.success(res.message); },
                    error: function () { toastr.error('Falha ao enviar teste.'); },
                    complete: function () { btn.prop('disabled', false).html(originalHtml); }
                });
            });

            // Start polling for initialization (crucial for slow-loading modals or SPA)
            let initCount = 0;
            const initInterval = setInterval(function() {
                initMailTemplateEditor();
                initCount++;
                if (initCount > 10) clearInterval(initInterval);
            }, 500);

            // Immediate call
            if (typeof jQuery !== 'undefined') {
                $(document).ready(initMailTemplateEditor);
            }
        })();
    </script>
@endpush
