@extends('admin.layouts.app')
@section('title', 'Controle de Repasses e Comissões')
@section('page_title', 'Controle de Repasses e Comissões')
@section('breadcrumb')
    <li class="breadcrumb-item active">Repasses &amp; Comissões</li>
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

    {{-- Barra de Acesso Rápido --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 8px;">
        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <a href="{{ route('admin.splits.index', ['receiver_type' => 'superadmin']) }}" class="btn btn-sm {{ $receiverType === 'superadmin' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-user-shield mr-1"></i>Minhas Comissões (Superadmin)
            </a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-receipt mr-1"></i>Pedidos
            </a>
            <a href="{{ route('admin.settings', ['group' => 'gateway']) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-credit-card mr-1"></i>Configurações de Pagamentos
            </a>
        </div>
        <button type="button" class="btn btn-sm btn-outline-info font-weight-bold" data-help-drawer="splits_overview">
            <i class="fas fa-book-open mr-1"></i>Guia Completo de Automação
        </button>
    </div>

    {{-- Indicadores KPI --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-circle-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Liquidado</span>
                    <span class="info-box-number text-success">R$ {{ number_format($summary['paid'], 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Aguardando repasse</span>
                    <span class="info-box-number text-warning">R$ {{ number_format($summary['pending'], 2, ',', '.') }}</span>
                    <span class="progress-description text-xs">{{ $summary['pending_count'] }} pendente(s)</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Falha registrada</span>
                    <span class="info-box-number text-danger">R$ {{ number_format($summary['failed'], 2, ',', '.') }}</span>
                    <span class="progress-description text-xs">{{ $summary['failed_count'] }} com erro</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary"><i class="fas fa-chart-pie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Volume total</span>
                    <span class="info-box-number">R$ {{ number_format($summary['total'], 2, ',', '.') }}</span>
                    <span class="progress-description text-xs">{{ $splits->total() }} registro(s)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card de Automação (exclusivo Superadmin) --}}
    @if(!empty($automation['is_superadmin']))
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
                <div>
                    <h3 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-robot mr-2 text-primary"></i>Automação de Comissões (SumUp &rarr; Mercado Pago)
                    </h3>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span class="badge badge-{{ $automation['enabled'] ? 'success' : 'secondary' }} px-2 py-1">
                        {{ $automation['enabled'] ? 'Automação Ativa' : 'Automação Desativada' }}
                    </span>
                    <button type="button" class="btn btn-xs btn-outline-primary" data-help-drawer="splits_overview">
                        <i class="fas fa-question-circle mr-1"></i>Como configurar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted text-sm mb-3">
                    Vendas criadas ou divulgadas pelo administrador são <strong>100% isentas automaticamente</strong>.
                    Clique no ícone <span class="unn-help-trigger" style="pointer-events:none;"><i class="fas fa-question"></i></span> ao lado de qualquer campo para abrir o passo a passo com link direto para as credenciais.
                </p>

                <form id="js-split-automation-form" action="{{ route('admin.splits.automation_settings') }}" method="POST">
                    @csrf

                    {{-- Bloco 1: Regras Gerais --}}
                    <div class="unn-section-box mb-3">
                        <div class="text-xs font-weight-bold text-uppercase text-muted mb-3">
                            <i class="fas fa-sliders-h mr-1"></i> 1. Regras Gerais da Comissão
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-3 mb-md-0">
                                <label class="font-weight-bold text-sm">
                                    Modo de Liquidação
                                    <button type="button" class="unn-help-trigger" data-help-drawer="payout_mode" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <select name="payout_mode" id="js-admin-payout-mode" class="form-control">
                                    <option value="mp_auto_billing" @selected($automation['mode'] === 'mp_auto_billing')>100% SumUp + Cobrança Automática no MP (a cada venda)</option>
                                    <option value="efi_pix_out" @selected($automation['mode'] === 'efi_pix_out')>100% SumUp + Pix Automático (Efí &rarr; MP)</option>
                                    <option value="mp_routing" @selected($automation['mode'] === 'mp_routing')>Roteamento Inteligente (Terceiros no MP)</option>
                                    <option value="manual" @selected($automation['mode'] === 'manual')>Apenas Controle (Repasse Manual)</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3 mb-md-0">
                                <label class="font-weight-bold text-sm">
                                    Comissão (%)
                                    <button type="button" class="unn-help-trigger" data-help-drawer="percent" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="number" step="0.01" min="0" max="100" name="percent" class="form-control" value="{{ $automation['percent'] }}">
                            </div>
                            <div class="col-md-2 mb-3 mb-md-0">
                                <label class="font-weight-bold text-sm">
                                    Mínimo (R$)
                                    <button type="button" class="unn-help-trigger" data-help-drawer="min_payout" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="number" step="0.01" min="0.01" name="min_payout" class="form-control" value="{{ number_format($automation['min_payout'], 2, '.', '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="font-weight-bold text-sm">
                                    E-mail Financeiro Admin
                                    <button type="button" class="unn-help-trigger" data-help-drawer="admin_email" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="email" name="admin_email" class="form-control" value="{{ $automation['admin_email'] ?? '' }}" placeholder="financeiro@empresa.com">
                            </div>
                        </div>
                    </div>

                    {{-- Bloco 2A: Credenciais Mercado Pago (Opção C) --}}
                    <div class="unn-section-box mb-3 js-mode-panel" data-mode-panel="mp_auto_billing">
                        <div class="text-xs font-weight-bold text-uppercase text-muted mb-3">
                            <i class="fas fa-wallet mr-1"></i> 2. Credenciais da Sua Conta Mercado Pago (Cobrança a cada venda)
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="font-weight-bold text-sm">
                                    Access Token do seu Mercado Pago
                                    <button type="button" class="unn-help-trigger" data-help-drawer="mp_access_token" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="password" name="mp_access_token" class="form-control" value=""
                                    placeholder="{{ !empty($automation['mp_has_token']) ? '•••••••• (já salvo — preencha para trocar)' : 'APP_USR-...' }}">
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="font-weight-bold text-sm">
                                    Customer ID MP do Admin (Cartão Auto)
                                    <button type="button" class="unn-help-trigger" data-help-drawer="mp_customer_id" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="text" name="mp_customer_id" class="form-control" value="{{ $automation['mp_customer_id'] ?? '' }}"
                                    placeholder="Opcional — sem cartão gera Pix automático">
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold text-sm">
                                    Card Token MP do Admin
                                    <button type="button" class="unn-help-trigger" data-help-drawer="mp_card_token" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="password" name="mp_card_token" class="form-control" value=""
                                    placeholder="{{ !empty($automation['mp_has_card_token']) ? '•••••••• (cartão salvo)' : 'Opcional para débito automático' }}">
                            </div>
                        </div>
                    </div>

                    {{-- Bloco 2B: Credenciais Efí Pay (Opção B) --}}
                    <div class="unn-section-box mb-3 js-mode-panel" data-mode-panel="efi_pix_out">
                        <div class="text-xs font-weight-bold text-uppercase text-muted mb-3">
                            <i class="fas fa-bolt mr-1"></i> 2. Recebimento Pix Automático (Efí Pay &rarr; Mercado Pago)
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3 mb-md-0">
                                <label class="font-weight-bold text-sm">
                                    Sua Chave Pix do Mercado Pago
                                    <button type="button" class="unn-help-trigger" data-help-drawer="pix_key" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="text" name="pix_key" class="form-control" value="{{ $automation['pix_key'] }}"
                                    placeholder="CPF, CNPJ, e-mail ou chave aleatória do Mercado Pago">
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold text-sm">
                                    Carência SumUp (D+dias)
                                    <button type="button" class="unn-help-trigger" data-help-drawer="delay_days" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="number" min="0" max="30" name="delay_days" class="form-control" value="{{ $automation['delay_days'] }}">
                            </div>
                        </div>
                    </div>

                    {{-- Rodapé de Ativação e Salvamento --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center pt-2 border-top" style="gap: 12px;">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="admin_auto_enabled" name="auto_enabled" value="1" @checked($automation['enabled'])>
                            <label class="custom-control-label font-weight-bold" for="admin_auto_enabled">
                                Ativar envio/cobrança automática sem interação humana
                                <button type="button" class="unn-help-trigger" data-help-drawer="auto_enabled" title="Ver instruções"><i class="fas fa-question"></i></button>
                            </label>
                            <small class="d-block text-muted mt-1">
                                Mercado Pago:
                                <span class="font-weight-bold {{ !empty($automation['mp_has_token']) ? 'text-success' : 'text-warning' }}">
                                    {{ !empty($automation['mp_has_token']) ? 'Access Token conectado' : 'Access Token pendente' }}
                                </span>
                                &nbsp;|&nbsp;
                                Efí Pay:
                                <span class="font-weight-bold {{ ($automation['efi_enabled'] && $automation['efi_has_cert']) ? 'text-success' : 'text-muted' }}">
                                    {{ ($automation['efi_enabled'] && $automation['efi_has_cert']) ? 'Conectada' : 'Opcional (não ativa)' }}
                                </span>
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary font-weight-bold px-4">
                            <i class="fas fa-save mr-1"></i>Salvar Automação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card card-outline card-secondary shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.splits.index') }}" method="GET" class="row align-items-center">
                <div class="col-lg-4 mb-2 mb-lg-0">
                    <input type="text" name="search" class="form-control" value="{{ $search }}"
                        placeholder="Buscar pedido, nome ou e-mail">
                </div>
                <div class="col-lg-2 mb-2 mb-lg-0">
                    <select name="status" class="form-control">
                        <option value="">Todos os status</option>
                        <option value="pending" @selected($status === 'pending')>Pendentes</option>
                        <option value="processing" @selected($status === 'processing')>Processando</option>
                        <option value="paid" @selected($status === 'paid')>Liquidados</option>
                        <option value="failed" @selected($status === 'failed')>Com falha</option>
                    </select>
                </div>
                <div class="col-lg-3 mb-2 mb-lg-0">
                    <select name="receiver_type" class="form-control">
                        <option value="">Todos os destinatários</option>
                        @foreach($typeConfig as $value => $cfg)
                            <option value="{{ $value }}" @selected($receiverType === $value)>{{ $cfg['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 d-flex" style="gap: 8px;">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.splits.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela de Repasses --}}
    <div class="card card-outline card-dark shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-money-bill-wave mr-2 text-primary"></i>Repasses por rateio
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
                                <th>Operação</th>
                                <th class="text-center">Ação</th>
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
                                        <div class="font-weight-bold">{{ $split->receiver?->name ?? 'Não vinculado' }}</div>
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
                                        @elseif($payoutStatus === 'processing')
                                            <span class="badge badge-info px-2 py-1">Processando</span>
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
                                                <div class="text-info mt-1"><strong>ID/E2E:</strong> <code>{{ \Illuminate\Support\Str::limit($payout->external_id, 24) }}</code></div>
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
                                        @if($payoutStatus !== 'paid')
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
                                                @if($split->pix_key)
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
                                                @endif
                                            </div>
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
                    <p class="text-muted mb-0">Os repasses são preparados automaticamente quando um pedido pago gera rateios.</p>
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
@include('partials.splits-help-registry', ['settingsPaymentsUrl' => route('admin.settings', ['group' => 'gateway'])])
<script>
    (function () {
        const modeSelect = document.getElementById('js-admin-payout-mode');
        function syncModePanels() {
            if (!modeSelect) return;
            const current = modeSelect.value;
            document.querySelectorAll('.js-mode-panel').forEach(function (panel) {
                const target = panel.getAttribute('data-mode-panel');
                panel.style.display = (target === current) ? 'block' : 'none';
            });
        }
        if (modeSelect) {
            modeSelect.addEventListener('change', syncModePanels);
            syncModePanels();
        }
    })();

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
            throw new Error(data.message || 'Não foi possível concluir a operação.');
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
                title: 'Processar repasse automático agora?',
                text: 'O sistema processará automaticamente ' + button.dataset.amount + ' referente à comissão de ' + button.dataset.receiver + '.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, processar agora',
                cancelButtonText: 'Cancelar'
            });

            if (!confirmed.isConfirmed) return;

            try {
                const data = await postJson(button.dataset.url);
                await Swal.fire('Processado', data.message, 'success');
                window.location.reload();
            } catch (error) {
                await Swal.fire('Erro no processamento', error.message, 'error');
            }
        });
    });

    document.querySelectorAll('.js-confirm-payout').forEach(function (button) {
        button.addEventListener('click', async function () {
            const confirmed = await Swal.fire({
                title: 'Confirmar repasse?',
                text: 'Use esta ação somente depois de realizar a transferência para ' + button.dataset.receiver + ' no valor de ' + button.dataset.amount + '.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, confirmar',
                cancelButtonText: 'Cancelar'
            });

            if (!confirmed.isConfirmed) return;

            try {
                const data = await postJson(button.dataset.url);
                await Swal.fire('Concluído', data.message, 'success');
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
                inputPlaceholder: 'Ex.: chave PIX recusada, conta indisponível, divergência de valor',
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

            if (!result.isConfirmed) return;

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
