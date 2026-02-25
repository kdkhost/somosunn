@extends('panel.layouts.app')

@section('title', $job->title)

@section('panel_content')
    @php
        $normalizedStatus = $application ? ($application->status === 'reviewing' ? 'standby' : $application->status) : null;
        if ($normalizedStatus === 'accepted') {
            $normalizedStatus = 'approved';
        }
        $visibility = strtolower(trim((string) ($job->visibility ?? 'public')));
        if ($visibility === '') {
            $visibility = 'public';
        }
        $frontendAvailable = in_array($visibility, ['public', 'external', 'both'], true);
        $statusMeta = [
            'pending' => [
                'label' => 'Pendente',
                'badge' => 'bg-amber-100 dark:bg-amber-900/20 text-amber-700',
                'text' => 'Sua candidatura foi recebida e esta aguardando analise inicial.',
            ],
            'standby' => [
                'label' => 'Standby',
                'badge' => 'bg-blue-100 dark:bg-blue-900/20 text-blue-700',
                'text' => 'Sua candidatura esta em observacao para proximas etapas.',
            ],
            'approved' => [
                'label' => 'Aprovado',
                'badge' => 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700',
                'text' => 'Parabens! Sua candidatura foi aprovada para avancar.',
            ],
            'rejected' => [
                'label' => 'Recusado',
                'badge' => 'bg-red-100 dark:bg-red-900/20 text-red-700',
                'text' => 'A vaga seguiu com outro perfil neste momento.',
            ],
        ];
        $currentMeta = $normalizedStatus && isset($statusMeta[$normalizedStatus])
            ? $statusMeta[$normalizedStatus]
            : null;
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">
            <a href="{{ route('panel.jobs.index') }}" class="hover:text-blue-600 transition-colors">Vagas</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-slate-900 dark:text-white">{{ $job->title }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div
                    class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-8">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-3xl transition-colors font-bold">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight transition-colors">
                                    {{ $job->title }}
                                </h1>
                                <div
                                    class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-slate-500 dark:text-slate-400 font-bold transition-colors">
                                    <span><i class="fas fa-building mr-1"></i> {{ $job->company_name ?: 'Confidencial' }}</span>
                                    <span><i class="fas fa-map-marker-alt mr-1"></i> {{ $job->location ?: 'Remoto' }}</span>
                                    <span><i class="fas fa-clock mr-1"></i> {{ $job->type }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 font-medium transition-colors">
                        {!! $job->description !!}
                    </div>

                    @if($job->requirements)
                        <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800 transition-colors">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Requisitos</h3>
                            <div class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 font-medium">
                                {!! $job->requirements !!}
                            </div>
                        </div>
                    @endif

                    @if($job->benefits)
                        <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800 transition-colors">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Beneficios</h3>
                            <div class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 font-medium">
                                {!! $job->benefits !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div
                    class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 sticky top-6 transition-colors">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-5 transition-colors">Andamento da Candidatura</h3>

                    @if($application && $currentMeta)
                        <div class="space-y-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold {{ $currentMeta['badge'] }}">
                                {{ $currentMeta['label'] }}
                            </span>

                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $currentMeta['text'] }}</p>

                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-4">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Data de envio</p>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    {{ $application->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            @if(!blank($application->cover_letter))
                                <div class="rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-4">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Carta enviada</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($application->cover_letter), 180) }}
                                    </p>
                                </div>
                            @endif

                            @if($frontendAvailable)
                                <a href="{{ route('jobs.public.show', $job) }}"
                                    class="block w-full py-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-2xl hover:bg-slate-200 transition-all text-sm text-center">
                                    Ver vaga no frontend
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="space-y-4">
                            <div
                                class="w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl transition-colors">
                                <i class="fas fa-file-upload"></i>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                O envio de curriculo agora acontece pela pagina publica da vaga.
                            </p>
                            @if($frontendAvailable)
                                <a href="{{ route('jobs.public.show', $job) }}#candidatura"
                                    class="block w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl transition-all text-sm text-center">
                                    Enviar curriculo no frontend
                                </a>
                            @else
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Esta vaga nao esta disponivel no frontend para novas candidaturas.
                                </p>
                            @endif
                        </div>
                    @endif

                    <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 space-y-4 transition-colors">
                        <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400">
                            <i class="fas fa-calendar-alt w-4"></i>
                            <span class="text-xs font-bold">Publicada em: {{ $job->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
