@extends('admin.layouts.app')

@section('page_title', 'Usuários')
@section('breadcrumb')<li class="breadcrumb-item active">Usuários</li>@endsection

@section('content')
    @php
        $marketingUserId = (int) \App\Models\Setting::get('platform_marketing_user_id', 0);
        $marketingUser = $marketingUserId > 0 ? \App\Models\User::find($marketingUserId) : null;
        $isNewUsersTodayView = ($registered ?? '') === 'today';
        $createdAtLabel = !empty($createdAt) ? \Carbon\Carbon::parse($createdAt)->format('d/m/Y') : null;
    @endphp

    {{-- KPI Cards --}}
    <div class="row mb-4">
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text fw-bold">Total de Usuários</span>
                    <span class="info-box-number">{{ $totalUsers }}</span>
                    <span class="progress-description text-xs">Cadastrados na plataforma</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-danger elevation-1"><i class="fas fa-user-shield"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text fw-bold">Administradores</span>
                    <span class="info-box-number text-danger">{{ $totalAdmins }}</span>
                    <span class="progress-description text-xs">Admin e Super Admin</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-12">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-success elevation-1"><i class="fas fa-user-graduate"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text fw-bold">Membros</span>
                    <span class="info-box-number text-success">{{ $totalMembers }}</span>
                    <span class="progress-description text-xs">Usuários regulares</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Responsavel de Marketing --}}
    <div class="card card-outline card-info shadow-sm mb-4">
        <div class="card-header border-0 py-2">
            <h3 class="card-title fw-bold text-info" style="font-size: 0.95rem;">
                <i class="fas fa-bullhorn me-2"></i> Responsavel de Marketing da Plataforma
            </h3>
        </div>
        <div class="card-body py-3">
            @if($marketingUser)
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <span class="badge text-bg-success px-3 py-2 me-2" style="font-size: 11px;">
                            <i class="fas fa-check-circle me-1"></i> Ativo
                        </span>
                        <strong>{{ $marketingUser->name }}</strong>
                        <span class="text-muted ms-2">({{ $marketingUser->email }})</span>
                    </div>
                    <small class="text-muted">Recebe 10% de cada venda concluida (split "traffic")</small>
                </div>
            @else
                <p class="text-muted mb-0 small">
                    <i class="fas fa-info-circle me-1"></i>
                    Nenhum usuario designado. Clique no icone <i class="fas fa-bullhorn text-info"></i> ao lado de um usuario para definir o responsavel.
                </p>
            @endif
        </div>
    </div>

    @if($isNewUsersTodayView || $createdAtLabel)
        <div class="alert alert-primary shadow-sm d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div class="pe-3">
                <strong><i class="fas fa-user-clock me-2"></i>Filtro ativo:</strong>
                @if($isNewUsersTodayView)
                    exibindo apenas os novos usuários cadastrados hoje.
                @else
                    exibindo apenas os usuários cadastrados em {{ $createdAtLabel }}.
                @endif
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fas fa-times me-1"></i> Limpar filtro
            </a>
        </div>
    @endif

    {{-- Tabela principal --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title fw-bold">
                <i class="fas fa-users-cog me-2 text-primary"></i>Gerenciar Usuários
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 elevation-1" data-pjax="true">
                    <i class="fas fa-plus me-1"></i> Novo Usuário
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="border-0 ps-4">Nome</th>
                            <th class="border-0">E-mail</th>
                            <th class="border-0 text-center">Papel</th>
                            <th class="border-0 text-center">Nível</th>
                            <th class="border-0 text-center">Ingressos</th>
                            <th class="border-0 text-center" style="width:160px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-sm">{{ $user->name }}</span>
                                </td>
                                <td>
                                    <span class="text-muted text-sm">{{ $user->email }}</span>
                                    <span class="badge d-block mt-1 {{ $user->hasVerifiedEmail() ? 'text-bg-success' : 'text-bg-warning' }}" style="width:max-content;">
                                        <i class="fas {{ $user->hasVerifiedEmail() ? 'fa-check-circle' : 'fa-exclamation-circle' }} me-1"></i>
                                        {{ $user->hasVerifiedEmail() ? 'E-mail validado' : 'E-mail pendente' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($user->role === 'superadmin')
                                        <span class="badge text-bg-danger px-3 py-2" style="font-size:11px;">
                                            <i class="fas fa-crown me-1" style="font-size:9px;"></i>Super Admin
                                        </span>
                                    @elseif($user->role === 'admin')
                                        <span class="badge text-bg-warning px-3 py-2" style="font-size:11px;">
                                            <i class="fas fa-user-shield me-1" style="font-size:9px;"></i>Admin
                                        </span>
                                    @else
                                        <span class="badge text-bg-secondary px-3 py-2" style="font-size:11px;">
                                            <i class="fas fa-user me-1" style="font-size:9px;"></i>Membro
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-light border px-2 py-1">{{ ucfirst($user->level ?? 'Iniciante') }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-info px-2 py-1">
                                        <i class="fas fa-ticket-alt me-1" style="font-size:9px;"></i>
                                        {{ $user->checked_in_tickets_count }} / {{ $user->total_tickets_count }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex" style="gap:4px;">
                                        @if(auth()->user()->isAdmin() && $user->id !== auth()->id() && !session()->has('impersonator_id'))
                                            @if(!$user->isAdmin() || auth()->user()->role === 'superadmin')
                                                <a href="{{ route('admin.users.impersonate', $user) }}" class="btn btn-sm btn-outline-warning rounded-pill px-2"
                                                    title="Acessar como usuário" data-pjax="false"><i class="fas fa-user-secret"></i></a>
                                            @endif
                                        @endif
                                        @if(auth()->user()->isAdmin())
                                            <button type="button"
                                                class="btn btn-sm rounded-pill px-2 btn-toggle-marketing {{ $marketingUserId === $user->id ? 'btn-success' : 'btn-outline-info' }}"
                                                title="{{ $marketingUserId === $user->id ? 'Remover como Responsavel de Marketing' : 'Definir como Responsavel de Marketing' }}"
                                                data-url="{{ route('admin.users.marketing-manager', $user) }}"
                                                data-user-name="{{ $user->name }}"
                                                data-has-pix="{{ filled($user->pix_key) ? '1' : '0' }}"
                                                data-is-current="{{ $marketingUserId === $user->id ? '1' : '0' }}">
                                                <i class="fas fa-bullhorn"></i>
                                            </button>
                                        @endif
                                        @if(!$user->hasVerifiedEmail())
                                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 btn-verify-email"
                                                title="Validar e-mail manualmente"
                                                data-url="{{ route('admin.users.verify-email', $user) }}"
                                                data-name="{{ $user->name }}">
                                                <i class="fas fa-envelope-circle-check"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Editar"
                                            data-pjax="true"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-delete" title="Excluir"
                                            data-action="{{ route('admin.users.destroy', $user) }}"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="text-muted text-sm">
                        Total na consulta: <b>{{ number_format($totalUsers, 0, ',', '.') }}</b> usuários.
                    </div>
                    <div>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-pjax="true">
                            <i class="fas fa-plus me-1"></i> Novo Usuário
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables Local -->
    <link rel="stylesheet" href="{{ cdn_asset('assets/admin/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ cdn_asset('assets/admin/datatables/responsive.bootstrap4.min.css') }}">
    <style>
        .align-middle td { vertical-align: middle !important; }
        #example1_wrapper .dataTables_paginate {
            display: flex;
            justify-content: center;
            padding: 0.75rem;
        }
        #example1_wrapper .dataTables_info {
            margin: 0.95rem 0 0 0.75rem;
            color: #6c757d;
            font-size: 0.875rem;
        }
    </style>
@endpush

@push('scripts')
    <!-- DataTables Server-Side Local -->
    <script src="{{ cdn_asset('assets/admin/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ cdn_asset('assets/admin/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ cdn_asset('assets/admin/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ cdn_asset('assets/admin/datatables/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(function () {
            $("#example1").DataTable({
                "processing": true,
                "serverSide": true,
                "deferLoading": {{ (int) $totalUsers }},
                "ajax": {
                    "url": @json(route('admin.users.data')),
                    "data": function (d) {
                        d.search_filter = @json($search ?? '');
                        d.registered = @json($registered ?? '');
                        d.created_at = @json($createdAt ?? '');
                    }
                },
                "columns": [
                    { "data": "name" },
                    { "data": "email" },
                    { "data": "role", "className": "text-center" },
                    { "data": "level", "className": "text-center" },
                    { "data": "tickets", "className": "text-center" },
                    { "data": "actions", "orderable": false, "searchable": false, "className": "text-center" }
                ],
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                "pageLength": 15,
                "order": [[0, "asc"]],
                "language": {
                    "url": "{{ cdn_asset('assets/admin/datatables/pt-BR.json') }}",
                    "emptyTable": "Nenhum usuário encontrado."
                }
            });

            // Marketing Manager toggle
            $(document).on('click', '.btn-toggle-marketing', function () {
                var btn = $(this);
                var url = btn.data('url');
                var userName = btn.data('user-name');
                var isCurrent = btn.data('is-current') === 1 || btn.data('is-current') === '1';
                var action = isCurrent ? 'unset' : 'set';
                var requiresPix = !isCurrent && String(btn.data('has-pix')) !== '1';

                var confirmText = isCurrent
                    ? 'Remover ' + userName + ' como Responsavel de Marketing?'
                    : 'Definir ' + userName + ' como Responsavel de Marketing? Um email e uma notificacao serao enviados.';

                if (typeof Swal !== 'undefined') {
                    var options = {
                        title: isCurrent ? 'Remover Responsavel?' : 'Definir Responsavel?',
                        text: confirmText,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: isCurrent ? 'Sim, remover' : 'Sim, definir',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: isCurrent ? '#dc3545' : '#17a2b8',
                    };
                    if (requiresPix) {
                        options.input = 'text';
                        options.inputLabel = 'Chave PIX obrigatória para recebimentos';
                        options.inputPlaceholder = 'E-mail, CPF, telefone ou chave aleatória';
                        options.inputValidator = function (value) {
                            return value && value.trim() ? null : 'Informe a chave PIX.';
                        };
                    }
                    Swal.fire(options).then(function (result) {
                        if (!result.isConfirmed) return;
                        performToggle(result.value || '');
                    });
                }

                function performToggle(pixKey) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            action: action,
                            pix_key: pixKey,
                        },
                    }).done(function (resp) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(resp.message || 'Atualizado.');
                        }
                        setTimeout(function () { location.reload(); }, 800);
                    }).fail(function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Erro ao atualizar.';
                        if (typeof toastr !== 'undefined') toastr.error(msg);
                    });
                }
            });

            $(document).on('click', '.btn-verify-email', function () {
                var button = $(this);
                var execute = function () {
                    $.post(button.data('url'), { _token: '{{ csrf_token() }}' })
                        .done(function (response) {
                            if (typeof toastr !== 'undefined') toastr.success(response.message);
                            setTimeout(function () { window.location.reload(); }, 500);
                        })
                        .fail(function (xhr) {
                            var message = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'Não foi possível validar o e-mail.';
                            if (typeof toastr !== 'undefined') toastr.error(message);
                        });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Validar e-mail?',
                        text: 'O membro poderá realizar compras sem abrir o link de confirmação.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, validar',
                        cancelButtonText: 'Cancelar'
                    }).then(function (result) { if (result.isConfirmed) execute(); });
                }
            });
        });
    </script>
@endpush
