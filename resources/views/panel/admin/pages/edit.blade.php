@extends('panel.layouts.app')

@section('title', 'Editar Página: ' . $page->title)

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.pages.index') }}"
        class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Páginas</a>
    <i class="fas fa-chevron-right text-[10px] mx-1 text-slate-300 dark:text-slate-700"></i>
    <span class="text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider">{{ $page->slug }}</span>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <a href="{{ route('panel.admin.pages.index') }}"
                        class="text-slate-400 hover:text-blue-600 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Editar Página: {{ $page->title }}</h1>
                </div>
                <p class="text-slate-500 dark:text-slate-400">Personalize o conteúdo da página <span
                        class="font-mono text-xs bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">{{ $page->slug }}</span>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" form="page-form"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-2xl font-bold shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    Salvar Alterações
                </button>
            </div>
        </div>

        @if($errors->any())
            <div
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-2xl">
                <ul class="list-disc list-inside text-sm font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="page-form" action="{{ route('panel.admin.pages.update', $page) }}" method="POST"
            enctype="multipart/form-data" class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            @csrf
            @method('PUT')

            <!-- Sidebar de Navegação Rápida -->
            <aside class="xl:col-span-1 space-y-4 sticky top-24 self-start">
                <div
                    class="bg-white dark:bg-slate-900 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500 mb-4 px-2">
                        Navegação</h2>
                    <nav class="space-y-1" id="section-nav">
                        <a href="#sec-seo"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/20 dark:text-blue-400 transition-all">
                            <i class="fas fa-search w-5 text-center"></i>
                            SEO & Meta
                        </a>
                        @php
                            $sections = [];
                            $slugSections = [
                                'home' => ['Hero', 'Estatísticas', 'Sobre', 'Eventos', 'Comunidade', 'Ranking', 'Depoimentos', 'CTA'],
                                'sobre' => ['Hero', 'Estatísticas', 'História', 'Diferenciais', 'CTA'],
                                'manifesto' => ['Hero', 'Citação Top', 'Seções', 'Pilares', 'CTA'],
                                'valores' => ['Hero', 'Citação', 'Valores', 'CTA'],
                                'como-funciona' => ['Hero', 'Planos', 'Passos', 'CTA'],
                                'quem-somos' => ['Hero', 'Fundadores', 'Time', 'Estatísticas', 'CTA'],
                                'eventos' => ['Hero', 'Listagem', 'CTA'],
                                'portal' => ['Hero', 'Estatísticas', 'Niveis da Comunidade', 'Top Networkers', 'CTA'],
                                'premium' => ['Hero', 'Planos'],
                                'somos-unicas' => ['Identidade', 'Banner', 'Conteúdo'],
                                'somos-unicas-sobre' => ['Identidade', 'Conteúdo'],
                                'termos-de-uso' => ['Hero', 'Conteúdo'],
                                'politica-de-privacidade' => ['Hero', 'Conteúdo'],
                                'consentimento-lgpd' => ['Hero', 'Conteúdo'],
                            ];
                            $pageSections = $slugSections[$page->slug] ?? [];
                        @endphp

                        @foreach($pageSections as $section)
                            <a href="#sec-{{ Str::slug($section) }}"
                                class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                                <i class="fas fa-chevron-right w-5 text-center text-[10px]"></i>
                                {{ $section }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="bg-blue-600 rounded-[2.5rem] p-8 text-white relative overflow-hidden group">
                    <div class="relative z-10">
                        <h3 class="text-xl font-black leading-tight mb-2 italic">Dica Pro</h3>
                        <p class="text-blue-100 text-xs leading-relaxed opacity-90">Utilize o switch de visibilidade em cada
                            seção para ocultar temporariamente conteúdos sem apagá-los.</p>
                    </div>
                    <div
                        class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700">
                    </div>
                    <div
                        class="absolute -left-4 -top-4 w-24 h-24 bg-blue-400/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700">
                    </div>
                </div>
            </aside>

            <!-- Área Central do Editor -->
            <main class="xl:col-span-3 space-y-8">
                <section id="sec-seo"
                    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
                    <div
                        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                                <i class="fas fa-search text-sm"></i>
                            </div>
                            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">
                                Configurações da Página</h2>
                        </div>
                    </div>
                    <div class="p-8 space-y-10">
                        {{-- Identificação Interna --}}
                        <div class="space-y-4">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500 flex items-center gap-2">
                                <span class="w-8 h-[1px] bg-slate-100 dark:bg-slate-800"></span>
                                Identificação Interna
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-xs">Slug da URL</label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">/</div>
                                        <input type="text" value="{{ $page->slug === 'home' ? '' : $page->slug }}" readonly
                                            class="w-full bg-slate-50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl pl-8 pr-5 py-3 text-sm text-slate-400 cursor-not-allowed font-mono">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-xs">Nome no Painel</label>
                                    <input type="text" name="title" value="{{ old('title', $page->title) }}"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border">
                                </div>
                            </div>
                        </div>

                        {{-- SEO Principal --}}
                        <div class="space-y-6">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500 flex items-center gap-2">
                                <span class="w-8 h-[1px] bg-slate-100 dark:bg-slate-800"></span>
                                SEO Principal
                            </h3>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-xs">Título H1 (Destaque Visual)</label>
                                <input type="text" name="h1_title" value="{{ old('h1_title', $data['h1_title'] ?? '') }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border"
                                    placeholder="Ex: O Manifesto SOMOS UNN">
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-xs">Meta Title (Google)</label>
                                <input type="text" name="seo_title" value="{{ old('seo_title', $data['seo_title'] ?? '') }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border"
                                    placeholder="Título para os motores de busca...">
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-xs flex justify-between items-center">
                                    Meta Descrição
                                    <span class="text-[10px] text-slate-400 font-normal"><span id="seo-desc-count">0</span>/320 caracteres</span>
                                </label>
                                <textarea name="seo_description" id="seo_description" rows="3"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border"
                                    placeholder="Breve resumo para resultados de busca...">{{ old('seo_description', $data['seo_description'] ?? '') }}</textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-xs">Meta Keywords</label>
                                <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $data['seo_keywords'] ?? '') }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border"
                                    placeholder="ex: curso, mentoria, comunidade (separadas por vírgula)">
                            </div>
                        </div>

                        {{-- Indexação & Avançado --}}
                        <div class="space-y-6">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500 flex items-center gap-2">
                                <span class="w-8 h-[1px] bg-slate-100 dark:bg-slate-800"></span>
                                Indexação &amp; Avançado
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-xs">Robots Management</label>
                                    <select name="meta_robots" class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border">
                                        @php $robots = $data['meta_robots'] ?? 'index,follow'; @endphp
                                        <option value="index,follow" {{ $robots === 'index,follow' ? 'selected' : '' }}>Index, Follow (Padrão)</option>
                                        <option value="noindex,follow" {{ $robots === 'noindex,follow' ? 'selected' : '' }}>NoIndex, Follow</option>
                                        <option value="index,nofollow" {{ $robots === 'index,nofollow' ? 'selected' : '' }}>Index, NoFollow</option>
                                        <option value="noindex,nofollow" {{ $robots === 'noindex,nofollow' ? 'selected' : '' }}>NoIndex, NoFollow</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-xs">OpenGraph Type</label>
                                    <select name="og_type" class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border">
                                        @php $ogType = $data['og_type'] ?? 'website'; @endphp
                                        <option value="website" {{ $ogType === 'website' ? 'selected' : '' }}>Website</option>
                                        <option value="article" {{ $ogType === 'article' ? 'selected' : '' }}>Article</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-xs">URL Canônica (Opcional)</label>
                                <input type="url" name="canonical_url" value="{{ old('canonical_url', $data['canonical_url'] ?? '') }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border"
                                    placeholder="https://somosunn.com.br/{{ $page->slug }}">
                            </div>
                        </div>

                        {{-- Social Sharing --}}
                        <div class="space-y-6">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500 flex items-center gap-2">
                                <span class="w-8 h-[1px] bg-slate-100 dark:bg-slate-800"></span>
                                Social Sharing (OG / Twitter)
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                {{-- OG Image --}}
                                <div class="space-y-4">
                                    <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-xs italic">OpenGraph Image (FB/WA 1200x630)</label>
                                    <div class="relative group">
                                        <div class="aspect-video rounded-[2rem] overflow-hidden bg-slate-100 dark:bg-slate-950 border-2 border-dashed border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center transition-all group-hover:border-blue-500/50">
                                            @if (!empty($data['seo_og_image'] ?? ($data['seo_image'] ?? null)))
                                                <img src="{{ Storage::url($data['seo_og_image'] ?? ($data['seo_image'] ?? null)) }}" alt="Preview OG" class="w-full h-full object-cover">
                                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                                    <label class="cursor-pointer bg-white text-slate-900 px-4 py-2 rounded-xl font-bold text-xs shadow-xl">
                                                        <i class="fas fa-edit mr-1"></i> Alterar
                                                        <input type="file" name="seo_og_image" accept="image/*" class="hidden">
                                                    </label>
                                                    <label class="cursor-pointer bg-rose-500 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-xl">
                                                        <input type="checkbox" name="remove_seo_og_image" value="1" class="hidden">
                                                        <i class="fas fa-trash"></i>
                                                    </label>
                                                </div>
                                            @else
                                                <i class="fas fa-cloud-upload-alt text-3xl text-slate-300 dark:text-slate-700 mb-2"></i>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Upload OG Image</span>
                                                <input type="file" name="seo_og_image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Twitter Image --}}
                                <div class="space-y-4">
                                    <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-xs italic">Twitter Card Image (1200x600)</label>
                                    <div class="relative group">
                                        <div class="aspect-video rounded-[2rem] overflow-hidden bg-slate-100 dark:bg-slate-950 border-2 border-dashed border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center transition-all group-hover:border-blue-500/50">
                                            @if (!empty($data['seo_twitter_image'] ?? null))
                                                <img src="{{ Storage::url($data['seo_twitter_image'] ?? null) }}" alt="Preview Twitter" class="w-full h-full object-cover">
                                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                                    <label class="cursor-pointer bg-white text-slate-900 px-4 py-2 rounded-xl font-bold text-xs shadow-xl">
                                                        <i class="fas fa-edit mr-1"></i> Alterar
                                                        <input type="file" name="seo_twitter_image" accept="image/*" class="hidden">
                                                    </label>
                                                    <label class="cursor-pointer bg-rose-500 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-xl">
                                                        <input type="checkbox" name="remove_seo_twitter_image" value="1" class="hidden">
                                                        <i class="fas fa-trash"></i>
                                                    </label>
                                                </div>
                                            @else
                                                <i class="fas fa-cloud-upload-alt text-3xl text-slate-300 dark:text-slate-700 mb-2"></i>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Upload Twitter Image</span>
                                                <input type="file" name="seo_twitter_image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div id="dynamic-sections" class="space-y-8">
                    @if(view()->exists("panel.admin.pages.partials.{$page->slug}"))
                        @include("panel.admin.pages.partials.{$page->slug}")
                    @else
                        <div
                            class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 p-8 rounded-[2rem] text-center">
                            <i class="fas fa-exclamation-triangle text-4xl mb-4 opacity-50"></i>
                            <h3 class="text-lg font-bold mb-2">Editor específico não encontrado</h3>
                            <p class="text-sm">O arquivo parcial para a página <span class="font-mono">{{ $page->slug }}</span>
                                ainda não foi migrado para Tailwind.</p>
                        </div>
                    @endif
                </div>

                <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-4 rounded-[2rem] font-black uppercase tracking-widest shadow-xl shadow-blue-500/25 transition-all text-sm flex items-center gap-3">
                        <i class="fas fa-save text-base"></i>
                        Salvar Página Completa
                    </button>
                </div>
            </main>
        </form>
    </div>

    @prepend('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const toggles = document.querySelectorAll('.section-toggle');
                toggles.forEach(toggle => {
                    toggle.addEventListener('change', function () {
                        const section = this.dataset.section;
                        const status = this.checked;

                        const label = this.closest('label');
                        if (label) label.style.opacity = '0.5';

                        fetch(`{{ route('panel.admin.pages.toggle-section', $page) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ section, status })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (label) label.style.opacity = '1';
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Atualizado!',
                                        text: data.message,
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true
                                    });
                                }
                            })
                            .catch(() => {
                                if (label) label.style.opacity = '1';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: 'Falha ao atualizar visibilidade.'
                                });
                            });
                    });
                });

                const navLinks = document.querySelectorAll('#section-nav a, a[href^="#sec-"]');
                const sections = document.querySelectorAll('main > section, #dynamic-sections > section, #dynamic-sections > div[id^="sec-"]');

                function showSection(targetId) {
                    if (!targetId) return;
                    
                    // Normalize hash
                    const id = targetId.startsWith('#') ? targetId.slice(1) : targetId;
                    const targetElement = document.getElementById(id);

                    if (targetElement) {
                        // Hide all
                        sections.forEach(s => s.classList.add('hidden'));
                        // Show target
                        targetElement.classList.remove('hidden');

                        // Update Nav UI
                        navLinks.forEach(link => {
                            const isMain = link.getAttribute('href') === `#${id}`;
                            if (isMain) {
                                link.classList.add('bg-blue-50', 'dark:bg-blue-900/20', 'text-blue-600', 'dark:text-blue-400');
                                link.classList.remove('text-slate-600', 'dark:text-slate-400', 'hover:bg-slate-50', 'dark:hover:bg-slate-800');
                            } else {
                                link.classList.remove('bg-blue-50', 'dark:bg-blue-900/20', 'text-blue-600', 'dark:text-blue-400');
                                link.classList.add('text-slate-600', 'dark:text-slate-400', 'hover:bg-slate-50', 'dark:hover:bg-slate-800');
                            }
                        });

                        // Update URL without jump
                        history.replaceState(null, null, `#${id}`);
                    }
                }

                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a[href^="#sec-"]');
                    if (link) {
                        e.preventDefault();
                        showSection(link.getAttribute('href'));
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                });

                const seoDesc = document.getElementById('seo_description');
                const seoCount = document.getElementById('seo-desc-count');
                if (seoDesc && seoCount) {
                    const updateCount = () => { seoCount.textContent = seoDesc.value.length; };
                    seoDesc.addEventListener('input', updateCount);
                    updateCount();
                }

                // Initial State
                const currentHash = window.location.hash || '#sec-seo';
                showSection(currentHash);
                
                // Initialize Repeater Engine
                window.initJSONRepeater = function({ containerId, inputId, addButtonId, itemSchema, template, initialData }) {
                        const container = document.getElementById(containerId);
                        const hiddenInput = document.querySelector(`[name="${inputId}"]`);
                        const addButton = document.getElementById(addButtonId);
                        let items = Array.isArray(initialData) ? initialData : [];

                        function sync() {
                            hiddenInput.value = JSON.stringify(items);
                        }

                        function render() {
                            container.innerHTML = '';
                            items.forEach((item, index) => {
                                const wrapper = document.createElement('div');
                                wrapper.className = 'repeater-item group relative bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50 rounded-3xl p-6 mb-4 transition-all hover:border-blue-500/30';
                                wrapper.dataset.index = index;
                                wrapper.innerHTML = `
                                    <div class="reorder-handle absolute -left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg flex items-center justify-center cursor-grab active:cursor-grabbing opacity-0 group-hover:opacity-100 transition-all shadow-sm z-10">
                                        <i class="fas fa-grip-lines text-slate-400 text-xs text-[10px]"></i>
                                    </div>
                                    <button type="button" class="btn-remove absolute -right-2 -top-2 w-8 h-8 bg-rose-500 text-white rounded-full flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-all transform hover:scale-110 z-10" data-index="${index}">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                    ${template(item, index)}
                                `;
                                
                                // Bind remove
                                wrapper.querySelector('.btn-remove').addEventListener('click', function() {
                                    items.splice(index, 1);
                                    render();
                                });

                                // Bind inputs
                                wrapper.querySelectorAll('input, textarea, select').forEach(input => {
                                    input.addEventListener('input', function() {
                                        const fieldMatch = this.name.match(/\[(.*?)\]/);
                                        if (fieldMatch) {
                                            const field = fieldMatch[1];
                                            items[index][field] = this.value;
                                            sync();
                                        }
                                    });
                                });

                                // Bind Image Uploads
                                wrapper.querySelectorAll('.repeater-upload-btn').forEach(btn => {
                                    const field = btn.dataset.field;
                                    btn.onclick = () => {
                                        const fileInput = document.createElement('input');
                                        fileInput.type = 'file';
                                        fileInput.accept = 'image/*';
                                        fileInput.onchange = (e) => {
                                            const file = e.target.files[0];
                                            if(!file) return;
                                            
                                            const fd = new FormData();
                                            fd.append('file', file);
                                            
                                            const originalContent = btn.innerHTML;
                                            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                                            btn.style.pointerEvents = 'none';

                                            fetch('/upload', {
                                                method: 'POST',
                                                body: fd,
                                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                                            }).then(r => r.json()).then(data => {
                                                if(data.path) {
                                                    items[index][field] = data.path;
                                                    sync();
                                                    render();
                                                } else {
                                                    Swal.fire('Erro', data.error || 'Falha no upload', 'error');
                                                    btn.innerHTML = originalContent;
                                                    btn.style.pointerEvents = 'auto';
                                                }
                                            }).catch(err => {
                                                Swal.fire('Erro', 'Falha na conexão', 'error');
                                                btn.innerHTML = originalContent;
                                                btn.style.pointerEvents = 'auto';
                                            });
                                        };
                                        fileInput.click();
                                    };
                                });

                                container.appendChild(wrapper);
                            });

                            if (window.Sortable && container) {
                                if (container._sortable) container._sortable.destroy();
                                container._sortable = Sortable.create(container, {
                                    handle: '.reorder-handle',
                                    animation: 150,
                                    onEnd: function() {
                                        const newItems = [];
                                        container.querySelectorAll('.repeater-item').forEach(el => {
                                            newItems.push(items[parseInt(el.dataset.index)]);
                                        });
                                        items = newItems;
                                        render();
                                    }
                                });
                            }

                            sync();
                        }

                        if(addButton) {
                            addButton.onclick = () => {
                                const newItem = itemSchema ? { ...itemSchema } : {};
                                items.push(newItem);
                                render();
                            };
                        }

                        render();
                    };
                </script>
            });
        </script>
    @endprepend
@endsection
