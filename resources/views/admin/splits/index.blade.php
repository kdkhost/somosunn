@extends('admin.layouts.app')
@section('title', 'Controle de Repasses')
@section('page_title', 'Controle de Repasses')
@section('breadcrumb')
    <li class="breadcrumb-item active">Splits</li>
@endsection

@section('content')
    @php
        $typeConfig = [
            'seller' => ['label' => 'Vendedor', 'icon' => 'fa-store', 'color' => 'primary'],
            'platform' => ['label' => 'Plataforma', 'icon' => 'fa-building', 'color' => 'info'],
            'traffic' => ['label' => 'Marketing', 'icon' => 'fa-bullhorn', 'color' => 'warning'],
            'superadmin' => ['label' => 'Superadmin', 'icon' => 'fa-user-shield', 'color' => 'danger'],
        ];
    @endphp

    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-success"><i class="fas fa-circle-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Liquidado</span>
                    <span class="info-box-number text-success">R$ {{ number_format($summary['paid'], 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-warning"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Aguardando repasse</span>
                    <span class="info-box-number text-warning">R$ {{ number_format($summary['pending'], 2, ',', '.') }}</span>
                    <span class="progress-description text-xs">{{ $summary['pending_count'] }} pendente(s)</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Falha registrada</span>
                    <span class="info-box-number text-danger">R$ {{ number_format($summary['failed'], 2, ',', '.') }}</span>
                    <span class="progress-description text-xs">{{ $summary['failed_count'] }} com erro</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-primary"><i class="fas fa-chart-pie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Volume total</span>
                    <span class="info-box-number">R$ {{ number_format($summary['total'], 2, ',', '.') }}</span>
                    <span class="progress-description text-xs">{{ $splits->total() }} registro(s)</span>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($automation['is_superadmin']))
        <div class="card card-outline card-info shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-robot mr-2 text-info"></i>Automação de Comissões (SumUp &rarr; Mercado Pago)
                </h3>
                <span class="badge badge-{{ $automation['enabled'] ? 'success' : 'secondary' }} px-2 py-1">
                    {{ $automation['enabled'] ? 'Automação Ativa' : 'Automação Desativada' }}
                </span>
            </div>
            <div class="card-body">
                <p class="text-muted text-sm mb-3">
                    Vendas criadas ou divulgadas pelo administrador são <strong>isentas automaticamente</strong>.
                    Para vendas de membros, mentores e produtores pagas na SumUp, o sistema agenda e dispara o Pix automático via Efí Pay direto para sua chave Pix do Mercado Pago.
                </p>
                <form id="js-split-automation-form" action="{{ route('admin.splits.automation_settings') }}" method="POST" class="row">
                    @csrf
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-sm">Modo de Liquidação</label>
                        <select name="payout_mode" class="form-control">
                            <option value="mp_auto_billing" @selected($automation['mode'] === 'mp_auto_billing')>100% SumUp + Cobrança Automática no MP (a cada venda)</option>
                            <option value="efi_pix_out" @selected($automation['mode'] === 'efi_pix_out')>100% SumUp + Pix Automático (Efí &rarr; MP)</option>
                            <option value="mp_routing" @selected($automation['mode'] === 'mp_routing')>Roteamento Inteligente (Terceiros no MP)</option>
                            <option value="manual" @selected($automation['mode'] === 'manual')>Apenas Controle (Repasse Manual)</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-sm">Access Token do seu Mercado Pago</label>
                        <input type="password" name="mp_access_token" class="form-control" value=""
                            placeholder="{{ !empty($automation['mp_has_token']) ? '•••••••• (já configurado — preencha para trocar)' : 'APP_USR-...' }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold text-sm">Comissão (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="percent" class="form-control" value="{{ $automation['percent'] }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold text-sm">E-mail Financeiro Admin</label>
                        <input type="email" name="admin_email" class="form-control" value="{{ $automation['admin_email'] ?? '' }}"
                            placeholder="financeiro@empresa.com">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold text-sm">Valor Mínimo (R$)</label>
                        <input type="number" step="0.01" min="0.01" name="min_payout" class="form-control" value="{{ number_format($automation['min_payout'], 2, '.', '') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-sm">Customer ID MP do Admin (Cartão Auto)</label>
                        <input type="text" name="mp_customer_id" class="form-control" value="{{ $automation['mp_customer_id'] ?? '' }}"
                            placeholder="Opcional — sem cartão gera Pix automático">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-sm">Card Token MP do Admin</label>
                        <input type="password" name="mp_card_token" class="form-control" value=""
                            placeholder="{{ !empty($automation['mp_has_card_token']) ? '•••••••• (salvo)' : 'Opcional para débito automático' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-sm">Sua Chave Pix Mercado Pago (Modo Efí)</label>
                        <input type="text" name="pix_key" class="form-control" value="{{ $automation['pix_key'] }}"
                            placeholder="CPF, CNPJ, e-mail ou chave aleatória do MP">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold text-sm">Carência Efí (D+dias)</label>
                        <input type="number" min="0" max="30" name="delay_days" class="form-control" value="{{ $automation['delay_days'] }}">
                    </div>
                    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center pt-2 border-top">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="admin_auto_enabled" name="auto_enabled" value="1" @checked($automation['enabled'])>
                            <label class="custom-control-label font-weight-bold" for="admin_auto_enabled">
                                Ativar envio automático sem interação humana
                            </label>
                            <small class="d-block text-muted">
                                Status Efí Pay:
                                @if($automation['efi_enabled'] && $automation['efi_has_cert'])
                                    <span class="text-success font-weight-bold">Conectada e com certificado ativo</span>
                                @else
                                    <span class="text-warning font-weight-bold">Configure as credenciais e certificado Efí Pay em Configurações &gt; Pagamentos</span>
                                @endif
                            </small>
                        </div>
                        <button type="submit" class="btn btn-info font-weight-bold mt-2 mt-md-0">
                            <i class="fas fa-save mr-1"></i>Salvar Automação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.splits.index') }}" method="GET" class="row">
                <div class="col-lg-4 mb-2">
                    <input type="text" name="search" class="form-control" value="{{ $search }}"
                        placeholder="Buscar pedido, nome ou e-mail">
                </div>
                <div class="col-lg-2 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Todos os repasses</option>
                        <option value="pending" @selected($status === 'pending')>Pendentes</option>
                        <option value="paid" @selected($status === 'paid')>Liquidados</option>
                        <option value="failed" @selected($status === 'failed')>Com falha</option>
                    </select>
                </div>
                <div class="col-lg-2 mb-2">
                    <select name="receiver_type" class="form-control">
                        <option value="">Todos os destinatarios</option>
                        @foreach($typeConfig as $value => $cfg)
                            <option value="{{ $value }}" @selected($receiverType === $value)>{{ $cfg['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 mb-2 d-flex">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.splits.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-left mr-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-dark shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-money-bill-wave mr-2 text-primary"></i>Repasse por rateio
            </h3>
        </div>
        <div class="card-body p-0">
            @if($splits->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th class="pl-4">Pedido</th>
                                <th>Recebedor</th>
                                <th>Tipo</th>
                                <th class="text-right">Valor</th>
                                <th class="text-center">%</th>
                                <th>Chave PIX</th>
                                <th>Repasse</th>
                                <th>Operacao</th>
                                <th class="text-center">Acao</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($splits as $split)
                                @php
                                    $cfg = $typeConfig[$split->receiver_type] ?? ['label' => ucfirst($split->receiver_type), 'icon' => 'fa-circle', 'color' => 'secondary'];
                                    $payout = $split->payout;
                                    $payoutStatus = $payout?->status ?? 'pending';
                                    $provider = $payout?->provider ?? 'manual';
                                @endphp
                                <tr>
                                    <td class="pl-4 align-middle">
                                        <a href="{{ route('admin.orders.show', $split->order_id) }}" class="font-weight-bold text-primary">#{{ $split->order_id }}</a>
                                        <div class="text-xs text-muted">{{ $split->created_at?->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold">{{ $split->receiver?->name ?? 'Nao vinculado' }}</div>
                                        <div class="text-xs text-muted">{{ $split->receiver?->email }}</div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-{{ $cfg['color'] }}">
                                            <i class="fas {{ $cfg['icon'] }} mr-1"></i>{{ $cfg['label'] }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-right font-weight-bold">R$ {{ number_format((float) $split->amount, 2, ',', '.') }}</td>
                                    <td class="align-middle text-center">{{ number_format((float) $split->percentage, 2, ',', '.') }}%</td>
                                    <td class="align-middle">
                                        @if($split->pix_key)
                                            <code class="bg-light border rounded px-2 py-1 d-inline-block">{{ $split->pix_key }}</code>
                                        @else
                                            <span class="text-danger text-xs font-weight-bold">PIX ausente</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($payoutStatus === 'paid')
                                            <span class="badge badge-success px-2 py-1">Liquidado</span>
                                        @elseif($payoutStatus === 'failed')
                                            <span class="badge badge-danger px-2 py-1">Falhou</span>
                                        @else
                                            <span class="badge badge-warning px-2 py-1">Pendente</span>
                                        @endif
                                        <div class="text-xs text-muted mt-1">
                                            @switch($provider)
                                                @case('internal') Interno @break
                                                @case('mp_auto_billing') Cobrança Auto (MP) @break
                                                @case('efi_pix_out') Pix Auto (Efí) @break
                                                @case('mercadopago_split') Split Mercado Pago @break
                                                @default Manual
                                            @endswitch
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="text-xs">
                                            <div><strong>Tentativas:</strong> {{ (int) ($payout?->attempts ?? 0) }}</div>
                                            @if(!empty($payout?->external_id))
                                                <div class="text-info mt-1"><strong>E2E:</strong> <code>{{ \Illuminate\Support\Str::limit($payout->external_id, 24) }}</code></div>
                                            @endif
                                            @if(!empty($payout?->last_error))
                                                <div class="text-danger mt-1">{{ $payout->last_error }}</div>
                                            @elseif($payout?->processed_at)
                                                <div class="text-success mt-1">Confirmado em {{ $payout->processed_at->format('d/m/Y H:i') }}</div>
                                            @elseif($payout?->scheduled_for)
                                                <div class="text-primary mt-1">Agendado para {{ $payout->scheduled_for->format('d/m/Y H:i') }}</div>
                                            @else
                                                <div class="text-muted mt-1">{{ $payout?->notes ?: 'Aguardando tratamento' }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($payoutStatus !== 'paid' && $split->pix_key)
                                            <div class="btn-group">
                                                @if($split->receiver_type === 'superadmin' && (!empty($automation['mp_has_token']) || !empty($automation['efi_enabled'])))
                                                    <button type="button"
                                                        class="btn btn-sm btn-info js-process-auto-payout"
                                                        data-url="{{ route('admin.splits.process_auto', $split) }}"
                                                        data-receiver="{{ $split->receiver?->name ?? $cfg['label'] }}"
                                                        data-amount="R$ {{ number_format((float) $split->amount, 2, ',', '.') }}"
                                                        title="Disparar cobrança/repasse automático agora">
                                                        <i class="fas fa-bolt mr-1"></i>Processar Auto
                                                    </button>
                                                @endif
                                                <button type="button"
                                                    class="btn btn-sm btn-success js-confirm-payout"
                                                    data-url="{{ route('admin.splits.pay', $split) }}"
                                                    data-receiver="{{ $split->receiver?->name ?? $cfg['label'] }}"
                                                    data-amount="R$ {{ number_format((float) $split->amount, 2, ',', '.') }}">
                                                    <i class="fas fa-check mr-1"></i>Confirmar
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger js-fail-payout"
                                                    data-url="{{ route('admin.splits.fail', $split) }}">
                                                    <i class="fas fa-triangle-exclamation"></i>
                                                </button>
                                            </div>
                                        @elseif($payoutStatus !== 'paid')
                                            <span class="text-danger text-xs font-weight-bold">Cadastre o PIX</span>
                                        @else
                                            <span class="text-success"><i class="fas fa-check-double"></i></span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted font-weight-bold">Nenhum repasse encontrado</h5>
                    <p class="text-muted mb-0">Os repasses sao preparados automaticamente quando um pedido pago gera rateios.</p>
                </div>
            @endif
        </div>

        @if($splits->hasPages())
            <div class="card-footer">
                {{ $splits->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload || {})
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Nao foi possivel concluir a operacao.');
        }

        return data;
    }

    const automationForm = document.getElementById('js-split-automation-form');
    if (automationForm) {
        automationForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const formData = new FormData(automationForm);
            const payload = {
                auto_enabled: formData.get('auto_enabled') ? 1 : 0,
                payout_mode: formData.get('payout_mode'),
                pix_key: formData.get('pix_key'),
                percent: formData.get('percent'),
                delay_days: formData.get('delay_days'),
                min_payout: formData.get('min_payout'),
                mp_access_token: formData.get('mp_access_token'),
                mp_customer_id: formData.get('mp_customer_id'),
                mp_card_token: formData.get('mp_card_token'),
                admin_email: formData.get('admin_email')
            };

            try {
                const data = await postJson(automationForm.action, payload);
                if (typeof toastr !== 'undefined') {
                    toastr.success(data.message);
                } else {
                    await Swal.fire('Salvo', data.message, 'success');
                }
                setTimeout(function () { window.location.reload(); }, 800);
            } catch (error) {
                await Swal.fire('Erro', error.message, 'error');
            }
        });
    }

    document.querySelectorAll('.js-process-auto-payout').forEach(function (button) {
        button.addEventListener('click', async function () {
            const confirmed = await Swal.fire({
                title: 'Disparar Pix automático agora?',
                text: 'O sistema enviará ' + button.dataset.amount + ' via Efí Pay para a chave Pix de ' + button.dataset.receiver + '.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, enviar Pix',
                cancelButtonText: 'Cancelar'
            });

            if (!confirmed.isConfirmed) {
                return;
            }

            try {
                const data = await postJson(button.dataset.url);
                await Swal.fire('Processado', data.message, 'success');
                window.location.reload();
            } catch (error) {
                await Swal.fire('Erro no envio', error.message, 'error');
            }
        });
    });

    document.querySelectorAll('.js-confirm-payout').forEach(function (button) {
        button.addEventListener('click', async function () {
            const confirmed = await Swal.fire({
                title: 'Confirmar repasse?',
                text: 'Use esta acao somente depois de realizar a transferencia para ' + button.dataset.receiver + ' no valor de ' + button.dataset.amount + '.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, confirmar',
                cancelButtonText: 'Cancelar'
            });

            if (!confirmed.isConfirmed) {
                return;
            }

            try {
                const data = await postJson(button.dataset.url);
                await Swal.fire('Concluido', data.message, 'success');
                window.location.reload();
            } catch (error) {
                await Swal.fire('Erro', error.message, 'error');
            }
        });
    });

    document.querySelectorAll('.js-fail-payout').forEach(function (button) {
        button.addEventListener('click', async function () {
            const result = await Swal.fire({
                title: 'Registrar falha',
                input: 'text',
                inputLabel: 'Motivo da falha do repasse',
                inputPlaceholder: 'Ex.: chave PIX recusada, conta indisponivel, divergencia de valor',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Registrar falha',
                cancelButtonText: 'Cancelar',
                inputValidator: function (value) {
                    if (!value) {
                        return 'Informe o motivo da falha.';
                    }
                }
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const data = await postJson(button.dataset.url, { message: result.value });
                await Swal.fire('Registrado', data.message, 'success');
                window.location.reload();
            } catch (error) {
                await Swal.fire('Erro', error.message, 'error');
            }
        });
    });
</script>
@endpush
