@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container py-5">

    {{-- Tombol Navigasi: Kembali & Logout --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ url('/') }}" class="btn btn-outline-success">
            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>
        <a href="{{ route('logout') }}" class="btn btn-danger">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>

    <h2 class="fw-bold mb-4">Dashboard Admin Kelurahan Labukkang</h2>

    {{-- Alert Sukses --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Statistik --}}
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card shadow border-0 text-center">
                <div class="card-body">
                    <i class="bi bi-file-earmark-text display-5 text-primary"></i>
                    <h6 class="mt-2">Total Laporan</h6>
                    <h2 class="fw-bold text-primary">{{ $total }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow border-0 text-center">
                <div class="card-body">
                    <i class="bi bi-clock display-5 text-danger"></i>
                    <h6 class="mt-2">Menunggu</h6>
                    <h2 class="fw-bold text-danger">{{ $menunggu }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow border-0 text-center">
                <div class="card-body">
                    <i class="bi bi-arrow-repeat display-5 text-warning"></i>
                    <h6 class="mt-2">Diproses</h6>
                    <h2 class="fw-bold text-warning">{{ $diproses }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow border-0 text-center">
                <div class="card-body">
                    <i class="bi bi-check-circle display-5 text-success"></i>
                    <h6 class="mt-2">Selesai</h6>
                    <h2 class="fw-bold text-success">{{ $selesai }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik --}}
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white fw-bold">Status Laporan</div>
                <div class="card-body">
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white fw-bold">Laporan per Bulan</div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Peta (menampilkan SEMUA laporan) --}}
    <div class="card shadow border-0 mb-5">
        <div class="card-header bg-success text-white fw-bold">
            Peta Lokasi Laporan
        </div>
        <div class="card-body p-0">
            <div id="map" style="height:450px; width:100%;"></div>
        </div>
    </div>

    {{-- Filter & Pencarian --}}
    <div class="card shadow border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="Menunggu" {{ request('status')=='Menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="Diproses" {{ request('status')=='Diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="Selesai" {{ request('status')=='Selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Cari Deskripsi</label>
                    <input type="text" name="search" class="form-control" placeholder="Kata kunci..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" class="form-control" value="{{ request('tanggal_awal') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            @if(request()->hasAny(['status', 'search', 'tanggal_awal', 'tanggal_akhir']))
                <div class="mt-2">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Reset Filter
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Tabel (menampilkan data per halaman) --}}
    <div class="card shadow border-0">
        <div class="card-header bg-success text-white fw-bold">
            Daftar Laporan
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Lokasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporans as $laporan)
                    <tr>
                        <td>{{ ($laporans->currentPage() - 1) * $laporans->perPage() + $loop->iteration }}</td>
                        <td width="120">
                            <img src="{{ asset('uploads/'.$laporan->foto) }}"
                                 class="rounded shadow-sm"
                                 style="width:90px;height:90px;object-fit:cover;cursor:pointer"
                                 onclick="window.open('{{ asset('uploads/'.$laporan->foto) }}','_blank')"
                                 title="Klik untuk lihat besar">
                        </td>
                        <td>{{ $laporan->deskripsi }}</td>
                        <td>
                            @if($laporan->status == 'Menunggu')
                                <span class="badge bg-danger">{{ $laporan->status }}</span>
                            @elseif($laporan->status == 'Diproses')
                                <span class="badge bg-warning text-dark">{{ $laporan->status }}</span>
                            @else
                                <span class="badge bg-success">{{ $laporan->status }}</span>
                            @endif
                        </td>
                        <td>{{ $laporan->created_at->format('d M Y H:i') }}</td>
                        <td>
                            {{-- LINK GOOGLE MAPS MODE RUTE --}}
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $laporan->latitude }},{{ $laporan->longitude }}"
                               target="_blank"
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-geo-alt"></i>
                            </a>
                        </td>
                        <td>
                            <form action="{{ route('admin.status', $laporan->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                    <option value="Menunggu" {{ $laporan->status=='Menunggu' ? 'selected' : '' }}>Menunggu</option>
                                    <option value="Diproses" {{ $laporan->status=='Diproses' ? 'selected' : '' }}>Diproses</option>
                                    <option value="Selesai" {{ $laporan->status=='Selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                            </form>
                            <form action="{{ route('admin.destroy', $laporan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada laporan sesuai filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $laporans->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Grafik Status (Pie)
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'pie',
            data: {
                labels: ['Menunggu', 'Diproses', 'Selesai'],
                datasets: [{
                    data: [{{ $menunggu }}, {{ $diproses }}, {{ $selesai }}],
                    backgroundColor: ['#dc3545', '#ffc107', '#28a745'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Grafik per Bulan (Bar)
        const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
        const bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const dataBulan = @json(array_values($chartData));
        new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: bulanLabels,
                datasets: [{
                    label: 'Jumlah Laporan',
                    data: dataBulan,
                    backgroundColor: '#28a745',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, stepSize: 1 } }
            }
        });

        // Peta (menampilkan SEMUA data dari mapLaporans)
        if (typeof map !== 'undefined') {
            map.eachLayer(function(layer) {
                if (layer instanceof L.Marker) {
                    map.removeLayer(layer);
                }
            });

            @foreach($mapLaporans as $laporan)
                var color = 'red';
                @if($laporan->status == 'Diproses')
                    color = 'orange';
                @elseif($laporan->status == 'Selesai')
                    color = 'green';
                @endif

                var markerIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-'+color+'.png',
                    shadowUrl: 'https://unpkg.com/leaflet/dist/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41]
                });

                var deskripsi = @json($laporan->deskripsi);
                var foto = @json($laporan->foto);
                var status = @json($laporan->status);
                var lat = @json($laporan->latitude);
                var lng = @json($laporan->longitude);

                L.marker([lat, lng], { icon: markerIcon })
                    .addTo(map)
                    .bindPopup(`
                        <div style="width:220px">
                            <img src="/uploads/${foto}" class="img-fluid rounded mb-2">
                            <b>Status:</b> ${status}
                            <br>
                            <b>Deskripsi:</b><br>
                            ${deskripsi}
                            <br><br>
                            {{-- LINK POPUP GOOGLE MAPS MODE RUTE --}}
                            <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}"
                               target="_blank"
                               class="btn btn-success btn-sm w-100">
                               Buka Rute
                            </a>
                        </div>
                    `);
            @endforeach

            @if($mapLaporans->count())
                var bounds = L.latLngBounds([
                    @foreach($mapLaporans as $l)
                        [{{ $l->latitude }}, {{ $l->longitude }}],
                    @endforeach
                ]);
                map.fitBounds(bounds, { padding: [50, 50] });
            @else
                map.setView([-4.0134, 119.6256], 13);
            @endif
        }
    });
</script>
@endpush