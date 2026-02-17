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
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-bold py-2 px-4 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02]">
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
                // ... (rest of test logic copied from original/partial logic if needed)
                // For standalone form, we can just use the same logic or alert
                if (!url) return alert('Salve o template primeiro antes de testar.');

                const email = $('#test_email_input').val();
                if (!email) return alert('Digite um e-mail para teste.');

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
                        alert(res.message || 'E-mail enviado com sucesso!');
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        alert('Erro ao enviar e-mail.');
                    },
                    complete: function () {
                        btn.prop('disabled', false).html(originalContent);
                    }
                });
            });
        });
    </script>
@endpush