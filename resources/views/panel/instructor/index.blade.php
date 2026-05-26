@extends('panel.layouts.app')

@section('title', 'Area do Instrutor')
@section('panel_breadcrumb')
    <a href="{{ route('panel.instructor.dashboard') }}"
        class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Instrutor</a>
@endsection

@section('panel_content')
    <div class="space-y-8">
        <div
            class="bg-gradient-to-br from-blue-700 via-indigo-800 to-slate-900 dark:from-blue-900/80 dark:via-indigo-950 dark:to-slate-950 rounded-[2.5rem] p-8 md:p-10 text-white shadow-[0_20px_60px_-15px_rgba(37,99,235,0.4)] dark:shadow-none border border-blue-400/20 dark:border-white/5 relative overflow-hidden group">
            <div class="relative z-10">
                <h2 class="text-3xl md:text-4xl font-black mb-3">Central do Instrutor</h2>
                <p class="text-blue-100/90 max-w-3xl text-base md:text-lg font-medium leading-relaxed">
                    Gerencie seus cursos, mentorias, eventos, certificados e vendas em um unico lugar dentro do painel
                    do membro.
                </p>
            </div>
            <div
                class="absolute top-0 right-0 -translate-y-1/3 translate-x-1/3 w-80 h-80 bg-white/10 dark:bg-blue-500/10 rounded-full blur-[100px] group-hover:bg-white/15 transition-all duration-1000 pointer-events-none">
            </div>
            <div
                class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 w-72 h-72 bg-blue-400/20 dark:bg-purple-500/10 rounded-full blur-[80px] group-hover:bg-blue-400/30 transition-all duration-1000 pointer-events-none">
            </div>
        </div>

        <!-- Scanner Quick Access -->
        <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl rounded-[2.5rem] border-2 border-dashed border-blue-200/60 dark:border-slate-700/80 p-6 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm hover:border-blue-400 hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-3xl group-hover:bg-blue-500/10 transition-all duration-700 pointer-events-none"></div>
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
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl rounded-[2rem] border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] hover:-translate-y-1 transition-all duration-300 p-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Cursos</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $coursesCount }}</p>
            </div>
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl rounded-[2rem] border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] hover:-translate-y-1 transition-all duration-300 p-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Mentorias</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $mentorshipsCount }}</p>
            </div>
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl rounded-[2rem] border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] hover:-translate-y-1 transition-all duration-300 p-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Eventos</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $eventsCount }}</p>
            </div>
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl rounded-[2rem] border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] hover:-translate-y-1 transition-all duration-300 p-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Certificados</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $certificatesCount }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl rounded-[2rem] border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] hover:-translate-y-1 transition-all duration-300 p-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Vendas pagas</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $salesCount }}</p>
            </div>
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl rounded-[2rem] border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] hover:-translate-y-1 transition-all duration-300 p-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Faturamento bruto</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">R$
                    {{ number_format($grossSalesTotal, 2, ',', '.') }}</p>
            </div>
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl rounded-[2rem] border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] hover:-translate-y-1 transition-all duration-300 p-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Receita liquida</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">R$
                    {{ number_format($netSalesTotal, 2, ',', '.') }}</p>
            </div>
        </div>

        <div
            class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl rounded-[2.5rem] border border-white/50 dark:border-slate-800/60 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] hover:-translate-y-1 hover:shadow-[0_25px_50px_-12px_rgba(0,0,0,0.1)] transition-all duration-500 p-6 md:p-8 relative overflow-hidden group/board">
            <div class="absolute bottom-0 right-0 w-40 h-40 bg-blue-500/5 rounded-full -mr-20 -mb-20 blur-3xl group-hover/board:bg-blue-500/10 transition-all duration-700 pointer-events-none"></div>
            <div class="relative z-10">
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
                            'enabled' => $access['exhibitors'],
                            'route' => route('panel.admin.events.list'),
                            'icon' => 'fa-store',
                            'title' => 'Areas para expositores',
                            'desc' => 'Configurar espacos, lotes e inscricoes.'
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
                            class="group relative overflow-hidden rounded-[1.5rem] border border-slate-200/80 dark:border-slate-800/80 bg-white/60 dark:bg-slate-900/60 backdrop-blur-2xl p-5 shadow-[0_4px_20px_rgb(0,0,0,0.02)] hover:shadow-[0_15px_30px_rgba(37,99,235,0.08)] hover:-translate-y-1 transition-all duration-300">
                            <div class="absolute inset-0 bg-gradient-to-br from-transparent to-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
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
                        class="group relative overflow-hidden rounded-[1.5rem] border border-slate-200/80 dark:border-slate-800/80 bg-white/60 dark:bg-slate-900/60 backdrop-blur-2xl p-5 shadow-[0_4px_20px_rgb(0,0,0,0.02)] hover:shadow-[0_15px_30px_rgba(16,185,129,0.08)] hover:-translate-y-1 transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-br from-transparent to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
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
                        class="group relative overflow-hidden rounded-[1.5rem] border border-slate-200/80 dark:border-slate-800/80 bg-white/60 dark:bg-slate-900/60 backdrop-blur-2xl p-5 shadow-[0_4px_20px_rgb(0,0,0,0.02)] hover:shadow-[0_15px_30px_rgba(217,70,239,0.08)] hover:-translate-y-1 transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-br from-transparent to-fuchsia-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
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
