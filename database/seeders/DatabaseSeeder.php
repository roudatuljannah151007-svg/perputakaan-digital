<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Anggota;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            KategoriSeeder::class,
            AnggotaSeeder::class,
            BukuSeeder::class,
            PeminjamanSeeder::class,
        ]);

        // 1. Akun Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@perpustakaan.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
            ]
        );
        $admin->assignRole('admin');

        // 2. Akun Petugas
        $petugas = User::firstOrCreate(
            ['email' => 'petugas@perpustakaan.test'],
            [
                'name' => 'Petugas Perpustakaan',
                'password' => Hash::make('password123'),
            ]
        );
        $petugas->assignRole('petugas');

        // 3. Akun Siswa + Otomatis terhubung ke tabel anggotas via Factory
        $siswa = User::firstOrCreate(
            ['email' => 'siswa@perpustakaan.test'],
            [
                'name' => 'Siswa Uji Coba',
                'password' => Hash::make('password123'),
            ]
        );
        $siswa->assignRole('siswa');

        // Gunakan factory agar semua kolom wajib di tabel anggotas otomatis terisi sesuai migrasi
        if (!Anggota::where('user_id', $siswa->id)->exists()) {
            Anggota::factory()->create([
                'user_id' => $siswa->id,
                'nama' => $siswa->name, // Hapus baris ini jika kolom di tabel anggotas bukan 'nama'
            ]);
        }
    }
}