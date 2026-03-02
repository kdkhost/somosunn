@extends('layouts.app')

@section('title', 'Solicitações de Compartilhamento')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-10 space-y-8">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Compartilhamentos Pendentes</h1>
                <p class="text-slate-500 text-sm mt-1">Membros que querem publicar algo na sua linha do tempo.</p>
            </div>
            <a href="{{ route('social.feed') }}"
               class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600 font-semibold transition-colors">
                <i class="fas fa-arrow-left"></i> Voltar ao feed
            </a>
        </div>

        {{-- Listagem --}}
        @forelse($requests as $sr)
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row gap-6"
                 id="share-req-{{ $sr->id }}">

                {{-- Quem enviou --}}
                <div class="flex items-start gap-4 flex-1">
                    <img src="{{ $sr->fromUser?->profile_photo_url ?? asset('img/default-user.svg') }}"
                         class="w-12 h-12 rounded-2xl object-cover shrink-0 border border-slate-100"
                         onerror="this.src='{{ asset('img/default-user.svg') }}'">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-900">{{ $sr->fromUser?->name }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $sr->created_at->diffForHumans() }} · expira em {{ $sr->expires_at?->format('d/m/Y') }}</p>

                        @if($sr->message)
                            <p class="mt-2 text-sm text-slate-600 italic bg-slate-50 rounded-xl px-3 py-2">"{{ $sr->message }}"</p>
                        @endif

                        {{-- Preview do post --}}
                        @if($sr->post)
                            <div class="mt-3 border border-slate-100 rounded-xl p-3 bg-slate-50 text-sm text-slate-700">
                                <p class="font-semibold text-xs text-slate-400 mb-1">Post de {{ $sr->post->user?->name }}</p>
                                <p class="line-clamp-3">{{ $sr->post->content }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Ações --}}
                <div class="flex sm:flex-col gap-3 sm:justify-center shrink-0">
                    <form action="{{ route('social.share-requests.approve', $sr) }}" method="POST" class="flex-1 sm:flex-none">
                        @csrf
                        <button type="submit"
                            class="w-full bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold px-6 py-2.5 rounded-2xl transition-all active:scale-95 shadow-sm shadow-violet-500/20">
                            <i class="fas fa-check mr-1"></i> Aprovar
                        </button>
                    </form>
                    <form action="{{ route('social.share-requests.reject', $sr) }}" method="POST" class="flex-1 sm:flex-none">
                        @csrf
                        <button type="submit"
                            class="w-full bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-600 text-sm font-bold px-6 py-2.5 rounded-2xl transition-all active:scale-95">
                            <i class="fas fa-times mr-1"></i> Recusar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-200 rounded-3xl p-16 text-center shadow-sm">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-share-alt text-2xl text-slate-300"></i>
                </div>
                <p class="font-bold text-slate-500">Nenhuma solicitação pendente</p>
                <p class="text-sm text-slate-400 mt-1">Quando alguém quiser compartilhar algo com você, aparecerá aqui.</p>
            </div>
        @endforelse

        @if($requests->hasPages())
            <div>{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
