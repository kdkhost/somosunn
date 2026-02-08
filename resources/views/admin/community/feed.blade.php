@extends('admin.layouts.app')

@section('title', 'Comunidade')
@section('page_title', 'Comunidade')

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title">Nova publicacao</h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-tool" title="Opcoes" onclick="togglePanel('admin-post-options')">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div id="admin-post-options" class="d-none position-absolute bg-white border rounded shadow p-3" style="right: 1rem; z-index: 20; min-width: 220px;">
                            <label class="mb-1 text-muted small">Visibilidade</label>
                            <select name="visibility" class="form-control form-control-sm">
                                <option value="public">Publico</option>
                                <option value="connections">Somente seguidores</option>
                                <option value="community" selected>Somente comunidade</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="admin-post-form" action="{{ route('social.post.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <textarea name="content" rows="3" class="form-control" placeholder="No que voce esta pensando?"></textarea>
                        </div>
                        <input type="file" name="media" class="d-none" id="admin-post-media" accept="image/*">
                        <div class="form-group" id="admin-dropzone">
                            <div class="border rounded p-3 bg-light d-flex flex-column gap-2">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="text-muted">
                                        <i class="fas fa-image mr-1"></i> Arraste e solte uma imagem
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="admin-post-select">
                                        Selecionar imagem
                                    </button>
                                </div>
                                <div class="text-muted small" id="admin-upload-name">Nenhuma imagem selecionada</div>
                                <div id="admin-preview-inline" class="d-none align-items-center">
                                    <img id="admin-preview-inline-img" src="" alt="Preview" class="mr-2 rounded border" style="width:44px;height:44px;object-fit:cover;">
                                    <span class="text-muted small" id="admin-preview-inline-name"></span>
                                </div>
                            </div>
                        </div>
                        <div id="admin-preview-top" class="d-none mb-3">
                            <div class="position-relative d-inline-block">
                                <img id="admin-preview-top-img" src="" alt="Preview" class="rounded border" style="max-height:160px;">
                                <button type="button" id="admin-preview-remove"
                                    class="btn btn-sm btn-light border position-absolute" style="top:-10px;right:-10px;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" id="admin-emoji-toggle" class="btn btn-light" title="Inserir emoji">
                                    <i class="far fa-smile"></i>
                                </button>
                                <div id="admin-emoji-picker" class="d-none position-absolute bg-white border rounded shadow p-2" style="z-index: 20; width: 260px;">
                                    @include('social.partials.emoji_tabs')
                                    @include('social.partials.emoji_grid')
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Publicar</button>
                        </div>
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
                            <form action="{{ route('social.post.destroy', $post) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-tool text-danger" title="Remover" data-confirm-delete>
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
                                            <div class="text-muted text-xs">Pendente {{ $pendingTime }}</div>
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

@push('styles')
    <style>
        #admin-dropzone.is-dragover .border {
            border-color: #1f5edb;
            background-color: #eef4ff;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function togglePanel(id) {
            const panel = document.getElementById(id);
            if (!panel) {
                return;
            }
            panel.classList.toggle('d-none');
        }

        const adminMedia = document.getElementById('admin-post-media');
        const adminSelect = document.getElementById('admin-post-select');
        const adminDropzone = document.getElementById('admin-dropzone');
        const adminUploadName = document.getElementById('admin-upload-name');
        const adminPreviewTop = document.getElementById('admin-preview-top');
        const adminPreviewTopImg = document.getElementById('admin-preview-top-img');
        const adminPreviewInline = document.getElementById('admin-preview-inline');
        const adminPreviewInlineImg = document.getElementById('admin-preview-inline-img');
        const adminPreviewInlineName = document.getElementById('admin-preview-inline-name');
        const adminPreviewRemove = document.getElementById('admin-preview-remove');
        const adminEmojiToggle = document.getElementById('admin-emoji-toggle');
        const adminEmojiPicker = document.getElementById('admin-emoji-picker');

        const setAdminUploadName = (file) => {
            if (adminUploadName) {
                adminUploadName.textContent = file ? file.name : 'Nenhuma imagem selecionada';
            }
        };

        const showAdminPreview = (file) => {
            if (!file || !adminPreviewTopImg || !adminPreviewInlineImg) {
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                const src = event.target ? event.target.result : null;
                if (!src) {
                    return;
                }
                adminPreviewTopImg.src = src;
                adminPreviewInlineImg.src = src;
                if (adminPreviewInlineName) {
                    adminPreviewInlineName.textContent = file.name;
                }
                if (adminPreviewTop) {
                    adminPreviewTop.classList.remove('d-none');
                }
                if (adminPreviewInline) {
                    adminPreviewInline.classList.remove('d-none');
                    adminPreviewInline.classList.add('d-flex');
                }
            };
            reader.readAsDataURL(file);
        };

        const clearAdminPreview = () => {
            if (adminPreviewTop) {
                adminPreviewTop.classList.add('d-none');
            }
            if (adminPreviewInline) {
                adminPreviewInline.classList.add('d-none');
                adminPreviewInline.classList.remove('d-flex');
            }
            if (adminPreviewTopImg) {
                adminPreviewTopImg.src = '';
            }
            if (adminPreviewInlineImg) {
                adminPreviewInlineImg.src = '';
            }
            if (adminPreviewInlineName) {
                adminPreviewInlineName.textContent = '';
            }
        };

        if (adminSelect && adminMedia) {
            adminSelect.addEventListener('click', () => adminMedia.click());
        }

        if (adminMedia) {
            adminMedia.addEventListener('change', () => {
                const file = adminMedia.files[0];
                setAdminUploadName(file);
                if (file) {
                    showAdminPreview(file);
                } else {
                    clearAdminPreview();
                }
            });
        }

        if (adminPreviewRemove && adminMedia) {
            adminPreviewRemove.addEventListener('click', () => {
                adminMedia.value = '';
                setAdminUploadName(null);
                clearAdminPreview();
            });
        }

        if (adminDropzone && adminMedia) {
            adminDropzone.addEventListener('dragover', (event) => {
                event.preventDefault();
                adminDropzone.classList.add('is-dragover');
            });

            adminDropzone.addEventListener('dragleave', () => {
                adminDropzone.classList.remove('is-dragover');
            });

            adminDropzone.addEventListener('drop', (event) => {
                event.preventDefault();
                adminDropzone.classList.remove('is-dragover');

                const files = event.dataTransfer ? event.dataTransfer.files : [];
                if (!files || !files.length) {
                    return;
                }

                const file = files[0];
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                adminMedia.files = dataTransfer.files;
                setAdminUploadName(file);
                showAdminPreview(file);
            });
        }

        if (adminEmojiToggle && adminEmojiPicker) {
            adminEmojiToggle.addEventListener('click', (event) => {
                event.preventDefault();
                adminEmojiPicker.classList.toggle('d-none');
            });
        }

        document.querySelectorAll('#admin-emoji-picker .emoji-tab').forEach((tab) => {
            tab.addEventListener('click', () => {
                const target = tab.getAttribute('data-target');
                const grid = document.querySelector('#admin-emoji-picker .emoji-grid');
                if (!grid) {
                    return;
                }
                grid.setAttribute('data-active', target);
                document.querySelectorAll('#admin-emoji-picker .emoji-tab').forEach((t) => t.classList.remove('is-active'));
                tab.classList.add('is-active');
            });
        });

        document.querySelectorAll('#admin-emoji-picker .emoji-item').forEach((item) => {
            item.addEventListener('click', () => {
                const target = document.querySelector('#admin-post-form textarea[name="content"]');
                const emoji = item.getAttribute('data-emoji');
                if (!target || !emoji) {
                    return;
                }
                target.value = `${target.value}${emoji}`;
                target.focus();
            });
        });
    </script>
@endpush

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
