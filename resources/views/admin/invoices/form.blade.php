@extends('admin.layouts.app')

@section('page_title', ($invoice->id ? 'Editar' : 'Nova') . ' fatura')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}" data-pjax>Faturas</a></li>
    <li class="breadcrumb-item active">{{ $invoice->id ? 'Editar' : 'Nova' }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" id="invoiceFormAdmin"
            action="{{ $invoice->id ? route('admin.invoices.update', $invoice) : route('admin.invoices.store') }}">
            @csrf
            @if($invoice->id) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Cliente</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">Selecione...</option>
                        @foreach(($users ?? collect()) as $u)
                            <option value="{{ $u->id }}" {{ (string) old('user_id', $invoice->user_id) === (string) $u->id ? 'selected' : '' }}>
                                #{{ $u->id }} — {{ $u->name }} ({{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Se o usuário não estiver na lista, abra a tela de usuários e crie/edite o
                        cadastro primeiro.</small>
                </div>

                <div class="form-group col-md-3">
                    <label>Status</label>
                    @php($status = old('status', $invoice->status ?: 'issued'))
                    <select name="status" class="form-control" required>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Rascunho</option>
                        <option value="issued" {{ $status === 'issued' ? 'selected' : '' }}>Emitida</option>
                        <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paga</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label>Moeda</label>
                    <input name="currency" class="form-control"
                        value="{{ old('currency', $invoice->currency ?: 'BRL') }}" placeholder="BRL">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Emissão</label>
                    <input type="hidden" name="issued_at" id="issued_at"
                        value="{{ old('issued_at', optional($invoice->issued_at)->format('Y-m-d H:i')) }}">
                    <input class="form-control js-datetime-br" id="issued_at_br"
                        value="" placeholder="DD/MM/AAAA HH:MM" autocomplete="off">
                </div>
                <div class="form-group col-md-3">
                    <label>Vencimento (opcional)</label>
                    <input type="hidden" name="due_at" id="due_at"
                        value="{{ old('due_at', optional($invoice->due_at)->format('Y-m-d H:i')) }}">
                    <input class="form-control js-datetime-br" id="due_at_br"
                        value="" placeholder="DD/MM/AAAA HH:MM" autocomplete="off">
                </div>
                <div class="form-group col-md-3">
                    <label>Desconto (opcional)</label>
                    <input name="discount_amount" class="form-control mask-money"
                        value="{{ old('discount_amount', $invoice->discount_amount) }}" placeholder="R$ 0,00">
                </div>
                <div class="form-group col-md-3 d-flex align-items-end">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                        <input type="hidden" name="send_email" value="0">
                        <input type="checkbox" class="custom-control-input" id="send_email" name="send_email" value="1"
                            {{ old('send_email') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="send_email">Enviar por e-mail ao salvar</label>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="m-0">Itens</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddItem">
                    <i class="fas fa-plus mr-1"></i> Adicionar item
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th style="width:90px;">Qtd</th>
                            <th style="width:180px;">Valor</th>
                            <th style="width:70px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- $rows is now passed from the InvoiceController --}}

                        @foreach($rows as $r)
                            <tr>
                                <td>
                                    <input name="items_description[]" class="form-control" value="{{ $r['description'] }}"
                                        required>
                                </td>
                                <td>
                                    <input name="items_quantity[]" class="form-control" type="number" min="1"
                                        value="{{ $r['quantity'] ?? 1 }}">
                                </td>
                                <td>
                                    <input name="items_unit_price[]" class="form-control mask-money"
                                        value="{{ $r['unit_price'] }}" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btnRemoveItem"
                                        title="Remover">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <label>Observações (opcional)</label>
            <textarea name="notes" class="form-control" rows="4"
                maxlength="5000">{{ old('notes', $invoice->notes) }}</textarea>
    </div>

    <hr>

    <div class="d-flex justify-content-between">
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary" data-pjax>Cancelar</a>

        <div class="btn-group">
            <button type="submit" name="send_email_type" value="none" class="btn btn-primary">
                <i class="fas fa-save"></i> Salvar (Sem enviar)
            </button>
            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <button type="submit" name="send_email_type" value="queue" class="dropdown-item">
                    <i class="fas fa-clock"></i> Salvar e Enviar na Fila (Cron)
                </button>
                <button type="submit" name="send_email_type" value="now" class="dropdown-item text-bold">
                    <i class="fas fa-paper-plane"></i> Salvar e Enviar AGORA
                </button>
            </div>
        </div>
    </div>
    </form>
</div>
</div>
@endsection

@push('scripts')
    <script>
        (function () {
            function showUiError(message) {
                if (typeof window.showError === 'function') {
                    window.showError(message);
                    return;
                }
                if (window.toastr && typeof window.toastr.error === 'function') {
                    window.toastr.error(message);
                    return;
                }
                if (typeof window.Swal !== 'undefined') {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: message
                    });
                    return;
                }
                console.error(message);
            }

            function applyMoneyMask(scope) {
                try {
                    if (!(window.jQuery && jQuery.fn && jQuery.fn.inputmask)) {
                        return;
                    }
                    jQuery(scope || document).find('.mask-money').inputmask('currency', {
                        prefix: 'R$ ',
                        radixPoint: ',',
                        groupSeparator: '.',
                        autoGroup: true,
                        digits: 2,
                        rightAlign: false,
                        substituteRadixPoint: true,
                        onBeforeMask: function (value) {
                            if (value === null || value === undefined) return value;
                            value = String(value);
                            if (value.includes(',') || !value.includes('.')) return value;
                            if (/^\d+\.\d{1,2}$/.test(value)) return value.replace('.', ',');
                            return value;
                        }
                    });
                } catch (e) { }
            }

            function formatDateTimeMask(input) {
                let digits = (input.value || '').replace(/\D/g, '').slice(0, 12);
                let out = '';
                if (digits.length > 0) out += digits.slice(0, 2);
                if (digits.length >= 3) out += '/' + digits.slice(2, 4);
                if (digits.length >= 5) out += '/' + digits.slice(4, 8);
                if (digits.length >= 9) out += ' ' + digits.slice(8, 10);
                if (digits.length >= 11) out += ':' + digits.slice(10, 12);
                input.value = out;
            }

            function canonicalToBr(value) {
                const raw = String(value || '').trim();
                if (!raw) return '';

                const match = raw.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::\d{2})?)?$/);
                if (!match) return raw;

                const y = match[1];
                const m = match[2];
                const d = match[3];
                const h = match[4] || '00';
                const i = match[5] || '00';
                return d + '/' + m + '/' + y + ' ' + h + ':' + i;
            }

            function brToCanonical(value) {
                const raw = String(value || '').trim();
                if (!raw) return '';

                const match = raw.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?$/);
                if (!match) return null;

                const d = Number(match[1]);
                const m = Number(match[2]);
                const y = Number(match[3]);
                const h = Number(match[4] || '0');
                const i = Number(match[5] || '0');

                if (m < 1 || m > 12 || d < 1 || d > 31 || h < 0 || h > 23 || i < 0 || i > 59) {
                    return null;
                }

                const test = new Date(y, m - 1, d, h, i, 0, 0);
                if (test.getFullYear() !== y || (test.getMonth() + 1) !== m || test.getDate() !== d) {
                    return null;
                }

                const dd = String(d).padStart(2, '0');
                const mm = String(m).padStart(2, '0');
                const hh = String(h).padStart(2, '0');
                const ii = String(i).padStart(2, '0');
                return y + '-' + mm + '-' + dd + ' ' + hh + ':' + ii;
            }

            function dateToCanonical(date) {
                if (!(date instanceof Date)) return '';
                const y = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');
                const hh = String(date.getHours()).padStart(2, '0');
                const ii = String(date.getMinutes()).padStart(2, '0');
                return y + '-' + mm + '-' + dd + ' ' + hh + ':' + ii;
            }

            function initBrDatePicker(input, hidden) {
                if (!(window.flatpickr && input && hidden)) return;

                flatpickr(input, {
                    enableTime: true,
                    time_24hr: true,
                    allowInput: true,
                    minuteIncrement: 1,
                    dateFormat: 'd/m/Y H:i',
                    locale: (window.flatpickr && flatpickr.l10ns && flatpickr.l10ns.pt) ? flatpickr.l10ns.pt : 'default',
                    onReady: function (_, __, instance) {
                        const canonical = String(hidden.value || '').trim();
                        if (!canonical) return;
                        const parsed = instance.parseDate(canonical, 'Y-m-d H:i');
                        if (parsed) {
                            instance.setDate(parsed, true, 'Y-m-d H:i');
                            hidden.value = dateToCanonical(parsed);
                        }
                    },
                    onChange: function (selectedDates) {
                        hidden.value = selectedDates[0] ? dateToCanonical(selectedDates[0]) : '';
                    },
                    onClose: function (selectedDates, dateStr, instance) {
                        if (selectedDates[0]) {
                            hidden.value = dateToCanonical(selectedDates[0]);
                            return;
                        }

                        const typed = String(dateStr || '').trim();
                        if (typed === '') {
                            hidden.value = '';
                            return;
                        }

                        const parsed = instance.parseDate(typed, 'd/m/Y H:i');
                        if (!parsed) {
                            showUiError('Data/hora invalida. Use o formato DD/MM/AAAA HH:MM.');
                            return;
                        }

                        instance.setDate(parsed, true);
                        hidden.value = dateToCanonical(parsed);
                    }
                });
            }

            function setupDateTimeFields() {
                const issuedHidden = document.getElementById('issued_at');
                const dueHidden = document.getElementById('due_at');
                const issuedDisplay = document.getElementById('issued_at_br');
                const dueDisplay = document.getElementById('due_at_br');

                if (!issuedHidden || !dueHidden || !issuedDisplay || !dueDisplay) {
                    return;
                }

                issuedDisplay.value = canonicalToBr(issuedHidden.value);
                dueDisplay.value = canonicalToBr(dueHidden.value);

                initBrDatePicker(issuedDisplay, issuedHidden);
                initBrDatePicker(dueDisplay, dueHidden);

                [issuedDisplay, dueDisplay].forEach(function (field) {
                    field.setAttribute('inputmode', 'numeric');
                    field.addEventListener('input', function () {
                        formatDateTimeMask(field);
                    });
                    field.addEventListener('blur', function () {
                        const raw = String(field.value || '').trim();
                        if (!raw) return;
                        const canonical = brToCanonical(raw);
                        if (!canonical) {
                            showUiError('Data/hora invalida. Use o formato DD/MM/AAAA HH:MM.');
                        }
                    });
                });

                const form = document.getElementById('invoiceFormAdmin');
                if (!form) return;

                form.addEventListener('submit', function (event) {
                    const issuedCanonical = brToCanonical(issuedDisplay.value);
                    if (String(issuedDisplay.value || '').trim() !== '' && !issuedCanonical) {
                        event.preventDefault();
                        issuedDisplay.focus();
                        showUiError('Emissao invalida. Use DD/MM/AAAA HH:MM.');
                        return;
                    }

                    const dueCanonical = brToCanonical(dueDisplay.value);
                    if (String(dueDisplay.value || '').trim() !== '' && !dueCanonical) {
                        event.preventDefault();
                        dueDisplay.focus();
                        showUiError('Vencimento invalido. Use DD/MM/AAAA HH:MM.');
                        return;
                    }

                    issuedHidden.value = issuedCanonical || '';
                    dueHidden.value = dueCanonical || '';
                });
            }

            function bindRemoveButtons(scope) {
                (scope || document).querySelectorAll('.btnRemoveItem').forEach(function (btn) {
                    if (btn.dataset.bound) return;
                    btn.dataset.bound = '1';
                    btn.addEventListener('click', function () {
                        const tr = btn.closest('tr');
                        const tbody = tr && tr.parentElement;
                        if (!tr || !tbody) return;
                        if (tbody.querySelectorAll('tr').length <= 1) {
                            tr.querySelectorAll('input').forEach(i => i.value = '');
                            const qty = tr.querySelector('input[name="items_quantity[]"]');
                            if (qty) qty.value = 1;
                            return;
                        }
                        tr.remove();
                    });
                });
            }

            function addRow() {
                const tbody = document.querySelector('#itemsTable tbody');
                if (!tbody) return;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                        <td><input name="items_description[]" class="form-control" required></td>
                        <td><input name="items_quantity[]" class="form-control" type="number" min="1" value="1"></td>
                        <td><input name="items_unit_price[]" class="form-control mask-money" required></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btnRemoveItem" title="Remover">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                tbody.appendChild(tr);
                applyMoneyMask(tr);
                bindRemoveButtons(tr);
            }

            const btn = document.getElementById('btnAddItem');
            if (btn) btn.addEventListener('click', addRow);

            applyMoneyMask(document);
            setupDateTimeFields();
            bindRemoveButtons(document);
        })();
    </script>
@endpush
