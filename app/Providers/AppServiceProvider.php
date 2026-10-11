<?php

namespace App\Providers;

use App\Models\Cctv;
use App\Observers\CctvObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mendaftarkan Observer untuk model CCTV
        Cctv::observe(CctvObserver::class);

        /**
         * Jika perintah artisan yang dijalankan adalah "serve",
         * jalankan juga skrip ffmpeg dan layanan tambahan secara otomatis.
         * Perintah ini hanya dieksekusi ketika berada di CLI.
         */
        if ($this->app->runningInConsole() && isset($_SERVER['argv'][1]) && strpos($_SERVER['argv'][1], 'serve') !== false) {
            $root = base_path();
            $scriptPath = $root . '\\run_ffmpeg_playlist_enhanced.ps1';

            // Guard supaya tidak dobel-start saat proses serve di-restart / reload.
            // Marker file disimpan di root project.
            $markerPath = $root . '\\.ffmpeg_autostart_marker';
            $markerExists = file_exists($markerPath);

            // marker dianggap expired setelah 10 menit agar bisa restart manual jika gagal.
            $markerFresh = false;
            if ($markerExists) {
                $mtime = @filemtime($markerPath);
                $markerFresh = $mtime && (time() - $mtime) < 600;
            }

            if (!$markerFresh && file_exists($scriptPath)) {
                @file_put_contents($markerPath, (string)time());

                // jalankan skrip sebagai proses terpisah di background
                if (class_exists(\Symfony\Component\Process\Process::class)) {
                    $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -File "' . $scriptPath . '"';
                    $ps = \Symfony\Component\Process\Process::fromShellCommandline($cmd);
                    $ps->disableOutput();
                    $ps->start();
                } else {
                    // fallback apabila komponen Process tidak tersedia
                    @exec('powershell -NoProfile -ExecutionPolicy Bypass -File "' . $scriptPath . '" > nul 2>&1 &');
                }
            }
        }

        /**
         * Memperbaiki tampilan yang berantakan di Ngrok/Production.
         * Memaksa semua URL asset menggunakan HTTPS agar tidak terkena Mixed Content error.
         * Hanya force HTTPS jika APP_URL menggunakan https://
         */
        $appUrl = config('app.url');
        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }
}