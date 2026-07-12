<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;

class LaporanController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input – maksimal 15 MB (15 * 1024 = 15360 KB)
        $request->validate([
            'foto'       => 'required|image|mimes:jpg,jpeg,png|max:15360',
            'deskripsi'  => 'required|string',
            'latitude'   => 'required',
            'longitude'  => 'required',
        ]);

        // Upload foto
        $namaFoto = time() . '.' . $request->file('foto')->extension();

        $request->file('foto')->move(
            public_path('uploads'),
            $namaFoto
        );

        // Simpan ke database
        Laporan::create([
            'foto'       => $namaFoto,
            'deskripsi'  => $request->deskripsi,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
        ]);

        // Kembali ke halaman utama
        return redirect('/')
            ->with('success', '✅ Laporan berhasil dikirim. Terima kasih atas partisipasi Anda.');
    }
}