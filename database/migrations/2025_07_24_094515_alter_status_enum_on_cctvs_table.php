<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('cctvs', function (Blueprint $table) {
                $table->string('status')->default('offline')->change();
            });
        } else {
            // For MySQL or other DBs supporting ENUM and MODIFY COLUMN
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE cctvs MODIFY COLUMN status ENUM('online', 'offline') DEFAULT 'offline'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('cctvs', function (Blueprint $table) {
                $table->string('status')->default('offline')->change();
            });
        } else {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE cctvs MODIFY COLUMN status ENUM('online', 'offline', 'maintenance') DEFAULT 'offline'");
        }
    }
};
