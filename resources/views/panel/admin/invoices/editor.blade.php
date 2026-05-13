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
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 transition-colors">Personalize o visual das faturas emitidas pelo sistema.</p>
        </div>
        <button type="button" id="btn-save" class="inline-flex items-center gap-2 px-6 py-3 text-white text-sm font-bold rounded-2xl transition-all hover:scale-[1.02] active:scale-[0.98]" style="background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%);">
            <i class="fas fa-save"></i> Salvar Configurações
        </button>
    </div>

    {{-- Preview em tela cheia com painel flutuante --}}
    <div class="relative bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden" style="min-height: 75vh;">

        {{-- Barra superior do preview --}}
        <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-slate-600 dark:text-slate-400"><i class="fas fa-file-pdf mr-1"></i> Preview da Fatura</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700" id="preview-badge">Atualizado</span>
            </div>
            <button type="button" id="btn-toggle-panel" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-600 transition-all">
                <i class="fas fa-sliders-h"></i> Configurações
            </button>
        </div>

        <div class="flex">
            {{-- Preview (área principal) --}}
            <div class="flex-1 bg-slate-100 dark:bg-slate-800 p-6 flex items-start justify-center overflow-auto" style="min-height: 70vh;">
                <div class="bg-white shadow-2xl rounded-lg overflow-hidden w-full" style="max-width: 650px; aspect-ratio: 210/297;">
                    <iframe id="preview-frame" class="w-full h-full border-0" srcdoc="<div style='padding:60px;text-align:center;color:#94a3b8;font-family:sans-serif;'><p>Carregando preview...</p></div>"></iframe>
                </div>
            </div>

            {{-- Painel lateral de configurações (toggle) --}}
            <div id="config-panel" class="w-80 border-l border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-y-auto transition-all" style="max-height: 75vh;">
                <div class="p-5 space-y-5">

                    {{-- Seção: Cores --}}
                    <div>
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3">Cores</h4>
                        <div class="space-y-2">
                            @foreach([
                                ['invoice_primary_color', 'Primária'],
                                ['invoice_secondary_color', 'Secundária'],
                                ['invoice_text_color', 'Texto'],
                                ['invoice_bg_color', 'Fundo'],
                            ] as [$key, $label])
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600 dark:text-slate-400">{{ $label }}</span>
                                <input type="color" class="invoice-setting w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer p-0" name="{{ $key }}" value="{{ $settings[$key] }}">
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <hr class="border-slate-100 dark:border-slate-800">

                    {{-- Seção: Logo --}}
                    <div>
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3">Logo</h4>
                        <div class="flex gap-1 mb-3">
                            @foreach(['left' => 'Esq.', 'center' => 'Centro', 'right' => 'Dir.'] as $val => $lbl)
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="invoice_logo_position" value="{{ $val }}" class="invoice-setting sr-only peer" {{ ($settings['invoice_logo_position'] ?? 'left') === $val ? 'checked' : '' }}>
                                <div class="py-2 text-center text-[11px] font-bold rounded-xl border border-slate-200 dark:border-slate-700 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/30 peer-checked:text-blue-700 transition-all">{{ $lbl }}</div>
                            </label>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-500">Tamanho:</span>
                            <input type="range" class="invoice-setting flex-1" name="invoice_logo_max_height" min="30" max="120" step="5" value="{{ $settings['invoice_logo_max_height'] ?? 60 }}">
                            <span class="text-xs font-bold text-slate-600 w-10 text-right" id="logo-h-val">{{ $settings['invoice_logo_max_height'] ?? 60 }}px</span>
                        </div>
                    </div>

                    <hr class="border-slate-100 dark:border-slate-800">

                    {{-- Seção: Visibilidade --}}
                    <div>
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3">Exibir</h4>
                        <div class="space-y-1.5">
                            @foreach([
                                ['invoice_show_company_address', 'Endereço'],
                                ['invoice_show_company_phone', 'Telefone'],
                                ['invoice_show_company_email', 'E-mail'],
                                ['invoice_show_due_date', 'Vencimento'],
                                ['invoice_show_status_badge', 'Status'],
                                ['invoice_show_notes', 'Observações'],
                                ['invoice_show_footer', 'Rodapé'],
                            ] as [$key, $label])
                            <label class="flex items-center gap-2 cursor-pointer py-0.5">
                                <input type="checkbox" class="invoice-setting w-4 h-4 rounded text-blue-600 border-slate-300 dark:border-slate-600" name="{{ $key }}" {{ ($settings[$key] ?? true) ? 'checked' : '' }}>
                                <span class="text-sm text-slate-600 dark:text-slate-400">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <hr class="border-slate-100 dark:border-slate-800">

                    {{-- Seção: Textos --}}
                    <div>
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3">Textos</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Título da fatura</label>
                                <input type="text" class="invoice-setting w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" name="invoice_header_text" value="{{ $settings['invoice_header_text'] ?? 'FATURA' }}">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Texto do rodapé</label>
                                <input type="text" class="invoice-setting w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" name="invoice_footer_text" value="{{ $settings['invoice_footer_text'] ?? 'Obrigado pela sua preferência!' }}">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Fonte</label>
                                <select class="invoice-setting w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" name="invoice_font_family">
                                    @foreach(['DejaVu Sans', 'Helvetica', 'Courier'] as $f)
                                    <option {{ ($settings['invoice_font_family'] ?? 'DejaVu Sans') === $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100 dark:border-slate-800">

                    {{-- Restaurar --}}
                    <button type="button" id="btn-reset" class="w-full py-2 text-xs font-bold text-red-500 hover:text-red-700 transition-colors">
                        <i class="fas fa-undo mr-1"></i> Restaurar padrões
                    </button>
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

    // Toggle painel
    $('#btn-toggle-panel').on('click', function() {
        $('#config-panel').toggleClass('hidden');
    });

    // Slider
    $('input[name="invoice_logo_max_height"]').on('input', function() {
        $('#logo-h-val').text($(this).val() + 'px');
    });

    // Preview sem refresh
    function loadPreview() {
        $('#preview-badge').text('...').removeClass('bg-green-100 text-green-700').addClass('bg-yellow-100 text-yellow-700');
        $.get(previewUrl + '?t=' + Date.now(), function(html) {
            document.getElementById('preview-frame').srcdoc = html;
            $('#preview-badge').text('Atualizado').removeClass('bg-yellow-100 text-yellow-700').addClass('bg-green-100 text-green-700');
        });
    }

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

    $(document).on('change input', '.invoice-setting', scheduleUpdate);

    $('#btn-save').on('click', function() {
        var $b = $(this).prop('disabled', true);
        $.ajax({ url: saveUrl, method: 'POST', data: collectSettings(), headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(r) { $b.prop('disabled', false); Swal.fire({ icon: 'success', title: 'Salvo!', timer: 1500, showConfirmButton: false }); },
            error: function() { $b.prop('disabled', false); Swal.fire({ icon: 'error', title: 'Erro' }); }
        });
    });

    $('#btn-reset').on('click', function() {
        Swal.fire({ title: 'Restaurar padrões?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Sim', cancelButtonText: 'Cancelar' }).then(function(r) {
            if (r.isConfirmed) $.ajax({ url: resetUrl, method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, success: function() { location.reload(); } });
        });
    });

    loadPreview();
});
</script>
@endpush
