@extends('panel.layouts.app')

@section('title', 'Editor de Faturas')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                <i class="fas fa-paint-brush text-blue-600 mr-2"></i>Editor Visual de Faturas
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Personalize cores, logo, layout e dados impressos nas faturas dos clientes.
            </p>
        </div>
        <div class="flex gap-3">
            <button type="button" id="btn-refresh-preview"
                class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl transition-all">
                <i class="fas fa-sync-alt"></i> Atualizar Preview
            </button>
            <button type="button" id="btn-save"
                class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-blue-600/20">
                <i class="fas fa-save"></i> Salvar Configurações
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Painel de Configurações --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 overflow-hidden">
                {{-- Abas --}}
                <div class="flex border-b border-slate-100 dark:border-slate-800 overflow-x-auto">
                    <button class="editor-tab active flex-1 px-3 py-3 text-xs font-bold text-center transition-all" data-tab="cores">
                        <i class="fas fa-palette block text-lg mb-1"></i>Cores
                    </button>
                    <button class="editor-tab flex-1 px-3 py-3 text-xs font-bold text-center transition-all" data-tab="logo">
                        <i class="fas fa-image block text-lg mb-1"></i>Logo
                    </button>
                    <button class="editor-tab flex-1 px-3 py-3 text-xs font-bold text-center transition-all" data-tab="layout">
                        <i class="fas fa-th-large block text-lg mb-1"></i>Layout
                    </button>
                    <button class="editor-tab flex-1 px-3 py-3 text-xs font-bold text-center transition-all" data-tab="dados">
                        <i class="fas fa-building block text-lg mb-1"></i>Dados
                    </button>
                    <button class="editor-tab flex-1 px-3 py-3 text-xs font-bold text-center transition-all" data-tab="avancado">
                        <i class="fas fa-code block text-lg mb-1"></i>CSS
                    </button>
                </div>

                <div class="p-5 max-h-[calc(100vh-280px)] overflow-y-auto">
                    {{-- Aba Cores --}}
                    <div class="editor-panel" id="panel-cores">
                        @foreach([
                            ['invoice_primary_color', 'Cor Primária', 'Títulos, bordas e destaques'],
                            ['invoice_secondary_color', 'Cor Secundária', 'Elementos secundários'],
                            ['invoice_text_color', 'Cor do Texto', 'Cor principal do texto'],
                            ['invoice_bg_color', 'Fundo do Header', 'Cor de fundo do cabeçalho'],
                        ] as [$key, $label, $hint])
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">{{ $label }}</label>
                            <div class="flex items-center gap-3">
                                <input type="color" class="invoice-setting w-12 h-10 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer"
                                    id="{{ $key }}" name="{{ $key }}" value="{{ $settings[$key] }}">
                                <span class="text-xs font-mono text-slate-500">{{ $settings[$key] }}</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">{{ $hint }}</p>
                        </div>
                        @endforeach
                    </div>

                    {{-- Aba Logo --}}
                    <div class="editor-panel hidden" id="panel-logo">
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Posição do Logo</label>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach(['left' => 'Esquerda', 'center' => 'Centro', 'right' => 'Direita'] as $val => $label)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="invoice_logo_position" value="{{ $val }}" class="invoice-setting sr-only peer"
                                        {{ $settings['invoice_logo_position'] === $val ? 'checked' : '' }}>
                                    <div class="p-3 text-center rounded-xl border-2 border-slate-200 dark:border-slate-700 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 transition-all">
                                        <i class="fas fa-align-{{ $val === 'center' ? 'center' : ($val === 'right' ? 'right' : 'left') }} text-lg mb-1"></i>
                                        <p class="text-xs font-bold">{{ $label }}</p>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                Tamanho: <span id="logo-height-value" class="text-blue-600">{{ $settings['invoice_logo_max_height'] }}px</span>
                            </label>
                            <input type="range" class="invoice-setting w-full" id="invoice_logo_max_height"
                                name="invoice_logo_max_height" min="30" max="120" step="5"
                                value="{{ $settings['invoice_logo_max_height'] }}">
                            <div class="flex justify-between text-xs text-slate-400 mt-1">
                                <span>30px</span><span>120px</span>
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3 text-xs text-blue-700 dark:text-blue-300">
                            <i class="fas fa-info-circle mr-1"></i>O logo utilizado é o configurado nas Configurações Globais.
                        </div>
                    </div>

                    {{-- Aba Layout --}}
                    <div class="editor-panel hidden" id="panel-layout">
                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-wider mb-3">Visibilidade</h4>

                        @foreach([
                            ['invoice_show_company_address', 'Endereço da empresa'],
                            ['invoice_show_company_phone', 'Telefone'],
                            ['invoice_show_company_email', 'E-mail'],
                            ['invoice_show_due_date', 'Data de vencimento'],
                            ['invoice_show_status_badge', 'Badge de status'],
                            ['invoice_show_notes', 'Observações'],
                            ['invoice_show_footer', 'Rodapé'],
                        ] as [$key, $label])
                        <label class="flex items-center justify-between py-2 cursor-pointer">
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                            <div class="relative">
                                <input type="checkbox" class="invoice-setting sr-only peer" id="{{ $key }}" name="{{ $key }}"
                                    {{ $settings[$key] ? 'checked' : '' }}>
                                <div class="w-10 h-5 bg-slate-200 dark:bg-slate-700 rounded-full peer-checked:bg-blue-600 transition-colors"></div>
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow peer-checked:translate-x-5 transition-transform"></div>
                            </div>
                        </label>
                        @endforeach

                        <hr class="my-4 border-slate-100 dark:border-slate-800">
                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-wider mb-3">Textos</h4>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Cabeçalho</label>
                            <input type="text" class="invoice-setting w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                                id="invoice_header_text" name="invoice_header_text" value="{{ $settings['invoice_header_text'] }}"
                                placeholder="FATURA, NOTA FISCAL, RECIBO...">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Rodapé</label>
                            <textarea class="invoice-setting w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm" rows="2"
                                id="invoice_footer_text" name="invoice_footer_text"
                                placeholder="Obrigado pela sua preferência!">{{ $settings['invoice_footer_text'] }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Fonte</label>
                            <select class="invoice-setting w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                                id="invoice_font_family" name="invoice_font_family">
                                <option value="DejaVu Sans" {{ $settings['invoice_font_family'] === 'DejaVu Sans' ? 'selected' : '' }}>DejaVu Sans</option>
                                <option value="Helvetica" {{ $settings['invoice_font_family'] === 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                                <option value="Courier" {{ $settings['invoice_font_family'] === 'Courier' ? 'selected' : '' }}>Courier</option>
                            </select>
                        </div>
                    </div>

                    {{-- Aba Dados --}}
                    <div class="editor-panel hidden" id="panel-dados">
                        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-3 text-xs text-amber-700 dark:text-amber-300 mb-4">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Alterações aqui afetam todo o sistema.
                        </div>

                        @foreach([
                            ['company_name', 'Nome da Empresa'],
                            ['company_cnpj', 'CNPJ'],
                            ['company_address', 'Endereço'],
                            ['company_phone', 'Telefone'],
                            ['company_email', 'E-mail'],
                        ] as [$key, $label])
                        <div class="mb-3">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">{{ $label }}</label>
                            <input type="text" class="company-field w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                                id="{{ $key }}" value="{{ $companyData[$key] ?? '' }}">
                        </div>
                        @endforeach

                        <button type="button" id="btn-save-company"
                            class="w-full mt-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all">
                            <i class="fas fa-save mr-1"></i> Salvar Dados da Empresa
                        </button>
                    </div>

                    {{-- Aba Avançado --}}
                    <div class="editor-panel hidden" id="panel-avancado">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">CSS Customizado</label>
                            <textarea class="invoice-setting w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono" rows="10"
                                id="invoice_custom_css" name="invoice_custom_css"
                                placeholder="/* CSS personalizado */">{{ $settings['invoice_custom_css'] }}</textarea>
                        </div>

                        <button type="button" id="btn-reset-defaults"
                            class="w-full py-2 border-2 border-red-300 text-red-600 hover:bg-red-50 text-sm font-bold rounded-xl transition-all">
                            <i class="fas fa-undo mr-1"></i> Restaurar Padrões
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">
                        <i class="fas fa-eye mr-1"></i> Preview da Fatura
                    </span>
                    <span class="text-xs font-bold px-2 py-1 rounded-full bg-green-100 text-green-700" id="preview-status">Atualizado</span>
                </div>
                <div class="relative" style="height: calc(100vh - 280px); background: #e5e7eb;">
                    <iframe id="preview-iframe"
                        src="{{ route('panel.admin.invoices.editor.preview') }}"
                        class="w-full h-full border-0 bg-white"
                        title="Preview da Fatura"></iframe>
                    <div id="preview-loading" class="hidden absolute inset-0 bg-white/80 flex items-center justify-center z-10">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin fa-2x text-blue-600"></i>
                            <p class="mt-2 text-sm text-slate-500">Gerando preview...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    var previewTimer = null;
    var saveUrl = @json(route('panel.admin.invoices.editor.save'));
    var resetUrl = @json(route('panel.admin.invoices.editor.reset'));
    var previewUrl = @json(route('panel.admin.invoices.editor.preview'));
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Abas
    $('.editor-tab').on('click', function() {
        var tab = $(this).data('tab');
        $('.editor-tab').removeClass('active text-blue-600 border-b-2 border-blue-600').addClass('text-slate-500');
        $(this).addClass('active text-blue-600 border-b-2 border-blue-600').removeClass('text-slate-500');
        $('.editor-panel').addClass('hidden');
        $('#panel-' + tab).removeClass('hidden');
    });

    // Slider label
    $('#invoice_logo_max_height').on('input', function() {
        $('#logo-height-value').text($(this).val() + 'px');
    });

    // Color picker label
    $('input[type="color"]').on('input', function() {
        $(this).next('span').text($(this).val());
    });

    // Debounce preview
    function schedulePreview() {
        $('#preview-status').text('Pendente...').removeClass('bg-green-100 text-green-700').addClass('bg-yellow-100 text-yellow-700');
        if (previewTimer) clearTimeout(previewTimer);
        previewTimer = setTimeout(refreshPreview, 500);
    }

    function refreshPreview() {
        $('#preview-loading').removeClass('hidden');
        var data = collectSettings();
        $.ajax({
            url: saveUrl,
            method: 'POST',
            data: data,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() {
                var iframe = document.getElementById('preview-iframe');
                iframe.src = previewUrl + '?t=' + Date.now();
                iframe.onload = function() {
                    $('#preview-loading').addClass('hidden');
                    $('#preview-status').text('Atualizado').removeClass('bg-yellow-100 text-yellow-700').addClass('bg-green-100 text-green-700');
                };
            },
            error: function() {
                $('#preview-loading').addClass('hidden');
                $('#preview-status').text('Erro').addClass('bg-red-100 text-red-700');
            }
        });
    }

    function collectSettings() {
        var data = {};
        $('.invoice-setting').each(function() {
            var $el = $(this);
            var name = $el.attr('name');
            if (!name) return;
            if ($el.is(':checkbox')) {
                data[name] = $el.is(':checked') ? '1' : '0';
            } else if ($el.is(':radio')) {
                if ($el.is(':checked')) data[name] = $el.val();
            } else {
                data[name] = $el.val();
            }
        });
        return data;
    }

    $(document).on('change input', '.invoice-setting', schedulePreview);
    $('#btn-refresh-preview').on('click', refreshPreview);

    // Salvar
    $('#btn-save').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');
        $.ajax({
            url: saveUrl, method: 'POST', data: collectSettings(),
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Salvar Configurações');
                Swal.fire({ icon: 'success', title: 'Salvo!', text: res.message, timer: 2000, showConfirmButton: false });
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Salvar Configurações');
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha ao salvar.' });
            }
        });
    });

    // Restaurar
    $('#btn-reset-defaults').on('click', function() {
        Swal.fire({
            title: 'Restaurar Padrões?', text: 'Todas as configurações visuais serão revertidas.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sim, restaurar!', cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (r.isConfirmed) {
                $.ajax({
                    url: resetUrl, method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function() { Swal.fire({ icon: 'success', title: 'Restaurado!', timer: 1500, showConfirmButton: false }); setTimeout(function() { location.reload(); }, 1500); }
                });
            }
        });
    });

    // Salvar empresa
    $('#btn-save-company').on('click', function() {
        var data = {};
        $('.company-field').each(function() { data[$(this).attr('id')] = $(this).val(); });
        $.ajax({
            url: saveUrl, method: 'POST', data: data, headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() {
                Swal.fire({ icon: 'success', title: 'Salvo!', text: 'Dados da empresa atualizados.', timer: 2000, showConfirmButton: false });
                refreshPreview();
            }
        });
    });
});
</script>
@endpush

<style>
.editor-tab.active { color: #2563eb; border-bottom: 2px solid #2563eb; }
.editor-tab:not(.active) { color: #94a3b8; }
.editor-tab:hover:not(.active) { color: #64748b; }
</style>
