<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Halaman utama diarahkan ke dashboard sesuai role (atau ke login jika belum masuk)
Route::get('/', fn () => redirect()->route('dashboard'));

// Hanya untuk pengguna yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1') // maksimal 5 percobaan per menit
        ->name('login.store');
});

// Hanya untuk pengguna yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Pengarah dashboard berdasarkan role
    Route::get('/dashboard', function (Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return $user->isOwner()
            ? redirect()->route('owner.dashboard')
            : redirect()->route('cashier.dashboard');
    })->name('dashboard');

    // Area Owner
    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function () {
        Route::view('/dashboard', 'owner.dashboard')->name('dashboard');
    });

    // Area Kasir
    Route::middleware('role:cashier')->prefix('cashier')->name('cashier.')->group(function () {
        Route::view('/dashboard', 'cashier.dashboard')->name('dashboard');
    });
});