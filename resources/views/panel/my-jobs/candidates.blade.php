@extends('panel.layouts.app')

@section('title', 'Candidatos — ' . $my_job->title)

@section('panel_content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 font-bold uppercase mb-1">
                <a href="{{ route('panel.my-jobs.index') }}" class="hover:text-blue-600 transition-colors">Minhas Vagas</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-slate-900 dark:text-white truncate max-w-xs">{{ $my_job->title }}</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">
                <i class="fas fa-users text-blue-600 mr-2"></i> Candidatos
            </h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                {{ $applications->count() }} {{ $applications->count() === 1 ? 'candidato' : 'candidatos' }} para esta vaga
            </p>
        </div>
        <a href="{{ route('panel.my-jobs.edit', $my_job) }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-2xl font-bold text-sm transition-all">
            <i class="fas fa-pen text-xs"></i> Editar Vaga
        </a>
    </div>

    @if($applications->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-16 text-center shadow-sm border border-slate-100 dark:border-slate-800">
            <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-inbox text-3xl text-slate-400"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Nenhum candidato ainda</h3>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Aguarde os candidatos se inscreverem para sua vaga.</p>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Candidato</th>
                            <th class="px-6 py-4">Data de Candidatura</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Carta de Apresentação</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($applications as $app)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition" id="app-row-{{ $app->id }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($app->user?->avatar)
                                            <img src="{{ asset($app->user->avatar) }}" class="w-10 h-10 rounded-full object-cover" alt="{{ $app->user->name }}">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center font-black text-sm">
                                                {{ strtoupper(substr($app->user?->name ?? 'C', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-bold text-slate-800 dark:text-white">{{ $app->user?->name ?? 'Usuário removido' }}</div>
                                            <div class="text-xs text-slate-400">{{ $app->user?->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium">
                                    {{ $app->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span id="status-badge-{{ $app->id }}"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold
                                        {{ $app->status === 'pending' ? 'bg-amber-100 dark:bg-amber-900/20 text-amber-700' :
                                           ($app->status === 'approved' ? 'bg-green-100 dark:bg-green-900/20 text-green-700' : 'bg-red-100 dark:bg-red-900/20 text-red-700') }}">
                                        {{ $app->status === 'pending' ? 'Pendente' : ($app->status === 'approved' ? 'Aprovado' : 'Recusado') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    @if($app->cover_letter)
                                        <button onclick="showCoverLetter('{{ addslashes($app->cover_letter) }}')"
                                            class="text-blue-600 hover:text-blue-700 text-xs font-bold underline underline-offset-2 transition">
                                            Ver carta
                                        </button>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Download currículo --}}
                                        @if($app->resume_path)
                                            <a href="{{ route('panel.my-jobs.candidates.download', [$my_job, $app]) }}"
                                                title="Baixar Currículo"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                                                <i class="fas fa-download text-xs"></i>
                                            </a>
                                        @endif

                                        {{-- Aprovar --}}
                                        <button onclick="updateStatus({{ $app->id }}, 'approved')"
                                            title="Aprovar"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition">
                                            <i class="fas fa-check text-xs"></i>
                                        </button>

                                        {{-- Recusar --}}
                                        <button onclick="updateStatus({{ $app->id }}, 'rejected')"
                                            title="Recusar"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function showCoverLetter(text) {
    Swal.fire({
        title: 'Carta de Apresentação',
        text: text,
        icon: 'info',
        confirmButtonText: 'Fechar',
        confirmButtonColor: '#3b82f6',
    });
}

function updateStatus(appId, status) {
    const label = status === 'approved' ? 'Aprovar' : 'Recusar';
    Swal.fire({
        title: label + ' candidato?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: label,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: status === 'approved' ? '#22c55e' : '#ef4444',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        fetch('/painel/my-jobs/applications/' + appId + '/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                toastr.success(data.message || 'Status atualizado!');
                // Atualiza badge sem reload
                const badge = document.getElementById('status-badge-' + appId);
                if (badge) {
                    badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold ' +
                        (status === 'approved' ? 'bg-green-100 dark:bg-green-900/20 text-green-700' : 'bg-red-100 dark:bg-red-900/20 text-red-700');
                    badge.textContent = status === 'approved' ? 'Aprovado' : 'Recusado';
                }
            } else {
                Swal.fire('Erro', data.message || 'Não foi possível atualizar.', 'error');
            }
        })
        .catch(function() {
            Swal.fire('Erro de conexão', 'Tente novamente.', 'error');
        });
    });
}
</script>
@endpush
@endsection
