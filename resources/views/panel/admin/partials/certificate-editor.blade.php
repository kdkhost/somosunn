@php
    $entity = $entity ?? null;
    $formId = $formId ?? 'resourceForm';
    $previewUrl = $previewUrl ?? null;
    $titleInput = $titleInput ?? 'CERTIFICADO';
    $presentationInput = $presentationInput ?? '';
    $tagLabels = $tagLabels ?? [];
    $backgroundLabel = $backgroundLabel ?? 'Fundo do Certificado';
    $backgroundHint = $backgroundHint ?? 'Recomendado: 1920x1080px (PNG/JPG)';
    $signatureLabel = $signatureLabel ?? 'Assinatura';
    $signatureHint = $signatureHint ?? 'Recomendado: PNG com fundo transparente.';
    $autoInfoLabel = $autoInfoLabel ?? null;
    $autoInfoValue = $autoInfoValue ?? null;
    $saveLabel = $saveLabel ?? 'Salvar Certificado';
    $certificateBgUrl = $entity && $entity->certificate_bg ? \App\Support\UploadStorage::url($entity->certificate_bg) : null;
    $signatureUrl = $entity && $entity->instructor_signature ? \App\Support\UploadStorage::url($entity->instructor_signature) : null;
@endphp

<div id="certificate-editor-root" class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white transition-colors">Editor de Certificado</h3>
            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400 transition-colors">
                O mesmo fluxo completo do painel legado, agora no painel novo: posicione elementos, ajuste grade,
                controle camadas e salve sem cortar a area de edicao.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($previewUrl)
                <button type="button" onclick="return previewCertificate();"
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-xs font-black uppercase tracking-[0.24em] text-slate-600 shadow-sm transition-all hover:border-blue-200 hover:text-blue-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:text-blue-400">
                    <i class="fas fa-eye"></i>
                    <span>Preview PDF</span>
                </button>
            @endif

            <button type="submit" form="{{ $formId }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-xs font-black uppercase tracking-[0.24em] text-white shadow-xl shadow-blue-500/20 transition-all hover:bg-blue-700 hover:shadow-blue-500/30">
                <i class="fas fa-save"></i>
                <span>{{ $saveLabel }}</span>
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         CANVAS — Certificado inteiro visível (layout vertical)
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="rounded-[2rem] border border-slate-800 bg-slate-950 p-4 shadow-xl">
        <div class="flex items-center justify-between gap-4 px-2 pb-3">
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-3 rounded-2xl border border-slate-700 bg-slate-900 px-3 py-2">
                    <label for="cert-zoom" class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Zoom</label>
                    <select id="cert-zoom"
                        class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-black text-white outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        <option value="0.5">50%</option>
                        <option value="0.75">75%</option>
                        <option value="1" selected>100%</option>
                        <option value="1.25">125%</option>
                        <option value="1.5">150%</option>
                        <option value="2">200%</option>
                        <option value="2.5">250%</option>
                        <option value="3">300%</option>
                    </select>
                </div>

                <button type="button" id="cert-fit"
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-xs font-black uppercase tracking-[0.2em] text-slate-300 transition-all hover:border-blue-500 hover:text-blue-400">
                    <i class="fas fa-expand-arrows-alt"></i>
                    <span>Fit</span>
                </button>
            </div>
        </div>

        <div id="cert-canvas-stage"
            class="flex items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-slate-900 p-6">
            <div id="cert-canvas" class="relative shrink-0 overflow-hidden bg-white shadow-[0_40px_120px_-30px_rgba(0,0,0,0.85)]"
                style="width: 842px; height: 595px; border-radius: 6px;">
                <img id="cert-bg-img" src="{{ $certificateBgUrl ?: '' }}"
                    class="absolute inset-0 h-full w-full object-cover {{ $certificateBgUrl ? '' : 'hidden' }}" style="z-index: 1;">

                <div id="cert-bg-placeholder"
                    class="absolute inset-0 flex items-center justify-center bg-slate-100 text-center text-slate-400 {{ $certificateBgUrl ? 'hidden' : '' }}"
                    style="z-index: 1;">
                    <div class="space-y-3 px-8">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-2xl shadow">
                            <i class="fas fa-image"></i>
                        </div>
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.2em] text-slate-500">Sem fundo ainda</p>
                            <p class="mt-2 text-xs font-medium text-slate-400">Envie a arte do certificado nos controles abaixo.</p>
                        </div>
                    </div>
                </div>

                <div id="cert-grid-overlay"
                    class="pointer-events-none absolute inset-0 hidden"
                    style="z-index: 5;"></div>

                <div id="cert-elements-layer" class="absolute inset-0" style="z-index: 10;"></div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         CONTROLES — Organizados em grid abaixo do canvas
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 transition-all hover:shadow-xl dark:border-slate-700/50 dark:bg-gradient-to-br dark:from-slate-800 dark:to-slate-900 dark:shadow-none">
                <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-700/50">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                        <i class="fas fa-image"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Assets</p>
                        <h4 class="text-sm font-black text-slate-800 dark:text-white">Fundo e assinatura</h4>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">{{ $backgroundLabel }}</label>
                        <label class="group flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-xs font-bold text-slate-500 transition-all hover:border-blue-300 hover:text-blue-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400 dark:hover:border-blue-900 dark:hover:text-blue-400">
                            <input type="file" name="certificate_bg" id="certificate_bg" accept="image/*" class="hidden">
                            <i class="fas fa-upload"></i>
                            <span id="certificate_bg_label">Selecionar arquivo</span>
                        </label>
                        <p class="mt-2 text-[11px] font-medium text-slate-400 dark:text-slate-500">{{ $backgroundHint }}</p>
                    </div>

                    <div>
                        <label for="cert-bg-fit" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Ajuste do fundo</label>
                        <select id="cert-bg-fit"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="cover">Cobrir (cover)</option>
                            <option value="stretch">Esticar inteiro</option>
                        </select>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">{{ $signatureLabel }}</p>
                                <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">{{ $signatureHint }}</p>
                            </div>
                        </div>
                        <div id="signaturePreviewWrapper"
                            class="flex min-h-[110px] items-center justify-center rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 {{ $signatureUrl ? '' : 'hidden' }}">
                            <img id="signaturePreview" src="{{ $signatureUrl ?: '' }}" class="max-h-20 max-w-full object-contain">
                        </div>
                        <div id="signatureEmptyState"
                            class="flex min-h-[110px] items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white p-4 text-center text-slate-400 dark:border-slate-800 dark:bg-slate-900 {{ $signatureUrl ? 'hidden' : '' }}">
                            <div class="space-y-2">
                                <i class="fas fa-signature text-2xl"></i>
                                <p class="text-xs font-bold uppercase tracking-[0.2em]">Sem assinatura</p>
                            </div>
                        </div>
                        <label class="mt-4 flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-4 text-xs font-bold text-slate-500 transition-all hover:border-blue-300 hover:text-blue-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-blue-900 dark:hover:text-blue-400">
                            <input type="file" name="instructor_signature" id="instructor_signature" accept="image/png,image/jpeg,image/jpg" class="hidden">
                            <i class="fas fa-pen-nib"></i>
                            <span id="instructor_signature_label">Substituir assinatura</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 transition-all hover:shadow-xl dark:border-slate-700/50 dark:bg-gradient-to-br dark:from-slate-800 dark:to-slate-900 dark:shadow-none">
                <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-700/50">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400">
                        <i class="fas fa-pen-fancy"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Texto</p>
                        <h4 class="text-sm font-black text-slate-800 dark:text-white">Titulo e apresentacao</h4>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="certificate_title" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Titulo do certificado</label>
                        <input type="text" name="certificate_title" id="certificate_title" value="{{ $titleInput }}"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                    </div>

                    <div>
                        <label for="presentation_text" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Texto de apresentacao</label>
                        <textarea name="presentation_text" id="presentation_text" rows="4"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            placeholder="Texto opcional exibido acima do nome do aluno.">{{ $presentationInput }}</textarea>
                    </div>

                    @if($autoInfoLabel && $autoInfoValue)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">{{ $autoInfoLabel }}</p>
                            <p class="mt-2 text-sm font-bold text-slate-700 dark:text-slate-200">{{ $autoInfoValue }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 transition-all hover:shadow-xl dark:border-slate-700/50 dark:bg-gradient-to-br dark:from-slate-800 dark:to-slate-900 dark:shadow-none">
                <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-700/50">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400">
                        <i class="fas fa-ruler-combined"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Ferramentas</p>
                        <h4 class="text-sm font-black text-slate-800 dark:text-white">Grade, snap e nudge</h4>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-800 dark:bg-slate-950">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Mostrar grade</span>
                        <input type="checkbox" id="cert-grid-enabled" class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900">
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="cert-grid-step" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Passo da grade</label>
                            <select id="cert-grid-step"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="1">1%</option>
                                <option value="2">2%</option>
                                <option value="5" selected>5%</option>
                                <option value="10">10%</option>
                            </select>
                        </div>

                        <div>
                            <label for="cert-snap-step" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Passo do snap</label>
                            <select id="cert-snap-step"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                                <option value="0.25">0,25%</option>
                                <option value="0.5">0,5%</option>
                                <option value="1" selected>1%</option>
                                <option value="2">2%</option>
                                <option value="5">5%</option>
                            </select>
                        </div>
                    </div>

                    <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-800 dark:bg-slate-950">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Snap na grade</span>
                        <input type="checkbox" id="cert-snap-enabled" checked class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900">
                    </label>

                    <div>
                        <label for="cert-nudge-step" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Nudge no teclado</label>
                        <select id="cert-nudge-step"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="0.1">0,1%</option>
                            <option value="0.25">0,25%</option>
                            <option value="0.5" selected>0,5%</option>
                            <option value="1">1%</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 transition-all hover:shadow-xl dark:border-slate-700/50 dark:bg-gradient-to-br dark:from-slate-800 dark:to-slate-900 dark:shadow-none">
                <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-700/50">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Elementos</p>
                        <h4 class="text-sm font-black text-slate-800 dark:text-white">Visibilidade e logo</h4>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($tagLabels as $tag => $label)
                        @php $toggleId = 'toggle-' . str_replace('_', '-', $tag); @endphp
                        <label
                            class="flex items-center justify-between gap-4 rounded-2xl border px-4 py-3 transition-all {{ $tag === 'platform_logo'
                                ? 'border-amber-200 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-950/20'
                                : 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950' }}">
                            <div>
                                <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">{{ $label }}</span>
                                @if($tag === 'platform_logo')
                                    <span class="mt-1 block text-[11px] font-medium text-amber-700 dark:text-amber-300">Obrigatorio no certificado.</span>
                                @endif
                            </div>
                            <input type="checkbox" id="{{ $toggleId }}" data-tag="{{ $tag }}"
                                class="cert-toggle h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900"
                                {{ $tag === 'platform_logo' ? 'checked' : '' }}>
                        </label>
                    @endforeach

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Tamanho da logo</p>
                        <div class="mt-3 grid grid-cols-2 gap-4">
                            <div>
                                <label for="logo-width" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Largura (px)</label>
                                <input type="number" id="logo-width" min="50" max="400" value="120"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label for="logo-height" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Altura (px)</label>
                                <input type="number" id="logo-height" min="30" max="200" value="60"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 transition-all hover:shadow-xl dark:border-slate-700/50 dark:bg-gradient-to-br dark:from-slate-800 dark:to-slate-900 dark:shadow-none">
                <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-700/50">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        <i class="fas fa-stream"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Camadas</p>
                        <h4 class="text-sm font-black text-slate-800 dark:text-white">Ordem de renderizacao</h4>
                    </div>
                </div>

                <div id="cert-layers" class="space-y-2"></div>
            </div>

            <div id="cert-style-controls" style="display:none;"
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 transition-all hover:shadow-xl dark:border-slate-700/50 dark:bg-gradient-to-br dark:from-slate-800 dark:to-slate-900 dark:shadow-none">
                <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-700/50">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-fuchsia-50 text-fuchsia-600 dark:bg-fuchsia-900/20 dark:text-fuchsia-400">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Edicao</p>
                        <h4 class="text-sm font-black text-slate-800 dark:text-white">
                            Elemento selecionado:
                            <span id="selected-elem-name" class="text-blue-600 dark:text-blue-400"></span>
                        </h4>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="style-x" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">X (%)</label>
                            <input type="number" id="style-x" step="0.1"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div>
                            <label for="style-y" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Y (%)</label>
                            <input type="number" id="style-y" step="0.1"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                        </div>
                    </div>

                    <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-800 dark:bg-slate-950">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Bloquear elemento</span>
                        <input type="checkbox" id="style-locked" class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900">
                    </label>

                    <div>
                        <label for="style-font-family" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Fonte</label>
                        <select id="style-font-family"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="Arial, sans-serif">Arial</option>
                            <option value="'Times New Roman', serif">Times New Roman</option>
                            <option value="'Courier New', monospace">Courier New</option>
                            <option value="Georgia, serif">Georgia</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="style-font-size" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Tamanho</label>
                            <input type="number" id="style-font-size" value="20"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div>
                            <label for="style-z-index" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Z-index</label>
                            <input type="number" id="style-z-index" step="1" value="10"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label for="style-color" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Cor</label>
                        <input type="color" id="style-color" value="#000000"
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-2 py-2 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900">
                    </div>

                    <div>
                        <label for="style-font-weight" class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Peso</label>
                        <select id="style-font-weight"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="normal">Normal</option>
                            <option value="bold">Negrito</option>
                        </select>
                    </div>
                </div>
            </div>

            <input type="hidden" name="certificate_settings" id="certificate_settings_input">
    </div>
</div>

@once
    @push('styles')
        <style>
            #cert-canvas.cert-editor-no-bg {
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            }

            #cert-canvas.cert-editor-no-bg #cert-bg-placeholder {
                background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%) !important;
                color: #334155 !important;
            }

            #cert-canvas.cert-editor-no-bg #cert-bg-placeholder p,
            #cert-canvas.cert-editor-no-bg #cert-bg-placeholder i {
                color: inherit !important;
            }

            #cert-canvas .cert-element.cert-editor-contrast {
                color: #0f172a !important;
                background: rgba(255, 255, 255, 0.78);
                border-radius: 8px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
                text-shadow: none !important;
            }

            #cert-canvas .cert-element.cert-editor-contrast:hover {
                background: rgba(255, 255, 255, 0.9);
                border-color: #2563eb !important;
            }
        </style>
    @endpush
@endonce
