@extends('layouts.app')

@section('title', 'Empresas Parceiras')
@section('description', 'Conheça as empresas parceiras da plataforma e acesse cupons de desconto exclusivos para membros.')

@section('content')
    <div class="min-h-screen" style="background: linear-gradient(135deg, #f0f4ff 0%, #f8fafc 60%, #e8f5ff 100%);">

        {{-- Hero --}}
        <div class="relative overflow-hidden pt-16 pb-12"
            style="background: linear-gradient(135deg, #0f172a 0%, #1e3a6b 60%, #1f5edb 100%);">
            <div class="max-w-5xl mx-auto px-4 text-center relative z-10">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold mb-5"
                    style="background:rgba(255,255,255,0.12);color:#93c5fd;border:1px solid rgba(255,255,255,0.2);backdrop-filter:blur(10px);">
                    <i class="fas fa-handshake"></i> ECOSSISTEMA SOMOS UNN
                </div>
                <h1 class="text-3xl md:text-4xl font-black text-white mb-3 leading-tight">
                    Empresas <span style="color:#60a5fa;">Parceiras</span>
                </h1>
                <p class="text-slate-300 text-base max-w-xl mx-auto mb-8">
                    Parceiros exclusivos que oferecem condições especiais para membros da plataforma SOMOS UNN. Clique em um
                    parceiro para ver os cupons.
                </p>

                @guest
                    <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
                        style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);">
                        <i class="fas fa-lock"></i>
                        <a href="{{ route('login') }}" class="text-blue-300 hover:text-white transition-colors">Faça login</a>
                        &nbsp;ou&nbsp;
                        <a href="{{ route('register') }}" class="text-blue-300 hover:text-white transition-colors">assine um
                            plano</a>
                        &nbsp;para acessar os cupons
                    </div>
                @endguest
            </div>
            {{-- Decorative blobs --}}
            <div
                style="position:absolute;top:-60px;right:-60px;width:280px;height:280px;border-radius:50%;background:rgba(31,94,219,0.2);filter:blur(60px);pointer-events:none;">
            </div>
            <div
                style="position:absolute;bottom:-40px;left:-40px;width:200px;height:200px;border-radius:50%;background:rgba(59,130,246,0.15);filter:blur(40px);pointer-events:none;">
            </div>
        </div>

        {{-- Grid de parceiros --}}
        <div class="max-w-6xl mx-auto px-4 py-12">
            @if($partners->isEmpty())
                <div class="text-center py-20">
                    <i class="fas fa-handshake fa-4x text-slate-300 mb-4"></i>
                    <p class="text-slate-400 text-lg">Em breve novos parceiros por aqui!</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5" id="partners-grid">
                    @foreach($partners as $partner)
                        <a href="{{ route('partners.show', $partner->slug) }}"
                            class="partner-card group block rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1"
                            style="background:#fff;border:1.5px solid #e2e8f0;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
                            {{-- Logo --}}
                            <div class="partner-logo-box flex items-center justify-center p-4"
                                style="height:100px;background:#fafbfc;">
                                @if($partner->logo_url)
                                    <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                                        class="max-w-full max-h-full object-contain transition-all duration-300 group-hover:scale-105"
                                        style="max-width:120px;max-height:72px;" loading="lazy">
                                @else
                                    <div class="text-center text-slate-400">
                                        <i class="fas fa-building text-3xl"></i>
                                    </div>
                                @endif
                            </div>
                            {{-- Info --}}
                            <div class="p-3 border-t border-slate-100" style="background:#fff;">
                                <p class="text-xs font-bold text-slate-700 truncate text-center">{{ $partner->name }}</p>
                                @if($partner->active_coupons_count > 0)
                                    <div class="mt-1.5 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                            style="background:linear-gradient(135deg,#dbeafe,#eff6ff);color:#1d4ed8;">
                                            <i class="fas fa-ticket-alt" style="font-size:0.6rem;"></i>
                                            {{ $partner->active_coupons_count }}
                                            cupom{{ $partner->active_coupons_count > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                @else
                                    <p class="text-center text-slate-400" style="font-size:0.65rem;margin-top:4px;">Em breve</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    <style>
        .partner-card:hover {
            box-shadow: 0 8px 30px rgba(31, 94, 219, 0.15) !important;
            border-color: #93c5fd !important;
        }
    </style>
@endsection