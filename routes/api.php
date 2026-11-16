<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Buku;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// GET: Ambil semua daftar buku
Route::get('/buku', function () {
    $bukus = Buku::with('kategori')->orderBy('id', 'desc')->get()->map(function ($buku) {
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

// POST: Tambah buku baru
Route::post('/buku', function (Request $request) {
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

// PUT: Ubah buku
Route::put('/buku/{id}', function (Request $request, int $id) {
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

// DELETE: Hapus buku
Route::delete('/buku/{id}', function (int $id) {
    $buku = Buku::findOrFail($id);
    $buku->delete();

    return response()->json([
        'message' => 'Buku berhasil dihapus.',
    ]);
});