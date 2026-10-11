@extends('layouts.cctv')

@section('title', 'CCTV Analysis - ' . $cctv->name)

@push('styles')
<style>
:root {
    --tiphon-excellent: #28a745;  /* Green - Sangat Bagus */
    --tiphon-good: #17a2b8;       /* Teal - Bagus */
    --tiphon-fair: #ffc107;       /* Orange - Sedang */
    --tiphon-poor: #dc3545;       /* Red - Buruk */
}

.metric-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.metric-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
    transition: color 0.3s ease;
}

.metric-value.updating {
    color: #ffc107;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.chart-container {
    position: relative;
    margin: auto;
    height: 400px;
}

.status-indicator {
    animation: blink 2s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.5; }
}

.tab-content {
    padding-top: 20px;
}

/* TIPHON Color Legend */
.tiphon-legend {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
    font-size: 0.85rem;
}

.tiphon-legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.tiphon-legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.tiphon-excellent { background-color: var(--tiphon-excellent); }
.tiphon-good { background-color: var(--tiphon-good); }
.tiphon-fair { background-color: var(--tiphon-fair); }
.tiphon-poor { background-color: var(--tiphon-poor); }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Analysis Results for {{ $cctv->name }}</h4>
                    <div class="d-flex align-items-center">
                        <button id="exportPdfBtn" class="btn btn-success btn-sm me-3">
                            <i class="fas fa-file-export"></i> Export PDF
                        </button>
                        <button id="refreshPageBtn" class="btn btn-outline-primary btn-sm me-3">
                            <i class="fas fa-sync"></i> Refresh Page
                        </button>
                        <div class="me-3">
                            <small class="text-muted">
                                <i class="fas fa-circle text-success me-1" id="statusIndicator"></i>
                                <span id="statusText">Real-time monitoring active</span>
                            </small>
                        </div>
                        <a href="{{ route('cctvanalys.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- CCTV Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>CCTV Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $cctv->name }}</td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td>{{ $cctv->location }}</td>
                                </tr>
                                <tr>
                                    <th>IP Address:</th>
                                    <td>{{ $cctv->ip_address }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $cctv->status === 'online' ? 'success' : ($cctv->status === 'offline' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($cctv->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Tabs for Protocols -->
                    <ul class="nav nav-tabs" id="analysisTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="rtsp-tab" data-bs-toggle="tab" data-bs-target="#rtsp" type="button" role="tab" aria-controls="rtsp" aria-selected="true">RTSP</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="hls-tab" data-bs-toggle="tab" data-bs-target="#hls" type="button" role="tab" aria-controls="hls" aria-selected="false">HLS</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="analysisTabsContent">
                        <!-- RTSP Tab -->
                        <div class="tab-pane fade show active" id="rtsp" role="tabpanel" aria-labelledby="rtsp-tab">
                            <div class="tiphon-legend mt-3 mb-3">
                                <div class="tiphon-legend-item">
                                    <div class="tiphon-legend-dot tiphon-excellent"></div>
                                    <span>Sangat Bagus (Excellent)</span>
                                </div>
                                <div class="tiphon-legend-item">
                                    <div class="tiphon-legend-dot tiphon-good"></div>
                                    <span>Bagus (Good)</span>
                                </div>
                                <div class="tiphon-legend-item">
                                    <div class="tiphon-legend-dot tiphon-fair"></div>
                                    <span>Sedang (Fair)</span>
                                </div>
                                <div class="tiphon-legend-item">
                                    <div class="tiphon-legend-dot tiphon-poor"></div>
                                    <span>Buruk (Poor)</span>
                                </div>
                            </div>
                            <div class="mt-4">
                                @if(isset($rtspAnalysis['error']))
                                    <div class="alert alert-danger">
                                        <strong>Error:</strong> {{ $rtspAnalysis['error'] }}
                                    </div>
                                @else
                                    <div class="row mt-5">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <canvas id="rtspChart" style="max-height: 400px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2 mt-5">
                                        <div class="col-12">
                                            <h6>Nilai Saat Ini</h6>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @php
                                            $rtspDelayBadge = $rtspAnalysis['delay_status']['level'] ?? 'poor';
                                            $rtspJitterBadge = $rtspAnalysis['jitter_status']['level'] ?? 'poor';
                                            $rtspPacketLossBadge = $rtspAnalysis['packet_loss_status']['level'] ?? 'poor';
                                            $rtspThroughputBadge = $rtspAnalysis['throughput_status']['level'] ?? 'poor';
                                            $badgeClasses = [
                                                'excellent' => 'bg-success',
                                                'good' => 'bg-primary',
                                                'fair' => 'bg-warning',
                                                'poor' => 'bg-danger',
                                            ];
                                        @endphp
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Delay</h5>
                                                    <p class="card-text metric-value" id="rtspDelay">{{ $rtspAnalysis['delay'] ?? 'N/A' }} ms</p>
                                                    <span class="badge {{ $badgeClasses[$rtspDelayBadge] ?? 'bg-secondary' }}">{{ $rtspAnalysis['delay_status']['label'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Jitter</h5>
                                                    <p class="card-text metric-value" id="rtspJitter">{{ $rtspAnalysis['jitter'] ?? 'N/A' }} ms</p>
                                                    <span class="badge {{ $badgeClasses[$rtspJitterBadge] ?? 'bg-secondary' }}">{{ $rtspAnalysis['jitter_status']['label'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Packet Loss</h5>
                                                    <p class="card-text metric-value" id="rtspPacketLoss">{{ $rtspAnalysis['packet_loss'] ?? 'N/A' }} %</p>
                                                    <span class="badge {{ $badgeClasses[$rtspPacketLossBadge] ?? 'bg-secondary' }}">{{ $rtspAnalysis['packet_loss_status']['label'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Throughput</h5>
                                                    <p class="card-text metric-value" id="rtspThroughput">{{ $rtspAnalysis['throughput'] ?? 'N/A' }} %</p>
                                                    <span class="badge {{ $badgeClasses[$rtspThroughputBadge] ?? 'bg-secondary' }}">{{ $rtspAnalysis['throughput_status']['label'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $rtspDelayAvg = isset($rtspAnalysis['delay_data']) ? array_sum($rtspAnalysis['delay_data']) / max(1, count($rtspAnalysis['delay_data'])) : null;
                                        $rtspJitterAvg = isset($rtspAnalysis['jitter_data']) ? array_sum($rtspAnalysis['jitter_data']) / max(1, count($rtspAnalysis['jitter_data'])) : null;
                                        $rtspPacketLossAvg = isset($rtspAnalysis['packet_loss_data']) ? array_sum($rtspAnalysis['packet_loss_data']) / max(1, count($rtspAnalysis['packet_loss_data'])) : null;
                                        $rtspThroughputAvg = isset($rtspAnalysis['throughput_data']) ? array_sum($rtspAnalysis['throughput_data']) / max(1, count($rtspAnalysis['throughput_data'])) : null;

                                        $getStatus = function ($metric, $value) {
                                            if ($value === null) {
                                                return ['label' => 'N/A', 'level' => 'secondary'];
                                            }

                                            if ($metric === 'delay') {
                                                if ($value < 150) return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
                                                if ($value <= 300) return ['label' => 'Bagus', 'level' => 'good'];
                                                if ($value <= 450) return ['label' => 'Sedang', 'level' => 'fair'];
                                                return ['label' => 'Buruk', 'level' => 'poor'];
                                            }

                                            if ($metric === 'jitter') {
                                                if ($value <= 0) return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
                                                if ($value <= 75) return ['label' => 'Bagus', 'level' => 'good'];
                                                if ($value <= 125) return ['label' => 'Sedang', 'level' => 'fair'];
                                                return ['label' => 'Buruk', 'level' => 'poor'];
                                            }

                                            if ($metric === 'packet_loss') {
                                                if ($value <= 3) return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
                                                if ($value <= 15) return ['label' => 'Bagus', 'level' => 'good'];
                                                if ($value <= 25) return ['label' => 'Sedang', 'level' => 'fair'];
                                                return ['label' => 'Buruk', 'level' => 'poor'];
                                            }

                                            if ($metric === 'throughput') {
                                                if ($value >= 75) return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
                                                if ($value >= 50) return ['label' => 'Bagus', 'level' => 'good'];
                                                if ($value >= 25) return ['label' => 'Sedang', 'level' => 'fair'];
                                                return ['label' => 'Buruk', 'level' => 'poor'];
                                            }

                                            return ['label' => 'N/A', 'level' => 'secondary'];
                                        };

                                        $rtspDelayAvgStatus = $getStatus('delay', $rtspDelayAvg);
                                        $rtspJitterAvgStatus = $getStatus('jitter', $rtspJitterAvg);
                                        $rtspPacketLossAvgStatus = $getStatus('packet_loss', $rtspPacketLossAvg);
                                        $rtspThroughputAvgStatus = $getStatus('throughput', $rtspThroughputAvg);
                                    @endphp
                                    <div class="row mt-5">
                                        <div class="col-12 mb-2">
                                            <h6>Average Metrics</h6>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Delay Average</h5>
                                                    <p class="card-text metric-value" id="rtspDelayAvg">{{ $rtspDelayAvg !== null ? number_format($rtspDelayAvg, 1) : 'N/A' }} ms</p>
                                                    <span class="badge {{ $badgeClasses[$rtspDelayAvgStatus['level']] ?? 'bg-secondary' }}" id="rtspDelayAvgBadge">{{ $rtspDelayAvgStatus['label'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Jitter Average</h5>
                                                    <p class="card-text metric-value" id="rtspJitterAvg">{{ $rtspJitterAvg !== null ? number_format($rtspJitterAvg, 1) : 'N/A' }} ms</p>
                                                    <span class="badge {{ $badgeClasses[$rtspJitterAvgStatus['level']] ?? 'bg-secondary' }}" id="rtspJitterAvgBadge">{{ $rtspJitterAvgStatus['label'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Packet Loss Average</h5>
                                                    <p class="card-text metric-value" id="rtspPacketLossAvg">{{ $rtspPacketLossAvg !== null ? number_format($rtspPacketLossAvg, 1) : 'N/A' }} %</p>
                                                    <span class="badge {{ $badgeClasses[$rtspPacketLossAvgStatus['level']] ?? 'bg-secondary' }}" id="rtspPacketLossAvgBadge">{{ $rtspPacketLossAvgStatus['label'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Throughput Average</h5>
                                                    <p class="card-text metric-value" id="rtspThroughputAvg">{{ $rtspThroughputAvg !== null ? number_format($rtspThroughputAvg, 1) : 'N/A' }} %</p>
                                                    <span class="badge {{ $badgeClasses[$rtspThroughputAvgStatus['level']] ?? 'bg-secondary' }}" id="rtspThroughputAvgBadge">{{ $rtspThroughputAvgStatus['label'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title">Standar TIPHON QoS</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Parameter</th>
                                                                    <th>Sangat Bagus</th>
                                                                    <th>Bagus</th>
                                                                    <th>Sedang</th>
                                                                    <th>Buruk</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>Delay</td>
                                                                    <td>&lt; 150 ms</td>
                                                                    <td>150 - 300 ms</td>
                                                                    <td>300 - 450 ms</td>
                                                                    <td>&gt; 450 ms</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Jitter</td>
                                                                    <td>&le; 0 ms</td>
                                                                    <td>0 - 75 ms</td>
                                                                    <td>75 - 125 ms</td>
                                                                    <td>&gt; 125 ms</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Packet Loss</td>
                                                                    <td>&le; 3 %</td>
                                                                    <td>3 - 15 %</td>
                                                                    <td>15 - 25 %</td>
                                                                    <td>&gt; 25 %</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Throughput</td>
                                                                    <td>&ge; 75 %</td>
                                                                    <td>50 - 74 %</td>
                                                                    <td>25 - 49 %</td>
                                                                    <td>&lt; 25 %</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <!-- HLS Tab -->
                        <div class="tab-pane fade" id="hls" role="tabpanel" aria-labelledby="hls-tab">
                            <div class="tiphon-legend mt-3 mb-3">
                                <div class="tiphon-legend-item">
                                    <div class="tiphon-legend-dot tiphon-excellent"></div>
                                    <span>Sangat Bagus (Excellent)</span>
                                </div>
                                <div class="tiphon-legend-item">
                                    <div class="tiphon-legend-dot tiphon-good"></div>
                                    <span>Bagus (Good)</span>
                                </div>
                                <div class="tiphon-legend-item">
                                    <div class="tiphon-legend-dot tiphon-fair"></div>
                                    <span>Sedang (Fair)</span>
                                </div>
                                <div class="tiphon-legend-item">
                                    <div class="tiphon-legend-dot tiphon-poor"></div>
                                    <span>Buruk (Poor)</span>
                                </div>
                            </div>
                            <div class="mt-4">
                                @if(isset($hlsAnalysis['error']))
                                    <div class="alert alert-danger">
                                        <strong>Error:</strong> {{ $hlsAnalysis['error'] }}
                                    </div>
                                @else
                                    <div class="row mt-5">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <canvas id="hlsChart" style="max-height: 400px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2 mt-5">
                                        <div class="col-12">
                                            <h6>Nilai Saat Ini</h6>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @php
                                            $hlsDelayBadge = $hlsAnalysis['delay_status']['level'] ?? 'poor';
                                            $hlsJitterBadge = $hlsAnalysis['jitter_status']['level'] ?? 'poor';
                                            $hlsPacketLossBadge = $hlsAnalysis['packet_loss_status']['level'] ?? 'poor';
                                            $hlsThroughputBadge = $hlsAnalysis['throughput_status']['level'] ?? 'poor';
                                            $badgeClasses = [
                                                'excellent' => 'bg-success',
                                                'good' => 'bg-primary',
                                                'fair' => 'bg-warning',
                                                'poor' => 'bg-danger',
                                            ];
                                        @endphp
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Delay</h5>
                                                    <p class="card-text metric-value" id="hlsDelay">{{ $hlsAnalysis['delay'] ?? 'N/A' }} ms</p>
                                                    <span class="badge {{ $badgeClasses[$hlsDelayBadge] ?? 'bg-secondary' }}">{{ $hlsAnalysis['delay_status']['label'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Jitter</h5>
                                                    <p class="card-text metric-value" id="hlsJitter">{{ $hlsAnalysis['jitter'] ?? 'N/A' }} ms</p>
                                                    <span class="badge {{ $badgeClasses[$hlsJitterBadge] ?? 'bg-secondary' }}">{{ $hlsAnalysis['jitter_status']['label'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Packet Loss</h5>
                                                    <p class="card-text metric-value" id="hlsPacketLoss">{{ $hlsAnalysis['packet_loss'] ?? 'N/A' }} %</p>
                                                    <span class="badge {{ $badgeClasses[$hlsPacketLossBadge] ?? 'bg-secondary' }}">{{ $hlsAnalysis['packet_loss_status']['label'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Throughput</h5>
                                                    <p class="card-text metric-value" id="hlsThroughput">{{ $hlsAnalysis['throughput'] ?? 'N/A' }} %</p>
                                                    <span class="badge {{ $badgeClasses[$hlsThroughputBadge] ?? 'bg-secondary' }}">{{ $hlsAnalysis['throughput_status']['label'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $hlsDelayAvg = isset($hlsAnalysis['delay_data']) ? array_sum($hlsAnalysis['delay_data']) / max(1, count($hlsAnalysis['delay_data'])) : null;
                                        $hlsJitterAvg = isset($hlsAnalysis['jitter_data']) ? array_sum($hlsAnalysis['jitter_data']) / max(1, count($hlsAnalysis['jitter_data'])) : null;
                                        $hlsPacketLossAvg = isset($hlsAnalysis['packet_loss_data']) ? array_sum($hlsAnalysis['packet_loss_data']) / max(1, count($hlsAnalysis['packet_loss_data'])) : null;
                                        $hlsThroughputAvg = isset($hlsAnalysis['throughput_data']) ? array_sum($hlsAnalysis['throughput_data']) / max(1, count($hlsAnalysis['throughput_data'])) : null;

                                        $getStatus = function ($metric, $value) {
                                            if ($value === null) {
                                                return ['label' => 'N/A', 'level' => 'secondary'];
                                            }

                                            if ($metric === 'delay') {
                                                if ($value < 150) return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
                                                if ($value <= 300) return ['label' => 'Bagus', 'level' => 'good'];
                                                if ($value <= 450) return ['label' => 'Sedang', 'level' => 'fair'];
                                                return ['label' => 'Buruk', 'level' => 'poor'];
                                            }

                                            if ($metric === 'jitter') {
                                                if ($value <= 0) return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
                                                if ($value <= 75) return ['label' => 'Bagus', 'level' => 'good'];
                                                if ($value <= 125) return ['label' => 'Sedang', 'level' => 'fair'];
                                                return ['label' => 'Buruk', 'level' => 'poor'];
                                            }

                                            if ($metric === 'packet_loss') {
                                                if ($value <= 3) return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
                                                if ($value <= 15) return ['label' => 'Bagus', 'level' => 'good'];
                                                if ($value <= 25) return ['label' => 'Sedang', 'level' => 'fair'];
                                                return ['label' => 'Buruk', 'level' => 'poor'];
                                            }

                                            if ($metric === 'throughput') {
                                                if ($value >= 75) return ['label' => 'Sangat Bagus', 'level' => 'excellent'];
                                                if ($value >= 50) return ['label' => 'Bagus', 'level' => 'good'];
                                                if ($value >= 25) return ['label' => 'Sedang', 'level' => 'fair'];
                                                return ['label' => 'Buruk', 'level' => 'poor'];
                                            }

                                            return ['label' => 'N/A', 'level' => 'secondary'];
                                        };

                                        $hlsDelayAvgStatus = $getStatus('delay', $hlsDelayAvg);
                                        $hlsJitterAvgStatus = $getStatus('jitter', $hlsJitterAvg);
                                        $hlsPacketLossAvgStatus = $getStatus('packet_loss', $hlsPacketLossAvg);
                                        $hlsThroughputAvgStatus = $getStatus('throughput', $hlsThroughputAvg);
                                    @endphp
                                    <div class="row mt-5">
                                        <div class="col-12 mb-2">
                                            <h6>Average Metrics</h6>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Delay Average</h5>
                                                    <p class="card-text metric-value" id="hlsDelayAvg">{{ $hlsDelayAvg !== null ? number_format($hlsDelayAvg, 1) : 'N/A' }} ms</p>
                                                    <span class="badge {{ $badgeClasses[$hlsDelayAvgStatus['level']] ?? 'bg-secondary' }}" id="hlsDelayAvgBadge">{{ $hlsDelayAvgStatus['label'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Jitter Average</h5>
                                                    <p class="card-text metric-value" id="hlsJitterAvg">{{ $hlsJitterAvg !== null ? number_format($hlsJitterAvg, 1) : 'N/A' }} ms</p>
                                                    <span class="badge {{ $badgeClasses[$hlsJitterAvgStatus['level']] ?? 'bg-secondary' }}" id="hlsJitterAvgBadge">{{ $hlsJitterAvgStatus['label'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Packet Loss Average</h5>
                                                    <p class="card-text metric-value" id="hlsPacketLossAvg">{{ $hlsPacketLossAvg !== null ? number_format($hlsPacketLossAvg, 1) : 'N/A' }} %</p>
                                                    <span class="badge {{ $badgeClasses[$hlsPacketLossAvgStatus['level']] ?? 'bg-secondary' }}" id="hlsPacketLossAvgBadge">{{ $hlsPacketLossAvgStatus['label'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card text-center metric-card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Throughput Average</h5>
                                                    <p class="card-text metric-value" id="hlsThroughputAvg">{{ $hlsThroughputAvg !== null ? number_format($hlsThroughputAvg, 1) : 'N/A' }} %</p>
                                                    <span class="badge {{ $badgeClasses[$hlsThroughputAvgStatus['level']] ?? 'bg-secondary' }}" id="hlsThroughputAvgBadge">{{ $hlsThroughputAvgStatus['label'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title">Standar TIPHON QoS</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Parameter</th>
                                                                    <th>Sangat Bagus</th>
                                                                    <th>Bagus</th>
                                                                    <th>Sedang</th>
                                                                    <th>Buruk</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>Delay</td>
                                                                    <td>&lt; 150 ms</td>
                                                                    <td>150 - 300 ms</td>
                                                                    <td>300 - 450 ms</td>
                                                                    <td>&gt; 450 ms</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Jitter</td>
                                                                    <td>&le; 0 ms</td>
                                                                    <td>0 - 75 ms</td>
                                                                    <td>75 - 125 ms</td>
                                                                    <td>&gt; 125 ms</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Packet Loss</td>
                                                                    <td>&le; 3 %</td>
                                                                    <td>3 - 15 %</td>
                                                                    <td>15 - 25 %</td>
                                                                    <td>&gt; 25 %</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Throughput</td>
                                                                    <td>&ge; 75 %</td>
                                                                    <td>50 - 74 %</td>
                                                                    <td>25 - 49 %</td>
                                                                    <td>&lt; 25 %</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script> <!-- html2canvas not required anymore -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let rtspChart, hlsChart;
    let updateInterval;

    // Keep last known values so chart can still animate setiap 1 menit
    // walaupun kamera sempat disconnected.
    let lastRtsp = null;
    let lastHls = null;


    // Initialize charts and controls
    function initCharts() {
        // Get initial data from server-side variables
        const initialRtspData = @json($rtspAnalysis ?? []);
        const initialHlsData = @json($hlsAnalysis ?? []);
        
        // Create toggle controls for metrics
        const metrics = ['jitter','packet_loss','delay','throughput'];
        ['rtsp','hls'].forEach(proto => {
            const container = document.createElement('div');
            container.className = 'mb-2';
            metrics.forEach(metric => {
                const id = `${proto}-${metric}-toggle`;
                const label = document.createElement('label');
                label.style.marginRight = '10px';
                label.innerHTML = `<input type="checkbox" id="${id}" class="metric-toggle" data-protocol="${proto}" data-metric="${metric}" checked> ${metric.replace('_',' ')} `;
                container.appendChild(label);
            });
            const tabPane = document.getElementById(proto);
            if (tabPane) tabPane.insertBefore(container, tabPane.firstChild);
        });

        // TIPHON thresholds used for point coloring with 4 levels: excellent, good, fair, poor
        const tiphonThresholds = {
            jitter: { excellent: 0, good: 75, fair: 125 },           // 0=excellent, 0-75=good, 75-125=fair, >125=poor
            delay: { excellent: 150, good: 300, fair: 450 },         // <150=excellent, 150-300=good, 300-450=fair, >450=poor
            packet_loss: { excellent: 3, good: 15, fair: 25 },       // 0-3=excellent, 3-15=good, 15-25=fair, >25=poor
            throughput: { excellent: 75, good: 50, fair: 25, invert: true } // >=75=excellent, 50-74=good, 25-49=fair, <25=poor
        };

        // Color scheme: green (excellent), teal (good), orange (fair), red (poor)
        const tiphonColors = {
            excellent: '#28a745', // Green
            good: '#17a2b8',      // Teal
            fair: '#ffc107',      // Orange
            poor: '#dc3545'       // Red
        };

        function pointColorFor(metric, val) {
            const t = tiphonThresholds[metric];
            if (!t || val === undefined || val === null) return '#6c757d'; // grey

            if (t.invert) {
                // For throughput (higher is better)
                if (val >= t.excellent) return tiphonColors.excellent;
                if (val >= t.good) return tiphonColors.good;
                if (val >= t.fair) return tiphonColors.fair;
                return tiphonColors.poor;
            } else {
                // For delay, jitter, packet_loss (lower is better)
                if (val <= t.excellent) return tiphonColors.excellent;
                if (val <= t.good) return tiphonColors.good;
                if (val <= t.fair) return tiphonColors.fair;
                return tiphonColors.poor;
            }
        }

        function makeDataset(label, color, metricKey) {
            function hexToRgba(hex, alpha) {
                if (!hex) return `rgba(108,117,125,${alpha})`;
                const h = hex.replace('#','');
                const bigint = parseInt(h, 16);
                const r = (bigint >> 16) & 255;
                const g = (bigint >> 8) & 255;
                const b = bigint & 255;
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }

            return {
                label,
                data: [],
                borderColor: color,
                backgroundColor: hexToRgba(color, 0.08),
                borderWidth: 2,
                fill: false,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: function(ctx) {
                    return pointColorFor(metricKey, ctx.raw);
                },
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1
            };
        }

        // RTSP Chart
        const rtspCtx = document.getElementById('rtspChart').getContext('2d');
        rtspChart = new Chart(rtspCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    makeDataset('Jitter (ms)','#ff8c00','jitter'),
                    makeDataset('Packet Loss (%)','#9370DB','packet_loss'),
                    makeDataset('Delay (ms)','#FFD700','delay'),
                    makeDataset('Throughput (%)','#2E8B57','throughput')
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'RTSP Stream Analysis - Real-time Metrics',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y;
                                if (/jitter|delay/i.test(label)) return `${label}: ${value} ms`;
                                if (/packet loss|throughput/i.test(label)) return `${label}: ${value} %`;
                                return `${label}: ${value}`;
                            }
                        }
                    },
                    legend: {
                        position: 'top'
                    },
                    // zoom plugin removed to avoid console warnings and recursion errors
                    // pan/zoom not required for export or display
                    
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Time'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Value'
                        },
                        beginAtZero: true
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // HLS Chart (uses same helper functions defined above)
        const hlsCtx = document.getElementById('hlsChart').getContext('2d');
        hlsChart = new Chart(hlsCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    makeDataset('Jitter (ms)','#ff8c00','jitter'),
                    makeDataset('Packet Loss (%)','#9370DB','packet_loss'),
                    makeDataset('Delay (ms)','#FFD700','delay'),
                    makeDataset('Throughput (%)','#2E8B57','throughput')
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'HLS Stream Analysis - Real-time Metrics',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y;
                                if (/jitter|delay/i.test(label)) return `${label}: ${value} ms`;
                                if (/packet loss|throughput/i.test(label)) return `${label}: ${value} %`;
                                return `${label}: ${value}`;
                            }
                        }
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Time'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Value'
                        },
                        beginAtZero: true
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    // Update metrics display
    function updateMetrics(protocol, data) {
        const metrics = ['Delay', 'Jitter', 'PacketLoss', 'Throughput'];
        const keyMap = { Delay: 'delay', Jitter: 'jitter', PacketLoss: 'packet_loss', Throughput: 'throughput' };

        metrics.forEach(metric => {
            const element = document.getElementById(`${protocol}${metric}`);
            if (element) {
                const key = keyMap[metric];
                let value = data ? data[key] : undefined;

                if (value !== undefined && value !== null) {
                    // Format values
                    if (key === 'delay' || key === 'jitter') {
                        value = Number(value);
                        element.classList.add('updating');
                        element.textContent = value.toFixed(1) + ' ms';
                    } else if (key === 'packet_loss' || key === 'throughput') {
                        value = Number(value);
                        element.classList.add('updating');
                        element.textContent = value.toFixed(1) + ' %';
                    }

                    // Remove animation after 1 second
                    setTimeout(() => {
                        element.classList.remove('updating');
                    }, 1000);
                }
            }
        });
    }

    function computeAverage(values) {
        if (!Array.isArray(values) || values.length === 0) return null;
        const sum = values.reduce((acc, val) => acc + (Number(val) || 0), 0);
        return sum / values.length;
    }

    function getLastValue(values) {
        return Array.isArray(values) && values.length > 0 ? values[values.length - 1] : null;
    }

    function formatMetricValue(metric, value) {
        if (value === null || value === undefined || Number.isNaN(value)) return 'N/A';
        if (metric === 'delay' || metric === 'jitter') {
            return value.toFixed(1) + ' ms';
        }
        return value.toFixed(1) + ' %';
    }

    function getMetricStatus(metric, value) {
        if (value === null || value === undefined || Number.isNaN(value)) {
            return { label: 'N/A', level: 'secondary' };
        }

        if (metric === 'delay') {
            if (value < 150) return { label: 'Sangat Bagus', level: 'excellent' };
            if (value <= 300) return { label: 'Bagus', level: 'good' };
            if (value <= 450) return { label: 'Sedang', level: 'fair' };
            return { label: 'Buruk', level: 'poor' };
        }

        if (metric === 'jitter') {
            if (value <= 0) return { label: 'Sangat Bagus', level: 'excellent' };
            if (value <= 75) return { label: 'Bagus', level: 'good' };
            if (value <= 125) return { label: 'Sedang', level: 'fair' };
            return { label: 'Buruk', level: 'poor' };
        }

        if (metric === 'packet_loss') {
            if (value <= 3) return { label: 'Sangat Bagus', level: 'excellent' };
            if (value <= 15) return { label: 'Bagus', level: 'good' };
            if (value <= 25) return { label: 'Sedang', level: 'fair' };
            return { label: 'Buruk', level: 'poor' };
        }

        if (metric === 'throughput') {
            if (value >= 75) return { label: 'Sangat Bagus', level: 'excellent' };
            if (value >= 50) return { label: 'Bagus', level: 'good' };
            if (value >= 25) return { label: 'Sedang', level: 'fair' };
            return { label: 'Buruk', level: 'poor' };
        }

        return { label: 'N/A', level: 'secondary' };
    }

    function updateAverageCards(protocol, chart) {
        if (!chart || !chart.data || !Array.isArray(chart.data.datasets)) {
            return;
        }

        const metricDatasetIndex = {
            delay: 2,
            jitter: 0,
            packet_loss: 1,
            throughput: 3
        };

        const badgeClassMap = {
            excellent: 'badge bg-success',
            good: 'badge bg-primary',
            fair: 'badge bg-warning',
            poor: 'badge bg-danger',
            secondary: 'badge bg-secondary'
        };

        const averageMap = [
            { key: 'delay', id: `${protocol}DelayAvg`, badgeId: `${protocol}DelayAvgBadge` },
            { key: 'jitter', id: `${protocol}JitterAvg`, badgeId: `${protocol}JitterAvgBadge` },
            { key: 'packet_loss', id: `${protocol}PacketLossAvg`, badgeId: `${protocol}PacketLossAvgBadge` },
            { key: 'throughput', id: `${protocol}ThroughputAvg`, badgeId: `${protocol}ThroughputAvgBadge` }
        ];

        averageMap.forEach(metric => {
            const element = document.getElementById(metric.id);
            const badgeElement = document.getElementById(metric.badgeId);
            const idx = metricDatasetIndex[metric.key];
            if (!element || typeof idx !== 'number' || !chart.data.datasets[idx]) return;

            const avg = computeAverage(chart.data.datasets[idx].data);
            element.textContent = formatMetricValue(metric.key, avg);

            if (badgeElement) {
                const status = getMetricStatus(metric.key, avg);
                badgeElement.textContent = status.label;
                badgeElement.className = badgeClassMap[status.level] || 'badge bg-secondary';
            }
        });
    }

    // Update chart data
    function updateChart(chart, data) {
        const now = new Date();
        const timeLabel = now.toLocaleTimeString();

        // Add new data point
        chart.data.labels.push(timeLabel);
        chart.data.datasets[0].data.push(data.jitter || 0);
        chart.data.datasets[1].data.push(data.packet_loss || 0);
        chart.data.datasets[2].data.push(data.delay || 0);
        if (chart.data.datasets[3]) {
            chart.data.datasets[3].data.push(data.throughput || 0);
        }

        // Keep only last 20 data points
        if (chart.data.labels.length > 20) {
            chart.data.labels.shift();
            chart.data.datasets.forEach(dataset => dataset.data.shift());
        }

        chart.update('active');
    }

    // Update status indicator
    function updateStatusIndicator(isActive) {
        const indicator = document.getElementById('statusIndicator');
        const statusText = document.getElementById('statusText');

        if (isActive) {
            indicator.className = 'fas fa-circle text-success me-1';
            statusText.textContent = 'Real-time monitoring active';
        } else {
            indicator.className = 'fas fa-circle text-warning me-1';
            statusText.textContent = 'Updating...';
        }
    }

    // Fetch real-time data
    function fetchRealTimeData() {
        updateStatusIndicator(false);

        fetch(`{{ route('cctvanalys.data', $cctv) }}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
                if (data.success) {
                    const analysisData = data.data;

                    // Determine if any protocol reports a connected camera
                    const rtspIsOnline = analysisData.rtsp && analysisData.rtsp.basic_checks && analysisData.rtsp.basic_checks.is_online;
                    const hlsIsOnline = analysisData.hls && analysisData.hls.basic_checks && analysisData.hls.basic_checks.is_online;
                    const anyConnected = !!rtspIsOnline || !!hlsIsOnline;

                    // Cache last known values
                    if (analysisData.rtsp && !analysisData.rtsp.error) {
                        lastRtsp = analysisData.rtsp;
                        updateMetrics('rtsp', analysisData.rtsp);
                    }
                    if (analysisData.hls && !analysisData.hls.error) {
                        lastHls = analysisData.hls;
                        updateMetrics('hls', analysisData.hls);
                    }

                    // Update chart setiap 1 menit agar bergerak otomatis
                    // - bila connected: pakai data baru
                    // - bila tidak connected: pakai last known snapshot (atau 0 jika belum ada)
                    const rtspConnected = !!(analysisData.rtsp && !analysisData.rtsp.error && analysisData.rtsp.connected);
                    const hlsConnected = !!(analysisData.hls && !analysisData.hls.error && analysisData.hls.connected);

                    // Update chart tiap menit.
                    // - bila connected: pakai data baru
                    // - bila tidak connected: pakai last known snapshot (atau 0 jika belum ada)
                    if (rtspConnected) {
                        const rtspForChart = analysisData.rtsp;
                        updateChart(rtspChart, {
                                        jitter: rtspForChart.jitter ?? rtspForChart.jitter_value ?? 0,
                                        packet_loss: rtspForChart.packet_loss ?? rtspForChart.packet_loss_value ?? 0,
                                        delay: rtspForChart.delay ?? rtspForChart.delay_value ?? 0,
                                        throughput: rtspForChart.throughput ?? rtspForChart.throughput_value ?? 0,
                                    });
                    } else {
                        // Gunakan last known snapshot agar grafik tetap "bergerak" saat offline
                        const rtspForChart = lastRtsp || {};
                        updateChart(rtspChart, {
                            jitter: rtspForChart.jitter ?? rtspForChart.jitter_value ?? 0,
                            packet_loss: rtspForChart.packet_loss ?? rtspForChart.packet_loss_value ?? 0,
                            delay: rtspForChart.delay ?? rtspForChart.delay_value ?? 0,
                            throughput: rtspForChart.throughput ?? rtspForChart.throughput_value ?? 0,
                        });
                    }

                    if (hlsConnected) {
                        const hlsForChart = analysisData.hls;
                        updateChart(hlsChart, {
                            jitter: hlsForChart.jitter ?? hlsForChart.jitter_value ?? 0,
                            packet_loss: hlsForChart.packet_loss ?? hlsForChart.packet_loss_value ?? 0,
                            delay: hlsForChart.delay ?? hlsForChart.delay_value ?? 0,
                            throughput: hlsForChart.throughput ?? hlsForChart.throughput_value ?? 0,
                        });
                    } else {
                        const hlsForChart = lastHls || {};
                        updateChart(hlsChart, {
                            jitter: hlsForChart.jitter ?? hlsForChart.jitter_value ?? 0,
                            packet_loss: hlsForChart.packet_loss ?? hlsForChart.packet_loss_value ?? 0,
                            delay: hlsForChart.delay ?? hlsForChart.delay_value ?? 0,
                            throughput: hlsForChart.throughput ?? hlsForChart.throughput_value ?? 0,
                        });
                    }


                    // Average cards hanya dihitung saat connected (agar akurat)
                    if (analysisData.rtsp && !analysisData.rtsp.error && analysisData.rtsp.connected) {
                        updateAverageCards('rtsp', rtspChart);
                    }

                    if (analysisData.hls && !analysisData.hls.error && analysisData.hls.connected) {
                        updateAverageCards('hls', hlsChart);
                    }

                    // Update status indicator
                    if (anyConnected) {
                        updateStatusIndicator(true);
                    } else {
                        const indicator = document.getElementById('statusIndicator');
                        const statusText = document.getElementById('statusText');
                        indicator.className = 'fas fa-circle text-danger me-1';
                        statusText.textContent = 'No connection to camera';
                    }
                } else {
                    updateStatusIndicator(false);
                    console.error('Failed to fetch data:', data.error);
                }
        })
        .catch(error => {
            updateStatusIndicator(false);
            console.error('Error fetching real-time data:', error);
        });
    }


    // Simple initialization: create charts, load initial data, start updates
    initCharts();

    // Load initial data into charts
    (function loadInitialData() {
        const initialRtspData = @json($rtspAnalysis ?? []);
        const initialHlsData = @json($hlsAnalysis ?? []);

        // Populate RTSP chart with initial data
        if (initialRtspData && initialRtspData.jitter_data) {
            const now = new Date();
            const rtspLabels = initialRtspData.jitter_data.map((_, i) => {
                const time = new Date(now.getTime() - ((initialRtspData.jitter_data.length - 1 - i) * 60000));
                return time.toLocaleTimeString();
            });
            rtspChart.data.labels = rtspLabels;
            rtspChart.data.datasets[0].data = initialRtspData.jitter_data || [];
            rtspChart.data.datasets[1].data = initialRtspData.packet_loss_data || [];
            rtspChart.data.datasets[2].data = initialRtspData.delay_data || [];
            if (rtspChart.data.datasets[3]) {
                rtspChart.data.datasets[3].data = initialRtspData.throughput_data || [];
            }
            rtspChart.update();
            updateAverageCards('rtsp', rtspChart);
            updateMetrics('rtsp', {
                jitter: getLastValue(initialRtspData.jitter_data),
                packet_loss: getLastValue(initialRtspData.packet_loss_data),
                delay: getLastValue(initialRtspData.delay_data),
                throughput: getLastValue(initialRtspData.throughput_data)
            });
        }

        // Populate HLS chart with initial data
        if (initialHlsData && initialHlsData.jitter_data) {
            const now = new Date();
            const hlsLabels = initialHlsData.jitter_data.map((_, i) => {
                const time = new Date(now.getTime() - ((initialHlsData.jitter_data.length - 1 - i) * 60000));
                return time.toLocaleTimeString();
            });
            hlsChart.data.labels = hlsLabels;
            hlsChart.data.datasets[0].data = initialHlsData.jitter_data || [];
            hlsChart.data.datasets[1].data = initialHlsData.packet_loss_data || [];
            hlsChart.data.datasets[2].data = initialHlsData.delay_data || [];
            if (hlsChart.data.datasets[3]) {
                hlsChart.data.datasets[3].data = initialHlsData.throughput_data || [];
            }
            hlsChart.update();
            updateAverageCards('hls', hlsChart);
            updateMetrics('hls', {
                jitter: getLastValue(initialHlsData.jitter_data),
                packet_loss: getLastValue(initialHlsData.packet_loss_data),
                delay: getLastValue(initialHlsData.delay_data),
                throughput: getLastValue(initialHlsData.throughput_data)
            });
        }
    })();

    // Start real-time updates (once per minute)
    updateInterval = setInterval(fetchRealTimeData, 60000);

    // Initial data load
    fetchRealTimeData();

    // attach listeners for metric toggles and save button
    document.querySelectorAll('.metric-toggle').forEach(cb => {
        cb.addEventListener('change', function() {
            const protocol = this.getAttribute('data-protocol');
            const metric = this.getAttribute('data-metric');
            const chartVar = protocol === 'rtsp' ? rtspChart : hlsChart;
            if (!chartVar) return;
            const idxMap = { jitter:0, packet_loss:1, delay:2, throughput:3 };
            const dsIndex = idxMap[metric];
            if (typeof dsIndex !== 'undefined' && chartVar.data.datasets[dsIndex]) {
                chartVar.data.datasets[dsIndex].hidden = !this.checked;
                chartVar.update('none');
            }
        });
    });

    document.getElementById('exportPdfBtn').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-file-export fa-spin me-1"></i> Generating...';

        function generatePdf() {
            try {
                const activeProtocolRawElem = document.querySelector('#analysisTabs .nav-link.active');
                const activeProtocolRaw = activeProtocolRawElem ? activeProtocolRawElem.id.replace('-tab','') : 'rtsp';
                const activeProtocol = activeProtocolRaw.toUpperCase();
                const cameraIp = '{{ $cctv->ip_address ?? "" }}';
                const cameraBrand = '{{ $cctv->camera_brand ?? "" }}';
                const timestamp = new Date();
                const tsString = timestamp.toISOString().split('T')[0];
                const filename = `${cameraIp}-${cameraBrand}-${activeProtocol}-${tsString}.pdf`;

                const { jsPDF } = window.jspdf || {};
                if (!jsPDF) throw new Error('jsPDF not available');
                const pdf = new jsPDF('portrait', 'mm', 'a4');

                const chartVar = activeProtocolRaw === 'rtsp' ? rtspChart : hlsChart;
                let chartData = null;
                if (chartVar && chartVar.data && chartVar.data.labels && chartVar.data.labels.length > 0) {
                    chartData = chartVar.data;
                } else {
                    const serverData = activeProtocolRaw === 'rtsp' ? @json($rtspAnalysis ?? []) : @json($hlsAnalysis ?? []);
                    if (serverData && serverData.jitter_data) {
                        chartData = {
                            labels: serverData.jitter_data.map((_, i) => `Data ${i + 1}`),
                            datasets: [
                                { data: serverData.jitter_data || [] },
                                { data: serverData.packet_loss_data || [] },
                                { data: serverData.delay_data || [] },
                                { data: serverData.throughput_data || [] }
                            ]
                        };
                    }
                }

                const metrics = ['packet_loss','delay','throughput','jitter'];
                const metricLabels = { packet_loss:'Packet Loss (%)', delay:'Delay (ms)', throughput:'Throughput (%)', jitter:'Jitter (ms)' };
                const datasetIndex = { jitter:0, packet_loss:1, delay:2, throughput:3 };

                pdf.setFontSize(12);
                pdf.text(10, 20, `IP: ${cameraIp}`);
                pdf.text(10, 26, `Brand: ${cameraBrand}`);
                pdf.text(10, 32, `Protocol: ${activeProtocol}`);
                pdf.text(10, 38, `Waktu: ${timestamp.toLocaleString()}`);
                pdf.text(10, 44, `Data terkini per menit berdasarkan nilai terakhir setiap interval.`);

                let y = 52;
                pdf.setFontSize(9);
                const startX = 10;
                const colWidths = [40, 30, 30, 30, 30];

                pdf.setFillColor(220);
                pdf.rect(startX, y, colWidths.reduce((a,b)=>a+b,0), 8, 'F');
                pdf.setDrawColor(0);
                pdf.rect(startX, y, colWidths.reduce((a,b)=>a+b,0), 8);
                pdf.text(startX + 2, y + 6, 'Time');
                metrics.forEach((m,i)=>{
                    const label = metricLabels[m] || m;
                    pdf.text(startX + 2 + colWidths.slice(0,i+1).reduce((a,b)=>a+b,0), y + 6, label);
                });
                let x = startX;
                colWidths.forEach(w=>{ pdf.line(x, y, x, y + 8); x += w; });
                pdf.line(x, y, x, y + 8);

                y += 8;

                let latestValues = { jitter:0, packet_loss:0, delay:0, throughput:0 };
                let rowCount = 0;

                if (chartData && chartData.labels) {
                    chartData.labels.forEach((lbl, idx) => {
                        pdf.line(startX, y, x, y);
                        pdf.text(startX + 2, y + 6, lbl.toString());

                        metrics.forEach((m,i)=>{
                            const dsIndex = datasetIndex[m];
                            const val = chartData.datasets[dsIndex]?.data[idx] ?? 0;
                            pdf.text(startX + 2 + colWidths.slice(0,i+1).reduce((a,b)=>a+b,0), y + 6, val.toString());
                            if (idx === chartData.labels.length - 1) {
                                latestValues[m] = Number(val) || 0;
                            }
                        });

                        rowCount++;
                        y += 8;
                        if (y > pdf.internal.pageSize.getHeight() - 20) {
                            pdf.addPage();
                            y = 20;
                        }
                    });
                }

                function tiphonLabel(metric, value) {
                    if (metric === 'jitter') {
                        return value <= 0 ? 'Sangat Bagus' : value <= 75 ? 'Bagus' : value <= 125 ? 'Sedang' : 'Buruk';
                    }
                    if (metric === 'delay') {
                        return value < 150 ? 'Sangat Bagus' : value <= 300 ? 'Bagus' : value <= 450 ? 'Sedang' : 'Buruk';
                    }
                    if (metric === 'packet_loss') {
                        return value <= 3 ? 'Sangat Bagus' : value <= 15 ? 'Bagus' : value <= 25 ? 'Sedang' : 'Buruk';
                    }
                    if (metric === 'throughput') {
                        return value >= 75 ? 'Sangat Bagus' : value >= 50 ? 'Bagus' : value >= 25 ? 'Sedang' : 'Buruk';
                    }
                    return 'N/A';
                }

                let conclusion = 'Tidak ada data untuk dianalisis.';
                if (rowCount > 0) {
                    conclusion = `Berdasarkan nilai terakhir per menit, jitter ${latestValues.jitter.toFixed(1)} ms (${tiphonLabel('jitter', latestValues.jitter)}), packet loss ${latestValues.packet_loss.toFixed(1)} % (${tiphonLabel('packet_loss', latestValues.packet_loss)}), delay ${latestValues.delay.toFixed(1)} ms (${tiphonLabel('delay', latestValues.delay)}), dan throughput ${latestValues.throughput.toFixed(1)} % (${tiphonLabel('throughput', latestValues.throughput)}).`;
                }

                pdf.addPage();
                pdf.setFontSize(10);
                pdf.text(10, 20, 'Kesimpulan:');
                pdf.setFontSize(9);
                pdf.text(10, 28, pdf.splitTextToSize(conclusion, 180));

                pdf.save(filename);
            } catch (e) {
                console.error('PDF generation error:', e);
                alert('Gagal membuat PDF: ' + (e.message || e));
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }

        // Ensure jsPDF is available; if not try loading a local fallback
        if (window.jspdf && window.jspdf.jsPDF) {
            generatePdf();
        } else {
            loadScript('/js/jspdf.umd.min.js', function(err) {
                if (window.jspdf && window.jspdf.jsPDF) {
                    generatePdf();
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    alert('jsPDF tidak tersedia. Silakan letakkan `jspdf.umd.min.js` pada `public/js/` atau perbaiki koneksi CDN.');
                }
            });
        }
    });

    document.getElementById('refreshPageBtn').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-sync fa-spin me-1"></i> Refreshing...';

        // Ensure charts are initialized
        function doRefresh() {
            // fetch immediately and restart interval
            try {
                fetchRealTimeData();
                if (updateInterval) clearInterval(updateInterval);
                updateInterval = setInterval(fetchRealTimeData, 60000);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }

        if (typeof Chart === 'undefined') {
            // try to load local Chart.js then start
            loadScript('/js/chart.min.js', function(err) {
                if (typeof Chart !== 'undefined') {
                    ensureChartAndStart();
                    doRefresh();
                } else {
                    // fallback: still attempt to fetch data without charts
                    doRefresh();
                    alert('Chart.js tidak tersedia. Grafik mungkin tidak tampil. Letakkan `chart.min.js` pada `public/js/`.');
                }
            });
        } else {
            doRefresh();
        }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
});
</script>
@endsection
