<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle mr-2"></i> Configurações principais do sistema. Essas informações são exibidas no
        rodapé e em e-mails transacionais.
    </div>

    <h5 class="text-primary mb-3"><i class="fas fa-building mr-2"></i> Informações da Empresa</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Nome do Site (App Name)</label>
                <input type="text" name="app_name" class="form-control form-control-lg"
                    value="<?php echo e($settings['app_name'] ?? config('app.name')); ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Razão Social / Nome da Empresa</label>
                <input type="text" name="company_name" class="form-control form-control-lg"
                    value="<?php echo e($settings['company_name'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Telefone / WhatsApp</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    </div>
                    <input type="text" name="company_phone" class="form-control mask-phone"
                        value="<?php echo e($settings['company_phone'] ?? ''); ?>">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>E-mail de Contato</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input type="email" name="company_email" class="form-control"
                        value="<?php echo e($settings['company_email'] ?? ''); ?>">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>CEP</label>
                <input type="text" id="company_zip" name="company_zip" class="form-control mask-cep"
                    value="<?php echo e($settings['company_zip'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-map-marker-alt mr-2"></i> Endereço</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Endereço (Rua/Av)</label>
                <input type="text" name="company_address" class="form-control"
                    value="<?php echo e($settings['company_address'] ?? ''); ?>">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>Número</label>
                <input type="text" id="company_number" name="company_number" class="form-control"
                    value="<?php echo e($settings['company_number'] ?? ''); ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Complemento</label>
                <input type="text" name="company_complement" class="form-control"
                    value="<?php echo e($settings['company_complement'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Bairro</label>
                <input type="text" name="company_district" class="form-control"
                    value="<?php echo e($settings['company_district'] ?? ''); ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Cidade</label>
                <input type="text" name="company_city" class="form-control"
                    value="<?php echo e($settings['company_city'] ?? ''); ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Estado (UF)</label>
                <input type="text" name="company_state" class="form-control"
                    value="<?php echo e($settings['company_state'] ?? ''); ?>">
            </div>
        </div>
    </div>
</div><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\settings\partials\general.blade.php ENDPATH**/ ?>