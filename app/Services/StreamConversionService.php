<?php

namespace App\Services;

use App\Models\Cctv;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class StreamConversionService
{
    /**
     * Convert RTSP stream to HLS format using FFmpeg.
     *
     * - Creates: public/stream/<slug>/start_stream.bat
     * - Starts FFmpeg using that batch file (FFmpeg runs detached via `start /B`).
     */
    public function convertRtspToM3u8(Cctv $cctv, ?int $segmentDuration = null): array
    {
        try {
            $streamFolder = $cctv->slug;
            $outputDir = public_path("stream/{$streamFolder}");

            // Remove any old output directory so the update can overwrite existing stream files
            if (File::exists($outputDir)) {
                File::deleteDirectory($outputDir);
            }



            File::makeDirectory($outputDir, 0755, true);

            $cmdFilePath = $outputDir . DIRECTORY_SEPARATOR . 'start_stream.bat';

            // Build RTSP URL with credentials if present
            $rtspUrl = $cctv->stream_url;

            // HLS settings:
            // - hls_time 1 second per segment
            // - hls_list_size 3600 => playlist window ~ 1 hour (max 1 hour worth)
            // This ensures we don't constantly split into many short .ts beyond a 1-hour window.
            $hlsTime = 1;
            $hlsListSize = 3600;

            // Create start_stream.bat per-slug (NO loop)
            $ffmpegCommand = ":: Start FFmpeg Streams\r\n";
            $ffmpegCommand .= "echo Starting FFmpeg Streams...\r\n\r\n";
            $ffmpegCommand .= ":: {$cctv->slug}\r\n";
            $ffmpegCommand .= "echo Starting stream for {$cctv->slug}...\r\n";
            $ffmpegCommand .= "cd /d \"{$outputDir}\"\r\n";
            $ffmpegCommand .= "start \"CCTV {$cctv->slug}\" /B ffmpeg -rtsp_transport tcp -i \"{$rtspUrl}\" -c:v libx264 -preset ultrafast -tune zerolatency -f hls -hls_time {$hlsTime} -hls_list_size {$hlsListSize} -hls_flags delete_segments playlist.m3u8 > ffmpeg_output.log 2>&1\r\n\r\n";
            $ffmpegCommand .= "echo FFmpeg Streams started.\r\n";

            File::put($cmdFilePath, $ffmpegCommand);
            Log::info("Created stream script at: {$cmdFilePath} that outputs to: {$outputDir}");

            // Execute the batch script so playlist generation starts immediately.
            // FFmpeg itself is detached via `start /B`.
            if (PHP_OS_FAMILY === 'Windows') {
                $command = 'cmd /c "' . $cmdFilePath . '"';

                $launcherLog = $outputDir . DIRECTORY_SEPARATOR . 'launcher_output.log';
                $proc = popen($command . ' 1>>"' . $launcherLog . '" 2>>&1', 'r');
                if (is_resource($proc)) {
                    pclose($proc);
                }

                Log::info("Executed stream script (foreground): {$command}");
            } else {
                // Non-windows fallback (not used in this project normally)
                $command = "cd '{$outputDir}' && bash -c 'ffmpeg -rtsp_transport tcp -i " . escapeshellarg($rtspUrl) . " -c:v libx264 -preset ultrafast -tune zerolatency -f hls -hls_time {$hlsTime} -hls_list_size {$hlsListSize} -hls_flags delete_segments playlist.m3u8'";
                exec($command);
            }

            // Kick-off in detached way so opening CCTV detail won't require extra manual click.
            // Even though start_stream.bat is created, we also attempt to execute it once here.
            if (PHP_OS_FAMILY === 'Windows') {
                try {
                    $launcherCmd = 'cmd /c "' . $cmdFilePath . '"';
                    // Fire-and-forget
                    pclose(popen('start /B ' . $launcherCmd, 'r'));
                } catch (\Throwable $e) {
                    Log::warning('Could not auto-launch start_stream.bat for ' . $cctv->slug . ': ' . $e->getMessage());
                }
            }

            $appUrl = rtrim(config('app.url') ?: '', '/');
            $playlistUrl = empty($appUrl)
                ? '/stream/' . rawurlencode($streamFolder) . '/playlist.m3u8'
                : $appUrl . '/stream/' . rawurlencode($streamFolder) . '/playlist.m3u8';


            return [
                'success' => true,
                'url' => $playlistUrl,
                'output' => $outputDir,
                'command' => $command ?? '',
                'threads' => 1,
                'deferred' => true,
            ];
        } catch (\Exception $e) {
            Log::error('Stream conversion error for CCTV ' . ($cctv->id ?? 'unknown') . ': ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Start streaming for a specific CCTV.
     */
    public function startStream(int $cctvId): bool
    {
        $cctv = Cctv::findOrFail($cctvId);

        $result = $this->convertRtspToM3u8($cctv);

        if (!empty($result['success'])) {
            $cctv->update([
                'is_streaming' => true,
                'playlist_url' => $result['url'],
            ]);
            return true;
        }

        return false;
    }

    /**
     * Stop streaming for a specific CCTV.
     */
    public function stopStream(Cctv $cctv): bool
    {
        try {
            $outputDir = public_path("stream/{$cctv->slug}");
            $pidFile = "{$outputDir}/ffmpeg.pid";

            if (File::exists($pidFile)) {
                $pid = (int) trim(File::get($pidFile));

                if ($pid > 0) {
                    if (PHP_OS_FAMILY === 'Windows') {
                        exec("taskkill /F /PID {$pid} 2>nul");
                    } else {
                        @posix_kill($pid, SIGTERM);
                    }
                }

                File::delete($pidFile);
            } else {
                // fallback: kill ffmpeg if we cannot find a specific PID
                if (PHP_OS_FAMILY === 'Windows') {
                    exec('taskkill /F /IM ffmpeg.exe 2>nul');
                } else {
                    exec('pkill -f ' . escapeshellcmd($outputDir) . ' 2>/dev/null');
                }
            }

            $cctv->update([
                'is_streaming' => false,
                'playlist_url' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error stopping stream for CCTV ' . $cctv->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test RTSP connection.
     */
    public function testConnection(Cctv $cctv): bool
    {
        try {
            $inputOptions = $this->buildInputOptions($cctv->stream_url);

            $command = sprintf(
                'ffprobe -v error %s -i "%s" -show_entries stream=codec_name -of default=noprint_wrappers=1:nokey=1 2>&1',
                $inputOptions,
                escapeshellcmd($cctv->stream_url)
            );

            exec($command, $output, $returnCode);

            return $returnCode === 0 && !empty($output);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function buildInputOptions(string $inputUrl): string
    {
        if (str_starts_with(strtolower($inputUrl), 'rtsp://')) {
            return '-rtsp_transport tcp -stimeout 5000000 -analyzeduration 5000000 -probesize 5000000';
        }

        if (str_starts_with(strtolower($inputUrl), 'http://') || str_starts_with(strtolower($inputUrl), 'https://')) {
            return '-timeout 5000000 -analyzeduration 5000000 -probesize 5000000';
        }

        return '';
    }

    // Legacy methods kept for compatibility with the existing project

    private function calculateThreadCount(float $maxThreadsPct): int
    {
        $coreCount = $this->getCpuCoreCount();
        $threads = (int) floor(max(1, $coreCount * $maxThreadsPct));
        return max(1, $threads);
    }

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
                Log::warning('Could not determine CPU core count: ' . $e->getMessage());
            }
        }

        if (is_file('/proc/cpuinfo')) {
            $cpuInfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuInfo, $matches);
            return max(1, count($matches[0]));
        }

        return 1;
    }

    public function detectCameraBrand(string $rtspUrl): ?string
    {
        try {
            $inputOptions = $this->buildInputOptions($rtspUrl);

            $command = sprintf(
                'ffprobe -v error %s -i "%s" -show_entries stream=title -of csv=p=0 2>&1',
                $inputOptions,
                escapeshellcmd($rtspUrl)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                $outputStr = strtolower(implode(' ', $output));

                if (strpos($outputStr, 'hikvision') !== false || strpos($outputStr, 'channel') !== false) {
                    return 'hikvision';
                }

                if (strpos($outputStr, 'dahua') !== false || strpos($outputStr, 'realmonitor') !== false) {
                    return 'dahua';
                }

                if (strpos($outputStr, 'axis') !== false) {
                    return 'axis';
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not detect camera brand: ' . $e->getMessage());
        }

        return null;
    }
}

