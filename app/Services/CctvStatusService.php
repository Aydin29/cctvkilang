<?php

namespace App\Services;

use App\Models\Cctv;
use Illuminate\Support\Facades\Log;

class CctvStatusService
{
    /**
     * Ping a CCTV IP address to check if it's online
     *
     * @param string $ipAddress
     * @param int $port
     * @param int $timeout
     * @return bool
     */
    public function pingCctv(string $ipAddress, int $port = 80, int $timeout = 5): bool
    {
        try {
            // Use fsockopen for TCP connection test
            $connection = @fsockopen($ipAddress, $port, $errno, $errstr, $timeout);

            if ($connection) {
                fclose($connection);
                return true;
            }

            // If port 80 fails, try port 554 (RTSP) as fallback
            if ($port !== 554) {
                $connection = @fsockopen($ipAddress, 554, $errno, $errstr, $timeout);
                if ($connection) {
                    fclose($connection);
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::warning("Failed to ping CCTV at {$ipAddress}:{$port} - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update CCTV status based on IP connectivity
     *
     * @param Cctv $cctv
     * @return bool
     */
    public function updateCctvStatus(Cctv $cctv): bool
    {
        if (empty($cctv->ip_address)) {
            $cctv->update(['status' => 'offline']);
            return false;
        }

        $isOnline = $this->pingCctv($cctv->ip_address, $cctv->rtsp_port ?? 554);

        $newStatus = $isOnline ? 'online' : 'offline';

        if ($cctv->status !== $newStatus) {
            $cctv->update([
                'status' => $newStatus,
                'last_seen' => now()
            ]);

            Log::info("CCTV {$cctv->name} ({$cctv->ip_address}) status changed to {$newStatus}");
        }

        return $isOnline;
    }

    /**
     * Update all CCTV statuses
     *
     * @return array
     */
    public function updateAllCctvStatuses(): array
    {
        $cctvs = Cctv::all();
        $results = [
            'total' => $cctvs->count(),
            'online' => 0,
            'offline' => 0,
            'updated' => []
        ];

        foreach ($cctvs as $cctv) {
            $wasOnline = $this->updateCctvStatus($cctv);
            if ($wasOnline) {
                $results['online']++;
            } else {
                $results['offline']++;
            }

            $results['updated'][] = [
                'id' => $cctv->id,
                'name' => $cctv->name,
                'ip_address' => $cctv->ip_address,
                'status' => $cctv->status,
                'is_online' => $wasOnline
            ];
        }

        return $results;
    }

    /**
     * Get real-time statistics
     *
     * @return array
     */
    public function getRealTimeStatistics(): array
    {
        // Update all statuses first
        $this->updateAllCctvStatuses();

        return [
            'total' => Cctv::count(),
            'online' => Cctv::where('status', 'online')->count(),
            'offline' => Cctv::where('status', 'offline')->count(),
        ];
    }
}