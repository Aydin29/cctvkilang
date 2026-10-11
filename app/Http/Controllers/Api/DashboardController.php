<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cctv;
use App\Services\CctvStatusService;
use App\Services\SystemMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $cctvStatusService;
    protected $systemMonitoringService;

    public function __construct(CctvStatusService $cctvStatusService, SystemMonitoringService $systemMonitoringService)
    {
        $this->cctvStatusService = $cctvStatusService;
        $this->systemMonitoringService = $systemMonitoringService;
    }

    public function getStatus()
    {
        // Get real-time statistics by checking IP connectivity
        $statistics = $this->cctvStatusService->getRealTimeStatistics();

        // Streaming stats seharusnya berdasarkan kondisi realtime, bukan DB semata.
        // Karena saat ini endpoint `getStatus()` hanya meng-update `status` online/offline,
        // maka `is_streaming` sering tidak sinkron.
        // Untuk menyinkronkan dashboard, anggap streaming aktif = status online.
        $streamingStats = [
            'active' => $statistics['online'],
            'inactive' => $statistics['offline'],
        ];


        // Get real-time system status
        $systemStatus = $this->systemMonitoringService->getSystemStatus();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $statistics,
                'streaming' => $streamingStats,
                'system' => $systemStatus, // Keep for backward compatibility but not used in UI
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }
}
