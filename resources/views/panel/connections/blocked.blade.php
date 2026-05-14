@extends('panel.layouts.app')

@section('title', 'Usuarios Bloqueados')

@section('panel_breadcrumb')
    <span class="text-slate-500 dark:text-slate-400">Bloqueados</span>
@endsection

@section('panel_content')
    <div class="space-y-6">

        {{-- HERO CARD --}}
        <div class="relative overflow-hidden rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 bg-gradient-to-br from-rose-600 via-rose-700 to-slate-900">
            <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-16 -left-16 w-64 h-64 rounded-full bg-rose-300/10 blur-3xl"></div>

            <div class="relative p-6 md:p-8">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-5">
                    <div class="flex h-16 w-16 md:h-20 md:w-20 items-center justify-center rounded-2xl bg-white/15 backdrop-blur border border-white/20 shrink-0">
                        <i class="fas fa-user-slash text-white text-2xl md:text-3xl"></i>
                    </div>

                    <div class="flex-1 min-w-0 text-center md:text-left">
                        <p class="text-rose-200 text-xs font-black uppercase tracking-widest mb-1">Comunidade</p>
                        <h1 class="text-2xl md:text-3xl font-black text-white">Usuarios Bloqueados</h1>
                        <p class="mt-2 text-sm text-rose-100/90 max-w-2xl">
                            Gerencie os usuarios que voce bloqueou. Voce pode desbloquea-los a qualquer momento.
                        </p>
                    </div>

                    <div class="md:ml-auto flex-shrink-0 w-full md:w-auto">
                        <a href="{{ route('social.feed') }}"
                           class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-sm font-black text-white bg-white/15 hover:bg-white/25 backdrop-blur border border-white/20 transition-all">
                            <i class="fas fa-arrow-left"></i>
                            <span>Voltar ao Feed</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($blockedUsers->isEmpty())
            {{-- ESTADO VAZIO --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-12 text-center">
                <div class="mx-auto w-20 h-20 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center mb-5">
                    <i class="fas fa-check-circle text-3xl text-emerald-500"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Nenhum usuario bloqueado</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md mx-auto">
                    Voce nao bloqueou nenhum usuario. Quando bloquear alguem na comunidade, ele aparecera aqui.
                </p>
                <a href="{{ route('social.feed') }}"
                   class="mt-6 inline-flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-black text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all">
                    <i class="fas fa-newspaper"></i>
                    <span>Ir para o feed</span>
                </a>
            </div>
        @else
            {{-- LISTA DE BLOQUEADOS --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400">
                            <i class="fas fa-list"></i>
                        </div>
                        <div>
                            <h2 class="font-black text-slate-900 dark:text-white">Lista de Bloqueados</h2>
                            <p class="text-xs text-slate-400">
                                {{ $blockedUsers->count() }} {{ $blockedUsers->count() === 1 ? 'usuario bloqueado' : 'usuarios bloqueados' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($blockedUsers as $item)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                {{-- Avatar --}}
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl overflow-hidden bg-slate-200 dark:bg-slate-700 shrink-0 ring-2 ring-rose-200/50 dark:ring-rose-900/40">
                                    @if($item->user->profile_photo)
                                        <img src="{{ asset($item->user->profile_photo) }}" alt="{{ $item->user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-slate-500 dark:text-slate-400 font-black text-lg">
                                            {{ strtoupper(substr($item->user->name ?? '?', 0, 1)) }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-black text-slate-900 dark:text-white text-sm truncate">
                                        {{ $item->user->name }}
                                    </h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                        {{ $item->user->email }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1">
                                        <i class="fas fa-clock"></i>
                                        Bloqueado em {{ $item->blocked_at ? $item->blocked_at->format('d/m/Y H:i') : '—' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Botao desbloquear --}}
                            <button type="button"
                                    class="btn-unblock inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 border border-rose-200 dark:border-rose-800 transition-all"
                                    data-user-id="{{ $item->user->id }}"
                                    data-user-name="{{ $item->user->name }}">
                                <i class="fas fa-unlock"></i>
                                <span>Desbloquear</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
$(function() {
    $(document).on('click', '.btn-unblock', function() {
        var $btn = $(this);
        var userId = $btn.data('user-id');
        var userName = $btn.data('user-name');

        Swal.fire({
            title: 'Desbloquear usuario?',
            html: 'Voce quer desbloquear <strong>' + userName + '</strong>?<br><small class="text-muted">Ele podera enviar conexoes e mensagens novamente.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-unlock"></i> Sim, desbloquear',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.post('/connection/unblock/' + userId, { _token: '{{ csrf_token() }}' }, function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Desbloqueado!',
                        text: userName + ' foi desbloqueado com sucesso.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        // Remove a linha do usuario desbloqueado
                        $btn.closest('div.flex.flex-col, div.flex.items-center').fadeOut(300, function() {
                            $(this).remove();
                            var remaining = $('.btn-unblock').length;
                            if (remaining === 0) {
                                location.reload();
                            }
                        });
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Erro', text: res.message || 'Nao foi possivel desbloquear.' });
                }
            }).fail(function() {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha na comunicacao com o servidor.' });
            });
        });
    });
});
</script>
@endpush
