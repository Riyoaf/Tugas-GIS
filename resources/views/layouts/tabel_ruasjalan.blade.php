@extends('layouts.map_home')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4>Data Ruas Jalan</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <a href="{{ route('map.show') }}" class="btn btn-secondary mb-3">Kembali ke Peta</a>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Ruas</th>
                                    <th>Kode Ruas</th>
                                    <th>Panjang (m)</th>
                                    <th>Lebar (m)</th>
                                    <th>Eksisting</th>
                                    <th>Kondisi</th>
                                    <th>Jenis Jalan</th>
                                    <th>Desa</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ruasjalan as $item)
                                <tr>
                                    <td>{{ $item['id'] ?? '-' }}</td>
                                    <td>{{ $item['nama_ruas'] ?? '-' }}</td>
                                    <td>{{ $item['kode_ruas'] ?? '-' }}</td>
                                    <td>{{ $item['panjang'] ?? '-' }}</td>
                                    <td>{{ $item['lebar'] ?? '-' }}</td>
                                    <td>
                                        @if(isset($item['meksisting']))
                                        {{ $item['meksisting']['eksisting'] ?? '-' }}
                                        @else
                                        {{ $item['eksisting'] ?? '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($item['mkondisi']))
                                        {{ $item['mkondisi']['kondisi'] ?? '-' }}
                                        @else
                                        {{ $item['kondisi'] ?? '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($item['mjenisjalan']))
                                        {{ $item['mjenisjalan']['jenisjalan'] ?? '-' }}
                                        @else
                                        {{ $item['jenisjalan'] ?? $item['jenis_jalan'] ?? '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($item['desa']))
                                        {{ $item['desa']['desa'] ?? '-' }}
                                        @else
                                        {{ $item['desa_id'] ?? '-' }}
                                        @endif
                                    </td>
                                    <td>{{ $item['keterangan'] ?? '-' }}</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary disabled" title="Fitur dalam pengembangan">Edit</a>
                                        <a href="#" class="btn btn-sm btn-danger disabled" title="Fitur dalam pengembangan">Hapus</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data ruas jalan ditemukan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(count($ruasjalan) > 0)
                    <div class="mt-3">
                        <small class="text-muted">Total: {{ count($ruasjalan) }} ruas jalan</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection