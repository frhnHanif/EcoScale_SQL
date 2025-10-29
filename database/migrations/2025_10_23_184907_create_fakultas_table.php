<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fakultas', function (Blueprint $table) {
            $table->id();
            // Gunakan 'kode' sebagai Primary Key unik (FT, FK, FEB)
            $table->string('kode', 10)->unique();
            $table->string('nama_lengkap');
            
            // Target yang ada di fakultas.js
            // Kita buat nullable jika ada fakultas tanpa target
            $table->float('target_harian_kg')->nullable();
            
            $table->timestamps();
        });

        // Isi data awal (bisa juga pakai Seeder)
        $fakultas = [
            ['kode' => 'FT', 'nama_lengkap' => 'Fakultas Teknik', 'target_harian_kg' => 50],
            ['kode' => 'FK', 'nama_lengkap' => 'Fakultas Kedokteran', 'target_harian_kg' => 45],
            ['kode' => 'FEB', 'nama_lengkap' => 'Fakultas Ekonomi dan Bisnis', 'target_harian_kg' => 55],
            ['kode' => 'FH', 'nama_lengkap' => 'Fakultas Hukum', 'target_harian_kg' => 35],
            ['kode' => 'FSM', 'nama_lengkap' => 'Fakultas Sains dan Matematika', 'target_harian_kg' => 40],
            ['kode' => 'FPP', 'nama_lengkap' => 'Fakultas Peternakan dan Pertanian', 'target_harian_kg' => 60],
            // Tambahkan fakultas lain jika ada
        ];

        foreach ($fakultas as $f) {
            DB::table('fakultas')->insert(array_merge($f, ['created_at' => now(), 'updated_at' => now()]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fakultas');
    }
};
