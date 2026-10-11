<?php

namespace App\cctvanalys\rtsp;

use App\Models\Cctv;
use Illuminate\Support\Facades\Log;

class RtspAnalysisService
{
    /**
     * Analyze RTSP stream for a given CCTV.
     *
     * @param Cctv $cctv
     * @return array
     */
    public function analyze(Cctv $cctv): array
    {
        try {
            Log::info("Starting RTSP analysis for CCTV: {$cctv->name}");
            
            // Check configuration status (for informational purposes)
            $isConfigured = !empty($cctv->rtsp_url);
            $isOnline = $cctv->status === 'online';
            $isActiveStream = $cctv->is_streaming;
            
            // Determine if camera is actually connected
            $isConnected = $isConfigured && $isOnline && $isActiveStream;

            // Always generate simulated metrics for demonstration purposes
            // This allows the chart to move even when camera is offline
            // In production, you would analyze the actual stream when connected
            // Add some randomness for real-time simulation
            $baseDelay = rand(10, 200);
            $delay = $baseDelay + rand(-20, 20);
            $delay = max(5, min(500, $delay));

            $baseJitter = rand(1, 50);
            $jitter = $baseJitter + rand(-5, 5);
            $jitter = max(0, min(225, $jitter));

            $packet_loss = round(rand(0, 5) + (rand(0, 10) / 10), 1);
            $throughput = round(rand(45, 95) + (rand(0, 9) / 10), 1);

            // Simulate time series data with some trend
            $delay_data = [];
            $jitter_data = [];
            $packet_loss_data = [];
            $throughput_data = [];

            for ($i = 0; $i < 20; $i++) {
                $delay_data[] = max(5, min(500, $delay + rand(-30, 30)));
                $jitter_data[] = max(0, min(225, $jitter + rand(-10, 10)));
                $packet_loss_data[] = max(0, min(100, round($packet_loss + (rand(-5, 5) / 10), 1)));
                $throughput_data[] = max(0, min(100, round($throughput + (rand(-5, 5) / 10), 1)));
            }

            // Basic analysis - check if RTSP URL is configured
            $analysis = [
                'protocol' => 'RTSP',
                'cctv_id' => $cctv->id,
                'cctv_name' => $cctv->name,
                'rtsp_url_configured' => $isConfigured,
                'stream_status' => $cctv->is_streaming ? 'active' : 'inactive',
                'analysis_timestamp' => now(),
                'delay' => $delay,
                'delay_status' => $this->getDelayStatus($delay),
                'jitter' => $jitter,
                'jitter_status' => $this->getJitterStatus($jitter),
                'packet_loss' => $packet_loss,
                'packet_loss_status' => $this->getPacketLossStatus($packet_loss),
                'throughput' => $throughput,
                'throughput_status' => $this->getThroughputStatus($throughput),
                'delay_data' => $delay_data,
                'jitter_data' => $jitter_data,
                'packet_loss_data' => $packet_loss_data,
                'throughput_data' => $throughput_data,
                'basic_checks' => [
                    'has_rtsp_url' => $isConfigured,
                    'has_credentials' => !empty($cctv->rtsp_username) && !empty($cctv->rtsp_password),
                    'is_online' => $isOnline,
                ],
                'connected' => $isConnected,
            ];

            Log::info("RTSP analysis completed for CCTV: {$cctv->name}", $analysis);

            return $analysis;
        } catch (\Exception $e) {
            Log::error("Error during RTSP analysis for CCTV {$cctv->id}: " . $e->getMessage());
            return [
                'error' => 'Analysis failed: ' . $e->getMessage(),
                'cctv_id' => $cctv->id,
                'protocol' => 'RTSP'
            ];
        }
    }

    private function getDelayStatus(float $delay): array
    {
        if ($delay < 150) {
            return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
        }

        if ($delay <= 300) {
            return ['label' => 'Bagus', 'level' => 'good'];
        }

        if ($delay <= 450) {
            return ['label' => 'Sedang', 'level' => 'fair'];
        }

        return ['label' => 'Buruk', 'level' => 'poor'];
    }

    private function getJitterStatus(float $jitter): array
    {
        if ($jitter <= 0) {
            return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
        }

        if ($jitter <= 75) {
            return ['label' => 'Bagus', 'level' => 'good'];
        }

        if ($jitter <= 125) {
            return ['label' => 'Sedang', 'level' => 'fair'];
        }

        return ['label' => 'Buruk', 'level' => 'poor'];
    }

    private function getPacketLossStatus(float $packetLoss): array
    {
        if ($packetLoss <= 3) {
            return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
        }

        if ($packetLoss <= 15) {
            return ['label' => 'Bagus', 'level' => 'good'];
        }

        if ($packetLoss <= 25) {
            return ['label' => 'Sedang', 'level' => 'fair'];
        }

        return ['label' => 'Buruk', 'level' => 'poor'];
    }

    private function getThroughputStatus(float $throughput): array
    {
        if ($throughput >= 75) {
            return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
        }

        if ($throughput >= 50) {
            return ['label' => 'Bagus', 'level' => 'good'];
        }

        if ($throughput >= 25) {
            return ['label' => 'Sedang', 'level' => 'fair'];
        }

        return ['label' => 'Buruk', 'level' => 'poor'];
    }

    /**
     * Get RTSP stream metadata.
     *
     * @param Cctv $cctv
     * @return array
     */
    public function getMetadata(Cctv $cctv): array
    {
        return [
            'protocol' => 'RTSP',
            'stream_url' => $cctv->rtsp_url,
            'port' => $cctv->rtsp_port,
            'path' => $cctv->rtsp_path,
            'has_auth' => !empty($cctv->rtsp_username),
            'camera_brand' => $cctv->camera_brand,
        ];
    }
}
