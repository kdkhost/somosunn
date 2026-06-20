@extends('admin.layouts.app')

@section('title', $company->exists ? 'Editar Empresa' : 'Nova Empresa')

@php
    $members = old('members', $company->memberships->map(fn($membership) => ['user_id' => $membership->user_id, 'role' => $membership->role])->values()->all() ?: [['user_id' => '', 'role' => 'owner']]);
@endphp

@section('content')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">{{ $company->exists ? 'Editar empresa' : 'Nova empresa' }}</h1>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <form method="POST" enctype="multipart/form-data" action="{{ $company->exists ? route('admin.companies.update', $company) : route('admin.companies.store') }}">
                @csrf
                @if($company->exists) @method('PUT') @endif
                <div class="card card-outline card-primary">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nome</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control" value="{{ old('slug', $company->slug) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>CNPJ</label>
                                <input type="text" name="cnpj" class="form-control" value="{{ old('cnpj', $company->cnpj) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>E-mail</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Telefone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>WhatsApp</label>
                                <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $company->whatsapp) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Cidade</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $company->city) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Estado</label>
                                <input type="text" name="state" class="form-control" value="{{ old('state', $company->state) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Site</label>
                                <input type="url" name="website" class="form-control" value="{{ old('website', $company->website) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Instagram</label>
                                <input type="url" name="instagram" class="form-control" value="{{ old('instagram', $company->instagram) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>LinkedIn</label>
                                <input type="url" name="linkedin" class="form-control" value="{{ old('linkedin', $company->linkedin) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>YouTube</label>
                                <input type="url" name="youtube" class="form-control" value="{{ old('youtube', $company->youtube) }}">
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Descricao</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description', $company->description) }}</textarea>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Logo</label>
                                <input type="file" name="logo" class="form-control-file">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Banner</label>
                                <input type="file" name="banner" class="form-control-file">
                            </div>
                            <div class="col-md-12">
                                <hr>
                                <h5>Equipe vinculada</h5>
                            </div>
                            @foreach($members as $index => $member)
                                <div class="col-md-8 form-group">
                                    <label>Membro {{ $index + 1 }}</label>
                                    <select name="members[{{ $index }}][user_id]" class="form-control">
                                        <option value="">Selecione</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected((string) data_get($member, 'user_id') === (string) $user->id)>{{ $user->name }} - {{ $user->email }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Papel</label>
                                    <select name="members[{{ $index }}][role]" class="form-control">
                                        @foreach(['owner' => 'Owner', 'manager' => 'Manager', 'staff' => 'Staff'] as $value => $label)
                                            <option value="{{ $value }}" @selected(data_get($member, 'role', 'staff') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                            <div class="col-md-12">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="verified" name="verified" value="1" @checked(old('verified', $company->verified))>
                                    <label class="custom-control-label" for="verified">Empresa verificada</label>
                                </div>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" @checked(old('active', $company->active ?? true))>
                                    <label class="custom-control-label" for="active">Empresa ativa</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">Salvar empresa</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
