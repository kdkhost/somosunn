@extends('panel.layouts.app')

@section('title', 'Meus Certificados')

@section('panel_breadcrumb')
    <div class="flex items-center gap-2">
        <i class="fas fa-certificate text-slate-400"></i>
        <span>Meus Certificados</span>
    </div>
@endsection

@section('panel_content')
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors">Meus
                    Certificados</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">Visualize e baixe suas conquistas
                    acadêmicas.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        @forelse($certificates as $certificate)
            <div
                class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col hover:shadow-md transition-all duration-300 group">
                <div class="flex items-center gap-4 mb-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 transition-colors">
                        <i class="fas fa-certificate text-xl"></i>
                    </div>
                    <span
                        class="font-bold text-lg text-slate-900 dark:text-white transition-colors">{{ $certificate->title }}</span>
                </div>
                <div class="text-slate-500 dark:text-slate-400 mb-4 text-sm transition-colors">
                    Emitido em: {{ $certificate->created_at->format('d/m/Y') }}
                </div>
                <div class="flex-1"></div>
                <div
                    class="flex items-center justify-between mt-4 pt-4 border-t border-slate-50 dark:border-slate-800 transition-colors">
                    <div class="flex gap-4">
                        <a href="{{ route('panel.certificates.show', $certificate) }}"
                            class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline">Visualizar</a>
                        <a href="{{ route('panel.certificates.download', $certificate) }}"
                            class="text-sm font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Baixar PDF</a>
                    </div>
                    <i
                        class="fas fa-download text-slate-300 dark:text-slate-700 transition-colors group-hover:translate-y-1 duration-300"></i>
                </div>
            </div>
        @empty
            <div class="col-span-2 py-12 text-center text-slate-400 dark:text-slate-600 transition-colors">
                <i class="fas fa-award text-5xl mb-4 opacity-20"></i>
                <p class="font-medium text-lg">Nenhum certificado encontrado.</p>
            </div>
        @endforelse
    </div>
@endsection