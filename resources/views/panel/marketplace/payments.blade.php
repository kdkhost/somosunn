@extends('panel.layouts.app')

@section('title', 'Pagamentos do Marketplace - UNN')

@section('panel_content')
    @php
        $paymentsConfigured = (bool) ($paymentsConfigured ?? false);
        $webhookUrl = (string) ($webhookUrl ?? '');
        $isAdmin = auth()->user() && auth()->user()->isAdmin();
    @endphp

    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors">Pagamentos
                </h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">Configuração compartilhada
                    (multi-tenant) para toda a plataforma.</p>
            </div>
            <a href="{{ route('panel.marketplace.index') }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 mt-6 transition-colors duration-300">
        <div class="flex items-start gap-4">
            <div
                class="w-12 h-12 rounded-2xl {{ $paymentsConfigured ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400' }} flex items-center justify-center transition-colors">
                <i class="fas {{ $paymentsConfigured ? 'fa-check-circle' : 'fa-exclamation-triangle' }} text-xl"></i>
            </div>
            <div class="flex-1">
                <div class="font-extrabold text-slate-900 dark:text-white transition-colors">
                    {{ $paymentsConfigured ? 'MercadoPago habilitado' : 'MercadoPago não configurado' }}
                </div>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">
                    Este sistema utiliza <strong>uma única configuração</strong> do gateway (multi-tenant) para toda a
                    plataforma.
                    Cada venda é registrada com <strong>vendedor</strong> e <strong>tipo</strong> (curso, mentoria, evento e
                    marketplace).
                </p>

                @if($isAdmin)
                    <div class="mt-4">
                        <a href="{{ route('panel.marketplace.payments.edit') }}"
                            class="inline-flex items-center justify-center rounded-full bg-blue-600 text-white px-6 py-3 text-sm font-bold hover:brightness-110 transition-all shadow-lg shadow-blue-500/20">
                            <i class="fas fa-cogs mr-2"></i> Abrir configurações do gateway
                        </a>
                    </div>
                @else
                    <div
                        class="mt-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 p-4 text-sm text-slate-700 dark:text-slate-400 transition-colors">
                        <i class="fas fa-info-circle mr-2 text-slate-500 dark:text-slate-500"></i>
                        As credenciais do gateway são gerenciadas pelos administradores da plataforma.
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($webhookUrl !== '')
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 mt-6 transition-colors duration-300">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2 transition-colors">
                <i class="fas fa-link text-slate-500 dark:text-slate-500"></i> URL de notificação (Webhook)
            </h2>
            <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">Caso precise informar manualmente no painel do
                MercadoPago, utilize:</p>

            <div class="mt-4 flex flex-col md:flex-row gap-3">
                <input id="webhook-url" type="text"
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-800 px-4 py-3 text-sm text-slate-700 dark:text-white bg-slate-50 dark:bg-slate-950 outline-none focus:ring-4 focus:ring-blue-500/10 transition-all"
                    readonly value="{{ $webhookUrl }}">
                <button type="button" id="copy-webhook"
                    class="inline-flex items-center justify-center rounded-2xl bg-slate-900 dark:bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-slate-800 dark:hover:bg-blue-700 transition-all shadow-lg dark:shadow-blue-500/20">
                    <i class="fas fa-copy mr-2"></i> Copiar
                </button>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-500 mt-3 transition-colors">O sistema também envia esta URL
                automaticamente no checkout.</p>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('click', async function (e) {
                    const btn = e.target.closest('#copy-webhook');
                    if (!btn) return;

                    const input = document.getElementById('webhook-url');
                    if (!input) return;

                    try {
                        await navigator.clipboard.writeText(input.value || '');
                        if (typeof toastr !== 'undefined') toastr.success('Copiado!');
                    } catch (err) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Erro', text: 'Não foi possível copiar. Copie manualmente.' });
                        }
                    }
                });
            </script>
        @endpush
    @endif
@endsection