<?php

namespace App\Console\Commands;

use App\Models\Cctv;
use App\Services\StreamConversionService;
use Illuminate\Console\Command;

class StartCctvStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cctv:start-streams {--force : Force restart all streams regardless of current status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start FFmpeg streams for all CCTV IP addresses';

    /**
     * @var StreamConversionService
     */
    protected StreamConversionService $streamService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(StreamConversionService $streamService)
    {
        parent::__construct();
        $this->streamService = $streamService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting FFmpeg streams for CCTV IP addresses...');

        $force = $this->option('force');

        // Build query based on force option
        $query = Cctv::where('status', 'online');
        
        if ($force) {
            $query->whereNotNull('stream_url');
        } else {
            $query->where('is_streaming', false)
                  ->whereNotNull('stream_url');
        }
        
        $cctvs = $query->get();

        if ($cctvs->isEmpty()) {
            $this->info('No CCTV cameras need to be started.');
            return Command::SUCCESS;
        }

        $started = 0;
        $failed = 0;

        foreach ($cctvs as $cctv) {
            $this->info("Starting stream for: {$cctv->name} ({$cctv->ip_address})");

            try {
                // If stream_url is null, generate it from the component parts
                if (empty($cctv->stream_url)) {
                    $rtspUrl = "rtsp://";
                    if ($cctv->rtsp_username && $cctv->rtsp_password) {
                        $rtspUrl .= $cctv->rtsp_username . ":" . $cctv->rtsp_password . "@";
                    }
                    $rtspUrl .= $cctv->ip_address . ":" . $cctv->rtsp_port . $cctv->rtsp_path;
                    $cctv->stream_url = $rtspUrl;
                    $cctv->save();
                }

                $result = $this->streamService->convertRtspToM3u8($cctv);

                if ($result['success']) {
                    $cctv->update([
                        'is_streaming' => true,
                        'playlist_url' => $result['url']
                    ]);
                    $this->info("  ✓ Stream started successfully for {$cctv->name}");
                    $started++;
                } else {
                    $this->error("  ✗ Failed to start stream for {$cctv->name}: " . ($result['error'] ?? 'Unknown error'));
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error starting stream for {$cctv->name}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("FFmpeg streams started in the background.");
        $this->info("Summary: {$started} started, {$failed} failed.");

        return Command::SUCCESS;
    }
}

