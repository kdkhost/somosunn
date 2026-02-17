@extends('panel.layouts.app')

@section('title', 'Gestão de Conteúdo (CMS)')

@section('content')
    <div x-data="{ activeTab: '{{ $slug }}' }" class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">CMS</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Gerencie os textos e imagens
                    estáticas do seu site oficial.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" form="cmsForm"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Salvar Alterações</span>
                </button>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div
            class="bg-slate-50 dark:bg-slate-900/50 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800 inline-flex items-center gap-1.5 mb-6">
            <a href="{{ route('panel.admin.cms.index', ['slug' => 'home']) }}"
                :class="activeTab === 'home' 
                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                    : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 shadow-sm'"
                class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                <i class="fas fa-home"></i>
                <span>Home / Hero</span>
            </a>
            <a href="{{ route('panel.admin.cms.index', ['slug' => 'about']) }}"
                :class="activeTab === 'about' 
                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                    : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 shadow-sm'"
                class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                <i class="fas fa-info-circle"></i>
                <span>Sobre Nós</span>
            </a>
            <a href="{{ route('panel.admin.cms.index', ['slug' => 'footer']) }}"
                :class="activeTab === 'footer' 
                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                    : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 shadow-sm'"
                class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                <i class="fas fa-shoe-prints"></i>
                <span>Rodapé / Links</span>
            </a>
        </div>

        <form id="cmsForm" action="{{ route('panel.admin.cms.update', $slug) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">
            @csrf

            <div
                class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-8 transition-colors duration-300">

                @if($slug === 'home')
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 text-balance transition-colors">Título
                                    Principal (Hero)</label>
                                <input type="text" name="hero_title" value="{{ $contents['hero_title'] ?? '' }}"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-bold text-xl">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Subtítulo
                                    (Hero)</label>
                                <textarea name="hero_subtitle" rows="4"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-600 dark:text-slate-400 font-medium">{{ $contents['hero_subtitle'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-4 transition-colors">Imagem
                                de Fundo (Hero)</label>
                            <div class="space-y-4">
                                @if(isset($contents['hero_image']))
                                    <div
                                        class="relative group aspect-video rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 transition-colors">
                                        <img src="{{ asset('storage/' . $contents['hero_image']) }}"
                                            class="w-full h-full object-cover">
                                        <div
                                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <label class="flex items-center gap-2 text-white font-bold cursor-pointer">
                                                <input type="checkbox" name="remove_hero_image" value="1"
                                                    class="w-4 h-4 rounded text-blue-600">
                                                <span>Remover Imagem</span>
                                            </label>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" name="hero_image"
                                    class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/20 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/30 cursor-pointer">
                            </div>
                        </div>
                    </div>

                @elseif($slug === 'about')
                    <div class="space-y-8">
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-4 transition-colors">Nosso
                                Manifesto</label>
                            <textarea name="manifesto" class="cms-editor">{{ $contents['manifesto'] ?? '' }}</textarea>
                        </div>

                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t border-slate-100 dark:border-slate-800 pt-8 transition-colors">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-4 transition-colors">Visão</label>
                                <textarea name="vision" rows="5"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl outline-none focus:border-blue-500 transition-all dark:text-white">{{ $contents['vision'] ?? '' }}</textarea>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-4 transition-colors">Valores</label>
                                <textarea name="values" rows="5"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl outline-none focus:border-blue-500 transition-all dark:text-white">{{ $contents['values'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                @elseif($slug === 'footer')
                    <div class="max-w-3xl space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Instagram
                                    URL</label>
                                <div class="relative">
                                    <i class="fab fa-instagram absolute left-4 top-1/2 -translate-y-1/2 text-pink-500"></i>
                                    <input type="url" name="instagram_url" value="{{ $contents['instagram_url'] ?? '' }}"
                                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:border-blue-500 outline-none transition-all text-sm font-medium dark:text-white">
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">LinkedIn
                                    URL</label>
                                <div class="relative">
                                    <i class="fab fa-linkedin-in absolute left-4 top-1/2 -translate-y-1/2 text-blue-700"></i>
                                    <input type="url" name="linkedin_url" value="{{ $contents['linkedin_url'] ?? '' }}"
                                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:border-blue-500 outline-none transition-all text-sm font-medium dark:text-white">
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">YouTube
                                    URL</label>
                                <div class="relative">
                                    <i class="fab fa-youtube absolute left-4 top-1/2 -translate-y-1/2 text-red-600"></i>
                                    <input type="url" name="youtube_url" value="{{ $contents['youtube_url'] ?? '' }}"
                                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:border-blue-500 outline-none transition-all text-sm font-medium dark:text-white">
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Facebook
                                    URL</label>
                                <div class="relative">
                                    <i class="fab fa-facebook-f absolute left-4 top-1/2 -translate-y-1/2 text-blue-600"></i>
                                    <input type="url" name="facebook_url" value="{{ $contents['facebook_url'] ?? '' }}"
                                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:border-blue-500 outline-none transition-all text-sm font-medium dark:text-white">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </form>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    @endpush
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
        <script>
            $(document).ready(function () {
                $('.cms-editor').summernote({
                    placeholder: 'Digite o conteúdo aqui...',
                    tabsize: 2,
                    height: 350,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link', 'picture']],
                        ['view', ['fullscreen', 'codeview']]
                    ]
                });
            });
        </script>
    @endpush

@endsection