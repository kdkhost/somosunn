@extends('panel.layouts.app')

@section('title', 'Gestão de Conteúdo (CMS)')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.cms.index', ['slug' => 'home']) }}" class="hover:underline">CMS e páginas</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    CMS
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">
                    Gerencie os textos e imagens estáticas do site institucional.
                </p>
            </div>

            <button type="submit" form="cmsForm"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span>Salvar Alterações</span>
            </button>
        </div>

        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Páginas institucionais</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                    Escolha a área que deseja editar e mantenha a comunicação pública da plataforma alinhada.
                </p>
            </div>

            <div class="flex overflow-x-auto no-scrollbar border-b border-slate-100 dark:border-slate-800">
                @php
                    $tabs = [
                        'home' => ['label' => 'Home / Hero', 'icon' => 'fa-home'],
                        'about' => ['label' => 'Sobre Nós', 'icon' => 'fa-info-circle'],
                        'footer' => ['label' => 'Rodapé / Links', 'icon' => 'fa-share-alt'],
                    ];
                @endphp

                @foreach($tabs as $key => $tab)
                    <a href="{{ route('panel.admin.cms.index', ['slug' => $key]) }}"
                        class="flex items-center gap-2 px-6 py-4 text-sm transition whitespace-nowrap border-b-4 {{ $slug === $key
                            ? 'border-blue-600 text-blue-600 font-black bg-blue-50 dark:bg-blue-900/30'
                            : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-200 font-bold hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <i class="fas {{ $tab['icon'] }}"></i>
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <form id="cmsForm" action="{{ route('panel.admin.cms.update', $slug) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">
            @csrf

            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 rounded-2xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                        <div>
                            <h4 class="text-red-800 dark:text-red-300 font-bold text-sm">Atenção</h4>
                            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 mt-1 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl p-4 text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            <div
                class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 md:p-8 transition-colors duration-300">
                @if($slug === 'home')
                    <div class="grid grid-cols-1 xl:grid-cols-[1.4fr,0.9fr] gap-8">
                        <div class="space-y-6">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">
                                    Título Principal (Hero)
                                </label>
                                <input type="text" name="hero_title" value="{{ old('hero_title', $contents['hero_title'] ?? '') }}"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">
                                    Subtítulo (Hero)
                                </label>
                                <textarea name="hero_subtitle" rows="4"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('hero_subtitle', $contents['hero_subtitle'] ?? '') }}</textarea>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">
                                    Texto (Hero)
                                </label>
                                <textarea name="hero_text" rows="5"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('hero_text', $contents['hero_text'] ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase transition-colors">
                                Imagem de Fundo (Hero)
                            </label>

                            @if(!empty($contents['hero_image']))
                                <div class="rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
                                    <img src="{{ asset('storage/' . $contents['hero_image']) }}" alt="Hero atual"
                                        class="w-full aspect-video object-cover">
                                </div>

                                <label class="inline-flex items-center gap-2 text-sm font-semibold text-rose-600 dark:text-rose-400">
                                    <input type="checkbox" name="remove_hero_image" value="1"
                                        class="w-4 h-4 text-rose-600 border-slate-300 dark:border-slate-700 rounded focus:ring-rose-500 bg-white dark:bg-slate-950">
                                    Remover imagem atual
                                </label>
                            @endif

                            <input type="file" name="hero_image" accept="image/*" data-max-files="1"
                                class="w-full">

                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Use uma imagem horizontal para o destaque principal da home.
                            </p>
                        </div>
                    </div>
                @elseif($slug === 'about')
                    <div class="space-y-8">
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-3 transition-colors">
                                Nosso Manifesto
                            </label>
                            <textarea name="manifesto" class="cms-editor">{{ old('manifesto', $contents['manifesto'] ?? '') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-8 border-t border-slate-100 dark:border-slate-800 pt-8">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">
                                    Visão
                                </label>
                                <textarea name="vision" rows="6"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('vision', $contents['vision'] ?? '') }}</textarea>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">
                                    Valores
                                </label>
                                <textarea name="values" rows="6"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('values', $contents['values'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                @elseif($slug === 'footer')
                    <div class="grid grid-cols-1 gap-6 max-w-5xl">
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">
                                Instagram URL
                            </label>
                            <div class="relative">
                                <i class="fab fa-instagram absolute left-4 top-1/2 -translate-y-1/2 text-pink-500"></i>
                                <input type="url" name="instagram_url" value="{{ old('instagram_url', $contents['instagram_url'] ?? '') }}"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">
                                LinkedIn URL
                            </label>
                            <div class="relative">
                                <i class="fab fa-linkedin-in absolute left-4 top-1/2 -translate-y-1/2 text-blue-700"></i>
                                <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $contents['linkedin_url'] ?? '') }}"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">
                                YouTube URL
                            </label>
                            <div class="relative">
                                <i class="fab fa-youtube absolute left-4 top-1/2 -translate-y-1/2 text-red-600"></i>
                                <input type="url" name="youtube_url" value="{{ old('youtube_url', $contents['youtube_url'] ?? '') }}"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">
                                Facebook URL
                            </label>
                            <div class="relative">
                                <i class="fab fa-facebook-f absolute left-4 top-1/2 -translate-y-1/2 text-blue-600"></i>
                                <input type="url" name="facebook_url" value="{{ old('facebook_url', $contents['facebook_url'] ?? '') }}"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02]">
                        <i class="fas fa-save mr-2"></i> Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!(window.jQuery && $.fn && $.fn.summernote)) {
                return;
            }

            $('.cms-editor').each(function () {
                const $field = $(this);

                if ($field.next('.note-editor').length) {
                    return;
                }

                $field.summernote({
                    lang: 'pt-BR',
                    placeholder: 'Digite o conteúdo aqui...',
                    tabsize: 2,
                    height: 350,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link']],
                        ['view', ['fullscreen', 'codeview']],
                    ],
                });
            });
        });
    </script>
@endpush
