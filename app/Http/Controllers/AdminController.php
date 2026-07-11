<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use Illuminate\Support\Facades\File;

class AdminController extends Controller
{
    /**
     * Dashboard Admin – Debugging bertahap
     */
    public function index(Request $request)
    {
        // 🔍 Step 1
        dd('Step 1: Query dimulai');

        $query = Laporan::query();

        // 🔍 Step 2
        dd('Step 2: Query dibuat');

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 🔍 Step 3
        dd('Step 3: Filter status selesai');

        // Pencarian deskripsi (case-insensitive untuk PostgreSQL)
        if ($request->filled('search')) {
            $query->where('deskripsi', 'ILIKE', '%' . $request->search . '%');
        }

        // 🔍 Step 4
        dd('Step 4: Pencarian selesai');

        // Filter tanggal awal
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('created_at', '>=', $request->tanggal_awal);
        }

        // 🔍 Step 5
        dd('Step 5: Filter tanggal awal selesai');

        // Filter tanggal akhir
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->tanggal_akhir);
        }

        // 🔍 Step 6
        dd('Step 6: Filter tanggal akhir selesai');

        // DATA UNTUK TABEL (dengan pagination)
        $laporans = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // 🔍 Step 7
        dd('Step 7: Pagination selesai, $laporans ada');

        // DATA UNTUK PETA (SEMUA data, tanpa pagination)
        $mapQuery = clone $query;
        $mapLaporans = $mapQuery->orderBy('created_at', 'desc')->get();

        // 🔍 Step 8
        dd('Step 8: $mapLaporans selesai');

        // STATISTIK (tetap total keseluruhan)
        $total    = Laporan::count();
        $menunggu = Laporan::where('status', 'Menunggu')->count();
        $diproses = Laporan::where('status', 'Diproses')->count();
        $selesai  = Laporan::where('status', 'Selesai')->count();

        // 🔍 Step 9
        dd('Step 9: Statistik selesai');

        // GRAFIK PER BULAN (PostgreSQL)
        $bulanan = Laporan::selectRaw("EXTRACT(MONTH FROM created_at) as bulan, COUNT(*) as total")
                          ->groupByRaw("EXTRACT(MONTH FROM created_at)")
                          ->pluck('total', 'bulan')
                          ->toArray();

        // 🔍 Step 10
        dd('Step 10: Grafik bulan selesai');

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[$i] = $bulanan[$i] ?? 0;
        }

        // 🔍 Step 11
        dd('Step 11: ChartData selesai');

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

    /**
     * Update status laporan
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Menunggu,Diproses,Selesai'
        ]);

        $laporan = Laporan::findOrFail($id);

        $laporan->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Status laporan berhasil diubah.');
    }

    /**
     * Hapus laporan (menggunakan File facade)
     */
    public function destroy($id)
    {
        $laporan = Laporan::findOrFail($id);

        $filePath = public_path('uploads/' . $laporan->foto);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $laporan->delete();

        return redirect()->back()->with('success', 'Laporan berhasil dihapus.');
    }
}