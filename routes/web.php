<?php

use Illuminate\Support\Facades\Route;
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

// Admin routes with middleware
Route::middleware(['admin.auth'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::put('/admin/status/{id}', [AdminController::class, 'updateStatus'])->name('admin.status');
    Route::delete('/admin/destroy/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
});