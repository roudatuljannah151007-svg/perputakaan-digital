<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Buku;
use App\Models\Kategori;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil kategori pertama yang tersedia di database
        $kategori = Kategori::first();
        $kategoriId = $kategori ? $kategori->id : 1;

        $daftarBuku = [
            [
                'kategori_id' => $kategoriId,
                'judul' => 'Laskar Pelangi',
                'penulis' => 'Andrea Hirata',
                'tahun_terbit' => 2005,
            ],
            [
                'kategori_id' => $kategoriId,
                'judul' => 'Bumi',
                'penulis' => 'Tere Liye',
                'tahun_terbit' => 2014,
            ],
            [
                'kategori_id' => $kategoriId,
                'judul' => 'Negeri 5 Menara',
                'penulis' => 'Ahmad Fuadi',
                'tahun_terbit' => 2009,
            ],
            [
                'kategori_id' => $kategoriId,
                'judul' => 'Filosofi Teras',
                'penulis' => 'Henry Manampiring',
                'tahun_terbit' => 2018,
            ],
            [
                'kategori_id' => $kategoriId,
                'judul' => 'Atomic Habits',
                'penulis' => 'James Clear',
                'tahun_terbit' => 2018,
            ],
        ];

        foreach ($daftarBuku as $buku) {
            Buku::firstOrCreate(
                ['judul' => $buku['judul']],
                $buku
            );
        }
    }
}