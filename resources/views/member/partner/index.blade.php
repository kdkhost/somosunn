@extends('layouts.app')

@section('title', 'Meu Painel de Parceiro — ' . $partner->name)
@section('description', 'Gerencie seus cupons exclusivos para membros da plataforma UNN.')

@section('content')
    <div style="background:linear-gradient(135deg,#f0f4ff 0%,#f8fafc 60%,#e8f5ff 100%);min-height:100vh;">

        {{-- Hero Header --}}
        <div style="background:linear-gradient(135deg,#0f172a 0%,#1e3a6b 60%,#1f5edb 100%);padding:2.5rem 1rem 4rem;">
            <div class="max-w-4xl mx-auto relative z-10">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-5">
                    {{-- Logo --}}
                    <div
                        style="background:#fff;border-radius:16px;padding:1rem;min-width:120px;min-height:70px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(0,0,0,0.25);">
                        @if($partner->logo_url)
                            <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                                style="max-width:100px;max-height:56px;object-fit:contain;">
                        @else
                            <i class="fas fa-building text-slate-400 text-3xl"></i>
                        @endif
                    </div>
                    {{-- Info --}}
                    <div class="text-center md:text-left">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold mb-2"
                            style="background:rgba(96,165,250,0.2);color:#93c5fd;border:1px solid rgba(96,165,250,0.3);">
                            <i class="fas fa-handshake"></i> Parceiro UNN
                        </div>
                        <h1 class="text-2xl md:text-3xl font-black text-white">{{ $partner->name }}</h1>
                        @if($partner->description)
                            <p class="text-slate-300 text-sm mt-1 max-w-lg">{{ $partner->description }}</p>
                        @endif
                    </div>
                    {{-- Botão de novo cupom --}}
                    <div class="md:ml-auto flex-shrink-0">
                        <button id="btn-novo-cupom"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white text-sm transition-all hover:scale-105"
                            style="background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 4px 15px rgba(5,150,105,0.3);">
                            <i class="fas fa-plus-circle"></i> Novo Cupom
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Conteúdo --}}
        <div class="max-w-4xl mx-auto px-4" style="margin-top:-2rem;padding-bottom:3rem;">

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                @php
                    $total = $coupons->count();
                    $ativos = $coupons->where('active', true)->count();
                    $expirados = $coupons->filter(fn($c) => $c->is_expired)->count();
                @endphp
                <div class="rounded-2xl p-4 text-center shadow" style="background:#fff;border:1px solid #e2e8f0;">
                    <div class="text-2xl font-black" style="color:#1f5edb;">{{ $total }}</div>
                    <div class="text-xs text-slate-500 mt-1">Total</div>
                </div>
                <div class="rounded-2xl p-4 text-center shadow" style="background:#fff;border:1px solid #e2e8f0;">
                    <div class="text-2xl font-black text-green-600">{{ $ativos }}</div>
                    <div class="text-xs text-slate-500 mt-1">Ativos</div>
                </div>
                <div class="rounded-2xl p-4 text-center shadow" style="background:#fff;border:1px solid #e2e8f0;">
                    <div class="text-2xl font-black text-amber-500">{{ $total - $ativos }}</div>
                    <div class="text-xs text-slate-500 mt-1">Inativos</div>
                </div>
                <div class="rounded-2xl p-4 text-center shadow" style="background:#fff;border:1px solid #e2e8f0;">
                    <div class="text-2xl font-black text-red-500">{{ $expirados }}</div>
                    <div class="text-xs text-slate-500 mt-1">Expirados</div>
                </div>
            </div>

            {{-- Lista de Cupons --}}
            <div class="rounded-2xl shadow-xl overflow-hidden" style="background:#fff;border:1px solid #e2e8f0;">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h2 class="font-bold text-slate-700 text-lg"><i class="fas fa-ticket-alt text-blue-500 mr-2"></i>Meus
                        Cupons</h2>
                </div>

                @if($coupons->isEmpty())
                    <div class="text-center py-12">
                        <i class="fas fa-ticket-alt fa-3x mb-3" style="color:#cbd5e1;"></i>
                        <p class="text-slate-400">Nenhum cupom cadastrado ainda.</p>
                        <button id="btn-novo-cupom-2"
                            class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white text-sm"
                            style="background:linear-gradient(135deg,#1f5edb,#177fd6);">
                            <i class="fas fa-plus"></i> Criar primeiro cupom
                        </button>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($coupons as $coupon)
                            <div
                                class="flex flex-col md:flex-row items-start md:items-center gap-3 px-6 py-4 hover:bg-slate-50 transition-colors">
                                {{-- Status badge --}}
                                <div class="flex-shrink-0">
                                    @if(!$coupon->active)
                                        <span class="badge"
                                            style="background:#f1f5f9;color:#64748b;padding:0.3rem 0.7rem;border-radius:8px;font-size:0.7rem;font-weight:700;">INATIVO</span>
                                    @elseif($coupon->is_expired)
                                        <span class="badge"
                                            style="background:#fef2f2;color:#dc2626;padding:0.3rem 0.7rem;border-radius:8px;font-size:0.7rem;font-weight:700;">EXPIRADO</span>
                                    @else
                                        <span class="badge"
                                            style="background:#d1fae5;color:#065f46;padding:0.3rem 0.7rem;border-radius:8px;font-size:0.7rem;font-weight:700;">ATIVO</span>
                                    @endif
                                </div>
                                {{-- Info --}}
                                <div class="flex-grow">
                                    <div class="font-semibold text-slate-800">{{ $coupon->title }}</div>
                                    <div class="flex flex-wrap gap-2 mt-1 text-xs text-slate-500">
                                        <span><i class="fas fa-tag mr-1"></i><strong
                                                style="color:#1d4ed8;">{{ $coupon->code }}</strong></span>
                                        <span><i class="fas fa-percent mr-1"></i>{{ $coupon->formatted_discount }} OFF</span>
                                        @if($coupon->expires_at)
                                            <span><i class="fas fa-clock mr-1"></i>Até {{ $coupon->expires_at->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                                {{-- Ações --}}
                                <div class="flex gap-2 flex-shrink-0">
                                    <button class="btn-editar-cupom px-3 py-1.5 rounded-lg text-sm font-semibold text-white"
                                        style="background:linear-gradient(135deg,#1f5edb,#177fd6);"
                                        data-cupom="{{ json_encode($coupon) }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-remover-cupom px-3 py-1.5 rounded-lg text-sm font-semibold text-white"
                                        style="background:#ef4444;" data-id="{{ $coupon->id }}" data-titulo="{{ $coupon->title }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Cupom --}}
    <div id="modal-cupom" class="fixed inset-0 z-50 hidden" style="background:rgba(0,0,0,0.5);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="w-full max-w-md rounded-3xl shadow-2xl" style="background:#fff;">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 id="modal-titulo" class="font-bold text-slate-800 text-lg"></h3>
                    <button id="modal-fechar" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="form-cupom" class="px-6 py-5 space-y-4">
                    @csrf
                    <input type="hidden" id="cupom-id" name="_cupom_id">
                    <input type="hidden" id="cupom-method" name="_method" value="POST">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Título *</label>
                        <input id="f-title" name="title" type="text" required placeholder="Ex: 10% OFF em todos os produtos"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Código do cupom *</label>
                        <input id="f-code" name="code" type="text" required placeholder="Ex: UNN10OFF"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400 uppercase font-mono">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo *</label>
                            <select id="f-discount-type" name="discount_type"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400">
                                <option value="percent">Percentual (%)</option>
                                <option value="fixed">Valor fixo (R$)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Desconto *</label>
                            <input id="f-discount-value" name="discount_value" type="number" step="0.01" min="0.01" required
                                placeholder="10"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Descrição</label>
                        <textarea id="f-description" name="description" rows="2"
                            placeholder="Detalhes e condições do cupom..."
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400 resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Compra mínima (R$)</label>
                            <input id="f-min-purchase" name="min_purchase" type="number" step="0.01" min="0" placeholder="0"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Válido até</label>
                            <input id="f-expires-at" name="expires_at" type="date"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="f-active" name="active" type="checkbox" checked class="w-4 h-4 rounded">
                        <label for="f-active" class="text-sm font-semibold text-slate-700">Cupom ativo</label>
                    </div>
                    <button type="submit"
                        class="w-full py-3 rounded-xl font-bold text-white text-sm transition-all hover:scale-105"
                        style="background:linear-gradient(135deg,#1f5edb,#177fd6);box-shadow:0 4px 15px rgba(31,94,219,0.3);">
                        <i class="fas fa-save mr-1"></i> Salvar Cupom
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const ROUTES = {
                store: '{{ route("member.partner.coupons.store") }}',
                update: (id) => `{{ url("meu-parceiro/cupons") }}/${id}`,
                destroy: (id) => `{{ url("meu-parceiro/cupons") }}/${id}`,
            };
            const CSRF = '{{ csrf_token() }}';

            // Abrir modal
            function abrirModal(titulo, dados = null) {
                document.getElementById('modal-titulo').textContent = titulo;
                const form = document.getElementById('form-cupom');

                if (dados) {
                    document.getElementById('cupom-id').value = dados.id;
                    document.getElementById('cupom-method').value = 'PUT';
                    document.getElementById('f-title').value = dados.title || '';
                    document.getElementById('f-code').value = dados.code || '';
                    document.getElementById('f-discount-type').value = dados.discount_type || 'percent';
                    document.getElementById('f-discount-value').value = dados.discount_value || '';
                    document.getElementById('f-description').value = dados.description || '';
                    document.getElementById('f-min-purchase').value = dados.min_purchase || '';
                    document.getElementById('f-expires-at').value = dados.expires_at ? dados.expires_at.substr(0, 10) : '';
                    document.getElementById('f-active').checked = dados.active == 1;
                } else {
                    form.reset();
                    document.getElementById('cupom-id').value = '';
                    document.getElementById('cupom-method').value = 'POST';
                    document.getElementById('f-active').checked = true;
                }

                document.getElementById('modal-cupom').classList.remove('hidden');
            }

            document.getElementById('btn-novo-cupom')?.addEventListener('click', () => abrirModal('Novo Cupom'));
            document.getElementById('btn-novo-cupom-2')?.addEventListener('click', () => abrirModal('Novo Cupom'));
            document.getElementById('modal-fechar').addEventListener('click', () => document.getElementById('modal-cupom').classList.add('hidden'));
            document.getElementById('modal-cupom').addEventListener('click', e => {
                if (e.target === document.getElementById('modal-cupom')) document.getElementById('modal-cupom').classList.add('hidden');
            });

            // Editar
            document.querySelectorAll('.btn-editar-cupom').forEach(btn => {
                btn.addEventListener('click', function () {
                    abrirModal('Editar Cupom', JSON.parse(this.dataset.cupom));
                });
            });

            // Remover
            document.querySelectorAll('.btn-remover-cupom').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const titulo = this.dataset.titulo;
                    Swal.fire({
                        title: 'Remover cupom?',
                        html: `O cupom <strong>${titulo}</strong> será removido permanentemente.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Remover',
                        cancelButtonText: 'Cancelar',
                    }).then(result => {
                        if (!result.isConfirmed) return;
                        fetch(ROUTES.destroy(id), {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        }).then(r => r.json()).then(data => {
                            if (data.success) {
                                Swal.fire({
                                    toast: true, position: 'top-end', icon: 'success', title: data.message,
                                    showConfirmButton: false, timer: 2500
                                }).then(() => location.reload());
                            }
                        });
                    });
                });
            });

            // Submeter form
            document.getElementById('form-cupom').addEventListener('submit', function (e) {
                e.preventDefault();
                const id = document.getElementById('cupom-id').value;
                const url = id ? ROUTES.update(id) : ROUTES.store;

                const fd = new FormData(this);
                const obj = {
                    title: fd.get('title'),
                    code: fd.get('code'),
                    discount_type: fd.get('discount_type'),
                    discount_value: fd.get('discount_value'),
                    description: fd.get('description') || '',
                    min_purchase: fd.get('min_purchase') || '',
                    expires_at: fd.get('expires_at') || '',
                    active: document.getElementById('f-active').checked ? 1 : 0,
                    _method: id ? 'PUT' : 'POST',
                };

                fetch(url, {
                    method: 'POST', // Laravel aceita POST + _method override
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(obj),
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        document.getElementById('modal-cupom').classList.add('hidden');
                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'success', title: data.message,
                            showConfirmButton: false, timer: 2500
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Erro', text: data.message || 'Verifique os campos.' });
                    }
                }).catch(() => Swal.fire({ icon: 'error', title: 'Erro de rede', text: 'Tente novamente.' }));
            });        </script>
    @endpush
@endsection