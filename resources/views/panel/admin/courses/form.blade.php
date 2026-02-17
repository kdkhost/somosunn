@extends('panel.layouts.app')

@section('title', $course->exists ? 'Editar Curso' : 'Novo Curso')

@section('content')
<div x-data="{ tab: 'general' }" class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('panel.admin.courses.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-700 transition-all shadow-sm">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    {{ $course->exists ? 'Editar Curso' : 'Novo Curso' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">
                    {{ $course->exists ? 'Atualize os detalhes e o conteúdo do curso.' : 'Preencha as informações para criar um novo curso.' }}
                </p>
            </div>
        </div>

        @if($course->exists)
        <div class="flex items-center gap-2">
            <a href="{{ route('courses.show', $course->slug ?: $course->id) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all shadow-sm">
                <i class="fas fa-external-link-alt text-slate-400 dark:text-slate-500"></i>
                <span>Ver na Loja</span>
            </a>
        </div>
        @endif
    </div>

    {{-- Tabs Navigation --}}
    <div class="bg-white dark:bg-slate-950 p-1 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 inline-flex items-center gap-1 transition-colors duration-300">
        <button @click="tab = 'general'" 
                :class="tab === 'general' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-300'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
            <i class="fas fa-info-circle"></i>
            <span>Geral</span>
        </button>
        <button @click="tab = 'pricing'" 
                :class="tab === 'pricing' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-300'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
            <i class="fas fa-tag"></i>
            <span>Preço</span>
        </button>
        @if($course->exists)
        <button @click="tab = 'lessons'" 
                :class="tab === 'lessons' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-300'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
            <i class="fas fa-layer-group"></i>
            <span>Conteúdo ({{ $course->lessons_count ?? $course->lessons()->count() }})</span>
        </button>
        <button @click="tab = 'certificate'" 
                :class="tab === 'certificate' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-300'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
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

        {{-- Tab: General --}}
        <div x-show="tab === 'general'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2 transition-colors">
                        <i class="fas fa-file-alt text-blue-500"></i>
                        Informações Principais
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Título do Curso</label>
                            <input type="text" name="title" value="{{ old('title', $course->title) }}" required
                                   class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-lg font-bold"
                                   placeholder="Ex: Domínio do Backend com Laravel">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Descrição Curta</label>
                            <textarea name="short_description" rows="3" maxlength="500"
                                      class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all resize-none"
                                      placeholder="Um breve resumo que aparece nos cards de curso...">{{ old('short_description', $course->short_description) }}</textarea>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 transition-colors">Máximo 500 caracteres.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Descrição Completa</label>
                            <textarea name="full_description" id="fullDescription" rows="10"
                                      class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">{{ old('full_description', $course->full_description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2 transition-colors">
                        <i class="fas fa-video text-blue-500"></i>
                        Vodeon & Player
                    </h3>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 transition-colors">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="video_block_download" id="video_block_download" value="1"
                                       {{ $course->video_block_download ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 rounded focus:ring-blue-500 bg-white dark:bg-slate-900">
                            </div>
                            <div>
                                <label for="video_block_download" class="block text-sm font-bold text-slate-900 dark:text-white transition-colors">Bloquear download do vídeo</label>
                                <p class="text-xs text-slate-500 dark:text-slate-400 transition-colors">Remove botões/menus de download nativos do player.</p>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-4 transition-colors">
                            <div class="flex items-start gap-4">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" x-model="floating" id="video_floating_enabled" value="1"
                                           name="video_floating_enabled" {{ $course->video_floating_enabled ? 'checked' : '' }}
                                           class="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 rounded focus:ring-blue-500 bg-white dark:bg-slate-900">
                                </div>
                                <div>
                                    <label for="video_floating_enabled" class="block text-sm font-bold text-slate-900 dark:text-white transition-colors">Mini player flutuante</label>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 transition-colors">Ao rolar a página, o vídeo fica fixo no canto da tela.</p>
                                </div>
                            </div>

                            <div x-show="floating" x-transition class="grid grid-cols-2 gap-4 ml-8">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase transition-colors">Largura (px)</label>
                                    <input type="number" name="video_floating_width" value="{{ $course->video_floating_width ?? 420 }}"
                                           class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase transition-colors">Altura (px)</label>
                                    <input type="number" name="video_floating_height" value="{{ $course->video_floating_height ?? 236 }}"
                                           class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                {{-- Publish Action --}}
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                    <button type="submit" class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center justify-center gap-2 mb-4">
                        <i class="fas fa-save"></i>
                        <span>{{ $course->exists ? 'Salvar Alterações' : 'Criar Curso' }}</span>
                    </button>

                    <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800 transition-colors">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Status</label>
                            <select name="status" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium">
                                <option value="draft" {{ $course->status == 'draft' ? 'selected' : '' }}>Rascunho</option>
                                <option value="published" {{ $course->status == 'published' ? 'selected' : '' }}>Publicado</option>
                                <option value="archived" {{ $course->status == 'archived' ? 'selected' : '' }}>Arquivado</option>
                                <option value="paused" {{ $course->status == 'paused' ? 'selected' : '' }}>Vendas Pausadas</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ $course->is_featured ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 rounded focus:ring-blue-500 bg-white dark:bg-slate-900">
                            <label for="is_featured" class="text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors">Destaque na Home</label>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_certificate_enabled" id="is_certificate_enabled" value="1" {{ $course->is_certificate_enabled ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 rounded focus:ring-blue-500 bg-white dark:bg-slate-900">
                            <label for="is_certificate_enabled" class="text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors">Habilitar Certificado</label>
                        </div>
                    </div>
                </div>

                {{-- Image Thumbnail --}}
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                    <h5 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2 transition-colors">
                        <i class="fas fa-image text-blue-500"></i>
                        Imagem de Capa
                    </h5>

                    <div x-data="{ photoName: null, photoPreview: '{{ $course->thumbnail ? asset($course->thumbnail) : '' }}' }" class="space-y-4">
                        <div class="relative group">
                            <div class="aspect-video w-full rounded-xl bg-slate-100 dark:bg-slate-950 border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden transition-all group-hover:border-blue-300 dark:group-hover:border-blue-500">
                                <template x-if="!photoPreview">
                                    <div class="flex flex-col items-center text-slate-400 dark:text-slate-500 transition-colors">
                                        <i class="fas fa-cloud-upload-alt text-3xl mb-2"></i>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-center px-4">Dimensionamento sugerido: 1280x720 (16:9)</span>
                                    </div>
                                </template>
                                <template x-if="photoPreview">
                                    <img :src="photoPreview" class="w-full h-full object-cover">
                                </template>
                                <div @click="$refs.thumbnail.click()" class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                                    <span class="text-white text-xs font-bold bg-white/20 backdrop-blur-md px-4 py-2 rounded-lg border border-white/30">Alterar Capa</span>
                                </div>
                            </div>
                            <input type="file" name="thumbnail" x-ref="thumbnail" class="hidden" 
                                   accept="image/*"
                                   @change="
                                    photoName = $event.target.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => { photoPreview = e.target.result; };
                                    reader.readAsDataURL($event.target.files[0]);
                                   ">
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2 transition-colors">Instrutor / Autor</label>
                    <input type="text" name="author_name" value="{{ old('author_name', $course->author_name ?? Auth::user()->name) }}"
                           class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium">
                </div>
            </div>
        </div>

        {{-- Tab: Pricing --}}
        <div x-show="tab === 'pricing'" class="max-w-3xl space-y-6">
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2 transition-colors">
                    <i class="fas fa-coins text-amber-500"></i>
                    Configuração de Preços
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1 text-center transition-colors">Preço Padrão</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 font-bold group-focus-within:text-blue-500 transition-colors">R$</span>
                            <input type="text" name="price" value="{{ old('price', $course->price) }}"
                                   class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-xl font-bold mask-money"
                                   placeholder="0,00">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 relative overflow-hidden group transition-colors duration-300">
                <div class="absolute top-0 right-0 p-4">
                    <i class="fas fa-bolt text-4xl text-amber-100 dark:text-amber-900/20 group-hover:text-amber-200 dark:group-hover:text-amber-900/40 transition-colors"></i>
                </div>

                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2 transition-colors">
                    <i class="fas fa-bolt text-amber-500"></i>
                    Promoção Relâmpago
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Preço Promocional</label>
                        <div class="relative peer group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 font-bold group-focus-within:text-amber-500 transition-colors">R$</span>
                            <input type="text" name="flash_sale_price" value="{{ old('flash_sale_price', $course->flash_sale_price) }}"
                                   class="w-full pl-12 pr-4 py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all text-xl font-bold mask-money"
                                   placeholder="0,00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Data de Expiração</label>
                        <input type="datetime-local" name="flash_sale_ends_at" 
                               value="{{ old('flash_sale_ends_at', $course->flash_sale_ends_at ? $course->flash_sale_ends_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full px-4 py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all text-sm font-medium">
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 italic transition-colors">A promoção será encerrada automaticamente nesta data e o preço voltará ao valor padrão.</p>
                    </div>
                </div>
            </div>
        </div>
        
        @if($course->exists)
        {{-- Tab: Content/Lessons --}}
        <div x-show="tab === 'lessons'" class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white transition-colors">Grade Curricular</h3>
                <button type="button" @click="openModal('lesson-modal')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 border border-transparent rounded-xl text-sm font-semibold text-white hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-500/30">
                    <i class="fas fa-plus"></i>
                    <span>Nova Aula</span>
                </button>
            </div>

            <div id="lessons-list" class="space-y-3">
                @forelse($course->lessons as $lesson)
                    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 flex items-center gap-4 group hover:border-blue-300 dark:hover:border-blue-500 transition-all" data-id="{{ $lesson->id }}">
                        <div class="cursor-move text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-950 flex items-center justify-center text-xs font-bold text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-800 transition-colors">
                            #{{ $lesson->order }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-slate-900 dark:text-white truncate transition-colors">{{ $lesson->title }}</h4>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1 transition-colors">
                                    <i class="fas fa-clock"></i>
                                    {{ gmdate("H:i:s", $lesson->duration) }}
                                </span>
                                @if($lesson->is_free_preview)
                                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-500 dark:text-blue-400 flex items-center gap-1 transition-colors">
                                    <i class="fas fa-eye"></i>
                                    Preview Grátis
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" @click="editLesson({{ $lesson->id }})"
                                    class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition-colors border border-transparent hover:border-blue-100 dark:hover:border-blue-900 text-slate-400 dark:text-slate-500"
                                    title="Editar Aula">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('courses.lessons.destroy', [$course, $lesson]) }}" method="POST"
                                  onsubmit="return confirm('Tem certeza que deseja excluir esta aula?');"
                                  class="inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-700 dark:hover:text-red-400 rounded-lg transition-colors border border-transparent hover:border-red-100 dark:hover:border-red-900 text-slate-400 dark:text-slate-500"
                                        title="Excluir Aula">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-slate-50 dark:bg-slate-950 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 transition-colors">
                        <i class="fas fa-layer-group text-4xl text-slate-200 dark:text-slate-800 mb-3"></i>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">Nenhuma aula cadastrada. Comece adicionando o conteúdo!</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Tab: Certificate --}}
        <div x-show="tab === 'certificate'" class="space-y-6">
            {{-- Certificate Editor Placeholder - We'll implement the Canvas Logic here --}}
            <div class="alert alert-info bg-blue-50 dark:bg-blue-900/20 border-blue-100 dark:border-blue-800/30 text-blue-700 dark:text-blue-400 rounded-2xl p-4 flex items-start gap-4 transition-colors">
                <i class="fas fa-info-circle text-xl mt-1"></i>
                <div>
                    <h5 class="font-bold mb-1">Editor de Certificado</h5>
                    <p class="text-sm opacity-90 text-justify">O design do certificado é editado visualmente. Você pode arrastar os elementos, alterar fontes e fazer upload de fundos personalizados.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {{-- Canvas Area --}}
                <div class="lg:col-span-3">
                    <div class="bg-slate-900 rounded-2xl p-8 border border-slate-800 dark:border-slate-700 shadow-2xl relative overflow-hidden flex items-center justify-center transition-colors" style="min-height: 500px;">
                        {{-- Editor Canvas --}}
                        <div id="cert-canvas" 
                             class="bg-white relative shadow-2xl origin-center transition-transform"
                             style="width: 842px; height: 595px;">
                             {{-- BG Image --}}
                             <img id="cert-bg-img" src="{{ $course->certificate_bg ? asset($course->certificate_bg) : '' }}" 
                                  class="absolute inset-0 w-full h-full object-cover {{ $course->certificate_bg ? '' : 'hidden' }}">
                             
                             <div id="cert-elements-layer" class="absolute inset-0 z-10">
                                {{-- Draggable elements will be injected here --}}
                             </div>
                        </div>
                    </div>
                </div>

                {{-- Certificate Sidebar --}}
                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800 pb-2 transition-colors">Plano de Fundo</h4>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-1 transition-colors">Imagem de Fundo</label>
                                <input type="file" name="certificate_bg" accept="image/*"
                                       class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50 transition-all cursor-pointer">
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-1 transition-colors">Ajuste</label>
                                <select id="cert-bg-fit" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                                    <option value="cover">Cobrir (Zoom)</option>
                                    <option value="stretch">Esticar</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800 pb-2 transition-colors">Assinatura</h4>
                        
                        <div class="space-y-4">
                            <div class="aspect-[3/1] rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden transition-colors">
                                @if($course->instructor_signature)
                                    <img src="{{ asset($course->instructor_signature) }}" class="max-h-full object-contain">
                                @else
                                    <i class="fas fa-signature text-slate-300 dark:text-slate-700 text-xl transition-colors"></i>
                                @endif
                            </div>
                            <input type="file" name="instructor_signature" accept="image/png"
                                   class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50 transition-all cursor-pointer">
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 italic transition-colors">Recomendado: PNG com fundo transparente.</p>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Sortable logic for lessons
        const el = document.getElementById('lessons-list');
        if (el) {
            Sortable.create(el, {
                animation: 150,
                handle: '.cursor-move',
                ghostClass: 'bg-blue-50',
                onEnd: function() {
                    const orders = Array.from(el.children).map((item, index) => ({
                        id: item.dataset.id,
                        order: index + 1
                    }));
                    
                    $.post('{{ route("panel.admin.courses.lessons.reorder", $course) }}', {
                        _token: '{{ csrf_token() }}',
                        lessons: orders
                    }).done(() => {
                        toastr.success('Ordem atualizada!');
                    }).fail(() => {
                        toastr.error('Erro ao reordenar.');
                    });
                }
            });
        }
    });

    // Money Mask Helper
    document.querySelectorAll('.mask-money').forEach(input => {
        input.addEventListener('input', function(e) {
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

    function editLesson(id) {
        $.get('/admin/courses/{{ $course->id }}/lessons/' + id + '/details', function(lesson) {
            $('#lessonId').val(id);
            $('#lessonTitle').val(lesson.title);
            $('#lessonOrder').val(lesson.order);
            $('#lessonDuration').val(lesson.duration);
            $('#lessonPreview').prop('checked', !!lesson.is_free_preview);
            $('#lessonVideo').val(lesson.video_url || '');
            $('#lessonContent').val(lesson.content || '');
            
            // Attachments
            const $list = $('#attachmentList').empty();
            if (lesson.attachments && lesson.attachments.length) {
                lesson.attachments.forEach(att => {
                    $list.append(`
                        <li class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="text-xs font-medium text-slate-700">${att.file_name}</span>
                            <button type="button" onclick="deleteAttachment(${id}, ${att.id})" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </li>
                    `);
                });
            }
            
            openModal('lesson-modal');
        });
    }

    $('#btnSaveLesson').on('click', function() {
        const id = $('#lessonId').val();
        const url = id ? `/admin/courses/{{ $course->id }}/lessons/${id}` : `{{ route('panel.admin.courses.lessons.store', $course) }}`;
        const formData = new FormData(document.getElementById('lessonForm'));
        if (id) formData.append('_method', 'PUT');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                toastr.success('Aula salva!');
                location.reload();
            },
            error: function(xhr) {
                toastr.error('Erro ao salvar aula.');
            }
        });
    });

    function deleteAttachment(lessonId, attId) {
        if (!confirm('Excluir anexo?')) return;
        $.ajax({
            url: `/admin/courses/{{ $course->id }}/lessons/${lessonId}/attachments/${attId}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function() {
                toastr.success('Anexo removido');
                editLesson(lessonId); // Refresh list
            }
        });
    }
    // Certificate Editor Logic
    $(document).ready(function () {
        if ('{{ $course->exists }}' == '') return;

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

        renderCertElements();

        $('#courseForm').on('submit', function() {
            $('#certificate_settings_input').val(JSON.stringify(certDoc));
        });
    });
</script>
@endpush

{{-- Modal HTML --}}
<div id="lesson-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] transition-colors">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between transition-colors">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white transition-colors">Gerenciar Aula</h3>
            <button @click="closeModal('lesson-modal')" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 h-full space-y-6">
            <form id="lessonForm" class="space-y-4">
                @csrf
                <input type="hidden" id="lessonId" name="lesson_id">
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-1 transition-colors">Título</label>
                        <input type="text" name="title" id="lessonTitle" required
                               class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none text-sm text-slate-900 dark:text-white transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-1 transition-colors">Ordem</label>
                        <input type="number" name="order" id="lessonOrder" value="1"
                               class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none text-sm text-slate-900 dark:text-white transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-1 transition-colors">Duração (s)</label>
                        <input type="number" name="duration" id="lessonDuration" value="0"
                               class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none text-sm text-slate-900 dark:text-white transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-1 transition-colors">Link do Vídeo (YT/Vimeo)</label>
                    <input type="text" name="video_url" id="lessonVideo"
                           class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none text-sm text-slate-900 dark:text-white transition-all"
                           placeholder="https://...">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-1 transition-colors">Conteúdo</label>
                    <textarea name="content" id="lessonContent" rows="4"
                              class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none text-sm text-slate-900 dark:text-white transition-all"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_free_preview" id="lessonPreview" value="1"
                           class="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 rounded focus:ring-blue-500 bg-white dark:bg-slate-950">
                    <label for="lessonPreview" class="text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors">Aula Gratuita (Preview)</label>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 transition-colors">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-3 transition-colors">Materiais de Apoio</h4>
                    <input type="file" name="file" class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50 transition-all cursor-pointer">
                    <ul id="attachmentList" class="mt-3 space-y-2"></ul>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-end gap-3 transition-colors">
            <button @click="closeModal('lesson-modal')" class="px-6 py-2 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                Cancelar
            </button>
            <button type="button" id="btnSaveLesson" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all">
                Salvar Aula
            </button>
        </div>
    </div>
</div>

@endsection
