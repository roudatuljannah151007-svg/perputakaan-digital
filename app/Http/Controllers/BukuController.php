<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;

class BukuController extends Controller
{
    public function index()
    {
        $kategori = Kategori::all();
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