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
                        <div class="mr-3">
                            <img src="{{ $post->user?->profile_photo_url ?? asset('img/default-user.svg') }}"
                                alt="Avatar" class="img-circle" style="width:40px;height:40px;object-fit:cover;">
                        </div>
                        <div>
                            <strong>{{ $post->user->name ?? 'Anonimo' }}</strong>
                            <div class="text-muted text-sm">{{ $post->created_at->diffForHumans() }}</div>
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
                        <form action="{{ route('social.post.destroy', $post) }}" method="POST" class="d-inline js-confirm-delete"
                            data-confirm-title="Remover publicacao?" data-confirm-text="Esta acao nao pode ser desfeita.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Remover">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
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
                    <p class="text-muted mb-0">O feed da comunidade no painel usa o layout AdminLTE 3.2.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
