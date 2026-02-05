@extends('layouts.app')

@section('title', $user->name . ' - Perfil UNN')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <!-- Cover & Info -->
    <!-- Cover & Info -->
    <div class="bg-white shadow relative">
        <!-- Capa -->
        <div class="h-64 sm:h-80 w-full bg-gray-200 overflow-hidden relative group">
            @if(isset($user->cover_photo) && $user->cover_photo)
                <img src="{{ asset($user->cover_photo) }}" alt="Capa" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-r from-[#1F5EDB] to-[#0d3b96]"></div>
            @endif
        </div>

        <!-- Info do Perfil -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative pb-8">
            <div class="flex flex-col md:flex-row items-end -mt-20 sm:-mt-24 mb-4 gap-6 relative">
                
                <!-- Avatar -->
                <div class="flex-shrink-0 relative">
                    <div class="w-40 h-40 sm:w-48 sm:h-48 bg-white rounded-full p-1.5 shadow-xl">
                        @if(isset($user->photo) && $user->photo)
                            <img src="{{ asset($user->photo) }}" class="w-full h-full rounded-full object-cover border-4 border-white">
                        @else
                            <div class="w-full h-full bg-[#1F5EDB] rounded-full flex items-center justify-center text-5xl text-white font-bold border-4 border-white">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Detalhes -->
                <div class="flex-1 pb-2 w-full text-center md:text-left pt-16 md:pt-0">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1">{{ $user->name }}</h1>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-gray-600 mb-4">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-calendar-alt text-[#1F5EDB]"></i> Membro desde {{ $user->created_at->format('M Y') }}
                        </span>
                        @if(isset($user->city) && $user->city)
                        <span class="flex items-center gap-1">
                            <i class="fas fa-map-marker-alt text-[#1F5EDB]"></i> {{ $user->city }}
                        </span>
                        @endif
                    </div>

                    <!-- Redes Sociais -->
                    @php
                        $socialLinks = collect([
                            ['key' => 'linkedin', 'icon' => 'fab fa-linkedin', 'color' => '#0A66C2'],
                            ['key' => 'instagram', 'icon' => 'fab fa-instagram', 'color' => '#E4405F'],
                            ['key' => 'facebook', 'icon' => 'fab fa-facebook', 'color' => '#1877F2'],
                            ['key' => 'twitter', 'icon' => 'fab fa-twitter', 'color' => '#1DA1F2'],
                            ['key' => 'youtube', 'icon' => 'fab fa-youtube', 'color' => '#FF0000'],
                            ['key' => 'website', 'icon' => 'fas fa-globe', 'color' => '#10B981'],
                        ])->filter(fn($s) => isset($user->{$s['key']}) && $user->{$s['key']});
                    @endphp
                    
                    @if($socialLinks->isNotEmpty())
                    <div class="flex items-center justify-center md:justify-start gap-3 mt-2">
                        @foreach($socialLinks as $social)
                        <a href="{{ $user->{$social['key']} }}" target="_blank" rel="noopener" 
                           class="w-10 h-10 flex items-center justify-center bg-gray-50 rounded-full hover:shadow-md transition transform hover:-translate-y-1" 
                           style="color: {{ $social['color'] }}" title="{{ ucfirst($social['key']) }}">
                            <i class="{{ $social['icon'] }} text-xl"></i>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Ações -->
                <div class="flex gap-3 pb-4 w-full md:w-auto justify-center md:justify-end">
                    @if(auth()->check() && auth()->id() !== $user->id)
                        @php
                            $isConnected = auth()->user()->isConnectedWith($user->id);
                            $pendingConnection = auth()->user()->hasPendingConnectionWith($user->id);
                            $isRequester = $pendingConnection && $pendingConnection->requester_id === auth()->id();
                        @endphp

                        @if($isConnected)
                            <button onclick="removeConnection({{ $user->id }})" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-full font-bold hover:bg-gray-300 transition shadow flex items-center gap-2">
                                <i class="fas fa-user-check text-green-600"></i> Conectado
                            </button>
                            <a href="{{ route('chat.start', $user->id) }}" class="bg-[#1F5EDB] text-white px-8 py-3 rounded-full font-bold hover:bg-blue-700 transition shadow-lg hover:shadow-xl flex items-center gap-2">
                                <i class="fas fa-comment-dots"></i> Mensagem
                            </a>
                        @elseif($pendingConnection)
                            @if($isRequester)
                                <button class="bg-gray-200 text-gray-500 px-8 py-3 rounded-full font-bold cursor-not-allowed shadow flex items-center gap-2">
                                    <i class="fas fa-clock"></i> Pendente
                                </button>
                            @else
                                <button onclick="acceptConnection({{ $user->id }})" class="bg-green-600 text-white px-8 py-3 rounded-full font-bold hover:bg-green-700 transition shadow-lg flex items-center gap-2">
                                    <i class="fas fa-check"></i> Aceitar
                                </button>
                            @endif
                        @else
                            <button onclick="requestConnection({{ $user->id }})" class="bg-[#1F5EDB] text-white px-8 py-3 rounded-full font-bold hover:bg-blue-700 transition shadow-lg hover:shadow-xl flex items-center gap-2" id="btn-connect-{{ $user->id }}">
                                <i class="fas fa-user-plus"></i> Conectar
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        function requestConnection(userId) {
            const btn = document.getElementById(`btn-connect-${userId}`);
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch(`/connect/${userId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    location.reload();
                } else {
                    toastr.error(data.message);
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            })
            .catch(() => {
                toastr.error('Erro ao conectar.');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            });
        }

        function acceptConnection(userId) {
            fetch(`/connection/accept/${userId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    location.reload();
                } else {
                    toastr.error(data.message);
                }
            });
        }

        function removeConnection(userId) {
            if(!confirm('Tem certeza que deseja desfazer a conexão?')) return;
            
            fetch(`/connection/remove/${userId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    location.reload();
                } else {
                    toastr.error(data.message);
                }
            });
        }
    </script>
    @endpush

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-gray-900 mb-4">Sobre</h3>
                <p class="text-gray-600 text-sm">
                    {{ $user->bio ?? 'Sem descrição.' }}
                </p>
                
                <hr class="my-4">
                
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-map-marker-alt w-5 text-gray-400"></i> 
                        @if($user->city || $user->state)
                            {{ $user->city }}{{ $user->city && $user->state ? ', ' : '' }}{{ $user->state }}
                        @else
                            Localização não informada
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-briefcase w-5 text-gray-400"></i>
                        @if($user->occupation || $user->company)
                            {{ $user->occupation }}{{ $user->occupation && $user->company ? ' em ' : '' }}{{ $user->company }}
                        @else
                            Cargo não informado
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="md:col-span-2 space-y-6">
            <h3 class="font-bold text-xl text-gray-800">Publicações</h3>
            
            @forelse($posts as $post)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-3">
                            <div class="bg-gray-200 text-gray-600 rounded-full w-10 h-10 flex items-center justify-center font-bold">
                                {{ substr($post->user->name, 0, 1) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $post->user->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="prose max-w-none text-gray-800">
                        {!! nl2br(e($post->content)) !!}
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-500 bg-white rounded-lg shadow">
                    <p>Nenhuma publicação ainda.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
