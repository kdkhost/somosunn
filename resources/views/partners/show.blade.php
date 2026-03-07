@extends('layouts.app')

@section('title', $partner->name . ' — Cupons Exclusivos')
@section('description', 'Acesse cupons e descontos exclusivos de ' . $partner->name . ' para membros da plataforma SOMOS UNN.')

@section('content')
    <div class="min-h-screen" style="background:linear-gradient(135deg,#f0f4ff 0%,#f8fafc 60%,#e8f5ff 100%);">

        {{-- Header do Parceiro --}}
        <div class="relative overflow-hidden"
            style="background:linear-gradient(135deg,#0f172a 0%,#1e3a6b 60%,#1f5edb 100%);padding:3rem 1rem 5rem;">
            <div class="max-w-4xl mx-auto relative z-10">
                <a href="{{ route('partners.index') }}"
                    class="inline-flex items-center gap-2 text-blue-300 hover:text-white text-sm font-medium mb-6 transition-colors group">
                    <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Todos os parceiros
                </a>
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                    {{-- Logo --}}
                    <div class="flex-shrink-0"
                        style="background:#ffffff;border:2px solid rgba(255,255,255,0.3);border-radius:20px;padding:1rem;min-width:140px;min-height:80px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(0,0,0,0.2);">
                        @if($partner->logo_url)
                            <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                                style="max-width:120px;max-height:64px;object-fit:contain;">
                        @else
                            <i class="fas fa-building text-slate-400 text-3xl"></i>
                        @endif
                    </div>
                    {{-- Info --}}
                    <div class="text-center md:text-left">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold mb-3"
                            style="background:rgba(96,165,250,0.2);color:#93c5fd;border:1px solid rgba(96,165,250,0.3);">
                            <i class="fas fa-handshake"></i> Parceiro SOMOS UNN
                        </div>
                        <h1 class="text-2xl md:text-3xl font-black text-white mb-2">{{ $partner->name }}</h1>
                        @if($partner->description)
                            <p class="text-slate-300 text-sm max-w-lg">{{ $partner->description }}</p>
                        @endif
                        @if($partner->website_url)
                            <a href="{{ $partner->website_url }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-1.5 mt-3 text-blue-300 hover:text-white text-sm transition-colors">
                                <i class="fas fa-external-link-alt"></i> Visitar site
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <div
                style="position:absolute;top:-60px;right:-60px;width:280px;height:280px;border-radius:50%;background:rgba(31,94,219,0.2);filter:blur(60px);pointer-events:none;">
            </div>
        </div>

        {{-- Conteúdo Principal --}}
        <div class="max-w-4xl mx-auto px-4" style="margin-top:-2.5rem;padding-bottom:3rem;">

            @if(!$user)
                {{-- Não logado --}}
                <div class="text-center rounded-2xl p-10 shadow-xl" style="background:#fff;border:1px solid #e2e8f0;">
                    <i class="fas fa-lock fa-3x mb-4" style="color:#94a3b8;"></i>
                    <h2 class="text-xl font-bold text-slate-700 mb-2">Acesso Exclusivo para Membros</h2>
                    <p class="text-slate-500 mb-6">Faça login e tenha um plano ativo para acessar os cupons exclusivos deste
                        parceiro.</p>
                    <div class="flex items-center justify-center gap-3 flex-wrap">
                        <a href="{{ route('login') }}"
                            class="px-6 py-2.5 rounded-xl font-semibold text-white text-sm transition-all hover:scale-105"
                            style="background:linear-gradient(135deg,#1f5edb,#177fd6);box-shadow:0 4px 15px rgba(31,94,219,0.3);">
                            <i class="fas fa-sign-in-alt mr-1"></i> Entrar
                        </a>
                        <a href="{{ route('register') }}"
                            class="px-6 py-2.5 rounded-xl font-semibold text-white text-sm transition-all hover:scale-105"
                            style="background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 4px 15px rgba(5,150,105,0.3);">
                            <i class="fas fa-user-plus mr-1"></i> Criar conta
                        </a>
                    </div>
                </div>

            @elseif(!$hasActivePlan)
                {{-- Sem plano ativo --}}
                <div class="text-center rounded-2xl p-10 shadow-xl" style="background:#fff;border:1px solid #e2e8f0;">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4"
                        style="background:linear-gradient(135deg,#fef3c7,#fde68a);">
                        <i class="fas fa-crown text-amber-500 text-2xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-700 mb-2">Recurso Exclusivo para Assinantes</h2>
                    <p class="text-slate-500 mb-2">Os cupons exclusivos são um benefício dos nossos membros com plano ativo.</p>
                    <p class="text-slate-400 text-sm mb-6">Assine um plano e desbloqueie descontos especiais aqui e em todos os
                        outros parceiros.</p>
                    <a href="{{ route('marketplace.index') }}"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white text-sm transition-all hover:scale-105"
                        style="background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 4px 15px rgba(245,158,11,0.35);">
                        <i class="fas fa-crown"></i> Ver planos disponíveis
                    </a>
                </div>

            @else
                {{-- Membro adimplente — mostrar cupons --}}
                <div class="mb-5">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold"
                        style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#065f46;">
                        <i class="fas fa-check-circle"></i> Você tem acesso a {{ $coupons->count() }} cupom(ns) exclusivo(s)
                    </div>
                </div>

                @if($coupons->isEmpty())
                    <div class="text-center rounded-2xl p-10 shadow-lg" style="background:#fff;border:1px solid #e2e8f0;">
                        <i class="fas fa-ticket-alt fa-3x mb-4" style="color:#cbd5e1;"></i>
                        <p class="text-slate-400">Nenhum cupom ativo no momento. Volte em breve!</p>
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($coupons as $coupon)
                            <div class="coupon-card rounded-2xl overflow-hidden shadow-lg transition-all duration-300 hover:-translate-y-1"
                                style="background:#fff;border:1.5px solid #e2e8f0;">
                                {{-- Top colorido --}}
                                <div class="flex items-center justify-between px-5 py-3"
                                    style="background:linear-gradient(135deg,#1f5edb,#177fd6);">
                                    <div>
                                        <div class="text-blue-100 text-xs font-semibold uppercase tracking-wider mb-0.5">
                                            {{ $coupon->discount_type === 'percent' ? 'Desconto em %' : 'Valor fixo' }}
                                        </div>
                                        <div class="text-white text-2xl font-black">
                                            {{ $coupon->formatted_discount }}
                                            <span class="text-blue-200 text-sm font-normal ml-1">OFF</span>
                                        </div>
                                    </div>
                                    <i class="fas fa-ticket-alt text-blue-200 text-3xl opacity-60"></i>
                                </div>
                                {{-- Corpo --}}
                                <div class="px-5 py-4">
                                    <h3 class="font-bold text-slate-800 mb-1 text-base">{{ $coupon->title }}</h3>
                                    @if($coupon->description)
                                        <p class="text-slate-500 text-sm mb-3">{{ $coupon->description }}</p>
                                    @endif

                                    {{-- Info badges --}}
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        @if($coupon->min_purchase)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                                                style="background:#f1f5f9;color:#64748b;">
                                                <i class="fas fa-shopping-cart" style="font-size:0.6rem;"></i>
                                                Mín. R$ {{ number_format($coupon->min_purchase, 2, ',', '.') }}
                                            </span>
                                        @endif
                                        @if($coupon->expires_at)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                                                style="background:#fef3c7;color:#92400e;">
                                                <i class="fas fa-clock" style="font-size:0.6rem;"></i>
                                                Até {{ $coupon->expires_at->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Código + copiar --}}
                                    <div class="flex items-center gap-2">
                                        <div class="coupon-code-box flex-grow flex items-center justify-between px-4 py-2.5 rounded-xl cursor-pointer transition-all"
                                            style="background:#f0f7ff;border:2px dashed #93c5fd;user-select:all;"
                                            onclick="copiarCodigo(this, '{{ $coupon->code }}')">
                                            <code class="text-base font-black tracking-widest"
                                                style="color:#1d4ed8;letter-spacing:0.12em;">{{ $coupon->code }}</code>
                                            <i class="fas fa-copy text-blue-400 text-sm ml-2 flex-shrink-0"></i>
                                        </div>
                                        @if($partner->website_url)
                                            <a href="{{ $partner->website_url }}" target="_blank" rel="noopener"
                                                class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-xl transition-all hover:scale-110"
                                                style="background:linear-gradient(135deg,#1f5edb,#177fd6);color:#fff;"
                                                title="Acessar site do parceiro">
                                                <i class="fas fa-external-link-alt text-sm"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>

    <style>
        .coupon-card:hover {
            box-shadow: 0 12px 35px rgba(31, 94, 219, 0.15) !important;
            border-color: #93c5fd !important;
        }

        .coupon-code-box:hover {
            background: #dbeafe !important;
            border-color: #3b82f6 !important;
        }

        .coupon-code-box.copied {
            background: #d1fae5 !important;
            border-color: #6ee7b7 !important;
        }
    </style>

    <script>
        function copiarCodigo(el, codigo) {
            navigator.clipboard.writeText(codigo).then(() => {
                el.classList.add('copied');
                const ico = el.querySelector('i');
                ico.className = 'fas fa-check text-green-500 text-sm ml-2 flex-shrink-0';
                Swal.fire({
                    toast: true, position: 'top-end', timer: 2500, timerProgressBar: true,
                    showConfirmButton: false, icon: 'success',
                    title: `Código <strong>${codigo}</strong> copiado!`,
                    html: `<span class="text-sm text-slate-500">Cole no site do parceiro ao finalizar sua compra.</span>`,
                });
                setTimeout(() => {
                    el.classList.remove('copied');
                    ico.className = 'fas fa-copy text-blue-400 text-sm ml-2 flex-shrink-0';
                }, 2500);
            }).catch(() => {
                Swal.fire({ icon: 'info', title: 'Código: ' + codigo, text: 'Copie manualmente o código acima.' });
            });
        }
    </script>
@endsection