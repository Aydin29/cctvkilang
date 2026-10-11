<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('cctv_cameras', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama kamera, misal: "CCTV-001 - Area Gerbang Utama"
            $table->string('rtsp_url'); // URL RTSP asli
            $table->string('hls_url')->nullable(); // URL HLS (m3u8) yang dihasilkan
            $table->enum('status', ['online', 'offline', 'processing'])->default('offline'); // Status kamera
            $table->string('server_process_id')->nullable(); // Opsional: ID proses FFmpeg di server
            $table->timestamps();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('cctv_cameras');
    }
};

