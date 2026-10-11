<?php

namespace App\Services;

use App\Models\Cctv;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class StreamConversionService_new
{
    /**
     * Convert RTSP stream to HLS format using FFmpeg.
     */
    public function convertRtspToM3u8(Cctv $cctv, ?int $segmentDuration = null): array
    {
        try {
            $streamFolder = $cctv->slug;
            $outputDir = public_path("stream/{$streamFolder}");

            if (File::exists($outputDir)) {
                File::deleteDirectory($outputDir);
            }

            File::makeDirectory($outputDir, 0755, true);

            $cmdFilePath = $outputDir . DIRECTORY_SEPARATOR . 'start_stream.bat';
            $rtspUrl = $cctv->stream_url;

            // start_stream.bat per-slug, TANPA loop
            $ffmpegCommand = ":: Start FFmpeg Streams\r\n";
            $ffmpegCommand .= "echo Starting FFmpeg Streams...\r\n\r\n";
            $ffmpegCommand .= ":: {$cctv->slug}\r\n";
            $ffmpegCommand .= "echo Starting stream for {$cctv->slug}...\r\n";
            $ffmpegCommand .= "cd /d \"{$outputDir}\"\r\n";
            $ffmpegCommand .= "start \"CCTV {$cctv->slug}\" /B ffmpeg -rtsp_transport tcp -i \"{$rtspUrl}\" -c:v libx264 -preset ultrafast -tune zerolatency -f hls -hls_time 1 -hls_time {$hlsTime} -hls_list_size {$hlsListSize} -hls_flags delete_segments playlist.m3u8 > ffmpeg_output.log 2>&1\r\n\r\n";
            $ffmpegCommand .= "echo FFmpeg Streams started.\r\n";
            $ffmpegCommand .= "echo.\r\n";

            File::put($cmdFilePath, $ffmpegCommand);
            Log::info("Created stream script at: {$cmdFilePath} that outputs to: {$outputDir}");

            // Execute in foreground so launcher log is generated, but FFmpeg is /B background.
            if (PHP_OS_FAMILY === 'Windows') {
                $command = 'cmd /c "' . $cmdFilePath . '"';
                $launcherLog = $outputDir . DIRECTORY_SEPARATOR . 'launcher_output.log';
                $proc = popen($command . ' 1>>"' . $launcherLog . '" 2>>&1', 'r');
                if (is_resource($proc)) {
                    pclose($proc);
                }
            } else {
                $command = "cd '{$outputDir}' && bash -c 'ffmpeg -rtsp_transport tcp -i \"{$rtspUrl}\" -c:v libx264 -preset ultrafast -tune zerolatency -f hls -hls_time 1 -hls_list_size 3 -hls_flags delete_segments playlist.m3u8'";
                exec($command);
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

    public function startStream(int $cctvId): bool
    {
        $cctv = Cctv::findOrFail($cctvId);
        $result = $this->convertRtspToM3u8($cctv);

        if ($result['success']) {
            $cctv->update([
                'is_streaming' => true,
                'playlist_url' => $result['url'],
            ]);
            return true;
        }

        return false;
    }

    public function stopStream(Cctv $cctv): bool
    {
        try {
            $outputDir = public_path("stream/{$cctv->slug}");
            $pidFile = "{$outputDir}/ffmpeg.pid";

            if (File::exists($pidFile)) {
                $pid = (int) trim(File::get($pidFile));
                if ($pid > 0 && PHP_OS_FAMILY === 'Windows') {
                    exec("taskkill /F /PID {$pid} 2>nul", $output, $exitCode);
                }

                File::delete($pidFile);
            } else {
                if (PHP_OS_FAMILY === 'Windows') {
                    exec('taskkill /F /IM ffmpeg.exe 2>nul', $output, $exitCode);
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

    // keep existing methods if other parts rely on them
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
}

