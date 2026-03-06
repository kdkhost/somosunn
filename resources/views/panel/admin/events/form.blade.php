@extends('panel.layouts.app')

@section('title', $event->exists ? 'Editar Evento: ' . $event->title : 'Novo Evento')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.events.index') }}" class="hover:underline">Eventos</a>
@endsection

@section('panel_content')
    <div x-data="{ tab: 'general' }" class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    {{ $event->exists ? 'Editar Evento' : 'Novo Evento' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Configure datas, local e venda
                    de ingressos para seu evento.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.events.index') }}"
                    class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                    Cancelar
                </a>
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
            <button type="button" @click="tab = 'location'"
                :class="tab === 'location' 
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                            : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 shadow-sm'"
                class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                <i class="fas fa-map-marker-alt"></i>
                <span>Local & Capacidade</span>
            </button>
            <button type="button" @click="tab = 'pricing'"
                :class="tab === 'pricing' 
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                            : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 shadow-sm'"
                class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
                <i class="fas fa-tag"></i>
                <span>Preço & Ingressos</span>
            </button>
            @if($event->exists)
                <button type="button" @click="tab = 'certificate'"
                    :class="tab === 'certificate' 
                                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                                        : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 shadow-sm'"
                    class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2">
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
            <div x-show="tab === 'general'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-6 transition-colors duration-300">
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Título
                                do Evento</label>
                            <input type="text" name="title" value="{{ old('title', $event->title) }}" required
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Início</label>
                                <input type="datetime-local" name="start_at"
                                    value="{{ old('start_at', $event->start_at ? $event->start_at->format('Y-m-d\TH:i') : '') }}"
                                    required
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Fim
                                    (Opcional)</label>
                                <input type="datetime-local" name="end_at"
                                    value="{{ old('end_at', $event->end_at ? $event->end_at->format('Y-m-d\TH:i') : '') }}"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-2">
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
                    <div
                        class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-4 transition-colors">Capa
                            do Evento</label>
                        <div class="space-y-4">
                            <div
                                class="aspect-video w-full rounded-2xl bg-slate-100 dark:bg-slate-950 overflow-hidden border border-slate-200 dark:border-slate-800 relative group">
                                @if($event->image)
                                    <img src="{{ asset('storage/' . $event->image) }}" class="w-full h-full object-cover">
                                @else
                                    <div
                                        class="w-full h-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 transition-colors">
                                        <i class="fas fa-image text-4xl mb-2"></i>
                                        <span class="text-xs font-bold text-center px-4 italic">Formatos: JPG, PNG, GIF. Máx:
                                            5MB</span>
                                    </div>
                                @endif
                            </div>
                            <input type="file" name="image"
                                class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50 transition-all cursor-pointer">
                        </div>
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
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Nome
                            do Local</label>
                        <input type="text" name="location" value="{{ old('location', $event->location) }}"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                            placeholder="Ex: Espaço Solarium / Online">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Endereço
                            Completo</label>
                        <input type="text" name="address" value="{{ old('address', $event->address) }}"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                            placeholder="Ex: Av. Paulista, 1000 - São Paulo, SP">
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
                </div>
            </div>

            {{-- Tab: Pricing --}}
            <div x-show="tab === 'pricing'" class="max-w-3xl space-y-6">
                <div
                    class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-6 transition-colors duration-300">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Preço
                            do Ingresso (R$)</label>
                        <div class="relative">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 font-bold transition-colors">R$</span>
                            <input type="text" name="price"
                                value="{{ old('price', number_format($event->price, 2, ',', '.')) }}"
                                class="mask-money w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-bold text-lg">
                        </div>
                    </div>

                    <div
                        class="p-6 bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-100 dark:border-emerald-800/30 space-y-6 transition-colors">
                        <div class="flex items-center gap-3 text-emerald-700 dark:text-emerald-400 font-bold">
                            <i class="fas fa-bolt"></i>
                            <span>Preço Promocional Flash</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase mb-2 transition-colors">Valor
                                    Flash</label>
                                <input type="text" name="flash_sale_price"
                                    value="{{ old('flash_sale_price', $event->flash_sale_price ? number_format($event->flash_sale_price, 2, ',', '.') : '') }}"
                                    class="mask-money w-full px-4 py-3 bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-emerald-900 dark:text-white font-bold">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase mb-2 transition-colors">Expira
                                    em</label>
                                <input type="datetime-local" name="flash_sale_ends_at"
                                    value="{{ old('flash_sale_ends_at', $event->flash_sale_ends_at ? $event->flash_sale_ends_at->format('Y-m-d\TH:i') : '') }}"
                                    class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-emerald-900 dark:text-white font-medium">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab: Certificate --}}
            @if($event->exists)
                <div x-show="tab === 'certificate'" class="space-y-6">
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                        {{-- Preview Canvas --}}
                        <div
                            class="xl:col-span-2 bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 min-h-[600px] flex flex-col transition-colors duration-300">
                            <div class="flex items-center justify-between mb-6">
                                <h3
                                    class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider transition-colors">
                                    Editor do Certificado</h3>
                            </div>

                            <div
                                class="flex-1 bg-slate-100 dark:bg-slate-950 rounded-2xl p-8 flex items-center justify-center overflow-auto border-2 border-dashed border-slate-200 dark:border-slate-800 transition-colors">
                                <div id="cert-canvas" class="relative bg-white shadow-2xl overflow-hidden shrink-0"
                                    style="width: 842px; height: 595px;">
                                    @if($event->certificate_bg)
                                        <img src="{{ asset($event->certificate_bg) }}" id="cert-bg-img"
                                            class="absolute inset-0 w-full h-full object-cover z-0">
                                    @endif
                                    <div id="cert-elements-layer" class="absolute inset-0 z-10"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Settings --}}
                        <div class="space-y-6">
                            <div
                                class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors duration-300">
                                <div class="flex items-center gap-3 mb-6">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center transition-colors">
                                        <i class="fas fa-cog"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase transition-colors">
                                        Configurações</h3>
                                </div>

                                <div class="space-y-6">
                                    <div
                                        class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-800 transition-colors">
                                        <span
                                            class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase transition-colors">Habilitar
                                            Certificado</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_certificate_enabled" value="1"
                                                @checked($event->is_certificate_enabled) class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 dark:after:border-slate-600 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 transition-colors">
                                            </div>
                                        </label>
                                    </div>

                                    <input type="hidden" name="certificate_settings" id="certificate_settings_input">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </form>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
        <script>
            $(document).ready(function () {
                $('#eventDescription').summernote({
                    height: 300,
                    placeholder: 'Descreva o evento aqui...',
                    lang: 'pt-BR',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            });

            // Money Mask
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
        </script>
    @endpush

@endsection
