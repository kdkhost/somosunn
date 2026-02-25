@extends('admin.layouts.app')

@section('title', 'Empresas Parceiras')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-handshake text-primary mr-2"></i>Empresas Parceiras</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.partners.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Novo Parceiro
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            {{-- As notificações agora são handled globalmente pelo app.blade.php via toastr --}}

            {{-- As notificações agora são handled globalmente pelo app.blade.php via toastr --}}

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-1"></i> {{ $partners->count() }} parceiro(s)
                        cadastrado(s)</h3>
                    <div class="card-tools">
                        <small class="text-muted"><i class="fas fa-grip-vertical mr-1"></i>Arraste para reordenar</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($partners->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum parceiro cadastrado ainda.</p>
                            <a href="{{ route('admin.partners.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i> Cadastrar primeiro parceiro
                            </a>
                        </div>
                    @else
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:40px;"></th>
                                    <th style="width:70px;">Logo</th>
                                    <th>Nome / Site</th>
                                    <th class="text-center">Cupons</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Ordem</th>
                                    <th class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="partners-sortable">
                                @foreach($partners as $partner)
                                    <tr data-id="{{ $partner->id }}">
                                        <td class="align-middle text-center text-muted" style="cursor:grab;">
                                            <i class="fas fa-grip-vertical"></i>
                                        </td>
                                        <td class="align-middle">
                                            <div
                                                style="width:56px;height:36px;background:#f8f9fa;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid #dee2e6;">
                                                @if($partner->logo_url)
                                                    <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                                                        style="max-width:52px;max-height:32px;object-fit:contain;">
                                                @else
                                                    <i class="fas fa-building text-muted"></i>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold">{{ $partner->name }}</div>
                                            @if($partner->website_url)
                                                <a href="{{ $partner->website_url }}" target="_blank" class="small text-muted">
                                                    <i class="fas fa-external-link-alt mr-1"></i>{{ $partner->website_url }}
                                                </a>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-info">{{ $partner->active_coupons_count ?? 0 }} ativos</span>
                                            <span class="badge badge-secondary">{{ $partner->coupons_count ?? 0 }} total</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            @if($partner->active)
                                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Ativo</span>
                                            @else
                                                <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Inativo</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-light">{{ $partner->order }}</span>
                                        </td>
                                        <td class="align-middle text-right">
                                            <a href="{{ route('admin.partners.edit', $partner) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <form method="POST" action="{{ route('admin.partners.destroy', $partner) }}"
                                                class="d-inline partner-delete-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-partner"
                                                    data-nome="{{ $partner->name }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </section>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            // Drag-to-reorder
            const tbody = document.getElementById('partners-sortable');
            if (tbody) {
                Sortable.create(tbody, {
                    handle: '.fa-grip-vertical',
                    animation: 150,
                    onEnd() {
                        const order = [...tbody.querySelectorAll('tr[data-id]')].map(tr => parseInt(tr.dataset.id));
                        fetch('{{ route('admin.partners.order') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ order })
                        }).then(r => r.json()).then(data => {
                            if (data.status === 'success') {
                                toastr.success(data.message || 'Ordem atualizada!');
                            }
                        });
                    }
                });
            }

            // Confirmação de exclusão via SweetAlert2
            document.querySelectorAll('.btn-delete-partner').forEach(btn => {
                btn.addEventListener('click', function () {
                    const nome = this.dataset.nome;
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Remover parceiro?',
                        html: `Isso removerá <strong>${nome}</strong> e todos os cupons vinculados.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e3342f',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Remover',
                        cancelButtonText: 'Cancelar'
                    }).then(result => { if (result.isConfirmed) form.submit(); });
                });
            });
        </script>
    @endpush
@endsection