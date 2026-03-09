@extends('layouts.app')

@section('title', 'Galeria de Eventos - SOMOS UNN')

@section('content')
    <div class="pt-32 pb-20 min-h-screen bg-slate-50">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center mb-16">
                <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-6 drop-shadow-sm">
                    Nossa <span class="text-transparent bg-clip-text"
                        style="background-image: linear-gradient(to right, var(--unn-azul-1), var(--unn-azul-3))">Galeria</span>
                </h1>
                <p class="text-lg text-slate-600">
                    Reviva os melhores momentos dos nossos eventos, palestras e encontros exclusivos da comunidade.
                </p>
            </div>

            @if($events->isEmpty())
                <div class="bg-white rounded-3xl p-12 text-center shadow-sm max-w-2xl mx-auto border border-slate-100">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-camera-retro text-3xl text-slate-300"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-2">Nenhuma foto ainda</h3>
                    <p class="text-slate-500">Estamos preparando a galeria com os registros dos nossos eventos mais recentes.
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('home') }}" class="btn-primary text-white px-8 py-3 rounded-full font-bold">Voltar
                            para Início</a>
                    </div>
                </div>
            @else
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($events as $event)
                        @php
                            $cover = $event->media->where('type', 'image')->first();
                            $coverPath = $cover ? asset('storage/' . $cover->file_path) : asset('storage/' . $event->image);
                        @endphp
                        <a href="{{ route('gallery.show', $event) }}"
                            class="group block bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-2 border border-slate-100">
                            <div class="relative aspect-video overflow-hidden">
                                <img src="{{ $coverPath }}" alt="{{ $event->title }}"
                                    class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                                <div
                                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                                    <span
                                        class="bg-white text-slate-900 px-6 py-2 rounded-full font-bold text-sm shadow-lg transform translate-y-4 group-hover:translate-y-0 transition duration-500">Ver
                                        todas as fotos</span>
                                </div>
                                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold shadow-sm"
                                    style="color: var(--unn-azul-1)">
                                    {{ $event->media->count() }} itens
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider mb-2"
                                    style="color: var(--unn-azul-1)">
                                    <i class="far fa-calendar-alt"></i>
                                    {{ \Carbon\Carbon::parse($event->start_at)->translatedFormat('d \d\e F, Y') }}
                                </div>
                                <h3
                                    class="text-xl font-bold text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-1">
                                    {{ $event->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-16">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection