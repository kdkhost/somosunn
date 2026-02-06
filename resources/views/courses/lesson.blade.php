@extends('layouts.app')

@section('title', $lesson->title . ' - ' . $course->title)

@section('content')
<div class="flex flex-col lg:flex-row min-h-screen bg-gray-100">
    <!-- Sidebar - Playlist -->
    <div class="w-full lg:w-80 bg-white border-r border-gray-200 flex-shrink-0 h-auto lg:h-screen lg:sticky lg:top-0 overflow-y-auto z-10">
        <div class="p-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-800 text-lg leading-tight">{{ $course->title }}</h2>
            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full" style="width: 0%"></div> <!-- Progress Bar Placeholder -->
            </div>
            <p class="text-xs text-gray-500 mt-1">0% Concluído</p>
        </div>
        <div class="py-2">
            @foreach($course->lessons()->orderBy('order')->get() as $l)
                <a href="{{ route('courses.lessons.show', [$course->id, $l->id]) }}" 
                   class="flex items-center p-4 hover:bg-gray-50 transition border-l-4 {{ $l->id == $lesson->id ? 'border-[#1F5EDB] bg-blue-50' : 'border-transparent' }}">
                    <div class="mr-3">
                        @if($l->id == $lesson->id)
                            <i class="fas fa-play text-[#1F5EDB]"></i>
                        @else
                            <i class="far fa-circle text-gray-400"></i>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium {{ $l->id == $lesson->id ? 'text-[#1F5EDB]' : 'text-gray-700' }}">
                            {{ $l->order }}. {{ $l->title }}
                        </p>
                        @if($l->duration > 0)
                        <p class="text-xs text-gray-400 mt-1">
                            <i class="far fa-clock mr-1"></i> {{ gmdate("H:i", $l->duration) }}
                        </p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Main Content - Player -->
    <div class="flex-1 p-6 md:p-10 overflow-y-auto">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $lesson->title }}</h1>
            
            <div class="aspect-w-16 aspect-h-9 bg-black rounded-xl overflow-hidden shadow-2xl mb-8">
                @if($lesson->video_url)
                    @php $usePlyr = (string) \App\Models\Setting::get('video_player_enabled', '1') === '1'; @endphp
                    @if($usePlyr)
                        <div class="w-full h-full min-h-[400px]" data-unn-video-player data-video-url="{{ $lesson->video_url }}"></div>
                    @else
                        <iframe src="{{ str_replace('youtu.be/', 'youtube.com/embed/', $lesson->video_url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="w-full h-full min-h-[400px]"></iframe>
                    @endif
                @else
                    <div class="flex items-center justify-center h-full text-white">
                        <p>Nenhum vídeo disponível para esta aula.</p>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h3 class="text-lg font-bold mb-4">Conteúdo da Aula</h3>
                <div class="prose max-w-none text-gray-700">
                    {!! \App\Support\RichText::toHtml($lesson->content) !!}
                </div>

                @if($lesson->attachments->count() > 0)
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <h4 class="text-md font-bold mb-3 flex items-center text-gray-800">
                        <i class="fas fa-paperclip mr-2 text-gray-500"></i> Materiais de Apoio
                    </h4>
                    <div class="grid gap-3">
                        @foreach($lesson->attachments as $attachment)
                        <a href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank" class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition group">
                            <div class="flex items-center overflow-hidden">
                                <div class="bg-blue-100 text-blue-600 w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 mr-3">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="truncate">
                                    <p class="text-sm font-medium text-gray-900 truncate group-hover:text-blue-600 transition">{{ $attachment->file_name }}</p>
                                    <p class="text-xs text-gray-500">{{ round($attachment->file_size / 1024 / 1024, 2) }} MB</p>
                                </div>
                            </div>
                            <i class="fas fa-download text-gray-400 group-hover:text-blue-600 transition"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="flex justify-between items-center">
                @if($previous)
                    <a href="{{ route('courses.lessons.show', [$course->id, $previous->id]) }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 font-medium transition">
                        <i class="fas fa-arrow-left mr-2"></i> Anterior
                    </a>
                @else
                    <div></div>
                @endif

                @if($next)
                    <a href="{{ route('courses.lessons.show', [$course->id, $next->id]) }}" class="px-5 py-2 bg-[#1F5EDB] text-white rounded-lg hover:bg-blue-700 font-medium transition">
                        Próxima <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                @else
                    <button class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition">
                        Concluir Curso <i class="fas fa-check ml-2"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
