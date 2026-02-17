@extends('panel.layouts.app')

@section('title', 'Moderando Depoimento')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    Moderando Depoimento
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Revise o conteúdo enviado por
                    {{ $testimonial->author_name }}.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.testimonials.index') }}"
                    class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit" form="testForm"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Salvar & Atualizar</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <form id="testForm" action="{{ route('panel.admin.testimonials.update', $testimonial) }}" method="POST">
                    @csrf @method('PUT')

                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-6 transition-colors duration-300">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Nome
                                    do Autor</label>
                                <input type="text" name="author_name"
                                    value="{{ old('author_name', $testimonial->author_name) }}"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Título
                                    / Cargo</label>
                                <input type="text" name="author_title"
                                    value="{{ old('author_title', $testimonial->author_title) }}"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Avaliação
                                (1 a 5
                                estrelas)</label>
                            <select name="rating"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-bold">
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" @selected(old('rating', $testimonial->rating) == $i)>{{ $i }}
                                        {{ $i == 1 ? 'estrela' : 'estrelas' }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Depoimento</label>
                            <textarea name="content" rows="6" required
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('content', $testimonial->content) }}</textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_featured" id="isFeatured" value="1" @checked(old('is_featured', $testimonial->is_featured))
                                class="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 rounded focus:ring-blue-500 transition-colors">
                            <label for="isFeatured"
                                class="text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors">Destacar
                                este depoimento na
                                página inicial</label>
                        </div>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                {{-- Status & Quick Actions --}}
                <div
                    class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                    <label
                        class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-4 transition-colors">Status
                        Atual</label>

                    <div class="mb-6">
                        @if($testimonial->status === 'pending')
                            <div
                                class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-2xl border border-amber-100 dark:border-amber-800/50 flex items-center gap-3 text-amber-700 dark:text-amber-400 transition-colors">
                                <i class="fas fa-clock"></i>
                                <span class="text-sm font-bold">Aguardando Avaliação</span>
                            </div>
                        @elseif($testimonial->status === 'approved')
                            <div
                                class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800/50 flex items-center gap-3 text-emerald-700 dark:text-emerald-400 transition-colors">
                                <i class="fas fa-check-circle"></i>
                                <span class="text-sm font-bold">Depoimento Aprovado</span>
                            </div>
                        @else
                            <div
                                class="p-4 bg-red-50 dark:bg-red-900/20 rounded-2xl border border-red-100 dark:border-red-800/50 flex items-center gap-3 text-red-700 dark:text-red-400 transition-colors">
                                <i class="fas fa-times-circle"></i>
                                <span class="text-sm font-bold">Depoimento Recusado</span>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @if($testimonial->status !== 'approved')
                            <form action="{{ route('panel.admin.testimonials.approve', $testimonial) }}" method="POST">
                                @csrf
                                <button
                                    class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-2xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/30">
                                    <i class="fas fa-check"></i>
                                    <span>Aprovar</span>
                                </button>
                            </form>
                        @endif

                        @if($testimonial->status !== 'rejected')
                            <div x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="w-full py-3 bg-slate-100 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-600 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 text-sm font-bold rounded-2xl transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-times"></i>
                                    <span>Reprovar</span>
                                </button>

                                <div x-show="open" x-transition
                                    class="mt-4 p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 transition-colors">
                                    <form action="{{ route('panel.admin.testimonials.reject', $testimonial) }}" method="POST">
                                        @csrf
                                        <label
                                            class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-1 transition-colors">Motivo
                                            (Opcional)</label>
                                        <textarea name="moderation_notes" rows="3"
                                            class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm outline-none focus:border-red-400 transition-all dark:text-white dark:placeholder-slate-600"></textarea>
                                        <button
                                            class="w-full mt-2 py-2 bg-red-600 text-white text-xs font-bold rounded-xl transition-all hover:bg-red-700">Confirmar
                                            Rejeição</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Metadata --}}
                <div
                    class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                    <label
                        class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-4 transition-colors">Informações</label>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 dark:text-slate-500">Enviado em:</span>
                            <span
                                class="font-semibold text-slate-900 dark:text-white transition-colors">{{ $testimonial->created_at->format('d/m/Y') }}</span>
                        </div>
                        @if($testimonial->moderator)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 dark:text-slate-500">Moderado por:</span>
                                <span
                                    class="font-semibold text-slate-900 dark:text-white transition-colors">{{ $testimonial->moderator->name }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 dark:text-slate-500">Moderado em:</span>
                                <span
                                    class="font-semibold text-slate-900 dark:text-white transition-colors">{{ $testimonial->moderated_at ? $testimonial->moderated_at->format('d/m/Y') : '-' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection