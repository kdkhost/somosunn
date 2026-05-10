@extends('panel.layouts.app')

@section('title', $event->exists ? 'Editar Evento: ' . $event->title : 'Novo Evento')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.events.index') }}" class="hover:underline">Eventos</a>
@endsection

@section('panel_content')
    <div
        x-data="{
            tab: '{{ request('tab', 'general') }}',
            type: '{{ old('type', $event->type ?? 'event') }}',
            certificateEnabled: {{ old('is_certificate_enabled', $event->is_certificate_enabled) ? 'true' : 'false' }},
            ticketEnabled: {{ old('is_ticket_enabled', $event->is_ticket_enabled) ? 'true' : 'false' }},
            scannerMode: '{{ old('scanner_restriction_mode', $event->scannerRestrictionMode()) }}'
        }"
        x-effect="if (!certificateEnabled && tab === 'certificate') { tab = 'general'; }"
        class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    {{ $event->exists ? 'Editar Evento' : 'Novo Evento' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Configure datas, local e venda
                    de ingressos para seu evento.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if($event->exists)
                    <form method="POST" action="{{ route('panel.admin.events.toggle-published', $event) }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 text-sm font-semibold rounded-xl transition-all border {{ $event->published
                                ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300'
                                : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300' }}">
                            <i class="fas {{ $event->published ? 'fa-eye-slash' : 'fa-eye' }} mr-2"></i>
                            <span>{{ $event->published ? 'Desativar Evento' : 'Ativar Evento' }}</span>
                        </button>
                    </form>
                @endif
                <a href="{{ route('panel.admin.events.index') }}"
                    class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                    Cancelar
                </a>
                @if($event->exists)
                    <form method="POST" action="{{ route('panel.admin.events.destroy', $event) }}"
                        data-confirm-title="Excluir evento?"
                        data-confirm-text="Deseja realmente excluir este evento? Esta ação não pode ser desfeita."
                        data-confirm-icon="warning">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-rose-500/20 transition-all flex items-center gap-2">
                            <i class="fas fa-trash-alt"></i>
                            <span>Excluir Evento</span>
                        </button>
                    </form>
                @endif
                <button type="submit" form="eventForm"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Salvar Evento</span>
                </button>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div
            class="bg-slate-50 dark:bg-slate-900/50 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800 inline-flex items-center gap-1.5 mb-6">
            <button type="button" @click="tab = 'general'"
                :class="tab === 'general' 
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                            : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 shadow-sm'"
                class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                <i class="fas fa-info-circle"></i>
                <span>Geral</span>
            </button>
            <button type="button" @click="tab = 'location'" x-show="type === 'event'"
                :class="tab === 'location' 
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                            : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 shadow-sm'"
                class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                <i class="fas fa-map-marker-alt"></i>
                <span>Local & Capacidade</span>
            </button>
            <button type="button" @click="tab = 'pricing'" x-show="type === 'event'"
                :class="tab === 'pricing' 
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                            : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 shadow-sm'"
                class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                <i class="fas fa-tag"></i>
                <span>Preço & Ingressos</span>
            </button>
            @if($event->exists)
                <button type="button" @click="tab = 'gallery'"
                    :class="tab === 'gallery' 
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 shadow-sm'"
                    class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                    <i class="fas fa-images"></i>
                    <span>Galeria</span>
                </button>
                <button type="button" @click="if (certificateEnabled) { tab = 'certificate'; }" :disabled="!certificateEnabled"
                    x-show="type === 'event'"
                    :class="tab === 'certificate' 
                                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                                        : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 shadow-sm'"
                    class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:text-slate-600 dark:disabled:hover:text-slate-300">
                    <i class="fas fa-certificate"></i>
                    <span>Certificado</span>
                </button>
            @endif
        </div>

        <form id="eventForm"
            action="{{ $event->exists ? route('panel.admin.events.update', $event) : route('panel.admin.events.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if($event->exists) @method('PUT') @endif

            {{-- Tab: General --}}
            <div x-show="tab === 'general'"
                x-init="$nextTick(() => {
                    var $desc = $('#eventDescription');
                    if (window.jQuery && $.fn && $.fn.summernote && $desc.length && !$desc.next('.note-editor').length) {
                        $desc.summernote({
                            height: 300,
                            placeholder: 'Descreva o evento aqui...',
                            lang: 'pt-BR',
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'underline', 'italic', 'clear']],
                                ['color', ['color']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['table', ['table']],
                                ['insert', ['link', 'picture', 'video']],
                                ['view', ['fullscreen', 'codeview', 'help']]
                            ]
                        });
                    }
                })"
                class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-6 transition-colors duration-300">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                            <div x-show="type === 'event'">
                                <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2">Tipo de Registro</label>
                                <select name="type" x-model="type" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-slate-900 dark:text-white font-semibold">
                                    <option value="event">Evento Tradicional</option>
                                    <option value="album">Álbum Privado / Galeria</option>
                                </select>
                                <p class="text-[10px] text-slate-500 mt-1">Álbuns não aparecem no calendário público.</p>
                            </div>
                            <div :class="type === 'album' ? 'md:col-span-2' : ''">
                                <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2">URL Amigável (Slug)</label>
                                <input type="text" name="slug" value="{{ old('slug', $event->slug) }}" placeholder="ex: onde-o-network-me-levou" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-slate-900 dark:text-white font-medium">
                                <p class="text-[10px] text-slate-500 mt-1">Deixe em branco para gerar pelo título.</p>
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors" x-text="type === 'album' ? 'Título do Álbum' : 'Título do Evento'">Título
                                do Evento</label>
                            <input type="text" name="title" value="{{ old('title', $event->title) }}" required
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="type === 'event'">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Início</label>
                                <input type="datetime-local" name="start_at"
                                    value="{{ old('start_at', $event->start_at ? $event->start_at->format('Y-m-d\TH:i') : '') }}"
                                    :required="type === 'event'"
                                    :disabled="type !== 'event'"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Fim
                                    (Opcional)</label>
                                <input type="datetime-local" name="end_at"
                                    value="{{ old('end_at', $event->end_at ? $event->end_at->format('Y-m-d\TH:i') : '') }}"
                                    :disabled="type !== 'event'"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-2" x-show="type === 'event'">
                                <input type="checkbox" name="all_day" id="allDay" value="1" @checked(old('all_day', $event->all_day))
                                    class="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 rounded focus:ring-blue-500 bg-white dark:bg-slate-950">
                                <label for="allDay"
                                    class="text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors">Dia
                                    Todo</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="published" id="published" value="1" @checked(old('published', $event->exists ? $event->published : true))
                                    class="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 rounded focus:ring-blue-500 bg-white dark:bg-slate-950">
                                <label for="published"
                                    class="text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors">Publicado</label>
                            </div>
                            @if($event->exists)
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="is_certificate_enabled" id="is_certificate_enabled" value="1"
                                        x-model="certificateEnabled" @checked(old('is_certificate_enabled', $event->is_certificate_enabled))
                                        class="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 rounded focus:ring-blue-500 bg-white dark:bg-slate-950">
                                    <label for="is_certificate_enabled"
                                        class="text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors">Certificado ativo</label>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Descrição</label>
                            <textarea name="description" id="eventDescription" rows="8"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('description', $event->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    {{-- Cover Image --}}
                    <div x-show="type === 'event'"
                        class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-4 transition-colors" x-text="type === 'album' ? 'Capa do Álbum' : 'Capa do Evento'">Capa
                            do Evento</label>
                        <div class="space-y-4" id="event-cover-upload-container">
                            <input type="hidden" name="remove_image" id="remove_image_input" value="0">
                            <input type="file" name="image" id="image_upload_input" accept="image/*" class="hidden" :required="type !== 'album' && !'{{ $event->image }}'">
                            
                            <div id="drag-drop-area" class="group relative w-full flex flex-col items-center justify-center border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-3xl p-8 transition-all duration-300 ease-out cursor-pointer overflow-hidden bg-slate-50 dark:bg-slate-900/50 hover:border-blue-500 hover:bg-blue-50/50 dark:hover:border-blue-500 dark:hover:bg-blue-900/20" style="min-height: 240px;">
                                
                                <!-- Estado Inicial / Vazio -->
                                <div id="upload-prompt" class="flex flex-col items-center justify-center pointer-events-none transition-opacity duration-300 z-10 {{ $event->image ? 'opacity-0' : '' }}">
                                    <div class="w-16 h-16 mb-4 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        <span class="text-blue-600 dark:text-blue-400">Clique para fazer upload</span> ou arraste a imagem
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">PNG, JPG, WEBP até 5MB. Recomendado: 1920x1080px</p>
                                </div>

                                <!-- Preview ativo -->
                                <div id="upload-preview-container" class="absolute inset-x-2 inset-y-2 pointer-events-none transition-opacity duration-300 z-0 flex flex-col items-center justify-center rounded-2xl overflow-hidden bg-black/5 dark:bg-white/5 {{ $event->image ? 'opacity-100' : 'opacity-0' }}">
                                    <img id="upload-preview-image" class="w-full h-full object-cover transition-transform duration-700" src="{{ $event->image ? asset('storage/' . $event->image) : '' }}" alt="Preview da Capa">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/10"></div>
                                    
                                    <!-- Barra de Progresso Simulação -->
                                    <div id="upload-progress-wrapper" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3/4 max-w-sm flex flex-col items-center opacity-0 transition-opacity duration-300 z-20 pointer-events-none">
                                        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-md px-5 py-4 rounded-2xl shadow-2xl w-full flex flex-col gap-3">
                                            <div class="flex items-center justify-between text-sm font-bold text-slate-700 dark:text-slate-300">
                                                <span class="flex items-center gap-2"><i class="fas fa-spinner fa-spin text-blue-500"></i> Processando...</span>
                                                <span id="upload-progress-text">0%</span>
                                            </div>
                                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden relative">
                                                <div id="upload-progress-bar" class="bg-blue-600 h-full rounded-full transition-all duration-200 absolute left-0 top-0" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Badge de Sucesso após upload simulado -->
                                    <div id="upload-success-badge" class="absolute top-4 right-4 bg-emerald-500/90 backdrop-blur text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1.5 opacity-0 transition-opacity duration-300 z-20 translate-y-2">
                                        <i class="fas fa-check-circle"></i> Imagem pronta
                                    </div>
                                </div>
                            </div>
                            
                            <div id="upload-actions" class="{{ $event->image ? 'flex' : 'hidden' }} items-center justify-between gap-3 px-2 mt-3 w-full">
                                <span class="text-xs font-mono font-medium text-slate-500 truncate min-w-0" id="upload-filename" title="{{ $event->image ? basename($event->image) : '' }}">
                                    {{ $event->image ? basename($event->image) : '' }}
                                </span>
                                <button type="button" id="btn-remove-image" class="shrink-0 text-xs font-bold text-rose-500 hover:text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                                    <i class="fas fa-trash-alt"></i> Remover Imagem
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                        @include('panel.admin.partials.content-visibility-selector', [
                            'selected' => $event->visibility ?: ($event->is_somos_unicas ? 'somos_unicas' : 'ambos'),
                            'title' => 'Onde vender',
                            'description' => 'Defina se este evento ou palestra aparece na Somos UNN, na Somos Unicas ou nos dois ambientes.',
                        ])
                    </div>

                    {{-- Event Color --}}
                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-4 transition-colors">Cor
                            no Calendário</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="color" value="{{ old('color', $event->color ?: '#3b82f6') }}"
                                class="w-12 h-12 rounded-xl border-none p-0 cursor-pointer overflow-hidden transition-transform hover:scale-105 shadow-sm">
                            <span
                                class="text-sm font-semibold text-slate-600 dark:text-slate-400 transition-colors">Representação
                                visual</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab: Location --}}
            <div x-show="tab === 'location'" class="max-w-4xl space-y-6">
                <div
                    class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-6 transition-colors duration-300">
                    <div
                        class="rounded-2xl border border-blue-100 dark:border-blue-900/40 bg-blue-50 dark:bg-blue-950/20 p-6 space-y-5 transition-colors">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="is_ticket_enabled" id="is_ticket_enabled" value="1"
                                x-model="ticketEnabled" @checked(old('is_ticket_enabled', $event->is_ticket_enabled))
                                class="mt-1 h-4 w-4 rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 bg-white dark:bg-slate-950">
                            <div>
                                <label for="is_ticket_enabled"
                                    class="block text-sm font-black text-slate-900 dark:text-white transition-colors">Ativar ingresso digital com QR Code</label>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Quando ativo, cada reserva gera um ingresso com QR Code para leitura no check-in.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" x-show="ticketEnabled" x-cloak>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Restricao
                                    de leitura</label>
                                <select name="scanner_restriction_mode" x-model="scannerMode"
                                    class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-900/40 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-semibold">
                                    <option value="disabled">Sem restricao de localizacao</option>
                                    <option value="exact">Localizacao exata do evento</option>
                                    <option value="radius">Margem de erro configuravel</option>
                                </select>
                            </div>
                            <div x-show="scannerMode === 'radius'" x-cloak>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Margem</label>
                                <div class="grid grid-cols-[minmax(0,1fr)_112px] gap-3">
                                    <input type="number" step="0.1" min="0.1" name="scanner_radius_value"
                                        value="{{ old('scanner_radius_value', $event->scannerFormRadiusValue()) }}"
                                        class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-900/40 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-semibold"
                                        placeholder="50">
                                    <select name="scanner_radius_unit"
                                        class="w-full px-3 py-3 bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-900/40 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-semibold">
                                        <option value="m" @selected(old('scanner_radius_unit', $event->scannerFormRadiusUnit()) === 'm')>metros</option>
                                        <option value="km" @selected(old('scanner_radius_unit', $event->scannerFormRadiusUnit()) === 'km')>km</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div x-show="ticketEnabled && scannerMode === 'exact'" x-cloak
                            class="rounded-2xl border border-amber-200 dark:border-amber-900/40 bg-amber-50 dark:bg-amber-950/20 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
                            O modo exato exige leitura no ponto configurado para o evento, com tolerancia tecnica de ate 5 metros.
                        </div>

                        <div x-show="ticketEnabled && scannerMode === 'radius'" x-cloak
                            class="rounded-2xl border border-emerald-200 dark:border-emerald-900/40 bg-emerald-50 dark:bg-emerald-950/20 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                            Use a margem em metros ou quilometros para permitir a leitura dentro do raio configurado ao redor do evento.
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/70 px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                            Toda tentativa de leitura, com sucesso ou erro, fica auditada no sistema para seguranca.
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Nome
                            do Local / Estabelecimento</label>
                        <div class="relative">
                            <div class="flex gap-2">
                                <input type="text" name="location" id="panelLocationInput" value="{{ old('location', $event->location) }}"
                                    class="flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                                    placeholder="Digite o nome do local e clique em buscar..."
                                    autocomplete="off">
                                <button type="button" id="panelSearchVenueBtn"
                                    class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold text-sm transition-colors flex items-center gap-2 shrink-0">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span class="hidden sm:inline">Buscar</span>
                                </button>
                            </div>
                            <div id="panelVenueResults" class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl overflow-hidden max-h-[280px] overflow-y-auto hidden"></div>
                        </div>
                        <div class="flex items-center gap-4 mt-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-slate-500 dark:text-slate-400">
                                <input type="checkbox" id="panelOutOfState" name="event_out_of_state" value="1"
                                    {{ old('event_out_of_state', $event->event_out_of_state ?? false) ? 'checked' : '' }}
                                    class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500">
                                <span><i class="fas fa-plane-departure mr-1"></i>Evento fora do raio de 70km da minha localidade</span>
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                            <i class="fas fa-lightbulb text-amber-400 mr-1"></i>
                            <strong>Dica:</strong> Se nao encontrar o local pelo nome, digite o nome no campo acima e o endereco completo no campo abaixo. Voce tambem pode clicar diretamente no mapa para marcar a localizacao exata.
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Endereço
                            Completo</label>
                        <div class="flex gap-2">
                            <input type="text" name="address" id="panelAddressInput" value="{{ old('address', $event->address) }}"
                                class="flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                                placeholder="Ex: Av. das Americas, 4666 - Barra da Tijuca, RJ">
                            <button type="button" id="panelSearchAddressBtn"
                                class="px-4 py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-2xl font-bold text-sm transition-colors flex items-center gap-2 shrink-0">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">Digite o endereco e clique em <i class="fas fa-search"></i> para localizar no mapa.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Capacidade
                                Máxima</label>
                            <input type="number" name="capacity" value="{{ old('capacity', $event->capacity) }}"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-bold"
                                placeholder="Ex: 100 (0 para ilimitado)">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Latitude</label>
                            <input type="number" step="any" name="latitude" id="panelLatInput"
                                value="{{ old('latitude', $event->latitude) }}"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                                placeholder="-23.550520">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Longitude</label>
                            <input type="number" step="any" name="longitude" id="panelLngInput"
                                value="{{ old('longitude', $event->longitude) }}"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                                placeholder="-46.633308">
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/70 px-4 py-4 text-sm text-slate-600 dark:text-slate-400">
                        <p class="font-bold text-slate-800 dark:text-slate-200">Ponto oficial da leitura</p>
                        <p class="mt-1">Se o evento usar cerca digital, estas coordenadas definem o ponto de leitura do QR Code. Sem latitude e longitude, a restricao de localizacao nao pode ser aplicada.</p>
                    </div>
                </div>
               </div>

            {{-- Tab: Pricing --}}
            <div x-show="tab === 'pricing'" class="max-w-3xl space-y-6">
                <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-6 transition-colors duration-300">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Preço Base do Ingresso (R$)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 font-bold transition-colors">R$</span>
                            <input type="text" name="price" value="{{ old('price', number_format($event->price ?: 0, 2, ',', '.')) }}" class="mask-money w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-bold text-lg">
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Valor principal de entrada. Se os lotes estiverem vazios, este valor será usado como entrada.</p>
                    </div>

                    {{-- Dynamic Batches --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 overflow-hidden text-sm">
                        <div class="p-4 bg-slate-100 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                            <i class="fas fa-layer-group"></i>
                            <span>Lotes de Ingressos</span>
                        </div>
                        <div class="p-6 space-y-6">
                            {{-- Batch 1 --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-slate-200 dark:border-slate-800">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">1º Lote (R$)</label>
                                    <input type="text" name="batch_1_price" value="{{ old('batch_1_price', $event->batch_1_price ? number_format($event->batch_1_price, 2, ',', '.') : '') }}" class="mask-money w-full px-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none text-slate-800 dark:text-white font-semibold" placeholder="0,00">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Até quando?</label>
                                    <input type="datetime-local" name="batch_1_deadline" value="{{ old('batch_1_deadline', $event->batch_1_deadline ? $event->batch_1_deadline->format('Y-m-d\TH:i') : '') }}" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none text-slate-800 dark:text-white font-semibold">
                                </div>
                            </div>
                            {{-- Batch 2 --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-slate-200 dark:border-slate-800">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">2º Lote (R$)</label>
                                    <input type="text" name="batch_2_price" value="{{ old('batch_2_price', $event->batch_2_price ? number_format($event->batch_2_price, 2, ',', '.') : '') }}" class="mask-money w-full px-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none text-slate-800 dark:text-white font-semibold" placeholder="0,00">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Até quando?</label>
                                    <input type="datetime-local" name="batch_2_deadline" value="{{ old('batch_2_deadline', $event->batch_2_deadline ? $event->batch_2_deadline->format('Y-m-d\TH:i') : '') }}" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none text-slate-800 dark:text-white font-semibold">
                                </div>
                            </div>
                            {{-- Batch 3 --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">3º Lote / Na hora (R$)</label>
                                    <input type="text" name="batch_3_price" value="{{ old('batch_3_price', $event->batch_3_price ? number_format($event->batch_3_price, 2, ',', '.') : '') }}" class="mask-money w-full px-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none text-slate-800 dark:text-white font-semibold" placeholder="0,00">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Até o evento</label>
                                    <input type="datetime-local" name="batch_3_deadline" value="{{ old('batch_3_deadline', $event->batch_3_deadline ? $event->batch_3_deadline->format('Y-m-d\TH:i') : '') }}" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none text-slate-800 dark:text-white font-semibold">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-100 dark:border-emerald-800/30 space-y-6 transition-colors">
                        <div class="flex items-center gap-3 text-emerald-700 dark:text-emerald-400 font-bold">
                            <i class="fas fa-bolt"></i>
                            <span>Preço Promocional Flash</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase mb-2 transition-colors">Valor Flash (R$)</label>
                                <input type="text" name="flash_sale_price" value="{{ old('flash_sale_price', $event->flash_sale_price ? number_format($event->flash_sale_price, 2, ',', '.') : '') }}" class="mask-money w-full px-4 py-3 bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-emerald-900 dark:text-white font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase mb-2 transition-colors">Expira em</label>
                                <input type="datetime-local" name="flash_sale_ends_at" value="{{ old('flash_sale_ends_at', $event->flash_sale_ends_at ? $event->flash_sale_ends_at->format('Y-m-d\TH:i') : '') }}" class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-emerald-900 dark:text-white font-medium">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($event->exists)
                {{-- Tab: Certificate --}}
                <div x-show="tab === 'certificate'" class="space-y-6">
                    @php
                        $eventCertificateSettings = $event->certificate_settings ?? [];
                        $eventCertificateTitle = old(
                            'certificate_title',
                            data_get($eventCertificateSettings, 'meta.titleText')
                                ?? data_get($eventCertificateSettings, 'custom_title')
                                ?? data_get($eventCertificateSettings, 'title')
                                ?? 'CERTIFICADO DE PARTICIPACAO'
                        );
                        $eventPresentationText = old(
                            'presentation_text',
                            data_get($eventCertificateSettings, 'meta.presentationText')
                                ?? data_get($eventCertificateSettings, 'custom_presentation_text')
                                ?? data_get($eventCertificateSettings, 'presentation_text')
                                ?? ''
                        );
                        $eventTagLabels = [
                            'student_name' => 'Nome do Participante',
                            'course_name' => 'Nome do Evento',
                            'completion_date' => 'Data do Evento',
                            'certificate_code' => 'Cod. Validacao',
                            'author_name' => 'Organizador',
                            'workload_hours' => 'Info Extra',
                            'title' => 'Titulo do Certificado',
                            'presentation_text' => 'Texto de Apresentacao',
                            'instructor_signature' => 'Assinatura do Organizador',
                            'platform_logo' => 'Logo da Plataforma',
                        ];
                        $eventDefaultTags = [
                            'student_name' => ['x' => 50, 'y' => 40, 'text' => '[Nome do Participante]', 'fontSize' => 30, 'color' => '#000000', 'fontWeight' => 'bold', 'fontFamily' => 'Arial, sans-serif'],
                            'course_name' => ['x' => 50, 'y' => 55, 'text' => $event->title ?: '[Nome do Evento]', 'fontSize' => 24, 'color' => '#333333', 'fontWeight' => 'bold', 'fontFamily' => 'Arial, sans-serif'],
                            'completion_date' => ['x' => 50, 'y' => 65, 'text' => 'Participou em: ' . ($event->start_at ? $event->start_at->format('d/m/Y') : '01/01/2026'), 'fontSize' => 16, 'color' => '#555555', 'fontWeight' => 'normal', 'fontFamily' => 'Arial, sans-serif'],
                            'certificate_code' => ['x' => 50, 'y' => 85, 'text' => 'Validacao: ABC-123', 'fontSize' => 12, 'color' => '#999999', 'fontWeight' => 'normal', 'fontFamily' => 'Arial, sans-serif'],
                            'author_name' => ['x' => 50, 'y' => 90, 'text' => 'UNN Eventos', 'fontSize' => 18, 'color' => '#333333', 'fontWeight' => 'bold', 'fontFamily' => 'Arial, sans-serif', 'zIndex' => 10],
                            'workload_hours' => ['x' => 80, 'y' => 90, 'text' => 'Evento', 'fontSize' => 14, 'color' => '#666666', 'fontWeight' => 'normal', 'fontFamily' => 'Arial, sans-serif', 'zIndex' => 10],
                            'title' => ['x' => 10, 'y' => 18, 'text' => $eventCertificateTitle, 'fontSize' => 34, 'color' => '#000000', 'fontWeight' => 'bold', 'fontFamily' => 'Arial, sans-serif', 'zIndex' => 15, 'visible' => false, 'multiline' => true, 'maxWidth' => 700, 'textAlign' => 'center'],
                            'presentation_text' => ['x' => 10, 'y' => 28, 'text' => $eventPresentationText, 'fontSize' => 16, 'color' => '#333333', 'fontWeight' => 'normal', 'fontFamily' => 'Arial, sans-serif', 'zIndex' => 15, 'visible' => false, 'multiline' => true, 'maxWidth' => 700, 'textAlign' => 'center'],
                            'instructor_signature' => ['x' => 70, 'y' => 80, 'text' => 'Assinatura do Organizador', 'fontSize' => 12, 'color' => '#6c757d', 'fontWeight' => 'normal', 'fontFamily' => 'Arial, sans-serif', 'width' => 200, 'height' => 60, 'zIndex' => 10, 'visible' => (bool) $event->instructor_signature],
                            'platform_logo' => ['x' => 50, 'y' => 10, 'text' => 'LOGO', 'fontSize' => 36, 'color' => '#0066cc', 'fontWeight' => 'bold', 'fontFamily' => 'Georgia, serif', 'width' => 120, 'height' => 60, 'mandatory' => true, 'zIndex' => 20],
                        ];
                    @endphp

                    @include('panel.admin.partials.certificate-editor', [
                        'entity' => $event,
                        'formId' => 'eventForm',
                        'previewUrl' => null,
                        'titleInput' => $eventCertificateTitle,
                        'presentationInput' => $eventPresentationText,
                        'defaultTags' => $eventDefaultTags,
                        'tagLabels' => $eventTagLabels,
                        'backgroundLabel' => 'Fundo do Certificado',
                        'backgroundHint' => 'Recomendado: 1920x1080px (PNG/JPG).',
                        'signatureLabel' => 'Assinatura do Organizador',
                        'signatureHint' => 'Use PNG com fundo transparente para o melhor resultado.',
                        'autoInfoLabel' => 'Referencia automatica',
                        'autoInfoValue' => $event->start_at ? $event->start_at->format('d/m/Y H:i') : 'Data definida apos o cadastro',
                        'saveLabel' => 'Salvar Certificado',
                    ])
                </div>

                {{-- Tab: Gallery --}}
                <div x-show="tab === 'gallery'" class="space-y-6">
                    @include('panel.admin.partials.gallery', [
                        'model' => $event,
                        'uploadUrl' => route('panel.admin.events.media.store', $event),
                        'deleteUrlPattern' => urldecode(route('panel.admin.events.media.destroy', [$event, ':media']))
                    ])
                </div>
            @endif
        </form>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
        <style>
            .note-editor.note-frame {
                border: 1px solid var(--summernote-border);
                border-radius: 1rem;
                overflow: visible;
                font-family: inherit;
                background: var(--summernote-surface);
                isolation: isolate;
            }
            .note-editor .note-editing-area {
                position: relative;
                z-index: 1;
                overflow: hidden;
                border-bottom-left-radius: 1rem;
                border-bottom-right-radius: 1rem;
            }
            .note-editor .note-toolbar,
            .note-editor .note-statusbar {
                position: relative;
            }
            .note-editor .note-toolbar {
                z-index: 4;
            }
            .note-editor .note-statusbar {
                z-index: 0;
            }
            .note-editor .note-toolbar .note-dropdown-menu {
                z-index: 1200;
            }
            .note-editor .note-toolbar {
                background: var(--summernote-toolbar);
                border-bottom: 1px solid var(--summernote-border);
            }
            .note-editor .note-editing-area .note-editable {
                background: var(--summernote-surface);
                color: var(--summernote-text);
            }
            .note-editor .note-editing-area .note-editable p {
                color: inherit;
            }
            .note-editor .note-statusbar {
                background: var(--summernote-toolbar);
                border-top: 1px solid var(--summernote-border);
            }
            .note-editor .note-toolbar .note-dropdown-menu {
                background: var(--summernote-dropdown-bg);
                border-color: var(--summernote-border);
            }
            .note-editor .note-toolbar .note-palette-title {
                color: var(--summernote-dropdown-text);
                border-bottom-color: var(--summernote-dropdown-separator);
            }
            .note-editor .note-toolbar .note-color-reset,
            .note-editor .note-toolbar .note-color-select {
                color: var(--summernote-dropdown-text);
            }
            .note-editor .note-toolbar .note-color-reset:hover,
            .note-editor .note-toolbar .note-color-select:hover {
                background: var(--summernote-dropdown-hover);
            }
            .upload-preview img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                border-radius: 0.75rem;
            }
            .dark .upload-preview img {
                opacity: 0.9;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropArea = document.getElementById('drag-drop-area');
            const fileInput = document.getElementById('image_upload_input');
            const removeInput = document.getElementById('remove_image_input');
            const previewContainer = document.getElementById('upload-preview-container');
            const previewImage = document.getElementById('upload-preview-image');
            const promptArea = document.getElementById('upload-prompt');
            const progressWrapper = document.getElementById('upload-progress-wrapper');
            const progressBar = document.getElementById('upload-progress-bar');
            const progressText = document.getElementById('upload-progress-text');
            const actionsArea = document.getElementById('upload-actions');
            const filenameText = document.getElementById('upload-filename');
            const btnRemove = document.getElementById('btn-remove-image');
            const successBadge = document.getElementById('upload-success-badge');

            if (!dropArea) return;

            // Open file chooser on click
            dropArea.addEventListener('click', () => {
                // Ensure we interact only if not simulating progress
                if (progressWrapper.style.pointerEvents === 'auto') return;
                fileInput.click();
            });

            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Highlight drop area when item is dragged over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => {
                    dropArea.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/40');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => {
                    dropArea.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/40');
                }, false);
            });

            // Handle dropped files
            dropArea.addEventListener('drop', handleDrop, false);
            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length) handleFiles(this.files);
            });

            function handleDrop(e) {
                let dt = e.dataTransfer;
                let files = dt.files;
                if (files.length) {
                    fileInput.files = files; // Sync hidden input for form post
                    handleFiles(files);
                }
            }

            function handleFiles(files) {
                const file = files[0];
                if (!file.type.match('image.*')) {
                    if (typeof toastr !== 'undefined') toastr.error('Por favor, selecione apenas arquivos de imagem.');
                    return;
                }

                if (file.size > 5242880) { // 5MB limit
                    if (typeof toastr !== 'undefined') toastr.error('A imagem excede o tamanho máximo de 5MB.');
                    return;
                }

                // Show basic UI
                filenameText.textContent = file.name;
                filenameText.title = file.name;
                removeInput.value = '0';
                
                // Read and set preview immediately
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    showPreviewUi();
                    simulateFakeUpload();
                };
                reader.readAsDataURL(file);
            }

            function showPreviewUi() {
                promptArea.classList.add('opacity-0');
                previewContainer.classList.remove('opacity-0');
                previewImage.classList.add('scale-105');
                setTimeout(() => previewImage.classList.remove('scale-105'), 50);
                
                actionsArea.classList.remove('hidden');
                actionsArea.classList.add('flex');
                
                successBadge.classList.add('opacity-0', 'translate-y-2');
                successBadge.classList.remove('opacity-100', 'translate-y-0');
            }

            function hidePreviewUi() {
                promptArea.classList.remove('opacity-0');
                previewContainer.classList.add('opacity-0');
                previewImage.src = '';
                
                actionsArea.classList.add('hidden');
                actionsArea.classList.remove('flex');
            }

            function simulateFakeUpload() {
                progressWrapper.classList.remove('opacity-0');
                progressWrapper.style.pointerEvents = 'auto'; // block clicks during upload
                
                let progress = 0;
                progressBar.style.width = '0%';
                progressText.textContent = '0%';

                const interval = setInterval(() => {
                    progress += Math.random() * 20; 
                    if (progress >= 100) progress = 100;
                    
                    progressBar.style.width = Math.min(progress, 100) + '%';
                    progressText.textContent = Math.floor(progress) + '%';

                    if (progress >= 100) {
                        clearInterval(interval);
                        setTimeout(() => {
                            progressWrapper.classList.add('opacity-0');
                            progressWrapper.style.pointerEvents = 'none';
                            
                            // Pop the success badge
                            successBadge.classList.remove('opacity-0', 'translate-y-2');
                            successBadge.classList.add('opacity-100', 'translate-y-0');
                        }, 400); // give user time to see 100%
                    }
                }, 100);
            }

            btnRemove.addEventListener('click', () => {
                fileInput.value = '';
                removeInput.value = '1';
                hidePreviewUi();
                if (typeof toastr !== 'undefined') toastr.info('Imagem removida.');
            });
        });
        </script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <script>
        // Money Mask
        document.querySelectorAll('.mask-money').forEach(function(input) {
            if (input._maskApplied) return;
            input._maskApplied = true;
            input.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, "");
                if (!value) { e.target.value = ""; return; }
                value = (value / 100).toFixed(2) + "";
                value = value.replace(".", ",");
                value = value.replace(/(\d)(\d{3},\d{2})$/g, "$1.$2");
                e.target.value = value;
            });
        });
        </script>

            @if($event->exists)
                @include('panel.admin.partials.certificate-editor-script', [
                    'entity' => $event,
                    'formId' => 'eventForm',
                    'previewUrl' => null,
                    'defaultTags' => $eventDefaultTags,
                    'tagLabels' => $eventTagLabels,
                ])
            @endif

            @if(false)
            // Certificate Editor (Simple)
            $(document).ready(function () {
                if ('{{ $event->exists }}' == '') return;

                let rawCertSettings = {!! $event->certificate_settings ? json_encode($event->certificate_settings) : '{}' !!};
                let certDoc = (rawCertSettings && rawCertSettings.schemaVersion === 2) ? rawCertSettings : { schemaVersion: 2, meta: {}, elements: {} };
                let certSettings = certDoc.elements;

                const platformLogoUrl = "{{ \App\Models\Setting::get('logo_auth') ? asset(ltrim(\App\Models\Setting::get('logo_auth'), '/')) : asset('img/logo.svg') }}";

                const defaultTags = {
                    'student_name': { x: 50, y: 40, text: '[Nome do Participante]', fontSize: 30, color: '#000000', fontWeight: 'bold' },
                    'course_name': { x: 50, y: 55, text: '{{ $event->title }}', fontSize: 24, color: '#333333', fontWeight: 'bold' },
                    'completion_date': { x: 50, y: 65, text: 'Realizado em: 01/01/2026', fontSize: 16, color: '#555555', fontWeight: 'normal' },
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

                $('#eventForm').on('submit', function () {
                    $('#certificate_settings_input').val(JSON.stringify(certDoc));
                });
            });
            @endif
        </script>

        {{-- Busca de estabelecimento por nome (Nominatim) --}}
        <script>
        (function() {
            var locationInput = document.getElementById('panelLocationInput');
            var venueResults = document.getElementById('panelVenueResults');
            var searchBtn = document.getElementById('panelSearchVenueBtn');
            var outOfStateCheck = document.getElementById('panelOutOfState');
            var addressInput = document.getElementById('panelAddressInput');
            var latInput = document.getElementById('panelLatInput');
            var lngInput = document.getElementById('panelLngInput');

            if (!locationInput || !venueResults || !searchBtn) return;

            var userState = @json(auth()->user()->state ?? '');
            var userCity = @json(auth()->user()->city ?? '');
            var userCep = @json(auth()->user()->cep ?? '');
            var locationIqKey = @json(\App\Models\Setting::get('locationiq_api_key', ''));
            var RADIUS_KM = 70;
            var userLat = null;
            var userLon = null;

            // Geocodificar endereco do usuario logado para calcular distancias
            (function() {
                var geoQuery = [userCity, userState, 'Brasil'].filter(Boolean).join(', ');
                if (!geoQuery || geoQuery === 'Brasil') return;
                if (locationIqKey) {
                    fetch('https://us1.locationiq.com/v1/search?key=' + locationIqKey + '&q=' + encodeURIComponent(geoQuery) + '&countrycodes=br&format=json&limit=1')
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data && data[0]) { userLat = parseFloat(data[0].lat); userLon = parseFloat(data[0].lon); }
                        }).catch(function() {});
                }
            })();

            function haversineKm(lat1, lon1, lat2, lon2) {
                var R = 6371;
                var dLat = (lat2 - lat1) * Math.PI / 180;
                var dLon = (lon2 - lon1) * Math.PI / 180;
                var a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
                return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            }

            function searchVenue() {
                var text = locationInput.value.trim();
                if (text.length < 3) { venueResults.classList.add('hidden'); return; }

                venueResults.innerHTML = '<div class="px-4 py-4 text-center text-sm text-slate-500"><i class="fas fa-spinner fa-spin mr-2"></i>Buscando estabelecimentos...</div>';
                venueResults.classList.remove('hidden');

                var promises = [];

                // 1. LocationIQ (melhor cobertura)
                if (locationIqKey) {
                    var liqUrl = 'https://us1.locationiq.com/v1/search?key=' + locationIqKey + '&q=' + encodeURIComponent(text) + '&countrycodes=br&format=json&limit=30&addressdetails=1&dedupe=1';
                    promises.push(fetch(liqUrl).then(function(r) { return r.json(); }).then(function(data) {
                        if (data.error) return [];
                        return (data || []).map(function(item) {
                            var addr = item.address || {};
                            var city = addr.city || addr.town || addr.village || '';
                            var state = addr.state || '';
                            var road = addr.road || ''; var number = addr.house_number || '';
                            var neighbourhood = addr.suburb || addr.neighbourhood || '';
                            var fullAddress = [road, number, neighbourhood, city, state].filter(Boolean).join(', ');
                            var shortName = (addr.amenity || addr.tourism || addr.leisure || addr.shop || '').trim();
                            return { lat: parseFloat(item.lat), lon: parseFloat(item.lon), name: shortName || item.display_name.split(',')[0], address: fullAddress || item.display_name };
                        });
                    }).catch(function() { return []; }));
                }

                // 2. Overpass (OSM direto)
                var overpassQuery = '[out:json][timeout:10];(node["name"~"' + text.replace(/"/g, '') + '",i]["amenity"](area:3600059470);node["name"~"' + text.replace(/"/g, '') + '",i]["shop"](area:3600059470););out body 30;';
                promises.push(fetch('https://overpass-api.de/api/interpreter?data=' + encodeURIComponent(overpassQuery)).then(function(r) { return r.json(); }).then(function(data) {
                    return (data.elements || []).filter(function(el) { return el.lat && el.lon; }).map(function(el) {
                        var tags = el.tags || {};
                        var city = tags['addr:city'] || ''; var state = tags['addr:state'] || '';
                        var road = tags['addr:street'] || ''; var number = tags['addr:housenumber'] || '';
                        var neighbourhood = tags['addr:suburb'] || '';
                        var fullAddress = [road, number, neighbourhood, city, state].filter(Boolean).join(', ');
                        return { lat: parseFloat(el.lat), lon: parseFloat(el.lon), name: tags.name || text, address: fullAddress || 'Sem endereco detalhado' };
                    });
                }).catch(function() { return []; }));

                Promise.all(promises).then(function(results) {
                    var seen = {}; var combined = [];
                    results.forEach(function(items) { (items || []).forEach(function(item) {
                        var key = item.lat.toFixed(4) + ',' + item.lon.toFixed(4);
                        if (!seen[key]) {
                            seen[key] = true;
                            // Calcular distancia do usuario
                            if (userLat !== null && userLon !== null) {
                                item.distance = haversineKm(userLat, userLon, item.lat, item.lon);
                                item.isNearby = item.distance <= RADIUS_KM;
                            } else {
                                item.distance = null;
                                item.isNearby = false;
                            }
                            combined.push(item);
                        }
                    }); });

                    // Ordenar: proximos primeiro, depois por distancia
                    combined.sort(function(a, b) {
                        if (a.isNearby && !b.isNearby) return -1;
                        if (!a.isNearby && b.isNearby) return 1;
                        if (a.distance !== null && b.distance !== null) return a.distance - b.distance;
                        return 0;
                    });

                    renderResults(combined);
                });
            }

            function renderResults(data) {
                if (!data || data.length === 0) {
                    venueResults.innerHTML = '<div class="px-4 py-4 text-center text-sm text-slate-500"><i class="fas fa-search mr-1"></i>Nenhum resultado encontrado. Tente outro nome ou digite o endereco manualmente.</div>';
                    return;
                }

                var items = data.slice(0, 20);
                var totalFound = data.length;
                var nearbyCount = items.filter(function(i) { return i.isNearby; }).length;

                var html = '<div class="px-4 py-2 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-500 flex items-center justify-between">'
                    + '<span><i class="fas fa-list mr-1"></i>' + Math.min(totalFound, 20) + ' resultados</span>'
                    + (nearbyCount > 0 ? '<span class="text-emerald-600"><i class="fas fa-map-pin mr-1"></i>' + nearbyCount + ' dentro de ' + RADIUS_KM + 'km</span>' : '')
                    + '</div>';

                items.forEach(function(item, idx) {
                    var distLabel = '';
                    if (item.distance !== null) {
                        distLabel = item.distance < 1 ? '< 1 km' : Math.round(item.distance) + ' km';
                    }

                    html += '<button type="button" class="venue-item w-full text-left px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-800 border-b border-slate-100 dark:border-slate-800 last:border-0 transition-colors" '
                        + 'data-lat="' + item.lat + '" data-lon="' + item.lon + '" '
                        + 'data-name="' + item.name.replace(/"/g, '&quot;') + '" '
                        + 'data-address="' + item.address.replace(/"/g, '&quot;') + '" '
                        + 'data-nearby="' + (item.isNearby ? '1' : '0') + '">'
                        + '<div class="flex items-start gap-2">'
                        + '<span class="mt-0.5 text-xs font-black ' + (item.isNearby ? 'text-emerald-500' : 'text-slate-400') + '">' + (idx + 1) + '.</span>'
                        + '<div class="flex-1 min-w-0">'
                        + '<p class="text-sm font-bold text-slate-900 dark:text-white truncate">' + item.name + '</p>'
                        + '<p class="text-xs text-slate-500 dark:text-slate-400 truncate">' + item.address + '</p>'
                        + '<div class="flex items-center gap-2 mt-1">'
                        + (distLabel ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ' + (item.isNearby ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300') + '"><i class="fas fa-route text-[8px]"></i>' + distLabel + '</span>' : '')
                        + (item.isNearby ? '<span class="text-[10px] font-bold text-emerald-600">Proximo</span>' : '<span class="text-[10px] font-bold text-amber-600">Fora do raio</span>')
                        + '</div>'
                        + '</div></div></button>';
                });

                venueResults.innerHTML = html;

                venueResults.querySelectorAll('.venue-item').forEach(function(el) {
                    el.addEventListener('click', function() {
                        locationInput.value = this.dataset.name;
                        if (addressInput) addressInput.value = this.dataset.address;
                        if (latInput) latInput.value = this.dataset.lat;
                        if (lngInput) lngInput.value = this.dataset.lon;
                        venueResults.classList.add('hidden');

                        // Auto-marcar "fora do estado" se o local esta fora do raio
                        var isNearby = this.dataset.nearby === '1';
                        if (!isNearby && outOfStateCheck) {
                            outOfStateCheck.checked = true;
                        } else if (isNearby && outOfStateCheck) {
                            outOfStateCheck.checked = false;
                        }

                        if (typeof toastr !== 'undefined') toastr.success('Local selecionado: ' + this.dataset.name);
                    });
                });
            }
            searchBtn.addEventListener('click', searchVenue);
            locationInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); searchVenue(); } });
            document.addEventListener('click', function(e) {
                if (!venueResults.contains(e.target) && e.target !== locationInput && e.target !== searchBtn && !searchBtn.contains(e.target)) {
                    venueResults.classList.add('hidden');
                }
            });
            if (outOfStateCheck) outOfStateCheck.addEventListener('change', function() { venueResults.classList.add('hidden'); });

            // Busca de endereco por texto (geocoding via Nominatim)
            var searchAddressBtn = document.getElementById('panelSearchAddressBtn');
            if (searchAddressBtn && addressInput) {
                searchAddressBtn.addEventListener('click', function() {
                    var query = addressInput.value.trim();
                    if (query.length < 5) { if (typeof toastr !== 'undefined') toastr.warning('Digite um endereco mais completo.'); return; }
                    if (typeof toastr !== 'undefined') toastr.info('Buscando endereco...');
                    fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query + ', Brasil') + '&countrycodes=br&limit=1&addressdetails=1', { headers: { 'Accept-Language': 'pt-BR' } })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data && data.length > 0) {
                                latInput.value = data[0].lat;
                                lngInput.value = data[0].lon;
                                if (typeof toastr !== 'undefined') toastr.success('Endereco localizado! Lat/Lng preenchidos.');
                            } else {
                                if (typeof toastr !== 'undefined') toastr.error('Endereco nao encontrado. Tente ser mais especifico.');
                            }
                        })
                        .catch(function() { if (typeof toastr !== 'undefined') toastr.error('Erro na busca de endereco.'); });
                });
                addressInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); searchAddressBtn.click(); } });
            }
        })();
        </script>
    @endpush

@endsection
