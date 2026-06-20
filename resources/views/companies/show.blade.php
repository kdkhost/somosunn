@extends('layouts.app')

@section('title', $company->name . ' - Empresa')
@section('meta_title', $company->name . ' - Empresa')
@section('meta_description', \Illuminate\Support\Str::limit((string) ($company->description ?: 'Conheca a empresa parceira ' . $company->name . ' na plataforma SOMOS UNN.'), 155))

@section('content')
    <div class="min-h-screen bg-slate-50">
        <section class="relative overflow-hidden bg-slate-950 text-white">
            <div class="absolute inset-0 opacity-20" style="background-image:url('{{ $company->banner_url ?: ($store?->banner_url ?? '') }}');background-size:cover;background-position:center;"></div>
            <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-blue-200 hover:text-white">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
                <div class="mt-6 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="flex items-start gap-5">
                        <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-3xl bg-white shadow-xl">
                            @if($company->logo_url)
                                <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="h-full w-full object-contain">
                            @else
                                <i class="fas fa-building text-3xl text-slate-400"></i>
                            @endif
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100">
                                <i class="fas fa-badge-check"></i>
                                <span>{{ $company->verified ? 'Empresa verificada' : 'Perfil empresarial' }}</span>
                            </div>
                            <h1 class="mt-4 text-3xl font-black sm:text-4xl">{{ $company->name }}</h1>
                            <p class="mt-3 max-w-3xl text-sm text-slate-200 sm:text-base">{{ $company->description ?: 'Empresa publicada na rede SOMOS UNN.' }}</p>
                            <div class="mt-4 flex flex-wrap gap-3 text-sm text-blue-100">
                                @if($company->city || $company->state)
                                    <span><i class="fas fa-map-marker-alt mr-1"></i>{{ trim(($company->city ? $company->city . ' - ' : '') . $company->state) }}</span>
                                @endif
                                @if($company->website)
                                    <a href="{{ $company->website }}" target="_blank" rel="noopener"><i class="fas fa-globe mr-1"></i>Site</a>
                                @endif
                                @if($company->instagram)
                                    <a href="{{ $company->instagram }}" target="_blank" rel="noopener"><i class="fab fa-instagram mr-1"></i>Instagram</a>
                                @endif
                                @if($company->linkedin)
                                    <a href="{{ $company->linkedin }}" target="_blank" rel="noopener"><i class="fab fa-linkedin mr-1"></i>LinkedIn</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($owner)
                        <div class="rounded-3xl bg-white/10 px-5 py-4 text-sm shadow-lg backdrop-blur">
                            <div class="text-xs uppercase tracking-[0.18em] text-blue-100">Responsavel</div>
                            <div class="mt-2 text-lg font-bold">{{ $owner->name }}</div>
                            @if($company->email || $owner->email)
                                <div class="mt-1 text-slate-200">{{ $company->email ?: $owner->email }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1.3fr_0.7fr] lg:px-8">
            <div class="space-y-8">
                @if($store)
                    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-black text-slate-900">Vitrine da empresa</h2>
                                <p class="mt-1 text-sm text-slate-500">Produtos, cursos, mentorias e eventos publicados.</p>
                            </div>
                            <a href="{{ route('seller-stores.show', $store->slug) }}" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                                <i class="fas fa-store"></i>
                                <span>Abrir vitrine</span>
                            </a>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Produtos</div>
                                <div class="mt-3 text-3xl font-black text-slate-900">{{ $storefront['products']->count() }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Cursos</div>
                                <div class="mt-3 text-3xl font-black text-slate-900">{{ $storefront['courses']->count() }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Mentorias</div>
                                <div class="mt-3 text-3xl font-black text-slate-900">{{ $storefront['mentorships']->count() }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Eventos</div>
                                <div class="mt-3 text-3xl font-black text-slate-900">{{ $storefront['events']->count() }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($company->activeSponsor)
                    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <h2 class="text-2xl font-black text-slate-900">Patrocinio ativo</h2>
                        <div class="mt-5 grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Plano</div>
                                <div class="mt-2 text-xl font-bold text-slate-900">{{ $company->activeSponsor->plan?->name ?: 'Nao informado' }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Inicio</div>
                                <div class="mt-2 text-xl font-bold text-slate-900">{{ optional($company->activeSponsor->starts_at)->format('d/m/Y') ?: 'Nao informado' }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Termino</div>
                                <div class="mt-2 text-xl font-bold text-slate-900">{{ optional($company->activeSponsor->ends_at)->format('d/m/Y') ?: 'Em aberto' }}</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <aside class="space-y-6">
                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h2 class="text-lg font-black text-slate-900">Contato</h2>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        @if($company->email)
                            <div><i class="fas fa-envelope mr-2 text-blue-600"></i>{{ $company->email }}</div>
                        @endif
                        @if($company->phone)
                            <div><i class="fas fa-phone mr-2 text-blue-600"></i>{{ $company->phone }}</div>
                        @endif
                        @if($company->whatsapp)
                            <div><i class="fab fa-whatsapp mr-2 text-green-500"></i>{{ $company->whatsapp }}</div>
                        @endif
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h2 class="text-lg font-black text-slate-900">Equipe vinculada</h2>
                    <div class="mt-4 space-y-3">
                        @forelse($company->memberships as $membership)
                            <div class="rounded-2xl border border-slate-200 px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $membership->user?->name ?: 'Usuario removido' }}</div>
                                <div class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-400">{{ $membership->role }}</div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Nenhum membro vinculado.</p>
                        @endforelse
                    </div>
                </div>
            </aside>
        </section>
    </div>
@endsection
