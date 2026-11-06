<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TransactionController;
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

    // Halaman yang dipakai Owner dan Kasir.
    // Saat ini masih halaman sementara, akan diganti controller pada langkah berikutnya.
    Route::middleware('role:owner,cashier')->group(function () {
        // Transaksi: form baru, simpan, dan detail. Daftar transaksi masih halaman sementara.
        Route::resource('transactions', TransactionController::class)->only(['create', 'store', 'show']);
        Route::view('/transactions', 'placeholder', ['title' => 'Data Transaksi'])->name('transactions.index');
        // Endpoint JSON untuk form transaksi (harus sebelum resource agar tidak tertangkap customers/{customer})
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::post('/customers/quick', [CustomerController::class, 'quickStore'])->name('customers.quick');
        Route::resource('customers', CustomerController::class)->except(['destroy']);
    });

    // Area Owner
    Route::middleware('role:owner')->group(function () {
        Route::get('/owner/dashboard', [DashboardController::class, 'owner'])->name('owner.dashboard');

        // Pengaturan tarif layanan (tambah, ubah, aktif/nonaktif)
        Route::resource('services', ServiceController::class)->except(['show', 'destroy']);
        Route::patch('/services/{service}/toggle', [ServiceController::class, 'toggle'])->name('services.toggle');

        // Data karyawan (akun Kasir)
        Route::resource('employees', EmployeeController::class)->except(['show', 'destroy']);
        Route::patch('/employees/{employee}/toggle', [EmployeeController::class, 'toggle'])->name('employees.toggle');

        Route::view('/reports/daily', 'placeholder', ['title' => 'Rekap Harian'])->name('reports.daily');
        Route::view('/reports/monthly', 'placeholder', ['title' => 'Rekap Bulanan'])->name('reports.monthly');
        Route::view('/reports', 'placeholder', ['title' => 'Laporan'])->name('reports.index');
    });

    // Area Kasir
    Route::middleware('role:cashier')->group(function () {
        Route::get('/cashier/dashboard', [DashboardController::class, 'cashier'])->name('cashier.dashboard');
    });
});