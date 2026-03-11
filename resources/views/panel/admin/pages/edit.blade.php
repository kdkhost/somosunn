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
                                'portal' => ['Hero', 'Estatísticas', 'CTA'],
                                'premium' => ['Hero', 'Planos'],
                                'somos-unicas' => ['Identidade', 'Banner', 'Conteúdo'],
                                'somos-unicas-sobre' => ['Identidade', 'Conteúdo'],
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
                                Configurações de SEO</h2>
                        </div>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título interno da
                                    página</label>
                                <input type="text" name="title" value="{{ old('title', $page->title) }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                                    placeholder="Ex: Home - Somos UNN">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Meta Title</label>
                                <input type="text" name="seo_title" value="{{ old('seo_title', $data['seo_title'] ?? '') }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                                    placeholder="Ex: Sobre a Somos UNN">
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Meta
                                    Descrição</label>
                                <textarea name="seo_description" rows="2"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                                    placeholder="Breve resumo para resultados de busca (Google)">{{ old('seo_description', $data['seo_description'] ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Imagem de
                                compartilhamento (SEO)</label>
                            <input type="file" name="seo_image" accept="image/*"
                                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                            @if (!empty($data['seo_image']))
                                <div class="rounded-[1.5rem] overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <img src="{{ Storage::url($data['seo_image']) }}" alt="Preview SEO"
                                        class="w-full h-48 object-cover">
                                </div>
                                <label
                                    class="inline-flex items-center gap-2 text-sm font-semibold text-rose-600 dark:text-rose-400">
                                    <input type="checkbox" name="remove_seo_image" value="1"
                                        class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                    Remover imagem atual
                                </label>
                            @endif
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

                const sections = document.querySelectorAll('section[id], div[id^="sec-"]');
                const navLinks = document.querySelectorAll('#section-nav a');

                window.addEventListener('scroll', () => {
                    let current = '';
                    sections.forEach(section => {
                        const sectionTop = section.offsetTop;
                        if (pageYOffset >= sectionTop - 120) {
                            current = section.getAttribute('id');
                        }
                    });

                    navLinks.forEach(link => {
                        link.classList.remove('text-blue-600', 'bg-blue-50', 'dark:bg-blue-900/20', 'dark:text-blue-400');
                        link.classList.add('text-slate-600', 'dark:text-slate-400');
                        if (link.getAttribute('href') === `#${current}`) {
                            link.classList.remove('text-slate-600', 'dark:text-slate-400');
                            link.classList.add('text-blue-600', 'bg-blue-50', 'dark:bg-blue-900/20', 'dark:text-blue-400');
                        }
                    });
                });
            });
        </script>
    @endprepend
@endsection
