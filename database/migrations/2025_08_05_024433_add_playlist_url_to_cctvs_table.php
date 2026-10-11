<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cctvs', function (Blueprint $table) {
            $table->string('playlist_url')->nullable()->after('rtsp_password'); // atau after kolom lain
        });
    }

    public function down(): void
    {
        Schema::table('cctvs', function (Blueprint $table) {
            $table->dropColumn('playlist_url');
        });
    }
};

