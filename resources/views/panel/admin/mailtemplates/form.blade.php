@extends('panel.layouts.app')

@section('title', ($template->id ? 'Editar' : 'Novo') . ' Modelo de E-mail')

@push('styles')
    <!-- Summernote Lite CSS (No Bootstrap) -->
    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    <style>
        .note-editor.note-frame {
            border-radius: 1rem;
            border-color: #e2e8f0;
        }
        .note-toolbar {
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
            background-color: #f8fafc !important;
            border-bottom-color: #e2e8f0;
        }
        .note-statusbar {
            border-bottom-left-radius: 1rem;
            border-bottom-right-radius: 1rem;
        }
    </style>
@endpush

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.mailtemplates.index') }}" class="hover:underline">Templates</a>
    <span class="mx-2">/</span>
    <span class="text-slate-500">{{ $template->id ? 'Editar' : 'Novo' }}</span>
@endsection

@section('panel_content')
    <form action="{{ $template->id ? route('panel.admin.mailtemplates.update', $template) : route('panel.admin.mailtemplates.store') }}" method="POST">
        @csrf
        @if($template->id)
            @method('PUT')
        @endif

        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-slate-800">
                    {{ $template->id ? 'Editar' : 'Novo' }} Modelo de E-mail
                </h2>
                <div class="flex gap-3">
                    <a href="{{ route('panel.admin.mailtemplates.index') }}" 
                       class="bg-white border border-slate-200 text-slate-600 font-bold py-2 px-4 rounded-xl hover:bg-slate-50 transition">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02]">
                        <i class="fas fa-save mr-2"></i> Salvar
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nome</label>
                                <input type="text" name="name" id="tpl_name" value="{{ old('name', $template->name) }}" required
                                       class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Slug (Identificador)</label>
                                <input type="text" name="slug" id="tpl_slug" value="{{ old('slug', $template->slug) }}" required
                                       {{ $template->id ? 'readonly' : '' }}
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Assunto do E-mail</label>
                            <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" required
                                   class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Conteúdo (HTML)</label>
                            <textarea name="body" id="bodyEditor">{{ old('body', $template->body) }}</textarea>
                            <p class="text-xs text-slate-500 mt-2">A logo da plataforma será inserida automaticamente no topo do e-mail.</p>
                        </div>
                    </div>

                    <!-- Variables -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h3 class="font-bold text-slate-800 mb-4">Variáveis Disponíveis</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @php
                                $vars = [
                                    ['{{user.name}}','Nome do usuário'],
                                    ['{{user.email}}','E-mail do usuário'],
                                    ['{{site.name}}','Nome do site'],
                                    ['{{site.url}}','URL do site'],
                                    ['{{order.id}}','ID do pedido'],
                                    ['{{order.total}}','Total do pedido'],
                                    ['{{order.status}}','Status do pedido'],
                                    ['{{payment.link}}','Link de pagamento'],
                                    ['{{event.title}}','Nome do evento'],
                                    ['{{course.title}}','Nome do curso'],
                                    ['{{mentorship.title}}','Nome da mentoria']
                                ];
                            @endphp
                            @foreach($vars as [$v, $d])
                                <button type="button" class="insert-var text-left p-3 rounded-xl border border-slate-100 hover:bg-blue-50 hover:border-blue-200 transition group" data-var="{{ $v }}">
                                    <div class="font-mono text-xs font-bold text-blue-600">{{ $v }}</div>
                                    <div class="text-xs text-slate-500 group-hover:text-blue-800">{{ $d }}</div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sidebar Settings -->
                <div class="space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-4">
                        <h3 class="font-bold text-slate-800 mb-2">Configurações</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Categoria</label>
                            <select name="category" class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                @foreach(['sistema', 'conta', 'financeiro', 'marketing'] as $cat)
                                    <option value="{{ $cat }}" {{ old('category', $template->category) == $cat ? 'selected' : '' }}>
                                        {{ ucfirst($cat) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="is_active" class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                <option value="1" {{ old('is_active', $template->is_active ?? true) ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ old('is_active', $template->is_active ?? true) ? '' : 'selected' }}>Inativo</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Idioma</label>
                            <input type="text" name="locale" value="{{ old('locale', $template->locale ?? 'pt-BR') }}"
                                   class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Test Email -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-4">
                        <h3 class="font-bold text-slate-800 mb-2">Testar Template</h3>
                        <p class="text-xs text-slate-500">Salve as alterações antes de testar para visualizar o conteúdo atualizado.</p>
                        
                        <div class="flex gap-2">
                             <input type="email" id="test_email_input" placeholder="seu@email.com" 
                                    class="flex-1 rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm">
                             <button type="button" id="btnSendTest" 
                                     data-url="{{ $template->id ? route('panel.admin.mailtemplates.sendpreview', $template) : '' }}"
                                     class="bg-slate-800 text-white rounded-xl px-3 hover:bg-slate-900 transition">
                                 <i class="fas fa-paper-plane"></i>
                             </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<!-- jQuery (Required for Summernote) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Summernote Lite JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-pt-BR.min.js"></script>

<script>
    $(document).ready(function() {
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
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        // Insert Variables
        $('.insert-var').on('click', function() {
            var v = $(this).data('var');
            $('#bodyEditor').summernote('pasteHTML', v);
        });

        // Auto Slug (only for new)
        @if(!$template->id)
            $('#tpl_name').on('keyup change', function(){
                if($('#tpl_slug').val().trim() !== '') return;
                const slug = $(this).val().toString()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
                    .toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
                $('#tpl_slug').val(slug);
            });
        @endif

        // Test Email
        $('#btnSendTest').click(function() {
            const url = $(this).data('url');
            if(!url) {
                alert('Salve o template primeiro antes de testar.');
                return;
            }
            
            const email = $('#test_email_input').val();
            if(!email) {
                alert('Digite um e-mail para teste.');
                return;
            }

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
                success: function(res) {
                    alert(res.message || 'E-mail enviado com sucesso!');
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('Erro ao enviar e-mail.');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalContent);
                }
            });
        });
    });
</script>
@endpush
