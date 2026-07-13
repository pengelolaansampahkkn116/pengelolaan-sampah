<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
        }

        .navbar {
            background: #ffffff;
        }

        .card {
            border-radius: 20px;
        }

        /* MAP - biarkan setiap halaman menentukan tinggi */
        #map {
            width: 100%;
            min-height: 400px;
        }

        .btn-success {
            background: #22c55e;
            border: none;
        }

        .btn-success:hover {
            background: #16a34a;
        }

        .leaflet-popup-content {
            margin: 12px;
        }
    </style>
</head>

<body>

@yield('content')

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    // =========================
    // DEKLARASI GLOBAL
    // =========================
    let map;
    window.marker = null; // marker GPS dari halaman home

    // =========================
    // JALANKAN SETELAH DOM SIAP
    // =========================
    document.addEventListener("DOMContentLoaded", function() {
        const mapElement = document.getElementById("map");
        if (!mapElement) {
            return;
        }

        // =========================
        // INISIALISASI PETA
        // =========================
        map = L.map("map").setView([-4.0134, 119.6256], 15);
        window.map = map; // EXPOSE ke global agar dashboard bisa pakai

        L.tileLayer(
            "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "© OpenStreetMap"
            }
        ).addTo(map);

        // =========================
        // ICON MARKER MERAH (untuk laporan)
        // =========================
        const laporanIcon = L.icon({
            iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png",
            shadowUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        // =========================
        // MARKER DARI DATABASE
        // HANYA UNTUK HALAMAN BUKAN ADMIN DASHBOARD
        // =========================
        @if(!request()->routeIs('admin.dashboard'))
            @isset($laporans)
                @foreach($laporans as $laporan)
                    L.marker(
                        [
                            {{ $laporan->latitude }},
                            {{ $laporan->longitude }}
                        ], {
                            icon: laporanIcon
                        }
                    )
                    .addTo(map)
                    .bindPopup(`
                        <div style="width:240px;">
                            <h6 class="fw-bold text-success mb-2">
                                📍 Laporan Limbah
                            </h6>
                            <img
                                src="/uploads/{{ $laporan->foto }}"
                                class="img-fluid rounded mb-2"
                                style="width:100%;">
                            <p class="mb-2">
                                <strong>📝 Deskripsi</strong><br>
                                {{ $laporan->deskripsi }}
                            </p>
                            <p class="mb-2">
                                <strong>📍 Koordinat</strong><br>
                                {{ $laporan->latitude }},
                                {{ $laporan->longitude }}
                            </p>
                            <p class="mb-3">
                                <strong>🕒 Tanggal</strong><br>
                                {{ optional($laporan->created_at)->format('d-m-Y H:i') }}
                            </p>
                            <a
                                href="https://www.google.com/maps/dir/?api=1&destination={{ $laporan->latitude }},{{ $laporan->longitude }}"
                                target="_blank"
                                class="btn btn-success btn-sm w-100">
                                Buka Rute di Google Maps
                            </a>
                        </div>
                    `);
                @endforeach
            @endisset
        @endif
    });
</script>

@stack('scripts')

</body>
</html>