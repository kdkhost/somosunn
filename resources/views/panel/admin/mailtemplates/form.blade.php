@extends('panel.layouts.app')

@section('title', ($template->id ? 'Editar' : 'Novo') . ' Modelo de E-mail')

@push('styles')
    <!-- Summernote Lite CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
@endpush
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Summernote -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-pt-BR.min.js"></script>

    <script>
        $(document).ready(function () {
            // Init Summernote
            $('#bodyEditor').summernote({
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
                ]
            });

            // Insert Variables
            $('.insert-var').on('click', function () {
                var v = $(this).data('var');
                $('#bodyEditor').summernote('pasteHTML', v);
            });

            // Auto Slug (only for new)
            @if(!$template->id)
                $('#tpl_name').on('keyup change', function () {
                    if ($('#tpl_slug').val().trim() !== '') return;
                    const slug = $(this).val().toString()
                        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                        .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                    $('#tpl_slug').val(slug);
                });
            @endif

            // Test Email
            $('#btnSendTest').click(function () {
                const url = $(this).data('url');
                if (!url) return toastr.warning('Salve o template primeiro antes de testar.');

                const email = $('#test_email_input').val();
                if (!email) return toastr.warning('Digite um e-mail para teste.');

                const btn = $(this);
                const originalContent = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        email: email,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        toastr.success(res.message || 'E-mail enviado com sucesso!');
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        toastr.error('Erro ao enviar e-mail.');
                    },
                    complete: function () {
                        btn.prop('disabled', false).html(originalContent);
                    }
                });
            });

            // Preview Logic
            function renderPreview() {
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
                @endphp

                const logo = '{{ $logoUrl }}';
                const siteName = '{{ config('app.name') }}';
                const siteUrl = '{{ url('/') }}';
                const year = '{{ date('Y') }}';
                const headerStyle = '{!! $headerStyle !!}';
                const primaryColor = '{{ $color1 }}';
                
                const bodyContent = $('#bodyEditor').summernote('code');
                
                $('#tpl_preview').html(`
                    <div style="background-color: #ffffff; width: 100%; font-family: sans-serif; box-sizing: border-box; display: flex; flex-direction: column; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;">
                        <div style="${headerStyle} padding: 40px 20px; text-align: center; flex-shrink: 0;">
                            <img src="${logo}" alt="${siteName}" style="max-height: 45px; max-width: 100%; height: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                        </div>
                        <div style="padding: 30px; color: #333333; line-height: 1.6; word-wrap: break-word; font-size: 14px; min-height: 250px;">
                            ${bodyContent}
                        </div>
                        <div style="background-color: #fcfcfc; padding: 25px 20px; text-align: center; color: #888888; font-size: 11px; border-top: 1px dashed #eeeeee; flex-shrink: 0;">
                            <p style="margin: 4px 0; font-weight: 500;">&copy; ${year} ${siteName}.</p>
                            <p style="margin: 4px 0;"><a href="${siteUrl}" style="color: ${primaryColor}; text-decoration: none; font-weight: 700;">Visite nosso site</a></p>
                        </div>
                    </div>
                `);
            }

            renderPreview();
            $('#bodyEditor').on('summernote.change', renderPreview);
        });
    </script>
@endpush