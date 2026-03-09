@extends('layouts.app')

@section('title', 'Fotos: ' . $event->title . ' - SOMOS UNN')

@section('content')
    <div class="pt-32 pb-20 min-h-screen bg-white">
        <div class="container mx-auto px-4">
            <div class="mb-12">
                <a href="{{ route('gallery.index') }}"
                    class="inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 font-bold mb-8 transition-colors group">
                    <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                    Voltar para Galeria
                </a>

                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-2 text-sm font-bold uppercase tracking-wider mb-2"
                            style="color: var(--unn-azul-1)">
                            <i class="far fa-calendar-alt"></i>
                            {{ \Carbon\Carbon::parse($event->start_at)->translatedFormat('d \d\e F, Y') }}
                        </div>
                        <h1 class="text-3xl md:text-4xl font-black text-slate-900 drop-shadow-sm">
                            {{ $event->title }}
                        </h1>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-slate-500 font-medium">Fotos e vídeos do evento organizado por</span>
                        <span
                            class="bg-slate-50 px-4 py-2 rounded-xl font-bold border border-slate-100">{{ $event->speaker ?: 'SOMOS UNN' }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @foreach($media as $item)
                    @if($item->type === 'image')
                        <div
                            class="group relative aspect-square rounded-2xl overflow-hidden bg-slate-100 shadow-sm hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
                            <img src="{{ asset('storage/' . $item->file_path) }}" alt="Foto do evento"
                                class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700 cursor-pointer"
                                onclick="openLightbox('{{ asset('storage/' . $item->file_path) }}')">
                            <div
                                class="absolute inset-x-2 bottom-2 p-3 bg-white/80 backdrop-blur-md rounded-xl opacity-0 group-hover:opacity-100 translate-y-4 group-hover:translate-y-0 transition-all duration-500 pointer-events-none">
                                <p class="text-xs font-bold text-center text-slate-900">Clique para ampliar</p>
                            </div>
                        </div>
                    @else
                        <div
                            class="group relative aspect-square rounded-2xl overflow-hidden bg-slate-900 shadow-sm hover:shadow-2xl transition-all duration-500 hover:-translate-y-1 flex items-center justify-center">
                            <video class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity">
                                <source src="{{ asset('storage/' . $item->file_path) }}" type="video/mp4">
                            </video>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="w-16 h-16 bg-white/20 backdrop-blur-md border border-white/30 rounded-full flex items-center justify-center group-hover:scale-110 transition duration-500">
                                    <i class="fas fa-play text-white text-xl ml-1"></i>
                                </div>
                            </div>
                            <div
                                class="absolute inset-x-2 bottom-2 p-3 bg-white/80 backdrop-blur-md rounded-xl opacity-0 group-hover:opacity-100 translate-y-4 group-hover:translate-y-0 transition-all duration-500 pointer-events-none">
                                <p class="text-xs font-bold text-center text-slate-900">Assistir Vídeo</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="mt-16">
                {{ $media->links() }}
            </div>
        </div>
    </div>

    {{-- Lightbox Simples --}}
    <div id="lightbox"
        class="fixed inset-0 z-[9999] bg-black/95 backdrop-blur-sm hidden flex items-center justify-center p-4 md:p-10"
        onclick="this.classList.add('hidden')">
        <button class="absolute top-6 right-6 text-white text-3xl hover:scale-110 transition">
            <i class="fas fa-times"></i>
        </button>
        <img id="lightbox-img" src=""
            class="max-w-full max-h-full rounded-lg shadow-2xl transform scale-95 opacity-0 transition-all duration-300">
    </div>

    <script>
        function openLightbox(url) {
            const lb = document.getElementById('lightbox');
            const img = document.getElementById('lightbox-img');
            img.src = url;
            lb.classList.remove('hidden');
            setTimeout(() => {
                img.classList.remove('scale-95', 'opacity-0');
                img.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        // Suporte ao ESC para fechar
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') document.getElementById('lightbox').classList.add('hidden');
        });
    </script>

    <style>
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }

        .page-item .page-link {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
            font-weight: bold;
            transition: all 0.3s;
        }

        .page-item.active .page-link {
            background: var(--unn-azul-1);
            color: white;
            border-color: var(--unn-azul-1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .page-item:not(.active) .page-link:hover {
            background: white;
            color: var(--unn-azul-1);
            border-color: var(--unn-azul-1);
        }
    </style>
@endsection