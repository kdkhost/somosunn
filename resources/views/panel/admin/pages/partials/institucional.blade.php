<!-- Hero & Content Section -->
<section id="sec-hero"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="fas fa-heading text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Cabeçalho (Hero)</h2>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título da Página</label>
                <input type="text" name="hero_title" value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-bold">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo (Opcional)</label>
                <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">
            </div>
        </div>
    </div>
</section>

<section id="sec-conteudo"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24 mt-8">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <i class="fas fa-file-alt text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Conteúdo Principal</h2>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-4">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Texto da Página (HTML)</label>
            <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                <textarea name="body_content" id="summernote" class="summernote">{{ old('body_content', $data['body_content'] ?? '') }}</textarea>
            </div>
            <p class="text-[10px] text-slate-400 italic px-2">Use o editor acima para formatar o texto com títulos, negrito, listas e links.</p>
        </div>
    </div>
</section>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            placeholder: 'Digite o conteúdo da página aqui...',
            tabsize: 2,
            height: 500,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onInit: function() {
                    $('.note-editable').addClass('prose prose-slate dark:prose-invert max-w-none');
                }
            }
        });
    });
</script>
@endpush
