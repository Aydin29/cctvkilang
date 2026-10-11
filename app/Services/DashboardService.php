<?php

namespace App\Services;

use App\Models\Cctv;
use App\Events\CctvStatusUpdated;

class DashboardService
{
    /**
     * Get CPU load average, with Windows compatibility
     * @return float
     */
    private function getCpuLoad(): float
    {
        // Menggunakan PowerShell untuk Windows
        if (PHP_OS_FAMILY === 'Windows') {
            // Hindari error environment seperti `wmic` tidak dikenali / hang.
            // Pakai PowerShell yang tersedia di Windows, dan jika gagal kembalikan 0.0.
            try {
                $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -Command "(Get-CimInstance Win32_Processor).LoadPercentage"';
                $output = [];
                @exec($cmd, $output);

                if (!empty($output) && isset($output[0])) {
                    $val = (float) trim((string)$output[0]);
                    if ($val >= 0) {
                        return $val / 100;
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Could not get CPU load: ' . $e->getMessage());
            }

            return 0.0;
        }


        // Default value jika tidak bisa mendapatkan CPU load
        return 0.0;
    }

    /**
     * Get current statistics for dashboard
     * @return array
     */
    public function getStatistics(): array
    {
        $statistics = [
            'total' => Cctv::count(),
            'online' => Cctv::where('status', 'online')->count(),
            'offline' => Cctv::where('status', 'offline')->count(),
        ];

        $streamingStats = [
            'active' => Cctv::where('is_streaming', true)->count(),
            'inactive' => Cctv::where('is_streaming', false)->count(),
            'conversion_success' => Cctv::where('is_streaming', true)->where('playlist_url', '!=', null)->count(),
            'conversion_failed' => Cctv::where('is_streaming', true)->where('playlist_url', null)->count()
        ];

        $systemStatus = [
            'server' => 'online',
            'database' => 'connected',
            'network' => 'stable',
            'security' => 'active',
            'disk_usage' => number_format(disk_free_space('/') / disk_total_space('/') * 100, 0),
            'cpu_load' => $this->getCpuLoad(),
            'memory_usage' => number_format(memory_get_usage(true) / 1024 / 1024, 0),
        ];

        return [
            'statistics' => $statistics,
            'streamingStats' => $streamingStats,
            'systemStatus' => $systemStatus
        ];
    }

    /**
     * Broadcast dashboard updates
     * @return array
     */
    public function broadcastUpdate(): array
    {
        $data = $this->getStatistics();
        broadcast(new CctvStatusUpdated(
            $data['statistics'],
            $data['streamingStats'],
            $data['systemStatus']
        ));
        
        return $data;
    }
}
