@extends('panel.layouts.app')

@section('title', 'Painel do Membro - UNN')

@section('panel_content')
    @php
        $user = auth()->user();
        $plan = $plan ?? null;
        $stats = $stats ?? [];

        $isImpersonatingAdmin = session()->has('impersonator_id') && session()->get('impersonator_is_admin');
        $isAdminUser = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
        $isSuperadminUser = $user && (($user->role ?? '') === 'superadmin' || ($user->level ?? '') === 'superadmin');
        $roleLabel = $isSuperadminUser ? 'Super Admin' : ($isAdminUser ? 'Administrador' : null);

        $canAccessCommunity = ($user && $user->canAccessFeature('community')) || $isImpersonatingAdmin;
        $canAccessCourses = ($user && $user->canAccessFeature('courses_access'))
            || ($user && method_exists($user, 'hasPurchasedCourses') && $user->hasPurchasedCourses())
            || $isImpersonatingAdmin;
        $canSellOnMarketplace = ($user && method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace()) || $isImpersonatingAdmin;

        $profileComplete = $user && method_exists($user, 'isProfileComplete') ? (bool) $user->isProfileComplete() : true;

        $coursesCount = (int) ($stats['courses_count'] ?? 0);
        $ordersPaidCount = (int) ($stats['orders_paid_count'] ?? 0);
        $ordersPaidTotal = (float) ($stats['orders_paid_total'] ?? 0);
        $sellerPaidCount = (int) ($stats['seller_paid_count'] ?? 0);
        $sellerNetTotal = (float) ($stats['seller_net_total'] ?? 0);
    @endphp

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6" id="painel-dashboard">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between flex-wrap w-full">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap min-w-0">
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 truncate">Olá, {{ $user?->name }}!</h1>
                    @if($roleLabel)
                        <span class="inline-flex items-center rounded-full bg-slate-900/5 px-3 py-1 text-xs font-extrabold text-slate-700">
                            <i class="fas fa-shield-alt mr-2 opacity-70"></i> {{ $roleLabel }}
                        </span>
                    @endif
                </div>
                <p class="text-slate-600 mt-1">
                    @if($roleLabel)
                        Acesso administrativo liberado neste painel.
                    @else
                        {{ $plan?->name ? 'Plano ativo: ' . $plan->name : 'Você ainda não possui um plano ativo.' }}
                    @endif
                </p>
            </div>
            <div class="flex flex-col gap-2 w-full mt-4 md:mt-0">
                <a href="{{ route('panel.profile.edit') }}"
                    class="inline-flex items-center justify-center rounded-full bg-[#1F5EDB] px-4 py-2 text-xs sm:text-sm font-bold text-white hover:brightness-110 transition w-full">
                    <i class="fas fa-user-edit mr-2"></i> Editar perfil
                </a>
                <a href="{{ route('premium') }}"
                    class="inline-flex items-center justify-center rounded-full border border-[#1F5EDB] px-4 py-2 text-xs sm:text-sm font-bold text-[#1F5EDB] hover:bg-[#1F5EDB]/10 transition w-full">
                    <i class="fas fa-crown mr-2"></i> Ver planos
                </a>
                @if(!$isSuperadminUser)
                    <a href="{{ route('panel.admin') }}"
                        class="inline-flex items-center justify-center rounded-full border border-slate-300 px-4 py-2 text-xs sm:text-sm font-bold text-slate-700 hover:bg-slate-100 transition w-full">
                        <i class="fas fa-layer-group mr-2"></i> Painel completo
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(!$profileComplete && !$roleLabel)
        <div class="mt-6 bg-amber-50 border border-amber-100 rounded-3xl p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <div class="font-extrabold text-amber-900">Complete seu perfil</div>
                <div class="text-sm text-amber-800 mt-1">
                    Preencha seus dados para melhorar sua experiência e liberar recursos do seu plano.
                </div>
            </div>
            <a href="{{ route('panel.profile.edit') }}"
                class="inline-flex items-center justify-center rounded-full bg-amber-600 px-5 py-2.5 text-sm font-extrabold text-white hover:bg-amber-700 transition">
                <i class="fas fa-pen mr-2"></i> Atualizar agora
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mt-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs sm:text-sm font-bold text-slate-500">Meus cursos</div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $coursesCount }}</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#1F5EDB]/10 flex items-center justify-center text-[#1F5EDB]">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-slate-600">
                @if($canAccessCourses)
                    Acesse seus cursos em <a class="text-[#1F5EDB] font-bold hover:underline" href="{{ route('courses.index') }}">Cursos</a>.
                @else
                    Libere o acesso aos cursos fazendo um upgrade de plano.
                @endif
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs sm:text-sm font-bold text-slate-500">Compras pagas</div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $ordersPaidCount }}</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-slate-600">
                Total: <span class="font-extrabold text-slate-900">R$ {{ number_format($ordersPaidTotal, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs sm:text-sm font-bold text-slate-500">Comunidade</div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">UNN</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900/5 flex items-center justify-center text-slate-700">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                @if($canAccessCommunity)
                    <a href="{{ route('social.feed') }}"
                        class="inline-flex items-center justify-center rounded-full bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800 transition">
                        <i class="fas fa-arrow-right mr-2"></i> Ir para o feed
                    </a>
                @else
                    <a href="{{ route('premium') }}"
                        class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                        <i class="fas fa-crown mr-2"></i> Fazer upgrade
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs sm:text-sm font-bold text-slate-500">Minhas vendas</div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $sellerPaidCount }}</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-600">
                    <i class="fas fa-receipt text-xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-slate-600">
                Líquido: <span class="font-extrabold text-slate-900">R$ {{ number_format($sellerNetTotal, 2, ',', '.') }}</span>
            </div>
            @if($canSellOnMarketplace)
                <div class="mt-3">
                    <a href="{{ route('panel.marketplace.sales') }}"
                        class="inline-flex items-center text-sm font-bold text-[#1F5EDB] hover:underline">
                        Ver detalhes <i class="fas fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
            @else
                <div class="mt-3 text-xs text-slate-500">
                    Ative um plano com permissão de vendas para liberar.
                </div>
            @endif
        </div>

        @if($roleLabel)
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5 md:col-span-2 xl:col-span-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="text-xs sm:text-sm font-bold text-slate-500">Administração</div>
                        <div class="text-lg sm:text-xl font-extrabold text-slate-900 mt-1">Atalhos rápidos</div>
                        <div class="text-xs sm:text-sm text-slate-600 mt-1">
                            Acesse configurações e módulos administrativos sem perder o padrão do novo painel.
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 w-full sm:flex-row sm:flex-wrap">
                        <a href="{{ route('panel.admin') }}"
                            class="inline-flex items-center justify-center rounded-full bg-slate-900 px-5 py-2.5 text-sm font-extrabold text-white hover:bg-slate-800 transition w-full sm:w-auto">
                            <i class="fas fa-th-large mr-2"></i> Painel Admin
                        </a>
                        <a href="{{ route('panel.admin', ['to' => 'settings/general']) }}"
                            class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition w-full sm:w-auto">
                            <i class="fas fa-cogs mr-2"></i> Configurações
                        </a>
                        <a href="{{ route('panel.admin', ['to' => 'settings/gateway']) }}"
                            class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition w-full sm:w-auto">
                            <i class="fas fa-credit-card mr-2"></i> Gateway
                        </a>
                        <a href="{{ route('panel.admin', ['to' => 'mailtemplates']) }}"
                            class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition w-full sm:w-auto">
                            <i class="fas fa-at mr-2"></i> E-mails
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
