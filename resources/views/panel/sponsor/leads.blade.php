@extends('panel.layouts.app')

@section('title', 'Leads do Patrocinador')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Leads do patrocinador</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $sponsor->company?->name }}</p>
        </div>
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950/60"><tr class="text-left text-xs uppercase tracking-[0.18em] text-slate-500"><th class="px-6 py-4">Membro</th><th class="px-6 py-4">Origem</th><th class="px-6 py-4">Evento</th><th class="px-6 py-4">Consentimento</th><th class="px-6 py-4">Data</th></tr></thead>
                <tbody class="divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    @forelse($leads as $lead)
                        <tr>
                            <td class="px-6 py-4">{{ $lead->user?->name ?: '-' }}</td>
                            <td class="px-6 py-4">{{ $lead->source }}</td>
                            <td class="px-6 py-4">{{ $lead->event?->title ?: '-' }}</td>
                            <td class="px-6 py-4">{{ $lead->consent ? 'Aceito' : 'Nao' }}</td>
                            <td class="px-6 py-4">{{ $lead->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">Nenhum lead registrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">{{ $leads->links() }}</div>
        </div>
    </div>
@endsection
