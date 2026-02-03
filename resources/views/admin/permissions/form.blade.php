@extends('admin.layouts.app')

@section('page_title', $role->exists ? 'Editar papel' : 'Novo papel')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissões</a></li>
<li class="breadcrumb-item active">{{ $role->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $role->exists ? route('admin.permissions.update',$role) : route('admin.permissions.store') }}" class="ajax-form">
        @csrf
        @if($role->exists) @method('PUT') @endif
        <div class="form-group mb-3"><label>Nome (slug)</label><input name="name" class="form-control" value="{{ old('name',$role->name) }}" required></div>
        <div class="form-group mb-3"><label>Rótulo</label><input name="label" class="form-control" value="{{ old('label',$role->label) }}"></div>
        @php
            $desc = [
                'dashboard.view' => 'Ver o painel inicial.',
                'users.view' => 'Listar usuários.',
                'users.create' => 'Criar usuários.',
                'users.edit' => 'Editar usuários.',
                'users.delete' => 'Excluir usuários.',
                'users.impersonate' => 'Assumir a sessão de um usuário.',
                'courses.view' => 'Listar cursos.',
                'courses.create' => 'Criar cursos.',
                'courses.edit' => 'Editar cursos.',
                'courses.delete' => 'Excluir cursos.',
                'courses.publish' => 'Publicar/arquivar cursos.',
                'mentorships.view' => 'Ver mentorias.',
                'mentorships.create' => 'Criar mentorias.',
                'mentorships.edit' => 'Editar mentorias.',
                'mentorships.delete' => 'Excluir mentorias.',
                'mentorships.schedule' => 'Agendar sessão de mentoria.',
                'events.view' => 'Listar eventos.',
                'events.create' => 'Criar eventos.',
                'events.edit' => 'Editar eventos.',
                'events.delete' => 'Excluir eventos.',
                'events.publish' => 'Publicar/encerrar eventos.',
                'events.ticket.manage' => 'Gerenciar ingressos/participações.',
                'plans.view' => 'Listar planos.',
                'plans.create' => 'Criar planos.',
                'plans.edit' => 'Editar planos.',
                'plans.delete' => 'Excluir planos.',
                'plans.feature.toggle' => 'Destacar/ocultar planos.',
                'plans.discount.manage' => 'Gerenciar descontos de planos.',
                'certificates.generate' => 'Gerar certificados.',
                'certificates.view' => 'Listar certificados.',
                'certificates.delete' => 'Excluir certificados.',
                'points.rules.manage' => 'Gerenciar regras de pontuação.',
                'ranking.view' => 'Ver ranking.',
                'ranking.edit' => 'Editar ranking.',
                'mailtemplates.view' => 'Listar templates de e-mail.',
                'mailtemplates.create' => 'Criar templates.',
                'mailtemplates.edit' => 'Editar templates.',
                'mailtemplates.delete' => 'Excluir templates.',
                'mail.sendtest' => 'Enviar e-mail de teste.',
                'uploads.manage' => 'Gerenciar uploads/arquivos.',
                'settings.view' => 'Ver configurações.',
                'settings.update' => 'Atualizar configurações.',
                'settings.smtp.test' => 'Testar SMTP.',
                'settings.pwa.toggle' => 'Ativar/desativar PWA.',
                'settings.branding.update' => 'Atualizar branding (logo/preloader).',
                'permissions.view' => 'Listar permissões.',
                'permissions.assign' => 'Atribuir permissões a papéis/usuários.',
                'permissions.sync' => 'Sincronizar permissões.',
                'roles.manage' => 'Gerenciar papéis (criar/editar/excluir).',
            ];
        @endphp
        <div class="form-group mb-3">
            <label>Permissões</label>
            <div class="row">
            @foreach($permissions as $p)
                <div class="col-md-4 mb-2">
                    @php $d = $desc[$p->name] ?? 'Sem descrição'; @endphp
                    <label class="mb-0" title="{{ $d }}">
                        <input type="checkbox" name="permissions[]" value="{{ $p->id }}" {{ $role->permissions->contains($p->id) ? 'checked' : '' }}>
                        {{ $p->name }}
                        <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="top" title="{{ $d }}"></i>
                    </label>
                </div>
            @endforeach
            </div>
        </div>
        <button class="btn btn-primary">Salvar</button>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary" data-pjax="true">Voltar</a>
    </form>
  </div>
</div>
@endsection
