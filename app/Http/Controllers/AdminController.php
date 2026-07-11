public function index(Request $request)
{
    $query = Laporan::query();

    // Filter status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Pencarian deskripsi (case-insensitive untuk PostgreSQL)
    if ($request->filled('search')) {
        $query->where('deskripsi', 'ILIKE', '%' . $request->search . '%');
    }

    // Filter tanggal awal
    if ($request->filled('tanggal_awal')) {
        $query->whereDate('created_at', '>=', $request->tanggal_awal);
    }

    // Filter tanggal akhir
    if ($request->filled('tanggal_akhir')) {
        $query->whereDate('created_at', '<=', $request->tanggal_akhir);
    }

    // DATA UNTUK TABEL (pagination)
    $laporans = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

    // DATA UNTUK PETA (semua data)
    $mapQuery = clone $query;
    $mapLaporans = $mapQuery->orderBy('created_at', 'desc')->get();

    // STATISTIK
    $total    = Laporan::count();
    $menunggu = Laporan::where('status', 'Menunggu')->count();
    $diproses = Laporan::where('status', 'Diproses')->count();
    $selesai  = Laporan::where('status', 'Selesai')->count();

    // GRAFIK PER BULAN (PostgreSQL)
    $bulanan = Laporan::selectRaw("EXTRACT(MONTH FROM created_at) as bulan, COUNT(*) as total")
                      ->groupByRaw("EXTRACT(MONTH FROM created_at)")
                      ->pluck('total', 'bulan')
                      ->toArray();

    $chartData = [];
    for ($i = 1; $i <= 12; $i++) {
        $chartData[$i] = $bulanan[$i] ?? 0;
    }

    // KEMBALIKAN VIEW
    return view('admin.dashboard', compact(
        'laporans',
        'mapLaporans',
        'total',
        'menunggu',
        'diproses',
        'selesai',
        'chartData'
    ));
}