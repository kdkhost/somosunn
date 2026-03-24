@extends('panel.layouts.app')

@section('title', 'Modelos de E-mail')

@push('styles')
    <!-- Summernote Lite CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    <style>
        .note-modal-backdrop {
            z-index: 40 !important;
        }
    </style>
@endpush

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.mailtemplates.index') }}" class="hover:underline">Templates</a>
@endsection

@section('panel_content')
    <div class="space-y-6">

        <!-- Header & Toolbar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Modelos de E-mail</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm">Gerencie os templates de notificação do sistema.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <form action="{{ route('panel.admin.mailtemplates.index') }}" method="GET"
                    class="relative group w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar templates..."
                        class="pl-10 py-3 w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium transition shadow-sm">
                </form>
                <button onclick="openEditor()"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02] flex items-center justify-center gap-2 whitespace-nowrap">
                    <i class="fas fa-plus"></i> Novo Modelo
                </button>
            </div>
        </div>

        <!-- Filters (Optional - Categories) -->
        <div class="flex gap-2 overflow-x-auto pb-2 no-scrollbar">
            <a href="{{ route('panel.admin.mailtemplates.index') }}"
                class="px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap transition {{ !request('category') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800' }}">
                Todos
            </a>
            @foreach(['sistema', 'conta', 'financeiro', 'marketing'] as $cat)
                <a href="{{ route('panel.admin.mailtemplates.index', ['category' => $cat]) }}"
                    class="px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap transition {{ request('category') == $cat ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800' }}">
                    {{ ucfirst($cat) }}
                </a>
            @endforeach
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 dark:text-slate-500 tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Nome / Slug</th>
                            <th class="px-6 py-4">Categoria</th>
                            <th class="px-6 py-4">Assunto</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($templates as $t)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition group">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-slate-800 dark:text-white">{{ $t->name }}</div>
                                                <div class="text-xs font-mono text-slate-400 dark:text-slate-500 mt-0.5">{{ $t->slug }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold 
                                                                                                                        {{ $t->category == 'financeiro' ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400' :
                            ($t->category == 'conta' ? 'bg-purple-100 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400' :
                                ($t->category == 'sistema' ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-400' : 'bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400')) }}">
                                                    {{ ucfirst($t->category) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 max-w-xs truncate dark:text-slate-300" title="{{ $t->subject }}">
                                                {{ $t->subject }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                @if($t->is_active)
                                                    <div class="w-2 h-2 rounded-full bg-green-500 mx-auto shadow-sm shadow-green-500/50" title="Ativo"></div>
                                                @else
                                                    <div class="w-2 h-2 rounded-full bg-red-400 mx-auto shadow-sm shadow-red-400/50" title="Inativo"></div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div
                                                    class="flex items-center justify-end gap-2 transition-opacity">
                                                    <button
                                                        onclick="openEditor('{{ route('panel.admin.mailtemplates.edit', $t) }}', '{{ route('panel.admin.mailtemplates.update', $t) }}')"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                                                        title="Editar">
                                                        <i class="fas fa-pen text-xs"></i>
                                                    </button>
                                                    <button onclick="preview({{ $t->id }})"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition"
                                                        title="Pré-visualizar">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </button>
                                                    <form action="{{ route('panel.admin.mailtemplates.destroy', $t) }}" method="POST"
                                                        onsubmit="return confirmAction(event, 'Excluir template?', 'Excluir este template?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition"
                                                            title="Remover">
                                                            <i class="fas fa-trash-alt text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="fas fa-inbox text-4xl opacity-20"></i>
                                        <p>Nenhum template encontrado.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($templates->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                    {{ $templates->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Editor Modal -->
    <div id="editorModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-5xl max-h-[95vh] flex flex-col transform scale-95 transition-transform duration-300 border border-slate-200 dark:border-slate-800">
            <!-- Modal Header -->
            <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 id="modalTitle" class="text-lg font-bold text-slate-800 dark:text-white">Novo Modelo</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Preencha as informações abaixo.</p>
                </div>
                <button onclick="closeEditor()"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 bg-slate-50 dark:bg-slate-950 custom-scrollbar relative" id="editorBody">
                <!-- Content injected via AJAX -->
                <div class="flex flex-col items-center justify-center h-40 text-slate-400 dark:text-slate-600">
                    <i class="fas fa-circle-notch fa-spin text-3xl mb-3 text-blue-500"></i>
                    <p>Carregando editor...</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-b-2xl flex justify-between items-center gap-4">
                <div class="text-xs text-slate-400 dark:text-slate-500 hidden sm:block">
                    <i class="fas fa-info-circle mr-1"></i> Todos os campos marcados são obrigatórios.
                </div>
                <div class="flex gap-3">
                    <button onclick="closeEditor()"
                        class="px-5 py-2.5 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                    <button id="btnSaveTemplate"
                        class="px-6 py-2.5 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02] flex items-center gap-2">
                        <i class="fas fa-save"></i> <span>Salvar Template</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal (Simplified reused) -->
    <div id="previewModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col transform scale-95 transition-transform duration-300 border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center p-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-eye text-blue-500"></i> Pré-visualização
                </h3>
                <button onclick="closePreview()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto bg-slate-100 dark:bg-slate-950 p-0 relative custom-scrollbar">
                <div id="previewLoader" class="absolute inset-0 flex items-center justify-center bg-slate-100 dark:bg-slate-950 z-10 transition-colors duration-300">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i>
                </div>
                <iframe id="previewFrame" class="w-full border-0 transition-all duration-300"
                    style="min-height: 500px;"></iframe>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-b-2xl">
                <div class="flex gap-3">
                    <input type="email" id="previewEmail" placeholder="E-mail para teste"
                        class="flex-1 px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    <button id="sendPreviewBtn"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-8 rounded-2xl font-bold shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02] whitespace-nowrap">Enviar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <!-- jQuery (Required for Summernote) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Summernote -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-pt-BR.min.js"></script>

    <script>
        // Modal & Editor Logic
        const editorModal = document.getElementById('editorModal');
        const editorBody = document.getElementById('editorBody');
        const modalTitle = document.getElementById('modalTitle');
        let currentAction = '';
        let currentMethod = 'POST';

        function openEditor(editUrl = null, updateUrl = null) {
            // Show modal
            editorModal.classList.remove('hidden');
            void editorModal.offsetWidth; // trigger reflow
            editorModal.classList.remove('opacity-0');
            editorModal.querySelector('div').classList.remove('scale-95');
            editorModal.querySelector('div').classList.add('scale-100');

            // Reset Content
            editorBody.innerHTML = `
                                <div class="flex flex-col items-center justify-center h-40 text-slate-400">
                                    <i class="fas fa-circle-notch fa-spin text-3xl mb-3 text-blue-500"></i>
                                    <p>Carregando editor...</p>
                                </div>
                            `;

            const url = editUrl || '{{ route("panel.admin.mailtemplates.create") }}';
            currentAction = updateUrl || '{{ route("panel.admin.mailtemplates.store") }}';
            currentMethod = updateUrl ? 'PUT' : 'POST';
            modalTitle.innerText = editUrl ? 'Editar Modelo' : 'Novo Modelo';

            // Fetch Form
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.text())
                .then(html => {
                    editorBody.innerHTML = html;
                    initSummernote();
                    initScripts(); // Re-init specific scripts for the form
                })
                .catch(err => {
                    editorBody.innerHTML = '<div class="text-red-500 text-center p-10">Erro ao carregar formulário.</div>';
                    console.error(err);
                });
        }

        function closeEditor() {
            // Destroy Summernote to prevent leaks
            if ($('#bodyEditor').length > 0) {
                $('#bodyEditor').summernote('destroy');
            }

            editorModal.classList.add('opacity-0');
            editorModal.querySelector('div').classList.remove('scale-100');
            editorModal.querySelector('div').classList.add('scale-95');
            setTimeout(() => {
                editorModal.classList.add('hidden');
            }, 300);
        }

        function initSummernote() {
            $('#bodyEditor').summernote({
                placeholder: 'Conteúdo do e-mail...',
                tabsize: 2,
                height: 350,
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
                    onPaste: function (e) {
                        var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                        e.preventDefault();
                        document.execCommand('insertText', false, bufferText);
                    }
                }
            });
        }

        function initScripts() {
            // Re-bind click events for variables
            $('.insert-var').off('click').on('click', function () {
                var v = $(this).data('var');
                $('#bodyEditor').summernote('pasteHTML', v);
            });

            // Auto slug for new items
            if (currentMethod === 'POST') {
                $('#tpl_name').off('keyup change').on('keyup change', function () {
                    if ($('#tpl_slug').val().trim() !== '') return;
                    const slug = $(this).val().toString()
                        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                        .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                    $('#tpl_slug').val(slug);
                });
            }

            // Test Email Button inside Editor
            $('#btnSendTest').off('click').on('click', function () {
                const url = $(this).data('url');
                if (!url) return toastr.warning('Salve o template antes de testar.');
                const email = $('#test_email_input').val();
                if (!email) return toastr.warning('Digite um e-mail.');

                const btn = $(this);
                const original = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: url, method: 'POST',
                    data: { email: email, _token: '{{ csrf_token() }}' },
                    success: (res) => toastr.success(res.message || 'Enviado!'),
                    error: () => toastr.error('Erro.'),
                    complete: () => btn.prop('disabled', false).html(original)
                });
            });
        }

        // Save Template
        document.getElementById('btnSaveTemplate').addEventListener('click', function () {
            const btn = this;
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            if (currentMethod === 'PUT') formData.append('_method', 'PUT');

            // Gather fields manually or use form element if wrapped (partial is just divs)
            // Since partial is just divs, we select inputs inside #editorBody
            const inputs = editorBody.querySelectorAll('input, select, textarea');
            inputs.forEach(el => {
                // Skip body, we handle it via Summernote API
                if (el.name && el.name !== 'body') formData.append(el.name, el.value);
            });

            // Get Summernote Content
            if ($('#bodyEditor').length > 0) {
                formData.append('body', $('#bodyEditor').summernote('code'));
            }

            fetch(currentAction, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw data;
                    return data;
                })
                .then(data => {
                    // Success
                    toastr.success(data.message || 'Salvo com sucesso!');
                    window.location.reload(); // Reload to update table
                })
                .catch(err => {
                    console.error(err);
                    if (err.errors) {
                        let msg = '';
                        for (let k in err.errors) msg += err.errors[k].join('\n') + '\n';
                        toastr.error('Erro de validação:<br>' + msg.replace(/\n/g, '<br>'));
                    } else {
                        toastr.error('Erro ao salvar.');
                    }
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
        });

        // Preview Logic (Reused)
        const previewModal = document.getElementById('previewModal');
        let currentPreviewId = null;

        function preview(id) {
            currentPreviewId = id;
            previewModal.classList.remove('hidden');
            void previewModal.offsetWidth;
            previewModal.classList.remove('opacity-0');
            previewModal.querySelector('div').classList.remove('scale-95');
            previewModal.querySelector('div').classList.add('scale-100');

            const loader = document.getElementById('previewLoader');
            const iframe = document.getElementById('previewFrame');
            loader.style.display = 'flex';
            
            // Clear previous content
            try {
                const doc = iframe.contentWindow.document;
                doc.open();
                doc.write('<div style="display:flex;align-items:center;justify-content:center;height:100%;font-family:sans-serif;color:#94a3b8;">Carregando...</div>');
                doc.close();
            } catch(e) {}

            fetch('{{ url("painel/admin/mailtemplates") }}/' + id + '/preview')
                .then(r => {
                    if (!r.ok) throw new Error('Falha ao carregar preview (Status: ' + r.status + ')');
                    return r.json();
                })
                .then(data => {
                    const doc = iframe.contentWindow.document;
                    doc.open();
                    // Force white background for the preview content
                    doc.write('<style>body{background:#fff!important;margin:0;padding:20px;display:flex;justify-content:center;}</style>');
                    doc.write(data.html);
                    doc.close();

                    // Adjust height after content written
                    setTimeout(() => {
                        const height = iframe.contentWindow.document.body.scrollHeight;
                        iframe.style.height = (height + 50) + 'px';
                    }, 300);
                })
                .catch(err => {
                    console.error(err);
                    const doc = iframe.contentWindow.document;
                    doc.open();
                    doc.write('<div style="padding:40px;text-align:center;color:#ef4444;font-family:sans-serif;"><b>Erro ao carregar pré-visualização</b><br>' + err.message + '</div>');
                    doc.close();
                })
                .finally(() => {
                    loader.style.display = 'none';
                });
        }

        function closePreview() {
            previewModal.classList.add('opacity-0');
            previewModal.querySelector('div').classList.remove('scale-100');
            previewModal.querySelector('div').classList.add('scale-95');
            setTimeout(() => { previewModal.classList.add('hidden'); }, 300);
        }

        document.getElementById('sendPreviewBtn').addEventListener('click', function () {
            const email = document.getElementById('previewEmail').value;
            if (!email) {
                Swal.fire('Atenção', 'Digite um e-mail de destino.', 'warning');
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerText = 'Enviando...';

            fetch('{{ url("painel/admin/mailtemplates") }}/' + currentPreviewId + '/send-preview', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ email: email })
            })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        Swal.fire({ icon: 'success', title: 'Enviado!', text: d.message || 'E-mail de teste enviado com sucesso!', timer: 3000, showConfirmButton: false });
                    } else {
                        Swal.fire('Erro ao enviar', d.error || 'Falha ao disparar o e-mail. Verifique as configurações SMTP.', 'error');
                    }
                })
                .catch(() => Swal.fire('Erro de conexão', 'Não foi possível comunicar com o servidor.', 'error'))
                .finally(() => { btn.disabled = false; btn.innerText = 'Enviar'; });
        });
    </script>
@endpush