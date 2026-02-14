@extends('admin.layouts.app')

@section('page_title', $user->exists ? 'Editar usuário' : 'Novo usuário')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Usuários</a></li>
    <li class="breadcrumb-item active">{{ $user->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
                class="ajax-form">
                @csrf
                @if($user->exists) @method('PUT') @endif
                <div class="form-group mb-3"><label>Nome</label><input name="name" class="form-control"
                        value="{{ old('name', $user->name) }}" required></div>
                <div class="form-group mb-3"><label>E-mail</label><input name="email" type="email" class="form-control"
                        value="{{ old('email', $user->email) }}" required></div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Senha @if($user->exists)<small class="text-muted">(deixe em branco para não alterar)</small>@endif</label>
                        <input name="password" type="password" class="form-control">
                    </div>
                    
                    @if(auth()->id() !== $user->id)
                        <div class="form-group col-md-3">
                            <label>Papel</label>
                            <select name="role" class="form-control">
                                <option value="member" {{ (old('role', $user->role) ?? 'member') == 'member' ? 'selected' : '' }}>Membro</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                                @if(auth()->user()->role === 'superadmin')
                                <option value="superadmin" {{ old('role', $user->role) == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Nível</label>
                            <select name="level" class="form-control">
                                @php
                                    $levels = ['iniciante', 'intermediario', 'avancado', 'sucesso'];
                                    // Apenas superadmin pode ver/atribuir o nível superadmin
                                    if (auth()->user()->role === 'superadmin') {
                                        $levels[] = 'superadmin';
                                    }
                                @endphp
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl }}" {{ (old('level', $user->level) ?? 'iniciante') == $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="form-group col-md-6 d-flex align-items-center mt-4">
                            <p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> Papel e Nível não podem ser alterados no próprio perfil.</p>
                        </div>
                    @endif
                </div>
        </div>

        @if(auth()->user()->isAdmin())
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title"><i class="fas fa-crown mr-1"></i> Gestão de Plano Manual</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Plano Atribuído</label>
                            <select name="plan_id" class="form-control">
                                <option value="">Nenhum plano (Usar assinaturas)</option>
                                @foreach(\App\Models\Plan::orderBy('price')->get() as $p)
                                    <option value="{{ $p->id }}" {{ old('plan_id', $user->plan_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Expiração do Acesso</label>
                            <input type="date" name="plan_expires_at" class="form-control"
                                value="{{ old('plan_expires_at', $user->plan_expires_at ? $user->plan_expires_at->format('Y-m-d') : '') }}">
                            <small class="text-muted">Deixe vazio para acesso ilimitado.</small>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $userFeatures = $userFeatures ?? [];
                $selectedFeatures = old('extra_features', $user->extra_features ?? []);
                if (!is_array($selectedFeatures)) {
                    $selectedFeatures = [];
                }
            @endphp

            <div class="card mt-4 border-success">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title"><i class="fas fa-unlock-alt mr-1"></i> Recursos Individuais (Extras)</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Libere recursos específicos para este usuário, independente do plano atribuído.
                        Esses recursos se somam aos do plano.
                    </p>
                    
                    @php
                        // Agrupar features por categoria
                        $featureGroups = [
                            'Acesso Básico' => ['community', 'chat', 'connections', 'connections.unlimited'],
                            'Cursos' => ['courses', 'courses.certificates', 'courses.downloads'],
                            'Eventos' => ['events', 'events.recordings', 'events.vip'],
                            'Mentorias' => ['mentorships', 'mentorships.group', 'mentorships.individual'],
                            'Extras' => ['rankings', 'support.priority', 'early.access'],
                        ];
                    @endphp

                    @foreach($featureGroups as $groupName => $groupKeys)
                        <h6 class="font-weight-bold text-success mb-2 {{ !$loop->first ? 'mt-3' : '' }}">
                            <i class="fas fa-folder mr-1"></i> {{ $groupName }}
                        </h6>
                        <div class="row mb-2">
                            @foreach($groupKeys as $featureKey)
                                @if(isset($userFeatures[$featureKey]))
                                    <div class="col-md-4 col-lg-3">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="extra-feature-{{ $featureKey }}"
                                                name="extra_features[]" value="{{ $featureKey }}"
                                                {{ in_array($featureKey, $selectedFeatures, true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="extra-feature-{{ $featureKey }}">{{ $userFeatures[$featureKey] }}</label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-4">
            <button class="btn btn-primary btn-lg px-5">Salvar Alterações</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-lg" data-pjax="true">Cancelar</a>
        </div>
        </form>
    </div>
    </div>
@endsection