@extends('panel.layouts.app')

@section('title', 'Mural de Vagas')

@section('panel_content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    Mural de Vagas
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Gerencie as oportunidades de
                    emprego publicadas no sistema.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.jobs.create') }}"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <span>Nova Vaga</span>
                </a>
            </div>
        </div>

        {{-- Vacancies Table --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50/50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 transition-colors">
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                Vaga</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                Empresa</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">
                                Candidatos</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">
                                Status</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">
                                Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 transition-colors">
                        @forelse($vacancies as $vacancy)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30 transition-all group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center transition-colors">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $vacancy->title }}
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $vacancy->type }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="text-sm text-slate-600 dark:text-slate-300 font-medium">{{ $vacancy->company_name ?: 'Própria' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                        {{ $vacancy->applications_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $vacancy->is_active ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                                        {{ $vacancy->is_active ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('panel.admin.jobs.edit', $vacancy) }}"
                                            class="p-2 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-all">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('panel.admin.jobs.destroy', $vacancy) }}" method="POST"
                                            class="inline delete-form">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                class="p-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-all btn-delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400 italic">
                                    Nenhuma vaga cadastrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function () {
                    Swal.fire({
                        title: 'Tem certeza?',
                        text: "Você não poderá reverter esta ação!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sim, deletar!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.closest('form').submit();
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
