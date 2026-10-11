<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Jalur FFmpeg dan FFprobe
    |--------------------------------------------------------------------------
    |
    | Tentukan jalur absolut ke biner FFmpeg dan FFprobe.
    | Anda perlu memastikan program ini terpasang di server Anda.
    | Contoh di bawah adalah untuk Linux, sesuaikan untuk Windows jika perlu.
    |
    */
    'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),

    /*
    |--------------------------------------------------------------------------
    | Pengaturan RTSP
    |--------------------------------------------------------------------------
    |
    | Pengaturan untuk koneksi RTSP. Ini termasuk batas waktu, protokol
    | transport, dan opsi error recovery.
    |
    */
    'rtsp_settings' => [
        // Batas waktu dalam detik untuk setiap operasi FFmpeg (contoh: 30 detik).
        'timeout' => 30,

        // Batas waktu dalam detik untuk operasi FFprobe.
        'ffprobe_timeout' => 30,

        // Batas waktu maksimal untuk eksekusi proses FFmpeg secara keseluruhan.
        'max_execution_time' => 300,

        // Transport protocol untuk RTSP (tcp = lebih reliable, udp = lebih cepat).
        'transport' => 'tcp',

        // Socket timeout dalam mikrodetik untuk RTSP (5000000 = 5 detik).
        'socket_timeout' => 5000000,

        // Durasi analisis stream dalam mikrodetik untuk deteksi codec lebih baik.
        'analyze_duration' => 5000000,

        // Ukuran probe dalam byte untuk analisis stream.
        'probe_size' => 5000000,

        // Aktifkan asynchronous timestamp correction untuk mencegah "backward in time" errors.
        'async_timestamps' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pengaturan Streaming (HLS)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini mengontrol bagaimana FFmpeg mengonversi stream menjadi HLS.
    |
    */
    'stream_settings' => [
        // Codec video yang digunakan untuk output saat mengonversi stream.
        // Samakan dengan worker batch: libx264.
        'video_codec' => 'libx264',

        // Sesuaikan dengan command worker batch: ultrafast + zerolatency.
        'preset' => 'ultrafast',

        // Target frame rate output.
        'target_fps' => 25,

        // Durasi setiap segmen HLS dalam detik.
        'segment_duration' => 1,

        // Jumlah segmen yang disimpan dalam playlist m3u8.
        'playlist_size' => 3,


        // Codec audio yang digunakan untuk output.
        'audio_codec' => 'aac',

        // Bitrate audio default.
        'audio_bitrate' => '96k',

        // Bobot penggunaan thread CPU terhadap jumlah inti yang tersedia.
        // Nilai 0.75 berarti gunakan maksimal 75% dari jumlah thread logis.
        'max_threads_pct' => 0.75,

        // Batas proporsional beban CPU yang ditargetkan oleh proses FFmpeg.
        'target_cpu_load' => 0.75,
    ],
];
