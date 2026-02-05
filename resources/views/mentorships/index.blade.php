@extends('layouts.app')

@section('title', 'Mentorias Disponíveis')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Mentorias</h1>
                <p class="text-gray-600">Conecte-se com especialistas e acelere seu crescimento.</p>
            </div>
        </div>

        @if($mentorships->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($mentorships as $mentorship)
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="h-48 bg-gray-200 relative">
                            {{-- Placeholder ou imagem da mentoria --}}
                            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                                <i class="fas fa-chalkboard-teacher text-5xl"></i>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $mentorship->title }}</h3>
                            <p class="text-gray-600 mb-4 line-clamp-2">{{ $mentorship->description ?? 'Sem descrição.' }}</p>

                            <div class="flex justify-between items-center mt-4">
                                <span class="text-blue-600 font-bold">
                                    {{ $mentorship->price ? 'R$ ' . number_format($mentorship->price, 2, ',', '.') : 'Gratuito' }}
                                </span>
                                <a href="#" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $mentorships->links() }}
            </div>
        @else
            <div class="text-center py-12 bg-white rounded-xl shadow-sm">
                <div class="text-gray-300 mb-4">
                    <i class="fas fa-search text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800">Nenhuma mentoria encontrada</h3>
                <p class="text-gray-600 mt-2">Novas oportunidades serão adicionadas em breve.</p>
            </div>
        @endif
    </div>
@endsection