@extends('admin.layouts.app')

@section('title', 'Comunidade')
@section('page_title', 'Comunidade')

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Nova publicacao</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('social.post.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <textarea name="content" rows="3" class="form-control" placeholder="No que voce esta pensando?"></textarea>
                        </div>
                        <div class="form-group">
                            <div class="custom-file">
                                <input type="file" name="media" class="custom-file-input" id="admin-post-media" accept="image/*">
                                <label class="custom-file-label" for="admin-post-media">Escolher imagem</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Visibilidade</label>
                            <select name="visibility" class="form-control">
                                <option value="public">Publico</option>
                                <option value="connections">Somente seguidores</option>
                                <option value="community" selected>Somente comunidade</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Publicar</button>
                    </form>
                </div>
            </div>

            @forelse($posts as $post)
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <img src="{{ $post->user?->profile_photo_url ?? asset('img/default-user.svg') }}"
                                    alt="Avatar" class="img-circle" style="width:40px;height:40px;object-fit:cover;">
                            </div>
                            <div>
                                <strong>{{ $post->user->name ?? 'Anonimo' }}</strong>
                                <div class="text-muted text-sm">{{ $post->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="card-tools ml-auto">
                            <form action="{{ route('social.post.destroy', $post) }}" method="POST" class="d-inline js-confirm-delete"
                                data-confirm-title="Remover publicacao?" data-confirm-text="Esta acao nao pode ser desfeita.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-tool text-danger" title="Remover">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">{!! nl2br(e($post->content)) !!}</p>
                        @if($post->media->isNotEmpty())
                            <img src="{{ asset($post->media->first()->path) }}" alt="Midia do post" class="img-fluid rounded">
                        @endif
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            {{ $post->reactions->count() }} curtida{{ $post->reactions->count() === 1 ? '' : 's' }} ·
                            {{ $post->comments->count() }} comentario{{ $post->comments->count() === 1 ? '' : 's' }}
                        </small>
                    </div>
                </div>
                @if(!empty($adsEnabled) && !empty($adsCode) && $loop->iteration % 3 === 0)
                    <div class="card">
                        <div class="card-body">
                            {!! $adsCode !!}
                        </div>
                    </div>
                @endif
            @empty
                <div class="card">
                    <div class="card-body text-center text-muted">Nenhuma publicacao ainda.</div>
                </div>
            @endforelse

            <div class="mt-3">
                {{ $posts->links() }}
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Resumo</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Sugestoes de membros para voce:</p>
                    @if(!empty($recommendedUsers) && $recommendedUsers->isNotEmpty())
                        @php
                            $connectionMap = $connectionMap ?? [];
                            $authUserId = auth()->id();
                        @endphp
                        <ul class="list-unstyled mb-0">
                            @foreach($recommendedUsers as $user)
                                @php
                                    $connection = $connectionMap[$user->id] ?? null;
                                    $isPending = $connection && $connection->status === 'pending';
                                    $isConnected = $connection && $connection->status === 'accepted';
                                    $isRequester = $connection && $authUserId && $connection->requester_id === $authUserId;
                                    $pendingTime = $connection ? $connection->created_at->diffForHumans() : '';
                                @endphp
                                <li class="d-flex align-items-center justify-content-between py-2">
                                    <div class="d-flex align-items-center">
                                        <a class="mr-2" href="{{ route('social.profile', $user->id) }}">
                                            <img src="{{ $user->profile_photo_url }}" alt="Avatar" class="img-circle"
                                                style="width:36px;height:36px;object-fit:cover;"
                                                onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                        </a>
                                        <div>
                                            <a href="{{ route('social.profile', $user->id) }}" class="text-sm font-weight-bold text-dark">
                                                {{ $user->name }}
                                            </a>
                                            <div class="text-muted text-xs">
                                                @if(!empty($user->segment))
                                                    {{ $user->segment }}
                                                @elseif(!empty($user->occupation))
                                                    {{ $user->occupation }}
                                                @elseif(!empty($user->company))
                                                    {{ $user->company }}
                                                @elseif(!empty($user->interests))
                                                    {{ \Illuminate\Support\Str::limit($user->interests, 40) }}
                                                @elseif(!empty($user->city))
                                                    {{ $user->city }}@if(!empty($user->state)), {{ $user->state }}@endif
                                                @else
                                                    Membro
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if($isConnected)
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                Conectado
                                            </button>
                                        @elseif($isPending && $isRequester)
                                            <div class="text-muted text-xs">Pendente ha {{ $pendingTime }}</div>
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-1"
                                                onclick="cancelInvite({{ $user->id }})">
                                                Cancelar
                                            </button>
                                        @elseif($isPending)
                                            <div class="text-muted text-xs">Solicitacao recebida</div>
                                            <button type="button" class="btn btn-sm btn-outline-success mt-1"
                                                onclick="acceptInvite({{ $user->id }})">
                                                Aceitar
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="requestInvite({{ $user->id }})">
                                                Conectar
                                            </button>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">Sem sugestoes no momento.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrfToken = '{{ csrf_token() }}';

        function requestInvite(userId) {
            Swal.fire({
                title: 'Conectar com este usuario?',
                text: 'Voce enviara uma solicitacao de conexao.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1F5EDB',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, conectar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                fetch(`/connect/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Enviado!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Ops!', data.message, 'warning');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Ops!', 'Erro ao conectar.', 'error');
                    });
            });
        }

        function cancelInvite(userId) {
            Swal.fire({
                title: 'Cancelar solicitacao?',
                text: 'Voce deseja cancelar o convite enviado?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, cancelar',
                cancelButtonText: 'Voltar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                fetch(`/connection/remove/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Cancelado!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Ops!', data.message, 'warning');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Ops!', 'Erro ao cancelar.', 'error');
                    });
            });
        }

        function acceptInvite(userId) {
            fetch(`/connection/accept/${userId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Conexao aceita!', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Ops!', data.message, 'warning');
                    }
                })
                .catch(() => {
                    Swal.fire('Ops!', 'Erro ao aceitar.', 'error');
                });
        }
    </script>
@endpush
