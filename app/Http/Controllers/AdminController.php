<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use Illuminate\Support\Facades\File;

class AdminController extends Controller
{
    /**
     * Dashboard Admin dengan filter, pencarian, pagination, dan grafik
     */
    public function index(Request $request)
    {
        $query = Laporan::query();

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pencarian deskripsi (case-insensitive untuk SQLite/PostgreSQL)
        if ($request->filled('search')) {
            $query->where('deskripsi', 'LIKE', '%' . $request->search . '%');
        }

        // Filter tanggal awal
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('created_at', '>=', $request->tanggal_awal);
        }

        // Filter tanggal akhir
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->tanggal_akhir);
        }

        // DATA UNTUK TABEL (dengan pagination)
        $laporans = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // DATA UNTUK PETA (SEMUA data, tanpa pagination)
        $mapQuery = clone $query;
        $mapLaporans = $mapQuery->orderBy('created_at', 'desc')->get();

        // STATISTIK (tetap total keseluruhan)
        $total    = Laporan::count();
        $menunggu = Laporan::where('status', 'Menunggu')->count();
        $diproses = Laporan::where('status', 'Diproses')->count();
        $selesai  = Laporan::where('status', 'Selesai')->count();

        // GRAFIK PER BULAN (SQLite compatible)
        $bulanan = Laporan::selectRaw("strftime('%m', created_at) as bulan, COUNT(*) as total")
                          ->groupBy('bulan')
                          ->pluck('total', 'bulan')
                          ->toArray();

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $bulanKey = str_pad($i, 2, '0', STR_PAD_LEFT); // '01'..'12'
            $chartData[$i] = $bulanan[$bulanKey] ?? 0;
        }

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
        $laporan->update(['status' => $request->status]);

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