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
        Schema::create('sampah', function (Blueprint $table) {
            // Tetap gunakan ID sebagai primary key (best practice)
            $table->id();
            
            // Kolom yang Anda minta:
            $table->float('berat_kg'); // 'berat'
            $table->string('jenis', 50)->index(); // 'jenis'

            // Relasi ke tabel fakultas ('kode_fakultas')
            $table->string('fakultas_kode', 10)->nullable()->index();
            $table->foreign('fakultas_kode')
                  ->references('kode')
                  ->on('fakultas')
                  ->onDelete('set null');

            // Kolom 'ditimbang_pada' dan 'device_id' telah dihapus.
            
            // Ini akan membuat 'created_at' dan 'updated_at'
            // 'created_at' akan berfungsi sebagai 'timestamp' yang Anda minta
            $table->timestamps();

            // Kita tambahkan index ke 'created_at' untuk kueri tanggal yang cepat
            // (menggantikan index pada 'ditimbang_pada' yang lama)
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sampah');
    }
};

