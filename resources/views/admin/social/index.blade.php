@extends('admin.layouts.app')
@section('title', 'Moderação Comunidade')
@section('page_title', 'Posts da Comunidade')

@section('content')
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Autor</th>
                        <th>Conteúdo</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td>{{ $post->user->name ?? 'Anon' }}</td>
                            <td>{{ Str::limit($post->content, 100) }}</td>
                            <td>{{ $post->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <form action="{{ route('admin.social.destroy', $post->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirmAction(event, 'Excluir este post?', 'Esta ação não pode ser desfeita.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Nenhum post encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $posts->links() }}
        </div>
    </div>
@endsection