@extends('panel.layouts.app')

@section('title', 'Area do Instrutor')
@section('panel_breadcrumb')
    <a href="{{ route('panel.instructor.dashboard') }}"
        class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Instrutor</a>
@endsection

@section('panel_content')
    <div class="space-y-8">
        <div
            class="bg-gradient-to-br from-blue-600 via-indigo-700 to-sky-800 rounded-[2.2rem] p-8 md:p-10 text-white shadow-2xl shadow-blue-900/20 relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-3xl md:text-4xl font-black mb-3">Central do Instrutor</h2>
                <p class="text-blue-100/90 max-w-3xl text-base md:text-lg font-medium leading-relaxed">
                    Gerencie seus cursos, mentorias, eventos, certificados e vendas em um unico lugar dentro do painel
                    do membro.
                </p>
            </div>
            <div
                class="absolute top-0 right-0 -translate-y-1/3 translate-x-1/3 w-80 h-80 bg-white/10 rounded-full blur-3xl">
            </div>
            <div
                class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 w-60 h-60 bg-blue-400/20 rounded-full blur-2xl">
            </div>
        </div>

        <!-- Scanner Quick Access -->
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border-2 border-dashed border-blue-200 dark:border-blue-900/50 p-6 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm hover:border-blue-400 transition-all">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-blue-600/20">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Scanner de Ingressos</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-medium">Valide entradas de seus eventos hoje usando a câmera do celular.</p>
                </div>
            </div>
            <a href="{{ route('panel.instructor.scanner') }}" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-2xl shadow-xl shadow-blue-600/20 transition-all text-center flex items-center justify-center gap-2">
                <i class="fas fa-camera"></i> Abrir Scanner Agora
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <div
                class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Cursos</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $coursesCount }}</p>
            </div>
            <div
                class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Mentorias</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $mentorshipsCount }}</p>
            </div>
            <div
                class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Eventos</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $eventsCount }}</p>
            </div>
            <div
                class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Certificados</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $certificatesCount }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div
                class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Vendas pagas</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $salesCount }}</p>
            </div>
            <div
                class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Faturamento bruto</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">R$
                    {{ number_format($grossSalesTotal, 2, ',', '.') }}</p>
            </div>
            <div
                class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Receita liquida</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">R$
                    {{ number_format($netSalesTotal, 2, ',', '.') }}</p>
            </div>
        </div>

        <div
            class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 md:p-8">
            <h3 class="text-lg md:text-xl font-black text-slate-900 dark:text-white mb-6">Modulos de Gestao</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @php
                    $modules = [
                        [
                            'enabled' => $access['courses'],
                            'route' => route('panel.admin.courses.index'),
                            'icon' => 'fa-graduation-cap',
                            'title' => 'Cursos',
                            'desc' => 'Criar, editar e organizar aulas.'
                        ],
                        [
                            'enabled' => $access['mentorships'],
                            'route' => route('panel.admin.mentorships.index'),
                            'icon' => 'fa-chalkboard-teacher',
                            'title' => 'Mentorias',
                            'desc' => 'Gerenciar agenda e sessoes.'
                        ],
                        [
                            'enabled' => $access['events'],
                            'route' => route('panel.admin.events.index'),
                            'icon' => 'fa-calendar-alt',
                            'title' => 'Eventos',
                            'desc' => 'Publicar e controlar eventos.'
                        ],
                        [
                            'enabled' => $access['certificates'],
                            'route' => route('panel.admin.certificates.index'),
                            'icon' => 'fa-certificate',
                            'title' => 'Certificados',
                            'desc' => 'Emitir e acompanhar certificados.'
                        ],
                        [
                            'enabled' => $access['marketplace'],
                            'route' => route('panel.marketplace.sales'),
                            'icon' => 'fa-receipt',
                            'title' => 'Vendas',
                            'desc' => 'Visualizar pedidos e recebimentos.'
                        ],
                        [
                            'enabled' => $access['marketplace'],
                            'route' => route('panel.marketplace.payments'),
                            'icon' => 'fa-credit-card',
                            'title' => 'Pagamentos',
                            'desc' => 'Conectar e testar gateway.'
                        ],
                    ];
                @endphp

                @foreach($modules as $module)
                    @if($module['enabled'])
                        <a href="{{ $module['route'] }}"
                            class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950 p-5 hover:border-blue-400 dark:hover:border-blue-700 hover:bg-blue-50/50 dark:hover:bg-blue-950/20 transition-all">
                            <div class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-4">
                                <i class="fas {{ $module['icon'] }}"></i>
                            </div>
                            <h4 class="font-bold text-slate-900 dark:text-white">{{ $module['title'] }}</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $module['desc'] }}</p>
                        </a>
                    @else
                        <div
                            class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/30 dark:bg-slate-950/40 p-5 opacity-80">
                            <div class="w-11 h-11 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-400 flex items-center justify-center mb-4">
                                <i class="fas {{ $module['icon'] }}"></i>
                            </div>
                            <h4 class="font-bold text-slate-500 dark:text-slate-400">{{ $module['title'] }}</h4>
                            <p class="text-sm text-slate-400 mt-1">Nao habilitado no seu plano/perfil.</p>
                        </div>
                    @endif
                @endforeach

                @if($access['partnerCoupons'])
                    <a href="{{ route('member.partner.index') }}"
                        class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950 p-5 hover:border-emerald-400 dark:hover:border-emerald-700 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20 transition-all">
                        <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white">Cupons de Parceiros</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Gerencie cupons vinculados ao seu parceiro ({{ $partnerCouponsCount }}).
                        </p>
                    </a>
                @else
                    <div
                        class="rounded-2xl border border-dashed border-amber-300 dark:border-amber-700 bg-amber-50/70 dark:bg-amber-950/20 p-5">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white">Cupons de Parceiros</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Nenhum perfil de parceiro vinculado a sua conta ainda.
                        </p>
                    </div>
                @endif

                @if($access['jobs'])
                    <a href="{{ route('panel.my-jobs.index') }}"
                        class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950 p-5 hover:border-fuchsia-400 dark:hover:border-fuchsia-700 hover:bg-fuchsia-50/50 dark:hover:bg-fuchsia-950/20 transition-all">
                        <div class="w-11 h-11 rounded-xl bg-fuchsia-100 dark:bg-fuchsia-900/30 text-fuchsia-600 dark:text-fuchsia-400 flex items-center justify-center mb-4">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white">Vagas e Candidaturas</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Controle vagas publicadas ({{ $jobsCount }}) e candidatos.
                        </p>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
