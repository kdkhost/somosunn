@extends('panel.layouts.app')

@section('title', 'Editor de Faturas')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.invoices.index') }}" class="hover:underline">Faturas</a>
    <span class="mx-1">/</span>
    <span>Editor Visual</span>
@endsection

@section('panel_content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white transition-colors">Editor de Faturas</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Personalize o visual das faturas emitidas pelo sistema.</p>
        </div>
        <button type="button" id="btn-save" class="inline-flex items-center gap-2 px-6 py-3 text-white text-sm font-bold rounded-2xl transition-all hover:scale-[1.02] active:scale-[0.98]" style="background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%);">
            <i class="fas fa-save"></i> Salvar Configurações
        </button>
    </div>

    {{-- Preview --}}
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 dark:border-slate-800">
            <span class="text-sm font-bold text-slate-600 dark:text-slate-400"><i class="fas fa-file-pdf mr-2"></i>Preview da Fatura</span>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700" id="preview-badge">Atualizado</span>
        </div>
        <div class="bg-slate-100 dark:bg-slate-800 p-6 flex justify-center">
            <div class="bg-white shadow-2xl rounded-lg overflow-hidden w-full" style="max-width: 680px; height: 500px;">
                <iframe id="preview-frame" class="w-full h-full border-0" srcdoc="<div style='padding:60px;text-align:center;color:#94a3b8;font-family:sans-serif;'><p>Carregando...</p></div>"></iframe>
            </div>
        </div>
    </div>

    {{-- Configurações em cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

        {{-- Card: Cores --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm p-6">
            <h3 class="text-sm font-black text-slate-800 dark:text-white mb-4"><i class="fas fa-palette mr-2 text-blue-500"></i>Cores</h3>
            <div class="space-y-3">
                @foreach([
                    ['invoice_primary_color', 'Cor primária (títulos, bordas)'],
                    ['invoice_secondary_color', 'Cor secundária'],
                    ['invoice_text_color', 'Cor do texto'],
                    ['invoice_bg_color', 'Fundo do cabeçalho'],
                ] as [$key, $label])
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 dark:text-slate-400">{{ $label }}</span>
                    <input type="color" class="invoice-setting w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer p-0.5" name="{{ $key }}" value="{{ $settings[$key] }}">
                </div>
                @endforeach
            </div>
        </div>

        {{-- Card: Logo --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm p-6">
            <h3 class="text-sm font-black text-slate-800 dark:text-white mb-4"><i class="fas fa-image mr-2 text-purple-500"></i>Logo</h3>

            <label class="block text-xs font-bold text-slate-500 mb-2">Posição do logo</label>
            <div class="grid grid-cols-3 gap-2 mb-5">
                @foreach(['left' => 'Esquerda', 'center' => 'Centro', 'right' => 'Direita'] as $val => $lbl)
                <label class="cursor-pointer">
                    <input type="radio" name="invoice_logo_position" value="{{ $val }}" class="invoice-setting sr-only peer" {{ ($settings['invoice_logo_position'] ?? 'left') === $val ? 'checked' : '' }}>
                    <div class="py-2.5 text-center text-xs font-bold rounded-2xl border-2 border-slate-200 dark:border-slate-700 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/30 peer-checked:text-blue-700 dark:peer-checked:text-blue-300 text-slate-500 transition-all">{{ $lbl }}</div>
                </label>
                @endforeach
            </div>

            <label class="block text-xs font-bold text-slate-500 mb-2">Tamanho máximo: <span id="logo-h-val" class="text-blue-600">{{ $settings['invoice_logo_max_height'] ?? 60 }}px</span></label>
            <input type="range" class="invoice-setting w-full accent-blue-600" name="invoice_logo_max_height" min="30" max="120" step="5" value="{{ $settings['invoice_logo_max_height'] ?? 60 }}">
            <div class="flex justify-between text-[10px] text-slate-400 mt-1"><span>30px</span><span>120px</span></div>
        </div>

        {{-- Card: Visibilidade --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm p-6">
            <h3 class="text-sm font-black text-slate-800 dark:text-white mb-4"><i class="fas fa-eye mr-2 text-green-500"></i>Elementos Visíveis</h3>
            <div class="space-y-2">
                @foreach([
                    ['invoice_show_company_address', 'Endereço da empresa'],
                    ['invoice_show_company_phone', 'Telefone'],
                    ['invoice_show_company_email', 'E-mail'],
                    ['invoice_show_due_date', 'Data de vencimento'],
                    ['invoice_show_status_badge', 'Badge de status'],
                    ['invoice_show_notes', 'Observações'],
                    ['invoice_show_footer', 'Rodapé'],
                ] as [$key, $label])
                <label class="flex items-center gap-3 cursor-pointer py-0.5">
                    <input type="checkbox" class="invoice-setting w-4 h-4 rounded text-blue-600 border-slate-300 dark:border-slate-600 focus:ring-blue-500" name="{{ $key }}" {{ ($settings[$key] ?? true) ? 'checked' : '' }}>
                    <span class="text-sm text-slate-600 dark:text-slate-400">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Card: Textos --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm p-6">
            <h3 class="text-sm font-black text-slate-800 dark:text-white mb-4"><i class="fas fa-font mr-2 text-amber-500"></i>Textos e Fonte</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Título da fatura</label>
                    <input type="text" class="invoice-setting w-full px-4 py-2.5 text-sm rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-bold" name="invoice_header_text" value="{{ $settings['invoice_header_text'] ?? 'FATURA' }}" placeholder="FATURA, NOTA FISCAL, RECIBO...">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Texto do rodapé</label>
                    <input type="text" class="invoice-setting w-full px-4 py-2.5 text-sm rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" name="invoice_footer_text" value="{{ $settings['invoice_footer_text'] ?? 'Obrigado pela sua preferência!' }}">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Fonte</label>
                    <select class="invoice-setting w-full px-4 py-2.5 text-sm rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" name="invoice_font_family">
                        @foreach(['DejaVu Sans', 'Helvetica', 'Courier'] as $f)
                        <option {{ ($settings['invoice_font_family'] ?? 'DejaVu Sans') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Card: Dados da Empresa --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm p-6">
            <h3 class="text-sm font-black text-slate-800 dark:text-white mb-4"><i class="fas fa-building mr-2 text-cyan-500"></i>Dados da Empresa</h3>
            <div class="space-y-2.5">
                @foreach([
                    ['company_name', 'Nome da empresa'],
                    ['company_cnpj', 'CNPJ'],
                    ['company_address', 'Endereço'],
                    ['company_city', 'Cidade'],
                    ['company_state', 'UF'],
                    ['company_phone', 'Telefone'],
                    ['company_email', 'E-mail'],
                ] as [$key, $label])
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 mb-0.5">{{ $label }}</label>
                    <input type="text" class="company-field w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" id="{{ $key }}" value="{{ $companyData[$key] ?? '' }}">
                </div>
                @endforeach
            </div>
            <button type="button" id="btn-save-company" class="w-full py-2.5 mt-4 bg-slate-800 dark:bg-slate-700 text-white text-xs font-bold rounded-2xl hover:bg-slate-700 transition-all">
                <i class="fas fa-save mr-1"></i> Salvar Dados da Empresa
            </button>
        </div>

        {{-- Card: Avançado --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm p-6">
            <h3 class="text-sm font-black text-slate-800 dark:text-white mb-4"><i class="fas fa-code mr-2 text-rose-500"></i>CSS Customizado</h3>
            <textarea class="invoice-setting w-full px-4 py-3 text-xs font-mono rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 resize-none" name="invoice_custom_css" rows="8" placeholder="/* Estilos adicionais para o PDF */&#10;.header { ... }&#10;.footer { ... }">{{ $settings['invoice_custom_css'] ?? '' }}</textarea>
            <button type="button" id="btn-reset" class="w-full py-2.5 mt-4 border-2 border-red-200 dark:border-red-800 text-red-500 text-xs font-bold rounded-2xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                <i class="fas fa-undo mr-1"></i> Restaurar Padrões
            </button>
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

    // Slider label
    $('input[name="invoice_logo_max_height"]').on('input', function() {
        $('#logo-h-val').text($(this).val() + 'px');
    });

    // Preview sem refresh (HTML direto via srcdoc)
    function loadPreview() {
        $('#preview-badge').text('Atualizando...').removeClass('bg-green-100 text-green-700').addClass('bg-yellow-100 text-yellow-700');
        $.get(previewUrl + '?t=' + Date.now(), function(html) {
            document.getElementById('preview-frame').srcdoc = html;
            $('#preview-badge').text('Atualizado').removeClass('bg-yellow-100 text-yellow-700').addClass('bg-green-100 text-green-700');
        });
    }

    // Debounce: salva + atualiza preview
    function scheduleUpdate() {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            $.ajax({ url: saveUrl, method: 'POST', data: collectSettings(), headers: { 'X-CSRF-TOKEN': csrfToken }, success: loadPreview });
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

    // Eventos de mudança
    $(document).on('change input', '.invoice-setting', scheduleUpdate);

    // Salvar configurações
    $('#btn-save').on('click', function() {
        var $b = $(this).prop('disabled', true);
        $.ajax({ url: saveUrl, method: 'POST', data: collectSettings(), headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(r) { $b.prop('disabled', false); Swal.fire({ icon: 'success', title: 'Configurações salvas!', timer: 1500, showConfirmButton: false }); },
            error: function() { $b.prop('disabled', false); Swal.fire({ icon: 'error', title: 'Erro ao salvar' }); }
        });
    });

    // Restaurar padrões
    $('#btn-reset').on('click', function() {
        Swal.fire({ title: 'Restaurar padrões?', text: 'Todas as configurações visuais serão revertidas.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Sim, restaurar', cancelButtonText: 'Cancelar' }).then(function(r) {
            if (r.isConfirmed) $.ajax({ url: resetUrl, method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, success: function() { Swal.fire({ icon: 'success', title: 'Restaurado!', timer: 1500, showConfirmButton: false }); setTimeout(function() { location.reload(); }, 1500); } });
        });
    });

    // Salvar dados da empresa
    $('#btn-save-company').on('click', function() {
        var $b = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');
        var data = {};
        $('.company-field').each(function() { data[$(this).attr('id')] = $(this).val(); });
        $.ajax({ url: saveUrl, method: 'POST', data: data, headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() { $b.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Salvar Dados da Empresa'); Swal.fire({ icon: 'success', title: 'Dados salvos!', timer: 1500, showConfirmButton: false }); loadPreview(); },
            error: function() { $b.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Salvar Dados da Empresa'); Swal.fire({ icon: 'error', title: 'Erro' }); }
        });
    });

    // Carregar preview inicial
    loadPreview();
});
</script>
@endpush
