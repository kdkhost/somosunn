@extends('panel.layouts.app')

@section('title', 'Usuarios Bloqueados')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                <i class="fas fa-user-slash text-red-500 mr-2"></i>Usuarios Bloqueados
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Gerencie os usuarios que voce bloqueou. Voce pode desbloquea-los a qualquer momento.
            </p>
        </div>
        <a href="{{ route('social.feed') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl transition-all">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    @if($blockedUsers->isEmpty())
        {{-- Estado vazio --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-12 text-center">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                <i class="fas fa-check-circle text-3xl text-green-500"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Nenhum usuario bloqueado</h3>
            <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md mx-auto">
                Voce nao bloqueou nenhum usuario. Quando bloquear alguem na comunidade, ele aparecera aqui.
            </p>
        </div>
    @else
        {{-- Lista --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <span class="text-sm font-bold text-slate-600 dark:text-slate-400">
                    {{ $blockedUsers->count() }} {{ $blockedUsers->count() === 1 ? 'usuario bloqueado' : 'usuarios bloqueados' }}
                </span>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($blockedUsers as $item)
                    <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex items-center gap-4">
                            {{-- Avatar --}}
                            <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-700 flex-shrink-0">
                                @if($item->user->profile_photo)
                                    <img src="{{ asset($item->user->profile_photo) }}" alt="{{ $item->user->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-500 dark:text-slate-400 font-bold text-lg">
                                        {{ strtoupper(substr($item->user->name ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-sm">
                                    {{ $item->user->name }}
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $item->user->email }}
                                </p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                    <i class="fas fa-clock mr-1"></i>Bloqueado em {{ $item->blocked_at ? $item->blocked_at->format('d/m/Y H:i') : '—' }}
                                </p>
                            </div>
                        </div>

                        {{-- Botao desbloquear --}}
                        <button type="button"
                                class="btn-unblock inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold rounded-xl transition-all border border-red-200 dark:border-red-800"
                                data-user-id="{{ $item->user->id }}"
                                data-user-name="{{ $item->user->name }}">
                            <i class="fas fa-unlock"></i> Desbloquear
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
                        $btn.closest('.flex.items-center.justify-between').fadeOut(300, function() {
                            $(this).remove();
                            // Atualiza contador
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
