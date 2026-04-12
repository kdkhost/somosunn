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
                <p class="text-slate-500 dark:text-slate-400">Gerencie o conteúdo e SEO da página <span
                        class="font-mono text-xs bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">{{ $page->slug }}</span>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" form="page-form"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-2xl font-black uppercase tracking-widest shadow-xl shadow-blue-500/20 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    Salvar Página
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
            enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nav Tabs horizontais (Padronizado com Configurações) -->
            <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <div class="flex overflow-x-auto no-scrollbar border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30" id="page-nav">
                    <a href="#sec-seo"
                        class="nav-tab flex items-center gap-2 px-8 py-5 text-sm transition whitespace-nowrap border-b-4 border-blue-600 text-blue-600 font-black bg-white dark:bg-slate-900">
                        <i class="fas fa-search"></i>
                        Geral & SEO
                    </a>
                    @php
                        $slugSections = [
                            'home' => ['Hero', 'Estatísticas', 'Sobre', 'Eventos', 'Comunidade', 'Ranking', 'Depoimentos', 'CTA'],
                            'sobre' => ['Hero', 'Estatísticas', 'História', 'Diferenciais', 'CTA'],
                            'manifesto' => ['Hero', 'Citação Top', 'Seções', 'Pilares', 'CTA'],
                            'valores' => ['Hero', 'Citação', 'Valores', 'CTA'],
                            'como-funciona' => ['Hero', 'Planos', 'Passos', 'CTA'],
                            'quem-somos' => ['Hero', 'Fundadores', 'Time', 'Estatísticas', 'CTA'],
                            'eventos' => ['Hero', 'Listagem', 'CTA'],
                            'portal' => ['Hero', 'Estatísticas', 'Niveis da Comunidade', 'Top Networkers', 'CTA'],
                            'somos-unicas' => ['Identidade', 'Banner', 'Conteudo'],
                            'somos-unicas-sobre' => ['Identidade', 'Conteudo'],
                            'termos-de-uso' => ['Hero', 'Conteudo'],
                            'politica-de-privacidade' => ['Hero', 'Conteudo'],
                            'consentimento-lgpd' => ['Hero', 'Conteudo'],
                        ];
                        $pageSections = $slugSections[$page->slug] ?? [];
                    @endphp

                    @foreach($pageSections as $section)
                        <a href="#sec-{{ Str::slug($section) }}"
                            class="nav-tab flex items-center gap-2 px-8 py-5 text-sm transition whitespace-nowrap border-b-4 border-transparent text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-200 font-bold hover:bg-white dark:hover:bg-slate-900">
                            {{ $section }}
                        </a>
                    @endforeach
                </div>

                <div class="p-8">
                    <!-- Tab: Geral & SEO -->
                    <div id="sec-seo" class="tab-pane space-y-10">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                            {{-- Coluna 1: Textos --}}
                            <div class="space-y-10">
                                <div class="space-y-4">
                                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">Identificação</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-xs font-bold text-slate-500 dark:text-slate-400 px-1">Slug da URL</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">/</div>
                                                <input type="text" value="{{ $page->slug === 'home' ? '' : $page->slug }}" readonly
                                                    class="w-full bg-slate-50 dark:bg-slate-800/50 border-slate-100 dark:border-slate-800 rounded-2xl pl-8 pr-5 py-3 text-sm text-slate-400 cursor-not-allowed font-mono">
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-xs font-bold text-slate-500 dark:text-slate-400 px-1">Nome no Painel</label>
                                            <input type="text" name="title" value="{{ old('title', $page->title) }}"
                                                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border">
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">Metadados SEO</h3>
                                    <div class="space-y-4">
                                        <div class="space-y-2">
                                            <label class="text-xs font-bold text-slate-500 dark:text-slate-400 px-1">Título H1 (Página)</label>
                                            <input type="text" name="h1_title" value="{{ old('h1_title', $data['h1_title'] ?? '') }}"
                                                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-xs font-bold text-slate-500 dark:text-slate-400 px-1">Meta Title (Busca)</label>
                                            <input type="text" name="seo_title" value="{{ old('seo_title', $data['seo_title'] ?? '') }}"
                                                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-xs font-bold text-slate-500 dark:text-slate-400 px-1 flex justify-between">
                                                Meta Descrição
                                                <span class="font-normal opacity-60"><span id="seo-desc-count">0</span>/320</span>
                                            </label>
                                            <textarea name="seo_description" id="seo_description" rows="4"
                                                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium border uppercase-none">{{ old('seo_description', $data['seo_description'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Coluna 2: Imagens e Social --}}
                            <div class="space-y-10">
                                <div class="space-y-6">
                                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">Social Sharing (OG / Twitter)</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-3">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1">Facebook / WhatsApp</label>
                                            <div class="relative group aspect-video rounded-3xl overflow-hidden bg-slate-50 dark:bg-slate-950 border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center">
                                                @if (!empty($data['seo_og_image'] ?? ($data['seo_image'] ?? null)))
                                                    <img src="{{ Storage::url($data['seo_og_image'] ?? ($data['seo_image'] ?? null)) }}" class="w-full h-full object-cover">
                                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-2">
                                                        <label class="cursor-pointer bg-white text-slate-900 px-3 py-1.5 rounded-xl font-bold text-[10px]"><i class="fas fa-edit mr-1"></i>Trocar<input type="file" name="seo_og_image" class="hidden"></label>
                                                        <label class="cursor-pointer bg-red-500 text-white px-3 py-1.5 rounded-xl font-bold text-[10px]"><input type="checkbox" name="remove_seo_og_image" value="1" class="hidden"><i class="fas fa-trash"></i></label>
                                                    </div>
                                                @else
                                                    <div class="text-center">
                                                        <i class="fas fa-image text-2xl text-slate-300 dark:text-slate-700 mb-1"></i>
                                                        <p class="text-[9px] font-black text-slate-400">UPLOAD OG</p>
                                                    </div>
                                                    <input type="file" name="seo_og_image" class="absolute inset-0 opacity-0 cursor-pointer">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="space-y-3">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1">Twitter / X</label>
                                            <div class="relative group aspect-video rounded-3xl overflow-hidden bg-slate-50 dark:bg-slate-950 border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center">
                                                @if (!empty($data['seo_twitter_image']))
                                                    <img src="{{ Storage::url($data['seo_twitter_image']) }}" class="w-full h-full object-cover">
                                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-2">
                                                        <label class="cursor-pointer bg-white text-slate-900 px-3 py-1.5 rounded-xl font-bold text-[10px]"><i class="fas fa-edit mr-1"></i>Trocar<input type="file" name="seo_twitter_image" class="hidden"></label>
                                                        <label class="cursor-pointer bg-red-500 text-white px-3 py-1.5 rounded-xl font-bold text-[10px]"><input type="checkbox" name="remove_seo_twitter_image" value="1" class="hidden"><i class="fas fa-trash"></i></label>
                                                    </div>
                                                @else
                                                    <div class="text-center">
                                                        <i class="fas fa-twitter text-2xl text-slate-300 dark:text-slate-700 mb-1"></i>
                                                        <p class="text-[9px] font-black text-slate-400">UPLOAD TWITTER</p>
                                                    </div>
                                                    <input type="file" name="seo_twitter_image" class="absolute inset-0 opacity-0 cursor-pointer">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">Indexação e Canônica</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label class="text-xs font-bold text-slate-500 dark:text-slate-400">Meta Robots</label>
                                            <select name="meta_robots" class="w-full bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2 text-sm font-medium border">
                                                <option value="index,follow">Index, Follow</option>
                                                <option value="noindex,follow">NoIndex, Follow</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-xs font-bold text-slate-500 dark:text-slate-400">Canônica</label>
                                            <input type="url" name="canonical_url" value="{{ $data['canonical_url'] ?? '' }}"
                                                class="w-full bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2 text-sm font-medium border" placeholder="https://...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Abas de Conteúdo (Dinâmicas) -->
                    <div id="dynamic-content-panes">
                        @if(view()->exists("panel.admin.pages.partials.{$page->slug}"))
                            @include("panel.admin.pages.partials.{$page->slug}")
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    @prepend('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const navTabs = document.querySelectorAll('.nav-tab');
                const contentSections = document.querySelectorAll('.tab-pane, #dynamic-content-panes > section, #dynamic-content-panes > div[id^="sec-"]');

                function showTab(targetId) {
                    const id = targetId.startsWith('#') ? targetId.slice(1) : targetId;
                    const targetEl = document.getElementById(id);
                    if(!targetEl) return;

                    // Ocultar todos
                    contentSections.forEach(s => s.classList.add('hidden'));
                    // Mostrar alvo
                    targetEl.classList.remove('hidden');

                    // Atualizar Abas
                    navTabs.forEach(tab => {
                        const isActive = tab.getAttribute('href') === `#${id}`;
                        if(isActive) {
                            tab.classList.add('border-blue-600', 'text-blue-600', 'font-black', 'bg-white', 'dark:bg-slate-900');
                            tab.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400', 'hover:text-blue-600', 'dark:hover:text-blue-200', 'font-bold', 'hover:bg-white', 'dark:hover:bg-slate-900');
                        } else {
                            tab.classList.remove('border-blue-600', 'text-blue-600', 'font-black', 'bg-white', 'dark:bg-slate-900');
                            tab.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400', 'hover:text-blue-600', 'dark:hover:text-blue-200', 'font-bold', 'hover:bg-white', 'dark:hover:bg-slate-900');
                        }
                    });

                    history.replaceState(null, null, `#${id}`);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }

                navTabs.forEach(tab => {
                    tab.addEventListener('click', function(e) {
                        e.preventDefault();
                        showTab(this.getAttribute('href'));
                    });
                });

                // Estado Inicial
                const initialHash = window.location.hash || '#sec-seo';
                showTab(initialHash);

                // Meta counter
                const seoDesc = document.getElementById('seo_description');
                const seoCount = document.getElementById('seo-desc-count');
                if(seoDesc && seoCount) {
                    const update = () => seoCount.textContent = seoDesc.value.length;
                    seoDesc.addEventListener('input', update);
                    update();
                }

                // --- NOVO: Listener para Toggles de Visibilidade das Seções ---
                document.querySelectorAll('.section-toggle').forEach(toggle => {
                    toggle.addEventListener('change', function() {
                        const section = this.getAttribute('data-section');
                        const status = this.checked;
                        const pageId = "{{ $page->id }}";
                        
                        // Feedback visual de carregamento opcional ou desativar temporariamente
                        this.disabled = true;

                        fetch(`/painel/admin/pages/${pageId}/toggle-section`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                section: section,
                                status: status
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.disabled = false;
                            if (data.success) {
                                // Notificação Toasty/Swal Toast conforme diretriz global
                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener('mouseenter', Swal.stopTimer)
                                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                                    }
                                });

                                Toast.fire({
                                    icon: 'success',
                                    title: data.message || 'Visibilidade atualizada!'
                                });
                            } else {
                                throw new Error(data.message || 'Erro ao atualizar visibilidade.');
                            }
                        })
                        .catch(error => {
                            this.disabled = false;
                            this.checked = !status; // Reverter estado em caso de erro
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: error.message || 'Não foi possível salvar a alteração.',
                                confirmButtonColor: '#3b82f6'
                            });
                        });
                    });
                });

                // Global Repeater Engine
                window.initJSONRepeater = function({ containerId, inputId, addButtonId, itemSchema, template, initialData }) {
                    const container = document.getElementById(containerId);
                    const hiddenInput = document.querySelector(`[name="${inputId}"]`);
                    const addButton = document.getElementById(addButtonId);
                    let items = Array.isArray(initialData) ? initialData : [];

                    function sync() { hiddenInput.value = JSON.stringify(items); }

                    function render() {
                        container.innerHTML = '';
                        items.forEach((item, index) => {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'repeater-item group relative bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50 rounded-3xl p-6 mb-4';
                            wrapper.dataset.index = index;
                            wrapper.innerHTML = `
                                <div class="reorder-handle absolute -left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg flex items-center justify-center cursor-grab active:cursor-grabbing opacity-0 group-hover:opacity-100 transition-all shadow-sm z-10">
                                    <i class="fas fa-grip-lines text-slate-400 text-[10px]"></i>
                                </div>
                                <button type="button" class="btn-remove absolute -right-2 -top-2 w-8 h-8 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all z-10">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                                ${template(item, index)}
                            `;
                            wrapper.querySelector('.btn-remove').onclick = () => { items.splice(index, 1); render(); sync(); };
                            wrapper.querySelectorAll('input, textarea, select').forEach(input => {
                                input.oninput = function() {
                                    const fieldMatch = this.name.match(/\[(.*?)\]/);
                                    if (fieldMatch) items[index][fieldMatch[1]] = this.value;
                                    sync();
                                };
                            });
                            container.appendChild(wrapper);
                        });
                        if (window.Sortable && container) {
                            if (container._sortable) container._sortable.destroy();
                            container._sortable = Sortable.create(container, {
                                handle: '.reorder-handle',
                                animation: 150,
                                onEnd: () => {
                                    const newItems = [];
                                    container.querySelectorAll('.repeater-item').forEach(el => newItems.push(items[parseInt(el.dataset.index)]));
                                    items = newItems; render(); sync();
                                }
                            });
                        }
                        sync();
                    }
                    if(addButton) addButton.onclick = () => { items.push({ ...itemSchema }); render(); sync(); };
                    render();
                };
            });
        </script>
    @endprepend
@endsection
