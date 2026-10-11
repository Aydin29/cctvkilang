@extends('layouts.cctv')

@section('title', 'Dashboard Overview - CCTV Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Dashboard Overview</h1>
    </div>
</div>

<!-- Statistics Cards (Clickable) -->
<div class="row mb-4">
    <div class="col-md-4">
        <a href="{{ route('cctvs.index', ['status' => 'online']) }}" class="text-decoration-none">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 id="online-count" data-stat="online">{{ $statistics['online'] }}</h3>
                            <p class="mb-0">CCTV Online</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('cctvs.index', ['status' => 'offline']) }}" class="text-decoration-none">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 id="offline-count" data-stat="offline">{{ $statistics['offline'] }}</h3>
                            <p class="mb-0">CCTV Offline</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('cctvs.index') }}" class="text-decoration-none">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 id="total-count" data-stat="total">{{ $statistics['total'] }}</h3>
                            <p class="mb-0">Total CCTV</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-video fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Real-time Status Indicator -->
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center justify-content-between gap-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-sync-alt fa-spin me-2" id="status-indicator"></i>
                <small class="mb-0">
                    <strong>Real-time Status:</strong> Monitoring CCTV connectivity via IP address
                    <span id="last-update" class="text-muted ms-2">
                        Last updated: <span id="last-update-time">{{ now()->setTimezone('Asia/Jakarta')->format('H:i:s') }}</span>
                    </span>
                </small>
            </div>


        </div>
    </div>
</div>


<!-- CCTV Activity and Streaming Status -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-white" style="background-color: #9fe400;">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>CCTV Activity</h5>
            </div>
            <div class="card-body">
                @if($recentActivity->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Kamera</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivity as $activity)
                                <tr>
                                    <td>{{ $activity->name }}</td>
                                    <td>{{ $activity->location }}</td>
                                    <td>{!! $activity->status_badge !!}</td>
                                    <td>
                                        <a href="{{ route('cctvs.show', $activity->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-clock fa-4x mb-3"></i>
                        <h4>Belum ada aktivitas CCTV</h4>
                        <p>Tambahkan kamera untuk mulai memantau aktivitas</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-white" style="background-color: #9fe400;">
                <h5 class="mb-0"><i class="fas fa-broadcast-tower me-2"></i>Status Streaming</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Streaming Aktif:</span>
                        <span class="badge bg-success">{{ $streamingStats['active'] ?? 0 }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $statistics['total'] > 0 ? (($streamingStats['active'] ?? 0) / $statistics['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Tidak Streaming:</span>
                        <span class="badge bg-secondary">{{ $streamingStats['inactive'] ?? 0 }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-secondary" style="width: {{ $statistics['total'] > 0 ? (($streamingStats['inactive'] ?? 0) / $statistics['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="{{ route('cctvs.index', ['is_streaming' => 'true']) }}" class="btn btn-outline-success">
                        <i class="fas fa-broadcast-tower me-2"></i>Lihat CCTV Streaming
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Status and Statistik CCTV -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header text-white" style="background-color: #9fe400;">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistik CCTV</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Distribusi Status CCTV</h6>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span><i class="fas fa-circle text-success me-2"></i>Online</span>
                                <span>{{ $statistics['total'] > 0 ? round(($statistics['online'] / $statistics['total']) * 100) : 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $statistics['total'] > 0 ? ($statistics['online'] / $statistics['total']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span><i class="fas fa-circle text-danger me-2"></i>Offline</span>
                                <span>{{ $statistics['total'] > 0 ? round(($statistics['offline'] / $statistics['total']) * 100) : 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: {{ $statistics['total'] > 0 ? ($statistics['offline'] / $statistics['total']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Berdasarkan Lokasi</h6>
                        @if(isset($locationStats) && count($locationStats) > 0)
                            @foreach($locationStats as $location => $count)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span><i class="fas fa-map-marker-alt me-2"></i>{{ $location }}</span>
                                        <span>{{ $count }}</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info" style="width: {{ $statistics['total'] > 0 ? ($count / $statistics['total']) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-map-marked fa-3x mb-2"></i>
                                <p>Belum ada data lokasi</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header text-white" style="background-color: #9fe400;">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>CCTV Performance</h5>
            </div>
            <div class="card-body">
                <!-- CCTV Uptime -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-clock text-success me-2"></i>CCTV Uptime</span>
                        <span class="badge bg-success">{{ $statistics['total'] > 0 ? round(($statistics['online'] / $statistics['total']) * 100) : 0 }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $statistics['total'] > 0 ? ($statistics['online'] / $statistics['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Streaming Performance -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-broadcast-tower text-info me-2"></i>Streaming Active</span>
                        <span class="badge bg-info">{{ $streamingStats['active'] ?? 0 }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: {{ $statistics['total'] > 0 ? (($streamingStats['active'] ?? 0) / $statistics['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Network Connectivity -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-wifi text-primary me-2"></i>Network Status</span>
                        <span class="badge bg-primary">Stable</span>
                    </div>
                    <small class="text-muted">All CCTV connections monitored</small>
                </div>

                <!-- Response Time -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-tachometer-alt text-warning me-2"></i>Avg Response</span>
                        <span class="badge bg-warning">~2.3s</span>
                    </div>
                    <small class="text-muted">IP connectivity check time</small>
                </div>

                <hr>

                <!-- Quick Stats -->
                <div class="row text-center">
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <div class="h5 mb-0 text-success">{{ $statistics['online'] }}</div>
                            <small class="text-muted">Online</small>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <div class="h5 mb-0 text-danger">{{ $statistics['offline'] }}</div>
                            <small class="text-muted">Offline</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="h5 mb-0 text-info">{{ $streamingStats['active'] ?? 0 }}</div>
                            <small class="text-muted">Streaming</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="h5 mb-0 text-secondary">{{ $streamingStats['inactive'] ?? 0 }}</div>
                            <small class="text-muted">Inactive</small>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Recent Activity Indicator -->
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-history me-1"></i>
                        Last check: <span id="performance-last-check">{{ now()->setTimezone('Asia/Jakarta')->format('H:i:s') }}</span>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://vjs.zencdn.net/8.5.2/video.min.js"></script>
<style>
    .streaming-flash {
        animation: streamingFlash 700ms ease-in-out;
    }

    @keyframes streamingFlash {
        0% { box-shadow: 0 0 0 rgba(13, 202, 240, 0.0); }
        30% { box-shadow: 0 0 18px rgba(13, 202, 240, 0.65); transform: translateY(-2px); }
        100% { box-shadow: 0 0 0 rgba(13, 202, 240, 0.0); transform: translateY(0); }
    }
</style>
<script>
    var players = {};
    
    function updateDashboardStats(data) {
        // Show loading indicator
        const indicator = document.getElementById('status-indicator');
        indicator.classList.add('fa-spin');

        // Update CCTV counts with animation
        animateCounter('online-count', data.statistics.online);
        animateCounter('offline-count', data.statistics.offline);
        animateCounter('total-count', data.statistics.total);

        // Update streaming stats
        // Perbarui hanya elemen yang benar-benar ada pada card:
        // 1) Card "Status Streaming" (badge bg-success / bg-secondary + progress bar bg-success / bg-secondary)
        // 2) Card "CCTV Performance" (badge bg-info + progress bar bg-info + dan badge inactive bg-secondary)

        // --- STATUS STREAMING (kiri/atas) ---
        const statusStreamingActive = document.querySelector('.row.mb-4 .col-md-6 .card-body .mb-3 .badge.bg-success');
        const statusStreamingInactive = document.querySelector('.row.mb-4 .col-md-6 .card-body .mb-3:nth-of-type(2) .badge.bg-secondary');
        if (statusStreamingActive) statusStreamingActive.textContent = data.streaming.active;
        if (statusStreamingInactive) statusStreamingInactive.textContent = data.streaming.inactive;

        const statusProgressSuccess = document.querySelector('.row.mb-4 .col-md-6 .card-body .mb-3 .progress .progress-bar.bg-success');
        const statusProgressSecondary = document.querySelector('.row.mb-4 .col-md-6 .card-body .mb-3:nth-of-type(2) .progress .progress-bar.bg-secondary');
        if (statusProgressSuccess) {
            const total = data.statistics.total || 0;
            const pct = total > 0 ? (data.streaming.active / total) * 100 : 0;
            statusProgressSuccess.style.width = pct + '%';
        }
        if (statusProgressSecondary) {
            const total = data.statistics.total || 0;
            const pct = total > 0 ? (data.streaming.inactive / total) * 100 : 0;
            statusProgressSecondary.style.width = pct + '%';
        }

        // --- CCTV PERFORMANCE (kanan) ---
        const performanceStreamingActive = document.querySelector('.col-md-4 .card-body .mb-3 .badge.bg-info');
        const performanceStreamingInactive = document.querySelector('.col-md-4 .card-body .mb-3:nth-of-type(2) .badge.bg-secondary');
        if (performanceStreamingActive) performanceStreamingActive.textContent = data.streaming.active;
        if (performanceStreamingInactive) performanceStreamingInactive.textContent = data.streaming.inactive;

        const performanceProgressActive = document.querySelector('.col-md-4 .card-body .mb-3 .progress .progress-bar.bg-info');
        if (performanceProgressActive) {
            const total = data.statistics.total || 0;
            const pct = total > 0 ? (data.streaming.active / total) * 100 : 0;
            performanceProgressActive.style.width = pct + '%';
        }
        if (progressSuccess && progressSuccess.parentElement) {
            const total = data.statistics.total || 0;
            const pct = total > 0 ? (data.streaming.active / total) * 100 : 0;
            progressSuccess.style.width = pct + '%';
        }
        if (progressSecondary && progressSecondary.parentElement) {
            const total = data.statistics.total || 0;
            const pct = total > 0 ? (data.streaming.inactive / total) * 100 : 0;
            progressSecondary.style.width = pct + '%';
        }

        // Update last update timestamp in Jakarta timezone
        const now = new Date();
        const jakartaTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Jakarta"}));
        document.getElementById('last-update-time').textContent = jakartaTime.toLocaleTimeString('id-ID');

        // Update performance last check
        if (document.getElementById('performance-last-check')) {
            document.getElementById('performance-last-check').textContent = jakartaTime.toLocaleTimeString('id-ID');
        }

        // Hide loading indicator after a short delay
        setTimeout(() => {
            indicator.classList.remove('fa-spin');
        }, 500);

        // Animasi highlight untuk card Status Streaming saat berubah
        try {
            const streamingCard = document.querySelector('.row.mb-4 .col-md-6:nth-child(2) .card-body');
            if (streamingCard) {
                streamingCard.classList.remove('streaming-flash');
                // force reflow agar animasi ter-trigger ulang
                void streamingCard.offsetWidth;
                streamingCard.classList.add('streaming-flash');
            }
        } catch (e) {
            // ignore
        }
    

    function animateCounter(elementId, targetValue) {
        const element = document.getElementById(elementId);
        const currentValue = parseInt(element.textContent) || 0;
        const difference = targetValue - currentValue;
        const duration = 500; // 500ms animation
        const steps = 20;
        const stepValue = difference / steps;
        let currentStep = 0;

        const timer = setInterval(() => {
            currentStep++;
            const newValue = Math.round(currentValue + (stepValue * currentStep));
            element.textContent = newValue;

            if (currentStep >= steps) {
                clearInterval(timer);
                element.textContent = targetValue; // Ensure final value is exact
            }
        }, duration / steps);
    }

    async function pingAllCctv() {
        const btn = document.getElementById('ping-all-btn');
        if (!btn) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memeriksa...';

        try {
            const response = await fetch('/api/dashboard/ping-all', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({})
            });

            if (!response.ok) {
                let text = '';
                try { text = await response.text(); } catch (e) {}
                throw new Error(`HTTP ${response.status}: ${text}`);
            }

            const data = await response.json();

            // Update last update timestamp
            const now = new Date();
            const jakartaTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Jakarta"}));
            const lastUpdateEl = document.getElementById('last-update-time');
            if (lastUpdateEl) {
                lastUpdateEl.textContent = jakartaTime.toLocaleTimeString('id-ID');
            }

            // Refresh dashboard counters (use existing stats API)
            fetch('/api/dashboard/stats')
                .then(r => r.json())
                .then(d => updateDashboardStats(d.data))
                .catch(console.error);
        } catch (e) {
            console.error('pingAllCctv error:', e);
            alert('Gagal ping semua CCTV. Cek koneksi/ffprobe di server.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plug me-2"></i>Ping Semua CCTV';
        }
    }






    function updateProgressBar(barId, textId, percentage) {
        const barElement = document.getElementById(barId);
        const textElement = document.getElementById(textId);

        if (barElement && textElement) {
            const currentWidth = parseFloat(barElement.style.width) || 0;
            const targetWidth = percentage;
            const difference = targetWidth - currentWidth;
            const duration = 1000; // 1 second animation
            const steps = 30;
            const stepValue = difference / steps;
            let currentStep = 0;

            const animate = () => {
                currentStep++;
                const newWidth = currentWidth + (stepValue * currentStep);
                barElement.style.width = `${Math.max(0, Math.min(100, newWidth))}%`;

                if (currentStep >= steps) {
                    barElement.style.width = `${targetWidth}%`;
                    textElement.textContent = `${Math.round(targetWidth)}%`;
                } else {
                    requestAnimationFrame(animate);
                }
            };

            // Animate text counter
            const currentText = parseInt(textElement.textContent) || 0;
            const textDifference = Math.round(targetWidth) - currentText;
            const textStepValue = textDifference / steps;
            let textCurrentStep = 0;

            const animateText = () => {
                textCurrentStep++;
                const newText = Math.round(currentText + (textStepValue * textCurrentStep));
                textElement.textContent = `${newText}%`;

                if (textCurrentStep < steps) {
                    setTimeout(animateText, duration / steps);
                } else {
                    textElement.textContent = `${Math.round(targetWidth)}%`;
                }
            };

            animate();
            animateText();
        }
    }

    // CCTV Feed Functions
    function toggleFullscreen(cameraId) {
        if (players[cameraId]) {
            if (players[cameraId].isFullscreen()) {
                players[cameraId].exitFullscreen();
            } else {
                players[cameraId].requestFullscreen();
            }
        }
    }

    function changeGridSize(size) {
        const gridButtons = document.querySelectorAll('.btn-group .btn');
        gridButtons.forEach(btn => btn.classList.remove('active'));
        
        const selectedButton = document.querySelector(`[onclick="changeGridSize('${size}')"]`);
        if (selectedButton) {
            selectedButton.classList.add('active');
        }

        const feedContainer = document.querySelector('.row .col-12 .card-body .row');
        if (feedContainer) {
            const columns = {
                '2': 'col-lg-6 col-md-6 col-sm-12',
                '4': 'col-lg-3 col-md-4 col-sm-6',
                '6': 'col-lg-2 col-md-3 col-sm-4'
            };
            
            const videoCards = feedContainer.querySelectorAll('.col-lg-3');
            videoCards.forEach(card => {
                card.className = columns[size] + ' mb-4';
            });
        }
    }

    function refreshCctvFeeds() {
        Object.keys(players).forEach(cameraId => {
            if (players[cameraId]) {
                const player = players[cameraId];
                const currentTime = player.currentTime();
                const src = player.currentSrc();
                player.src({
                    src: src.split('?')[0] + '?t=' + new Date().getTime(),
                    type: 'application/x-mpegURL'
                });
                player.load();
                player.play().then(function() {
                    player.currentTime(currentTime);
                }).catch(function(error) {
                    console.error('Error playing video:', error);
                });
            }
        });
    }

    // Initialize VideoJS players
    document.addEventListener('DOMContentLoaded', function() {
        const videoElements = document.querySelectorAll('video-js');
        videoElements.forEach(video => {
            const cameraId = video.id.replace('videoPlayer-', '');
            players[cameraId] = videojs(video.id, {
                fluid: true,
                autoplay: true,
                muted: true,
                liveui: true,
                html5: {
                    hls: {
                        enableLowInitialPlaylist: true,
                        smoothQualityChange: true,
                        overrideNative: true,
                        maxBufferLength: 30,
                        maxMaxBufferLength: 60,
                        bufferWhilePaused: true
                    }
                }
            });

            players[cameraId].on('error', function() {
                console.error('Video player error for camera:', cameraId);
                // Auto-recover from errors
                setTimeout(function() {
                    const currentSrc = players[cameraId].currentSrc();
                    players[cameraId].src({
                        src: currentSrc.split('?')[0] + '?t=' + new Date().getTime(),
                        type: 'application/x-mpegURL'
                    });
                    players[cameraId].load();
                    players[cameraId].play();
                }, 3000);
            });

            players[cameraId].on('playing', function() {
                console.log('Video playing for camera:', cameraId);
            });
        });

        // Add hover effects to clickable cards
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            if (card.parentElement.tagName === 'A') {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                    this.style.transition = 'transform 0.3s ease';
                    this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)';
                });
            }
        });

                // Initial stats update
                fetch('/api/dashboard/stats')
                    .then(response => response.json())
                    .then(data => updateDashboardStats(data.data))
                    .catch(console.error);

        // Auto-refresh CCTV feeds every 20 seconds
        setInterval(refreshCctvFeeds, 20000);
    });

    // Real-time status updates every 20 seconds
    setInterval(function() {
        fetch('/api/dashboard/stats')
            .then(response => response.json())
            .then(data => updateDashboardStats(data.data))
            .catch(error => {
                console.error('Failed to update CCTV status:', error);
                // Hide loading indicator on error
                document.getElementById('status-indicator').classList.remove('fa-spin');
            });
    }, 20000); // Update every 20 seconds for real-time monitoring
</script>
@endsection