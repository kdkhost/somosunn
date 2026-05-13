@extends('admin.layouts.app')

@section('title', 'Editor de Faturas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-invoice mr-2"></i>Editor Visual de Faturas</h1>
        <div>
            <button type="button" class="btn btn-success btn-sm" id="btn-save">
                <i class="fas fa-save mr-1"></i> Salvar Configurações
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm ml-2" id="btn-refresh-preview">
                <i class="fas fa-sync-alt mr-1"></i> Atualizar Preview
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    {{-- Painel de Configurações --}}
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-header p-0">
                <ul class="nav nav-tabs" id="editor-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab-cores" role="tab">
                            <i class="fas fa-palette"></i> Cores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-logo" role="tab">
                            <i class="fas fa-image"></i> Logo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-layout" role="tab">
                            <i class="fas fa-th-large"></i> Layout
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-dados" role="tab">
                            <i class="fas fa-building"></i> Dados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-avancado" role="tab">
                            <i class="fas fa-code"></i> Avançado
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body tab-content" style="max-height: calc(100vh - 220px); overflow-y: auto;">
                {{-- Aba Cores --}}
                <div class="tab-pane fade show active" id="tab-cores" role="tabpanel">
                    <div class="form-group">
                        <label for="invoice_primary_color">Cor Primária</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color invoice-setting"
                                id="invoice_primary_color" name="invoice_primary_color"
                                value="{{ $settings['invoice_primary_color'] }}" style="height: 38px; padding: 2px;">
                            <div class="input-group-append">
                                <span class="input-group-text" id="label-primary-color">{{ $settings['invoice_primary_color'] }}</span>
                            </div>
                        </div>
                        <small class="text-muted">Títulos, bordas e destaques</small>
                    </div>

                    <div class="form-group">
                        <label for="invoice_secondary_color">Cor Secundária</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color invoice-setting"
                                id="invoice_secondary_color" name="invoice_secondary_color"
                                value="{{ $settings['invoice_secondary_color'] }}" style="height: 38px; padding: 2px;">
                            <div class="input-group-append">
                                <span class="input-group-text" id="label-secondary-color">{{ $settings['invoice_secondary_color'] }}</span>
                            </div>
                        </div>
                        <small class="text-muted">Elementos secundários</small>
                    </div>

                    <div class="form-group">
                        <label for="invoice_text_color">Cor do Texto</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color invoice-setting"
                                id="invoice_text_color" name="invoice_text_color"
                                value="{{ $settings['invoice_text_color'] }}" style="height: 38px; padding: 2px;">
                            <div class="input-group-append">
                                <span class="input-group-text" id="label-text-color">{{ $settings['invoice_text_color'] }}</span>
                            </div>
                        </div>
                        <small class="text-muted">Cor principal do texto</small>
                    </div>

                    <div class="form-group">
                        <label for="invoice_bg_color">Cor de Fundo do Header</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color invoice-setting"
                                id="invoice_bg_color" name="invoice_bg_color"
                                value="{{ $settings['invoice_bg_color'] }}" style="height: 38px; padding: 2px;">
                            <div class="input-group-append">
                                <span class="input-group-text" id="label-bg-color">{{ $settings['invoice_bg_color'] }}</span>
                            </div>
                        </div>
                        <small class="text-muted">Fundo do cabeçalho da fatura</small>
                    </div>
                </div>

                {{-- Aba Logo --}}
                <div class="tab-pane fade" id="tab-logo" role="tabpanel">
                    <div class="form-group">
                        <label>Posição do Logo</label>
                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                            <label class="btn btn-outline-primary flex-fill {{ $settings['invoice_logo_position'] === 'left' ? 'active' : '' }}">
                                <input type="radio" name="invoice_logo_position" value="left" class="invoice-setting"
                                    {{ $settings['invoice_logo_position'] === 'left' ? 'checked' : '' }}>
                                <i class="fas fa-align-left"></i> Esquerda
                            </label>
                            <label class="btn btn-outline-primary flex-fill {{ $settings['invoice_logo_position'] === 'center' ? 'active' : '' }}">
                                <input type="radio" name="invoice_logo_position" value="center" class="invoice-setting"
                                    {{ $settings['invoice_logo_position'] === 'center' ? 'checked' : '' }}>
                                <i class="fas fa-align-center"></i> Centro
                            </label>
                            <label class="btn btn-outline-primary flex-fill {{ $settings['invoice_logo_position'] === 'right' ? 'active' : '' }}">
                                <input type="radio" name="invoice_logo_position" value="right" class="invoice-setting"
                                    {{ $settings['invoice_logo_position'] === 'right' ? 'checked' : '' }}>
                                <i class="fas fa-align-right"></i> Direita
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="invoice_logo_max_height">
                            Tamanho Máximo do Logo: <span id="logo-height-value">{{ $settings['invoice_logo_max_height'] }}px</span>
                        </label>
                        <input type="range" class="custom-range invoice-setting" id="invoice_logo_max_height"
                            name="invoice_logo_max_height" min="30" max="120" step="5"
                            value="{{ $settings['invoice_logo_max_height'] }}">
                        <div class="d-flex justify-content-between text-muted small">
                            <span>30px</span>
                            <span>120px</span>
                        </div>
                    </div>

                    <div class="callout callout-info">
                        <small><i class="fas fa-info-circle mr-1"></i>O logo utilizado é o configurado nas Configurações Globais do sistema (logo da plataforma).</small>
                    </div>
                </div>

                {{-- Aba Layout --}}
                <div class="tab-pane fade" id="tab-layout" role="tabpanel">
                    <h6 class="text-muted text-uppercase mb-3">Visibilidade</h6>

                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input invoice-setting"
                            id="invoice_show_company_address" name="invoice_show_company_address"
                            {{ $settings['invoice_show_company_address'] ? 'checked' : '' }}>
                        <label class="custom-control-label" for="invoice_show_company_address">Mostrar endereço da empresa</label>
                    </div>

                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input invoice-setting"
                            id="invoice_show_company_phone" name="invoice_show_company_phone"
                            {{ $settings['invoice_show_company_phone'] ? 'checked' : '' }}>
                        <label class="custom-control-label" for="invoice_show_company_phone">Mostrar telefone</label>
                    </div>

                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input invoice-setting"
                            id="invoice_show_company_email" name="invoice_show_company_email"
                            {{ $settings['invoice_show_company_email'] ? 'checked' : '' }}>
                        <label class="custom-control-label" for="invoice_show_company_email">Mostrar e-mail</label>
                    </div>

                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input invoice-setting"
                            id="invoice_show_due_date" name="invoice_show_due_date"
                            {{ $settings['invoice_show_due_date'] ? 'checked' : '' }}>
                        <label class="custom-control-label" for="invoice_show_due_date">Mostrar data de vencimento</label>
                    </div>

                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input invoice-setting"
                            id="invoice_show_status_badge" name="invoice_show_status_badge"
                            {{ $settings['invoice_show_status_badge'] ? 'checked' : '' }}>
                        <label class="custom-control-label" for="invoice_show_status_badge">Mostrar badge de status</label>
                    </div>

                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input invoice-setting"
                            id="invoice_show_notes" name="invoice_show_notes"
                            {{ $settings['invoice_show_notes'] ? 'checked' : '' }}>
                        <label class="custom-control-label" for="invoice_show_notes">Mostrar observações</label>
                    </div>

                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input invoice-setting"
                            id="invoice_show_footer" name="invoice_show_footer"
                            {{ $settings['invoice_show_footer'] ? 'checked' : '' }}>
                        <label class="custom-control-label" for="invoice_show_footer">Mostrar rodapé</label>
                    </div>

                    <hr>
                    <h6 class="text-muted text-uppercase mb-3">Textos</h6>

                    <div class="form-group">
                        <label for="invoice_header_text">Texto do Cabeçalho</label>
                        <input type="text" class="form-control invoice-setting" id="invoice_header_text"
                            name="invoice_header_text" value="{{ $settings['invoice_header_text'] }}"
                            placeholder="FATURA, NOTA FISCAL, RECIBO...">
                        <small class="text-muted">Texto exibido acima do número da fatura</small>
                    </div>

                    <div class="form-group">
                        <label for="invoice_footer_text">Texto do Rodapé</label>
                        <textarea class="form-control invoice-setting" id="invoice_footer_text"
                            name="invoice_footer_text" rows="3"
                            placeholder="Obrigado pela sua preferência!">{{ $settings['invoice_footer_text'] }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="invoice_font_family">Fonte</label>
                        <select class="form-control invoice-setting" id="invoice_font_family" name="invoice_font_family">
                            <option value="DejaVu Sans" {{ $settings['invoice_font_family'] === 'DejaVu Sans' ? 'selected' : '' }}>DejaVu Sans</option>
                            <option value="Helvetica" {{ $settings['invoice_font_family'] === 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                            <option value="Courier" {{ $settings['invoice_font_family'] === 'Courier' ? 'selected' : '' }}>Courier</option>
                        </select>
                    </div>
                </div>

                {{-- Aba Dados --}}
                <div class="tab-pane fade" id="tab-dados" role="tabpanel">
                    <div class="callout callout-warning">
                        <small><i class="fas fa-exclamation-triangle mr-1"></i>Estes dados são compartilhados com outras áreas do sistema. Alterações aqui afetam todo o sistema.</small>
                    </div>

                    <div class="form-group">
                        <label for="company_name">Nome da Empresa</label>
                        <input type="text" class="form-control company-field" id="company_name"
                            value="{{ $companyData['company_name'] }}">
                    </div>

                    <div class="form-group">
                        <label for="company_cnpj">CNPJ</label>
                        <input type="text" class="form-control company-field" id="company_cnpj"
                            value="{{ $companyData['company_cnpj'] }}">
                    </div>

                    <div class="form-group">
                        <label for="company_address">Endereço</label>
                        <input type="text" class="form-control company-field" id="company_address"
                            value="{{ $companyData['company_address'] }}">
                    </div>

                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label for="company_number">Número</label>
                                <input type="text" class="form-control company-field" id="company_number"
                                    value="{{ $companyData['company_number'] }}">
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="form-group">
                                <label for="company_district">Bairro</label>
                                <input type="text" class="form-control company-field" id="company_district"
                                    value="{{ $companyData['company_district'] }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="company_city">Cidade</label>
                                <input type="text" class="form-control company-field" id="company_city"
                                    value="{{ $companyData['company_city'] }}">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="company_state">UF</label>
                                <input type="text" class="form-control company-field" id="company_state"
                                    value="{{ $companyData['company_state'] }}" maxlength="2">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="company_zip">CEP</label>
                                <input type="text" class="form-control company-field" id="company_zip"
                                    value="{{ $companyData['company_zip'] }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="company_phone">Telefone</label>
                        <input type="text" class="form-control company-field" id="company_phone"
                            value="{{ $companyData['company_phone'] }}">
                    </div>

                    <div class="form-group">
                        <label for="company_email">E-mail</label>
                        <input type="email" class="form-control company-field" id="company_email"
                            value="{{ $companyData['company_email'] }}">
                    </div>

                    <button type="button" class="btn btn-primary btn-block" id="btn-save-company">
                        <i class="fas fa-save mr-1"></i> Salvar Dados da Empresa
                    </button>
                </div>

                {{-- Aba Avançado --}}
                <div class="tab-pane fade" id="tab-avancado" role="tabpanel">
                    <div class="form-group">
                        <label for="invoice_custom_css">CSS Customizado</label>
                        <textarea class="form-control invoice-setting" id="invoice_custom_css"
                            name="invoice_custom_css" rows="12"
                            placeholder="/* Adicione CSS personalizado aqui */&#10;.header { ... }&#10;.footer { ... }"
                            style="font-family: monospace; font-size: 12px;">{{ $settings['invoice_custom_css'] }}</textarea>
                        <small class="text-muted">CSS adicional aplicado diretamente no PDF da fatura</small>
                    </div>

                    <hr>

                    <button type="button" class="btn btn-outline-danger btn-block" id="btn-reset-defaults">
                        <i class="fas fa-undo mr-1"></i> Restaurar Padrões
                    </button>
                    <small class="text-muted d-block text-center mt-2">Isso irá reverter todas as configurações visuais para os valores originais.</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview --}}
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Preview da Fatura</h3>
                <div class="card-tools">
                    <span class="badge badge-info" id="preview-status">Atualizado</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div style="position: relative; width: 100%; height: calc(100vh - 220px); background: #e5e7eb;">
                    <iframe id="preview-iframe"
                        src="{{ route('admin.invoices.editor.preview') }}"
                        style="width: 100%; height: 100%; border: none; background: white;"
                        title="Preview da Fatura"></iframe>
                    <div id="preview-loading" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 10; justify-content: center; align-items: center;">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                            <p class="mt-2 text-muted">Gerando preview...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(function() {
    var previewTimer = null;
    var saveUrl = @json(route('admin.invoices.editor.save'));
    var resetUrl = @json(route('admin.invoices.editor.reset'));
    var previewUrl = @json(route('admin.invoices.editor.preview'));
    var settingsUpdateUrl = @json(route('admin.settings.update'));
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Atualizar label dos color pickers
    $('input[type="color"]').on('input', function() {
        var id = $(this).attr('id');
        var labelId = id.replace('invoice_', 'label-').replace('_color', '-color');
        $('#' + labelId).text($(this).val());
    });

    // Atualizar label do slider
    $('#invoice_logo_max_height').on('input', function() {
        $('#logo-height-value').text($(this).val() + 'px');
    });

    // Debounce para preview
    function schedulePreview() {
        $('#preview-status').text('Pendente...').removeClass('badge-info').addClass('badge-warning');
        if (previewTimer) clearTimeout(previewTimer);
        previewTimer = setTimeout(function() {
            refreshPreview();
        }, 500);
    }

    // Atualizar preview
    function refreshPreview() {
        $('#preview-loading').css('display', 'flex');
        $('#preview-status').text('Carregando...').removeClass('badge-warning badge-info').addClass('badge-secondary');

        // Primeiro salvar temporariamente, depois recarregar iframe
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
                    $('#preview-loading').hide();
                    $('#preview-status').text('Atualizado').removeClass('badge-warning badge-secondary').addClass('badge-info');
                };
            },
            error: function() {
                $('#preview-loading').hide();
                $('#preview-status').text('Erro').removeClass('badge-warning badge-secondary badge-info').addClass('badge-danger');
            }
        });
    }

    // Coletar todas as configurações
    function collectSettings() {
        var data = {};
        // Campos de texto, cor, select, textarea
        $('.invoice-setting').each(function() {
            var $el = $(this);
            var name = $el.attr('name');
            if (!name) return;

            if ($el.is(':checkbox')) {
                data[name] = $el.is(':checked') ? '1' : '0';
            } else if ($el.is(':radio')) {
                if ($el.is(':checked')) {
                    data[name] = $el.val();
                }
            } else {
                data[name] = $el.val();
            }
        });
        return data;
    }

    // Eventos de mudança
    $(document).on('change input', '.invoice-setting', function() {
        schedulePreview();
    });

    // Botão Atualizar Preview
    $('#btn-refresh-preview').on('click', function() {
        refreshPreview();
    });

    // Botão Salvar
    $('#btn-save').on('click', function() {
        var data = collectSettings();
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');

        $.ajax({
            url: saveUrl,
            method: 'POST',
            data: data,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Salvar Configurações');
                Swal.fire({
                    icon: 'success',
                    title: 'Salvo!',
                    text: res.message || 'Configurações salvas com sucesso!',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Salvar Configurações');
                var msg = 'Erro ao salvar configurações.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Erro', text: msg });
            }
        });
    });

    // Botão Restaurar Padrões
    $('#btn-reset-defaults').on('click', function() {
        Swal.fire({
            title: 'Restaurar Padrões?',
            text: 'Todas as configurações visuais da fatura serão revertidas para os valores originais.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, restaurar!',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: resetUrl,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Restaurado!',
                            text: res.message || 'Configurações restauradas!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        // Recarregar a página para refletir os novos valores
                        setTimeout(function() { location.reload(); }, 1500);
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro ao restaurar configurações.' });
                    }
                });
            }
        });
    });

    // Botão Salvar Dados da Empresa
    $('#btn-save-company').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');

        var companyData = {};
        $('.company-field').each(function() {
            companyData[$(this).attr('id')] = $(this).val();
        });

        $.ajax({
            url: settingsUpdateUrl,
            method: 'POST',
            data: companyData,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Salvar Dados da Empresa');
                Swal.fire({
                    icon: 'success',
                    title: 'Salvo!',
                    text: 'Dados da empresa atualizados com sucesso!',
                    timer: 2000,
                    showConfirmButton: false
                });
                refreshPreview();
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Salvar Dados da Empresa');
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro ao salvar dados da empresa.' });
            }
        });
    });
});
</script>
@endsection
