@extends('panel.layouts.app')

@section('title', 'Painel Administrativo')

@section('panel_content')
    <div class="space-y-8">
        {{-- Hero Section --}}
        <div
            class="bg-gradient-to-br from-blue-600 via-indigo-700 to-violet-800 rounded-[2.5rem] p-10 text-white shadow-2xl shadow-blue-500/20 relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-3xl font-bold mb-3">Central de Administração</h2>
                <p class="text-blue-100/90 max-w-2xl text-lg font-medium">
                    Gerencie todos os aspectos da sua plataforma em um único lugar, com interface moderna e ferramentas
                    otimizadas.
                </p>
            </div>
            {{-- Abstract background shapes --}}
            <div
                class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-white/10 rounded-full blur-3xl">
            </div>
            <div
                class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-64 h-64 bg-blue-400/20 rounded-full blur-2xl">
            </div>
        </div>

        {{-- Section: Gestão & Financeiro --}}
        <div>
            <div class="flex items-center gap-3 mb-6 px-2">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-wallet text-sm"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Gestão & Financeiro</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $gestao = [
                        ['route' => 'panel.admin.users.index', 'icon' => 'fa-users-cog', 'color' => 'blue', 'title' => 'Usuários', 'desc' => 'Membros e níveis'],
                        ['route' => 'panel.admin.plans.index', 'icon' => 'fa-gem', 'color' => 'indigo', 'title' => 'Planos', 'desc' => 'Pacotes e preços'],
                        ['route' => 'panel.admin.orders.index', 'icon' => 'fa-shopping-basket', 'color' => 'emerald', 'title' => 'Vendas', 'desc' => 'Pedidos realizados'],
                        ['route' => 'panel.admin.invoices.index', 'icon' => 'fa-file-invoice-dollar', 'color' => 'amber', 'title' => 'Faturas', 'desc' => 'Gestão de faturamento'],
                    ];
                @endphp

                @foreach($gestao as $item)
                    <a href="{{ route($item['route']) }}"
                        class="group bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/60 dark:border-slate-800 shadow-sm hover:shadow-xl hover:shadow-{{ $item['color'] }}-500/5 hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-14 h-14 rounded-2xl bg-{{ $item['color'] }}-50 dark:bg-{{ $item['color'] }}-900/20 text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas {{ $item['icon'] }} text-xl"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-lg mb-1">{{ $item['title'] }}</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed">{{ $item['desc'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Section: Conteúdo --}}
        <div>
            <div class="flex items-center gap-3 mb-6 px-2">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <i class="fas fa-layer-group text-sm"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Educação & Conteúdo</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $conteudo = [
                        ['route' => 'panel.admin.courses.index', 'icon' => 'fa-graduation-cap', 'color' => 'purple', 'title' => 'Cursos', 'desc' => 'Trilhas e aulas'],
                        ['route' => 'panel.admin.mentorships.index', 'icon' => 'fa-chalkboard-teacher', 'color' => 'fuchsia', 'title' => 'Mentorias', 'desc' => 'Agenda e sessões'],
                        ['route' => 'panel.admin.events.index', 'icon' => 'fa-calendar-day', 'color' => 'rose', 'title' => 'Eventos', 'desc' => 'Workshops e lives'],
                        ['route' => 'panel.admin.certificates.index', 'icon' => 'fa-award', 'color' => 'orange', 'title' => 'Certificados', 'desc' => 'Emissão e design'],
                    ];
                @endphp

                @foreach($conteudo as $item)
                    <a href="{{ route($item['route']) }}"
                        class="group bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/60 dark:border-slate-800 shadow-sm hover:shadow-xl hover:shadow-{{ $item['color'] }}-500/5 hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-14 h-14 rounded-2xl bg-{{ $item['color'] }}-50 dark:bg-{{ $item['color'] }}-900/20 text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas {{ $item['icon'] }} text-xl"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-lg mb-1">{{ $item['title'] }}</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed">{{ $item['desc'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Section: Ajustes Técnicos --}}
        <div>
            <div class="flex items-center gap-3 mb-6 px-2">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center">
                    <i class="fas fa-sliders-h text-sm"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Ajustes & Configurações</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $ajustes = [
                        ['route' => 'panel.admin.settings', 'icon' => 'fa-sliders-h', 'color' => 'slate', 'title' => 'Gerais', 'desc' => 'Identidade e dados', 'param' => ['group' => 'general']],
                        ['route' => 'panel.admin.settings', 'icon' => 'fa-credit-card', 'color' => 'sky', 'title' => 'Gateways', 'desc' => 'Meios de pagamento', 'param' => ['group' => 'gateway']],
                        ['route' => 'panel.admin.settings', 'icon' => 'fa-envelope-open-text', 'color' => 'cyan', 'title' => 'SMTP', 'desc' => 'Envio de e-mails', 'param' => ['group' => 'smtp']],
                        ['route' => 'panel.admin.mailtemplates.index', 'icon' => 'fa-at', 'color' => 'violet', 'title' => 'Templates', 'desc' => 'Design das mensagens'],
                    ];
                @endphp

                @foreach($ajustes as $item)
                    <a href="{{ route($item['route'], $item['param'] ?? []) }}"
                        class="group bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/60 dark:border-slate-800 shadow-sm hover:shadow-xl hover:shadow-{{ $item['color'] }}-500/5 hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-13 h-13 rounded-2xl bg-{{ $item['color'] }}-50 dark:bg-{{ $item['color'] }}-900/20 text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas {{ $item['icon'] }} text-xl"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-lg mb-1">{{ $item['title'] }}</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed">{{ $item['desc'] }}</p>
                    </a>
                @endforeach

                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.dashboard') }}" target="_blank"
                        class="group bg-slate-50 dark:bg-slate-950 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 border-dashed hover:border-blue-300 dark:hover:border-blue-700 hover:bg-blue-50/30 dark:hover:bg-blue-900/20 transition-all duration-300">
                        <div
                            class="w-13 h-13 rounded-2xl bg-white dark:bg-slate-900 text-slate-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas fa-external-link-alt text-lg"></i>
                        </div>
                        <h4 class="font-bold text-slate-500 dark:text-slate-400 text-lg mb-1">Painel Legacy</h4>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium italic">Acesso fallback ao AdminLTE</p>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection