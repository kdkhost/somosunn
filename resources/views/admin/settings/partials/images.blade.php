<div class="card-body">
    <h5 class="text-primary mb-3"><i class="fas fa-images mr-2"></i>Logotipos e Ícones</h5>
    <div class="row">
        <div class="col-md-3 form-group">
            <label>Logo Principal</label>
            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('logo_image') }}" data-remove-input="[name='remove_logo_image']">
                <input type="hidden" name="remove_logo_image" value="0">
                <input type="file" name="logo_image" accept="image/*" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <label>Favicon</label>
            <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('favicon_image') }}" data-remove-input="[name='remove_favicon_image']">
                <input type="hidden" name="remove_favicon_image" value="0">
                <input type="file" name="favicon_image" accept="image/*" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <label>Logo (Painel Admin)</label>
            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('logo_admin') }}" data-remove-input="[name='remove_logo_admin']">
                <input type="hidden" name="remove_logo_admin" value="0">
                <input type="file" name="logo_admin" accept="image/*" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <label>Logo (Login/Auth)</label>
            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('logo_auth') }}"
                data-remove-input="[name='remove_logo_auth']">
                <input type="hidden" name="remove_logo_auth" value="0">
                <input type="file" name="logo_auth" accept="image/*" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <label>Logo (Frontend/Site)</label>
            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('logo_front') }}" data-remove-input="[name='remove_logo_front']">
                <input type="hidden" name="remove_logo_front" value="0">
                <input type="file" name="logo_front" accept="image/*" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
        </div>
    </div>

    <hr>
    <h5 class="text-primary mb-3"><i class="fas fa-image mr-2"></i>Backgrounds</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Hero Image (Frente)</label>
            <div class="upload-box" data-max-size="{{ 10 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('hero_image') }}" data-remove-input="[name='remove_hero_image']">
                <input type="hidden" name="remove_hero_image" value="0">
                <input type="file" name="hero_image" accept="image/*" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
        </div>
        <div class="col-md-6 form-group">
            <label>Background do Site</label>
            <div class="upload-box" data-max-size="{{ 10 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('site_bg_image') }}" data-remove-input="[name='remove_site_bg_image']">
                <input type="hidden" name="remove_site_bg_image" value="0">
                <input type="file" name="site_bg_image" accept="image/*" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
        </div>
    </div>
</div>