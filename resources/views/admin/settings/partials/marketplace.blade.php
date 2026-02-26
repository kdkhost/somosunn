<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle mr-2"></i> Ajustes do marketplace (comissão da plataforma e regras de venda).
    </div>

    <h5 class="text-primary mb-3"><i class="fas fa-percent mr-2"></i> Taxas da Plataforma</h5>
    <style>
        .border-dashed { border-style: dashed !important; }
    </style>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Comissão do Marketplace (%)</label>
                <div class="input-group">
                    <input type="number" name="marketplace_platform_fee_percent" class="form-control"
                        value="{{ $settings['marketplace_platform_fee_percent'] ?? '0' }}" min="0" max="100"
                        step="0.01">
                    <div class="input-group-append">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <small class="text-muted">
                    Percentual descontado do vendedor em cada venda (não altera o preço exibido para o comprador).
                </small>
            </div>

            <div class="form-group">
                <label>Comportamento da Taxa</label>
                <select name="marketplace_fee_behavior" class="form-control">
                    @php $behavior = $settings['marketplace_fee_behavior'] ?? 'absorb'; @endphp
                    <option value="absorb" {{ $behavior === 'absorb' ? 'selected' : '' }}>Absorver (Descontar do Vendedor)</option>
                    <option value="pass" {{ $behavior === 'pass' ? 'selected' : '' }}>Repassar (Adicionar ao Cliente)</option>
                </select>
                <small class="text-muted">
                    Defina se a taxa será descontada do valor recebido pelo vendedor ou acrescida ao valor pago pelo cliente.
                </small>
            </div>

            <div class="form-group mb-4">
                <div class="custom-control custom-switch custom-switch-lg custom-switch-off-danger custom-switch-on-success">
                    <input type="hidden" name="marketplace_manual_approval_enabled" value="0">
                    <input type="checkbox" class="custom-control-input" id="marketplace_manual_approval_enabled"
                        name="marketplace_manual_approval_enabled" value="1" {{ ($settings['marketplace_manual_approval_enabled'] ?? 1) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="marketplace_manual_approval_enabled">Habilitar Aprovação Manual (Permuta)</label>
                </div>
                <small class="text-muted">Permite admins aprovarem pedidos sem pagamento financeiro (ex: negociação direta/permuta).</small>
            </div>
        </div>
    </div>

    <hr class="my-4">

    @php
        $slide1Image = isset($getUrl) ? $getUrl('marketplace_hero_slide_1_image') : '';
        $slide1ImageMobile = isset($getUrl) ? $getUrl('marketplace_hero_slide_1_image_mobile') : '';
        $slide2Image = isset($getUrl) ? $getUrl('marketplace_hero_slide_2_image') : '';
        $slide2ImageMobile = isset($getUrl) ? $getUrl('marketplace_hero_slide_2_image_mobile') : '';
        $slide3Image = isset($getUrl) ? $getUrl('marketplace_hero_slide_3_image') : '';
        $slide3ImageMobile = isset($getUrl) ? $getUrl('marketplace_hero_slide_3_image_mobile') : '';

        $exitImage = isset($getUrl) ? $getUrl('marketplace_exit_banner_image') : '';
    @endphp

    <h5 class="text-primary mb-3"><i class="fas fa-images mr-2"></i> Hero (Banner Rotativo)</h5>

    <div class="row">
        <div class="col-lg-6">
            <div class="form-group mb-4">
                <div class="custom-control custom-switch custom-switch-lg custom-switch-off-danger custom-switch-on-success">
                    <input type="hidden" name="marketplace_hero_enabled" value="0">
                    <input type="checkbox" class="custom-control-input" id="marketplace_hero_enabled"
                        name="marketplace_hero_enabled" value="1" {{ ($settings['marketplace_hero_enabled'] ?? 1) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="marketplace_hero_enabled">Habilitar banner rotativo</label>
                </div>
            </div>

            <div class="form-group mb-3">
                <div class="custom-control custom-switch custom-switch-lg custom-switch-off-danger custom-switch-on-success">
                    <input type="hidden" name="marketplace_hero_autoplay" value="0">
                    <input type="checkbox" class="custom-control-input" id="marketplace_hero_autoplay"
                        name="marketplace_hero_autoplay" value="1" {{ ($settings['marketplace_hero_autoplay'] ?? 1) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="marketplace_hero_autoplay">Autoplay</label>
                </div>
                <small class="text-muted">Quando ativo, o banner muda automaticamente.</small>
            </div>

            <div class="form-group mb-3">
                <label>Intervalo (segundos)</label>
                <input type="number" name="marketplace_hero_interval_seconds" class="form-control"
                    value="{{ $settings['marketplace_hero_interval_seconds'] ?? '6' }}" min="2" max="20" step="1">
                <small class="text-muted">Entre 2 e 20 segundos.</small>
            </div>

            <div class="form-group mb-0">
                <label>Animação</label>
                <select name="marketplace_hero_animation" class="form-control">
                    @php $anim = $settings['marketplace_hero_animation'] ?? 'slide'; @endphp
                    <option value="slide" {{ $anim === 'slide' ? 'selected' : '' }}>Slide</option>
                    <option value="fade" {{ $anim === 'fade' ? 'selected' : '' }}>Fade</option>
                </select>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="alert alert-secondary">
                <div class="font-weight-bold mb-1">Como funciona</div>
                <div class="small mb-0">
                    Configure até 3 slides. Se um slide ficar sem título e sem imagem, ele não aparece no banner.
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-4">
            <div class="card border">
                <div class="card-header">
                    <strong>Slide 1</strong>
                </div>
                <div class="card-body">

                    @include('components.forms.data-upload', [
                        'name' => 'marketplace_hero_slide_1_image',
                        'label' => 'Imagem Desktop (1920x600)',
                        'currentValue' => $slide1Image
                    ])
                    @if($slide1Image)
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="remove_marketplace_hero_slide_1_image" name="remove_marketplace_hero_slide_1_image" value="1">
                            <label class="custom-control-label" for="remove_marketplace_hero_slide_1_image">Remover desktop</label>
                        </div>
                    @endif

                    <hr class="border-dashed my-3">

                    @include('components.forms.data-upload', [
                        'name' => 'marketplace_hero_slide_1_image_mobile',
                        'label' => 'Imagem Mobile (800x1200)',
                        'currentValue' => $slide1ImageMobile
                    ])
                    @if($slide1ImageMobile)
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="remove_marketplace_hero_slide_1_image_mobile" name="remove_marketplace_hero_slide_1_image_mobile" value="1">
                            <label class="custom-control-label" for="remove_marketplace_hero_slide_1_image_mobile">Remover mobile</label>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" name="marketplace_hero_slide_1_title" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_1_title'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Texto</label>
                        <input type="text" name="marketplace_hero_slide_1_subtitle" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_1_subtitle'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Botão (texto)</label>
                        <input type="text" name="marketplace_hero_slide_1_button_text" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_1_button_text'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Botão (URL)</label>
                        <input type="text" name="marketplace_hero_slide_1_button_url" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_1_button_url'] ?? '' }}" placeholder="/marketplace">
                    </div>
                    <div class="form-group mb-0">
                        <label>Abrir link</label>
                        @php $t1 = $settings['marketplace_hero_slide_1_button_target'] ?? '_self'; @endphp
                        <select name="marketplace_hero_slide_1_button_target" class="form-control">
                            <option value="_self"  {{ $t1 === '_self'  ? 'selected' : '' }}>Mesma aba</option>
                            <option value="_blank" {{ $t1 === '_blank' ? 'selected' : '' }}>Nova guia</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border">
                <div class="card-header">
                    <strong>Slide 2</strong>
                </div>
                <div class="card-body">
                    @include('components.forms.data-upload', [
                        'name' => 'marketplace_hero_slide_2_image',
                        'label' => 'Imagem Desktop (1920x600)',
                        'currentValue' => $slide2Image
                    ])
                    @if($slide2Image)
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="remove_marketplace_hero_slide_2_image" name="remove_marketplace_hero_slide_2_image" value="1">
                            <label class="custom-control-label" for="remove_marketplace_hero_slide_2_image">Remover desktop</label>
                        </div>
                    @endif

                    <hr class="border-dashed my-3">

                    @include('components.forms.data-upload', [
                        'name' => 'marketplace_hero_slide_2_image_mobile',
                        'label' => 'Imagem Mobile (800x1200)',
                        'currentValue' => $slide2ImageMobile
                    ])
                    @if($slide2ImageMobile)
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="remove_marketplace_hero_slide_2_image_mobile" name="remove_marketplace_hero_slide_2_image_mobile" value="1">
                            <label class="custom-control-label" for="remove_marketplace_hero_slide_2_image_mobile">Remover mobile</label>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" name="marketplace_hero_slide_2_title" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_2_title'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Texto</label>
                        <input type="text" name="marketplace_hero_slide_2_subtitle" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_2_subtitle'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Botão (texto)</label>
                        <input type="text" name="marketplace_hero_slide_2_button_text" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_2_button_text'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Botão (URL)</label>
                        <input type="text" name="marketplace_hero_slide_2_button_url" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_2_button_url'] ?? '' }}" placeholder="/marketplace">
                    </div>
                    <div class="form-group mb-0">
                        <label>Abrir link</label>
                        @php $t2 = $settings['marketplace_hero_slide_2_button_target'] ?? '_self'; @endphp
                        <select name="marketplace_hero_slide_2_button_target" class="form-control">
                            <option value="_self"  {{ $t2 === '_self'  ? 'selected' : '' }}>Mesma aba</option>
                            <option value="_blank" {{ $t2 === '_blank' ? 'selected' : '' }}>Nova guia</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border">
                <div class="card-header">
                    <strong>Slide 3</strong>
                </div>
                <div class="card-body">
                    @include('components.forms.data-upload', [
                        'name' => 'marketplace_hero_slide_3_image',
                        'label' => 'Imagem Desktop (1920x600)',
                        'currentValue' => $slide3Image
                    ])
                    @if($slide3Image)
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="remove_marketplace_hero_slide_3_image" name="remove_marketplace_hero_slide_3_image" value="1">
                            <label class="custom-control-label" for="remove_marketplace_hero_slide_3_image">Remover desktop</label>
                        </div>
                    @endif

                    <hr class="border-dashed my-3">

                    @include('components.forms.data-upload', [
                        'name' => 'marketplace_hero_slide_3_image_mobile',
                        'label' => 'Imagem Mobile (800x1200)',
                        'currentValue' => $slide3ImageMobile
                    ])
                    @if($slide3ImageMobile)
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="remove_marketplace_hero_slide_3_image_mobile" name="remove_marketplace_hero_slide_3_image_mobile" value="1">
                            <label class="custom-control-label" for="remove_marketplace_hero_slide_3_image_mobile">Remover mobile</label>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" name="marketplace_hero_slide_3_title" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_3_title'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Texto</label>
                        <input type="text" name="marketplace_hero_slide_3_subtitle" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_3_subtitle'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Botão (texto)</label>
                        <input type="text" name="marketplace_hero_slide_3_button_text" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_3_button_text'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Botão (URL)</label>
                        <input type="text" name="marketplace_hero_slide_3_button_url" class="form-control"
                            value="{{ $settings['marketplace_hero_slide_3_button_url'] ?? '' }}" placeholder="/marketplace">
                    </div>
                    <div class="form-group mb-0">
                        <label>Abrir link</label>
                        @php $t3 = $settings['marketplace_hero_slide_3_button_target'] ?? '_self'; @endphp
                        <select name="marketplace_hero_slide_3_button_target" class="form-control">
                            <option value="_self"  {{ $t3 === '_self'  ? 'selected' : '' }}>Mesma aba</option>
                            <option value="_blank" {{ $t3 === '_blank' ? 'selected' : '' }}>Nova guia</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-bullhorn mr-2"></i> Banner de Saída (Oferta)</h5>

    <div class="row">
        <div class="col-lg-6">
            <div class="form-group mb-4">
                <div class="custom-control custom-switch custom-switch-lg custom-switch-off-danger custom-switch-on-success">
                    <input type="hidden" name="marketplace_exit_enabled" value="0">
                    <input type="checkbox" class="custom-control-input" id="marketplace_exit_enabled"
                        name="marketplace_exit_enabled" value="1" {{ ($settings['marketplace_exit_enabled'] ?? 0) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="marketplace_exit_enabled">Habilitar popup de saída</label>
                </div>
                <small class="text-muted">Aparece uma vez por sessão quando o usuário tenta sair da página.</small>
            </div>

            <div class="form-group mb-3">
                <label>Delay (segundos)</label>
                <input type="number" name="marketplace_exit_delay_seconds" class="form-control"
                    value="{{ $settings['marketplace_exit_delay_seconds'] ?? '15' }}" min="0" max="120" step="1">
                <small class="text-muted">Tempo mínimo antes do popup poder aparecer.</small>
            </div>

            <div class="form-group mb-3">
                <label>Título</label>
                <input type="text" name="marketplace_exit_title" class="form-control"
                    value="{{ $settings['marketplace_exit_title'] ?? 'Espere! Temos uma oferta pra você' }}">
            </div>

            <div class="form-group mb-3">
                <label>Texto</label>
                <textarea name="marketplace_exit_text" class="form-control" rows="3">{{ $settings['marketplace_exit_text'] ?? 'Use um cupom e ganhe desconto agora mesmo.' }}</textarea>
            </div>

            <div class="form-group mb-3">
                <label>Cupom (opcional)</label>
                <input type="text" name="marketplace_exit_coupon_code" class="form-control"
                    value="{{ $settings['marketplace_exit_coupon_code'] ?? '' }}" placeholder="EX: UNN10">
            </div>

            <div class="form-group mb-3">
                <label>Botão (texto)</label>
                <input type="text" name="marketplace_exit_button_text" class="form-control"
                    value="{{ $settings['marketplace_exit_button_text'] ?? 'Ver ofertas' }}">
            </div>

            <div class="form-group mb-0">
                <label>Botão (URL)</label>
                <input type="text" name="marketplace_exit_button_url" class="form-control"
                    value="{{ $settings['marketplace_exit_button_url'] ?? '/marketplace' }}" placeholder="/marketplace">
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border">
                <div class="card-header">
                    <strong>Imagem do Popup</strong>
                </div>
                <div class="card-body">

                    @include('components.forms.data-upload', [
                        'name' => 'marketplace_exit_banner_image',
                        'label' => 'Imagem do Popup de Saída',
                        'currentValue' => $exitImage
                    ])

                    @if($exitImage)
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="remove_marketplace_exit_banner_image" name="remove_marketplace_exit_banner_image" value="1">
                            <label class="custom-control-label" for="remove_marketplace_exit_banner_image">Remover imagem existente</label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-bell mr-2"></i> Pop-up de Eventos</h5>

    <div class="row">
        <div class="col-lg-6">
            <div class="form-group mb-4">
                <div class="custom-control custom-switch custom-switch-lg custom-switch-off-danger custom-switch-on-success">
                    <input type="hidden" name="marketplace_events_popup_enabled" value="0">
                    <input type="checkbox" class="custom-control-input" id="marketplace_events_popup_enabled"
                        name="marketplace_events_popup_enabled" value="1" {{ ($settings['marketplace_events_popup_enabled'] ?? 1) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="marketplace_events_popup_enabled">Habilitar pop-up de eventos</label>
                </div>
                <small class="text-muted">Exibe um toast com sugestões de eventos para quem estiver navegando no marketplace.</small>
            </div>

            <div class="form-group mb-3">
                <label>Intervalo (segundos)</label>
                <input type="number" name="marketplace_events_popup_interval_seconds" class="form-control"
                    value="{{ $settings['marketplace_events_popup_interval_seconds'] ?? '60' }}" min="20" max="300" step="1">
                <small class="text-muted">Entre 20 e 300 segundos.</small>
            </div>

            <div class="form-group mb-0">
                <label>Máximo por sessão</label>
                <input type="number" name="marketplace_events_popup_max_per_session" class="form-control"
                    value="{{ $settings['marketplace_events_popup_max_per_session'] ?? '3' }}" min="0" max="10" step="1">
                <small class="text-muted">Use 0 para desativar a exibição automática.</small>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="alert alert-secondary mb-0">
                <div class="font-weight-bold mb-1">Como funciona</div>
                <div class="small mb-0">
                    O pop-up aparece apenas para eventos <strong>publicados</strong> e com data futura. Recomendamos intervalos maiores para não incomodar o visitante.
                </div>
            </div>
        </div>
    </div>
</div>
