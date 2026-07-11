@extends('layouts.app')

@section('title', 'Sistem Pelaporan Titik Sampah')

@section('content')

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg bg-white shadow-sm">
    <div class="container py-3">
        <a class="navbar-brand fw-bold fs-3 text-success">
            🌿 KKN 116 Pengelolaan Sampah
        </a>
        <a href="{{ route('login.form') }}" class="btn btn-success rounded-pill px-4">
            <i class="bi bi-person-lock"></i> Login Admin
        </a>
    </div>
</nav>

<!-- ================= HERO ================= -->
<section class="container py-5 text-center">
    <span class="badge bg-success-subtle text-success px-3 py-2">
        SISTEM PELAPORAN TITIK SAMPAH
    </span>
    <h1 class="display-3 fw-bold mt-4">
        Lingkungan Bersih
        <span class="text-success">Dimulai Dari</span>
        Laporan Anda
    </h1>
    <p class="lead text-secondary mt-3">
        Ayo laporkan titik sampah menggunakan GPS agar bisa ditangani
    </p>
    <a href="#formLaporan" class="btn btn-success btn-lg rounded-pill px-5 mt-3">
        Laporkan Sekarang
    </a>
</section>

<!-- ================= MAP ================= -->
<section class="container mb-5">
    <div class="card border-0 shadow-lg rounded-5 overflow-hidden">
        <div id="map" style="height: 400px; width: 100%;"></div>
    </div>
</section>

<!-- ================= FORM ================= -->
<section id="formLaporan" class="container mb-5">
    <div class="card border-0 shadow-lg rounded-5 p-5">
        <h2 class="fw-bold mb-4">Laporkan Sampah</h2>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="formLaporanUser" action="{{ route('laporan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- FOTO -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Upload Foto Sampah</label>
                <input type="file" id="foto" name="foto" class="form-control" accept="image/*" capture="environment" required>
                <img id="preview" class="img-fluid rounded mt-3 shadow-sm" style="display:none; max-width:320px;">
                <small class="text-muted">Maksimal 2 MB</small>
            </div>

            <!-- DESKRIPSI -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="deskripsi" rows="5" class="form-control" placeholder="Contoh: Terdapat tumpukan sampah plastik di pinggir jalan." required>{{ old('deskripsi') }}</textarea>
            </div>

            <!-- HIDDEN GPS -->
            <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
            <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">

            <!-- GPS -->
            <div class="mb-4">
                <button type="button" id="btnGPS" class="btn btn-success">📍 Ambil Lokasi GPS</button>
                <small id="gpsStatus" class="d-block mt-2 text-secondary">Lokasi belum diambil.</small>
            </div>

            <!-- SUBMIT -->
            <button type="submit" id="btnSubmit" class="btn btn-dark btn-lg rounded-pill px-5">Kirim Laporan</button>
        </form>
    </div>
</section>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // ==========================
        // 1. Scroll ke form jika sukses
        // ==========================
        @if(session('success'))
            document.getElementById("formLaporan").scrollIntoView({ behavior: "smooth" });
        @endif

        // ==========================
        // 2. Arahkan peta ke Labukkang (map sudah ada dari layout)
        // ==========================
        if (typeof map !== 'undefined') {
            map.setView([-4.0134, 119.6256], 13);
        }

        // ==========================
        // 3. Ikon GPS (biru) - konsisten dengan layout
        // ==========================
        const gpsIcon = L.icon({
            iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png",
            shadowUrl: "https://unpkg.com/leaflet/dist/images/marker-shadow.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41]
        });

        let gpsMarker = null; // marker untuk lokasi pengguna

        // ==========================
        // 4. Preview Foto + Validasi Ukuran
        // ==========================
        const foto = document.getElementById("foto");
        const preview = document.getElementById("preview");

        foto.addEventListener("change", function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validasi ukuran (maks 2 MB = 2.097.152 bytes)
                if (file.size > 2097152) {
                    alert("Ukuran foto maksimal 2 MB. Silakan pilih file yang lebih kecil.");
                    foto.value = "";
                    preview.style.display = "none";
                    preview.src = "";
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    preview.src = event.target.result;
                    preview.style.display = "block";
                }
                reader.readAsDataURL(file);
            }
        });

        // ==========================
        // 5. Ambil / Perbarui Lokasi GPS
        // ==========================
        const btnGPS = document.getElementById("btnGPS");
        const gpsStatus = document.getElementById("gpsStatus");

        btnGPS.addEventListener("click", function() {
            if (!navigator.geolocation) {
                alert("Browser tidak mendukung GPS.");
                return;
            }

            gpsStatus.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengambil lokasi...';
            gpsStatus.classList.remove("text-secondary", "text-danger", "text-success");

            navigator.geolocation.getCurrentPosition(
                // ----- SUCCESS -----
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    document.getElementById("latitude").value = lat;
                    document.getElementById("longitude").value = lng;

                    map.setView([lat, lng], 18);

                    if (gpsMarker !== null) {
                        map.removeLayer(gpsMarker);
                    }

                    gpsMarker = L.marker([lat, lng], { icon: gpsIcon })
                        .addTo(map)
                        .bindPopup("📍 Lokasi Anda")
                        .openPopup();

                    gpsStatus.innerHTML = "✅ Lokasi berhasil diambil.";
                    gpsStatus.classList.add("text-success");

                    // Ubah teks tombol agar pengguna bisa memperbarui lokasi
                    btnGPS.innerHTML = "📍 Perbarui Lokasi";
                },
                // ----- ERROR -----
                function(error) {
                    gpsStatus.innerHTML = "❌ Gagal mengambil lokasi. Pastikan GPS aktif dan izinkan akses lokasi.";
                    gpsStatus.classList.add("text-danger");
                    console.log(error);
                },
                // ----- OPTIONS -----
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });

        // ==========================
        // 6. Submit Form
        // ==========================
        const form = document.getElementById("formLaporanUser");
        const btnSubmit = document.getElementById("btnSubmit");

        form.addEventListener("submit", function(e) {
            const lat = document.getElementById("latitude").value;
            const lng = document.getElementById("longitude").value;

            if (lat === "" || lng === "") {
                e.preventDefault();
                alert("Silakan ambil lokasi GPS terlebih dahulu.");
                return;
            }

            // Disable tombol kirim agar tidak dobel
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim Laporan...';
        });

        // ==========================
        // 7. Reset tombol submit jika validasi gagal
        // ==========================
        @if ($errors->any())
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = "Kirim Laporan";
        @endif

    });
</script>
@endpush