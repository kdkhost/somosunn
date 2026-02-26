@php
    $supportedProviders = ['google', 'facebook', 'linkedin'];
    $socialProviders = [
        'google' => [
            'label' => 'Google',
            'iconClass' => 'fab fa-google',
            'outlineClass' => 'card-danger',
            'textClass' => 'text-danger',
            'collapseId' => 'collapseGoogle',
            'idLabel' => 'Client ID',
            'secretLabel' => 'Client Secret',
            'consoleUrl' => 'https://console.cloud.google.com/apis/credentials',
        ],
        'facebook' => [
            'label' => 'Facebook',
            'iconClass' => 'fab fa-facebook',
            'outlineClass' => 'card-primary',
            'textClass' => '',
            'collapseId' => 'collapseFacebook',
            'idLabel' => 'App ID',
            'secretLabel' => 'App Secret',
            'consoleUrl' => 'https://developers.facebook.com/apps/',
        ],
        'twitter' => [
            'label' => 'Twitter / X',
            'iconClass' => 'fab fa-twitter',
            'outlineClass' => 'card-info',
            'textClass' => 'text-info',
            'collapseId' => 'collapseTwitter',
            'idLabel' => 'Client ID (API Key)',
            'secretLabel' => 'Client Secret (API Secret)',
            'consoleUrl' => 'https://developer.x.com/en/portal/dashboard',
        ],
        'linkedin' => [
            'label' => 'LinkedIn',
            'iconClass' => 'fab fa-linkedin',
            'outlineClass' => 'card-dark',
            'textClass' => 'text-dark',
            'collapseId' => 'collapseLinkedin',
            'idLabel' => 'Client ID',
            'secretLabel' => 'Client Secret',
            'consoleUrl' => 'https://www.linkedin.com/developers/apps',
        ],
    ];
@endphp

<div class="card-body">
    <h5 class="text-primary mb-3"><i class="fas fa-users mr-2"></i>Login Social</h5>

    <div class="alert alert-info">
        <h6 class="mb-2"><i class="fas fa-info-circle mr-1"></i>Instrucoes rapidas</h6>
        <p class="mb-2">
            Para cada provedor: crie um app OAuth 2.0 no portal oficial, adicione a callback URL e copie o Client/App ID e Secret para os campos abaixo.
        </p>
        <div class="row">
            @foreach($socialProviders as $key => $provider)
                @php
                    $callbackUrl = route('social.callback', ['provider' => $key]);
                    $isSupported = in_array($key, $supportedProviders, true);
                @endphp
                <div class="col-md-6 mb-2">
                    <label class="small font-weight-bold d-block mb-1">{{ $provider['label'] }}</label>
                    <div class="input-group input-group-sm">
                        <input type="text" readonly class="form-control" value="{{ $callbackUrl }}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" data-copy-social-url="{{ $callbackUrl }}">Copiar</button>
                        </div>
                    </div>
                    @if(!$isSupported)
                        <small class="text-warning d-block mt-1">Nao ativo no backend atual.</small>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div id="social-accordion">
        @foreach($socialProviders as $key => $provider)
            @php
                $collapseId = $provider['collapseId'];
                $expanded = $key === 'google';
                $callbackUrl = route('social.callback', ['provider' => $key]);
                $isSupported = in_array($key, $supportedProviders, true);
            @endphp
            <div class="card card-outline {{ $provider['outlineClass'] }} social-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <button type="button" class="btn btn-link py-0 {{ $provider['textClass'] }}" data-toggle="collapse"
                            data-target="#{{ $collapseId }}" aria-expanded="{{ $expanded ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                            <i class="{{ $provider['iconClass'] }} mr-1"></i> {{ $provider['label'] }}
                        </button>
                    </h3>
                    @if($isSupported)
                        <span class="badge badge-success float-right">Suportado</span>
                    @else
                        <span class="badge badge-warning float-right">Nao ativo no backend</span>
                    @endif
                </div>
                <div id="{{ $collapseId }}" class="collapse {{ $expanded ? 'show' : '' }}" data-parent="#social-accordion">
                    <div class="card-body">
                        <div class="custom-control custom-switch mb-3">
                            <input type="hidden" name="social_{{ $key }}_enabled" value="0">
                            <input type="checkbox" class="custom-control-input" id="social_{{ $key }}_enabled"
                                name="social_{{ $key }}_enabled" value="1" {{ ($settings['social_'.$key.'_enabled'] ?? 0) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="social_{{ $key }}_enabled">Ativar Login com {{ $provider['label'] }}</label>
                        </div>

                        <div class="alert alert-light border mb-3">
                            <div class="mb-2">
                                <strong>Portal do desenvolvedor:</strong>
                                <a href="{{ $provider['consoleUrl'] }}" target="_blank" rel="noopener">{{ $provider['consoleUrl'] }}</a>
                            </div>
                            <div class="mb-2"><strong>Callback URL:</strong> <code>{{ $callbackUrl }}</code></div>
                            <ol class="mb-0 pl-3">
                                <li>Crie um app OAuth 2.0 (Web).</li>
                                <li>Adicione a callback URL acima.</li>
                                <li>Copie {{ $provider['idLabel'] }} e {{ $provider['secretLabel'] }}.</li>
                                <li>Cole nos campos abaixo e salve.</li>
                            </ol>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{ $provider['idLabel'] }}</label>
                                <input name="social_{{ $key }}_client_id" class="form-control"
                                    value="{{ $settings['social_'.$key.'_client_id'] ?? ($key === 'facebook' ? ($settings['social_facebook_app_id'] ?? '') : '') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ $provider['secretLabel'] }}</label>
                                <input type="password" name="social_{{ $key }}_client_secret" class="form-control"
                                    value="{{ $settings['social_'.$key.'_client_secret'] ?? ($key === 'facebook' ? ($settings['social_facebook_app_secret'] ?? '') : '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-copy-social-url]').forEach(function (button) {
                if (button.dataset.bound === '1') {
                    return;
                }
                button.dataset.bound = '1';

                button.addEventListener('click', async function () {
                    const value = this.getAttribute('data-copy-social-url') || '';
                    if (!value) {
                        return;
                    }

                    const original = this.textContent;
                    try {
                        await navigator.clipboard.writeText(value);
                        this.textContent = 'Copiado';
                        setTimeout(() => {
                            this.textContent = original;
                        }, 1200);
                    } catch (e) {
                        if (window.toastr && typeof window.toastr.error === 'function') {
                            window.toastr.error('Nao foi possivel copiar a URL.');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Nao foi possivel copiar a URL.'
                            });
                        }
                    }
                });
            });
        });
    </script>
@endpush
