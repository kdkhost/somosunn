@extends('admin.layouts.app')

@section('title', 'Áreas para Expositores')

@section('content')
@php
    $statusBadge = 'badge-' . ($status['badge'] ?? 'secondary');
    $money = fn ($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
@endphp

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-8">
                <h1 class="m-0">Áreas para Expositores</h1>
                <p class="text-muted mb-0">{{ $event->title }}</p>
            </div>
            <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar ao evento
                </a>
                <a href="{{ route($routePrefix . '.export', $event) }}" class="btn btn-outline-success" id="btn-export-exhibitors">
                    <i class="fas fa-file-csv mr-1"></i> Exportar CSV
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid" id="event-exhibitor-admin"
        data-registrations-url="{{ route($routePrefix . '.registrations', $event) }}"
        data-settings-url="{{ route($routePrefix . '.settings', $event) }}"
        data-toggle-url="{{ route($routePrefix . '.toggle', $event) }}"
        data-confirm-url="{{ route($routePrefix . '.registrations.confirm', ['event' => $event, 'registration' => '__ID__']) }}"
        data-cancel-url="{{ route($routePrefix . '.registrations.cancel', ['event' => $event, 'registration' => '__ID__']) }}"
        data-refund-url="{{ route($routePrefix . '.registrations.refund', ['event' => $event, 'registration' => '__ID__']) }}">

        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 data-summary="total_slots">{{ (int) $summary['total_slots'] }}</h3>
                        <p>Total de áreas</p>
                    </div>
                    <div class="icon"><i class="fas fa-border-all"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 data-summary="sold_slots">{{ (int) $summary['sold_slots'] }}</h3>
                        <p>Vendidas/reservadas</p>
                    </div>
                    <div class="icon"><i class="fas fa-store"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 data-summary="remaining_slots">{{ (int) $summary['remaining_slots'] }}</h3>
                        <p>Restantes</p>
                    </div>
                    <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3 class="h4" data-summary="confirmed_revenue">{{ $money($summary['confirmed_revenue']) }}</h3>
                        <p>Receita confirmada</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <div class="card card-outline card-primary">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0"><i class="fas fa-sliders-h mr-1"></i> Configuração</h3>
                        <span class="badge {{ $statusBadge }}" id="exhibitor-status-badge">{{ $status['label'] ?? 'Inativo' }}</span>
                    </div>
                    <form id="exhibitor-settings-form" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-3">
                                <div>
                                    <strong>Venda de expositor</strong>
                                    <small class="d-block text-muted">Ative ou desative a oferta pública.</small>
                                </div>
                                <button type="button" class="btn btn-sm {{ $event->exhibitor_sales_enabled ? 'btn-outline-danger' : 'btn-outline-success' }}" id="btn-toggle-exhibitor">
                                    <i class="fas {{ $event->exhibitor_sales_enabled ? 'fa-toggle-off' : 'fa-toggle-on' }} mr-1"></i>
                                    {{ $event->exhibitor_sales_enabled ? 'Desativar' : 'Ativar' }}
                                </button>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Quantidade total de áreas</label>
                                    <input type="number" min="0" name="exhibitor_total_slots" class="form-control" value="{{ old('exhibitor_total_slots', $event->exhibitor_total_slots) }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Quantidade já vendida</label>
                                    <input type="text" class="form-control" value="{{ (int) $summary['sold_slots'] }}" readonly>
                                </div>
                            </div>

                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="exhibitor_sales_enabled" name="exhibitor_sales_enabled" value="1" {{ $event->exhibitor_sales_enabled ? 'checked' : '' }}>
                                <label class="custom-control-label" for="exhibitor_sales_enabled">Publicar venda de áreas</label>
                            </div>
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="exhibitor_includes_ticket" name="exhibitor_includes_ticket" value="1" {{ $event->exhibitor_includes_ticket ? 'checked' : '' }}>
                                <label class="custom-control-label" for="exhibitor_includes_ticket">Expositor recebe ingresso incluso</label>
                            </div>
                            <div class="custom-control custom-switch mb-3">
                                <input type="hidden" name="exhibitor_show_publicly" value="0">
                                <input type="checkbox" class="custom-control-input" id="exhibitor_show_publicly" name="exhibitor_show_publicly" value="1" {{ ($event->exhibitor_show_publicly ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="exhibitor_show_publicly">Exibir publicamente</label>
                            </div>

                            <div class="form-group">
                                <label>Descrição pública da área</label>
                                <textarea name="exhibitor_description" rows="4" class="form-control">{{ old('exhibitor_description', $event->exhibitor_description) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Observações internas administrativas</label>
                                <textarea name="exhibitor_internal_notes" rows="3" class="form-control">{{ old('exhibitor_internal_notes', $event->exhibitor_internal_notes) }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>Imagem, planta ou mapa</label>
                                <x-unn-dropzone name="exhibitor_area_image" accept="image/*" label="Arraste e solte a planta ou mapa" :current-url="$event->exhibitor_area_image_url" :is-image="true" theme="admin-lte" :max-size-mb="10" />
                                @if($event->exhibitor_area_image)
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="remove_exhibitor_area_image" name="remove_exhibitor_area_image" value="1">
                                        <label class="custom-control-label" for="remove_exhibitor_area_image">Remover imagem atual</label>
                                    </div>
                                @endif
                            </div>

                            <hr>
                            <h5 class="font-weight-bold mb-3">Lotes para expositor</h5>
                            @for($i = 1; $i <= 3; $i++)
                                <div class="border rounded p-3 mb-3">
                                    <strong>{{ $i }}º lote</strong>
                                    <div class="form-row mt-2">
                                        <div class="form-group col-md-4">
                                            <label>Preço</label>
                                            <input type="text" name="exhibitor_batch_{{ $i }}_price" class="form-control js-money" value="{{ old('exhibitor_batch_' . $i . '_price', $event->{'exhibitor_batch_' . $i . '_price'}) }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Data limite</label>
                                            <input type="datetime-local" name="exhibitor_batch_{{ $i }}_deadline" class="form-control" value="{{ optional($event->{'exhibitor_batch_' . $i . '_deadline'})->format('Y-m-d\TH:i') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Quantidade limite</label>
                                            <input type="number" min="0" name="exhibitor_batch_{{ $i }}_slots" class="form-control" value="{{ old('exhibitor_batch_' . $i . '_slots', $event->{'exhibitor_batch_' . $i . '_slots'}) }}">
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Salvar configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Inscrições de expositores</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row mb-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="filter-search" placeholder="Buscar por nome, marca ou e-mail">
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="filter-status">
                                    <option value="">Todos os status</option>
                                    <option value="reserved">Reservado</option>
                                    <option value="paid">Pago</option>
                                    <option value="confirmed">Confirmado</option>
                                    <option value="cancelled">Cancelado</option>
                                    <option value="refunded">Reembolsado</option>
                                    <option value="expired">Expirado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="filter-payment-status">
                                    <option value="">Pagamento</option>
                                    <option value="pending">Pendente</option>
                                    <option value="paid">Pago</option>
                                    <option value="cancelled">Cancelado</option>
                                    <option value="refunded">Reembolsado</option>
                                    <option value="expired">Expirado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-primary btn-block" id="btn-filter">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Expositor</th>
                                        <th>Contato</th>
                                        <th>Lote</th>
                                        <th>Qtde.</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th class="text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="exhibitor-registrations-body">
                                    @forelse($registrations as $registration)
                                        <tr>
                                            <td>
                                                <strong>{{ $registration->brand_name ?: $registration->company_name }}</strong>
                                                <small class="d-block text-muted">{{ $registration->name }}</small>
                                            </td>
                                            <td>
                                                {{ $registration->email }}
                                                <small class="d-block text-muted">{{ $registration->phone }}</small>
                                            </td>
                                            <td>{{ $registration->batch_label }}</td>
                                            <td>{{ (int) $registration->quantity }}</td>
                                            <td>{{ $money($registration->total_price) }}</td>
                                            <td>
                                                <span class="badge badge-secondary">{{ $registration->status }}</span>
                                                <small class="d-block text-muted">{{ $registration->payment_status }}</small>
                                            </td>
                                            <td class="text-right">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-success js-registration-action" data-action="confirm" data-id="{{ $registration->id }}" title="Confirmar manualmente"><i class="fas fa-check"></i></button>
                                                    <button class="btn btn-outline-warning js-registration-action" data-action="refund" data-id="{{ $registration->id }}" title="Reembolsar"><i class="fas fa-undo"></i></button>
                                                    <button class="btn btn-outline-danger js-registration-action" data-action="cancel" data-id="{{ $registration->id }}" title="Cancelar"><i class="fas fa-ban"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma inscrição de expositor registrada.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $registrations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const root = document.getElementById('event-exhibitor-admin');
    if (!root) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const money = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    function toast(type, message) {
        if (window.toastr && toastr[type]) {
            toastr[type](message);
        } else {
            alert(message);
        }
    }

    function actionUrl(template, id) {
        return template.replace('__ID__', id);
    }

    function moneyMask(value) {
        const digits = String(value || '').replace(/\D/g, '');
        const cents = Number(digits || 0) / 100;
        return cents.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    document.querySelectorAll('.js-money').forEach(function (input) {
        input.addEventListener('input', function () {
            this.value = moneyMask(this.value);
        });
    });

    document.getElementById('exhibitor-settings-form')?.addEventListener('submit', function (event) {
        event.preventDefault();
        const form = event.currentTarget;
        const button = form.querySelector('button[type="submit"]');
        const data = new FormData(form);
        button.disabled = true;

        fetch(root.dataset.settingsUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: data
        }).then(response => response.json().then(json => ({ ok: response.ok, json })))
            .then(({ ok, json }) => {
                if (!ok || json.success === false) throw new Error(json.message || 'Falha ao salvar configurações.');
                toast('success', json.message || 'Configurações salvas.');
                if (json.data?.status?.label) {
                    document.getElementById('exhibitor-status-badge').textContent = json.data.status.label;
                }
                refreshRegistrations();
            })
            .catch(error => toast('error', error.message))
            .finally(() => { button.disabled = false; });
    });

    document.getElementById('btn-toggle-exhibitor')?.addEventListener('click', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Alterar venda de expositor?',
            text: 'Esta ação muda a disponibilidade pública da oferta.',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch(root.dataset.toggleUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            }).then(response => response.json().then(json => ({ ok: response.ok, json })))
                .then(({ ok, json }) => {
                    if (!ok || json.success === false) throw new Error(json.message || 'Falha ao alterar status.');
                    toast('success', json.message);
                    setTimeout(() => window.location.reload(), 700);
                })
                .catch(error => toast('error', error.message));
        });
    });

    function renderRows(rows) {
        const body = document.getElementById('exhibitor-registrations-body');
        if (!body) return;
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Nenhuma inscrição de expositor registrada.</td></tr>';
            return;
        }
        body.innerHTML = rows.map(row => `
            <tr>
                <td><strong>${escapeHtml(row.brand_name || row.company_name || '-')}</strong><small class="d-block text-muted">${escapeHtml(row.name || '')}</small></td>
                <td>${escapeHtml(row.email || '')}<small class="d-block text-muted">${escapeHtml(row.phone || '')}</small></td>
                <td>${escapeHtml(row.batch_label || '')}</td>
                <td>${row.quantity}</td>
                <td>${money.format(Number(row.total_price || 0))}</td>
                <td><span class="badge badge-secondary">${escapeHtml(row.status || '')}</span><small class="d-block text-muted">${escapeHtml(row.payment_status || '')}</small></td>
                <td class="text-right">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-success js-registration-action" data-action="confirm" data-id="${row.id}" title="Confirmar manualmente"><i class="fas fa-check"></i></button>
                        <button class="btn btn-outline-warning js-registration-action" data-action="refund" data-id="${row.id}" title="Reembolsar"><i class="fas fa-undo"></i></button>
                        <button class="btn btn-outline-danger js-registration-action" data-action="cancel" data-id="${row.id}" title="Cancelar"><i class="fas fa-ban"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[char]));
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
                    if (!el) return;
                    el.textContent = String(key.includes('revenue') ? money.format(Number(summary[key] || 0)) : summary[key]);
                });
            });
    }

    document.getElementById('btn-filter')?.addEventListener('click', refreshRegistrations);
    document.getElementById('filter-search')?.addEventListener('keydown', event => {
        if (event.key === 'Enter') refreshRegistrations();
    });

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
            const payload = action === 'refund'
                ? { amount: result.value || null }
                : { reason: result.value || null };

            fetch(actionUrl(config[2], id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            }).then(response => response.json().then(json => ({ ok: response.ok, json })))
                .then(({ ok, json }) => {
                    if (!ok || json.success === false) throw new Error(json.message || 'Falha ao processar ação.');
                    toast('success', json.message || 'Ação concluída.');
                    refreshRegistrations();
                })
                .catch(error => toast('error', error.message));
        });
    });
})();
</script>
@endpush
