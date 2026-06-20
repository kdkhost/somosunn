@php
    $documentDigits = preg_replace('/\D/', '', (string) old('doc', $user->doc));
    $personType = old('person_type', strlen($documentDigits) > 11 ? 'J' : 'F');
    $selectedRole = old('role', $user->role ?? 'member');
    $isMarketingManager = $user->exists && $user->isMarketingManager();
    $showPixKey = in_array($selectedRole, ['admin', 'superadmin'], true) || $isMarketingManager;
@endphp

<hr class="my-4">
<h5 class="font-weight-bold mb-3"><i class="fas fa-id-card mr-2 text-primary"></i>Dados pessoais e profissionais</h5>
<div class="form-row">
    <div class="form-group col-md-3">
        <label>Tipo de pessoa</label>
        <select name="person_type" id="admin-user-person-type" class="form-control">
            <option value="F" {{ $personType === 'F' ? 'selected' : '' }}>Pessoa Física</option>
            <option value="J" {{ $personType === 'J' ? 'selected' : '' }}>Pessoa Jurídica</option>
        </select>
    </div>
    <div class="form-group col-md-3">
        <label>CPF/CNPJ</label>
        <input name="doc" id="admin-user-doc" class="form-control" inputmode="numeric"
            value="{{ old('doc', $user->doc) }}" autocomplete="off">
    </div>
    <div class="form-group col-md-3">
        <label>Telefone</label>
        <input name="phone" id="admin-user-phone" class="form-control" inputmode="tel"
            value="{{ old('phone', $user->phone) }}" placeholder="(00) 00000-0000">
    </div>
    <div class="form-group col-md-3">
        <label>Data de nascimento</label>
        <input type="date" name="birth_date" class="form-control"
            value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
    </div>
    <div class="form-group col-md-3">
        <label>Gênero</label>
        <select name="gender" class="form-control">
            <option value="">Não informado</option>
            <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Masculino</option>
            <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Feminino</option>
            <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Outro</option>
            <option value="prefer_not_to_say" {{ old('gender', $user->gender) === 'prefer_not_to_say' ? 'selected' : '' }}>Prefere não informar</option>
        </select>
    </div>
    <div class="form-group col-md-3">
        <label>Ocupação</label>
        <input name="occupation" class="form-control" value="{{ old('occupation', $user->occupation) }}">
    </div>
    <div class="form-group col-md-3">
        <label>Empresa</label>
        <input name="company" class="form-control" value="{{ old('company', $user->company) }}">
    </div>
    <div class="form-group col-md-3">
        <label>Segmento</label>
        <input name="segment" class="form-control" value="{{ old('segment', $user->segment) }}">
    </div>
    <div class="form-group col-md-6">
        <label>Interesses</label>
        <input name="interests" class="form-control" value="{{ old('interests', $user->interests) }}"
            placeholder="Networking, cursos, eventos">
    </div>
    <div class="form-group col-md-6" id="admin-user-pix-container"
        data-marketing-manager="{{ $isMarketingManager ? '1' : '0' }}"
        data-current-role="{{ $selectedRole }}"
        style="{{ $showPixKey ? '' : 'display:none;' }}">
        <label>Chave PIX para recebimentos</label>
        <input name="pix_key" id="admin-user-pix-key" class="form-control" value="{{ old('pix_key', $user->pix_key) }}"
            {{ $showPixKey ? 'required' : 'disabled' }}>
        <small class="form-text text-muted">Obrigatória para Super Admin, Administrador e responsável de marketing.</small>
    </div>
    <div class="form-group col-12">
        <label>Biografia</label>
        <textarea name="bio" class="form-control" rows="3" maxlength="500">{{ old('bio', $user->bio) }}</textarea>
    </div>
</div>

<hr class="my-4">
<h5 class="font-weight-bold mb-3"><i class="fas fa-map-marker-alt mr-2 text-primary"></i>Endereço</h5>
<div class="form-row">
    <div class="form-group col-md-3">
        <label>CEP</label>
        <input name="cep" id="admin-user-cep" class="form-control" inputmode="numeric"
            value="{{ old('cep', $user->cep) }}" placeholder="00000-000">
        <small id="admin-user-cep-status" class="form-text text-muted">O endereço será preenchido automaticamente.</small>
    </div>
    <div class="form-group col-md-6">
        <label>Logradouro</label>
        <input name="street" id="admin-user-street" class="form-control" value="{{ old('street', $user->street) }}">
    </div>
    <div class="form-group col-md-3">
        <label>Número</label>
        <input name="number" id="admin-user-number" class="form-control" value="{{ old('number', $user->number) }}">
    </div>
    <div class="form-group col-md-4">
        <label>Complemento</label>
        <input name="complement" class="form-control" value="{{ old('complement', $user->complement) }}">
    </div>
    <div class="form-group col-md-3">
        <label>Bairro</label>
        <input name="neighborhood" id="admin-user-neighborhood" class="form-control" value="{{ old('neighborhood', $user->neighborhood) }}">
    </div>
    <div class="form-group col-md-3">
        <label>Cidade</label>
        <input name="city" id="admin-user-city" class="form-control" value="{{ old('city', $user->city) }}">
    </div>
    <div class="form-group col-md-2">
        <label>Estado</label>
        <input name="state" id="admin-user-state" class="form-control text-uppercase" maxlength="2" value="{{ old('state', $user->state) }}">
    </div>
</div>

<hr class="my-4">
<h5 class="font-weight-bold mb-3"><i class="fas fa-share-alt mr-2 text-primary"></i>Site, redes sociais e privacidade</h5>
<div class="form-row">
    @foreach([
        'website' => 'Site',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'twitter' => 'X / Twitter',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
    ] as $field => $label)
        <div class="form-group col-md-4">
            <label>{{ $label }}</label>
            <input name="{{ $field }}" class="form-control" value="{{ old($field, $user->{$field}) }}">
        </div>
    @endforeach
</div>
<div class="form-row">
    @foreach([
        'show_email_public' => 'Exibir e-mail no perfil público',
        'show_phone_public' => 'Exibir telefone no perfil público',
        'show_address_public' => 'Exibir endereço no perfil público',
        'hide_profile' => 'Ocultar perfil da comunidade',
    ] as $field => $label)
        <div class="form-group col-md-3">
            <div class="custom-control custom-switch mt-2">
                <input type="checkbox" class="custom-control-input" id="admin-user-{{ $field }}" name="{{ $field }}" value="1"
                    {{ old($field, (bool) $user->{$field}) ? 'checked' : '' }}>
                <label class="custom-control-label" for="admin-user-{{ $field }}">{{ $label }}</label>
            </div>
        </div>
    @endforeach
</div>
