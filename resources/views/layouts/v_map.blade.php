@extends('layouts.map_home')
@section('content')

<!-- Dropdown untuk memilih layer -->
<select id="layerSelect" style="margin: 10px; padding: 8px; border-radius: 5px;">
    <option value="streets">Streets</option>
    <option value="grayscale">Grayscale</option>
    <option value="satellite">Satellite</option>
</select>

<!-- Tombol untuk menambahkan marker -->
<button id="addMarkerBtn" style="margin: 10px; padding: 8px 12px; background-color: blue; color: white; border: none; cursor: pointer;">
    Add Marker
</button>

<div id="map" style="width: 100%; height: 600px; margin-left: 10px"></div>

<script>
    var streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    });

    var grayscale = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://carto.com/">CartoDB</a>'
    });

    var satellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '&copy; Google Maps'
    });

    var map = L.map('map', {
        center: [-8.797383470360561, 115.18563064391185], // Koordinat awal
        zoom: 10,
        layers: [streets] // Default layer
    });

    var layers = {
        "streets": streets,
        "grayscale": grayscale,
        "satellite": satellite,
    };

    var currentLayer = streets; // Set layer awal

    // Event listener untuk dropdown
    document.getElementById('layerSelect').addEventListener('change', function (e) {
        var selectedLayer = e.target.value;

        if (currentLayer) {
            map.removeLayer(currentLayer); // Hapus layer lama
        }

        currentLayer = layers[selectedLayer]; // Set layer baru
        map.addLayer(currentLayer);
    });

    var isAddingMarker = false;
    var markers = [];

    // Event Listener untuk tombol Add Marker
    document.getElementById('addMarkerBtn').addEventListener('click', function () {
        isAddingMarker = !isAddingMarker;

        if (isAddingMarker) {
            alert("Klik pada peta untuk menambahkan marker!");
        }
    });

    // Event Listener untuk menambahkan marker saat peta diklik
    map.on('click', function (e) {
        if (isAddingMarker) {
            var marker = L.marker([e.latlng.lat, e.latlng.lng], { draggable: true }).addTo(map);
            
            marker.bindPopup(createPopupContent(marker)).openPopup();

            markers.push(marker); // Simpan marker ke array

            isAddingMarker = false; // Matikan mode tambah marker setelah satu kali klik
        }
    });

    // Fungsi untuk membuat isi popup dengan form edit dan tombol hapus
    function createPopupContent(marker) {
        var lat = marker.getLatLng().lat;
        var lng = marker.getLatLng().lng;

        return `
            <div>
                <label for="markerName">Nama Marker:</label>
                <input type="text" id="markerName" value="Marker Baru">
                <button onclick="saveMarkerDetails(${lat}, ${lng}, this)">Simpan</button>
            </div>
        `;
    }

    // Fungsi untuk menyimpan detail marker setelah diedit
    function saveMarkerDetails(lat, lng, button) {
        var inputField = button.previousElementSibling; // Ambil input field
        var markerName = inputField.value;

        // Temukan marker berdasarkan koordinat
        markers.forEach(marker => {
            if (marker.getLatLng().lat === lat && marker.getLatLng().lng === lng) {
                marker.bindPopup(`<b>${markerName}</b><br>Lat: ${lat}, Lng: ${lng}`).openPopup();
            }
        });
    }
    
</script>

@endsection
