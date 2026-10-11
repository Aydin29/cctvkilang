<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cctvs', function (Blueprint $table) {
            if (!Schema::hasColumn('cctvs', 'latitude')) {
                $table->decimal('latitude', 10, 6)->nullable();
            }
            if (!Schema::hasColumn('cctvs', 'longitude')) {
                $table->decimal('longitude', 10, 6)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('cctvs', function (Blueprint $table) {
            if (Schema::hasColumn('cctvs', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('cctvs', 'longitude')) {
                $table->dropColumn('longitude');
            }
        });
    }
};
