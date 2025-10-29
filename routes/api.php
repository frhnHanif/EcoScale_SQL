<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ini adalah file untuk mendaftarkan endpoint API Anda.
| URL ini akan dipanggil oleh file JavaScript (fetch).
|
*/

// Endpoint untuk mengambil data yang dibutuhkan oleh halaman-halaman
Route::prefix('data')->group(function () {
    Route::get('/global-stats', [ApiDataController::class, 'getGlobalStats']);
    Route::get('/dashboard', [ApiDataController::class, 'getDashboardData']);
    Route::get('/analitik', [ApiDataController::class, 'getAnalitikData']);
    
    // Endpoint untuk halaman fakultas, butuh filter
    // (cth: /api/data/fakultas?reduction=weekly&target=today)
    Route::get('/fakultas', [ApiDataController::class, 'getFakultasData']);
    
    // Endpoint untuk halaman laporan, butuh filter
    // (cth: /api/data/laporan?start_date=...&end_date=...&fakultas=FT)
    Route::get('/laporan', [ApiDataController::class, 'getLaporanData']);
});

// Endpoint untuk perangkat IoT mengirim data
Route::post('/submit-data', [ApiDataController::class, 'submitData']);

// Endpoint fallback jika user (API) belum login
Route::fallback(function () {
    return response()->json(['message' => 'Not Found'], 404);
});
