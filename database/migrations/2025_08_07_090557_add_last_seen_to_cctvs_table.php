<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cctvs', function (Blueprint $table) {
            if (!Schema::hasColumn('cctvs', 'last_seen')) {
                $table->timestamp('last_seen')->nullable()->after('rtsp_path');
            }
        });
    }

    public function down()
    {
        Schema::table('cctvs', function (Blueprint $table) {
            if (Schema::hasColumn('cctvs', 'last_seen')) {
                $table->dropColumn('last_seen');
            }
        });
    }
};
