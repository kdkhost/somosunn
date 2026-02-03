@extends('layouts.app')

@section('content')
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden border border-gray-100">
        @if($event->color)
        <div class="h-2 w-full" style="background-color: {{ $event->color }};"></div>
        @endif
        <div class="p-6 md:p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">{{ $event->title }}</h1>
            
            <div class="flex items-center text-gray-500 mb-6 text-sm md:text-base">
                <i class="far fa-calendar-alt mr-2"></i> 
                <span class="font-medium">
                    {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') }}
                    @if($event->end_at)
                         - {{ \Carbon\Carbon::parse($event->end_at)->format('d/m/Y H:i') }}
                    @endif
                </span>
            </div>

            @if($event->description)
            <div class="mb-8 prose max-w-none text-gray-600">
                <h5 class="text-xl font-semibold text-gray-800 mb-2">Sobre o Evento</h5>
                <p>{!! nl2br(e($event->description)) !!}</p>
            </div>
            @endif

            @if($event->location || $event->address)
            <div class="mb-8 bg-gray-50 p-4 rounded-lg">
                <h5 class="text-xl font-semibold text-gray-800 mb-2">Localização</h5>
                @if($event->location)
                <p class="mb-1 text-gray-700"><strong>Local:</strong> {{ $event->location }}</p>
                @endif
                @if($event->address)
                <p class="mb-1 text-gray-700 flex items-start"><i class="fas fa-map-marker-alt mt-1 mr-2 text-red-500"></i> {{ $event->address }}</p>
                @endif
            </div>
            @endif

            @if($event->latitude && $event->longitude)
            <div class="mb-6">
                <div id="eventMap" class="w-full h-80 rounded-lg border border-gray-300 z-0"></div>
                <div class="mt-4 flex flex-col sm:flex-row gap-3">
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $event->latitude }},{{ $event->longitude }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition font-medium">
                        <i class="fas fa-route mr-2"></i> Traçar Rota (Google Maps)
                    </a>
                    <a href="https://www.openstreetmap.org/directions?to={{ $event->latitude }},{{ $event->longitude }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 bg-white rounded-md hover:bg-gray-50 transition font-medium">
                        <i class="fas fa-map-marked-alt mr-2"></i> Rota (OpenStreetMap)
                    </a>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($event->latitude && $event->longitude)
        var map = L.map('eventMap').setView([{{ $event->latitude }}, {{ $event->longitude }}], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        L.marker([{{ $event->latitude }}, {{ $event->longitude }}])
            .addTo(map)
            .bindPopup("<b>{{ $event->title }}</b><br>{{ $event->address }}")
            .openPopup();
        @endif
    });
</script>
@endpush
@endsection
