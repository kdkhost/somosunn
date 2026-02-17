@extends('panel.layouts.app')

@section('title', 'Modelos de E-mail')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.mailtemplates.index') }}" class="hover:underline">Templates</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">
                Modelos de E-mail
            </h2>
            <a href="{{ route('panel.admin.mailtemplates.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02] flex items-center gap-2">
                <i class="fas fa-plus"></i> Novo Modelo
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Nome</th>
                            <th class="px-6 py-4">Slug</th>
                            <th class="px-6 py-4">Categoria</th>
                            <th class="px-6 py-4">Assunto</th>
                            <th class="px-6 py-4">Ativo</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($templates as $t)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $t->name }}</td>
                                <td class="px-6 py-4 text-xs font-mono bg-slate-100 rounded px-2 py-1 text-slate-600 w-fit">
                                    {{ $t->slug }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($t->category) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 truncate max-w-xs">{{ $t->subject }}</td>
                                <td class="px-6 py-4">
                                    @if($t->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Sim
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                            Não
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('panel.admin.mailtemplates.edit', $t) }}" 
                                       class="text-slate-400 hover:text-blue-500 transition" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="preview({{ $t->id }})" 
                                            class="text-slate-400 hover:text-blue-500 transition" title="Pré-visualizar">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <form action="{{ route('panel.admin.mailtemplates.destroy', $t) }}" method="POST" class="inline-block" 
                                          onsubmit="return confirm('Tem certeza que deseja remover este modelo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-red-500 transition" title="Remover">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($templates->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $templates->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col transform scale-95 transition-transform duration-300">
            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800">Pré-visualização</h3>
                <button onclick="closePreview()" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="flex-1 overflow-auto p-0 bg-slate-100">
                <div id="previewBody" class="w-full h-full min-h-[400px]"></div>
            </div>

            <div class="p-6 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                <div class="flex gap-4">
                    <input type="email" id="previewEmail" placeholder="Enviar para e-mail de teste" 
                           class="flex-1 rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <button id="sendPreviewBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl transition">
                        Enviar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const modal = document.getElementById('previewModal');
        const modalContent = modal.querySelector('div');
        let currentPreviewId = null;

        function preview(id) {
            currentPreviewId = id;
            
            // Show modal
            modal.classList.remove('hidden');
            // Trigger reflow
            void modal.offsetWidth;
            
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');

            document.getElementById('previewBody').innerHTML = '<div class="flex items-center justify-center h-full p-10"><i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i></div>';

            fetch('{{ url("panel/admin/mailtemplates") }}/' + id + '/preview')
                .then(r => r.json())
                .then(data => {
                    // Create an iframe to isolate styles
                    const iframe = document.createElement('iframe');
                    iframe.style.width = '100%';
                    iframe.style.height = '100%';
                    iframe.style.border = 'none';
                    document.getElementById('previewBody').innerHTML = '';
                    document.getElementById('previewBody').appendChild(iframe);
                    
                    const doc = iframe.contentWindow.document;
                    doc.open();
                    doc.write(data.html);
                    doc.close();
                });
        }

        function closePreview() {
            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        document.getElementById('sendPreviewBtn').addEventListener('click', function() {
            const email = document.getElementById('previewEmail').value;
            if(!email) return alert('Digite um e-mail');

            const btn = this;
            const originalText = btn.innerText;
            btn.innerText = 'Enviando...';
            btn.disabled = true;

            fetch('{{ url("panel/admin/mailtemplates") }}/' + currentPreviewId + '/send-preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: email })
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message || 'E-mail enviado!');
            })
            .catch(e => {
                console.error(e);
                alert('Erro ao enviar');
            })
            .finally(() => {
                btn.innerText = originalText;
                btn.disabled = false;
            });
        });
    </script>
    @endpush
@endsection
