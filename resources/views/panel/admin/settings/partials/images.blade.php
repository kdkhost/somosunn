<div class="space-y-8">
    <!-- Visual Identity -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    @else
                        <div class="text-center p-2">
                            <i class="{{ $data[2] }} text-xl text-slate-300 mb-1"></i>
                            <p class="text-[9px] text-slate-400 capitalize">{{ $data[1] }}</p>
                        </div>
                    @endif
                </div>

                <div class="flex gap-2 w-full">
                    <button type="button" onclick="document.getElementById('input_{{ $name }}').click()" 
                            class="flex-1 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Alterar
                    </button>
                    <input type="file" id="input_{{ $name }}" name="{{ $name }}" class="hidden" accept="image/*" onchange="previewImage(this, 'preview_{{ $name }}')">
                    
                    <input type="hidden" name="remove_{{ $name }}" id="remove_{{ $name }}" value="0">
                    <button type="button" onclick="removeImage('{{ $name }}', 'preview_{{ $name }}')"
                            class="px-2 py-2 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Backgrounds & Covers -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i class="fas fa-laptop-code"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Backgrounds e Capas</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach([
                'hero_image' => ['Hero Image (Home)', 'Banner Principal (1920x600)'],
                'site_bg_image' => ['Background Global', 'Fundo Padrão (Pattern/Imagem)']
            ] as $name => $data)
            <div class="space-y-4">
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">{{ $data[0] }}</label>
                
                <div class="w-full h-48 rounded-3xl bg-slate-50 dark:bg-slate-950 border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden relative">
                    @if($url = $getUrl($name))
                        <img id="preview_{{ $name }}" src="{{ $url }}" class="w-full h-full object-cover">
                    @else
                        <div class="text-center p-8">
                            <i class="fas fa-image text-4xl text-slate-300 mb-2"></i>
                            <p class="text-xs text-slate-400">{{ $data[1] }}</p>
                        </div>
                    @endif
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('input_{{ $name }}').click()" 
                            class="flex-1 px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800 transition inline-flex items-center justify-center gap-2">
                        <i class="fas fa-upload text-blue-500"></i>
                        <span id="btn_text_{{ $name }}">Selecionar Imagem</span>
                    </button>
                    <input type="file" id="input_{{ $name }}" name="{{ $name }}" class="hidden" accept="image/*" onchange="previewImage(this, 'preview_{{ $name }}')">
                    
                    <input type="hidden" name="remove_{{ $name }}" id="remove_{{ $name }}" value="0">
                    <button type="button" onclick="removeImage('{{ $name }}', 'preview_{{ $name }}')"
                            class="px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-2xl hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <p id="file_label_{{ $name }}" class="text-xs font-medium text-slate-500 dark:text-slate-400 truncate">
                    {{ $getUrl($name) ? 'Imagem atual definida' : 'Nenhuma imagem selecionada' }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    function previewImage(input, previewId) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                const placeholder = previewId.includes('logo') || previewId.includes('favicon') 
                    ? `<i class="fas fa-image text-xl text-slate-300"></i>` 
                    : `<i class="fas fa-image text-4xl text-slate-300"></i>`;

                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                } else {
                    const container = input.closest('div').previousElementSibling;
                    container.innerHTML = `<img id="${previewId}" src="${e.target.result}" class="w-full h-full ${previewId.includes('hero') || previewId.includes('bg') ? 'object-cover' : 'object-contain'}">`;
                }
                
                // Reset remove hidden input
                const name = previewId.replace('preview_', '');
                document.getElementById('remove_' + name).value = '0';

                const fileLabel = document.getElementById('file_label_' + name);
                if (fileLabel) {
                    fileLabel.textContent = file.name;
                }

                const btnText = document.getElementById('btn_text_' + name);
                if (btnText) {
                    btnText.textContent = 'Alterar Imagem';
                }
            }
            reader.readAsDataURL(file);
        }
    }

    function removeImage(name, previewId) {
        document.getElementById('remove_' + name).value = '1';
        const preview = document.getElementById(previewId);
        if (preview) {
            const container = preview.parentElement;
            const isLogo = name.includes('logo') || name.includes('favicon');
            container.innerHTML = `<div class="text-center p-2">
                <i class="${isLogo ? 'fas fa-image text-xl' : 'fas fa-image text-4xl'} text-slate-300 mb-1"></i>
                <p class="text-[9px] text-slate-400">Removido</p>
            </div>`;
        }
        document.getElementById('input_' + name).value = '';

        const fileLabel = document.getElementById('file_label_' + name);
        if (fileLabel) {
            fileLabel.textContent = 'Imagem removida';
        }

        const btnText = document.getElementById('btn_text_' + name);
        if (btnText) {
            btnText.textContent = 'Selecionar Imagem';
        }
    }
</script>
