@extends('panel.layouts.app')

@section('title', $job->title)

@section('panel_content')
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Header & Breadcrumb --}}
        <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">
            <a href="{{ route('panel.jobs.index') }}" class="hover:text-blue-600 transition-colors">Vagas</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-slate-900 dark:text-white">{{ $job->title }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Job Details --}}
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
                                <h1
                                    class="text-2xl font-black text-slate-900 dark:text-white leading-tight transition-colors">
                                    {{ $job->title }}
                                </h1>
                                <div
                                    class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-slate-500 dark:text-slate-400 font-bold transition-colors">
                                    <span><i class="fas fa-building mr-1"></i>
                                        {{ $job->company_name ?: 'Confidencial' }}</span>
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
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Benefícios</h3>
                            <div class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 font-medium">
                                {!! $job->benefits !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar Application --}}
            <div class="space-y-6">
                <div
                    class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 sticky top-6 transition-colors">
                    @if($hasApplied)
                        <div class="text-center space-y-4">
                            <div
                                class="w-16 h-16 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-emerald-500 flex items-center justify-center text-3xl mx-auto transition-colors">
                                <i class="fas fa-check"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white transition-colors">Inscrição Enviada!
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 transition-colors">Você já se candidatou para
                                esta vaga. Boa sorte!</p>
                            <a href="{{ route('panel.jobs.index') }}"
                                class="block w-full py-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-2xl hover:bg-slate-200 transition-all text-sm">
                                Voltar para Vagas
                            </a>
                        </div>
                    @else
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 transition-colors">Candidatar-se</h3>

                        {{-- Formulário sem multipart: arquivo convertido para Base64 via JS --}}
                        <form id="applyForm" action="{{ route('panel.jobs.apply.external', $job) }}" method="POST"
                            class="space-y-4">
                            @csrf
                            <input type="hidden" name="file_data" id="file_data">
                            <input type="hidden" name="file_name" id="file_name">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Seu Currículo
                                    (PDF/DOC)</label>
                                <input type="file" id="cv_file_picker" accept=".pdf,.doc,.docx" required
                                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 transition-all cursor-pointer">
                                <p id="file_status" class="text-xs text-slate-400 mt-1"></p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-widest">Carta de
                                    Apresentação</label>
                                <textarea name="cover_letter" rows="4"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-slate-900 dark:text-white font-medium"
                                    placeholder="Conte um pouco sobre você..."></textarea>
                            </div>

                            <button type="submit" id="applyBtn"
                                class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl shadow-blue-500/30 transition-all transform hover:-translate-y-1">
                                Enviar Candidatura
                            </button>
                        </form>

                        <script>
                            (function () {
                                var picker = document.getElementById('cv_file_picker');
                                var status = document.getElementById('file_status');
                                var fileData = document.getElementById('file_data');
                                var fileName = document.getElementById('file_name');
                                var form = document.getElementById('applyForm');
                                var btn = document.getElementById('applyBtn');

                                picker.addEventListener('change', function () {
                                    var file = this.files[0];
                                    if (!file) return;
                                    var maxMB = 2;
                                    if (file.size > maxMB * 1024 * 1024) {
                                        Swal.fire('Arquivo muito grande', 'O currículo deve ter no máximo 2MB.', 'warning');
                                        this.value = '';
                                        return;
                                    }
                                    status.textContent = 'Lendo arquivo...';
                                    var reader = new FileReader();
                                    reader.onload = function (e) {
                                        fileData.value = e.target.result; // base64 data URL
                                        fileName.value = file.name;
                                        status.textContent = '✅ ' + file.name + ' pronto para envio.';
                                    };
                                    reader.onerror = function () {
                                        status.textContent = '❌ Erro ao ler o arquivo.';
                                    };
                                    reader.readAsDataURL(file);
                                });

                                form.addEventListener('submit', function (e) {
                                    if (!fileData.value) {
                                        e.preventDefault();
                                        Swal.fire('Atenção', 'Selecione o arquivo do currículo.', 'warning');
                                        return;
                                    }
                                    btn.disabled = true;
                                    btn.textContent = 'Enviando...';
                                });
                            })();
                        </script>
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