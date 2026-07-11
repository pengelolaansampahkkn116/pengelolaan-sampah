<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    $laporans = Laporan::where('status', '!=', 'Selesai')->latest()->get();
    return view('pages.home', compact('laporans'));
});

Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');

// Auth
Route::get('/login', [AuthController::class, 'loginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// ADMIN ROUTES – TANPA MIDDLEWARE
Route::get('/admin', function (Request $request) {
    if (!session('admin_login')) {
        return redirect()->route('login.form');
    }
    return app(AdminController::class)->index($request);
})->name('admin.dashboard');

Route::put('/admin/status/{id}', function (Request $request, $id) {
    if (!session('admin_login')) {
        return redirect()->route('login.form');
    }
    return app(AdminController::class)->updateStatus($request, $id);
})->name('admin.status');

Route::delete('/admin/destroy/{id}', function ($id) {
    if (!session('admin_login')) {
        return redirect()->route('login.form');
    }
    return app(AdminController::class)->destroy($id);
})->name('admin.destroy');