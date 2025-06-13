@extends('layouts.map_home')
@section('content')

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>{{ isset($marker) ? 'Edit Marker' : 'Tambah Marker Baru' }}</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ isset($marker) ? route('marker.update', $marker->id) : route('marker.store') }}" method="POST">
                        @csrf
                        @if(isset($marker))
                            @method('PUT')
                        @endif

                        <div class="form-group mb-3">
                            <label for="name">Nama Marker</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" 
                                value="{{ isset($marker) ? $marker->name : old('name', 'Marker Baru') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" 
                                rows="3">{{ isset($marker) ? $marker->description : old('description', 'Deskripsi marker') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="latitude">Latitude</label>
                                    <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" 
                                        value="{{ isset($marker) ? $marker->latitude : old('latitude', request('lat')) }}" required>
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="longitude">Longitude</label>
                                    <input type="text" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" 
                                        value="{{ isset($marker) ? $marker->longitude : old('longitude', request('lng')) }}" required>
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">
                                {{ isset($marker) ? 'Update Marker' : 'Simpan Marker' }}
                            </button>
                            <a href="{{ route('map.show') }}" class="btn btn-secondary">Kembali ke Peta</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!isset($marker))
    <div class="container mt-3">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5>Preview Lokasi</h5>
                    </div>
                    <div class="card-body">
                        <div id="preview-map" style="width: 100%; height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dapatkan koordinat dari URL parameters
            var lat = parseFloat(document.getElementById('latitude').value) || -8.797383470360561;
            var lng = parseFloat(document.getElementById('longitude').value) || 115.18563064391185;
            
            // Inisialisasi peta preview
            var previewMap = L.map('preview-map').setView([lat, lng], 15);
            
            // Tambahkan tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(previewMap);
            
            // Tambahkan marker preview
            var previewMarker = L.marker([lat, lng], { draggable: true }).addTo(previewMap);
            
            // Update koordinat saat marker di-drag
            previewMarker.on('dragend', function() {
                var position = previewMarker.getLatLng();
                document.getElementById('latitude').value = position.lat.toFixed(7);
                document.getElementById('longitude').value = position.lng.toFixed(7);
            });
            
            // Update marker saat input koordinat berubah
            document.getElementById('latitude').addEventListener('change', updateMarkerPosition);
            document.getElementById('longitude').addEventListener('change', updateMarkerPosition);
            
            function updateMarkerPosition() {
                var newLat = parseFloat(document.getElementById('latitude').value);
                var newLng = parseFloat(document.getElementById('longitude').value);
                
                if (!isNaN(newLat) && !isNaN(newLng)) {
                    previewMarker.setLatLng([newLat, newLng]);
                    previewMap.setView([newLat, newLng], 15);
                }
            }
        });
    </script>
@endif

@endsection