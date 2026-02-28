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
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Confira as melhores
                    oportunidades exclusivas para nossos membros.</p>
            </div>
        </div>

        {{-- Vacancies Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($vacancies as $vacancy)
                @php
                    $myApplication = $vacancy->applications->first();
                    $status = $myApplication ? ($myApplication->status === 'reviewing' ? 'standby' : $myApplication->status) : null;
                    if ($status === 'accepted') {
                        $status = 'approved';
                    }
                    $statusClasses = match ($status) {
                        'pending' => 'bg-amber-100 dark:bg-amber-900/20 text-amber-700',
                        'standby' => 'bg-blue-100 dark:bg-blue-900/20 text-blue-700',
                        'approved' => 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700',
                        'rejected' => 'bg-red-100 dark:bg-red-900/20 text-red-700',
                        default => 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400',
                    };
                    $statusLabel = match ($status) {
                        'pending' => 'Pendente',
                        'standby' => 'Standby',
                        'approved' => 'Aprovado',
                        'rejected' => 'Recusado',
                        default => 'Nao enviado',
                    };
                @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex flex-col transition-all hover:shadow-xl hover:shadow-blue-500/5 group">
                    <div class="flex items-start justify-between mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl transition-colors">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-widest">
                            {{ $vacancy->type }}
                        </span>
                    </div>

                    <div class="flex-1 space-y-2">
                        <h3
                            class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">
                            {{ $vacancy->title }}
                        </h3>
                        <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <i class="fas fa-building text-xs"></i>
                            <span>{{ $vacancy->company_name ?: 'Confidencial' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <i class="fas fa-map-marker-alt text-xs"></i>
                            <span>{{ $vacancy->location ?: 'Remoto' }}</span>
                        </div>

                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-4 line-clamp-3">
                            {{ $vacancy->short_description }}
                        </p>
                    </div>

                    <div
                        class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between transition-colors">
                        <div class="flex flex-col gap-1">
                            @if($vacancy->salary_range)
                                <div class="text-sm font-bold text-emerald-600">{{ $vacancy->salary_range }}</div>
                            @endif
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <a href="{{ route('panel.jobs.show', $vacancy) }}"
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl hover:bg-blue-600 hover:text-white transition-all">
                            Acompanhar
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center">
                    <div class="text-slate-400 dark:text-slate-600 text-5xl mb-4 opacity-20">
            @empty
                <div class="col-span-full py-12 text-center">
                    <div class="text-slate-400 dark:text-slate-600 text-5xl mb-4 opacity-20">
                        <i class="fas fa-search"></i>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 italic font-medium">Nenhuma vaga disponível no momento. Volte
                        em breve!</p>
                </div>
            @endforelse
        </div>

        @if(method_exists($vacancies, 'links'))
            <div class="mt-8">
                {{ $vacancies->links() }}
            </div>
        @endif
    </div>
@endsection
