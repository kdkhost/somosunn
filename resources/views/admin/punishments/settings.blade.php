@extends('admin.layouts.app')

@section('title', 'Configuracoes de Punicao')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-cog text-secondary mr-2"></i>Configuracoes de Punicao</h1>
        <a href="{{ route('admin.punishments.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Voltar para Lista
        </a>
    </div>
@endsection

@section('content')
<form id="punishment-settings-form" method="POST" action="{{ route('admin.punishments.settings.update') }}">
    @csrf
    @method('PUT')

    <div class="row">
        {{-- Coluna principal: formulario --}}
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Parametros de Punicao Automatica</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="punishment_block_days">Dias de bloqueio</label>
                                <input type="number" class="form-control" id="punishment_block_days"
                                       name="punishment_block_days"
                                       value="{{ $settings['punishment_block_days'] }}"
                                       min="1" max="365" required>
                                <small class="text-muted">Quantidade de dias que o usuario ficara bloqueado ao receber punicao automatica.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="punishment_events_suspended">Eventos suspensos</label>
                                <input type="number" class="form-control" id="punishment_events_suspended"
                                       name="punishment_events_suspended"
                                       value="{{ $settings['punishment_events_suspended'] }}"
                                       min="0" max="100" required>
                                <small class="text-muted">Quantidade de eventos que o usuario nao podera participar.</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="punishment_auto_enabled"
                                       name="punishment_auto_enabled" value="1"
                                       {{ $settings['punishment_auto_enabled'] === '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="punishment_auto_enabled">
                                    <strong>Punicao automatica habilitada</strong>
                                </label>
                                <br><small class="text-muted">Quando ativado, o sistema aplica punicao automaticamente ao detectar resgates nao entregues no prazo.</small>
                            </div>

                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="punishment_notify_user"
                                       name="punishment_notify_user" value="1"
                                       {{ $settings['punishment_notify_user'] === '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="punishment_notify_user">
                                    <strong>Notificar usuario por email</strong>
                                </label>
                                <br><small class="text-muted">Envia um email ao usuario informando sobre a punicao aplicada.</small>
                            </div>

                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="punishment_accumulate"
                                       name="punishment_accumulate" value="1"
                                       {{ $settings['punishment_accumulate'] === '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="punishment_accumulate">
                                    <strong>Acumular punicoes</strong>
                                </label>
                                <br><small class="text-muted">Se o usuario ja estiver punido, a nova punicao sera somada a existente (bloqueio e eventos).</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Salvar Configuracoes
                    </button>
                </div>
            </div>
        </div>

        {{-- Coluna lateral: cards informativos --}}
        <div class="col-lg-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Como funciona</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Punicao automatica:</strong> Quando um vendedor nao entrega um resgate no prazo configurado, o sistema automaticamente:</p>
                    <ul class="pl-3 mb-0">
                        <li>Bloqueia o acesso do vendedor pelo numero de dias configurado</li>
                        <li>Suspende a participacao em eventos</li>
                        <li>Cancela o resgate e restaura os pontos do comprador</li>
                    </ul>
                </div>
            </div>

            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i>Acumulacao</h3>
                </div>
                <div class="card-body">
                    <p class="mb-0">Quando a opcao <strong>"Acumular punicoes"</strong> esta ativa, se um usuario ja possui um bloqueio vigente e recebe uma nova punicao, o novo periodo sera somado ao tempo restante. O mesmo vale para a suspensao de eventos.</p>
                </div>
            </div>

            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bell mr-1"></i>Notificacoes</h3>
                </div>
                <div class="card-body">
                    <p class="mb-0">Quando habilitado, o usuario recebera um email informando o motivo da punicao, a duracao do bloqueio e quantos eventos esta suspenso.</p>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js')
<script>
$(function() {
    @if(session('success'))
        Swal.fire('Sucesso', '{{ session('success') }}', 'success');
    @endif
});
</script>
@endsection
