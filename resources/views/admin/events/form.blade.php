@extends('admin.layouts.app')

@section('page_title', $event->exists ? 'Editar Evento' : 'Novo Evento')

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" enctype="multipart/form-data" action="{{ $event->exists ? route('admin.events.update',$event) : route('admin.events.store') }}">
        @csrf
        @if($event->exists) @method('PUT') @endif
        <div class="form-group mb-2"><label>Título</label><input name="title" class="form-control" value="{{ old('title',$event->title) }}" required></div>
        <div class="form-group mb-2"><label>Início</label><input name="start_at" type="datetime-local" class="form-control" value="{{ old('start_at',$event->start_at) }}"></div>
        <div class="form-group mb-2"><label>Preço</label><input name="price" class="form-control mask-money" value="{{ old('price',$event->price) }}"></div>
        <div class="form-group mb-2">
            <label>Imagem do evento</label>
            <input type="hidden" name="remove_image" value="0">
            <div class="upload-box" data-max-size="5242880" data-existing-url="{{ $event->image ? asset('storage/'.$event->image) : '' }}" data-remove-input="[name='remove_image']">
                <input type="file" name="image" accept="image/*" class="d-none">
                <div class="upload-preview mb-2"></div>
                <div class="upload-meta text-muted"></div>
                <small class="text-muted upload-help"></small>
                <div class="progress upload-progress progress-sm d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-2"><label>Local (Nome do Local)</label><input name="location" class="form-control" value="{{ old('location',$event->location) }}"></div>
                <div class="form-group mb-2"><label>Endereço Completo</label><div class="input-group"><input name="address" id="addressInput" class="form-control" value="{{ old('address',$event->address) }}"><div class="input-group-append"><button type="button" class="btn btn-secondary" id="searchBtn"><i class="fas fa-search"></i> Buscar</button></div></div></div>
                <div class="form-group mb-2"><label>Latitude</label><input name="latitude" id="latInput" class="form-control" value="{{ old('latitude',$event->latitude) }}" readonly></div>
                <div class="form-group mb-2"><label>Longitude</label><input name="longitude" id="lngInput" class="form-control" value="{{ old('longitude',$event->longitude) }}" readonly></div>
                <input type="hidden" name="published" value="0">
                <div class="form-check mb-2">
                    <input type="checkbox" name="published" value="1" class="form-check-input" {{ old('published', $event->published) ? 'checked' : '' }}>
                    <label class="form-check-label">Publicado</label>
                </div>
            </div>
            <div class="col-md-6">
                <label>Mapa (Clique para marcar)</label>
                <div id="map" style="height: 300px; border-radius: 8px; border: 1px solid #ddd;"></div>
            </div>
        </div>
        
        <button class="btn btn-primary mt-3">Salvar</button>
    </form>
</div></div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Init map
        var initialLat = {{ $event->latitude ?? '-23.5505' }};
        var initialLng = {{ $event->longitude ?? '-46.6333' }};
        var zoom = {{ $event->latitude ? 15 : 10 }};

        var map = L.map('map').setView([initialLat, initialLng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker;
        if ({{ $event->latitude ? 'true' : 'false' }}) {
            marker = L.marker([initialLat, initialLng]).addTo(map);
        }

        // Click to set marker
        map.on('click', function(e) {
            setMarker(e.latlng.lat, e.latlng.lng);
        });

        function setMarker(lat, lng) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }
            document.getElementById('latInput').value = lat;
            document.getElementById('lngInput').value = lng;
        }

        // Geocoding (Simple Nominatim)
        document.getElementById('searchBtn').addEventListener('click', function() {
            var query = document.getElementById('addressInput').value;
            if (!query) return;
            
            toastr.info('Buscando endereço...');
            fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        var lat = data[0].lat;
                        var lon = data[0].lon;
                        map.setView([lat, lon], 16);
                        setMarker(lat, lon);
                        toastr.success('Endereço encontrado!');
                    } else {
                        toastr.error('Endereço não encontrado.');
                    }
                })
                .catch(err => toastr.error('Erro na busca.'));
        });
    });
</script>
@endpush
@endsection
