@extends('admin.layouts.app')

@section('title', $rule ? 'WAF - Editar Regra' : 'WAF - Nova Regra')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-gavel text-primary"></i>
                    {{ $rule ? 'Editar Regra: ' . $rule->name : 'Nova Regra WAF' }}
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.waf.rules.index') }}" class="btn btn-sm btn-default">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ $rule ? route('admin.waf.rules.update', $rule->id) : route('admin.waf.rules.store') }}">
            @csrf
            @if($rule)
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dados da Regra</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Nome <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control"
                                       value="{{ old('name', $rule->name ?? '') }}" required maxlength="255">
                            </div>

                            <div class="form-group">
                                <label for="description">Descricao</label>
                                <textarea name="description" id="description" class="form-control" rows="2" maxlength="1000">{{ old('description', $rule->description ?? '') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="attack_pattern">Attack Pattern <span class="text-danger">*</span></label>
                                        <select name="attack_pattern" id="attack_pattern" class="form-control" required>
                                            <option value="">Selecione...</option>
                                            @foreach(['sqli','xss','rfi','lfi','path_traversal','command_injection','ssrf','csrf','brute_force','rate_limit','bot_detection','custom'] as $p)
                                                <option value="{{ $p }}" {{ old('attack_pattern', $rule->attack_pattern ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="matcher_type">Matcher Type <span class="text-danger">*</span></label>
                                        <select name="matcher_type" id="matcher_type" class="form-control" required>
                                            <option value="">Selecione...</option>
                                            @foreach(['regex','list','numeric','function'] as $mt)
                                                <option value="{{ $mt }}" {{ old('matcher_type', $rule->matcher_type ?? '') === $mt ? 'selected' : '' }}>{{ $mt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="matcher_payload">Matcher Payload (JSON) <span class="text-danger">*</span></label>
                                <textarea name="matcher_payload" id="matcher_payload" class="form-control" rows="5" required
                                          placeholder='{"pattern": "(?i)(union\\s+select|insert\\s+into)", "flags": "i"}'>{{ old('matcher_payload', $rule ? json_encode($rule->matcher_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                                <small class="text-muted">Objeto JSON com a configuracao do matcher.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="score">Score (0-100) <span class="text-danger">*</span></label>
                                        <input type="range" name="score" id="score" class="custom-range" min="0" max="100"
                                               value="{{ old('score', $rule->score ?? 50) }}" oninput="document.getElementById('score-val').textContent=this.value">
                                        <span class="badge badge-info" id="score-val">{{ old('score', $rule->score ?? 50) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="action">Acao <span class="text-danger">*</span></label>
                                        <select name="action" id="action" class="form-control" required>
                                            @foreach(['monitor' => 'Monitor', 'challenge' => 'Challenge', 'block' => 'Block'] as $val => $label)
                                                <option value="{{ $val }}" {{ old('action', $rule->action ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="severity">Severidade <span class="text-danger">*</span></label>
                                        <select name="severity" id="severity" class="form-control" required>
                                            @foreach(['info' => 'Info', 'low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $val => $label)
                                                <option value="{{ $val }}" {{ old('severity', $rule->severity ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Regra Ativa</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ $rule ? 'Atualizar' : 'Criar' }} Regra
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Painel de Teste --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-flask"></i> Testar Regra</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Payload de Teste</label>
                                <textarea id="test-sample" class="form-control" rows="3" placeholder="Insira um payload para testar..."></textarea>
                            </div>
                            <button type="button" id="btn-test-rule" class="btn btn-sm btn-outline-primary btn-block">
                                <i class="fas fa-play"></i> Testar
                            </button>
                            <div id="test-result" class="mt-3" style="display:none;">
                                <div class="alert" id="test-alert"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function() {
    $('#btn-test-rule').on('click', function() {
        var matcherType = $('#matcher_type').val();
        var matcherPayload = $('#matcher_payload').val();
        var sample = $('#test-sample').val();

        if (!matcherType || !matcherPayload || !sample) {
            alert('Preencha matcher_type, matcher_payload e o payload de teste.');
            return;
        }

        $.post('{{ route("admin.waf.rules.test") }}', {
            _token: '{{ csrf_token() }}',
            matcher_type: matcherType,
            matcher_payload: matcherPayload,
            sample: sample
        }, function(res) {
            var $result = $('#test-result').show();
            var $alert = $('#test-alert');
            if (res.matched) {
                $alert.attr('class', 'alert alert-danger').html('<i class="fas fa-exclamation-circle"></i> <strong>MATCH!</strong> ' + (res.details || ''));
            } else {
                $alert.attr('class', 'alert alert-success').html('<i class="fas fa-check-circle"></i> <strong>Sem match.</strong> ' + (res.details || ''));
            }
        }).fail(function(xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao testar.';
            $('#test-result').show();
            $('#test-alert').attr('class', 'alert alert-warning').text(msg);
        });
    });
});
</script>
@endpush
