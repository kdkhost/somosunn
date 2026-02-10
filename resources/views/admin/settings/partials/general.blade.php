<div class="card-body">
    <h5 class="text-primary mb-3"><i class="fas fa-info-circle mr-2"></i>Informações Básicas</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Nome do site</label>
            <input name="app_name" class="form-control" value="{{ $settings['app_name'] ?? config('app.name') }}">
        </div>
        <div class="col-md-6 form-group">
            <label>Empresa</label>
            <input name="company_name" class="form-control" value="{{ $settings['company_name'] ?? '' }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label>Telefone</label>
            <input name="company_phone" class="form-control mask-phone" value="{{ $settings['company_phone'] ?? '' }}">
        </div>
        <div class="col-md-4 form-group">
            <label>E-mail de Contato</label>
            <input name="company_email" class="form-control" value="{{ $settings['company_email'] ?? '' }}">
        </div>
        <div class="col-md-4 form-group">
            <label>CEP</label>
            <input id="company_zip" name="company_zip" class="form-control mask-cep"
                value="{{ $settings['company_zip'] ?? '' }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label>Endereço</label>
            <input name="company_address" class="form-control" value="{{ $settings['company_address'] ?? '' }}">
        </div>
        <div class="col-md-2 form-group">
            <label>Número</label>
            <input id="company_number" name="company_number" class="form-control"
                value="{{ $settings['company_number'] ?? '' }}">
        </div>
        <div class="col-md-4 form-group">
            <label>Complemento</label>
            <input name="company_complement" class="form-control" value="{{ $settings['company_complement'] ?? '' }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label>Bairro</label>
            <input name="company_district" class="form-control" value="{{ $settings['company_district'] ?? '' }}">
        </div>
        <div class="col-md-4 form-group">
            <label>Cidade</label>
            <input name="company_city" class="form-control" value="{{ $settings['company_city'] ?? '' }}">
        </div>
        <div class="col-md-4 form-group">
            <label>Estado</label>
            <input name="company_state" class="form-control" value="{{ $settings['company_state'] ?? '' }}">
        </div>
    </div>
</div>