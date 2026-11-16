<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;

class BukuController extends Controller
{
    public function index()
    {
        $kategori = Kategori::all();
        $bukus = Buku::with('kategori')->latest()->get();

        return view('buku.index', compact('kategori', 'bukus'));
    }

    public function show(int $id)
    {
        $bukuDetail = Buku::with('kategori')->find($id);

        if ($bukuDetail) {
            $bukuDetail = [
                'id' => $bukuDetail->id,
                'judul' => $bukuDetail->judul,
                'penulis' => $bukuDetail->penulis,
                'tahun' => $bukuDetail->tahun_terbit,
                'kategori' => $bukuDetail->kategori->nama ?? '-',
            ];
        }

        return view('buku.show', compact('bukuDetail'));
    }
}