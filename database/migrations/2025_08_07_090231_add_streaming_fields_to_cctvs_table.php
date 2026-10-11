<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cctvs', function (Blueprint $table) {
            if (!Schema::hasColumn('cctvs', 'rtsp_url')) {
                $table->string('rtsp_url')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('cctvs', 'rtsp_username')) {
                $table->string('rtsp_username')->nullable()->after('rtsp_url');
            }
            if (!Schema::hasColumn('cctvs', 'rtsp_password')) {
                $table->string('rtsp_password')->nullable()->after('rtsp_username');
            }
            if (!Schema::hasColumn('cctvs', 'rtsp_path')) {
                $table->string('rtsp_path')->default('/stream1')->after('rtsp_password');
            }
            if (!Schema::hasColumn('cctvs', 'rtsp_port')) {
                $table->integer('rtsp_port')->default(554)->after('rtsp_path');
            }
            if (!Schema::hasColumn('cctvs', 'camera_brand')) {
                $table->string('camera_brand')->default('generic')->after('rtsp_port');
            }
            if (!Schema::hasColumn('cctvs', 'stream_url')) {
                $table->text('stream_url')->nullable()->after('camera_brand');
            }
            if (!Schema::hasColumn('cctvs', 'is_streaming')) {
                $table->boolean('is_streaming')->default(false)->after('stream_url');
            }
            if (!Schema::hasColumn('cctvs', 'stream_process_id')) {
                $table->integer('stream_process_id')->nullable()->after('is_streaming');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cctvs', function (Blueprint $table) {
            $table->dropColumn([
                'rtsp_url',
                'rtsp_username',
                'rtsp_password',
                'rtsp_path',
                'rtsp_port',
                'camera_brand',
                'stream_url',
                'is_streaming',
                'stream_process_id',
            ]);
        });
    }
};
