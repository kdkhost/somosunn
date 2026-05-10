@extends('admin.layouts.app')

@section('page_title', 'Usuários')
@section('breadcrumb')<li class="breadcrumb-item active">Usuários</li>@endsection

@section('content')
    @php
        $totalUsers = $users->count();
        $totalAdmins = $users->whereIn('role', ['admin', 'superadmin'])->count();
        $totalMembers = $users->where('role', 'member')->count() + $users->whereNull('role')->count();
        $marketingUserId = (int) \App\Models\Setting::get('platform_marketing_user_id', 0);
        $marketingUser = $marketingUserId > 0 ? \App\Models\User::find($marketingUserId) : null;
    @endphp

    {{-- KPI Cards --}}
    <div class="row mb-4">
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Total de Usuários</span>
                    <span class="info-box-number">{{ $totalUsers }}</span>
                    <span class="progress-description text-xs">Cadastrados na plataforma</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-danger elevation-1"><i class="fas fa-user-shield"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Administradores</span>
                    <span class="info-box-number text-danger">{{ $totalAdmins }}</span>
                    <span class="progress-description text-xs">Admin e Super Admin</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-12">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-success elevation-1"><i class="fas fa-user-graduate"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Membros</span>
                    <span class="info-box-number text-success">{{ $totalMembers }}</span>
                    <span class="progress-description text-xs">Usuários regulares</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Responsavel de Marketing --}}
    <div class="card card-outline card-info shadow-sm mb-4">
        <div class="card-header border-0 py-2">
            <h3 class="card-title font-weight-bold text-info" style="font-size: 0.95rem;">
                <i class="fas fa-bullhorn mr-2"></i> Responsavel de Marketing da Plataforma
            </h3>
        </div>
        <div class="card-body py-3">
            @if($marketingUser)
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <span class="badge badge-success px-3 py-2 mr-2" style="font-size: 11px;">
                            <i class="fas fa-check-circle mr-1"></i> Ativo
                        </span>
                        <strong>{{ $marketingUser->name }}</strong>
                        <span class="text-muted ml-2">({{ $marketingUser->email }})</span>
                    </div>
                    <small class="text-muted">Recebe 10% de cada venda concluida (split "traffic")</small>
                </div>
            @else
                <p class="text-muted mb-0 small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Nenhum usuario designado. Clique no icone <i class="fas fa-bullhorn text-info"></i> ao lado de um usuario para definir o responsavel.
                </p>
            @endif
        </div>
    </div>

    {{-- Tabela principal --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-users-cog mr-2 text-primary"></i>Gerenciar Usuários
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 elevation-1" data-pjax="true">
                    <i class="fas fa-plus mr-1"></i> Novo Usuário
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="border-0 pl-4">Nome</th>
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
                                <td class="pl-4">
                                    <span class="font-weight-bold text-sm">{{ $user->name }}</span>
                                </td>
                                <td>
                                    <span class="text-muted text-sm">{{ $user->email }}</span>
                                </td>
                                <td class="text-center">
                                    @if($user->role === 'superadmin')
                                        <span class="badge badge-danger px-3 py-2" style="font-size:11px;">
                                            <i class="fas fa-crown mr-1" style="font-size:9px;"></i>Super Admin
                                        </span>
                                    @elseif($user->role === 'admin')
                                        <span class="badge badge-warning px-3 py-2" style="font-size:11px;">
                                            <i class="fas fa-user-shield mr-1" style="font-size:9px;"></i>Admin
                                        </span>
                                    @else
                                        <span class="badge badge-secondary px-3 py-2" style="font-size:11px;">
                                            <i class="fas fa-user mr-1" style="font-size:9px;"></i>Membro
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border px-2 py-1">{{ ucfirst($user->level ?? 'Iniciante') }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info px-2 py-1">
                                        <i class="fas fa-ticket-alt mr-1" style="font-size:9px;"></i>
                                        {{ $user->getCheckedInTicketsCount() }} / {{ $user->getTotalTicketsCount() }}
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
                                                data-is-current="{{ $marketingUserId === $user->id ? '1' : '0' }}">
                                                <i class="fas fa-bullhorn"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Editar"
                                            data-pjax="true"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-delete" title="Excluir"
                                            data-action="{{ route('admin.users.destroy', $user) }}"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fas fa-users fa-3x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted font-weight-bold">Nenhum usuário encontrado</h5>
                                    <p class="text-muted">Cadastre o primeiro usuário para começar.</p>
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary rounded-pill px-4 elevation-1" data-pjax="true">
                                        <i class="fas fa-plus mr-1"></i> Novo Usuário
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
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
    <!-- DataTables & Plugins -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <script>
        $(function () {
            $("#example1").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "pageLength": 15,
                "order": [[0, "asc"]],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
                },
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

            // Marketing Manager toggle
            $(document).on('click', '.btn-toggle-marketing', function () {
                var btn = $(this);
                var url = btn.data('url');
                var userName = btn.data('user-name');
                var isCurrent = btn.data('is-current') === 1 || btn.data('is-current') === '1';
                var action = isCurrent ? 'unset' : 'set';

                var confirmText = isCurrent
                    ? 'Remover ' + userName + ' como Responsavel de Marketing?'
                    : 'Definir ' + userName + ' como Responsavel de Marketing? Um email e uma notificacao serao enviados.';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: isCurrent ? 'Remover Responsavel?' : 'Definir Responsavel?',
                        text: confirmText,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: isCurrent ? 'Sim, remover' : 'Sim, definir',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: isCurrent ? '#dc3545' : '#17a2b8',
                    }).then(function (result) {
                        if (!result.isConfirmed) return;
                        performToggle();
                    });
                } else if (confirm(confirmText)) {
                    performToggle();
                }

                function performToggle() {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            action: action,
                        },
                    }).done(function (resp) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(resp.message || 'Atualizado.');
                        }
                        setTimeout(function () { location.reload(); }, 800);
                    }).fail(function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Erro ao atualizar.';
                        if (typeof toastr !== 'undefined') toastr.error(msg);
                        else alert(msg);
                    });
                }
            });
        });
    </script>
@endpush
