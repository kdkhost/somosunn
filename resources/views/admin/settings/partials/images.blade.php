<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle mr-2"></i> Gerencie os logotipos e imagens de fundo do seu sistema. Recomenda-se
        usar imagens otimizadas (WebP ou PNG transparente).
    </div>

    <h5 class="text-primary mb-3"><i class="fas fa-id-card mr-2"></i> Identidade Visual</h5>
    <div class="row">
        <!-- Logo Principal -->
        <div class="col-md-3 col-sm-6">
            <div class="form-group text-center">
                <label class="font-weight-bold d-block mb-3">Logo Principal (Header)</label>
                <div class="upload-box premium-upload-box" data-remove-input="#remove_logo_image"
                    data-preview-image-class="img-fluid rounded shadow-sm border"
                    data-preview-image-style="max-height: 80px;" data-existing-url="{{ $getUrl('logo_image') }}">
                    <input type="file" name="logo_image" class="d-none" accept="image/*">
                    <input type="hidden" name="remove_logo_image" id="remove_logo_image" value="0">

                    <div class="upload-preview mb-3 drop-zone-area">
                        @if($url = $getUrl('logo_image'))
                            <img src="{{ $url }}" class="img-fluid rounded shadow-sm border" style="max-height: 80px;">
                        @else
                            <div class="text-muted p-4 border-2 border-dashed rounded bg-light d-flex flex-column align-items-center justify-content-center"
                                style="min-height: 100px;">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-primary opacity-50"></i>
                                <span class="small font-weight-bold">Logo Site</span>
                            </div>
                        @endif
                    </div>

                    <div class="upload-actions d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-xs btn-primary upload-btn rounded-pill px-3">
                            <i class="fas fa-sync-alt mr-1"></i> Alterar
                        </button>
                        <button type="button"
                            class="btn btn-xs btn-danger upload-remove rounded-pill px-2 {{ $getUrl('logo_image') ? '' : 'd-none' }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Favicon -->
        <div class="col-md-3 col-sm-6">
            <div class="form-group text-center">
                <label class="font-weight-bold d-block mb-3">Favicon (Navegador)</label>
                <div class="upload-box premium-upload-box" data-remove-input="#remove_favicon_image"
                    data-preview-image-class="img-fluid rounded shadow-sm border"
                    data-preview-image-style="width: 32px; height: 32px;"
                    data-existing-url="{{ $getUrl('favicon_image') }}">
                    <input type="file" name="favicon_image" class="d-none" accept="image/*">
                    <input type="hidden" name="remove_favicon_image" id="remove_favicon_image" value="0">

                    <div class="upload-preview mb-3 drop-zone-area">
                        @if($url = $getUrl('favicon_image'))
                            <img src="{{ $url }}" class="img-fluid rounded shadow-sm border"
                                style="width: 32px; height: 32px;">
                        @else
                            <div class="text-muted p-4 border-2 border-dashed rounded bg-light d-flex flex-column align-items-center justify-content-center"
                                style="min-height: 100px;">
                                <i class="fas fa-globe-americas fa-2x mb-2 text-primary opacity-50"></i>
                                <span class="small font-weight-bold">Favicon</span>
                            </div>
                        @endif
                    </div>

                    <div class="upload-actions d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-xs btn-primary upload-btn rounded-pill px-3">
                            <i class="fas fa-sync-alt mr-1"></i> Alterar
                        </button>
                        <button type="button"
                            class="btn btn-xs btn-danger upload-remove rounded-pill px-2 {{ $getUrl('favicon_image') ? '' : 'd-none' }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logo Admin -->
        <div class="col-md-3 col-sm-6">
            <div class="form-group text-center">
                <label class="font-weight-bold d-block mb-3">Logo Admin (Sidebar)</label>
                <div class="upload-box premium-upload-box" data-remove-input="#remove_logo_admin"
                    data-preview-image-class="img-fluid rounded shadow-sm border"
                    data-preview-image-style="max-height: 80px; background: #343a40; padding: 5px;"
                    data-existing-url="{{ $getUrl('logo_admin') }}">
                    <input type="file" name="logo_admin" class="d-none" accept="image/*">
                    <input type="hidden" name="remove_logo_admin" id="remove_logo_admin" value="0">

                    <div class="upload-preview mb-3 drop-zone-area">
                        @if($url = $getUrl('logo_admin'))
                            <img src="{{ $url }}" class="img-fluid rounded shadow-sm border"
                                style="max-height: 80px; background: #343a40; padding: 5px;">
                        @else
                            <div class="text-muted p-4 border-2 border-dashed rounded bg-light d-flex flex-column align-items-center justify-content-center"
                                style="min-height: 100px;">
                                <i class="fas fa-user-shield fa-2x mb-2 text-primary opacity-50"></i>
                                <span class="small font-weight-bold">Logo Admin</span>
                            </div>
                        @endif
                    </div>

                    <div class="upload-actions d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-xs btn-primary upload-btn rounded-pill px-3">
                            <i class="fas fa-sync-alt mr-1"></i> Alterar
                        </button>
                        <button type="button"
                            class="btn btn-xs btn-danger upload-remove rounded-pill px-2 {{ $getUrl('logo_admin') ? '' : 'd-none' }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logo Auth -->
        <div class="col-md-3 col-sm-6">
            <div class="form-group text-center">
                <label class="font-weight-bold d-block mb-3">Logo Login/Auth</label>
                <div class="upload-box premium-upload-box" data-remove-input="#remove_logo_auth"
                    data-preview-image-class="img-fluid rounded shadow-sm border"
                    data-preview-image-style="max-height: 80px;" data-existing-url="{{ $getUrl('logo_auth') }}">
                    <input type="file" name="logo_auth" class="d-none" accept="image/*">
                    <input type="hidden" name="remove_logo_auth" id="remove_logo_auth" value="0">

                    <div class="upload-preview mb-3 drop-zone-area">
                        @if($url = $getUrl('logo_auth'))
                            <img src="{{ $url }}" class="img-fluid rounded shadow-sm border" style="max-height: 80px;">
                        @else
                            <div class="text-muted p-4 border-2 border-dashed rounded bg-light d-flex flex-column align-items-center justify-content-center"
                                style="min-height: 100px;">
                                <i class="fas fa-lock fa-2x mb-2 text-primary opacity-50"></i>
                                <span class="small font-weight-bold">Logo Auth</span>
                            </div>
                        @endif
                    </div>

                    <div class="upload-actions d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-xs btn-primary upload-btn rounded-pill px-3">
                            <i class="fas fa-sync-alt mr-1"></i> Alterar
                        </button>
                        <button type="button"
                            class="btn btn-xs btn-danger upload-remove rounded-pill px-2 {{ $getUrl('logo_auth') ? '' : 'd-none' }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-laptop-code mr-2"></i> Backgrounds e Capas</h5>
    <div class="row">
        <!-- Hero Image -->
        <div class="col-md-6 form-group">
            <label class="font-weight-bold d-block mb-3">Hero Image (Home)</label>
            <div class="upload-box premium-upload-box" 
                data-remove-input="#remove_hero_image"
                data-preview-image-class="img-fluid rounded shadow-sm border"
                data-preview-image-style="max-height: 200px; width: 100%; object-fit: cover;"
                data-existing-url="{{ $getUrl('hero_image') }}" style="height: auto;">
                <input type="file" name="hero_image" class="d-none" accept="image/*">
                <input type="hidden" name="remove_hero_image" id="remove_hero_image" value="0">
                
                <div class="upload-preview mb-3 drop-zone-area text-center">
                    @if($url = $getUrl('hero_image'))
                        <img src="{{ $url }}" class="img-fluid rounded shadow-sm border"
                            style="max-height: 200px; width: 100%; object-fit: cover;">
                    @else
                        <div class="text-muted p-5 border-2 border-dashed rounded bg-light d-flex flex-column align-items-center justify-content-center" style="min-height: 150px;">
                            <i class="fas fa-image fa-3x mb-3 text-primary opacity-50"></i>
                            <span class="font-weight-bold">Banner Principal (1920x600)</span>
                            <span class="small opacity-75">Arraste e solte ou clique para selecionar</span>
                        </div>
                    @endif
                </div>
                
                <div class="text-center">
                    <button type="button" class="btn btn-sm btn-primary upload-btn rounded-pill px-4 shadow-sm">
                        <i class="fas fa-images mr-1"></i> Selecionar Imagem
                    </button>
                    <button type="button" class="btn btn-sm btn-danger upload-remove rounded-pill px-3 shadow-sm {{ $getUrl('hero_image') ? '' : 'd-none' }}">
                        <i class="fas fa-trash mr-1"></i> Remover
                    </button>
                </div>
            </div>
        </div>

        <!-- Site Background -->
        <div class="col-md-6 form-group">
            <label class="font-weight-bold d-block mb-3">Background Geral do Site</label>
            <div class="upload-box premium-upload-box" 
                data-remove-input="#remove_site_bg_image"
                data-preview-image-class="img-fluid rounded shadow-sm border"
                data-preview-image-style="max-height: 200px; width: 100%; object-fit: cover;"
                data-existing-url="{{ $getUrl('site_bg_image') }}" style="height: auto;">
                <input type="file" name="site_bg_image" class="d-none" accept="image/*">
                <input type="hidden" name="remove_site_bg_image" id="remove_site_bg_image" value="0">
                
                <div class="upload-preview mb-3 drop-zone-area text-center">
                    @if($url = $getUrl('site_bg_image'))
                        <img src="{{ $url }}" class="img-fluid rounded shadow-sm border"
                            style="max-height: 200px; width: 100%; object-fit: cover;">
                    @else
                        <div class="text-muted p-5 border-2 border-dashed rounded bg-light d-flex flex-column align-items-center justify-content-center" style="min-height: 150px;">
                            <i class="fas fa-fill-drip fa-3x mb-3 text-primary opacity-50"></i>
                            <span class="font-weight-bold">Fundo Padrão (Pattern/Imagem)</span>
                            <span class="small opacity-75">Arraste e solte ou clique para selecionar</span>
                        </div>
                    @endif
                </div>
                
                <div class="text-center">
                    <button type="button" class="btn btn-sm btn-primary upload-btn rounded-pill px-4 shadow-sm">
                        <i class="fas fa-images mr-1"></i> Selecionar Imagem
                    </button>
                    <button type="button" class="btn btn-sm btn-danger upload-remove rounded-pill px-3 shadow-sm {{ $getUrl('site_bg_image') ? '' : 'd-none' }}">
                        <i class="fas fa-trash mr-1"></i> Remover
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>