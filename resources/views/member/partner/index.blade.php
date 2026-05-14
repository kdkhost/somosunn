@extends('panel.layouts.app')

@section('title', 'Meu Painel de Parceiro')

@section('panel_breadcrumb')
    <span class="text-slate-500 dark:text-slate-400">Meu Parceiro</span>
@endsection

@section('panel_content')
    @php
        $total = $coupons->count();
        $ativos = $coupons->where('active', true)->count();
        $expirados = $coupons->filter(fn($c) => $c->is_expired)->count();
        $inativos = $total - $ativos;
    @endphp

    <div class="space-y-6">

        {{-- HERO CARD --}}
        <div class="relative overflow-hidden rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 bg-gradient-to-br from-blue-600 via-blue-700 to-slate-900">
            <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-16 -left-16 w-64 h-64 rounded-full bg-cyan-300/10 blur-3xl"></div>

            <div class="relative p-6 md:p-8">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-5">
                    {{-- Logo --}}
                    <div class="flex h-20 w-20 md:h-24 md:w-24 items-center justify-center rounded-2xl bg-white shadow-xl border border-white/20 shrink-0">
                        @if($partner->logo_url)
                            <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                                 class="max-h-16 max-w-16 md:max-h-20 md:max-w-20 object-contain">
                        @else
                            <i class="fas fa-building text-slate-400 text-3xl"></i>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0 text-center md:text-left">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mb-2 bg-white/15 text-blue-100 border border-white/20">
                            <i class="fas fa-handshake"></i> Parceiro UNN
                        </div>
                        <h1 class="text-2xl md:text-3xl font-black text-white">{{ $partner->name }}</h1>
                        @if($partner->description)
                            <p class="mt-2 text-sm text-blue-100/90 max-w-2xl">{{ $partner->description }}</p>
                        @endif
                    </div>

                    {{-- CTA --}}
                    <div class="md:ml-auto flex-shrink-0 w-full md:w-auto">
                        <button id="btn-novo-cupom"
                                class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-sm font-black text-white bg-emerald-500 hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all">
                            <i class="fas fa-plus-circle"></i>
                            <span>Novo Cupom</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-2xl p-5 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Total</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $total }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-5 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Ativos</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $ativos }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-5 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Inativos</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $inativos }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-5 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Expirados</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $expirados }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LISTA DE CUPONS --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div>
                        <h2 class="font-black text-slate-900 dark:text-white">Meus Cupons</h2>
                        <p class="text-xs text-slate-400">Gerencie os cupons exclusivos para membros</p>
                    </div>
                </div>
            </div>

            @if($coupons->isEmpty())
                <div class="text-center py-16 px-6">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                        <i class="fas fa-ticket-alt text-2xl text-slate-300 dark:text-slate-600"></i>
                    </div>
                    <h3 class="font-black text-slate-700 dark:text-slate-200 mb-1">Nenhum cupom cadastrado</h3>
                    <p class="text-sm text-slate-500 mb-5 max-w-md mx-auto">
                        Crie seu primeiro cupom para que os membros da plataforma possam aproveitar descontos exclusivos.
                    </p>
                    <button id="btn-novo-cupom-2"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-black text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all">
                        <i class="fas fa-plus"></i>
                        <span>Criar primeiro cupom</span>
                    </button>
                </div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($coupons as $coupon)
                        <div class="flex flex-col md:flex-row items-start md:items-center gap-4 px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                            {{-- Status badge --}}
                            <div class="flex-shrink-0">
                                @if(!$coupon->active)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Inativo
                                    </span>
                                @elseif($coupon->is_expired)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Expirado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Ativo
                                    </span>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex-grow min-w-0">
                                <div class="font-black text-slate-900 dark:text-white">{{ $coupon->title }}</div>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fas fa-tag text-blue-500"></i>
                                        <strong class="text-blue-700 dark:text-blue-400 font-mono">{{ $coupon->code }}</strong>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fas fa-percent text-emerald-500"></i>
                                        {{ $coupon->formatted_discount }} OFF
                                    </span>
                                    @if($coupon->expires_at)
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fas fa-clock text-amber-500"></i>
                                            Até {{ $coupon->expires_at->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Ações --}}
                            <div class="flex gap-2 flex-shrink-0">
                                <button class="btn-editar-cupom inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-all"
                                        data-cupom="{{ json_encode($coupon) }}"
                                        title="Editar Cupom">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-remover-cupom inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-bold text-white bg-rose-500 hover:bg-rose-600 transition-all"
                                        data-id="{{ $coupon->id }}"
                                        data-titulo="{{ $coupon->title }}"
                                        title="Excluir Cupom">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL CUPOM --}}
    <div id="modal-cupom" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="w-full max-w-md rounded-3xl shadow-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 id="modal-titulo" class="font-black text-slate-900 dark:text-white">Novo Cupom</h3>
                    <button id="modal-fechar" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="form-cupom" class="px-6 py-5 space-y-4">
                    @csrf
                    <input type="hidden" id="cupom-id" name="_cupom_id">
                    <input type="hidden" id="cupom-method" name="_method" value="POST">

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1.5">Título *</label>
                        <input id="f-title" name="title" type="text" required
                               placeholder="Ex: 10% OFF em todos os produtos"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1.5">Código do cupom *</label>
                        <input id="f-code" name="code" type="text" required
                               placeholder="Ex: UNN10OFF"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 uppercase font-mono focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1.5">Tipo *</label>
                            <select id="f-discount-type" name="discount_type"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                                <option value="percent">Percentual (%)</option>
                                <option value="fixed">Valor fixo (R$)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1.5">Desconto *</label>
                            <input id="f-discount-value" name="discount_value" type="number" step="0.01" min="0.01" required
                                   placeholder="10"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1.5">Descrição</label>
                        <textarea id="f-description" name="description" rows="2"
                                  placeholder="Detalhes e condições do cupom..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 resize-none focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1.5">Compra mínima (R$)</label>
                            <input id="f-min-purchase" name="min_purchase" type="number" step="0.01" min="0"
                                   placeholder="0"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1.5">Válido até</label>
                            <input id="f-expires-at" name="expires_at" type="date"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                        </div>
                    </div>

                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 cursor-pointer">
                        <input id="f-active" name="active" type="checkbox" checked
                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Cupom ativo</span>
                    </label>

                    <button type="submit"
                            class="w-full py-3 rounded-xl text-sm font-black text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all">
                        <i class="fas fa-save mr-1"></i>
                        Salvar Cupom
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

            document.querySelectorAll('.btn-editar-cupom').forEach(btn => {
                btn.addEventListener('click', function () {
                    abrirModal('Editar Cupom', JSON.parse(this.dataset.cupom));
                });
            });

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
                                if (typeof toastr !== 'undefined') toastr.success(data.message);
                                setTimeout(() => location.reload(), 1500);
                            }
                        });
                    });
                });
            });

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
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(obj),
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        document.getElementById('modal-cupom').classList.add('hidden');
                        if (typeof toastr !== 'undefined') toastr.success(data.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(data.message || 'Verifique os campos.');
                    }
                }).catch(() => {
                    if (typeof toastr !== 'undefined') toastr.error('Tente novamente.');
                });
            });
        </script>
    @endpush
@endsection
