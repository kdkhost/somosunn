@extends('panel.layouts.app')

@section('title', 'Áreas para Expositores')

@section('panel_content')
@php
    $money = fn ($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $badgeColor = match($status['badge'] ?? 'secondary') {
        'success' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200',
        'danger' => 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200',
        'warning' => 'bg-yellow-100 text-yellow-900 dark:bg-yellow-500/15 dark:text-yellow-100',
        'dark' => 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-100',
        default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    };
    $user = auth()->user();
    $canEditEvent = $user && ($user->isAdmin() || ($user->hasPermission('events.edit') && (int) $event->user_id === (int) $user->id));
    $backRoute = $canEditEvent ? route('panel.admin.events.edit', $event) : route('panel.admin.events.list');
@endphp

<div class="panel-theme-shell mx-auto max-w-7xl space-y-6 px-4 py-6"
    id="event-exhibitor-admin"
    data-registrations-url="{{ route($routePrefix . '.registrations', $event) }}"
    data-settings-url="{{ route($routePrefix . '.settings', $event) }}"
    data-toggle-url="{{ route($routePrefix . '.toggle', $event) }}"
    data-confirm-url="{{ route($routePrefix . '.registrations.confirm', ['event' => $event, 'registration' => '__ID__']) }}"
    data-cancel-url="{{ route($routePrefix . '.registrations.cancel', ['event' => $event, 'registration' => '__ID__']) }}"
    data-refund-url="{{ route($routePrefix . '.registrations.refund', ['event' => $event, 'registration' => '__ID__']) }}">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-950 dark:text-white">Áreas para Expositores</h1>
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $event->title }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $backRoute }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
            <a href="{{ route($routePrefix . '.export', $event) }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-black text-white hover:bg-emerald-700">
                <i class="fas fa-file-csv mr-1"></i> Exportar CSV
            </a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-900">
            <p class="text-xs font-black uppercase tracking-widest text-slate-500">Total de áreas</p>
            <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white" data-summary="total_slots">{{ (int) $summary['total_slots'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-900">
            <p class="text-xs font-black uppercase tracking-widest text-slate-500">Vendidas/reservadas</p>
            <p class="mt-2 text-3xl font-black text-emerald-700 dark:text-emerald-300" data-summary="sold_slots">{{ (int) $summary['sold_slots'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-900">
            <p class="text-xs font-black uppercase tracking-widest text-slate-500">Restantes</p>
            <p class="mt-2 text-3xl font-black text-yellow-700 dark:text-yellow-200" data-summary="remaining_slots">{{ (int) $summary['remaining_slots'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-900">
            <p class="text-xs font-black uppercase tracking-widest text-slate-500">Receita confirmada</p>
            <p class="mt-2 text-2xl font-black text-blue-700 dark:text-blue-300" data-summary="confirmed_revenue">{{ $money($summary['confirmed_revenue']) }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-5">
        <form id="exhibitor-settings-form" enctype="multipart/form-data" class="space-y-5 rounded-xl bg-white p-6 shadow-sm dark:bg-slate-900 xl:col-span-2">
            @csrf
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black text-slate-950 dark:text-white">Configuração</h2>
                    <span id="exhibitor-status-badge" class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-black {{ $badgeColor }}">{{ $status['label'] ?? 'Inativo' }}</span>
                </div>
                <button type="button" id="btn-toggle-exhibitor" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ $event->exhibitor_sales_enabled ? 'Desativar' : 'Ativar' }}
                </button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-xs font-black uppercase tracking-widest text-slate-500">Quantidade total</span>
                    <input type="number" min="0" name="exhibitor_total_slots" value="{{ old('exhibitor_total_slots', $event->exhibitor_total_slots) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                </label>
                <label class="space-y-2">
                    <span class="text-xs font-black uppercase tracking-widest text-slate-500">Quantidade vendida</span>
                    <input type="text" value="{{ (int) $summary['sold_slots'] }}" readonly class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-800">
                </label>
            </div>

            <div class="grid gap-3">
                <label class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="exhibitor_sales_enabled" value="1" {{ $event->exhibitor_sales_enabled ? 'checked' : '' }} class="h-5 w-5 rounded border-slate-300 text-blue-600">
                    Publicar venda de áreas
                </label>
                <label class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="exhibitor_includes_ticket" value="1" {{ $event->exhibitor_includes_ticket ? 'checked' : '' }} class="h-5 w-5 rounded border-slate-300 text-blue-600">
                    Expositor recebe ingresso incluso
                </label>
                <label class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="exhibitor_show_publicly" value="1" {{ ($event->exhibitor_show_publicly ?? true) ? 'checked' : '' }} class="h-5 w-5 rounded border-slate-300 text-blue-600">
                    Exibir publicamente
                </label>
            </div>

            <label class="space-y-2 block">
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Descrição pública</span>
                <textarea name="exhibitor_description" rows="4" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">{{ old('exhibitor_description', $event->exhibitor_description) }}</textarea>
            </label>
            <label class="space-y-2 block">
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Observações internas</span>
                <textarea name="exhibitor_internal_notes" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">{{ old('exhibitor_internal_notes', $event->exhibitor_internal_notes) }}</textarea>
            </label>

            <div class="space-y-2">
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Imagem, planta ou mapa</span>
                <input type="file" name="exhibitor_area_image" accept="image/*" class="filepond" data-max-file-size="10MB">
                @if($event->exhibitor_area_image_url)
                    <a href="{{ $event->exhibitor_area_image_url }}" target="_blank" class="inline-flex text-sm font-bold text-blue-700 dark:text-blue-300">Ver imagem atual</a>
                    <label class="ml-3 inline-flex items-center gap-2 text-sm font-bold text-slate-600 dark:text-slate-300">
                        <input type="checkbox" name="remove_exhibitor_area_image" value="1"> Remover atual
                    </label>
                @endif
            </div>

            <div class="space-y-3">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">Lotes</h3>
                @for($i = 1; $i <= 3; $i++)
                    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        <p class="mb-3 font-black text-slate-900 dark:text-white">{{ $i }}º lote</p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <input type="text" name="exhibitor_batch_{{ $i }}_price" placeholder="Preço" value="{{ old('exhibitor_batch_' . $i . '_price', $event->{'exhibitor_batch_' . $i . '_price'}) }}" class="js-money rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <input type="datetime-local" name="exhibitor_batch_{{ $i }}_deadline" value="{{ optional($event->{'exhibitor_batch_' . $i . '_deadline'})->format('Y-m-d\TH:i') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <input type="number" min="0" name="exhibitor_batch_{{ $i }}_slots" placeholder="Limite" value="{{ old('exhibitor_batch_' . $i . '_slots', $event->{'exhibitor_batch_' . $i . '_slots'}) }}" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        </div>
                    </div>
                @endfor
            </div>

            <button type="submit" class="w-full rounded-xl bg-blue-600 px-5 py-3 font-black text-white hover:bg-blue-700">
                <i class="fas fa-save mr-1"></i> Salvar configurações
            </button>
        </form>

        <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-slate-900 xl:col-span-3">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-end">
                <label class="flex-1 space-y-2">
                    <span class="text-xs font-black uppercase tracking-widest text-slate-500">Busca</span>
                    <input type="text" id="filter-search" placeholder="Nome, marca ou e-mail" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                </label>
                <select id="filter-status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Todos os status</option>
                    <option value="reserved">Reservado</option>
                    <option value="paid">Pago</option>
                    <option value="confirmed">Confirmado</option>
                    <option value="cancelled">Cancelado</option>
                    <option value="refunded">Reembolsado</option>
                    <option value="expired">Expirado</option>
                </select>
                <select id="filter-payment-status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Pagamento</option>
                    <option value="pending">Pendente</option>
                    <option value="paid">Pago</option>
                    <option value="cancelled">Cancelado</option>
                    <option value="refunded">Reembolsado</option>
                    <option value="expired">Expirado</option>
                </select>
                <button id="btn-filter" type="button" class="rounded-xl border border-blue-200 px-4 py-3 font-black text-blue-700 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-300">
                    <i class="fas fa-filter"></i>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="text-left text-xs font-black uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="py-3 pr-4">Expositor</th>
                            <th class="py-3 pr-4">Contato</th>
                            <th class="py-3 pr-4">Lote</th>
                            <th class="py-3 pr-4">Qtde.</th>
                            <th class="py-3 pr-4">Total</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="exhibitor-registrations-body" class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($registrations as $registration)
                            <tr>
                                <td class="py-3 pr-4">
                                    <strong class="text-slate-950 dark:text-white">{{ $registration->brand_name ?: $registration->company_name }}</strong>
                                    <span class="block text-xs text-slate-500">{{ $registration->name }}</span>
                                </td>
                                <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ $registration->email }}<span class="block text-xs text-slate-500">{{ $registration->phone }}</span></td>
                                <td class="py-3 pr-4">{{ $registration->batch_label }}</td>
                                <td class="py-3 pr-4">{{ (int) $registration->quantity }}</td>
                                <td class="py-3 pr-4">{{ $money($registration->total_price) }}</td>
                                <td class="py-3 pr-4"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-black text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $registration->status }}</span><span class="block text-xs text-slate-500">{{ $registration->payment_status }}</span></td>
                                <td class="py-3 text-right">
                                    <button class="js-registration-action rounded-lg border border-emerald-200 px-2 py-1 text-emerald-700" data-action="confirm" data-id="{{ $registration->id }}"><i class="fas fa-check"></i></button>
                                    <button class="js-registration-action rounded-lg border border-yellow-200 px-2 py-1 text-yellow-700" data-action="refund" data-id="{{ $registration->id }}"><i class="fas fa-undo"></i></button>
                                    <button class="js-registration-action rounded-lg border border-red-200 px-2 py-1 text-red-700" data-action="cancel" data-id="{{ $registration->id }}"><i class="fas fa-ban"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-8 text-center text-slate-500">Nenhuma inscrição de expositor registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $registrations->links() }}</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const root = document.getElementById('event-exhibitor-admin');
    if (!root) return;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const money = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    function notify(type, message) {
        if (window.toastr && toastr[type]) toastr[type](message);
        else if (window.showSuccess && type === 'success') showSuccess(message);
        else if (window.showError && type === 'error') showError(message);
        else alert(message);
    }
    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
    }
    function actionUrl(template, id) {
        return template.replace('__ID__', id);
    }
    function moneyMask(value) {
        const digits = String(value || '').replace(/\D/g, '');
        return (Number(digits || 0) / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    document.querySelectorAll('.js-money').forEach(input => input.addEventListener('input', function () { this.value = moneyMask(this.value); }));

    document.getElementById('exhibitor-settings-form')?.addEventListener('submit', function (event) {
        event.preventDefault();
        const button = event.currentTarget.querySelector('button[type="submit"]');
        button.disabled = true;
        fetch(root.dataset.settingsUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: new FormData(event.currentTarget)
        }).then(response => response.json().then(json => ({ ok: response.ok, json })))
            .then(({ ok, json }) => {
                if (!ok || json.success === false) throw new Error(json.message || 'Falha ao salvar configurações.');
                notify('success', json.message || 'Configurações salvas.');
                refreshRegistrations();
            }).catch(error => notify('error', error.message))
            .finally(() => button.disabled = false);
    });

    document.getElementById('btn-toggle-exhibitor')?.addEventListener('click', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Alterar venda de expositor?',
            text: 'A disponibilidade pública será atualizada.',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch(root.dataset.toggleUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                .then(response => response.json().then(json => ({ ok: response.ok, json })))
                .then(({ ok, json }) => {
                    if (!ok || json.success === false) throw new Error(json.message || 'Falha ao alterar status.');
                    notify('success', json.message || 'Status alterado.');
                    setTimeout(() => location.reload(), 600);
                }).catch(error => notify('error', error.message));
        });
    });

    function renderRows(rows) {
        const body = document.getElementById('exhibitor-registrations-body');
        if (!body) return;
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-slate-500">Nenhuma inscrição de expositor registrada.</td></tr>';
            return;
        }
        body.innerHTML = rows.map(row => `
            <tr>
                <td class="py-3 pr-4"><strong class="text-slate-950 dark:text-white">${escapeHtml(row.brand_name || row.company_name || '-')}</strong><span class="block text-xs text-slate-500">${escapeHtml(row.name || '')}</span></td>
                <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">${escapeHtml(row.email || '')}<span class="block text-xs text-slate-500">${escapeHtml(row.phone || '')}</span></td>
                <td class="py-3 pr-4">${escapeHtml(row.batch_label || '')}</td>
                <td class="py-3 pr-4">${row.quantity}</td>
                <td class="py-3 pr-4">${money.format(Number(row.total_price || 0))}</td>
                <td class="py-3 pr-4"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-black text-slate-700 dark:bg-slate-800 dark:text-slate-200">${escapeHtml(row.status || '')}</span><span class="block text-xs text-slate-500">${escapeHtml(row.payment_status || '')}</span></td>
                <td class="py-3 text-right">
                    <button class="js-registration-action rounded-lg border border-emerald-200 px-2 py-1 text-emerald-700" data-action="confirm" data-id="${row.id}"><i class="fas fa-check"></i></button>
                    <button class="js-registration-action rounded-lg border border-yellow-200 px-2 py-1 text-yellow-700" data-action="refund" data-id="${row.id}"><i class="fas fa-undo"></i></button>
                    <button class="js-registration-action rounded-lg border border-red-200 px-2 py-1 text-red-700" data-action="cancel" data-id="${row.id}"><i class="fas fa-ban"></i></button>
                </td>
            </tr>
        `).join('');
    }

    function refreshRegistrations() {
        const url = new URL(root.dataset.registrationsUrl, window.location.origin);
        const status = document.getElementById('filter-status')?.value || '';
        const paymentStatus = document.getElementById('filter-payment-status')?.value || '';
        const search = document.getElementById('filter-search')?.value || '';
        if (status) url.searchParams.set('status', status);
        if (paymentStatus) url.searchParams.set('payment_status', paymentStatus);
        if (search) url.searchParams.set('search', search);
        return fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(response => response.json())
            .then(json => {
                renderRows(json.data?.rows || []);
                const summary = json.data?.summary || {};
                Object.keys(summary).forEach(key => {
                    const el = document.querySelector(`[data-summary="${key}"]`);
                    if (el) el.textContent = key.includes('revenue') ? money.format(Number(summary[key] || 0)) : summary[key];
                });
            });
    }
    document.getElementById('btn-filter')?.addEventListener('click', refreshRegistrations);

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.js-registration-action');
        if (!button) return;
        const id = button.dataset.id;
        const action = button.dataset.action;
        const config = {
            confirm: ['Confirmar inscrição?', 'A confirmação manual marca a reserva como paga.', root.dataset.confirmUrl, 'Confirmar'],
            cancel: ['Cancelar inscrição?', 'A área reservada será liberada.', root.dataset.cancelUrl, 'Cancelar inscrição'],
            refund: ['Reembolsar inscrição?', 'Informe um valor para reembolso parcial ou deixe vazio para integral.', root.dataset.refundUrl, 'Reembolsar']
        }[action];
        Swal.fire({
            icon: action === 'cancel' ? 'warning' : 'question',
            title: config[0],
            text: config[1],
            input: action === 'refund' ? 'text' : 'textarea',
            inputPlaceholder: action === 'refund' ? 'Valor opcional. Ex: 120,00' : 'Motivo opcional',
            showCancelButton: true,
            confirmButtonText: config[3],
            cancelButtonText: 'Fechar'
        }).then(result => {
            if (!result.isConfirmed) return;
            const payload = action === 'refund' ? { amount: result.value || null } : { reason: result.value || null };
            fetch(actionUrl(config[2], id), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(response => response.json().then(json => ({ ok: response.ok, json })))
                .then(({ ok, json }) => {
                    if (!ok || json.success === false) throw new Error(json.message || 'Falha ao processar ação.');
                    notify('success', json.message || 'Ação concluída.');
                    refreshRegistrations();
                }).catch(error => notify('error', error.message));
        });
    });
})();
</script>
@endpush
