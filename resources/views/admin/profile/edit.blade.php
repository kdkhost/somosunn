@extends('admin.layouts.app')

@section('page_title', 'Meu Perfil')
@section('breadcrumb')
    <li class="breadcrumb-item active">Meu Perfil</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <!-- Profile Sidebar -->
    <div class="col-md-3">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center mb-3" id="profile-photo-preview">
                    @if($user->photo)
                        <img class="profile-user-img img-fluid img-circle" src="{{ asset($user->photo) }}" alt="Avatar" id="current-photo" style="width: 100px; height: 100px; object-fit: cover;">
                    @else
                        <div class="profile-user-img img-fluid img-circle d-flex align-items-center justify-content-center bg-light text-primary font-weight-bold" 
                             style="width:100px;height:100px;font-size:2rem;margin:0 auto;border:3px solid #adb5bd;" id="current-photo">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>

                <h3 class="profile-username text-center">{{ $user->name }}</h3>
                <p class="text-muted text-center">
                    {{ $user->role === 'superadmin' ? 'Super Administrador' : ($user->role === 'admin' ? 'Administrador' : 'Membro') }}
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Pontos</b> <a class="float-right">{{ number_format($user->points ?? 0, 0, ',', '.') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Nível</b> <a class="float-right badge badge-info">{{ ucfirst($user->level ?? 'Iniciante') }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="col-md-9">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills" id="profileTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="personal-tab" data-toggle="pill" href="#personal" role="tab" aria-controls="personal" aria-selected="true">
                            <i class="fas fa-user mr-1"></i> Dados Pessoais
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="address-tab" data-toggle="pill" href="#address" role="tab" aria-controls="address" aria-selected="false">
                            <i class="fas fa-map-marker-alt mr-1"></i> Endereço
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="social-tab" data-toggle="pill" href="#social" role="tab" aria-controls="social" aria-selected="false">
                            <i class="fas fa-share-alt mr-1"></i> Redes Sociais
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="privacy-tab" data-toggle="pill" href="#privacy" role="tab" aria-controls="privacy" aria-selected="false">
                            <i class="fas fa-lock mr-1"></i> Privacidade
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <form class="ajax-form" method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" id="profile-form">
                    @csrf
                    <div class="tab-content" id="profileTabsContent">
                        
                        <!-- Dados Pessoais Tab -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-3 col-form-label">Nome Completo <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="inputName" name="name" value="{{ $user->name }}" required>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="inputEmail" class="col-sm-3 col-form-label">E-mail <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" id="inputEmail" name="email" value="{{ $user->email }}" required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputPhone" class="col-sm-3 col-form-label">Telefone</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control mask-phone" id="inputPhone" name="phone" value="{{ $user->phone }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputDoc" class="col-sm-3 col-form-label">CPF/CNPJ</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control mask-cpf-cnpj" id="inputDoc" name="doc" value="{{ $user->doc }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputOccupation" class="col-sm-3 col-form-label">Cargo/Função</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="inputOccupation" name="occupation" value="{{ $user->occupation ?? '' }}" placeholder="Ex: CEO, Desenvolvedor...">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputCompany" class="col-sm-3 col-form-label">Empresa</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="inputCompany" name="company" value="{{ $user->company ?? '' }}" placeholder="Nome da sua empresa">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputSegment" class="col-sm-3 col-form-label">Segmento/Nicho</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="inputSegment" name="segment" value="{{ $user->segment ?? '' }}" placeholder="Ex: Tecnologia, Saude, Educacao">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputInterests" class="col-sm-3 col-form-label">Interesses</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" id="inputInterests" name="interests" rows="3" placeholder="Ex: marketing, vendas, ecommerce, inovacao">{{ $user->interests ?? '' }}</textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Foto de Perfil</label>
                                <div class="col-sm-9">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="photo-upload" name="photo" accept="image/*">
                                        <label class="custom-file-label" for="photo-upload">Escolher foto...</label>
                                    </div>
                                    <small class="text-muted d-block mt-1">Recomendado: 500x500px (JPG/PNG, máx: 2MB)</small>
                                    
                                    <!-- Progress Bar -->
                                    <div class="progress mt-2" id="upload-progress" style="height: 4px; display: none;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                             role="progressbar" style="width: 0%"></div>
                                    </div>
                                    
                                    <!-- Preview -->
                                    <div id="photo-preview" class="mt-2" style="{{ $user->photo ? '' : 'display: none;' }}">
                                        <img src="{{ $user->photo ? asset($user->photo) : '' }}" class="img-thumbnail" style="max-height: 150px;">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Foto de Capa</label>
                                <div class="col-sm-9">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="cover-upload" name="cover_photo" accept="image/*">
                                        <label class="custom-file-label" for="cover-upload">Escolher capa...</label>
                                    </div>
                                    <small class="text-muted d-block mt-1">Recomendado: 1200x300px (JPG/PNG)</small>
                                    
                                    <!-- Progress Bar Capa -->
                                    <div class="progress mt-2" id="cover-upload-progress" style="height: 4px; display: none;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                             role="progressbar" style="width: 0%"></div>
                                    </div>
                                    
                                    <!-- Preview Capa -->
                                    <div id="cover-preview" class="mt-2" style="{{ $user->cover_photo ? '' : 'display: none;' }}">
                                        <img src="{{ $user->cover_photo ? asset($user->cover_photo) : '' }}" class="img-fluid rounded" style="max-height: 150px; width: 100%; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="inputBio" class="col-sm-3 col-form-label">Sobre Mim</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" id="inputBio" name="bio" rows="4" placeholder="Conte um pouco sobre você...">{{ $user->bio }}</textarea>
                                </div>
                            </div>

                            <hr>

                            <div class="alert alert-light border">
                                <i class="fas fa-lock mr-2 text-warning"></i> 
                                <span class="text-muted">Deixe os campos de senha em branco para manter a atual.</span>
                            </div>

                            <div class="form-group row">
                                <label for="inputPassword" class="col-sm-3 col-form-label">Nova Senha</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control" id="inputPassword" name="password" minlength="6">
                                </div>
                            </div>
                            
                             <div class="form-group row">
                                <label for="inputPasswordConf" class="col-sm-3 col-form-label">Confirmar Senha</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control" id="inputPasswordConf" name="password_confirmation" minlength="6">
                                </div>
                            </div>
                        </div>

                        <!-- Endereço Tab -->
                        <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
                            <div class="form-group row">
                                <label for="inputCep" class="col-sm-3 col-form-label">CEP</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control mask-cep" id="inputCep" name="cep" value="{{ $user->cep }}">
                                    <small class="text-muted">Digite o CEP para preencher automaticamente</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputStreet" class="col-sm-3 col-form-label">Rua/Avenida</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="inputStreet" name="street" value="{{ $user->street }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputNumber" class="col-sm-3 col-form-label">Número</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control" id="inputNumber" name="number" value="{{ $user->number }}">
                                </div>
                                <label for="inputComplement" class="col-sm-2 col-form-label text-right">Complemento</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" id="inputComplement" name="complement" value="{{ $user->complement }}" placeholder="Apto, Bloco...">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputNeighborhood" class="col-sm-3 col-form-label">Bairro</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="inputNeighborhood" name="neighborhood" value="{{ $user->neighborhood }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputCity" class="col-sm-3 col-form-label">Cidade</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="inputCity" name="city" value="{{ $user->city }}">
                                </div>
                                <label for="inputState" class="col-sm-1 col-form-label text-right">UF</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control text-uppercase" id="inputState" name="state" value="{{ $user->state }}" maxlength="2">
                                </div>
                            </div>
                        </div>

                        <!-- Redes Sociais Tab -->
                        <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
                            <p class="text-muted mb-3">
                                <i class="fas fa-info-circle"></i> 
                                Adicione seus perfis nas redes sociais para que outros membros possam te encontrar.
                            </p>

                            <div class="form-group row">
                                <label for="inputWebsite" class="col-sm-3 col-form-label"><i class="fas fa-globe mr-2"></i>Website</label>
                                <div class="col-sm-9">
                                    <input type="url" class="form-control" id="inputWebsite" name="website" value="{{ $user->website }}" placeholder="https://seusite.com">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputFacebook" class="col-sm-3 col-form-label"><i class="fab fa-facebook mr-2 text-primary"></i>Facebook</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="inputFacebook" name="facebook" value="{{ $user->facebook }}" placeholder="facebook.com/seuperfil">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputInstagram" class="col-sm-3 col-form-label"><i class="fab fa-instagram mr-2 text-danger"></i>Instagram</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="inputInstagram" name="instagram" value="{{ $user->instagram }}" placeholder="@seuusuario">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputTwitter" class="col-sm-3 col-form-label"><i class="fab fa-twitter mr-2 text-info"></i>Twitter</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="inputTwitter" name="twitter" value="{{ $user->twitter }}" placeholder="@seuusuario">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputLinkedin" class="col-sm-3 col-form-label"><i class="fab fa-linkedin mr-2 text-primary"></i>LinkedIn</label>
                                <div class="col-sm-9">
                                    <input type="url" class="form-control" id="inputLinkedin" name="linkedin" value="{{ $user->linkedin }}" placeholder="linkedin.com/in/seuusuario">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputYoutube" class="col-sm-3 col-form-label"><i class="fab fa-youtube mr-2 text-danger"></i>YouTube</label>
                                <div class="col-sm-9">
                                    <input type="url" class="form-control" id="inputYoutube" name="youtube" value="{{ $user->youtube }}" placeholder="youtube.com/c/seucanal">
                                </div>
                            </div>
                        </div>

                        <!-- Privacidade Tab -->
                        <div class="tab-pane fade" id="privacy" role="tabpanel" aria-labelledby="privacy-tab">
                            <p class="text-muted mb-3">
                                <i class="fas fa-shield-alt"></i> 
                                Escolha quais informações outros membros podem ver no seu perfil público.
                            </p>

                            <div class="form-group">
                                <div class="custom-control custom-switch custom-switch-lg">
                                    <input type="checkbox" class="custom-control-input" id="hideProfile" name="hide_profile" 
                                           {{ $user->hide_profile ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="hideProfile">
                                        <strong>Restringir perfil apenas para contatos</strong>
                                        <small class="d-block text-muted">Nao conectado vera somente uma mensagem de perfil restrito</small>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch custom-switch-lg">
                                    <input type="checkbox" class="custom-control-input" id="showEmail" name="show_email_public" 
                                           {{ $user->show_email_public ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="showEmail">
                                        <strong>Exibir e-mail publicamente</strong>
                                        <small class="d-block text-muted">Outros membros poderão ver seu endereço de e-mail</small>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch custom-switch-lg">
                                    <input type="checkbox" class="custom-control-input" id="showPhone" name="show_phone_public" 
                                           {{ $user->show_phone_public ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="showPhone">
                                        <strong>Exibir telefone publicamente</strong>
                                        <small class="d-block text-muted">Outros membros poderão ver seu número de telefone</small>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch custom-switch-lg">
                                    <input type="checkbox" class="custom-control-input" id="showAddress" name="show_address_public" 
                                           {{ $user->show_address_public ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="showAddress">
                                        <strong>Exibir endereço publicamente</strong>
                                        <small class="d-block text-muted">Outros membros poderão ver sua cidade e estado</small>
                                    </label>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Atenção:</strong> Suas redes sociais sempre serão públicas quando preenchidas.
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group row">
                        <div class="col-sm-12 text-right">
                            <button type="submit" class="btn btn-primary btn-lg px-5" id="btn-save">
                                <i class="fas fa-save mr-2"></i>Salvar Alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    // Inicialização da aba padrão (caso Bootstrap não pegue sozinho)
    // $('#profileTabs a:first').tab('show');

    // Máscaras
    $('.mask-phone').mask('(00) 00000-0000');
    $('.mask-cep').mask('00000-000');
    $('.mask-cpf-cnpj').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length <= 11) {
            $(this).mask('000.000.000-00', {reverse: true});
        } else {
            $(this).mask('00.000.000/0000-00', {reverse: true});
        }
    }).trigger('input');

    // Custom File Label Input
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Preview de foto
    $('#photo-upload').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photo-preview img').attr('src', e.target.result);
                $('#photo-preview').fadeIn();
                
                // Atualiza foto da sidebar
                if ($('#current-photo').is('img')) {
                    $('#current-photo').attr('src', e.target.result);
                } else {
                    // Update div to img
                    const src = e.target.result;
                    const container = $('#profile-photo-preview');
                    container.html(`<img class="profile-user-img img-fluid img-circle" src="${src}" alt="Avatar" id="current-photo" style="width: 100px; height: 100px; object-fit: cover;">`);
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // Preview de capa
    $('#cover-upload').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#cover-preview img').attr('src', e.target.result);
                $('#cover-preview').fadeIn();
            };
            reader.readAsDataURL(file);
        }
    });

    // ViaCEP
    $('#inputCep').on('blur', function() {
        const cep = $(this).val().replace(/\D/g, '');
        if (cep.length === 8) {
            // Usa apenas a barra de progresso do endereço ou global se houvesse, aqui usamos a de perfil como fallback
            toastr.info('Buscando endereço...');
            
            $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(data) {
                if (!data.erro) {
                    $('#inputStreet').val(data.logradouro);
                    $('#inputNeighborhood').val(data.bairro);
                    $('#inputCity').val(data.localidade);
                    $('#inputState').val(data.uf);
                    toastr.success('Endereço encontrado!');
                } else {
                    toastr.error('CEP não encontrado.');
                }
            }).fail(function() {
                toastr.error('Erro ao buscar CEP.');
            });
        }
    });

    // Form Submit com Progress
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#btn-save');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...');
        
        const formData = new FormData(this);
        const hasPhoto = $('#photo-upload').get(0).files.length > 0;
        const hasCover = $('#cover-upload').get(0).files.length > 0;

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        
                        // Atualiza barras de progresso relevantes
                        if (hasPhoto) {
                            $('#upload-progress').show();
                            $('#upload-progress .progress-bar').css('width', percentComplete + '%');
                        }
                        if (hasCover) {
                            $('#cover-upload-progress').show();
                            $('#cover-upload-progress .progress-bar').css('width', percentComplete + '%');
                        }
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                toastr.success(response.message || 'Perfil atualizado com sucesso!');
                
                // Atualiza Avataa
                if(response.photo_url) {
                    if ($('#current-photo').is('img')) {
                        $('#current-photo').attr('src', response.photo_url);
                    } else {
                       $('#profile-photo-preview').html(`<img class="profile-user-img img-fluid img-circle" src="${response.photo_url}" alt="Avatar" id="current-photo" style="width: 100px; height: 100px; object-fit: cover;">`);
                    }
                }

                // Atualiza Capa
                if(response.cover_url) {
                     $('#cover-preview img').attr('src', response.cover_url);
                     $('#cover-preview').show();
                }

                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalText);
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let msg = '';
                    $.each(xhr.responseJSON.errors, (k,v) => msg += v[0]+'<br>');
                    toastr.error(msg);
                } else {
                    toastr.error('Erro ao salvar perfil.');
                }
            },
            complete: function() {
                setTimeout(() => {
                    $('#upload-progress').fadeOut();
                    $('#cover-upload-progress').fadeOut();
                }, 500);
            }
        });
    });
});
</script>
@endpush
