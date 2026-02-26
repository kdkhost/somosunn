@extends('admin.layouts.app')

@section('title', isset($partner->id) ? 'Editar Parceiro' : 'Novo Parceiro')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col">
                    <h1 class="m-0">
                        <i class="fas fa-handshake text-primary mr-2"></i>
                        {{ isset($partner->id) ? 'Editar: ' . $partner->name : 'Novo Parceiro' }}
                    </h1>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.partners.index') }}" class="btn btn-outline-secondary"
                        title="Voltar para a listagem">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            {{-- As notificações agora são handled globalmente pelo app.blade.php via toastr --}}

            <div class="row">
                {{-- ── Card Principal ────────────────────────────────────────────── --}}
                <div class="col-lg-7">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-building mr-1"></i> Dados do Parceiro</h3>
                        </div>
                        <form method="POST"
                            action="{{ isset($partner->id) ? route('admin.partners.update', $partner) : route('admin.partners.store') }}"
                            enctype="multipart/form-data">
                            @csrf
                            @if(isset($partner->id)) @method('PUT') @endif
                            <div class="card-body">

                                {{-- Logo --}}
                                <div class="form-group text-center">
                                    <div id="logo-preview-wrap"
                                        style="width:200px;height:110px;margin:0 auto 12px;background:#f4f6f9;border-radius:12px;border:2px dashed #adb5bd;display:flex;align-items:center;justify-content:center;overflow:hidden;cursor:pointer;"
                                        onclick="document.getElementById('logo-input').click()">
                                        @if(isset($partner->id) && $partner->logo_url)
                                            <img id="logo-preview" src="{{ $partner->logo_url }}"
                                                style="max-width:190px;max-height:100px;object-fit:contain;">
                                        @else
                                            <div id="logo-placeholder" style="color:#adb5bd;text-align:center;">
                                                <i class="fas fa-image fa-2x mb-1"></i><br>
                                                <small>Clique para enviar logo</small>
                                            </div>
                                            <img id="logo-preview" src=""
                                                style="max-width:190px;max-height:100px;object-fit:contain;display:none;">
                                        @endif
                                    </div>
                                    <input type="file" name="logo" id="logo-input" accept="image/*" class="d-none">
                                    <small class="text-muted">PNG/JPG/SVG · Recomendado: fundo transparente · Máx
                                        3MB</small>
                                    @if(isset($partner->id) && $partner->logo_url)
                                        <div class="mt-2">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="remove_logo"
                                                    name="remove_logo" value="1">
                                                <label class="custom-control-label text-danger" for="remove_logo">Remover logo
                                                    atual</label>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Nome da Empresa <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $partner->name ?? '') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="form-group">
                                    <label>Usuário Responsável (Membro Parceiro)</label>
                                    <select name="user_id"
                                        class="form-control select2 @error('user_id') is-invalid @enderror">
                                        <option value="">-- Sem usuário vinculado --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id', $partner->user_id ?? '') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Usuário que poderá gerenciar os cupons na área de
                                        membros.</small>
                                    @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="form-group">
                                    <label>Slug (URL)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">/parceiros/</span>
                                        </div>
                                        <input type="text" name="slug"
                                            class="form-control @error('slug') is-invalid @enderror"
                                            value="{{ old('slug', $partner->slug ?? '') }}"
                                            placeholder="automático se vazio">
                                    </div>
                                    @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="form-group">
                                    <label>Site da Empresa</label>
                                    <input type="url" name="website_url"
                                        class="form-control @error('website_url') is-invalid @enderror"
                                        value="{{ old('website_url', $partner->website_url ?? '') }}"
                                        placeholder="https://...">
                                    @error('website_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="form-group">
                                    <label>Descrição breve</label>
                                    <textarea name="description" class="form-control" rows="3" maxlength="1000"
                                        placeholder="Fale um pouco sobre a empresa e seus benefícios para os membros UNN...">{{ old('description', $partner->description ?? '') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Ordem de exibição</label>
                                            <input type="number" name="order" class="form-control"
                                                value="{{ old('order', $partner->order ?? 0) }}" min="0">
                                            <small class="text-muted">Menor número = exibido primeiro. Deixe vazio para
                                                preencher automaticamente.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>&nbsp;</label><br>
                                            <div
                                                class="custom-control custom-switch custom-switch-lg custom-switch-on-success custom-switch-off-danger">
                                                <input type="hidden" name="active" value="0">
                                                <input type="checkbox" class="custom-control-input" id="active"
                                                    name="active" value="1" {{ old('active', $partner->active ?? true) ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold" for="active">Parceiro
                                                    Ativo</label>
                                            </div>
                                            <small class="text-muted d-block mt-1">Aparece no carrossel público</small>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary"
                                    title="{{ isset($partner->id) ? 'Salvar alterações do parceiro' : 'Salvar e cadastrar parceiro' }}">
                                    <i class="fas fa-save mr-1"></i>
                                    {{ isset($partner->id) ? 'Salvar Alterações' : 'Criar Parceiro' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ── Card Cupons ───────────────────────────────────────────────── --}}
                @if(isset($partner->id))
                    <div class="col-lg-5">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-ticket-alt mr-1"></i> Cupons de Desconto</h3>
                                <div class="card-tools">
                                    <button class="btn btn-sm btn-success" data-toggle="collapse" data-target="#new-coupon-form"
                                        title="Abrir formulário de novo cupom">
                                        <i class="fas fa-plus"></i> Novo Cupom
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">

                                {{-- Formulário Novo Cupom --}}
                                <div class="collapse p-3 border-bottom bg-light" id="new-coupon-form">
                                    <form method="POST" action="{{ route('admin.partners.coupons.store', $partner) }}">
                                        @csrf
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Código <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="code" class="form-control form-control-sm" required
                                                        placeholder="EX: UNN20">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Título <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="title" class="form-control form-control-sm"
                                                        required placeholder="Ex: 20% de desconto">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Tipo de desconto</label>
                                                    <select name="discount_type" class="form-control form-control-sm">
                                                        <option value="percent">Percentual (%)</option>
                                                        <option value="fixed">Valor fixo (R$)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Valor</label>
                                                    <input type="number" name="discount_value"
                                                        class="form-control form-control-sm" min="0" step="0.01" value="0">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Compra mínima (R$)</label>
                                                    <input type="number" name="min_purchase"
                                                        class="form-control form-control-sm" min="0" step="0.01"
                                                        placeholder="Sem mínimo">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Válido até</label>
                                                    <input type="date" name="expires_at" class="form-control form-control-sm">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Descrição</label>
                                                    <textarea name="description" class="form-control form-control-sm" rows="2"
                                                        placeholder="Detalhes do cupom..."></textarea>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="custom-control custom-switch d-inline-block mr-3">
                                                    <input type="hidden" name="active" value="1">
                                                    <input type="checkbox" class="custom-control-input" id="new_coupon_active"
                                                        name="active" value="1" checked>
                                                    <label class="custom-control-label small"
                                                        for="new_coupon_active">Ativo</label>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-success float-right"
                                                    title="Confirmar e adicionar cupom">
                                                    <i class="fas fa-plus mr-1"></i> Adicionar Cupom
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                {{-- Lista de Cupons --}}
                                @if(isset($coupons) && $coupons->count() > 0)
                                    <div class="list-group list-group-flush">
                                        @foreach($coupons as $coupon)
                                            <div class="list-group-item py-2 px-3">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="flex-grow-1 mr-2">
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <span
                                                                class="badge badge-{{ $coupon->discount_type === 'percent' ? 'success' : 'info' }} font-size-sm px-2 py-1 mr-2">
                                                                {{ $coupon->formatted_discount }}
                                                            </span>
                                                            <code class="text-primary font-weight-bold">{{ $coupon->code }}</code>
                                                            @if(!$coupon->active)
                                                                <span class="badge badge-secondary ml-1">Inativo</span>
                                                            @endif
                                                            @if($coupon->isExpired())
                                                                <span class="badge badge-danger ml-1">Expirado</span>
                                                            @endif
                                                        </div>
                                                        <div class="small text-muted">
                                                            {{ $coupon->title }}
                                                            @if($coupon->expires_at)
                                                                · até {{ $coupon->expires_at->format('d/m/Y') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-1">
                                                        <button class="btn btn-xs btn-outline-secondary"
                                                            onclick="editCoupon({{ $coupon->id }}, {{ $coupon->toJson() }})"
                                                            title="Editar Cupom">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form method="POST"
                                                            action="{{ route('admin.partners.coupons.destroy', [$partner, $coupon]) }}"
                                                            class="d-inline js-swal-confirm"
                                                            data-confirm-title="Remover cupom"
                                                            data-confirm-text="Remover este cupom?"
                                                            data-confirm-button="Remover">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-outline-danger"
                                                                title="Excluir Cupom">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-ticket-alt fa-2x mb-2"></i><br>
                                        <small>Nenhum cupom cadastrado.<br>Clique em "Novo Cupom" para adicionar.</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Modal de edição de cupom --}}
                    <div class="modal fade" id="editCouponModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form id="editCouponForm" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Cupom</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Código</label>
                                                    <input type="text" name="code" id="edit_code" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Título</label>
                                                    <input type="text" name="title" id="edit_title" class="form-control"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Tipo</label>
                                                    <select name="discount_type" id="edit_discount_type" class="form-control">
                                                        <option value="percent">Percentual (%)</option>
                                                        <option value="fixed">Valor fixo (R$)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Valor</label>
                                                    <input type="number" name="discount_value" id="edit_discount_value"
                                                        class="form-control" min="0" step="0.01">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Compra mínima (R$)</label>
                                                    <input type="number" name="min_purchase" id="edit_min_purchase"
                                                        class="form-control" min="0" step="0.01">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Válido até</label>
                                                    <input type="date" name="expires_at" id="edit_expires_at"
                                                        class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label class="small font-weight-bold">Descrição</label>
                                                    <textarea name="description" id="edit_description" class="form-control"
                                                        rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="custom-control custom-switch">
                                                    <input type="hidden" name="active" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="edit_active"
                                                        name="active" value="1">
                                                    <label class="custom-control-label" for="edit_active">Ativo</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary"><i
                                                class="fas fa-save mr-1"></i>Salvar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            // Preview de logo
            document.getElementById('logo-input')?.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.getElementById('logo-preview');
                    const ph = document.getElementById('logo-placeholder');
                    img.src = e.target.result;
                    img.style.display = 'block';
                    if (ph) ph.style.display = 'none';
                };
                reader.readAsDataURL(file);
            });

            // Editar cupom
            function editCoupon(id, coupon) {
                const base = '{{ route('admin.partners.coupons.update', [$partner->id ?? 0, ':coupon']) }}'
                    .replace(':coupon', id);
                document.getElementById('editCouponForm').action = base;
                document.getElementById('edit_code').value = coupon.code;
                document.getElementById('edit_title').value = coupon.title;
                document.getElementById('edit_discount_type').value = coupon.discount_type;
                document.getElementById('edit_discount_value').value = coupon.discount_value;
                document.getElementById('edit_min_purchase').value = coupon.min_purchase || '';
                document.getElementById('edit_expires_at').value = coupon.expires_at || '';
                document.getElementById('edit_description').value = coupon.description || '';
                document.getElementById('edit_active').checked = !!coupon.active;
                $('#editCouponModal').modal('show');
            }
        
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.js-swal-confirm').forEach(function (form) {
                    if (form.dataset.bound === '1') {
                        return;
                    }
                    form.dataset.bound = '1';

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();

                        if (typeof Swal === 'undefined') {
                            form.submit();
                            return;
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: form.dataset.confirmTitle || 'Confirmar ação',
                            text: form.dataset.confirmText || 'Deseja continuar?',
                            showCancelButton: true,
                            confirmButtonText: form.dataset.confirmButton || 'Confirmar',
                            cancelButtonText: 'Cancelar',
                            reverseButtons: true
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection
