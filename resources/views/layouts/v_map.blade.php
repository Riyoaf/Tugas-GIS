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
    Tambah Marker
</button>

<!-- button tambah ruas jalan -->
<a href="{{ route('ruasjalan.create') }}" class="btn btn-warning" style="margin: 10px; padding: 8px 12px; color: white; text-decoration: none;">
    Tambah Ruas Jalan
</a>
<!-- tombol lihat tabel ruas -->
<a href="{{ route('ruasjalan.tabel') }}" class="btn btn-info" style="margin: 10px; padding: 8px 12px; color: white; text-decoration: none;">
    Lihat Tabel Ruas Jalan
</a>

<!-- Tombol refresh ruas jalan untuk debugging -->
<button id="refreshRuasBtn" style="margin: 10px; padding: 8px 12px; background-color: green; color: white; border: none; cursor: pointer;">
    Refresh Ruas Jalan
</button>

<div id="map" style="width: 100%; height: 600px; margin-left: 10px"></div>

<!-- CSRF Token untuk request AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Tambahkan library polyline dari Mapbox -->
<script src="https://unpkg.com/@mapbox/polyline"></script>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi layer peta
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

        // Inisialisasi peta
        var map = L.map('map', {
            center: [-8.797383470360561, 115.18563064391185], // Koordinat awal
            zoom: 10,
            layers: [streets] // Layer default
        });

        // Layer dasar yang tersedia 
        var layers = {
            "streets": streets,
            "grayscale": grayscale,
            "satellite": satellite,
        };

        var currentLayer = streets; // Set layer awal

        // Event listener untuk dropdown layer
        document.getElementById('layerSelect').addEventListener('change', function(e) {
            var selectedLayer = e.target.value;

            if (currentLayer) {
                map.removeLayer(currentLayer); // Hapus layer lama
            }

            currentLayer = layers[selectedLayer]; // Set layer baru
            map.addLayer(currentLayer);
        });

        // Membuat peta tersedia secara global
        window.mapInstance = map;

        // Variable untuk menyimpan layer group ruas jalan
        var ruasJalanLayer = L.layerGroup().addTo(map);

        // Variable untuk mode tambah marker
        var isAddingMarker = false;

        // Event listener untuk tombol tambah marker
        document.getElementById('addMarkerBtn').addEventListener('click', function() {
            isAddingMarker = !isAddingMarker;

            if (isAddingMarker) {
                this.style.backgroundColor = 'red';
                this.textContent = 'Batal';
                alert("Klik pada peta untuk menambahkan marker!");
            } else {
                this.style.backgroundColor = 'blue';
                this.textContent = 'Tambah Marker';
            }
        });

        // Event listener untuk klik peta
        map.on('click', function(e) {
            if (isAddingMarker) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;

                // Redirect ke halaman add marker dengan parameter koordinat
                window.location.href = '{{ route("marker.add") }}?lat=' + lat + '&lng=' + lng;

                // Reset mode tambah marker
                isAddingMarker = false;
                var addBtn = document.getElementById('addMarkerBtn');
                addBtn.style.backgroundColor = 'blue';
                addBtn.textContent = 'Tambah Marker';
            }
        });

        // Event listener untuk tombol refresh ruas jalan
        document.getElementById('refreshRuasBtn').addEventListener('click', function() {
            console.log('🔄 Refresh ruas jalan dimulai...');
            loadRuasJalan();
        });

        // Muat marker yang ada dari database saat halaman dimuat
        loadMarkers();

        // Muat ruas jalan saat halaman dimuat
        loadRuasJalan();

        // Fungsi untuk memuat marker dari database
        function loadMarkers() {
            fetch('{{ route("marker.getAll") }}')
                .then(response => response.json())
                .then(data => {
                    data.forEach(markerData => {
                        // Buat marker dari data database
                        var marker = L.marker([markerData.latitude, markerData.longitude], {
                            draggable: true
                        }).addTo(map);

                        // Buat konten popup untuk marker yang sudah ada
                        var popupContent = `
                            <div>
                                <strong>${markerData.name}</strong><br>
                                ${markerData.description}<br>
                                <small>Lat: ${markerData.latitude}, Lng: ${markerData.longitude}</small><br>
                                <a href="{{ url('marker/edit') }}/${markerData.id}" class="btn btn-sm btn-primary">Edit</a>
                                <button onclick="deleteMarker(${markerData.id}, this)" class="btn btn-sm btn-danger">Hapus</button>
                            </div>
                        `;

                        marker.bindPopup(popupContent);

                        // Store marker ID for reference
                        marker.markerId = markerData.id;

                        // Update marker position when dragged
                        marker.on('dragend', function() {
                            const newLat = this.getLatLng().lat.toFixed(6);
                            const newLng = this.getLatLng().lng.toFixed(6);

                            // Update marker in database
                            updateMarkerPosition(this.markerId, newLat, newLng);
                        });
                    });
                })
                .catch(error => console.error('Error saat memuat marker:', error));
        }

        // Fungsi untuk menghapus marker
        window.deleteMarker = function(markerId, button) {
            if (confirm('Apakah Anda yakin ingin menghapus marker ini?')) {
                fetch('{{ url("marker/delete") }}/' + markerId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload halaman untuk memperbarui marker
                            window.location.reload();
                        } else {
                            alert('Gagal menghapus marker');
                        }
                    })
                    .catch(error => console.error('Error saat menghapus marker:', error));
            }
        };

        // Fungsi untuk memperbarui posisi marker setelah di-drag
        function updateMarkerPosition(markerId, lat, lng) {
            fetch('{{ url("marker/updatePosition") }}/' + markerId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        latitude: parseFloat(lat),
                        longitude: parseFloat(lng)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Posisi marker diperbarui:', data);
                })
                .catch(error => console.error('Error saat memperbarui posisi marker:', error));
        }

        // Fungsi untuk memuat ruas jalan
        function loadRuasJalan() {
            console.log('🚀 Memulai load ruas jalan...');

            // Bersihkan layer ruas jalan yang ada
            ruasJalanLayer.clearLayers();

            // Test endpoint langsung terlebih dahulu
            console.log('🔗 Testing endpoint:', '{{ route("ruasjalan.getAll") }}');

            fetch('{{ route("ruasjalan.getAll") }}')
                .then(response => {
                    console.log('📡 Response status:', response.status);
                    console.log('📡 Response headers:', response.headers);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text(); // Gunakan text() dulu untuk debug
                })
                .then(text => {
                    console.log('📦 Raw response text:', text);

                    // Parse JSON
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('❌ Error parsing JSON:', e);
                        console.error('❌ Raw text:', text);
                        return;
                    }

                    console.log('📦 Parsed data:', data);
                    console.log('📦 Data type:', typeof data);
                    console.log('📦 Is array:', Array.isArray(data));

                    // Cek apakah data adalah array
                    if (!Array.isArray(data)) {
                        console.error('❌ Data bukan array:', typeof data, data);
                        // Coba cek apakah data ada di dalam property lain
                        if (data && typeof data === 'object') {
                            console.log('🔍 Checking for data in object properties...');
                            Object.keys(data).forEach(key => {
                                console.log(`🔍 Property "${key}":`, data[key]);
                                if (Array.isArray(data[key])) {
                                    console.log(`✅ Found array in property "${key}"`);
                                }
                            });
                        }
                        return;
                    }

                    if (data.length === 0) {
                        console.warn('⚠️ Data ruas jalan kosong - tidak ada data di database');
                        alert('Data ruas jalan kosong. Pastikan sudah ada data ruas jalan di database.');
                        return;
                    }

                    console.log(`✅ Memproses ${data.length} ruas jalan...`);

                    data.forEach((item, index) => {
                        console.log(`🔍 Processing item ${index + 1}:`, item);

                        // Cek apakah paths ada dan valid
                        if (!item.paths) {
                            console.warn(`⚠️ Item ${index + 1}: paths tidak ada`);
                            return;
                        }

                        if (typeof item.paths !== 'string') {
                            console.warn(`⚠️ Item ${index + 1}: paths bukan string:`, typeof item.paths);
                            return;
                        }

                        if (item.paths.length === 0) {
                            console.warn(`⚠️ Item ${index + 1}: paths kosong`);
                            return;
                        }

                        try {
                            console.log(`🔧 Decoding paths untuk ${item.nama_ruas || 'Unnamed'}:`, item.paths);

                            // Decode polyline
                            const decoded = polyline.decode(item.paths);
                            console.log(`✅ Decoded coordinates:`, decoded);

                            // Konversi ke format Leaflet
                            const latlngs = decoded.map(coord => L.latLng(coord[0], coord[1]));

                            // Buat polyline
                            const line = L.polyline(latlngs, {
                                color: 'red',
                                weight: 4,
                                opacity: 0.8
                            });

                            // Buat popup content yang lebih detail
                            const popupContent = `
                                <div style="min-width: 200px;">
                                    <strong>${item.nama_ruas || 'Tanpa Nama'}</strong><br>
                                    <small>Kode: ${item.kode_ruas || 'N/A'}</small><br>
                                    <small>Panjang: ${item.panjang || 'N/A'} m</small><br>
                                    <small>Lebar: ${item.lebar || 'N/A'} m</small><br>
                                    <small>Keterangan: ${item.keterangan || 'Tidak ada'}</small>
                                </div>
                            `;

                            line.bindPopup(popupContent);

                            // Tambahkan ke layer group
                            ruasJalanLayer.addLayer(line);

                            console.log(`✅ Ruas jalan "${item.nama_ruas}" berhasil ditambahkan ke peta`);
                        } catch (error) {
                            console.error(`❌ Gagal decode polyline untuk item ${index + 1}:`, item, error);
                        }
                    });

                    console.log(`🎉 Selesai memproses ruas jalan. Total yang berhasil dimuat: ${ruasJalanLayer.getLayers().length}`);
                })
                .catch(error => {
                    console.error('❌ Error saat mengambil ruas jalan:', error);
                    alert('Gagal memuat ruas jalan. Cek console untuk detail error.');
                });
        }
    });
</script>

@endsection