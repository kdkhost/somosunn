@extends('panel.layouts.app')

@section('title', 'Controle de Repasses e Comissões')

@section('panel_content')
    @php
        $typeLabels = [
            'seller' => 'Vendedor',
            'platform' => 'Plataforma',
            'traffic' => 'Marketing',
            'superadmin' => 'Superadmin',
        ];
    @endphp

    <div class="space-y-6">
        {{-- Cabeçalho e Acesso Rápido --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Controle de Repasses e Comissões</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Gestão prática de rateios e cobrança automática de comissões no Mercado Pago, com isenção automática de vendas do administrador.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('panel.admin.splits.index', ['receiver_type' => 'superadmin']) }}"
                    class="rounded-xl px-3.5 py-2 text-xs font-bold transition {{ $receiverType === 'superadmin' ? 'bg-blue-600 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800' }}">
                    <i class="fas fa-user-shield mr-1.5"></i>Minhas Comissões
                </a>
                <a href="{{ route('panel.admin.orders.index') }}"
                    class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <i class="fas fa-receipt mr-1.5"></i>Pedidos
                </a>
                <a href="{{ route('panel.admin.settings', ['group' => 'gateway']) }}"
                    class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <i class="fas fa-credit-card mr-1.5"></i>Pagamentos
                </a>
                <button type="button" data-help-drawer="splits_overview"
                    class="rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-600 hover:text-white dark:border-blue-800 dark:bg-blue-950/50 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white">
                    <i class="fas fa-book-open mr-1.5"></i>Guia Completo
                </button>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Total rateado', $summary['total'], 'fa-chart-pie', 'text-blue-600 dark:text-blue-400'],
                ['Liquidado', $summary['paid'], 'fa-circle-check', 'text-emerald-600 dark:text-emerald-400'],
                ['Aguardando repasse', $summary['pending'], 'fa-clock', 'text-amber-600 dark:text-amber-400'],
                ['Com falha', $summary['failed_count'], 'fa-exclamation-triangle', 'text-rose-600 dark:text-rose-400', true],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $card[0] }}</p>
                        <i class="fas {{ $card[2] }} {{ $card[3] }}"></i>
                    </div>
                    <p class="mt-3 text-xl font-black text-slate-900 dark:text-white">
                        {{ !empty($card[4]) ? number_format($card[1], 0, ',', '.') : 'R$ ' . number_format($card[1], 2, ',', '.') }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Card de Automação (exclusivo Superadmin) --}}
        @if(!empty($automation['is_superadmin']))
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-robot text-blue-600 dark:text-blue-400"></i>
                            Automação de Comissões (SumUp &rarr; Mercado Pago)
                        </h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Vendas do administrador são <strong>100% isentas</strong>. Clique no ícone <span class="unn-help-trigger" style="pointer-events:none;"><i class="fas fa-question"></i></span> em qualquer campo para abrir o guia passo a passo.
                        </p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $automation['enabled'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                        {{ $automation['enabled'] ? 'Automação Ativa' : 'Automação Desativada' }}
                    </span>
                </div>

                <form id="js-panel-split-automation-form" action="{{ route('panel.admin.splits.automation_settings') }}" method="POST" class="mt-5 space-y-4">
                    @csrf

                    {{-- Bloco 1: Regra Oficial de Gateways e Divisão 70% / 10% / 10% / 10% --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/60">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <i class="fas fa-sliders-h mr-1"></i> 1. Regra de Gateways e Divisão do Rateio (Soma = 100%)
                                <button type="button" class="unn-help-trigger" data-help-drawer="split_shares" title="Ver instruções"><i class="fas fa-question"></i></button>
                            </p>
                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                Admin = SumUp (isento) &bull; Usuários = Somente Mercado Pago (Split 30%)
                            </span>
                        </div>
                        <div class="grid gap-4 md:grid-cols-12">
                            <div class="md:col-span-5">
                                <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Modo de Liquidação
                                    <button type="button" class="unn-help-trigger" data-help-drawer="payout_mode" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <select name="payout_mode" id="js-panel-payout-mode" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                    <option value="mp_routing" @selected($automation['mode'] === 'mp_routing')>Split Nativo Mercado Pago (Usuários = MP 30% | Admin = SumUp)</option>
                                    <option value="mp_auto_billing" @selected($automation['mode'] === 'mp_auto_billing')>100% SumUp + Cobrança Automática no MP (a cada venda)</option>
                                    <option value="efi_pix_out" @selected($automation['mode'] === 'efi_pix_out')>100% SumUp + Pix Automático (Efí &rarr; MP)</option>
                                    <option value="manual" @selected($automation['mode'] === 'manual')>Apenas Controle (Repasse Manual)</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">Vendedor (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="seller_percent" value="{{ $automation['seller_percent'] ?? 70 }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Dono SaaS (%)
                                    <button type="button" class="unn-help-trigger" data-help-drawer="percent" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="number" step="0.01" min="0" max="100" name="percent" value="{{ $automation['percent'] }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div class="md:col-span-3 grid grid-cols-2 gap-2">
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">Marketing (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="traffic_percent" value="{{ $automation['traffic_percent'] ?? 10 }}"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">Admin (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="platform_percent" value="{{ $automation['platform_percent'] ?? 10 }}"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                </div>
                            </div>
                            <div class="md:col-span-3">
                                <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Valor Mínimo (R$)
                                    <button type="button" class="unn-help-trigger" data-help-drawer="min_payout" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="number" step="0.01" min="0.01" name="min_payout" value="{{ number_format($automation['min_payout'], 2, '.', '') }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div class="md:col-span-5">
                                <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    E-mail Financeiro Admin
                                    <button type="button" class="unn-help-trigger" data-help-drawer="admin_email" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="email" name="admin_email" value="{{ $automation['admin_email'] ?? '' }}" placeholder="financeiro@empresa.com"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div class="md:col-span-4">
                                <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Sua Chave Pix Mercado Pago
                                    <button type="button" class="unn-help-trigger" data-help-drawer="pix_key" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="text" name="pix_key" value="{{ $automation['pix_key'] }}" placeholder="Chave Pix da sua conta MP"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                    {{-- Bloco 2A: Mercado Pago (Opção C) --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/60 js-panel-mode-box" data-mode-box="mp_auto_billing">
                        <p class="mb-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <i class="fas fa-wallet mr-1"></i> 2. Credenciais da Sua Conta Mercado Pago (Cobrança a cada venda)
                        </p>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Access Token do seu Mercado Pago
                                    <button type="button" class="unn-help-trigger" data-help-drawer="mp_access_token" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="password" name="mp_access_token" value=""
                                    placeholder="{{ !empty($automation['mp_has_token']) ? '•••••••• (já salvo — preencha para trocar)' : 'APP_USR-...' }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Customer ID MP do Admin (Cartão Auto)
                                    <button type="button" class="unn-help-trigger" data-help-drawer="mp_customer_id" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="text" name="mp_customer_id" value="{{ $automation['mp_customer_id'] ?? '' }}" placeholder="Opcional — sem cartão gera Pix automático"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Card Token MP do Admin
                                    <button type="button" class="unn-help-trigger" data-help-drawer="mp_card_token" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="password" name="mp_card_token" value=""
                                    placeholder="{{ !empty($automation['mp_has_card_token']) ? '•••••••• (cartão salvo)' : 'Opcional para débito automático' }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                    {{-- Bloco 2B: Efí Pay (Opção B) --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/60 js-panel-mode-box" data-mode-box="efi_pix_out">
                        <p class="mb-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <i class="fas fa-bolt mr-1"></i> 2. Recebimento Pix Automático (Efí Pay &rarr; Mercado Pago)
                        </p>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Sua Chave Pix do Mercado Pago
                                    <button type="button" class="unn-help-trigger" data-help-drawer="pix_key" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="text" name="pix_key" value="{{ $automation['pix_key'] }}" placeholder="CPF, CNPJ, e-mail ou chave aleatória do Mercado Pago"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Carência SumUp (D+dias)
                                    <button type="button" class="unn-help-trigger" data-help-drawer="delay_days" title="Ver instruções"><i class="fas fa-question"></i></button>
                                </label>
                                <input type="number" min="0" max="30" name="delay_days" value="{{ $automation['delay_days'] }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                    {{-- Rodapé de Ativação --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <div>
                            <label class="inline-flex cursor-pointer items-center gap-2.5 text-sm font-bold text-slate-800 dark:text-white">
                                <input type="checkbox" name="auto_enabled" value="1" @checked($automation['enabled'])
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                Ativar envio/cobrança automática sem interação humana
                                <button type="button" class="unn-help-trigger" data-help-drawer="auto_enabled" title="Ver instruções"><i class="fas fa-question"></i></button>
                            </label>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Mercado Pago:
                                <span class="font-bold {{ !empty($automation['mp_has_token']) ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                    {{ !empty($automation['mp_has_token']) ? 'Access Token conectado' : 'Access Token pendente' }}
                                </span>
                                &nbsp;|&nbsp;
                                Efí Pay:
                                <span class="font-bold {{ ($automation['efi_enabled'] && $automation['efi_has_cert']) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                    {{ ($automation['efi_enabled'] && $automation['efi_has_cert']) ? 'Conectada' : 'Opcional (não ativa)' }}
                                </span>
                            </p>
                        </div>
                        <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i>Salvar Automação
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Filtros --}}
        <form method="GET" action="{{ route('panel.admin.splits.index') }}"
            class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_180px_180px_auto] dark:border-slate-800 dark:bg-slate-900">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar pedido, nome ou e-mail..."
                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos os status</option>
                <option value="pending" @selected($status === 'pending')>Pendentes</option>
                <option value="processing" @selected($status === 'processing')>Processando</option>
                <option value="paid" @selected($status === 'paid')>Liquidados</option>
                <option value="failed" @selected($status === 'failed')>Com falha</option>
            </select>
            <select name="receiver_type" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos os destinatários</option>
                @foreach($typeLabels as $value => $label)
                    <option value="{{ $value }}" @selected($receiverType === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-700">
                <i class="fas fa-filter mr-2"></i>Filtrar
            </button>
        </form>

        {{-- Tabela --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1220px] text-left">
                    <thead class="bg-slate-50 dark:bg-slate-950/70">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-5 py-4">Pedido</th>
                            <th class="px-5 py-4">Destinatário</th>
                            <th class="px-5 py-4">Tipo</th>
                            <th class="px-5 py-4 text-right">Percentual</th>
                            <th class="px-5 py-4 text-right">Valor</th>
                            <th class="px-5 py-4">Chave PIX</th>
                            <th class="px-5 py-4">Repasse</th>
                            <th class="px-5 py-4">Operação</th>
                            <th class="px-5 py-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($splits as $split)
                            @php
                                $payout = $split->payout;
                                $payoutStatus = $payout?->status ?? 'pending';
                                $provider = $payout?->provider ?? 'manual';
                            @endphp
                            <tr class="text-sm text-slate-600 hover:bg-slate-50/70 dark:text-slate-300 dark:hover:bg-slate-800/40">
                                <td class="px-5 py-4">
                                    <a href="{{ route('panel.admin.orders.show', $split->order_id) }}" class="font-black text-blue-600 hover:underline dark:text-blue-400">#{{ $split->order_id }}</a>
                                    <div class="text-xs text-slate-400">{{ $split->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $split->receiver?->name ?? 'Não vinculado' }}</div>
                                    <div class="text-xs text-slate-400">{{ $split->receiver?->email }}</div>
                                </td>
                                <td class="px-5 py-4">{{ $typeLabels[$split->receiver_type] ?? ucfirst($split->receiver_type) }}</td>
                                <td class="px-5 py-4 text-right font-bold">{{ number_format((float) $split->percentage, 2, ',', '.') }}%</td>
                                <td class="px-5 py-4 text-right font-black text-slate-900 dark:text-white">R$ {{ number_format((float) $split->amount, 2, ',', '.') }}</td>
                                <td class="px-5 py-4"><span class="max-w-[220px] truncate font-mono text-xs">{{ $split->pix_key ?: 'PIX ausente' }}</span></td>
                                <td class="px-5 py-4">
                                    @if($payoutStatus === 'paid')
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Liquidado</span>
                                    @elseif($payoutStatus === 'failed')
                                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">Falhou</span>
                                    @elseif($payoutStatus === 'processing')
                                        <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">Processando</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Pendente</span>
                                    @endif
                                    <div class="mt-1 text-[11px] text-slate-400">
                                        @switch($provider)
                                            @case('internal') Interno @break
                                            @case('mp_auto_billing') Cobrança Auto (MP) @break
                                            @case('efi_pix_out') Pix Auto (Efí) @break
                                            @case('mercadopago_split') Split Mercado Pago @break
                                            @default Manual
                                        @endswitch
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-500 dark:text-slate-400">
                                    <div><strong>Tentativas:</strong> {{ (int) ($payout?->attempts ?? 0) }}</div>
                                    @if(!empty($payout?->external_id))
                                        <div class="mt-1 text-blue-600 dark:text-blue-400"><strong>ID/E2E:</strong> <span class="font-mono">{{ \Illuminate\Support\Str::limit($payout->external_id, 24) }}</span></div>
                                    @endif
                                    @if(!empty($payout?->last_error))
                                        <div class="mt-1 text-rose-500">{{ $payout->last_error }}</div>
                                    @elseif($payout?->processed_at)
                                        <div class="mt-1 text-emerald-500">Confirmado em {{ $payout->processed_at->format('d/m/Y H:i') }}</div>
                                    @elseif($payout?->scheduled_for)
                                        <div class="mt-1 text-blue-500">Agendado para {{ $payout->scheduled_for->format('d/m/Y H:i') }}</div>
                                    @else
                                        <div class="mt-1">{{ $payout?->notes ?: 'Aguardando tratamento' }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if($payoutStatus !== 'paid')
                                        <div class="flex justify-end gap-2">
                                            @if($split->receiver_type === 'superadmin' && (!empty($automation['mp_has_token']) || !empty($automation['efi_enabled'])))
                                                <button type="button" data-auto-url="{{ route('panel.admin.splits.process_auto', $split) }}"
                                                    data-receiver="{{ $split->receiver?->name ?? ($typeLabels[$split->receiver_type] ?? 'Destinatário') }}"
                                                    data-amount="R$ {{ number_format((float) $split->amount, 2, ',', '.') }}"
                                                    class="js-auto-split rounded-xl bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700">
                                                    <i class="fas fa-bolt mr-1"></i>Processar Auto
                                                </button>
                                            @endif
                                            @if($split->pix_key)
                                                <button type="button" data-pay-url="{{ route('panel.admin.splits.pay', $split) }}"
                                                    data-receiver="{{ $split->receiver?->name ?? ($typeLabels[$split->receiver_type] ?? 'Destinatário') }}"
                                                    data-amount="R$ {{ number_format((float) $split->amount, 2, ',', '.') }}"
                                                    class="js-pay-split rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700">
                                                    Confirmar
                                                </button>
                                                <button type="button" data-fail-url="{{ route('panel.admin.splits.fail', $split) }}"
                                                    class="js-fail-split rounded-xl border border-rose-200 px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:border-rose-900/40 dark:hover:bg-rose-900/20">
                                                    Falhou
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Concluído</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-16 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Nenhum repasse encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($splits->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">{{ $splits->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
@include('partials.splits-help-registry', ['settingsPaymentsUrl' => route('panel.admin.settings', ['group' => 'gateway'])])
<script>
(function () {
    const modeSelect = document.getElementById('js-panel-payout-mode');
    function syncModeBoxes() {
        if (!modeSelect) return;
        const current = modeSelect.value;
        document.querySelectorAll('.js-panel-mode-box').forEach(function (box) {
            const target = box.getAttribute('data-mode-box');
            box.style.display = (target === current) ? 'block' : 'none';
        });
    }
    if (modeSelect) {
        modeSelect.addEventListener('change', syncModeBoxes);
        syncModeBoxes();
    }
})();

async function postSplitAction(url, payload) {
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

const panelAutoForm = document.getElementById('js-panel-split-automation-form');
if (panelAutoForm) {
    panelAutoForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        const formData = new FormData(panelAutoForm);
        const payload = {
            auto_enabled: formData.get('auto_enabled') ? 1 : 0,
            payout_mode: formData.get('payout_mode'),
            pix_key: formData.get('pix_key'),
            seller_percent: formData.get('seller_percent'),
            platform_percent: formData.get('platform_percent'),
            traffic_percent: formData.get('traffic_percent'),
            percent: formData.get('percent'),
            delay_days: formData.get('delay_days') || 0,
            min_payout: formData.get('min_payout'),
            mp_access_token: formData.get('mp_access_token'),
            mp_customer_id: formData.get('mp_customer_id'),
            mp_card_token: formData.get('mp_card_token'),
            admin_email: formData.get('admin_email')
        };

        try {
            const data = await postSplitAction(panelAutoForm.action, payload);
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

document.querySelectorAll('.js-auto-split').forEach(function (button) {
    button.addEventListener('click', async function () {
        const result = await Swal.fire({
            title: 'Processar repasse automático agora?',
            text: 'O sistema processará automaticamente ' + button.dataset.amount + ' referente à comissão de ' + button.dataset.receiver + '.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, processar agora',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            const data = await postSplitAction(button.dataset.autoUrl);
            await Swal.fire('Processado', data.message, 'success');
            window.location.reload();
        } catch (error) {
            await Swal.fire('Erro no processamento', error.message, 'error');
        }
    });
});

document.querySelectorAll('.js-pay-split').forEach(function (button) {
    button.addEventListener('click', async function () {
        const result = await Swal.fire({
            title: 'Confirmar repasse?',
            text: 'Confirme somente depois da transferência para ' + button.dataset.receiver + ' no valor de ' + button.dataset.amount + '.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, confirmar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            const data = await postSplitAction(button.dataset.payUrl);
            await Swal.fire('Concluído', data.message, 'success');
            window.location.reload();
        } catch (error) {
            await Swal.fire('Erro', error.message, 'error');
        }
    });
});

document.querySelectorAll('.js-fail-split').forEach(function (button) {
    button.addEventListener('click', async function () {
        const result = await Swal.fire({
            title: 'Registrar falha',
            input: 'text',
            inputLabel: 'Motivo da falha do repasse',
            inputPlaceholder: 'Ex.: PIX recusado, conta indisponível, divergência de dados',
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
            const data = await postSplitAction(button.dataset.failUrl, { message: result.value });
            await Swal.fire('Registrado', data.message, 'success');
            window.location.reload();
        } catch (error) {
            await Swal.fire('Erro', error.message, 'error');
        }
    });
});
</script>
@endpush
