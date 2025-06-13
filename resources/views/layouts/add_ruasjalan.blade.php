@extends('layouts.map_home')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4>Tambah Ruas Jalan</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form id="ruasjalanForm" action="{{ route('ruasjalan.store') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label>Nama Ruas Jalan</label>
                            <input type="text" name="nama_ruas" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Kode Ruas</label>
                            <input type="text" name="kode_ruas" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Desa ID</label>
                            <input type="number" name="desa_id" class="form-control" value="473" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Panjang Jalan (meter)</label>
                            <input type="number" name="panjang" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Lebar Jalan (meter)</label>
                            <input type="number" name="lebar" class="form-control" required>
                        </div>

                        <!-- ✅ Eksisting Jalan -->
                        <div class="form-group mb-3">
                            <label>Eksisting Jalan</label>
                            <select name="eksisting_id" class="form-control" required>
                                <option value="">-- Pilih Eksisting --</option>
                                @foreach($eksistingOptions as $item)
                                <option value="{{ $item['id'] }}">{{ $item['eksisting'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- ✅ Kondisi Jalan -->
                        <div class="form-group mb-3">
                            <label>Kondisi Jalan</label>
                            <select name="kondisi_id" class="form-control" required>
                                <option value="">-- Pilih Kondisi --</option>
                                @foreach($kondisiOptions as $item)
                                <option value="{{ $item['id'] }}">{{ $item['kondisi'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- ✅ Jenis Jalan -->
                        <div class="form-group mb-3">
                            <label>Jenis Jalan</label>
                            <select name="jenisjalan_id" class="form-control" required>
                                <option value="">-- Pilih Jenis Jalan --</option>
                                @foreach($jenisJalanOptions as $item)
                                <option value="{{ $item['id'] }}">{{ $item['jenisjalan'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2"></textarea>
                        </div>

                        <input type="hidden" name="paths" id="encodedPaths">

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-success">Simpan Ruas Jalan</button>
                            <button type="button" class="btn btn-warning" onclick="resetPolyline()">Reset Titik</button>
                            <a href="{{ route('map.show') }}" class="btn btn-secondary">Kembali ke Peta</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5>Petakan Titik Ruas Jalan</h5>
                </div>
                <div class="card-body">
                    <div id="map" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script polyline -->
<script src="https://unpkg.com/@mapbox/polyline"></script>
<script>
    let map = L.map('map').setView([-8.797383470360561, 115.18563064391185], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    setTimeout(() => {
        map.invalidateSize();
    }, 300);

    let points = [];
    let markers = [];
    let jalanPolyline = null; // Ubah nama ini

    map.on('click', function(e) {
        const marker = L.marker(e.latlng, {
            draggable: true
        }).addTo(map);
        markers.push(marker);
        points.push([e.latlng.lat, e.latlng.lng]);

        marker.on('dragend', function(evt) {
            const index = markers.indexOf(marker);
            if (index !== -1) {
                const pos = marker.getLatLng();
                points[index] = [pos.lat, pos.lng];
                drawPolyline();
            }
        });

        drawPolyline();
    });

    function drawPolyline() {
        if (jalanPolyline) {
            map.removeLayer(jalanPolyline);
        }
        jalanPolyline = L.polyline(points, {
            color: 'blue'
        }).addTo(map);
    }

    function resetPolyline() {
        markers.forEach(marker => map.removeLayer(marker));
        if (jalanPolyline) map.removeLayer(jalanPolyline);
        markers = [];
        points = [];
        jalanPolyline = null;
    }

    document.getElementById('ruasjalanForm').addEventListener('submit', function(e) {
        if (points.length < 2) {
            alert('Minimal 2 titik diperlukan untuk membuat ruas jalan!');
            e.preventDefault();
            return;
        }

        const encoded = polyline.encode(points);
        console.log('DEBUG polyline:', encoded); // ✅ debug hasil encode
        console.log('DEBUG points:', points); // ✅ debug array koordinat
        document.getElementById('encodedPaths').value = encoded;
    });
</script>
@endsection