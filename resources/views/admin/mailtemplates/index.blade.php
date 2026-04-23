@extends('admin.layouts.app')

@section('page_title', 'Modelos de E-mail')
@section('breadcrumb')
    <li class="breadcrumb-item active">Templates</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <h3 class="m-0">Modelos de E-mail</h3>
                <a href="{{ route('admin.mailtemplates.create') }}" class="btn btn-primary">Novo Modelo</a>
            </div>

            {{-- Toastr global --}}

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th>Categoria</th>
                        <th>Assunto</th>
                        <th>Ativo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $t)
                        <tr>
                            <td>{{ $t->name }}</td>
                            <td>{{ $t->slug }}</td>
                            <td>{{ ucfirst($t->category) }}</td>
                            <td>{{ $t->subject }}</td>
                            <td>{{ $t->is_active ? 'Sim' : 'Não' }}</td>
                            <td>
                                <a href="{{ route('admin.mailtemplates.edit', $t) }}"
                                    class="btn btn-sm btn-secondary">Editar</a>
                                <a href="#" onclick="preview({{ $t->id }})" class="btn btn-sm btn-info">Pré-visualizar</a>
                                <form action="{{ route('admin.mailtemplates.destroy', $t) }}" method="POST"
                                    style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" data-confirm-delete data-confirm-title="Remover?"
                                        data-confirm-text="Esta ação não pode ser desfeita.">Remover</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $templates->links() }}
        </div>
    </div>

    <!-- preview modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pré-visualização</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="previewBody"></div>
                <div class="modal-footer">
                    <input type="email" id="previewEmail" class="form-control" placeholder="Enviar para e-mail de teste">
                    <button class="btn btn-primary" id="sendPreviewBtn">Enviar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function preview(id) {
            const previewUrl = '{{ route("admin.mailtemplates.preview", ":id") }}'.replace(':id', id);
            fetch(previewUrl)
                .then(r => {
                    if (!r.ok) throw new Error('Erro ao carregar preview');
                    return r.json();
                })
                .then(data => {
                    document.getElementById('previewBody').innerHTML = data.html;
                    window.previewId = id;
                    $('#previewModal').modal('show');
                })
                .catch(e => {
                    Swal.fire({ 
                        title: 'Erro de conexão', 
                        text: 'Não foi possível comunicar com o servidor.', 
                        icon: 'error' 
                    });
                });
        }

        document.getElementById('sendPreviewBtn').addEventListener('click', function () {
            var to = document.getElementById('previewEmail').value;
            if (!to) {
                Swal.fire({ title: 'Atenção', text: 'Digite um e-mail válido.', icon: 'warning' });
                return;
            }
            
            const sendUrl = '{{ route("admin.mailtemplates.sendpreview", ":id") }}'.replace(':id', window.previewId);
            
            Swal.fire({ title: 'Enviando...', text: 'Aguarde', icon: 'info', showConfirmButton: false });
            
            fetch(sendUrl, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: to })
            })
            .then(r => {
                if (!r.ok) throw new Error('Erro ao enviar');
                return r.json();
            })
            .then(data => {
                Swal.fire({ title: 'Sucesso', text: data.message || 'E-mail enviado com sucesso!', icon: 'success' });
            })
            .catch(e => { 
                Swal.fire({ title: 'Erro', text: 'Não foi possível enviar o e-mail de teste.', icon: 'error' }); 
            });
        });
    </script>
@endsection