@extends('admin.layouts.app')

@section('title', 'WAF - Configuracoes')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-cog text-primary"></i> WAF - Configuracoes</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if(!$hasTable)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Tabela <code>waf_settings</code> nao encontrada. Execute <code>php artisan migrate</code>.
            </div>
        @else
            <form method="POST" action="{{ route('admin.waf.settings.update') }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-sliders-h"></i> Limiares de Score</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Limiar Monitor (score minimo para monitorar)</label>
                                    <input type="number" name="threshold_monitor" class="form-control" min="0" max="100"
                                           value="{{ old('threshold_monitor', $settings['threshold_monitor'] ?? 20) }}">
                                    <small class="text-muted">Requisicoes com score >= este valor serao monitoradas.</small>
                                </div>
                                <div class="form-group">
                                    <label>Limiar Challenge (score minimo para desafiar)</label>
                                    <input type="number" name="threshold_challenge" class="form-control" min="0" max="100"
                                           value="{{ old('threshold_challenge', $settings['threshold_challenge'] ?? 50) }}">
                                    <small class="text-muted">Requisicoes com score >= este valor receberao challenge.</small>
                                </div>
                                <div class="form-group">
                                    <label>Limiar Block (score minimo para bloquear)</label>
                                    <input type="number" name="threshold_block" class="form-control" min="0" max="100"
                                           value="{{ old('threshold_block', $settings['threshold_block'] ?? 80) }}">
                                    <small class="text-muted">Requisicoes com score >= este valor serao bloqueadas.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-shield-alt"></i> Modo e Politicas</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Modo de Operacao</label>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="mode-detection" name="mode" value="detection-only"
                                               class="custom-control-input" {{ ($settings['mode'] ?? 'detection-only') === 'detection-only' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="mode-detection">
                                            <strong>Detection-Only</strong> — Apenas registra eventos, nao bloqueia
                                        </label>
                                    </div>
                                    <div class="custom-control custom-radio mt-2">
                                        <input type="radio" id="mode-enforce" name="mode" value="enforce"
                                               class="custom-control-input" {{ ($settings['mode'] ?? '') === 'enforce' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="mode-enforce">
                                            <strong>Enforce</strong> — Aplica bloqueios e challenges ativamente
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <label>Fail Policy (quando o WAF falha internamente)</label>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="fail-allow" name="fail_policy" value="allow"
                                               class="custom-control-input" {{ ($settings['fail_policy'] ?? 'allow') === 'allow' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="fail-allow">
                                            <strong>Allow</strong> — Permite a requisicao (fail-open)
                                        </label>
                                    </div>
                                    <div class="custom-control custom-radio mt-2">
                                        <input type="radio" id="fail-block" name="fail_policy" value="block"
                                               class="custom-control-input" {{ ($settings['fail_policy'] ?? '') === 'block' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="fail-block">
                                            <strong>Block</strong> — Bloqueia a requisicao (fail-closed)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-route"></i> Rotas Isentas</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Rotas que nao passam pelo WAF (uma por linha)</label>
                                    <textarea name="exempt_routes" class="form-control" rows="5"
                                              placeholder="/api/health&#10;/webhook/*">{{ old('exempt_routes', is_array($settings['exempt_routes'] ?? null) ? implode("\n", $settings['exempt_routes']) : '') }}</textarea>
                                    <small class="text-muted">Suporta wildcards (*). Ex: /api/health, /webhook/*</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Configuracoes
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</section>
@endsection
