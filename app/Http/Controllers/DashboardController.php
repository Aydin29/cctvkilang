<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use App\Events\CctvStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $statistics = $this->getStatistics();
        $streamingStats = $this->getStreamingStats();
        $systemStatus = $this->getSystemStatus();

        // Broadcast the real-time update
        broadcast(new CctvStatusUpdated($statistics, $streamingStats, $systemStatus));

        // Get all CCTVs for the map and other features
        $cctvs = Cctv::paginate(10); // Implement pagination

        // Location statistics
        $locationStats = Cctv::select('location', DB::raw('count(*) as count'))
            ->groupBy('location')
            ->pluck('count', 'location')
            ->toArray();

        // Recent activity
        $recentActivity = Cctv::orderBy('updated_at', 'desc')
            ->take(8)
            ->get();

        // Live CCTV feeds for dashboard - limit to 8 cameras and avoid file system checks
        $liveFeeds = Cctv::take(8)
            ->get()
            ->map(function ($cctv) {
                // Use the playlist_url from database instead of file system check
                $playlistUrl = $cctv->is_streaming ? $cctv->playlist_url : null;
                
                return [
                    'id' => $cctv->id,
                    'name' => $cctv->name,
                    'location' => $cctv->location,
                    'status' => $cctv->status,
                    'is_streaming' => $cctv->is_streaming ?? false,
                    'stream_url' => $cctv->streaming_url ?? null,
                    'playlist_url' => $playlistUrl,
                    'thumbnail' => $cctv->thumbnail_url ?? null,
                ];
            })
            ->toArray();

        return view('dashboard', compact(
            'statistics',
            'streamingStats',
            'systemStatus',
            'locationStats',
            'recentActivity',
            'cctvs',
            'liveFeeds'
        ));
    }

    private function getStatistics()
    {
        // Single query to get all statistics
        $stats = Cctv::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "online" THEN 1 ELSE 0 END) as online,
            SUM(CASE WHEN status = "offline" THEN 1 ELSE 0 END) as offline
        ')->first();

        return [
            'total' => $stats->total,
            'online' => $stats->online,
            'offline' => $stats->offline,
        ];
    }

    private function getStreamingStats()
    {
        // Single query to get all streaming statistics
        $stats = Cctv::selectRaw('
            SUM(CASE WHEN is_streaming = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN is_streaming = 0 THEN 1 ELSE 0 END) as inactive,
            SUM(CASE WHEN is_streaming = 1 AND playlist_url IS NOT NULL THEN 1 ELSE 0 END) as conversion_success,
            SUM(CASE WHEN is_streaming = 1 AND playlist_url IS NULL THEN 1 ELSE 0 END) as conversion_failed
        ')->first();

        return [
            'active' => $stats->active,
            'inactive' => $stats->inactive,
            'conversion_success' => $stats->conversion_success,
            'conversion_failed' => $stats->conversion_failed,
        ];
    }

    private function getSystemStatus()
    {
        $cpuLoad = 'N/A'; // Default value if function is not available

        // Check if sys_getloadavg() exists before calling it
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $cpuLoad = $load[0]; // Get the 1-minute load average
        }

        return [
            'server' => 'online',
            'database' => 'connected',
            'network' => 'stable',
            'security' => 'active',
            'disk_usage' => number_format(disk_free_space('/') / disk_total_space('/') * 100, 0),
            'cpu_load' => $cpuLoad, // Use the determined CPU load
            'memory_usage' => number_format(memory_get_usage(true) / 1024 / 1024, 0),
        ];
    }
}