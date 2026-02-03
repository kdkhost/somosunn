@extends('admin.layouts.app')

@section('page_title', 'Meu Perfil')
@section('breadcrumb')
    <li class="breadcrumb-item active">Meu Perfil</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <!-- Profile Image & Bio -->
    <div class="col-md-3">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center mb-3">
                    @if($user->photo)
                        <img class="profile-user-img img-fluid img-circle" src="{{ asset('storage/'.$user->photo) }}" alt="Avatar">
                    @else
                        <div class="profile-user-img img-fluid img-circle d-flex align-items-center justify-content-center bg-light text-primary font-weight-bold" style="width:100px;height:100px;font-size:2rem;margin:0 auto;border:3px solid #adb5bd;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>

                <h3 class="profile-username text-center">{{ $user->name }}</h3>
                <p class="text-muted text-center">{{ $user->role === 'superadmin' ? 'Super Administrador' : ($user->role === 'admin' ? 'Administrador' : 'Membro') }}</p>

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
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Dados Pessoais</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="active tab-pane" id="settings">
                        <form class="form-horizontal ajax-form" method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 col-form-label">Nome Completo</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="inputName" name="name" value="{{ $user->name }}" required>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="inputEmail" class="col-sm-2 col-form-label">E-mail</label>
                                <div class="col-sm-10">
                                    <input type="email" class="form-control" id="inputEmail" name="email" value="{{ $user->email }}" required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputPhone" class="col-sm-2 col-form-label">Telefone</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control mask-phone" id="inputPhone" name="phone" value="{{ $user->phone }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputDoc" class="col-sm-2 col-form-label">CPF/CNPJ</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control mask-cpf-cnpj" id="inputDoc" name="doc" value="{{ $user->doc }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Foto de Perfil</label>
                                <div class="col-sm-10">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="customFile" name="photo" accept="image/*">
                                        <label class="custom-file-label" for="customFile">Escolher arquivo</label>
                                    </div>
                                    <small class="text-muted">Recomendado: 500x500px (JPG/PNG)</small>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="form-group row">
                                <label for="inputCep" class="col-sm-2 col-form-label">CEP</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control mask-cep" id="inputCep" name="cep" value="{{ $user->cep }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputAddress" class="col-sm-2 col-form-label">Endereço</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" id="inputAddress" name="address" rows="2" placeholder="Rua, número, bairro...">{{ $user->address }}</textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputBio" class="col-sm-2 col-form-label">Sobre Mim (Bio)</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" id="inputBio" name="bio" rows="3" placeholder="Conte um pouco sobre você...">{{ $user->bio }}</textarea>
                                </div>
                            </div>

                            <div class="alert alert-light border">
                                <i class="fas fa-lock mr-2 text-warning"></i> 
                                <span class="text-muted">Deixe os campos de senha em branco para manter a atual.</span>
                            </div>

                            <div class="form-group row">
                                <label for="inputPassword" class="col-sm-2 col-form-label">Nova Senha</label>
                                <div class="col-sm-10">
                                    <input type="password" class="form-control" id="inputPassword" name="password" minlength="6">
                                </div>
                            </div>
                            
                             <div class="form-group row">
                                <label for="inputPasswordConf" class="col-sm-2 col-form-label">Confirmar Senha</label>
                                <div class="col-sm-10">
                                    <input type="password" class="form-control" id="inputPasswordConf" name="password_confirmation" minlength="6">
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="offset-sm-2 col-sm-10">
                                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Preview de nome de arquivo
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>
@endpush
