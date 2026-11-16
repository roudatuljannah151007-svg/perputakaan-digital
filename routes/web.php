<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\RiwayatPeminjamanController;
use App\Http\Controllers\LaporanController;
use App\Models\Buku;

/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| DASHBOARD BERDASARKAN ROLE
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('petugas')) {
        return redirect()->route('petugas.dashboard');
    }

    if ($user->hasRole('siswa')) {
        return redirect()->route('buku.index');
    }

    abort(403, 'Akun belum memiliki role.');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin',
    'permission:access admin dashboard'
])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::middleware([
        'permission:manage users',
        'permission:manage user roles'
    ])->group(function () {
        Route::resource(
            'users',
            \App\Http\Controllers\Admin\UserController::class
        )->except(['show']);
    });

    Route::middleware('permission:manage loans')->group(function () {
        Route::get('/peminjaman', [
            PeminjamanController::class,
            'index'
        ])->name('peminjaman.index');

        Route::patch('/peminjaman/{peminjaman}/status', [
            PeminjamanController::class,
            'updateStatus'
        ])->middleware('permission:approve book loans')
          ->name('peminjaman.status');
    });

    Route::middleware('permission:manage returns')->group(function () {
        Route::get('/pengembalian', [
            PengembalianController::class,
            'index'
        ])->name('pengembalian.index');

        Route::patch('/pengembalian/{peminjaman}', [
            PengembalianController::class,
            'update'
        ])->middleware('permission:record book returns')
          ->name('pengembalian.update');
    });

    Route::get('/laporan', [
        LaporanController::class,
        'index'
    ])
    ->middleware('permission:view library reports')
    ->name('laporan.index');
});

/*
|--------------------------------------------------------------------------
| PETUGAS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:petugas',
    'permission:access petugas dashboard'
])->prefix('petugas')->name('petugas.')->group(function () {

    Route::get('/dashboard', function () {
        return view('petugas.dashboard');
    })->name('dashboard');

    Route::middleware('permission:manage loans')->group(function () {
        Route::get('/peminjaman', [
            PeminjamanController::class,
            'index'
        ])->name('peminjaman.index');

        Route::patch('/peminjaman/{peminjaman}/status', [
            PeminjamanController::class,
            'updateStatus'
        ])->middleware('permission:approve book loans')
          ->name('peminjaman.status');
    });

    Route::middleware('permission:manage returns')->group(function () {
        Route::get('/pengembalian', [
            PengembalianController::class,
            'index'
        ])->name('pengembalian.index');

        Route::patch('/pengembalian/{peminjaman}', [
            PengembalianController::class,
            'update'
        ])->middleware('permission:record book returns')
          ->name('pengembalian.update');
    });

    Route::get('/laporan', [
        LaporanController::class,
        'index'
    ])->middleware('permission:view library reports')
      ->name('laporan.index');
});

/*
|--------------------------------------------------------------------------
| ENDPOINT AJAX CRUD BUKU (GET, POST, PUT, DELETE)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('buku/ajax')->group(function () {

    Route::get('/', function () {
        $bukus = Buku::with('kategori')->orderBy('id', 'asc')->get()->map(function ($buku) {
            return [
                'id' => $buku->id,
                'kategori_id' => $buku->kategori_id,
                'kategori' => $buku->kategori->nama ?? '-',
                'judul' => $buku->judul,
                'penulis' => $buku->penulis,
                'tahun' => $buku->tahun_terbit,
            ];
        });

        return response()->json([
            'data' => $bukus,
        ]);
    });

    Route::post('/', function (Request $request) {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategoris,id',
            'judul' => 'required|string|max:150',
            'penulis' => 'required|string|max:100',
            'tahun_terbit' => 'required|integer|min:1900|max:2026',
        ]);

        $buku = Buku::create($validated);

        return response()->json([
            'message' => 'Buku berhasil ditambahkan.',
            'data' => $buku,
        ], 201);
    });

    Route::put('/{id}', function (Request $request, int $id) {
        $buku = Buku::findOrFail($id);

        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategoris,id',
            'judul' => 'required|string|max:150',
            'penulis' => 'required|string|max:100',
            'tahun_terbit' => 'required|integer|min:1900|max:2026',
        ]);

        $buku->update($validated);

        return response()->json([
            'message' => 'Buku berhasil diperbarui.',
            'data' => $buku,
        ]);
    });

    Route::delete('/{id}', function (int $id) {
        $buku = Buku::findOrFail($id);
        $buku->delete();

        return response()->json([
            'message' => 'Buku berhasil dihapus.',
        ]);
    });

});

/*
|--------------------------------------------------------------------------
| BUKU (HALAMAN WEB)
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:view books'
])->group(function () {

    Route::get('/buku', [
        BukuController::class,
        'index'
    ])->name('buku.index');

    Route::get('/buku/{id}', [
        BukuController::class,
        'show'
    ])->name('buku.show');

});

/*
|--------------------------------------------------------------------------
| PROFIL
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [
        ProfileController::class,
        'edit'
    ])->middleware('permission:view own profile')
      ->name('profile.edit');

    Route::patch('/profile', [
        ProfileController::class,
        'update'
    ])->middleware('permission:update own profile')
      ->name('profile.update');

    Route::delete('/profile', [
        ProfileController::class,
        'destroy'
    ])->name('profile.destroy');

});

/*
|--------------------------------------------------------------------------
| RIWAYAT PEMINJAMAN SEMUA ROLE
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/riwayat-peminjaman', [
        RiwayatPeminjamanController::class,
        'index'
    ])->name('peminjaman.riwayat');

});

/*
|--------------------------------------------------------------------------
| PENGAJUAN PEMINJAMAN SISWA
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:siswa',
    'permission:request book loans'
])->group(function () {

    Route::post('/peminjaman', [
        PeminjamanController::class,
        'store'
    ])->name('peminjaman.store');

});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION BREEZE
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';