<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CctvController extends Controller
{
    /**
     * Test RTSP connection using FFmpeg.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testRtspConnection(Request $request)
    {
        try {
            $url = $request->input('url');
            if (!$url) {
                return response()->json(['success' => false, 'error' => 'URL tidak valid'], 400);
            }

            // Build FFprobe options based on URL protocol
            $inputOptions = '';
            if (str_starts_with(strtolower($url), 'rtsp://')) {
                $inputOptions = '-rtsp_transport tcp -stimeout 5000000';
            } elseif (str_starts_with(strtolower($url), 'http://') || str_starts_with(strtolower($url), 'https://')) {
                $inputOptions = '-timeout 5000000';
            }

            // Use FFprobe to test RTSP connection
            // Catatan: ffprobe kadang exit code 0 meski output kosong.
            // Jadi kita ambil stream metadata sesingkat mungkin agar stdout lebih sering terisi.
            $command = sprintf(
                'ffprobe -v error %s -select_streams v:0 -show_entries stream=codec_type -of default=noprint_wrappers=1:nokey=1 "%s"',
                $inputOptions,
                $url
            );

            // Execute command with a timeout
            $descriptorspec = array(
                0 => array("pipe", "r"),  // stdin
                1 => array("pipe", "w"),  // stdout
                2 => array("pipe", "w")   // stderr
            );

            $process = proc_open($command, $descriptorspec, $pipes, null, null);
            
            if (is_resource($process)) {
                // Set streams to non-blocking
                stream_set_blocking($pipes[1], 0);
                stream_set_blocking($pipes[2], 0);

                // Wait for up to 6 seconds
                $start = time();
                $output = '';
                $error = '';

                while (time() - $start < 6) {
                    $read = array($pipes[1], $pipes[2]);
                    $write = null;
                    $except = null;

                    if (stream_select($read, $write, $except, 1)) {
                        foreach ($read as $pipe) {
                            if ($pipe === $pipes[1]) {
                                $output .= fread($pipe, 8192);
                            } else {
                                $error .= fread($pipe, 8192);
                            }
                        }
                    }

                    $status = proc_get_status($process);
                    if (!$status['running']) {
                        break;
                    }
                }

                // Clean up
                foreach ($pipes as $pipe) {
                    fclose($pipe);
                }

                $status = proc_get_status($process);
                if ($status['running']) {
                    proc_terminate($process);
                }
                $exitCode = proc_close($process);


                // Deteksi sukses yang lebih stabil:
                // - ffprobe exit code 0 biasanya berarti RTSP bisa diakses
                // - output tidak kosong mendukung bahwa stream metadata terbaca
                $outputTrimmed = trim((string) $output);

                if ($exitCode === 0 && $outputTrimmed !== '') {
                    return response()->json(['success' => true]);
                }

                if ($error) {
                    Log::error('RTSP connection test error (ffprobe): ' . $error);
                    return response()->json(['success' => false, 'error' => 'Gagal mengakses stream: ' . $error]);
                }

                $fallbackMsg = $outputTrimmed !== ''
                    ? ('Stream tidak valid atau belum terdeteksi (output: ' . $outputTrimmed . ')')
                    : 'Stream tidak valid atau tidak dapat diakses';

                return response()->json(['success' => false, 'error' => $fallbackMsg]);
            }
            
            return response()->json(['success' => false, 'error' => 'Gagal menjalankan test koneksi']);

        } catch (\Exception $e) {
            Log::error('Error testing RTSP connection: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
