<?php

use Illuminate\Support\Facades\Route;
use App\Models\Laporan;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| HALAMAN PENGGUNA
|--------------------------------------------------------------------------
*/
Route::get('/', function () {

    $laporans = Laporan::where('status', '!=', 'Selesai')
        ->latest()
        ->get();

    return view('pages.home', compact('laporans'));
});

/*
|--------------------------------------------------------------------------
| SIMPAN LAPORAN
|--------------------------------------------------------------------------
*/
Route::post('/laporan', [LaporanController::class, 'store'])
    ->name('laporan.store');

/*
|--------------------------------------------------------------------------
| LOGIN ADMIN
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'loginForm'])
    ->name('login.form');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login');

Route::get('/logout', [AuthController::class, 'logout'])
    ->name('logout');

/*
|--------------------------------------------------------------------------
| DASHBOARD ADMIN
|--------------------------------------------------------------------------
*/
Route::get('/admin', function () {

    if (!session('admin_login')) {
        return redirect()->route('login.form');
    }

    return app(AdminController::class)->index(request());

})->name('admin.dashboard');

Route::put('/admin/status/{id}', function ($id) {

    if (!session('admin_login')) {
        return redirect()->route('login.form');
    }

    return app(AdminController::class)
        ->updateStatus(request(), $id);

})->name('admin.status');

Route::delete('/admin/destroy/{id}', function ($id) {

    if (!session('admin_login')) {
        return redirect()->route('login.form');
    }

    return app(AdminController::class)
        ->destroy($id);

})->name('admin.destroy');