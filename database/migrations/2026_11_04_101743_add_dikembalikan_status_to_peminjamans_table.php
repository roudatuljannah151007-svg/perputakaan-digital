<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah kolom status menjadi string biasa terlebih dahulu
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->string('status')->default('menunggu')->change();
        });

        // 2. Hapus constraint check yang lama jika ada, lalu buat yang baru dengan nilai 'dikembalikan'
        DB::statement("ALTER TABLE peminjamans DROP CONSTRAINT IF EXISTS peminjamans_status_check;");
        DB::statement("ALTER TABLE peminjamans ADD CONSTRAINT peminjamans_status_check CHECK (status IN ('menunggu', 'disetujui', 'ditolak', 'dikembalikan'));");
    }

    public function down(): void
    {
        // 1. Ubah kembali ke string biasa
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->string('status')->default('menunggu')->change();
        });

        // 2. Kembalikan constraint check ke kondisi awal (tanpa 'dikembalikan')
        DB::statement("ALTER TABLE peminjamans DROP CONSTRAINT IF EXISTS peminjamans_status_check;");
        DB::statement("ALTER TABLE peminjamans ADD CONSTRAINT peminjamans_status_check CHECK (status IN ('menunggu', 'disetujui', 'ditolak'));");
    }
};