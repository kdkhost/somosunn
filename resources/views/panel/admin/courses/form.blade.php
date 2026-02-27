@extends('panel.layouts.app')

@section('title', $course->exists ? 'Editar Curso' : 'Novo Curso')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.courses.index') }}" class="hover:underline transition-all">Cursos</a>
    <span class="mx-2 text-slate-300 dark:text-slate-700 transition-colors">/</span>
    <span class="text-slate-500 dark:text-slate-400 transition-colors">{{ $course->exists ? 'Editar' : 'Novo' }}</span>
@endsection

@section('panel_content')
    <div x-data="{ tab: 'general', floating: {{ $course->video_floating_enabled ? 'true' : 'false' }} }" class="space-y-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between items-start gap-6">
            <div class="flex items-center gap-5">
                <a href="{{ route('panel.admin.courses.index') }}"
                    class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-200 dark:hover:border-blue-900 transition-all shadow-sm group">
                    <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-800 dark:text-white transition-colors">
                        {{ $course->exists ? 'Editar' : 'Novo' }} Curso
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors font-medium">
                        {{ $course->exists ? 'Atualize os detalhes e o conteúdo pedagógico.' : 'Configure as informações para o novo treinamento.' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                @if($course->exists)
                    <a href="{{ route('courses.show', $course->slug ?: $course->id) }}" target="_blank"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                        <i class="fas fa-external-link-alt text-slate-400 dark:text-slate-500"></i>
                        <span>Ver na Loja</span>
                    </a>
                @endif
                <button type="submit" form="courseForm"
                    class="flex-1 sm:flex-none px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-xl shadow-blue-500/20 transition transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Salvar</span>
                </button>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div
            class="bg-slate-50 dark:bg-slate-950 p-2 rounded-[2rem] border border-slate-100 dark:border-slate-800 inline-flex items-center gap-2 mb-2 transition-all shadow-inner">
            <button type="button" @click="tab = 'general'"
                :class="tab === 'general' 
                                        ? 'bg-blue-600 text-white shadow-xl shadow-blue-500/30 font-black scale-105' 
                                        : 'text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-200 font-bold hover:bg-white dark:hover:bg-slate-900'"
                class="px-6 py-3 rounded-2xl text-xs uppercase tracking-widest transition-all flex items-center gap-2">
                <i class="fas fa-info-circle"></i>
                <span>Geral</span>
            </button>
            <button type="button" @click="tab = 'pricing'"
                :class="tab === 'pricing' 
                                        ? 'bg-blue-600 text-white shadow-xl shadow-blue-500/30 font-black scale-105' 
                                        : 'text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-200 font-bold hover:bg-white dark:hover:bg-slate-900'"
                class="px-6 py-3 rounded-2xl text-xs uppercase tracking-widest transition-all flex items-center gap-2">
                <i class="fas fa-tag"></i>
                <span>Preço</span>
            </button>
            @if($course->exists)
                <button type="button" @click="tab = 'lessons'"
                    :class="tab === 'lessons' 
                                                                ? 'bg-blue-600 text-white shadow-xl shadow-blue-500/30 font-black scale-105' 
                                                                : 'text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-200 font-bold hover:bg-white dark:hover:bg-slate-900'"
                    class="px-6 py-3 rounded-2xl text-xs uppercase tracking-widest transition-all flex items-center gap-2">
                    <i class="fas fa-layer-group"></i>
                    <span>Grade Curricular ({{ $course->lessons_count ?? $course->lessons()->count() }})</span>
                </button>
                <button type="button" @click="tab = 'certificate'"
                    :class="tab === 'certificate' 
                                                                ? 'bg-blue-600 text-white shadow-xl shadow-blue-500/30 font-black scale-105' 
                                                                : 'text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-200 font-bold hover:bg-white dark:hover:bg-slate-900'"
                    class="px-6 py-3 rounded-2xl text-xs uppercase tracking-widest transition-all flex items-center gap-2">
                    <i class="fas fa-certificate"></i>
                    <span>Certificado</span>
                </button>
            @endif
        </div>

        <form id="courseForm" method="POST"
            action="{{ $course->exists ? route('panel.admin.courses.update', $course) : route('panel.admin.courses.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if($course->exists) @method('PUT') @endif
            <input type="hidden" name="certificate_settings" id="certificate_settings_input">

            {{-- Tab: General --}}
            <div x-show="tab === 'general'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-5 mb-8">
                            <div
                                class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 class="font-bold text-xl text-slate-800 dark:text-white transition-colors">Informações
                                Principais</h3>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">Título
                                    do Curso</label>
                                <input type="text" name="title" value="{{ old('title', $course->title) }}" required
                                    class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-800 dark:text-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all text-xl font-bold placeholder:text-slate-300"
                                    placeholder="Ex: Domínio do Backend com Laravel">
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">Descrição
                                    Curta</label>
                                <textarea name="short_description" rows="3" maxlength="500"
                                    class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-700 dark:text-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all resize-none font-medium placeholder:text-slate-300"
                                    placeholder="Um breve resumo que aparece nos cards de curso...">{{ old('short_description', $course->short_description) }}</textarea>
                                <p
                                    class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 font-medium italic transition-colors text-right">
                                    Máximo 500 caracteres.</p>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">Descrição
                                    Completa</label>
                                <textarea name="full_description" id="fullDescription" rows="10"
                                    class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-700 dark:text-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all">{{ old('full_description', $course->full_description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-5 mb-8">
                            <div
                                class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <i class="fas fa-video"></i>
                            </div>
                            <h3 class="font-bold text-xl text-slate-800 dark:text-white transition-colors">Player &
                                Experiência</h3>
                        </div>

                        <div class="space-y-6">
                            <label
                                class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50/50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 hover:border-blue-200 dark:hover:border-blue-900 transition-all cursor-pointer group">
                                <div class="flex items-center h-5 mt-1">
                                    <input type="checkbox" name="video_block_download" id="video_block_download" value="1"
                                        {{ $course->video_block_download ? 'checked' : '' }}
                                        class="w-5 h-5 text-blue-600 border-slate-300 dark:border-slate-800 rounded-lg focus:ring-blue-500 bg-white dark:bg-slate-900 transition-all">
                                </div>
                                <div>
                                    <span
                                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">Bloquear
                                        Download</span>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400 transition-colors mt-0.5 font-medium">
                                        Remove botões e menus de download nativos do navegador.</p>
                                </div>
                            </label>

                            <div
                                class="p-5 rounded-2xl bg-slate-50/50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 space-y-5 transition-all">
                                <label class="flex items-start gap-4 cursor-pointer group">
                                    <div class="flex items-center h-5 mt-1">
                                        <input type="checkbox" x-model="floating" id="video_floating_enabled" value="1"
                                            name="video_floating_enabled" {{ $course->video_floating_enabled ? 'checked' : '' }}
                                            class="w-5 h-5 text-blue-600 border-slate-300 dark:border-slate-800 rounded-lg focus:ring-blue-500 bg-white dark:bg-slate-900 transition-all">
                                    </div>
                                    <div>
                                        <span
                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">Mini
                                            Player Flutuante</span>
                                        <p
                                            class="text-xs text-slate-500 dark:text-slate-400 transition-colors mt-0.5 font-medium">
                                            Ao rolar a página, o vídeo fica fixo no canto da tela (Picture-in-Picture).</p>
                                    </div>
                                </label>

                                <div x-show="floating" x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="grid grid-cols-2 gap-6 pl-9 pt-2">
                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Largura
                                            (px)</label>
                                        <input type="number" name="video_floating_width"
                                            value="{{ $course->video_floating_width ?? 420 }}"
                                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Altura
                                            (px)</label>
                                        <input type="number" name="video_floating_height"
                                            value="{{ $course->video_floating_height ?? 236 }}"
                                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    {{-- Publish Action --}}
                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 mb-6">
                            <div
                                class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Publicação</h3>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 transition-colors">Status
                                    do Curso</label>
                                <select name="status"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all text-sm font-bold">
                                    <option value="draft" {{ $course->status == 'draft' ? 'selected' : '' }}>Rascunho</option>
                                    <option value="published" {{ $course->status == 'published' ? 'selected' : '' }}>Publicado
                                    </option>
                                    <option value="archived" {{ $course->status == 'archived' ? 'selected' : '' }}>Arquivado
                                    </option>
                                    <option value="paused" {{ $course->status == 'paused' ? 'selected' : '' }}>Vendas Pausadas
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-4 pt-4 border-t border-slate-50 dark:border-slate-800">
                                <label
                                    class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30">
                                    <span
                                        class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Destaque
                                        na Home</span>
                                    <div class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_featured" value="1" class="sr-only peer" {{ $course->is_featured ? 'checked' : '' }}>
                                        <div
                                            class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500">
                                        </div>
                                    </div>
                                </label>

                                <label
                                    class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30">
                                    <span
                                        class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Certificado</span>
                                    <div class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_certificate_enabled" value="1" class="sr-only peer"
                                            {{ $course->is_certificate_enabled ? 'checked' : '' }}>
                                        <div
                                            class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600">
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Image Thumbnail --}}
                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 mb-6">
                            <div
                                class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs">
                                <i class="fas fa-image"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Capa do Curso</h3>
                        </div>

                        <div x-data="{ photoPreview: '{{ $course->thumbnail ? asset($course->thumbnail) : '' }}' }"
                            class="space-y-4">
                            <div
                                class="relative group aspect-video w-full rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden transition-all group-hover:border-blue-300 dark:group-hover:border-blue-500 shadow-inner">
                                <template x-if="!photoPreview">
                                    <div
                                        class="flex flex-col items-center text-slate-400 dark:text-slate-600 transition-colors">
                                        <i class="fas fa-cloud-upload-alt text-4xl mb-3"></i>
                                        <span
                                            class="text-[10px] font-black uppercase tracking-widest text-center px-6">Upload
                                            1280x720</span>
                                    </div>
                                </template>
                                <template x-if="photoPreview">
                                    <img :src="photoPreview" class="w-full h-full object-cover">
                                </template>
                                <div @click="$refs.thumbnail.click()"
                                    class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center cursor-pointer backdrop-blur-sm">
                                    <div class="flex flex-col items-center gap-2 text-white">
                                        <div
                                            class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center border border-white/30">
                                            <i class="fas fa-camera"></i>
                                        </div>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Alterar Capa</span>
                                    </div>
                                </div>
                                <input type="file" name="thumbnail" x-ref="thumbnail" class="hidden" accept="image/*"
                                    @change="
                                                    const reader = new FileReader();
                                                    reader.onload = (e) => { photoPreview = e.target.result; toastr.success('Capa selecionada!'); };
                                                    reader.readAsDataURL($event.target.files[0]);
                                                ">
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Instrutor</h3>
                        </div>
                        <input type="text" name="author_name"
                            value="{{ old('author_name', $course->author_name ?? Auth::user()->name) }}"
                            class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all"
                            placeholder="Nome do autor">
                    </div>
                </div>
            </div>

            {{-- Tab: Pricing --}}
            <div x-show="tab === 'pricing'" class="max-w-4xl space-y-8">
                <div
                    class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
                    <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-5 mb-8">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <i class="fas fa-coins"></i>
                        </div>
                        <h3 class="font-bold text-xl text-slate-800 dark:text-white transition-colors">Configuração de Venda
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label
                                class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">Preço
                                do Investimento</label>
                            <div class="relative group">
                                <span
                                    class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-600 font-black group-focus-within:text-blue-600 transition-colors">R$</span>
                                <input type="text" name="price" value="{{ old('price', $course->price) }}"
                                    class="w-full pl-14 pr-5 py-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-800 dark:text-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all text-2xl font-black mask-money"
                                    placeholder="0,00">
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium italic">Valor principal exibido no checkout.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden group transition-all hover:shadow-md">
                    <div
                        class="absolute -top-4 -right-4 w-32 h-32 bg-amber-500/5 rounded-full blur-3xl group-hover:bg-amber-500/10 transition-all">
                    </div>

                    <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-5 mb-8">
                        <div
                            class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3 class="font-bold text-xl text-slate-800 dark:text-white transition-colors">Oferta de Lançamento
                            / Relâmpago</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label
                                class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">Preço
                                Promocional</label>
                            <div class="relative group">
                                <span
                                    class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-600 font-black group-focus-within:text-orange-500 transition-colors">R$</span>
                                <input type="text" name="flash_sale_price"
                                    value="{{ old('flash_sale_price', $course->flash_sale_price) }}"
                                    class="w-full pl-14 pr-5 py-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-800 dark:text-white focus:outline-none focus:ring-4 focus:ring-orange-500/10 focus:border-orange-500 transition-all text-2xl font-black mask-money"
                                    placeholder="0,00">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label
                                class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">Encerramento
                                da Oferta</label>
                            <input type="datetime-local" name="flash_sale_ends_at"
                                value="{{ old('flash_sale_ends_at', $course->flash_sale_ends_at ? $course->flash_sale_ends_at->format('Y-m-d\TH:i') : '') }}"
                                class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-800 dark:text-white focus:outline-none focus:ring-4 focus:ring-orange-500/10 focus:border-orange-500 transition-all text-sm font-bold">
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 italic font-medium">O valor
                                retornará ao preço padrão automaticamente nesta data.</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($course->exists)
                {{-- Tab: Content/Lessons --}}
                <div x-show="tab === 'lessons'" class="space-y-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 dark:text-white transition-colors">Grade Curricular
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium italic">Organize os módulos e aulas
                                do seu treinamento.</p>
                        </div>
                        <button type="button" @click="openNewLessonModal()"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-xl shadow-blue-500/20 transition transform hover:scale-[1.05] active:scale-[0.95]">
                            <i class="fas fa-plus"></i>
                            <span>Nova Aula</span>
                        </button>
                    </div>

                    <div id="lessons-list" class="space-y-4">
                        @forelse($course->lessons as $lesson)
                            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center gap-6 group hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-xl hover:shadow-blue-500/5 transition-all"
                                data-id="{{ $lesson->id }}">
                                <div
                                    class="cursor-move w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-950 flex items-center justify-center text-slate-400 dark:text-slate-600 hover:text-blue-600 dark:hover:text-blue-400 transition-all border border-slate-100 dark:border-slate-800">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-1">
                                        <span
                                            class="px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-900/20 text-[10px] font-black text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50 uppercase tracking-tighter">
                                            Aula {{ $lesson->order }}
                                        </span>
                                        <h4 class="font-bold text-lg text-slate-800 dark:text-white truncate transition-colors">
                                            {{ $lesson->title }}
                                        </h4>
                                    </div>
                                    <div class="flex items-center gap-4 text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                        <span
                                            class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-950 px-2 py-1 rounded-lg border border-slate-100 dark:border-slate-800 transition-colors">
                                            <i class="fas fa-clock text-blue-500"></i>
                                            {{ gmdate("H:i:s", $lesson->duration) }}
                                        </span>
                                        @if($lesson->is_free_preview)
                                            <span
                                                class="flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 px-2 py-1 rounded-lg border border-emerald-100 dark:border-emerald-800/50 transition-colors">
                                                <i class="fas fa-check-circle"></i>
                                                {{ (($lesson->free_preview_mode ?? 'full') === 'time' && (int) ($lesson->free_preview_seconds ?? 0) > 0) ? ('Preview ' . gmdate('i:s', (int) $lesson->free_preview_seconds)) : 'Preview Grátis' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                    <button type="button" @click="editLesson({{ $lesson->id }})"
                                        class="w-10 h-10 inline-flex items-center justify-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-200 dark:hover:border-blue-900 transition-all shadow-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button"
                                        onclick="return confirmDeleteLesson('{{ route('courses.lessons.destroy', [$course, $lesson]) }}');"
                                        class="w-10 h-10 inline-flex items-center justify-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:border-red-200 dark:hover:border-red-900 transition-all shadow-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div
                                class="flex flex-col items-center justify-center p-12 bg-slate-50/50 dark:bg-slate-950/50 rounded-[3rem] border-4 border-dashed border-slate-100 dark:border-slate-900 transition-all group hover:border-blue-100 dark:hover:border-blue-950">
                                <div
                                    class="w-20 h-20 rounded-3xl bg-white dark:bg-slate-900 shadow-xl flex items-center justify-center text-slate-200 dark:text-slate-800 mb-6 group-hover:scale-110 group-hover:rotate-12 transition-all">
                                    <i class="fas fa-layer-group text-4xl"></i>
                                </div>
                                <h4 class="font-black text-xl text-slate-800 dark:text-white mb-2 transition-colors">Grade Vazia
                                </h4>
                                <p class="text-slate-500 dark:text-slate-400 text-center max-w-sm font-medium transition-colors">
                                    Comece a estruturar seu curso adicionando sua primeira vídeo-aula agora mesmo!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Tab: Certificate --}}
                <div x-show="tab === 'certificate'" class="space-y-8">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 dark:text-white transition-colors">Editor de
                                Certificado</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium italic">Personalize o documento de
                                conclusão dos seus alunos.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="previewCertificate()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                                <i class="fas fa-eye"></i>
                                <span>Preview PDF</span>
                            </button>
                        </div>
                    </div>

                    <div
                        class="bg-blue-600 rounded-[2.5rem] p-8 shadow-2xl shadow-blue-500/20 flex flex-col md:flex-row items-center gap-8 relative overflow-hidden group">
                        <div
                            class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-3xl transition-transform group-hover:scale-150 duration-700">
                        </div>
                        <div
                            class="absolute -left-20 -bottom-20 w-64 h-64 bg-black/10 rounded-full blur-3xl transition-transform group-hover:scale-150 duration-700">
                        </div>

                        <div
                            class="w-16 h-16 rounded-3xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-3xl shrink-0 border border-white/30 shadow-inner">
                            <i class="fas fa-magic"></i>
                        </div>
                        <div class="relative z-10 flex-1 text-center md:text-left">
                            <h5 class="text-2xl font-black text-white mb-2">Designer Visual Interativo</h5>
                            <p class="text-white/80 font-medium text-sm leading-relaxed max-w-2xl">
                                O design deste certificado é editado em tempo real. Arraste os elementos para posicionar
                                assinaturas, nome do aluno e datas no local perfeito.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                        {{-- Canvas Area --}}
                        <div class="lg:col-span-3">
                            <div
                                class="bg-slate-900 rounded-[3rem] p-12 border border-slate-800 shadow-2xl relative overflow-hidden flex items-center justify-center transition-all">
                                <div
                                    class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(59,130,246,0.1),transparent)] pointer-events-none">
                                </div>

                                {{-- Editor Canvas --}}
                                <div id="cert-canvas"
                                    class="bg-white relative shadow-[0_40px_100px_-20px_rgba(0,0,0,0.8)] origin-center transition-all overflow-hidden"
                                    style="width: 842px; height: 595px; border-radius: 4px;">
                                    {{-- BG Image --}}
                                    <img id="cert-bg-img"
                                        src="{{ $course->certificate_bg ? asset($course->certificate_bg) : '' }}"
                                        class="absolute inset-0 w-full h-full object-cover {{ $course->certificate_bg ? '' : 'hidden' }}">

                                    <div id="cert-elements-layer" class="absolute inset-0 z-10">
                                        {{-- Draggable elements will be injected here --}}
                                    </div>

                                    {{-- Ruler/Guides Overlay --}}
                                    <div class="absolute inset-0 border-[20px] border-black/5 pointer-events-none z-0"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Certificate Sidebar --}}
                        <div class="space-y-6">
                            <div
                                class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
                                <div class="flex items-center gap-3 mb-6">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-800 dark:text-white transition-colors">Background</h4>
                                </div>

                                <div class="space-y-5">
                                    <div class="group">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 transition-colors">Imagem
                                            de Fundo</label>
                                        <div class="relative">
                                            <input type="file" name="certificate_bg" id="certificate_bg" accept="image/*"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                            <div
                                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-500 flex items-center gap-2 group-hover:border-blue-300 transition-all shadow-inner">
                                                <i class="fas fa-upload text-blue-500"></i>
                                                <span>Selecionar Arquivo</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 transition-colors">Enquadramento</label>
                                        <select id="cert-bg-fit"
                                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-black text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                                            <option value="cover">Cobrir (Aspecto)</option>
                                            <option value="stretch">Esticar total</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
                                <div class="flex items-center gap-3 mb-6">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs">
                                        <i class="fas fa-signature"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-800 dark:text-white transition-colors">Assinatura Digital
                                    </h4>
                                </div>

                                <div class="space-y-5">
                                    <div
                                        class="aspect-[3/1] rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden transition-all shadow-inner relative group">
                                        @if($course->instructor_signature)
                                            <img src="{{ asset($course->instructor_signature) }}"
                                                class="max-h-full object-contain p-2">
                                        @else
                                            <div class="flex flex-col items-center gap-1 text-slate-300 dark:text-slate-700">
                                                <i class="fas fa-signature text-2xl transition-colors"></i>
                                                <span class="text-[9px] font-black uppercase tracking-widest">Nenhuma</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="relative group">
                                        <input type="file" name="instructor_signature" accept="image/png"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                        <div
                                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-500 flex items-center gap-2 group-hover:border-purple-300 transition-all shadow-inner">
                                            <i class="fas fa-pen-nib text-purple-500"></i>
                                            <span>Substituir Assinatura</span>
                                        </div>
                                    </div>

                                    <p
                                        class="text-[9px] text-slate-400 dark:text-slate-500 font-medium italic text-center transition-colors">
                                        Utilize arquivos PNG com fundo transparente para melhor resultado.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </form>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
        <script>
            const isExistingCourse = @json($course->exists);
            const lessonReorderUrl = @json($course->exists ? route('panel.admin.courses.lessons.reorder', $course) : null);
            const lessonStoreUrl = @json($course->exists ? route('panel.admin.courses.lessons.store', $course) : null);
            const lessonBaseUrl = lessonStoreUrl;
            const lessonContentImageUploadUrl = @json($course->exists ? route('panel.admin.courses.lessons.content-image', $course) : null);
            const certificatePreviewUrl = @json($course->exists ? route('panel.admin.courses.certificate.preview', $course) : null);

            document.addEventListener('DOMContentLoaded', function () {
                // Sortable logic for lessons
                const el = document.getElementById('lessons-list');
                if (el && lessonReorderUrl) {
                    Sortable.create(el, {
                        animation: 150,
                        handle: '.cursor-move',
                        ghostClass: 'bg-blue-50',
                        onEnd: function () {
                            const orders = Array.from(el.querySelectorAll('[data-id]'))
                                .map((item, index) => ({
                                    id: Number(item.dataset.id || 0),
                                    order: index + 1
                                }))
                                .filter((item) => item.id > 0);

                            if (!orders.length) {
                                return;
                            }

                            $.post(lessonReorderUrl, {
                                _token: '{{ csrf_token() }}',
                                lessons: orders
                            }).done(() => {
                                toastr.success('Ordem atualizada!');
                            }).fail((xhr) => {
                                toastr.error(xhr?.responseJSON?.message || 'Erro ao reordenar.');
                            });
                        }
                    });
                }
            });

            // Money Mask Helper
            document.querySelectorAll('.mask-money').forEach(input => {
                input.addEventListener('input', function (e) {
                    let value = e.target.value.replace(/\D/g, "");
                    if (!value) { e.target.value = ""; return; }
                    value = (value / 100).toFixed(2) + "";
                    value = value.replace(".", ",");
                    value = value.replace(/(\d)(\d{3},\d{2})$/g, "$1.$2");
                    e.target.value = value;
                });
            });

            // Lesson Modal logic
            function openModal(id) {
                document.getElementById(id).classList.remove('hidden');
                document.getElementById(id).classList.add('flex');
            }
            function closeModal(id) {
                document.getElementById(id).classList.add('hidden');
                document.getElementById(id).classList.remove('flex');
            }

            function updateLessonPreviewConfig() {
                const isPreview = $('#lessonPreview').is(':checked');
                const mode = $('#lessonPreviewMode').val() || 'full';
                const isTimeMode = isPreview && mode === 'time';

                $('#lessonPreviewConfig').toggleClass('hidden', !isPreview);
                $('#lessonPreviewSecondsGroup').toggleClass('hidden', !isTimeMode);
                $('#lessonPreviewSeconds').prop('required', isTimeMode);
            }

            function openNewLessonModal() {
                const form = document.getElementById('lessonForm');
                if (form) {
                    form.reset();
                }

                $('#lessonId').val('');
                const nextOrder = Math.max(1, document.querySelectorAll('#lessons-list [data-id]').length + 1);
                $('#lessonOrder').val(nextOrder);
                $('#lessonPreview').prop('checked', false);
                $('#lessonPreviewMode').val('full');
                $('#lessonPreviewSeconds').val('');
                $('#lessonVideo').val('');
                $('#lessonVideoFile').val('');
                $('#lessonVideoFileLabel').html('<i class="fas fa-cloud-upload-alt text-lg"></i><span>Selecionar video para envio</span>');
                if (window.jQuery && $.fn && $.fn.summernote && $('#lessonContent').next('.note-editor').length) {
                    $('#lessonContent').summernote('code', '');
                } else {
                    $('#lessonContent').val('');
                }
                $('#attachmentList').empty();
                updateLessonPreviewConfig();
                openModal('lesson-modal');
            }

            function editLesson(id) {
                if (!lessonBaseUrl) return;
                $.get(lessonBaseUrl + '/' + id + '/details', function (lesson) {
                    $('#lessonId').val(id);
                    $('#lessonTitle').val(lesson.title);
                    $('#lessonOrder').val(lesson.order);
                    $('#lessonDuration').val(lesson.duration);
                    $('#lessonPreview').prop('checked', !!lesson.is_free_preview);
                    $('#lessonPreviewMode').val(lesson.free_preview_mode || 'full');
                    $('#lessonPreviewSeconds').val(lesson.free_preview_seconds || '');
                    if (lesson.video_has_upload) {
                        const statusHls = (lesson.video_transcode_status || '') === 'ready'
                            ? 'Video interno protegido (HLS ativo)'
                            : 'Video interno protegido (processando)';
                        $('#lessonVideo').val('');
                        $('#lessonVideoFile').val('');
                        $('#lessonVideoFileLabel').html('<i class="fas fa-lock text-lg"></i><span>' + statusHls + '</span>');
                    } else {
                        $('#lessonVideo').val(lesson.video_url || '');
                        $('#lessonVideoFile').val('');
                        $('#lessonVideoFileLabel').html('<i class="fas fa-cloud-upload-alt text-lg"></i><span>Selecionar video para envio</span>');
                    }
                    if (window.jQuery && $.fn && $.fn.summernote && $('#lessonContent').next('.note-editor').length) {
                        $('#lessonContent').summernote('code', lesson.content || '');
                    } else {
                        $('#lessonContent').val(lesson.content || '');
                    }
                    updateLessonPreviewConfig();
                    if (!lesson.duration || Number(lesson.duration) <= 0) {
                        agendarDeteccaoDuracaoUrl(false);
                    }

                    // Attachments
                    const $list = $('#attachmentList').empty();
                    if (lesson.attachments && lesson.attachments.length) {
                        lesson.attachments.forEach(att => {
                            $list.append(`
                                        <li class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-100 dark:border-slate-800 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <i class="fas fa-paperclip text-blue-500"></i>
                                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">${att.file_name}</span>
                                            </div>
                                            <button type="button" onclick="deleteAttachment(${id}, ${att.id})" class="w-8 h-8 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </li>
                                    `);
                        });
                    }

                    openModal('lesson-modal');
                });
            }

            let promessaApiYoutube = null;
            let temporizadorDeteccaoDuracaoUrl = null;

            function extrairIdYoutube(url) {
                const valor = String(url || '').trim();
                if (!valor) return null;

                try {
                    const normalizado = /^(https?:)?\/\//i.test(valor) ? valor : ('https://' + valor.replace(/^\/+/, ''));
                    const parsed = new URL(normalizado);
                    const host = String(parsed.hostname || '').toLowerCase();
                    const pathParts = String(parsed.pathname || '').split('/').filter(Boolean);

                    if (host === 'youtu.be' && pathParts[0]) return pathParts[0];

                    if (host.endsWith('youtube.com') || host.endsWith('youtube-nocookie.com')) {
                        if (pathParts.length >= 2 && ['embed', 'shorts', 'live'].includes(pathParts[0])) {
                            return pathParts[1];
                        }
                        const v = parsed.searchParams.get('v');
                        if (v) return v;
                        const vi = parsed.searchParams.get('vi');
                        if (vi) return vi;
                    }
                } catch (e) { }

                const match = valor.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?.*v=|embed\/|shorts\/|live\/)|youtube-nocookie\.com\/embed\/)([^?&#/]+)/i);
                return match && match[1] ? match[1] : null;
            }

            function carregarApiYoutube() {
                if (window.YT && typeof window.YT.Player === 'function') {
                    return Promise.resolve(window.YT);
                }

                if (promessaApiYoutube) {
                    return promessaApiYoutube;
                }

                promessaApiYoutube = new Promise((resolve, reject) => {
                    const anterior = window.onYouTubeIframeAPIReady;
                    window.onYouTubeIframeAPIReady = function () {
                        if (typeof anterior === 'function') {
                            try { anterior(); } catch (e) { }
                        }
                        resolve(window.YT);
                    };

                    if (!document.querySelector('script[data-youtube-iframe-api="1"]')) {
                        const script = document.createElement('script');
                        script.src = 'https://www.youtube.com/iframe_api';
                        script.async = true;
                        script.setAttribute('data-youtube-iframe-api', '1');
                        script.onerror = () => reject(new Error('Falha ao carregar API do YouTube.'));
                        document.head.appendChild(script);
                    }

                    setTimeout(() => {
                        if (!(window.YT && typeof window.YT.Player === 'function')) {
                            reject(new Error('Tempo esgotado ao carregar API do YouTube.'));
                        }
                    }, 12000);
                });

                return promessaApiYoutube;
            }

            function detectarDuracaoYoutube(videoId) {
                return carregarApiYoutube().then(() => new Promise((resolve, reject) => {
                    const probeId = 'yt-duration-probe-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
                    const probe = document.createElement('div');
                    probe.id = probeId;
                    probe.style.cssText = 'position:fixed;left:-99999px;top:-99999px;width:1px;height:1px;opacity:0;pointer-events:none;';
                    document.body.appendChild(probe);

                    let player = null;
                    let finalizado = false;

                    const finalizar = (duracao = 0, erro = null) => {
                        if (finalizado) return;
                        finalizado = true;

                        if (player && typeof player.destroy === 'function') {
                            try { player.destroy(); } catch (e) { }
                        }
                        if (probe && probe.parentNode) {
                            probe.parentNode.removeChild(probe);
                        }

                        if (erro) {
                            reject(erro);
                            return;
                        }

                        if (duracao > 0) {
                            resolve(duracao);
                        } else {
                            reject(new Error('Não foi possível detectar a duração do vídeo.'));
                        }
                    };

                    const timeout = setTimeout(() => finalizar(0, new Error('Tempo esgotado ao detectar duracao do YouTube.')), 18000);

                    player = new window.YT.Player(probeId, {
                        videoId: videoId,
                        width: '1',
                        height: '1',
                        playerVars: { autoplay: 0, controls: 0, rel: 0, modestbranding: 1, playsinline: 1 },
                        events: {
                            onReady: function (event) {
                                const inicio = Date.now();
                                const tentar = function () {
                                    const duracao = Math.floor(Number(event.target.getDuration()) || 0);
                                    if (duracao > 0) {
                                        clearTimeout(timeout);
                                        finalizar(duracao);
                                        return;
                                    }

                                    if ((Date.now() - inicio) >= 15000) {
                                        clearTimeout(timeout);
                                        finalizar(0, new Error('Nao foi possivel detectar a duracao do YouTube.'));
                                        return;
                                    }

                                    setTimeout(tentar, 400);
                                };

                                tentar();
                            },
                            onError: function () {
                                clearTimeout(timeout);
                                finalizar(0, new Error('Video do YouTube indisponivel para previa.'));
                            }
                        }
                    });
                }));
            }

            function detectarDuracaoVideoRemoto(url) {
                return new Promise((resolve, reject) => {
                    const probe = document.createElement('video');
                    probe.preload = 'metadata';
                    probe.muted = true;
                    probe.style.display = 'none';

                    const finalizar = (duracao = 0, erro = null) => {
                        try {
                            probe.pause();
                            probe.removeAttribute('src');
                            probe.load();
                        } catch (e) { }

                        if (probe.parentNode) {
                            probe.parentNode.removeChild(probe);
                        }

                        if (erro) {
                            reject(erro);
                            return;
                        }

                        if (duracao > 0) {
                            resolve(duracao);
                        } else {
                            reject(new Error('Não foi possível detectar a duração.'));
                        }
                    };

                    probe.onloadedmetadata = function () {
                        const duracao = Math.floor(Number(probe.duration) || 0);
                        finalizar(duracao);
                    };

                    probe.onerror = function () {
                        finalizar(0, new Error('Falha ao carregar metadados do vídeo.'));
                    };

                    document.body.appendChild(probe);
                    probe.src = url;
                });
            }

            function detectarDuracaoPorUrlAula(mostrarErro = false) {
                const rawUrl = ($('#lessonVideo').val() || '').trim();
                if (!rawUrl) return;

                const normalizada = /^(https?:)?\/\//i.test(rawUrl) ? rawUrl : ('https://' + rawUrl.replace(/^\/+/, ''));
                const idYoutube = extrairIdYoutube(normalizada);

                const promessa = idYoutube
                    ? detectarDuracaoYoutube(idYoutube)
                    : detectarDuracaoVideoRemoto(normalizada);

                promessa.then((duracao) => {
                    if (!Number.isFinite(duracao) || duracao <= 0) return;
                    $('#lessonDuration').val(duracao);
                    toastr.info('Duração detectada automaticamente: ' + duracao + 's');
                }).catch(() => {
                    if (mostrarErro) {
                        toastr.warning('Não foi possível detectar a duração automática para este link.');
                    }
                });
            }

            function agendarDeteccaoDuracaoUrl(mostrarErro = false) {
                if (temporizadorDeteccaoDuracaoUrl) {
                    clearTimeout(temporizadorDeteccaoDuracaoUrl);
                }
                temporizadorDeteccaoDuracaoUrl = setTimeout(() => {
                    detectarDuracaoPorUrlAula(mostrarErro);
                }, 650);
            }

            $(document).on('change blur', '#lessonVideo', function () {
                agendarDeteccaoDuracaoUrl(false);
            });

            $(document).on('change', '#lessonVideoFile', function (e) {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                const limiteMb = {{ (int) config('uploads.video_max_mb', 1024) }};
                const limiteBytes = limiteMb * 1024 * 1024;
                if (file.size > limiteBytes) {
                    toastr.error('O video excede o limite permitido de ' + limiteMb + 'MB.');
                    e.target.value = '';
                    $('#lessonVideoFileLabel').html('<i class="fas fa-cloud-upload-alt text-lg"></i><span>Selecionar video para envio</span>');
                    return;
                }

                $('#lessonVideoFileLabel').html('<i class="fas fa-file-video text-lg"></i><span>' + file.name + '</span>');

                const video = document.createElement('video');
                video.preload = 'metadata';
                video.onloadedmetadata = function () {
                    window.URL.revokeObjectURL(video.src);
                    const duracao = Math.floor(Number(video.duration) || 0);
                    if (duracao > 0) {
                        $('#lessonDuration').val(duracao);
                        toastr.info('Duração detectada: ' + duracao + 's');
                    }
                };
                video.src = URL.createObjectURL(file);
            });

            $(document).on('change', '#lessonPreview, #lessonPreviewMode', updateLessonPreviewConfig);
            updateLessonPreviewConfig();

            $('#btnSaveLesson').on('click', function () {
                if (!lessonStoreUrl || !lessonBaseUrl) return;
                const id = $('#lessonId').val();
                const url = id ? `${lessonBaseUrl}/${id}` : lessonStoreUrl;
                const formData = new FormData(document.getElementById('lessonForm'));
                if (window.jQuery && $.fn && $.fn.summernote && $('#lessonContent').next('.note-editor').length) {
                    formData.set('content', $('#lessonContent').summernote('code') || '');
                }
                if (id) formData.append('_method', 'PUT');

                const $btn = $(this);
                const textoOriginal = $btn.html();
                $btn.prop('disabled', true).html('Salvando...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 900000,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        if (response && response.success === false) {
                            toastr.error(response.message || 'Falha ao salvar a aula.');
                            return;
                        }
                        toastr.success((response && response.message) ? response.message : 'Aula salva!');
                        location.reload();
                    },
                    error: function (xhr) {
                        if (xhr && xhr.statusText === 'timeout') {
                            toastr.error('Tempo limite excedido no upload. Tente um arquivo menor.');
                            return;
                        }

                        if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                            let msg = '';
                            $.each(xhr.responseJSON.errors, function (k, v) { msg += v[0] + '<br>'; });
                            toastr.error(msg || 'Erro ao salvar aula.');
                            return;
                        }

                        if (xhr && xhr.status === 413) {
                            toastr.error('Arquivo muito grande para o servidor.');
                            return;
                        }

                        toastr.error('Erro ao salvar aula.');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html(textoOriginal);
                    }
                });
            });

            $(document).on('change', '#lessonAttachmentsInput', function (event) {
                if (!lessonBaseUrl) return;

                const lessonId = Number($('#lessonId').val() || 0);
                const arquivos = Array.from((event.target.files || []));

                if (!lessonId) {
                    toastr.warning('Salve a aula primeiro para enviar materiais de apoio.');
                    event.target.value = '';
                    return;
                }

                if (!arquivos.length) {
                    return;
                }

                let indice = 0;
                const enviarProximo = function () {
                    if (indice >= arquivos.length) {
                        event.target.value = '';
                        editLesson(lessonId);
                        return;
                    }

                    const arquivo = arquivos[indice];
                    indice += 1;

                    const nomeArquivo = String((arquivo && arquivo.name) || 'arquivo')
                        .replace(/[\r\n\t]+/g, ' ')
                        .trim()
                        .substring(0, 180);

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('file', arquivo);
                    formData.append('name', nomeArquivo || 'arquivo');

                    $.ajax({
                        url: `${lessonBaseUrl}/${lessonId}/attachments`,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function () {
                            toastr.success('Material enviado com sucesso.');
                            enviarProximo();
                        },
                        error: function (xhr) {
                            toastr.error(xhr?.responseJSON?.message || 'Falha ao enviar material de apoio.');
                            enviarProximo();
                        }
                    });
                };

                enviarProximo();
            });

            function deleteAttachment(lessonId, attId) {
                if (!lessonBaseUrl) return;
                Swal.fire({
                    title: 'Excluir anexo?',
                    text: "Esta ação não pode ser desfeita.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-[1.5rem] dark:bg-slate-900 border-none shadow-2xl',
                        title: 'text-2xl font-black text-slate-800 dark:text-white',
                        htmlContainer: 'text-sm font-medium text-slate-500 dark:text-slate-400'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `${lessonBaseUrl}/${lessonId}/attachments/${attId}`,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function () {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Anexo removido!',
                                    showConfirmButton: false,
                                    timer: 1500,
                                    customClass: {
                                        popup: 'rounded-[1.5rem] dark:bg-slate-900 border-none shadow-2xl',
                                        title: 'text-xl font-black text-slate-800 dark:text-white',
                                    }
                                });
                                editLesson(lessonId); // Refresh list
                            }
                        });
                    }
                });
            }

            function enviarImagemConteudoAulaPainel(file, $editor) {
                if (!lessonContentImageUploadUrl) {
                    toastr.warning('Salve o curso primeiro para enviar imagens no conteúdo.');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('image', file);

                $.ajax({
                    url: lessonContentImageUploadUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        if (response && response.url) {
                            $editor.summernote('insertImage', response.url);
                            return;
                        }

                        toastr.error('Falha ao inserir a imagem no conteúdo.');
                    },
                    error: function (xhr) {
                        toastr.error(xhr?.responseJSON?.message || 'Falha ao enviar imagem do conteúdo.');
                    }
                });
            }
            // Summernote Initialization
            const summernoteConfig = {
                height: 300,
                placeholder: 'Digite o conteúdo aqui...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']],
                ],
                callbacks: {
                    onImageUpload: function (files) {
                        if (!files || !files.length) return;
                        const $editor = $(this);
                        Array.from(files).forEach((file) => enviarImagemConteudoAulaPainel(file, $editor));
                    }
                }
            };

            const initSummernoteEditors = () => {
                if (!(window.jQuery && $.fn && $.fn.summernote)) {
                    return false;
                }

                ['#fullDescription', '#lessonContent'].forEach((selector) => {
                    const $field = $(selector);
                    if (!$field.length || $field.next('.note-editor').length) {
                        return;
                    }

                    $field.summernote(summernoteConfig);
                });

                return true;
            };

            if (!initSummernoteEditors()) {
                let attempts = 0;
                const maxAttempts = 30;
                const timer = setInterval(() => {
                    attempts += 1;
                    if (initSummernoteEditors() || attempts >= maxAttempts) {
                        clearInterval(timer);
                    }
                }, 200);
            }

            // Certificate Editor Logic
            $(document).ready(function () {
                if (!isExistingCourse || !certificatePreviewUrl) return;

                let rawCertSettings = {!! $course->certificate_settings ? json_encode($course->certificate_settings) : '{}' !!};
                let certDoc = (rawCertSettings && rawCertSettings.schemaVersion === 2) ? rawCertSettings : { schemaVersion: 2, meta: {}, elements: {} };
                let certSettings = certDoc.elements;

                const platformLogoUrl = "{{ \App\Models\Setting::get('logo_auth') ? asset(ltrim(\App\Models\Setting::get('logo_auth'), '/')) : asset('img/logo.svg') }}";
                const instructorSignatureUrl = "{{ $course->instructor_signature ? asset($course->instructor_signature) : '' }}";

                const defaultTags = {
                    'student_name': { x: 50, y: 40, text: '[Nome do Aluno]', fontSize: 30, color: '#000000', fontWeight: 'bold' },
                    'course_name': { x: 50, y: 55, text: '[Nome do Curso]', fontSize: 24, color: '#333333', fontWeight: 'bold' },
                    'completion_date': { x: 50, y: 65, text: 'Concluído em: 01/01/2024', fontSize: 16, color: '#555555', fontWeight: 'normal' },
                    'platform_logo': { x: 50, y: 10, text: 'LOGO', width: 120, height: 60, mandatory: true }
                };

                $.each(defaultTags, function (key, val) {
                    if (!certSettings[key]) certSettings[key] = val;
                });

                const $canvasLayer = $('#cert-elements-layer');

                function renderCertElements() {
                    $canvasLayer.empty();
                    $.each(certSettings, function (key, data) {
                        if (!data || data.x === undefined) return;

                        let $el = $('<div>')
                            .addClass('absolute cursor-move select-none p-2 border border-transparent hover:border-blue-400')
                            .attr('id', 'cert-el-' + key)
                            .css({
                                left: data.x + '%',
                                top: data.y + '%',
                                fontSize: (data.fontSize || 16) + 'px',
                                color: data.color || '#000000',
                                fontWeight: data.fontWeight || 'normal',
                                zIndex: 10
                            });

                        if (key === 'platform_logo') {
                            $el.css({
                                width: (data.width || 120) + 'px',
                                height: (data.height || 60) + 'px',
                                backgroundImage: `url("${platformLogoUrl}")`,
                                backgroundSize: 'contain',
                                backgroundRepeat: 'no-repeat',
                                backgroundPosition: 'center'
                            }).text('');
                        } else {
                            $el.text(data.text);
                        }

                        $el.draggable({
                            containment: '#cert-canvas',
                            stop: function (event, ui) {
                                let parentW = $('#cert-canvas').width();
                                let parentH = $('#cert-canvas').height();
                                certSettings[key].x = (ui.position.left / parentW) * 100;
                                certSettings[key].y = (ui.position.top / parentH) * 100;
                            }
                        });

                        $canvasLayer.append($el);
                    });
                }

                // Global confirm action
                window.confirmAction = function (event, title, text) {
                    event.preventDefault();
                    const form = event.target.closest('form') || event.target;

                    Swal.fire({
                        title: title,
                        text: text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3b82f6',
                        cancelButtonColor: '#ef4444',
                        confirmButtonText: 'Sim, confirmar!',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-[2rem] dark:bg-slate-900 border-none shadow-2xl',
                            title: 'text-2xl font-black text-slate-800 dark:text-white',
                            htmlContainer: 'text-sm font-medium text-slate-500 dark:text-slate-400',
                            confirmButton: 'rounded-xl px-6 py-3 font-bold',
                            cancelButton: 'rounded-xl px-6 py-3 font-bold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                    return false;
                };

                window.confirmDeleteLesson = function (url) {
                    Swal.fire({
                        title: 'Excluir aula?',
                        text: 'Deseja realmente remover esta aula?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3b82f6',
                        cancelButtonColor: '#ef4444',
                        confirmButtonText: 'Sim, confirmar!',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-[2rem] dark:bg-slate-900 border-none shadow-2xl',
                            title: 'text-2xl font-black text-slate-800 dark:text-white',
                            htmlContainer: 'text-sm font-medium text-slate-500 dark:text-slate-400',
                            confirmButton: 'rounded-xl px-6 py-3 font-bold',
                            cancelButton: 'rounded-xl px-6 py-3 font-bold'
                        }
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            return;
                        }

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function () {
                                toastr.success('Aula removida com sucesso.');
                                window.location.reload();
                            },
                            error: function () {
                                toastr.error('Nao foi possivel remover a aula.');
                            }
                        });
                    });

                    return false;
                };

                window.previewCertificate = function () {
                    $('#certificate_settings_input').val(JSON.stringify(certDoc));
                    const form = $('#courseForm')[0];
                    const originalAction = form.action;
                    const originalTarget = form.target;

                    form.action = certificatePreviewUrl;
                    form.target = "_blank";
                    form.submit();

                    // Restore
                    form.action = originalAction;
                    form.target = originalTarget;
                };
            });
        </script>
    @endpush

    {{-- Modal HTML --}}
    <div id="lesson-modal"
        class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/10 backdrop-blur-xl p-4">
        <div
            class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[3rem] shadow-[0_40px_100px_-20px_rgba(0,0,0,0.3)] overflow-hidden flex flex-col max-h-[90vh] transition-all border border-slate-100 dark:border-slate-800">
            <div
                class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between transition-colors bg-slate-50/50 dark:bg-slate-950/50">
                <div class="flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <i class="fas fa-video"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white transition-colors">Gerenciar Aula</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Editor de Conteúdo
                        </p>
                    </div>
                </div>
                <button @click="closeModal('lesson-modal')"
                    class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-400 hover:text-red-500 hover:border-red-100 transition-all shadow-sm flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div
                class="p-8 overflow-y-auto flex-1 h-full space-y-8 scrollbar-thin scrollbar-thumb-blue-500 scrollbar-track-transparent">
                <form id="lessonForm" class="space-y-6">
                    @csrf
                    <input type="hidden" id="lessonId" name="lesson_id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Título
                                da Aula</label>
                            <input type="text" id="lessonTitle" name="title" required
                                class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold placeholder:text-slate-300"
                                placeholder="Ex: Introdução ao Mercado">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Ordem</label>
                            <input type="number" id="lessonOrder" name="order" required
                                class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Duração
                                (seg)</label>
                            <input type="number" id="lessonDuration" name="duration" required
                                class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold"
                                placeholder="Ex: 360">
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">URL
                                do Vídeo (Vimeo/YouTube/Panda)</label>
                            <div class="relative group">
                                <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400">
                                    <i class="fas fa-link"></i>
                                </div>
                                <input type="text" id="lessonVideo" name="video_url"
                                    class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-slate-300"
                                    placeholder="https://...">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Arquivo
                                de VÃ­deo (Upload)</label>
                            <div class="relative group">
                                <input type="file" id="lessonVideoFile" name="video_file" accept="video/*,video/mp4,video/webm"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div id="lessonVideoFileLabel"
                                    class="w-full px-5 py-4 bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-bold text-slate-500 dark:text-slate-400 flex items-center justify-center gap-2 group-hover:border-blue-400 group-hover:bg-blue-50/10 transition-all">
                                    <i class="fas fa-cloud-upload-alt text-lg"></i>
                                    <span>Selecionar video para envio</span>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Se enviar arquivo, ele terá prioridade sobre a URL.</p>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="flex items-center gap-3 cursor-pointer group p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 hover:border-blue-200 transition-all">
                                <input type="checkbox" id="lessonPreview" name="is_free_preview" value="1"
                                    class="w-5 h-5 text-blue-600 border-slate-300 rounded-lg focus:ring-blue-500 bg-white transition-all">
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Marcar como aula gratuita
                                    de preview</span>
                            </label>
                        </div>

                        <div id="lessonPreviewConfig" class="md:col-span-2 hidden">
                            <div
                                class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Tipo
                                            de Gratuidade</label>
                                        <select id="lessonPreviewMode" name="free_preview_mode"
                                            class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-200 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                                            <option value="full">Aula inteira gratuita</option>
                                            <option value="time">Tempo limitado (prévia parcial)</option>
                                        </select>
                                    </div>
                                    <div id="lessonPreviewSecondsGroup" class="hidden">
                                        <label
                                            class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Tempo
                                            Gratuito (segundos)</label>
                                        <input type="number" id="lessonPreviewSeconds" name="free_preview_seconds" min="1"
                                            step="1"
                                            class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-200 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-slate-400"
                                            placeholder="Ex: 180">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Defina quantos segundos
                                            ficarão liberados para não assinantes.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Descrição
                                da Aula</label>
                            <textarea id="lessonContent" name="content" rows="4"
                                class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-700 dark:text-white focus:ring-4 focus:ring-blue-500/10 outline-none transition-all resize-none font-medium"></textarea>
                        </div>

                        <div class="md:col-span-2 space-y-4">
                            <label
                                class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Materiais
                                Complementares</label>
                            <ul id="attachmentList" class="space-y-2"></ul>

                            <div class="relative group">
                                <input type="file" id="lessonAttachmentsInput" name="attachments[]" multiple
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div
                                    class="w-full px-5 py-4 bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-bold text-slate-400 flex items-center justify-center gap-2 group-hover:border-blue-400 group-hover:bg-blue-50/10 transition-all">
                                    <i class="fas fa-cloud-upload-alt text-xl"></i>
                                    <span>Adicionar Arquivos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div
                class="p-8 border-t border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30 flex items-center justify-end gap-3 transition-colors">
                <button @click="closeModal('lesson-modal')"
                    class="px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-black text-slate-600 dark:text-slate-400 hover:bg-slate-50 transition-all">
                    Cancelar
                </button>
                <button type="button" id="btnSaveLesson"
                    class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl shadow-blue-500/20 transition transform hover:scale-[1.05] active:scale-[0.95]">
                    Salvar Aula
                </button>
            </div>
        </div>
    </div>

@endsection
