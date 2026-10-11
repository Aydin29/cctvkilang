<?php

namespace App\Observers;

use App\Models\Cctv;
use App\Services\StreamConversionService;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class CctvObserver
{
    protected $streamConversionService;
    protected $dashboardService;

    public function __construct(
        StreamConversionService $streamConversionService,
        DashboardService $dashboardService
    ) {
        $this->streamConversionService = $streamConversionService;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Handle the Cctv "created" event.
     */
    public function created(Cctv $cctv): void
    {
        try {
            Log::info("CCTV '{$cctv->name}' with ID {$cctv->id} has been created.");

            // Create stream directory
            $streamDir = storage_path("app/public/streams/{$cctv->slug}");
            if (!File::exists($streamDir)) {
                File::makeDirectory($streamDir, 0755, true);
                Log::info("Created stream directory: {$streamDir}");
            }

            // Create stream script for starting the stream
            $this->createStreamScript($cctv, $streamDir);
            Log::info("Stream script created for CCTV: {$cctv->name}");

            // Broadcast dashboard update
            $this->dashboardService->broadcastUpdate();

        } catch (\Exception $e) {
            Log::error("Error in CctvObserver@created: " . $e->getMessage());
        }
    }

    /**
     * Handle the Cctv "updated" event.
     */
    public function updated(Cctv $cctv): void
    {
        try {
            if ($cctv->isDirty(['status', 'is_streaming', 'playlist_url'])) {
                Log::info("CCTV '{$cctv->name}' status updated.");
                $this->dashboardService->broadcastUpdate();
            }
        } catch (\Exception $e) {
            Log::error("Error in CctvObserver@updated: " . $e->getMessage());
        }
    }

    /**
     * Handle the Cctv "deleted" event.
     */
    public function deleted(Cctv $cctv): void
    {
        try {
            Log::info("CCTV '{$cctv->name}' deleted.");
            
            // Stop stream if running
            $this->streamConversionService->stopStream($cctv);
            
            // Clean up stream directory
            $streamDir = storage_path("app/public/streams/{$cctv->slug}");
            if (File::exists($streamDir)) {
                File::deleteDirectory($streamDir);
                Log::info("Deleted stream directory: {$streamDir}");
            }

            // Update dashboard stats
            $this->dashboardService->broadcastUpdate();
        } catch (\Exception $e) {
            Log::error("Error in CctvObserver@deleted: " . $e->getMessage());
        }
    }

    /**
     * Create FFmpeg stream script for CCTV
     */
    private function createStreamScript(Cctv $cctv, string $streamDir): void
    {
        // Build RTSP URL
        $username = $cctv->rtsp_username;
        $password = $cctv->rtsp_password;
        $ip = $cctv->ip_address;
        $port = $cctv->rtsp_port;
        $path = $cctv->rtsp_path;

        $authPart = ($username && $password) ? "{$username}:{$password}@" : "";
        $rtspUrl = "rtsp://{$authPart}{$ip}:{$port}{$path}";

        // Create the public stream directory for this CCTV using slug-based camera name
        $publicStreamDir = public_path("stream/{$cctv->slug}");
        if (!File::exists($publicStreamDir)) {
            File::makeDirectory($publicStreamDir, 0755, true);
            Log::info("Created public stream directory: {$publicStreamDir}");
        }

        $cmdFilePath = $publicStreamDir . DIRECTORY_SEPARATOR . 'start_stream.bat';

        $streamSettings = config('streaming.stream_settings', []);
        $preset = $streamSettings['preset'] ?? 'superfast';
        $targetFps = $streamSettings['target_fps'] ?? 25;
        $playlistSize = $streamSettings['playlist_size'] ?? 6;
        $audioBitrate = $streamSettings['audio_bitrate'] ?? '96k';
        $maxThreadsPct = $streamSettings['max_threads_pct'] ?? 0.75;
        $threadCount = $this->calculateThreadCount($maxThreadsPct);

        // Build a resilient batch that restarts ffmpeg when it exits (handles transient RTSP drops)
        $ffmpegCommand = "@echo off\r\n";
        $ffmpegCommand .= ":: Stream script for {$cctv->slug}\r\n";
        $ffmpegCommand .= "cd /d \"{$publicStreamDir}\"\r\n";
        $ffmpegCommand .= ":loop\r\n";
        $ffmpegCommand .= "echo [FFMPEG] Starting at %date% %time% >> ffmpeg_output.log\r\n";
        $ffmpegCommand .= "ffmpeg -rtsp_transport tcp -stimeout 10000000 -rw_timeout 5000000 -analyzeduration 5000000 -probesize 5000000 -fflags nobuffer -flags low_delay -i \"{$rtspUrl}\" -c:v libx264 -preset {$preset} -tune zerolatency -r {$targetFps} -threads {$threadCount} -c:a aac -b:a {$audioBitrate} -f hls -hls_time 1 -hls_list_size {$playlistSize} -hls_flags delete_segments playlist.m3u8 >> ffmpeg_output.log 2>&1\r\n";
        $ffmpegCommand .= "echo [FFMPEG] Exited with %errorlevel% at %date% %time% >> ffmpeg_output.log\r\n";
        $ffmpegCommand .= "goto loop\r\n";

        File::put($cmdFilePath, $ffmpegCommand);
        Log::info("Created stream script at: {$cmdFilePath} that outputs to: {$publicStreamDir}");

        // Eksekusi otomatis script bat di background dihilangkan. 
        // Eksekusi akan dilakukan secara asynchronous dari frontend melalui ajax request.
        /*
        if (PHP_OS_FAMILY === 'Windows') {
            $command = 'start /B "" "' . $cmdFilePath . '"';
            pclose(popen($command, 'r'));
            Log::info("Executed stream script in background: {$command}");
            
            // Update status CCTV ke streaming agar langsung terdeteksi aktif di frontend
            $cctv->updateQuietly([
                'is_streaming' => true,
                'playlist_url' => url("stream/{$cctv->slug}/playlist.m3u8")
            ]);
        }
        */
    }

    /**
     * Calculate thread count for the FFmpeg batch script.
     *
     * @param float $maxThreadsPct
     * @return int
     */
    private function calculateThreadCount(float $maxThreadsPct): int
    {
        $coreCount = $this->getCpuCoreCount();
        return max(1, (int) floor($coreCount * $maxThreadsPct));
    }

    /**
     * Determine available CPU cores on the host.
     *
     * @return int
     */
    private function getCpuCoreCount(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            try {
                $output = [];
                exec('wmic cpu get NumberOfLogicalProcessors /value', $output);
                foreach ($output as $line) {
                    if (str_contains($line, 'NumberOfLogicalProcessors')) {
                        [$key, $value] = explode('=', $line);
                        return max(1, (int) trim($value));
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not determine CPU core count in observer: ' . $e->getMessage());
            }
        }

        if (is_file('/proc/cpuinfo')) {
            $cpuInfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuInfo, $matches);
            return max(1, count($matches[0]));
        }

        return 1;
    }
}
