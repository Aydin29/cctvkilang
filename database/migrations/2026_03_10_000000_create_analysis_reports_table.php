<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates table for storing periodic analysis reports captured from the
     * real‑time monitoring page. Each record belongs to a CCTV and contains
     * the captured metric values plus the raw dataset that was displayed on
     * the chart. This allows later retrieval or exporting for audit purposes.
     *
     * Fields:
     *  - cctv_id (unsignedBigInteger) foreign key
     *  - protocol (string) one of "rtsp", "hls", "non_streaming"
     *  - metrics (json) current delay/jitter/packet_loss/throughput values
     *  - chart_data (json) the labels+datasets that were rendered
     *  - created_at/updated_at timestamps
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analysis_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cctv_id');
            $table->string('protocol', 32);
            $table->json('metrics');
            $table->json('chart_data');
            $table->timestamps();

            $table->foreign('cctv_id')->references('id')->on('cctvs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('analysis_reports');
    }
};
