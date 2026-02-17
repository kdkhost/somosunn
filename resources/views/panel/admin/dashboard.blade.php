@extends('panel.layouts.app')

@section('title', 'Painel Administrativo')

@section('panel_content')
    <div class="space-y-6">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-8 text-white shadow-xl shadow-blue-500/20">
            <h2 class="text-2xl font-bold mb-2">Área Administrativa</h2>
            <p class="text-blue-100 opacity-90">
                Bem-vindo à área de administração integrada ao painel. Aqui você pode gerenciar configurações essenciais.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Settings -->
            <a href="{{ route('panel.admin.settings', ['group' => 'general']) }}"
                class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition group">
                <div
                    class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-cogs text-xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-1">Configurações Gerais</h3>
                <p class="text-sm text-slate-500">Dados da empresa, endereço e informações básicas.</p>
            </a>

            <!-- Gateway -->
            <a href="{{ route('panel.admin.settings', ['group' => 'gateway']) }}"
                class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition group">
                <div
                    class="w-12 h-12 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-credit-card text-xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-1">Pagamentos</h3>
                <p class="text-sm text-slate-500">Configure gateways (MercadoPago, PagSeguro) e taxas.</p>
            </a>

            <!-- SMTP -->
            <a href="{{ route('panel.admin.settings', ['group' => 'smtp']) }}"
                class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition group">
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-envelope text-xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-1">SMTP</h3>
                <p class="text-sm text-slate-500">Servidor de e-mail para notificações e transações.</p>
            </a>

            <!-- Mail Templates -->
            <a href="{{ route('panel.admin.mailtemplates.index') }}"
                class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition group">
                <div
                    class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-at text-xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-1">Templates de E-mail</h3>
                <p class="text-sm text-slate-500">Personalize os e-mails enviados pelo sistema.</p>
            </a>

            <!-- Link to old panel if needed -->
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.dashboard') }}" target="_blank"
                    class="bg-slate-50 p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition group border-dashed">
                    <div
                        class="w-12 h-12 rounded-2xl bg-slate-200 text-slate-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-external-link-alt text-xl"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-lg mb-1">Painel Legacy</h3>
                    <p class="text-sm text-slate-500">Acessar painel completo (AdminLTE).</p>
                </a>
            @endif
        </div>
    </div>
@endsection