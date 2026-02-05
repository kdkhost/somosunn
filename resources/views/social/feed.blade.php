@extends($extends ?? 'layouts.app')

@section('title', 'Comunidade - UNN')

@section('content')
    <div class="bg-gray-100 min-h-screen pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Sidebar Left -->
                <div class="hidden md:block">
                    <div class="bg-white rounded-lg shadow p-4 sticky top-24">
                        @auth
                            <div class="flex items-center gap-3 mb-6">
                                <div
                                    class="bg-blue-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">Membro</p>
                                </div>
                            </div>
                            <nav class="space-y-2">
                                <a href="{{ route('social.feed') }}"
                                    class="flex items-center gap-2 text-blue-600 font-medium p-2 bg-blue-50 rounded">
                                    <i class="fas fa-newspaper w-6"></i> Feed
                                </a>
                                <a href="{{ route('chat.index') }}"
                                    class="flex items-center gap-2 text-gray-600 hover:text-blue-600 p-2 rounded transition">
                                    <i class="fas fa-comments w-6"></i> Mensagens
                                </a>
                                <a href="{{ route('social.profile', Auth::id()) }}"
                                    class="flex items-center gap-2 text-gray-600 hover:text-blue-600 p-2 rounded transition">
                                    <i class="fas fa-user w-6"></i> Meu Perfil
                                </a>
                                <a href="{{ route('courses.index') }}"
                                    class="flex items-center gap-2 text-gray-600 hover:text-blue-600 p-2 rounded transition">
                                    <i class="fas fa-graduation-cap w-6"></i> Cursos
                                </a>
                            </nav>
                        @else
                            <div class="text-center py-4">
                                <p class="text-gray-600 mb-2">Faça login para participar</p>
                                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Entrar</a>
                            </div>
                        @endauth
                    </div>
                </div>

                <!-- Main Feed -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Composer -->
                    @auth
                        <div class="bg-white rounded-lg shadow p-4">
                            <form action="{{ route('social.post.store') }}" method="POST">
                                @csrf
                                <div class="flex gap-3">
                                    <div
                                        class="bg-blue-600 text-white rounded-full w-10 h-10 flex-shrink-0 flex items-center justify-center font-bold">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <textarea name="content" rows="3"
                                            class="w-full border-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-gray-50 p-3"
                                            placeholder="No que você está pensando?"></textarea>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center mt-3 pt-3 border-t">
                                    <div class="flex gap-2">
                                        <button type="button"
                                            class="text-gray-500 hover:text-blue-600 p-2 rounded hover:bg-gray-100">
                                            <i class="fas fa-image"></i>
                                        </button>
                                        <button type="button"
                                            class="text-gray-500 hover:text-blue-600 p-2 rounded hover:bg-gray-100">
                                            <i class="fas fa-smile"></i>
                                        </button>
                                    </div>
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-6 py-2 rounded-full font-medium hover:bg-blue-700 transition">
                                        Publicar
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endauth

                    <!-- Posts -->
                    @forelse($posts as $post)
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="bg-gray-200 text-gray-600 rounded-full w-10 h-10 flex items-center justify-center font-bold">
                                        {{ substr($post->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900">{{ $post->user->name }}</h4>
                                        <p class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <button class="text-gray-400 hover:text-gray-600"><i class="fas fa-ellipsis-h"></i></button>
                            </div>

                            <div class="prose max-w-none text-gray-800 mb-4">
                                {!! nl2br(e($post->content)) !!}
                            </div>

                            <!-- Reactions / Actions -->
                            <div class="flex items-center justify-between pt-3 border-t text-sm text-gray-500">
                                <button class="flex items-center gap-2 hover:text-blue-600 transition">
                                    <i class="far fa-thumbs-up"></i> Curtir
                                </button>
                                <button class="flex items-center gap-2 hover:text-blue-600 transition">
                                    <i class="far fa-comment"></i> Comentar
                                </button>
                                <button class="flex items-center gap-2 hover:text-blue-600 transition">
                                    <i class="fas fa-share"></i> Compartilhar
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-gray-500">
                            <p>Nenhum post ainda. Seja o primeiro a publicar!</p>
                        </div>
                    @endforelse

                    {{ $posts->links() }}
                </div>

                <!-- Sidebar Right (Suggestions) -->
                <div class="hidden md:block">
                    <div class="bg-white rounded-lg shadow p-4 sticky top-24">
                        <h3 class="font-bold text-gray-900 mb-4">Recomendados</h3>
                        <div class="space-y-4">
                            <!-- Placeholder -->
                            <p class="text-xs text-gray-500">Nenhum evento próximo.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection