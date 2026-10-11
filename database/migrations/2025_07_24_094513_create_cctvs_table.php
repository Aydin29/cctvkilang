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
        Schema::create('cctvs', function (Blueprint $table) {
            $table->id();

            // Info dasar
            $table->string('name');
            $table->ipAddress('ip_address')->nullable();
            $table->enum('status', ['online', 'offline'])->default('offline');


            // Info lokasi
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();

            // Info RTSP/Streaming
            $table->string('rtsp_username')->nullable();
            $table->string('rtsp_password')->nullable();
            $table->string('rtsp_path')->default('/stream1');
            $table->integer('rtsp_port')->default(554);
            $table->string('camera_brand')->default('generic');
            $table->text('stream_url')->nullable();
            $table->boolean('is_streaming')->default(false);
            $table->integer('stream_process_id')->nullable();

            // Timestamp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cctvs');
    }
};