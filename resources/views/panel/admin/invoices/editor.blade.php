@extends('panel.layouts.app')

@section('title', 'Editor de Faturas')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.invoices.index') }}" class="hover:underline">Faturas</a>
    <span class="mx-1">/</span>
    <span>Editor Visual</span>
@endsection

@section('panel_content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white transition-colors">Editor de Faturas</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm transition-colors">Personalize o visual das faturas emitidas pelo sistema.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" id="btn-save" class="inline-flex items-center gap-2 px-5 py-2.5 text-white text-sm font-bold rounded-2xl transition-all shadow-lg hover:scale-[1.02] active:scale-[0.98]" style="background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%);">
                <i class="fas fa-save"></i> Salvar
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        {{-- Configurações (coluna esquerda) --}}
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden sticky top-24">
                {{-- Abas --}}
                <div class="flex border-b border-slate-100 dark:border-slate-800">
                    @foreach(['cores' => 'Cores', 'logo' => 'Logo', 'layout' => 'Layout', 'dados' => 'Dados', 'css' => 'CSS'] as $tab => $label)
                    <button class="editor-tab flex-1 py-3 text-[11px] font-bold text-center transition-all {{ $loop->first ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-400 hover:text-slate-600' }}" data-tab="{{ $tab }}">{{ $label }}</button>
                    @endforeach
                </div>

                <div class="p-4 max-h-[60vh] overflow-y-auto">
                    {{-- Cores --}}
                    <div class="editor-panel" id="panel-cores">
                        @foreach([
                            ['invoice_primary_color', 'Primária', '#1F5EDB'],
                            ['invoice_secondary_color', 'Secundária', '#177FD6'],
                            ['invoice_text_color', 'Texto', '#1f2937'],
                            ['invoice_bg_color', 'Fundo Header', '#f9fafb'],
                        ] as [$key, $label, $default])
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                            <input type="color" class="invoice-setting w-8 h-8 rounded-lg border-0 cursor-pointer" name="{{ $key }}" value="{{ $settings[$key] ?? $default }}">
                        </div>
                        @endforeach
                    </div>

                    {{-- Logo --}}
                    <div class="editor-panel hidden" id="panel-logo">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Posição</label>
                        <div class="grid grid-cols-3 gap-2 mb-4">
                            @foreach(['left' => 'Esq.', 'center' => 'Centro', 'right' => 'Dir.'] as $val => $lbl)
                            <label class="cursor-pointer">
                                <input type="radio" name="invoice_logo_position" value="{{ $val }}" class="invoice-setting sr-only peer" {{ ($settings['invoice_logo_position'] ?? 'left') === $val ? 'checked' : '' }}>
                                <div class="py-2 text-center text-xs font-bold rounded-xl border-2 border-slate-200 dark:border-slate-700 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 transition-all">{{ $lbl }}</div>
                            </label>
                            @endforeach
                        </div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tamanho: <span id="logo-h-val">{{ $settings['invoice_logo_max_height'] ?? 60 }}px</span></label>
                        <input type="range" class="invoice-setting w-full" name="invoice_logo_max_height" min="30" max="120" step="5" value="{{ $settings['invoice_logo_max_height'] ?? 60 }}">
                    </div>

                    {{-- Layout --}}
                    <div class="editor-panel hidden" id="panel-layout">
                        @foreach([
                            ['invoice_show_company_address', 'Endereço'],
                            ['invoice_show_company_phone', 'Telefone'],
                            ['invoice_show_company_email', 'E-mail'],
                            ['invoice_show_due_date', 'Vencimento'],
                            ['invoice_show_status_badge', 'Status'],
                            ['invoice_show_notes', 'Observações'],
                            ['invoice_show_footer', 'Rodapé'],
                        ] as [$key, $label])
                        <label class="flex items-center justify-between py-1.5 cursor-pointer">
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                            <input type="checkbox" class="invoice-setting w-4 h-4 rounded text-blue-600 border-slate-300" name="{{ $key }}" {{ ($settings[$key] ?? true) ? 'checked' : '' }}>
                        </label>
                        @endforeach
                        <hr class="my-3 border-slate-100 dark:border-slate-800">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Título</label>
                                <input type="text" class="invoice-setting w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" name="invoice_header_text" value="{{ $settings['invoice_header_text'] ?? 'FATURA' }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Rodapé</label>
                                <input type="text" class="invoice-setting w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" name="invoice_footer_text" value="{{ $settings['invoice_footer_text'] ?? 'Obrigado pela sua preferência!' }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Fonte</label>
                                <select class="invoice-setting w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" name="invoice_font_family">
                                    @foreach(['DejaVu Sans', 'Helvetica', 'Courier'] as $f)
                                    <option {{ ($settings['invoice_font_family'] ?? 'DejaVu Sans') === $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Dados --}}
                    <div class="editor-panel hidden" id="panel-dados">
                        @foreach(['company_name' => 'Empresa', 'company_phone' => 'Telefone', 'company_email' => 'E-mail', 'company_address' => 'Endereço'] as $key => $label)
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-500 mb-1">{{ $label }}</label>
                            <input type="text" class="company-field w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" id="{{ $key }}" value="{{ $companyData[$key] ?? '' }}">
                        </div>
                        @endforeach
                        <button type="button" id="btn-save-company" class="w-full py-2 mt-2 bg-slate-800 dark:bg-slate-700 text-white text-sm font-bold rounded-xl">
                            <i class="fas fa-save mr-1"></i> Salvar Dados
                        </button>
                    </div>

                    {{-- CSS --}}
                    <div class="editor-panel hidden" id="panel-css">
                        <textarea class="invoice-setting w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" name="invoice_custom_css" rows="8" placeholder="/* CSS personalizado */">{{ $settings['invoice_custom_css'] ?? '' }}</textarea>
                        <button type="button" id="btn-reset" class="w-full py-2 mt-3 border-2 border-red-200 text-red-600 text-sm font-bold rounded-xl hover:bg-red-50 transition-all">
                            <i class="fas fa-undo mr-1"></i> Restaurar Padrões
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview (coluna direita) --}}
        <div class="lg:col-span-8 xl:col-span-9">
            <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-sm font-bold text-slate-600 dark:text-slate-400"><i class="fas fa-eye mr-1"></i> Preview</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700" id="preview-badge">Atualizado</span>
                </div>
                <div class="bg-slate-100 dark:bg-slate-800 p-4" style="min-height: 70vh;">
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden mx-auto" style="max-width: 700px; aspect-ratio: 210/297;">
                        <iframe id="preview-frame" class="w-full h-full border-0" srcdoc="<p style='padding:40px;color:#999;text-align:center;'>Carregando preview...</p>"></iframe>
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
    var debounceTimer = null;
    var previewUrl = @json(route('panel.admin.invoices.editor.preview'));
    var saveUrl = @json(route('panel.admin.invoices.editor.save'));
    var resetUrl = @json(route('panel.admin.invoices.editor.reset'));
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Abas
    $('.editor-tab').on('click', function() {
        var tab = $(this).data('tab');
        $('.editor-tab').removeClass('text-blue-600 border-b-2 border-blue-600').addClass('text-slate-400');
        $(this).addClass('text-blue-600 border-b-2 border-blue-600').removeClass('text-slate-400');
        $('.editor-panel').addClass('hidden');
        $('#panel-' + tab).removeClass('hidden');
    });

    // Slider
    $('input[name="invoice_logo_max_height"]').on('input', function() {
        $('#logo-h-val').text($(this).val() + 'px');
    });

    // Preview sem refresh — carrega HTML direto no iframe
    function loadPreview() {
        $('#preview-badge').text('Atualizando...').removeClass('bg-green-100 text-green-700').addClass('bg-yellow-100 text-yellow-700');
        $.get(previewUrl + '?t=' + Date.now(), function(html) {
            var frame = document.getElementById('preview-frame');
            frame.srcdoc = html;
            $('#preview-badge').text('Atualizado').removeClass('bg-yellow-100 text-yellow-700').addClass('bg-green-100 text-green-700');
        });
    }

    // Debounce: salva + atualiza preview sem reload de página
    function scheduleUpdate() {
        $('#preview-badge').text('Pendente...').removeClass('bg-green-100 text-green-700').addClass('bg-yellow-100 text-yellow-700');
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            var data = collectSettings();
            $.ajax({
                url: saveUrl, method: 'POST', data: data,
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function() { loadPreview(); }
            });
        }, 400);
    }

    function collectSettings() {
        var data = {};
        $('.invoice-setting').each(function() {
            var $el = $(this), name = $el.attr('name');
            if (!name) return;
            if ($el.is(':checkbox')) data[name] = $el.is(':checked') ? '1' : '0';
            else if ($el.is(':radio')) { if ($el.is(':checked')) data[name] = $el.val(); }
            else data[name] = $el.val();
        });
        return data;
    }

    // Eventos
    $(document).on('change input', '.invoice-setting', scheduleUpdate);

    // Salvar
    $('#btn-save').on('click', function() {
        var $b = $(this).prop('disabled', true);
        $.ajax({
            url: saveUrl, method: 'POST', data: collectSettings(),
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(r) {
                $b.prop('disabled', false);
                Swal.fire({ icon: 'success', title: 'Salvo!', text: r.message, timer: 1500, showConfirmButton: false });
            },
            error: function() { $b.prop('disabled', false); Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha ao salvar.' }); }
        });
    });

    // Restaurar
    $('#btn-reset').on('click', function() {
        Swal.fire({ title: 'Restaurar padrões?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Sim', cancelButtonText: 'Cancelar' }).then(function(r) {
            if (r.isConfirmed) $.ajax({ url: resetUrl, method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, success: function() { location.reload(); } });
        });
    });

    // Salvar empresa
    $('#btn-save-company').on('click', function() {
        var data = {};
        $('.company-field').each(function() { data[$(this).attr('id')] = $(this).val(); });
        $.ajax({ url: saveUrl, method: 'POST', data: data, headers: { 'X-CSRF-TOKEN': csrfToken }, success: function() {
            Swal.fire({ icon: 'success', title: 'Salvo!', timer: 1500, showConfirmButton: false }); loadPreview();
        }});
    });

    // Carregar preview inicial
    loadPreview();
});
</script>
@endpush
