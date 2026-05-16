{{--
Form de configuracao de UM provedor S3 (parcial reutilizada nas
abas IDrive e2, Wasabi e AWS S3 da view storage-providers).

Variaveis recebidas:
  - $provider : array com key, name, access_key, secret_masked, bucket,
                region, endpoint, url, path_style, configured
  - $isActive : bool, true quando este e o provedor ativo

Spec: .kiro/specs/multi-provider-s3-storage (task 5.1)
--}}
@php
    $providerKey = $provider['key'];
    $hints = [
        'idrive' => 'Path Style ativado e recomendado. Endpoint exemplo: https://b1l1.la4.idrivee2-XX.com',
        'wasabi' => 'Endpoint regional exemplo: s3.us-east-1.wasabisys.com (sem https://). Path Style ativado.',
        'aws'    => 'Endpoint pode ficar vazio (usa endpoint padrao da AWS). Path Style desativado.',
    ];
    $hintText = $hints[$providerKey] ?? '';
@endphp

<form action="{{ url('/admin/settings/storage-providers/' . $providerKey) }}"
      method="POST"
      class="form-storage-provider"
      data-provider="{{ $providerKey }}">
    @csrf
    <input type="hidden" name="provider" value="{{ $providerKey }}">

    @if ($hintText)
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            {{ $hintText }}
        </div>
    @endif

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="{{ $providerKey }}_access_key">
                Access Key <span class="text-danger">*</span>
            </label>
            <input type="text"
                   id="{{ $providerKey }}_access_key"
                   name="access_key"
                   class="form-control"
                   value="{{ $provider['access_key'] }}"
                   autocomplete="off"
                   placeholder="ex: AKIA...">
        </div>
        <div class="form-group col-md-6">
            <label for="{{ $providerKey }}_secret_key">
                Secret Key <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <input type="password"
                       id="{{ $providerKey }}_secret_key"
                       name="secret_key"
                       class="form-control"
                       value="{{ $provider['secret_masked'] ? '' : '' }}"
                       autocomplete="new-password"
                       placeholder="{{ $provider['secret_masked'] ? 'atual: ' . $provider['secret_masked'] . ' - deixe em branco para manter' : 'cole o secret aqui' }}">
                <div class="input-group-append">
                    <button type="button"
                            class="btn btn-default btn-toggle-secret"
                            data-target="{{ $providerKey }}_secret_key"
                            tabindex="-1"
                            title="Mostrar/ocultar">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            @if ($provider['secret_masked'])
                <small class="form-text text-muted">
                    Atualmente: <code>{{ $provider['secret_masked'] }}</code>. Preencha somente para alterar.
                </small>
            @endif
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="{{ $providerKey }}_bucket">
                Bucket <span class="text-danger">*</span>
            </label>
            <input type="text"
                   id="{{ $providerKey }}_bucket"
                   name="bucket"
                   class="form-control"
                   value="{{ $provider['bucket'] }}"
                   autocomplete="off">
        </div>
        <div class="form-group col-md-6">
            <label for="{{ $providerKey }}_region">
                Region <span class="text-danger">*</span>
            </label>
            <input type="text"
                   id="{{ $providerKey }}_region"
                   name="region"
                   class="form-control"
                   value="{{ $provider['region'] }}"
                   autocomplete="off"
                   placeholder="ex: us-east-1">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="{{ $providerKey }}_endpoint">Endpoint</label>
            <input type="text"
                   id="{{ $providerKey }}_endpoint"
                   name="endpoint"
                   class="form-control"
                   value="{{ $provider['endpoint'] }}"
                   autocomplete="off"
                   placeholder="(opcional - vazio = endpoint padrao do provedor)">
            <small class="form-text text-muted">
                Para Wasabi/IDrive use o host (ex.: s3.us-east-1.wasabisys.com). Para AWS deixe vazio.
            </small>
        </div>
        <div class="form-group col-md-6">
            <label for="{{ $providerKey }}_url">URL Publica (CDN)</label>
            <input type="text"
                   id="{{ $providerKey }}_url"
                   name="url"
                   class="form-control"
                   value="{{ $provider['url'] }}"
                   autocomplete="off"
                   placeholder="(opcional)">
            <small class="form-text text-muted">
                URL base usada para servir arquivos publicos. Vazio = URL gerada pelo SDK.
            </small>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <div class="custom-control custom-switch">
                <input type="checkbox"
                       id="{{ $providerKey }}_path_style"
                       name="path_style"
                       value="1"
                       class="custom-control-input"
                       {{ $provider['path_style'] ? 'checked' : '' }}>
                <label for="{{ $providerKey }}_path_style" class="custom-control-label">
                    Usar Path-Style URLs
                </label>
            </div>
            <small class="form-text text-muted">
                Recomendado para IDrive e2 e Wasabi. AWS S3 normalmente usa Virtual Hosted Style (desativado).
            </small>
        </div>
    </div>

    <div class="text-right mt-3">
        <button type="button"
                class="btn btn-info btn-test-provider"
                data-provider="{{ $providerKey }}"
                data-name="{{ $provider['name'] }}"
                {{ $provider['configured'] ? '' : 'disabled' }}>
            <i class="fas fa-vial"></i> Testar Conexao
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Salvar {{ $provider['name'] }}
        </button>
    </div>
</form>
