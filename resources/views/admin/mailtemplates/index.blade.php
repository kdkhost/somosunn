@extends('admin.layouts.app')

@section('page_title', 'Modelos de E-mail')
@section('breadcrumb')
    <li class="breadcrumb-item active">Templates</li>
@endsection

@section('content')
    @php
        $isPanelAdmin = request()->is('painel/admin/*');
        $routePrefix = $isPanelAdmin ? 'panel.admin' : 'admin';
    @endphp

<div class="container-fluid">
    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-info elevation-1">
                <span class="info-box-icon"><i class="fas fa-envelope-open-text"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de Templates</span>
                    <span class="info-box-number">{{ $templates->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-success elevation-1">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ativos</span>
                    <span class="info-box-number">{{ $templates->where('is_active', true)->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-secondary elevation-1">
                <span class="info-box-icon"><i class="fas fa-pause-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Inativos</span>
                    <span class="info-box-number">{{ $templates->where('is_active', false)->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-mail-bulk mr-2"></i>Modelos de E-mail</h3>
            <div class="card-tools">
                <a href="{{ route($routePrefix . '.mailtemplates.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1">
                    <i class="fas fa-plus mr-1"></i> Novo Modelo
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th><i class="fas fa-tag text-muted mr-1"></i>Nome</th>
                            <th><i class="fas fa-code text-muted mr-1"></i>Slug</th>
                            <th><i class="fas fa-folder text-muted mr-1"></i>Categoria</th>
                            <th><i class="fas fa-heading text-muted mr-1"></i>Assunto</th>
                            <th><i class="fas fa-toggle-on text-muted mr-1"></i>Ativo</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $t)
                            <tr>
                                <td class="font-weight-bold">{{ $t->name }}</td>
                                <td><code class="text-muted">{{ $t->slug }}</code></td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-folder-open mr-1"></i>{{ ucfirst($t->category) }}</span>
                                </td>
                                <td class="text-muted">{{ Str::limit($t->subject, 50) }}</td>
                                <td>
                                    @if($t->is_active)
                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Sim</span>
                                    @else
                                        <span class="badge badge-secondary"><i class="fas fa-times-circle mr-1"></i>Não</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route($routePrefix . '.mailtemplates.edit', $t) }}"
                                        class="btn btn-sm btn-outline-info rounded-pill elevation-1 mr-1" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" onclick="preview({{ $t->id }})" class="btn btn-sm btn-outline-primary rounded-pill elevation-1 mr-1" title="Pré-visualizar">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <form action="{{ route($routePrefix . '.mailtemplates.destroy', $t) }}" method="POST"
                                        style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger rounded-pill elevation-1" data-confirm-delete data-confirm-title="Remover?"
                                            data-confirm-text="Esta ação não pode ser desfeita." title="Remover">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-envelope-open fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-2">Nenhum modelo de e-mail cadastrado.</p>
                                    <a href="{{ route($routePrefix . '.mailtemplates.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1">
                                        <i class="fas fa-plus mr-1"></i> Criar primeiro modelo
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $templates->links() }}</div>
        </div>
    </div>
</div>

    <!-- preview modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white"><i class="fas fa-eye mr-2"></i>Pré-visualização</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0" id="previewBody" style="overflow-y: auto; overflow-x: hidden; -webkit-overflow-scrolling: touch;"></div>
                <div class="modal-footer">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                        </div>
                        <input type="email" id="previewEmail" class="form-control" placeholder="Enviar para e-mail de teste">
                        <div class="input-group-append">
                            <button class="btn btn-primary rounded-right" id="sendPreviewBtn"><i class="fas fa-paper-plane mr-1"></i>Enviar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Detectar qual painel está sendo usado pela URL atual
        const isPanelAdmin = window.location.pathname.startsWith('/painel/admin');
        const routePrefix = isPanelAdmin ? 'panel.admin' : 'admin';
        
        // Debug: verificar qual URL está sendo gerada
        const previewRouteTemplate = isPanelAdmin 
            ? '{{ route("panel.admin.mailtemplates.preview", ":id") }}'
            : '{{ route("admin.mailtemplates.preview", ":id") }}';
        console.log('Using route prefix:', routePrefix);
        console.log('Preview route template:', previewRouteTemplate);
        
        function preview(id) {
            const previewUrl = previewRouteTemplate.replace(':id', id);
            console.log('Calling preview URL:', previewUrl);
            
            fetch(previewUrl)
                .then(r => {
                    console.log('Response status:', r.status);
                    if (!r.ok) throw new Error('Erro ao carregar preview');
                    return r.json();
                })
                .then(data => {
                    document.getElementById('previewBody').innerHTML = data.html;
                    window.previewId = id;
                    $('#previewModal').modal('show');
                })
                .catch(e => {
                    console.error('Preview error:', e);
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
            
            const sendRouteTemplate = isPanelAdmin
                ? '{{ route("panel.admin.mailtemplates.sendpreview", ":id") }}'
                : '{{ route("admin.mailtemplates.sendpreview", ":id") }}';
            const sendUrl = sendRouteTemplate.replace(':id', window.previewId);
            
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
@endpush
