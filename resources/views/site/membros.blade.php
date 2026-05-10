@extends('layouts.app')

@section('title', ($pageData['seo_title'] ?? null) ?: 'Membros - UNN')

@section('content')
    @php $pageData = $pageData ?? []; @endphp
    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <!-- Hero -->
        <section class="pt-10 md:pt-20 pb-6 px-4 md:px-8">
            <div class="max-w-7xl mx-auto text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black leading-tight mb-3 unn-title-gradient">
                    {{ ($pageData['hero_title'] ?? null) ?: 'Membros UNN' }}
                </h1>
                <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    {!! ($pageData['hero_subtitle'] ?? null) ?: 'Conecte-se com empreendedores da nossa comunidade exclusiva.' !!}
                </p>
            </div>
        </section>

        @if(isset($isDemo) && $isDemo)
            <div class="max-w-7xl mx-auto px-4 mb-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 flex items-center gap-2 text-sm">
                    <i class="fas fa-info-circle text-yellow-600"></i>
                    <p class="text-yellow-800"><strong>Demonstracao:</strong> Perfis de exemplo. Membros reais aparecerao com cadastros.</p>
                </div>
            </div>
        @endif

        <!-- Stats -->
        @if($pageData['stats_enabled'] ?? true)
            <section class="pb-6 px-4 md:px-8">
                <div class="max-w-7xl mx-auto">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @php
                            $stats = [
                                ['value' => $pageData['stat_1_value'] ?? '500+', 'label' => $pageData['stat_1_label'] ?? 'Empreendedores'],
                                ['value' => $pageData['stat_2_value'] ?? '50+', 'label' => $pageData['stat_2_label'] ?? 'Mentores'],
                                ['value' => $pageData['stat_3_value'] ?? '27', 'label' => $pageData['stat_3_label'] ?? 'Estados'],
                                ['value' => $pageData['stat_4_value'] ?? '1.2k+', 'label' => $pageData['stat_4_label'] ?? 'Conexoes feitas'],
                            ];
                        @endphp
                        @foreach($stats as $stat)
                            <div class="bg-white rounded-xl p-4 text-center shadow-sm border border-slate-100">
                                <p class="text-xl sm:text-2xl font-black text-blue-600">{{ $stat['value'] }}</p>
                                <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- Members -->
        <section class="pb-16 px-4 md:px-8">
            <div class="max-w-7xl mx-auto">

                {{-- Desktop/Tablet Grid (hidden on mobile) --}}
                <div class="hidden sm:grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @forelse($members as $member)
                        @include('site.partials.member-card', ['member' => $member, 'connectionMap' => $connectionMap])
                    @empty
                        <div class="col-span-full text-center py-12">
                            <div class="bg-white rounded-2xl p-10 shadow-sm max-w-sm mx-auto border border-slate-100">
                                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-black text-gray-900 mb-1">Nenhum membro ainda</h3>
                                <p class="text-sm text-gray-500 mb-4">Seja o primeiro!</p>
                                <a href="{{ route('register') }}" class="btn-primary text-white px-6 py-2 rounded-full font-bold text-sm inline-flex items-center gap-2">
                                    <i class="fas fa-user-plus"></i> Fazer parte
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Mobile Swiper (visible only on mobile) --}}
                <div class="sm:hidden">
                    <div class="swiper members-swiper">
                        <div class="swiper-wrapper">
                            @foreach($members as $member)
                                <div class="swiper-slide">
                                    @include('site.partials.member-card', ['member' => $member, 'connectionMap' => $connectionMap])
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination mt-4"></div>
                    </div>
                </div>

                {{-- Paginacao --}}
                @if(method_exists($members, 'hasPages') && $members->hasPages())
                    <div class="mt-8">
                        {{ $members->links() }}
                    </div>
                @endif
            </div>
        </section>

        <!-- CTA -->
        @if($pageData['cta_enabled'] ?? true)
            <section class="py-12 px-4 md:px-8" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                <div class="max-w-3xl mx-auto text-center text-white">
                    <h2 class="text-2xl lg:text-3xl font-black mb-3">{{ $pageData['cta_title'] ?? 'Faca parte desta comunidade' }}</h2>
                    <p class="text-base opacity-90 mb-6">{!! $pageData['cta_subtitle'] ?? 'Conecte-se com empreendedores de sucesso.' !!}</p>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white px-6 py-3 rounded-full font-bold text-sm hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                        <i class="fas fa-rocket"></i> {{ $pageData['cta_btn'] ?? 'Quero fazer parte' }}
                    </a>
                </div>
            </section>
        @endif
    </div>

    <style>
        .unn-title-gradient {
            background: linear-gradient(90deg, #2E3192 0%, #0071BC 60%, #29ABE2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                new Swiper('.members-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 16,
                    autoplay: { delay: 4000, disableOnInteraction: false },
                    pagination: { el: '.swiper-pagination', clickable: true },
                    grabCursor: true,
                    loop: {{ $members->count() > 2 ? 'true' : 'false' }},
                    breakpoints: {
                        480: { slidesPerView: 1.2 },
                    }
                });
            });

            function requestConnection(userId, btn) {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;
                fetch('/connect/' + userId, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { toastr.success(data.message); setTimeout(() => location.reload(), 1000); }
                        else { toastr.error(data.message); btn.innerHTML = originalHtml; btn.disabled = false; }
                    })
                    .catch(() => { toastr.error('Erro ao processar.'); btn.innerHTML = originalHtml; btn.disabled = false; });
            }

            function acceptConnection(userId) {
                fetch('/connection/accept/' + userId, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
                    .then(r => r.json())
                    .then(data => { if (data.success) { toastr.success(data.message); location.reload(); } else { toastr.error(data.message); } });
            }

            function blockUser(userId) {
                Swal.fire({ title: 'Bloquear?', text: 'Este membro nao podera te enviar mensagens.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Bloquear', cancelButtonText: 'Cancelar' })
                    .then(r => { if (r.isConfirmed) { fetch('/connection/block/' + userId, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } }).then(r => r.json()).then(data => { if (data.success) { toastr.success(data.message); location.reload(); } }); } });
            }

            function openChat(userId) { window.location.href = '{{ url("/chat/start") }}/' + userId; }
        </script>
    @endpush
@endsection
