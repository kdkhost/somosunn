@extends('panel.layouts.app')

@section('title', 'Painel do Membro - UNN')



@section('panel_content')
    @php
        $plan = $plan ?? null;
        $stats = $stats ?? [];
        $isImpersonatingAdmin = session()->has('impersonator_id') && session()->get('impersonator_is_admin');
        $canAccessCommunity = auth()->user()->canAccessFeature('community') || $isImpersonatingAdmin;
        $canAccessCourses = auth()->user()->canAccessFeature('courses_access') || (method_exists(auth()->user(), 'hasPurchasedCourses') && auth()->user()->hasPurchasedCourses()) || $isImpersonatingAdmin;
        $canSellOnMarketplace = auth()->user()->canSellOnMarketplace() || $isImpersonatingAdmin;
        $coursesCount = (int) ($stats['courses_count'] ?? 0);
        $ordersPaidCount = (int) ($stats['orders_paid_count'] ?? 0);
        $ordersPaidTotal = (float) ($stats['orders_paid_total'] ?? 0);
        $sellerPaidCount = (int) ($stats['seller_paid_count'] ?? 0);
        $sellerNetTotal = (float) ($stats['seller_net_total'] ?? 0);
    @endphp

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Olá, {{ auth()->user()->name }}!</h1>
                <p class="text-slate-600 mt-1">
                    {{ $plan?->name ? 'Plano ativo: ' . $plan->name : 'Você ainda não possui um plano ativo.' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('panel.profile.edit') }}"
                    class="inline-flex items-center justify-center rounded-full bg-[#1F5EDB] px-5 py-2.5 text-sm font-bold text-white hover:brightness-110 transition">
                    <i class="fas fa-user-edit mr-2"></i> Editar perfil
                </a>
                <a href="{{ route('premium') }}"
                    class="inline-flex items-center justify-center rounded-full border border-[#1F5EDB] px-5 py-2.5 text-sm font-bold text-[#1F5EDB] hover:bg-[#1F5EDB]/10 transition">
                    <i class="fas fa-crown mr-2"></i> Ver planos
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mt-6">
        @component('components.panel-widget', [
            'title' => 'Meus cursos',
            'value' => '<span id="counter-curso">' . $coursesCount . '</span>',
            'icon' => 'fas fa-graduation-cap',
            'iconBg' => 'bg-[#1F5EDB]/10',
            'iconColor' => 'text-[#1F5EDB]'
        ])
            @if($canAccessCourses)
                Acesse seus cursos em <a class="text-[#1F5EDB] font-bold hover:underline" href="{{ route('courses.index') }}">Cursos</a>.
            @else
                Libere o acesso aos cursos fazendo um upgrade de plano.
            @endif
        @endcomponent

        @component('components.panel-widget', [
            'title' => 'Compras pagas',
            'value' => $ordersPaidCount,
            'icon' => 'fas fa-check-circle',
            'iconBg' => 'bg-emerald-500/10',
            'iconColor' => 'text-emerald-600'
        ])
            Total: <span class="font-extrabold text-slate-900">R$ {{ number_format($ordersPaidTotal, 2, ',', '.') }}</span>
        @endcomponent

        @component('components.panel-widget', [
            'title' => 'Comunidade',
            'value' => 'UNN',
            'icon' => 'fas fa-users',
            'iconBg' => 'bg-slate-900/5',
            'iconColor' => 'text-slate-700'
        ])
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
        @endcomponent

        @component('components.panel-widget', [
            'title' => 'Minhas vendas',
            'value' => $sellerPaidCount,
            'icon' => 'fas fa-receipt',
            'iconBg' => 'bg-amber-500/10',
            'iconColor' => 'text-amber-600'
        ])
            Líquido: <span class="font-extrabold text-slate-900">R$ {{ number_format($sellerNetTotal, 2, ',', '.') }}</span>
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
        @endcomponent
    </div>
@endsection

@push('scripts')
<!-- Laravel Echo + Pusher CDN -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
<script>
    // Configurar Pusher/Echo
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ env('PUSHER_APP_KEY') }}',
        cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
        forceTLS: true
    });
    // IDs dos elementos dos contadores
    const counters = {
        'curso': document.getElementById('counter-curso'),
        // Adicione outros tipos conforme necessário
    };
    window.Echo.channel('service-visits')
        .listen('ServiceVisitRegistered', (e) => {
            if (e.serviceType && counters[e.serviceType] && (!e.serviceId || counters[e.serviceType].dataset?.id == e.serviceId)) {
                counters[e.serviceType].textContent = e.count;
            }
        });
</script>
@endpush
