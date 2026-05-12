@extends('admin.layouts.app')

@section('title', 'Gestao de Punicoes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-gavel text-danger mr-2"></i>Gestao de Punicoes</h1>
        <div>
            <a href="{{ route('admin.punishments.settings') }}" class="btn btn-outline-secondary btn-sm mr-2">
                <i class="fas fa-cog"></i> Configuracoes
            </a>
            <button type="button" class="btn btn-danger btn-sm" id="btn-apply-punishment">
                <i class="fas fa-plus"></i> Aplicar Punicao Manual
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="card card-outline card-danger">
    <div class="card-header">
        <h3 class="card-title">Usuarios com Punicao</h3>
        <div class="card-tools">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary filter-btn active" data-filter="todos">Todos</button>
                <button type="button" class="btn btn-outline-danger filter-btn" data-filter="bloqueado">Bloqueados</button>
                <button type="button" class="btn btn-outline-warning filter-btn" data-filter="suspenso">Suspensos</button>
                <button type="button" class="btn btn-outline-success filter-btn" data-filter="livre">Livres</button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="punishments-table" class="table table-bordered table-striped table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Status</th>
                    <th>Motivo</th>
                    <th>Bloqueado ate</th>
                    <th>Eventos Suspensos</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr data-status="{{ $user->punishment_status }}">
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="{{ $user->photo ? asset($user->photo) : asset('img/user.png') }}"
                                 class="img-circle mr-2" style="width:32px;height:32px;object-fit:cover;" alt="">
                            <div>
                                <strong>{{ $user->name }}</strong><br>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($user->punishment_status === 'ambos')
                            <span class="badge badge-danger">Bloqueado</span>
                            <span class="badge badge-warning">Suspenso</span>
                        @elseif($user->punishment_status === 'bloqueado')
                            <span class="badge badge-danger">Bloqueado</span>
                        @elseif($user->punishment_status === 'suspenso')
                            <span class="badge badge-warning">Suspenso</span>
                        @else
                            <span class="badge badge-success">Livre</span>
                        @endif
                    </td>
                    <td>{{ $user->block_reason ?? '-' }}</td>
                    <td>
                        @if($user->blocked_until && \Carbon\Carbon::parse($user->blocked_until)->isFuture())
                            {{ \Carbon\Carbon::parse($user->blocked_until)->format('d/m/Y H:i') }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if((int)$user->events_suspension_remaining > 0)
                            <span class="badge badge-warning">{{ $user->events_suspension_remaining }} evento(s)</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info btn-view" data-user-id="{{ $user->id }}" title="Ver detalhes">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-warning btn-edit" data-user-id="{{ $user->id }}" title="Editar punicao">
                                <i class="fas fa-edit"></i>
                            </button>
                            @if($user->punishment_status !== 'livre')
                            <button class="btn btn-success btn-remove" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" title="Remover punicao">
                                <i class="fas fa-unlock"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('css')
<style>
    .swal2-select-user { width: 100%; }
    .filter-btn.active { font-weight: bold; }
</style>
@endsection

@section('js')
<script>
$(function() {
    // DataTable
    var table = $('#punishments-table').DataTable({
        responsive: true,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
        order: [[3, 'desc']],
        pageLength: 25,
    });

    // Filtros por status
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        var filter = $(this).data('filter');

        if (filter === 'todos') {
            table.search('').columns().search('').draw();
        } else {
            table.search('').columns().search('').draw();
            // Filtrar via custom search
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = table.row(dataIndex).node();
                var status = $(row).data('status');
                if (filter === 'todos') return true;
                return status === filter || (filter === 'bloqueado' && status === 'ambos') || (filter === 'suspenso' && status === 'ambos');
            });
            table.draw();
            $.fn.dataTable.ext.search.pop();
        }
    });

    // Filtro persistente
    var currentFilter = 'todos';
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (currentFilter === 'todos') return true;
        var row = table.row(dataIndex).node();
        var status = $(row).data('status');
        return status === currentFilter || (currentFilter === 'bloqueado' && status === 'ambos') || (currentFilter === 'suspenso' && status === 'ambos');
    });

    $('.filter-btn').on('click', function() {
        currentFilter = $(this).data('filter');
        table.draw();
    });

    // Ver detalhes
    $(document).on('click', '.btn-view', function() {
        var userId = $(this).data('user-id');
        $.get("{{ url('admin/punishments') }}/" + userId, function(res) {
            if (res.success) {
                var u = res.user;
                var statusBadge = '';
                if (u.status === 'ambos') statusBadge = '<span class="badge badge-danger">Bloqueado</span> <span class="badge badge-warning">Suspenso</span>';
                else if (u.status === 'bloqueado') statusBadge = '<span class="badge badge-danger">Bloqueado</span>';
                else if (u.status === 'suspenso') statusBadge = '<span class="badge badge-warning">Suspenso</span>';
                else statusBadge = '<span class="badge badge-success">Livre</span>';

                Swal.fire({
                    title: 'Detalhes da Punicao',
                    html: '<div class="text-left">' +
                        '<div class="d-flex align-items-center mb-3">' +
                        '<img src="' + u.photo + '" class="img-circle mr-3" style="width:48px;height:48px;object-fit:cover;">' +
                        '<div><strong>' + u.name + '</strong><br><small>' + u.email + '</small></div>' +
                        '</div>' +
                        '<table class="table table-sm table-bordered">' +
                        '<tr><th>Status</th><td>' + statusBadge + '</td></tr>' +
                        '<tr><th>Motivo</th><td>' + (u.block_reason || '-') + '</td></tr>' +
                        '<tr><th>Bloqueado ate</th><td>' + (u.blocked_until_formatted || '-') + '</td></tr>' +
                        '<tr><th>Eventos suspensos</th><td>' + u.events_suspension_remaining + '</td></tr>' +
                        '</table></div>',
                    icon: 'info',
                    confirmButtonText: 'Fechar',
                    width: '500px',
                });
            }
        });
    });

    // Editar punicao
    $(document).on('click', '.btn-edit', function() {
        var userId = $(this).data('user-id');
        $.get("{{ url('admin/punishments') }}/" + userId, function(res) {
            if (!res.success) return;
            var u = res.user;

            Swal.fire({
                title: 'Editar Punicao - ' + u.name,
                html:
                    '<div class="form-group text-left">' +
                    '<label>Duracao do bloqueio (horas a partir de agora)</label>' +
                    '<input type="number" id="swal-edit-hours" class="form-control" value="0" min="0">' +
                    '<small class="text-muted">0 = remover bloqueio de tempo</small>' +
                    '</div>' +
                    '<div class="form-group text-left">' +
                    '<label>Motivo</label>' +
                    '<textarea id="swal-edit-reason" class="form-control" rows="2">' + (u.block_reason || '') + '</textarea>' +
                    '</div>' +
                    '<div class="form-group text-left">' +
                    '<label>Eventos suspensos</label>' +
                    '<input type="number" id="swal-edit-events" class="form-control" value="' + u.events_suspension_remaining + '" min="0">' +
                    '</div>',
                showCancelButton: true,
                confirmButtonText: 'Salvar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e6a817',
                preConfirm: function() {
                    return {
                        block_duration_hours: parseInt($('#swal-edit-hours').val()) || 0,
                        block_reason: $('#swal-edit-reason').val(),
                        events_suspension: parseInt($('#swal-edit-events').val()) || 0,
                    };
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/punishments') }}/" + userId + "/edit",
                        method: 'PUT',
                        data: result.value,
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('Sucesso', res.message, 'success').then(function() {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Erro', xhr.responseJSON?.message || 'Erro ao editar punicao.', 'error');
                        }
                    });
                }
            });
        });
    });

    // Remover punicao
    $(document).on('click', '.btn-remove', function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');

        Swal.fire({
            title: 'Remover Punicao',
            text: 'Tem certeza que deseja remover todas as punicoes de ' + userName + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar',
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/punishments') }}/" + userId + "/remove",
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Sucesso', res.message, 'success').then(function() {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Erro', xhr.responseJSON?.message || 'Erro ao remover punicao.', 'error');
                    }
                });
            }
        });
    });

    // Aplicar punicao manual
    $('#btn-apply-punishment').on('click', function() {
        Swal.fire({
            title: 'Aplicar Punicao Manual',
            html:
                '<div class="form-group text-left">' +
                '<label>Usuario (nome ou email)</label>' +
                '<input type="text" id="swal-user-search" class="form-control" placeholder="Buscar usuario...">' +
                '<select id="swal-user-id" class="form-control mt-2" style="display:none;"></select>' +
                '</div>' +
                '<div class="form-group text-left">' +
                '<label>Duracao do bloqueio (horas)</label>' +
                '<input type="number" id="swal-block-hours" class="form-control" value="48" min="0">' +
                '</div>' +
                '<div class="form-group text-left">' +
                '<label>Motivo</label>' +
                '<textarea id="swal-reason" class="form-control" rows="2" placeholder="Motivo da punicao..."></textarea>' +
                '</div>' +
                '<div class="form-group text-left">' +
                '<label>Eventos suspensos</label>' +
                '<input type="number" id="swal-events" class="form-control" value="2" min="0">' +
                '</div>' +
                '<div class="form-group text-left">' +
                '<div class="custom-control custom-checkbox">' +
                '<input type="checkbox" class="custom-control-input" id="swal-notify" checked>' +
                '<label class="custom-control-label" for="swal-notify">Notificar usuario por email</label>' +
                '</div>' +
                '</div>',
            showCancelButton: true,
            confirmButtonText: 'Aplicar Punicao',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            width: '500px',
            didOpen: function() {
                var searchTimer;
                $('#swal-user-search').on('input', function() {
                    var query = $(this).val();
                    clearTimeout(searchTimer);
                    if (query.length < 2) {
                        $('#swal-user-id').hide();
                        return;
                    }
                    searchTimer = setTimeout(function() {
                        $.get("{{ route('admin.users.index') }}", { search: query, format: 'json' }, function(data) {
                            var select = $('#swal-user-id');
                            select.empty().append('<option value="">Selecione...</option>');
                            var users = data.data || data;
                            if (Array.isArray(users)) {
                                users.forEach(function(u) {
                                    select.append('<option value="' + u.id + '">' + u.name + ' (' + u.email + ')</option>');
                                });
                            }
                            select.show();
                        });
                    }, 300);
                });
            },
            preConfirm: function() {
                var userId = $('#swal-user-id').val();
                if (!userId) {
                    Swal.showValidationMessage('Selecione um usuario');
                    return false;
                }
                var reason = $('#swal-reason').val();
                if (!reason) {
                    Swal.showValidationMessage('Informe o motivo da punicao');
                    return false;
                }
                return {
                    user_id: userId,
                    block_duration_hours: parseInt($('#swal-block-hours').val()) || 0,
                    block_reason: reason,
                    events_suspension: parseInt($('#swal-events').val()) || 0,
                    notify_user: $('#swal-notify').is(':checked') ? 1 : 0,
                };
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.punishments.apply') }}",
                    method: 'POST',
                    data: result.value,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Sucesso', res.message, 'success').then(function() {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        var msg = 'Erro ao aplicar punicao.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Erro', msg, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
