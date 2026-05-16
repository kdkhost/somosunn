{{--
============================================================
PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
============================================================
@autor marcelo-brad rj
@contato Tel: 21 981325441 - Email: contato@kdkhost.com.br
============================================================
View AdminLTE para configuracao de multiplos provedores S3
(IDrive e2, Wasabi, AWS S3) com selecao de provedor ativo
e teste de conexao por provedor.

Spec: .kiro/specs/multi-provider-s3-storage (task 5.1)
Requirements: 3.1-3.5, 9.1-9.4
--}}
@extends('admin.layouts.app')

@section('title', 'Provedores S3 - SOMOS UNN')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Armazenamento - Multi Provedor S3</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/admin/settings') }}">Configuracoes</a></li>
                    <li class="breadcrumb-item active">Provedores S3</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-check"></i> {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-exclamation-triangle"></i> Verifique os erros abaixo.
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Card: provedor ativo --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-toggle-on"></i> Provedor Ativo</h3>
            </div>
            <div class="card-body">
                <p class="mb-3 text-muted">
                    Apenas um provedor pode estar ativo por vez. Ao alternar, o sistema valida
                    a conexao antes de aplicar. Caso o teste falhe, o provedor anterior e mantido.
                </p>

                <form action="{{ url('/admin/settings/storage-providers/active') }}" method="POST" id="form-switch-active">
                    @csrf
                    <div class="form-row align-items-end">
                        <div class="col-md-5">
                            <label for="active-provider-select">Selecionar provedor ativo</label>
                            <select name="provider" id="active-provider-select" class="form-control">
                                @foreach ($displayNames as $key => $name)
                                    <option value="{{ $key }}" {{ $activeProvider === $key ? 'selected' : '' }}>
                                        {{ $name }}
                                        @if ($activeProvider === $key) (atual) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" name="skip_test" id="skip-test" value="1" class="custom-control-input">
                                <label for="skip-test" class="custom-control-label small">
                                    Pular teste de conexao
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="submit" class="btn btn-primary" id="btn-switch-active">
                                <i class="fas fa-power-off"></i> Ativar Provedor Selecionado
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-3">
                    <strong>Atualmente ativo:</strong>
                    <span class="badge badge-success p-2">
                        <i class="fas fa-check-circle"></i>
                        {{ $displayNames[$activeProvider] ?? $activeProvider }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card: configuracao por provedor --}}
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills" role="tablist">
                    @foreach ($providers as $providerKey => $provider)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                               data-toggle="tab"
                               href="#tab-{{ $providerKey }}"
                               role="tab">
                                <i class="fas fa-cloud"></i> {{ $provider['name'] }}
                                @if ($provider['key'] === $activeProvider)
                                    <span class="badge badge-success ml-1">ATIVO</span>
                                @elseif ($provider['configured'])
                                    <span class="badge badge-secondary ml-1">configurado</span>
                                @else
                                    <span class="badge badge-warning ml-1">incompleto</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    @foreach ($providers as $providerKey => $provider)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $providerKey }}" role="tabpanel">
                            @include('admin.settings.partials.storage-provider-form', [
                                'provider' => $provider,
                                'isActive' => $provider['key'] === $activeProvider,
                            ])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</section>

{{-- Modal: resultado do teste de conexao --}}
<div class="modal fade" id="modal-test-result" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-vial"></i> Resultado do Teste de Conexao</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="test-result-loading" class="text-center py-4" style="display:none;">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 mb-0 text-muted">Testando conexao com o provedor... pode levar ate 30 segundos.</p>
                </div>
                <div id="test-result-content" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    'use strict';

    // ============================================================
    // Toggle de visibilidade do Secret Key
    // ============================================================
    $('.btn-toggle-secret').on('click', function () {
        var $btn = $(this);
        var $input = $('#' + $btn.data('target'));
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $btn.html('<i class="fas fa-eye-slash"></i>');
        } else {
            $input.attr('type', 'password');
            $btn.html('<i class="fas fa-eye"></i>');
        }
    });

    // ============================================================
    // Confirmacao SweetAlert2 antes de trocar provedor ativo
    // ============================================================
    $('#form-switch-active').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        var providerKey = $('#active-provider-select').val();
        var providerName = $('#active-provider-select option:selected').text().trim();
        var skipTest = $('#skip-test').is(':checked');

        Swal.fire({
            title: 'Ativar provedor?',
            html: 'O provedor <strong>' + providerName + '</strong> sera ativado.<br>' +
                (skipTest
                    ? '<span class="text-warning">Teste de conexao sera <b>pulado</b>.</span>'
                    : 'Uma validacao de conexao sera executada antes da ativacao.'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ativar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#1F5EDB',
        }).then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // ============================================================
    // Teste de conexao por provedor (AJAX)
    // ============================================================
    $('.btn-test-provider').on('click', function () {
        var $btn = $(this);
        var providerKey = $btn.data('provider');
        var providerName = $btn.data('name') || providerKey;

        $('#modal-test-result').modal('show');
        $('#test-result-loading').show();
        $('#test-result-content').hide().empty();

        var url = "{{ url('/admin/settings/storage-providers') }}" + '/' + providerKey + '/test';

        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            timeout: 35000,
            success: function (data) {
                renderTestResult(providerName, data);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.error_message)
                    ? xhr.responseJSON.error_message
                    : 'Erro ao executar teste (HTTP ' + xhr.status + ').';
                renderTestResult(providerName, {
                    status: 'failed',
                    error_message: msg,
                    steps: [],
                    total_latency_ms: 0,
                });
            },
        });
    });

    function renderTestResult(providerName, data) {
        $('#test-result-loading').hide();

        var statusClass = data.status === 'success' ? 'success' : (data.status === 'timeout' ? 'warning' : 'danger');
        var statusIcon = data.status === 'success' ? 'check-circle' : (data.status === 'timeout' ? 'clock' : 'times-circle');
        var statusLabel = data.status === 'success' ? 'Sucesso' : (data.status === 'timeout' ? 'Timeout' : 'Falha');

        var html = '<div class="alert alert-' + statusClass + '">'
                 +    '<i class="fas fa-' + statusIcon + '"></i> '
                 +    '<strong>' + escapeHtml(providerName) + '</strong>: ' + statusLabel
                 +    (data.total_latency_ms ? ' <small class="text-muted">(' + data.total_latency_ms + ' ms)</small>' : '')
                 + '</div>';

        if (data.error_message) {
            html += '<div class="alert alert-light border"><strong>Detalhe:</strong> ' + escapeHtml(data.error_message) + '</div>';
        }

        if (data.steps && data.steps.length > 0) {
            html += '<table class="table table-sm table-striped"><thead><tr>'
                  +    '<th style="width:40px"></th>'
                  +    '<th>Etapa</th>'
                  +    '<th>Detalhe</th>'
                  +    '<th class="text-right" style="width:90px">Latencia</th>'
                  +  '</tr></thead><tbody>';

            $.each(data.steps, function (i, step) {
                var ok = step.status === 'success';
                html += '<tr>'
                     +    '<td class="text-center">'
                     +       '<i class="fas fa-' + (ok ? 'check text-success' : 'times text-danger') + '"></i>'
                     +    '</td>'
                     +    '<td><code>' + escapeHtml(step.name) + '</code></td>'
                     +    '<td>' + escapeHtml(step.detail || '') + '</td>'
                     +    '<td class="text-right text-muted">' + (step.latency_ms || 0) + ' ms</td>'
                     +  '</tr>';
            });

            html += '</tbody></table>';
        }

        $('#test-result-content').html(html).show();
    }

    function escapeHtml(value) {
        return $('<div/>').text(String(value == null ? '' : value)).html();
    }
});
</script>
@endpush
