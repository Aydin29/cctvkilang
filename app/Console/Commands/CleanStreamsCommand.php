<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Cctv;
use Illuminate\Support\Facades\Log;

class CleanStreamsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
protected $signature = 'stream:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan folder stream HLS yang sudah tidak digunakan dan file .ts yang usang';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $streamPath = public_path('stream');
        if (!File::exists($streamPath)) {
            $this->info('Folder stream tidak ditemukan. Melewati proses pembersihan.');
            return;
        }

        $directories = File::directories($streamPath);
        $allSlugs = Cctv::pluck('slug')->toArray();
        $now = now()->timestamp;

        foreach ($directories as $dir) {
            $folderName = basename($dir);

// 1. Jika folder tidak terdaftar di database CCTV (sudah dihapus), sebelumnya folder dihapus seutuhnya.
            // Sekarang penghapusan folder dimatikan agar file/script seperti start_stream.bat tidak ikut hilang.
            // Jika ingin tetap delete, implement via opsi signature.
            if (!in_array($folderName, $allSlugs)) {
                $this->info("Skip folder (CCTV tidak ditemukan di DB): {$folderName}");
                continue;
            }

// 2. Jika folder masih dipakai, hapus file .ts yang usianya lebih dari 5 menit (300 detik)
            // DIMATIKAN agar playlist*.ts tidak ikut terhapus.
            // $files = File::files($dir);
            // foreach ($files as $file) {
            //     if ($file->getExtension() === 'ts' && ($now - $file->getMTime()) > 300) {
            //         File::delete($file->getPathname());
            //     }
            // }
        }

        // 3. Cek kapasitas harddisk
        $freeSpace = disk_free_space($streamPath);
        $totalSpace = disk_total_space($streamPath);
        
        if ($freeSpace !== false && $totalSpace !== false) {
            $freePercentage = ($freeSpace / $totalSpace) * 100;
            if ($freePercentage < 10) { // Jika sisa penyimpanan kurang dari 10%
                $freeGb = round($freeSpace / 1024 / 1024 / 1024, 2);
                $warningMsg = "PERINGATAN: Penyimpanan harddisk hampir penuh! Sisa ruang: {$freeGb} GB (" . round($freePercentage, 1) . "%).";
                Log::warning($warningMsg);
                $this->warn('[' . now()->toDateTimeString() . '] ' . $warningMsg);
            }
        }

        $this->info('[' . now()->toDateTimeString() . '] Pembersihan stream selesai dilakukan.');
    }
}