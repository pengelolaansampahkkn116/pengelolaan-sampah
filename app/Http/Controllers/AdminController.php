<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.dashboard', [
            'total' => 0,
            'menunggu' => 0,
            'diproses' => 0,
            'selesai' => 0,
            'chartData' => [],
            'laporans' => [],
            'mapLaporans' => []
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        return back()->with('success', 'Status berhasil diubah (testing)');
    }

    public function destroy($id)
    {
        return back()->with('success', 'Laporan berhasil dihapus (testing)');
    }
}