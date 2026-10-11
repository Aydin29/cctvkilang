<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cctv;
use Symfony\Component\Process\Process;

class StartStream extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:start {slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start and supervise ffmpeg process for given CCTV slug';

    public function handle()
    {
        $slug = $this->argument('slug');

        $cctv = Cctv::where('slug', $slug)->first();
        if (!$cctv) {
            $this->error("CCTV with slug {$slug} not found");
            return 1;
        }

        // Prefer explicit env override, otherwise use `ffmpeg` from PATH so
        // the system-installed executable is used when available.
        $ffmpeg = env('FFMPEG_PATH', 'ffmpeg');
        $workdir = base_path("public/stream/{$slug}");
        if (!is_dir($workdir)) {
            mkdir($workdir, 0755, true);
        }

        $logPath = public_path("stream/{$slug}/ffmpeg_output.log");

        $this->info("Starting supervisor for {$slug} using ffmpeg: {$ffmpeg}");

        // Build RTSP URL in-memory using model helper
        $input = $cctv->getRtspFullUrl();

        while (true) {
            $this->info('Launching ffmpeg for ' . $slug . ' at ' . now());

            $cmd = [
                $ffmpeg,
                '-rtsp_transport', 'tcp',
                '-analyzeduration', '5000000',
                '-probesize', '5000000',
                '-fflags', 'nobuffer',
                '-flags', 'low_delay',
                '-i', $input,
                '-c:v', 'libx264',
                '-preset', 'ultrafast',
                '-tune', 'zerolatency',
                '-f', 'hls',
                '-hls_time', '1',
                '-hls_list_size', '3',
                '-hls_flags', 'delete_segments',
                'playlist.m3u8'
            ];

            $process = new Process($cmd, $workdir);
            $process->setTimeout(0);

            // ensure log exists
            if (!file_exists($logPath)) {
                file_put_contents($logPath, "[LAUNCHER] Created log at " . now() . PHP_EOL, FILE_APPEND);
            }

            $process->run(function ($type, $buffer) use ($logPath) {
                file_put_contents($logPath, $buffer, FILE_APPEND);
            });

            $exit = $process->getExitCode();
            file_put_contents($logPath, "[LAUNCHER] ffmpeg exited with {$exit} at " . now() . PHP_EOL, FILE_APPEND);

            // wait briefly before restart
            sleep(3);
        }

        return 0;
    }
}
