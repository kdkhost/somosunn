@extends('layouts.app')

@section('title', 'Mural de Vagas Abertas')

@section('content')
    <div class="py-20 bg-slate-50">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center mb-16">
                <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-6">Oportunidades de Carreira</h1>
                <p class="text-xl text-slate-600">Confira as vagas abertas em nossa rede de empresas parceiras e na
                    comunidade SOMOS UNN.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @forelse($vacancies as $job)
                    <div
                        class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100 flex flex-col transition-all hover:-translate-y-2">
                        <div class="flex items-center justify-between mb-6">
                            <span
                                class="px-4 py-1.5 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider">
                                {{ $job->type }}
                            </span>
                            <span class="text-xs font-bold text-slate-400">
                                <i class="far fa-calendar-alt mr-1"></i> {{ $job->created_at->format('d/m/Y') }}
                            </span>
                        </div>

                        <h3 class="text-2xl font-bold text-slate-900 mb-2">{{ $job->title }}</h3>
                        <p class="text-blue-600 font-bold mb-4">{{ $job->company_name ?? 'Empresa Confidencial' }}</p>

                        <div class="flex items-center gap-4 text-sm text-slate-500 mb-6 font-medium">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-map-marker-alt text-slate-300"></i> {{ $job->location ?? 'Não informado' }}
                            </span>
                            @if($job->salary_range)
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-coins text-slate-300"></i> {{ $job->salary_range }}
                                </span>
                            @endif
                        </div>

                        <p class="text-slate-600 mb-8 line-clamp-3">
                            {{ $job->short_description ?? Str::limit(strip_tags($job->description), 150) }}
                        </p>

                        <a href="{{ route('jobs.public.show', $job) }}"
                            class="mt-auto w-full py-4 bg-slate-900 hover:bg-black text-white rounded-2xl font-bold text-center transition-all">
                            Ver Detalhes da Vaga
                        </a>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center">
                        <div class="text-slate-300 text-6xl mb-6 opacity-30">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <p class="text-xl text-slate-500 font-medium italic">Nenhuma vaga aberta no momento. Volte em breve!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection