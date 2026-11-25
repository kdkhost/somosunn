@extends('admin.layouts.app')

@section('title', 'Usuários - Admin')
@section('page_title', 'Gestão de Usuários')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Usuários</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ cdn_asset('assets/admin/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ cdn_asset('assets/admin/datatables/responsive.bootstrap4.min.css') }}">
    <style>
        .users-hero {
            position: relative;
            overflow: hidden;
            border-radius: 1.1rem;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #1F5EDB 100%);
            color: #ffffff;
            padding: 1.5rem 1.75rem;
            box-shadow: 0 18px 40px -12px rgba(15, 23, 42, 0.35);
        }
        .users-hero::after {
            content: '';
            position: absolute;
            right: -50px;
            top: -50px;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, rgba(56, 189, 248, 0) 70%);
            pointer-events: none;
        }
        .users-hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.36rem 0.78rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #f8fafc;
            backdrop-filter: blur(8px);
        }
        .users-hero-btn-light {
            background: #ffffff;
            color: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 0.7rem;
            font-weight: 700;
            padding: 0.52rem 1.05rem;
            box-shadow: 0 8px 20px -6px rgba(15, 23, 42, 0.35);
        }
        .users-hero-btn-light:hover {
            background: #f8fafc;
            color: #1F5EDB;
            transform: translateY(-1px);
        }
        .users-hero-btn-ghost {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.26);
            border-radius: 0.7rem;
            font-weight: 600;
            padding: 0.52rem 1rem;
        }
        .users-hero-btn-ghost:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
        }
        .users-segmented {
            display: inline-flex;
            flex-wrap: wrap;
            padding: 0.25rem;
            border-radius: 0.75rem;
            background: var(--bs-tertiary-bg);
            border: 1px solid var(--bs-border-color);
            gap: 0.2rem;
        }
        .users-segmented .btn {
            border: 0;
            border-radius: 0.55rem;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.38rem 0.8rem;
            color: var(--bs-secondary-color);
            background: transparent;
        }
        .users-segmented .btn.is-active {
            background: var(--unn-gradient);
            color: #ffffff;
            box-shadow: 0 4px 12px -3px rgba(31, 94, 219, 0.45);
        }
        .users-avatar-img,
        .users-avatar-fallback {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            flex-shrink: 0;
        }
        .users-avatar-img {
            object-fit: cover;
            border: 2px solid rgba(31, 94, 219, 0.25);
        }
        .users-avatar-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.88rem;
            color: #ffffff;
            background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%);
        }
        #example1_wrapper .dataTables_filter,
        #example1_wrapper .dataTables_length {
            display: none !important;
        }
        #example1_wrapper .dataTables_paginate {
            display: flex;
            justify-content: flex-end;
            padding: 0.85rem 1.25rem;
        }
        #example1_wrapper .dataTables_info {
            padding: 0.95rem 1.25rem;
            color: var(--bs-secondary-color);
            font-size: 0.82rem;
        }
        .users-kpi-clickable {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    @php
        $marketingUserId = (int) \App\Models\Setting::get('platform_marketing_user_id', 0);
        $marketingUser = $marketingUserId > 0 ? \App\Models\User::find($marketingUserId) : null;
        $isNewUsersTodayView = ($registered ?? '') === 'today';
        $createdAtLabel = !empty($createdAt) ? \Carbon\Carbon::parse($createdAt)->format('d/m/Y') : null;
    @endphp

    {{-- Hero Banner Moderno --}}
    <div class="users-hero mb-4">
        <div class="row align-items-center g-3 position-relative" style="z-index: 2;">
            <div class="col-lg-7">
                <div class="text-uppercase fw-bold mb-1" style="font-size: 0.72rem; letter-spacing: 0.14em; color: #93c5fd;">
                    <i class="fas fa-users-gear me-1"></i> Diretório &amp; Acessos • SOMOS UNN
                </div>
                <h2 class="h4 fw-bold text-white mb-2">Usuários, Papéis &amp; Permissões</h2>
                <p class="mb-3 text-white-50" style="max-width: 640px; font-size: 0.9rem;">
                    Filtre por papel ou validação de e-mail em tempo real, valide contas manualmente, supervisione acessos e gerencie o responsável de marketing.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="users-hero-pill">
                        <i class="fas fa-user-plus text-info"></i>
                        Hoje: {{ $totalNewToday ?? 0 }} novo(s)
                    </span>
                    <span class="users-hero-pill">
                        <i class="fas fa-bullhorn text-warning"></i>
                        Marketing: {{ $marketingUser ? $marketingUser->name : 'Não definido' }}
                    </span>
                </div>
            </div>
            <div class="col-lg-5 d-flex flex-wrap justify-content-lg-end gap-2">
                @if(!$isNewUsersTodayView)
                    <a href="{{ route('admin.users.index', ['registered' => 'today']) }}" class="btn users-hero-btn-ghost" data-pjax="true">
                        <i class="fas fa-calendar-day me-1"></i> Novos Hoje ({{ $totalNewToday ?? 0 }})
                    </a>
                @else
                    <a href="{{ route('admin.users.index') }}" class="btn users-hero-btn-ghost" data-pjax="true">
                        <i class="fas fa-rotate-left me-1"></i> Ver Todos
                    </a>
                @endif
                <a href="{{ route('admin.users.create') }}" class="btn users-hero-btn-light" data-pjax="true">
                    <i class="fas fa-user-plus me-2 text-primary"></i> Novo Usuário
                </a>
            </div>
        </div>
    </div>

    {{-- 4 KPI Cards (AdminLTE 4 .small-box com animação CountUp) --}}
    <div class="row g-3 mb-2">
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-primary users-kpi-clickable" onclick="window.setUsersRoleFilter('all')">
                <div class="inner">
                    <h3>{{ $totalUsers }}</h3>
                    <p>Total de Usuários</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <div class="small-box-footer"><span>Clique para ver todos</span></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-success users-kpi-clickable" onclick="window.setUsersRoleFilter('member')">
                <div class="inner">
                    <h3>{{ $totalMembers }}</h3>
                    <p>Membros</p>
                </div>
                <div class="icon"><i class="fas fa-user-graduate"></i></div>
                <div class="small-box-footer"><span>Filtrar membros</span></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-danger users-kpi-clickable" onclick="window.setUsersRoleFilter('admin')">
                <div class="inner">
                    <h3>{{ $totalAdmins }}</h3>
                    <p>Administradores</p>
                </div>
                <div class="icon"><i class="fas fa-user-shield"></i></div>
                <div class="small-box-footer"><span>Filtrar admins</span></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-warning users-kpi-clickable" onclick="window.setUsersVerifiedFilter('pending')">
                <div class="inner">
                    <h3>{{ $totalPendingEmail ?? 0 }}</h3>
                    <p>E-mails Pendentes</p>
                </div>
                <div class="icon"><i class="fas fa-envelope-open-text"></i></div>
                <div class="small-box-footer"><span>Filtrar pendentes</span></div>
            </div>
        </div>
    </div>

    {{-- Responsável de Marketing --}}
    <div class="unn-section-box mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="users-avatar-fallback" style="background: linear-gradient(135deg, #0284c7, #0ea5e9);">
                <i class="fas fa-bullhorn"></i>
            </span>
            <div>
                <div class="fw-bold small text-uppercase text-secondary">Responsável de Marketing (Split 10% Tráfego)</div>
                @if($marketingUser)
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                        <span class="badge rounded-pill text-bg-success"><i class="fas fa-check-circle me-1"></i>Ativo</span>
                        <strong>{{ $marketingUser->name }}</strong>
                        <span class="text-secondary small">({{ $marketingUser->email }})</span>
                    </div>
                @else
                    <div class="text-secondary small mt-1">
                        Nenhum usuário designado. Clique no ícone <i class="fas fa-bullhorn text-info"></i> na linha de um usuário para definir.
                    </div>
                @endif
            </div>
        </div>
        @if($isNewUsersTodayView || $createdAtLabel)
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill text-bg-primary px-3 py-2">
                    <i class="fas fa-filter me-1"></i>
                    {{ $isNewUsersTodayView ? 'Somente cadastrados hoje' : 'Cadastrados em ' . $createdAtLabel }}
                </span>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill" data-pjax="true">
                    <i class="fas fa-times me-1"></i> Limpar
                </a>
            </div>
        @endif
    </div>

    {{-- Tabela Principal com Toolbar React --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="users-segmented" id="usersRoleTabs">
                    <button type="button" class="btn is-active" data-role-filter="all">Todos</button>
                    <button type="button" class="btn" data-role-filter="member">Membros</button>
                    <button type="button" class="btn" data-role-filter="admin">Admins</button>
                    @if(auth()->user()?->role === 'superadmin')
                        <button type="button" class="btn" data-role-filter="superadmin">Super Admins</button>
                    @endif
                </div>

                <select id="usersVerifiedFilter" class="form-select form-select-sm" style="width: 175px;">
                    <option value="all">Todos os e-mails</option>
                    <option value="verified">E-mails validados</option>
                    <option value="pending">E-mails pendentes</option>
                </select>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="width: 240px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="usersSearchInput" class="form-control" value="{{ $search ?? '' }}" placeholder="Buscar nome, e-mail ou ID...">
                </div>
                <select id="usersPageLength" class="form-select form-select-sm" style="width: 92px;" title="Itens por página">
                    <option value="15" selected>15 / pág</option>
                    <option value="30">30 / pág</option>
                    <option value="50">50 / pág</option>
                    <option value="100">100 / pág</option>
                </select>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Usuário</th>
                            <th>E-mail &amp; Status</th>
                            <th class="text-center">Papel</th>
                            <th class="text-center">Nível</th>
                            <th class="text-center">Ingressos</th>
                            <th class="text-center" style="width: 195px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            @php
                                $initial = mb_strtoupper(mb_substr(trim((string) $user->name) ?: 'U', 0, 1));
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($user->photo)
                                            <img src="{{ asset($user->photo) }}" alt="" class="users-avatar-img" loading="lazy" decoding="async">
                                        @else
                                            <span class="users-avatar-fallback">{{ $initial }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="fw-bold text-truncate" style="max-width: 220px;">{{ $user->name }}</div>
                                            @if($user->plan)
                                                <small class="text-secondary d-block" style="font-size: 0.74rem;">
                                                    <i class="fas fa-crown text-warning me-1"></i>{{ $user->plan->name }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary small d-block">{{ $user->email }}</span>
                                    <span class="badge rounded-pill d-inline-flex align-items-center mt-1 {{ $user->hasVerifiedEmail() ? 'text-bg-success' : 'text-bg-warning' }}" style="font-size: 10.5px;">
                                        <i class="fas {{ $user->hasVerifiedEmail() ? 'fa-check-circle' : 'fa-exclamation-circle' }} me-1"></i>
                                        {{ $user->hasVerifiedEmail() ? 'Validado' : 'Pendente' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($user->role === 'superadmin')
                                        <span class="badge rounded-pill text-bg-danger px-3 py-1" style="font-size: 11px;"><i class="fas fa-crown me-1" style="font-size: 9px;"></i>Super Admin</span>
                                    @elseif($user->role === 'admin')
                                        <span class="badge rounded-pill text-bg-warning px-3 py-1" style="font-size: 11px;"><i class="fas fa-user-shield me-1" style="font-size: 9px;"></i>Admin</span>
                                    @else
                                        <span class="badge rounded-pill text-bg-secondary px-3 py-1" style="font-size: 11px;"><i class="fas fa-user me-1" style="font-size: 9px;"></i>Membro</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill text-bg-light border px-2 py-1">{{ ucfirst($user->level ?? 'Iniciante') }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill text-bg-info px-2 py-1">
                                        <i class="fas fa-ticket-alt me-1" style="font-size: 9px;"></i>{{ $user->checked_in_tickets_count }} / {{ $user->total_tickets_count }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        @if(auth()->user()->isAdmin() && $user->id !== auth()->id() && !session()->has('impersonator_id'))
                                            @if(!$user->isAdmin() || auth()->user()->role === 'superadmin')
                                                <a href="{{ route('admin.users.impersonate', $user) }}" class="btn btn-sm btn-outline-warning rounded-pill px-2"
                                                    title="Acessar como usuário" data-pjax="false"><i class="fas fa-user-secret"></i></a>
                                            @endif
                                        @endif
                                        @if(auth()->user()->isAdmin())
                                            <button type="button"
                                                class="btn btn-sm rounded-pill px-2 btn-toggle-marketing {{ $marketingUserId === $user->id ? 'btn-success' : 'btn-outline-info' }}"
                                                title="{{ $marketingUserId === $user->id ? 'Remover como Responsável de Marketing' : 'Definir como Responsável de Marketing' }}"
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
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-delete" title="Excluir"
                                            data-action="{{ route('admin.users.destroy', $user) }}"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ cdn_asset('assets/admin/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ cdn_asset('assets/admin/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ cdn_asset('assets/admin/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ cdn_asset('assets/admin/datatables/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(function () {
            let activeRoleFilter = 'all';
            let activeVerifiedFilter = 'all';
            let searchTimer = null;

            const table = $("#example1").DataTable({
                processing: true,
                serverSide: true,
                deferLoading: {{ (int) $totalUsers }},
                ajax: {
                    url: @json(route('admin.users.data')),
                    data: function (d) {
                        d.search_filter = $('#usersSearchInput').val() || '';
                        d.registered = @json($registered ?? '');
                        d.created_at = @json($createdAt ?? '');
                        d.role_filter = activeRoleFilter;
                        d.verified_filter = activeVerifiedFilter;
                    }
                },
                columns: [
                    { data: 'name', className: 'ps-4' },
                    { data: 'email' },
                    { data: 'role', className: 'text-center' },
                    { data: 'level', className: 'text-center' },
                    { data: 'tickets', className: 'text-center' },
                    { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                pageLength: 15,
                order: [[0, 'asc']],
                language: {
                    url: "{{ cdn_asset('assets/admin/datatables/pt-BR.json') }}",
                    emptyTable: "Nenhum usuário encontrado para os filtros aplicados."
                }
            });

            window.setUsersRoleFilter = function (role) {
                activeRoleFilter = role || 'all';
                $('#usersRoleTabs .btn').removeClass('is-active');
                $('#usersRoleTabs .btn[data-role-filter="' + activeRoleFilter + '"]').addClass('is-active');
                table.ajax.reload();
            };

            window.setUsersVerifiedFilter = function (val) {
                activeVerifiedFilter = val || 'all';
                $('#usersVerifiedFilter').val(activeVerifiedFilter);
                table.ajax.reload();
            };

            $('#usersRoleTabs').on('click', '.btn', function () {
                window.setUsersRoleFilter($(this).data('role-filter'));
            });

            $('#usersVerifiedFilter').on('change', function () {
                activeVerifiedFilter = $(this).val() || 'all';
                table.ajax.reload();
            });

            $('#usersPageLength').on('change', function () {
                table.page.len(parseInt($(this).val(), 10) || 15).draw();
            });

            $('#usersSearchInput').on('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    table.ajax.reload();
                }, 260);
            });

            // Marketing Manager toggle
            $(document).off('click.unnMarketing', '.btn-toggle-marketing').on('click.unnMarketing', '.btn-toggle-marketing', function () {
                const btn = $(this);
                const url = btn.data('url');
                const userName = btn.data('user-name');
                const isCurrent = btn.data('is-current') === 1 || btn.data('is-current') === '1';
                const action = isCurrent ? 'unset' : 'set';
                const requiresPix = !isCurrent && String(btn.data('has-pix')) !== '1';

                const confirmText = isCurrent
                    ? 'Remover ' + userName + ' como Responsável de Marketing?'
                    : 'Definir ' + userName + ' como Responsável de Marketing? Um e-mail e uma notificação serão enviados.';

                const options = {
                    title: isCurrent ? 'Remover Responsável?' : 'Definir Responsável?',
                    text: confirmText,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: isCurrent ? 'Sim, remover' : 'Sim, definir',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: isCurrent ? '#dc2626' : '#1F5EDB',
                    reverseButtons: true
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
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}', action: action, pix_key: result.value || '' },
                    }).done(function (resp) {
                        toastr.success(resp.message || 'Atualizado.');
                        setTimeout(function () { location.reload(); }, 700);
                    }).fail(function (xhr) {
                        toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Erro ao atualizar.');
                    });
                });
            });

            // Verificação manual de e-mail
            $(document).off('click.unnVerifyEmail', '.btn-verify-email').on('click.unnVerifyEmail', '.btn-verify-email', function () {
                const button = $(this);
                Swal.fire({
                    title: 'Validar e-mail manualmente?',
                    text: 'O membro poderá realizar compras e acessar recursos sem abrir o link de confirmação.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, validar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#10b981',
                    reverseButtons: true
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.post(button.data('url'), { _token: '{{ csrf_token() }}' })
                        .done(function (response) {
                            toastr.success(response.message || 'E-mail validado com sucesso.');
                            table.ajax.reload(null, false);
                        })
                        .fail(function (xhr) {
                            toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Não foi possível validar o e-mail.');
                        });
                });
            });
        });
    </script>
@endpush
