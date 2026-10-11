@extends('layouts.cctv')

@section('title', 'Detail Kamera CCTV')

@section('styles')
<style>
    .video-container {
        border-radius: 5px;
        overflow: hidden;
        background-color: #000;
    }
    
    .stream-controls .btn {
        transition: all 0.2s;
    }
    
    .stream-controls .btn:hover {
        transform: translateY(-2px);
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .stream-overlay {
        border-radius: 5px;
        z-index: 10;
    }
    
    #snapshotImage {
        max-height: 70vh;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Detail Kamera CCTV</h3>
            <div>
                <a href="{{ route('cctvs.edit', $cctv->id) }}" class="btn btn-warning me-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('cctvs.destroy', $cctv->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus CCTV ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Informasi Dasar -->
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center" style="background:#1976d2; color:white;">
                    <span><i class="fas fa-info-circle me-2"></i>Informasi Dasar</span>
                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>{{ ucfirst($cctv->status) }}</span>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th>Nama Kamera:</th>
                            <td>{{ $cctv->name }}</td>
                        </tr>
                        <tr>
                            <th>Lokasi:</th>
                            <td>{{ $cctv->location }}</td>
                        </tr>
                        <tr>
                            <th>IP Address:</th>
                            <td class="text-danger">{{ $cctv->ip_address }}</td>
                        </tr>
                        <tr>
                            <th>Brand Kamera:</th>
                            <td>{{ $cctv->camera_brand ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Koordinat:</th>
                            <td>{{ $cctv->latitude && $cctv->longitude ? $cctv->latitude . ', ' . $cctv->longitude : 'Belum diset' }}</td>
                        </tr>
                        <tr>
                            <th>Terakhir Dilihat:</th>
                            <td>{{ $cctv->last_seen ? $cctv->last_seen->diffForHumans() : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat:</th>
                            <td>{{ $cctv->created_at->format('d M Y, H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Diperbarui:</th>
                            <td>{{ $cctv->updated_at->format('d M Y, H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Live Streaming -->
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center" style="background:#388e3c; color:white;">
                    <span><i class="fas fa-video me-2"></i>Live Streaming</span>
                    <span id="streamStatus" class="badge bg-success"><i class="fas fa-broadcast-tower me-1"></i>Streaming</span>
                </div>
                <div class="card-body">
                    <div class="video-container position-relative mb-3">
                        <video id="videoPlayer" class="w-100" height="300" controls autoplay muted>
                            <source src="{{ asset("storage/streams/{$cctv->id}/playlist.m3u8") }}" type="application/x-mpegURL">
                            Browser tidak mendukung video tag.
                        </video>
                        <div class="stream-overlay d-none position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75 text-white">
                            <div class="text-center">
                                <i class="fas fa-exclamation-triangle fa-3x mb-2"></i>
                                <p>Stream tidak tersedia</p>
                                <button class="btn btn-sm btn-light" onclick="retryStream()">Coba Lagi</button>
                            </div>
                        </div>
                    </div>
                    <div class="stream-info mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                <i class="fas fa-link me-1"></i> Stream URL: 
                                <a href="{{ asset("storage/streams/{$cctv->id}/playlist.m3u8") }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 250px;">{{ asset("storage/streams/{$cctv->id}/playlist.m3u8") }}</a>
                            </span>
                            <span class="text-muted small" id="streamQuality">HD</span>
                        </div>
                    </div>
                    <div class="stream-controls d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshStream()">
                            <i class="fas fa-sync me-1"></i> Refresh
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="toggleFullscreen('videoPlayer')">
                            <i class="fas fa-expand me-1"></i> Fullscreen
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="snapshotVideo('videoPlayer')">
                            <i class="fas fa-camera me-1"></i> Snapshot
                        </button>
                        <button class="btn btn-outline-warning btn-sm" id="muteToggle" onclick="toggleMute()">
                            <i class="fas fa-volume-mute me-1"></i> Unmute
                        </button>
                        
                        <div class="ms-auto">
                            <form action="{{ route('cctvs.start-streaming', $cctv->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-play me-1"></i> Start Stream
                                </button>
                            </form>
                            <form action="{{ route('cctvs.stop-streaming', $cctv->id) }}" method="POST" class="d-inline ms-1">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-stop me-1"></i> Stop Stream
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Konfigurasi & Aksi Streaming -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center" style="background:#00bcd4; color:white;">
                    <span><i class="fas fa-cogs me-2"></i>Konfigurasi & Aksi Streaming</span>
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#streamingConfig">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="card-body collapse show" id="streamingConfig">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <i class="fas fa-play-circle me-2"></i>Status Streaming
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Status:</span>
                                        <span class="badge {{ $cctv->is_streaming ? 'bg-success' : 'bg-danger' }}" id="streamStatusBadge">
                                            {{ $cctv->is_streaming ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Durasi:</span>
                                        <span id="streamDuration">00:00:00</span>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <form action="{{ route('cctvs.start-streaming', $cctv->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-success btn-sm w-100" type="submit">
                                                <i class="fas fa-play me-1"></i> Mulai Ulang Stream
                                            </button>
                                        </form>
                                        <form action="{{ route('cctvs.stop-streaming', $cctv->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-danger btn-sm w-100" type="submit">
                                                <i class="fas fa-stop me-1"></i> Hentikan Stream
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <i class="fas fa-sliders-h me-2"></i>Pengaturan Video
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Kualitas Video</label>
                                        <select class="form-select form-select-sm">
                                            <option value="high">Tinggi (HD)</option>
                                            <option value="medium">Menengah (SD)</option>
                                            <option value="low">Rendah (LD)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Frame Rate</label>
                                        <select class="form-select form-select-sm">
                                            <option value="30">30 fps</option>
                                            <option value="25">25 fps</option>
                                            <option value="15">15 fps</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <i class="fas fa-history me-2"></i>Rekaman
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enableRecording">
                                            <label class="form-check-label" for="enableRecording">Aktifkan Rekaman</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Durasi Rekaman</label>
                                        <select class="form-select form-select-sm">
                                            <option value="1">1 Jam</option>
                                            <option value="6">6 Jam</option>
                                            <option value="12">12 Jam</option>
                                            <option value="24">24 Jam</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-list me-1"></i> Lihat Rekaman
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Snapshot Results Modal -->
    <div class="modal fade" id="snapshotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Snapshot CCTV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="snapshotImage" class="img-fluid mb-3" alt="CCTV Snapshot">
                    <div class="d-flex justify-content-center gap-2">
                        <a id="downloadSnapshot" href="#" class="btn btn-primary" download="cctv-snapshot.jpg">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Video player functions
function toggleFullscreen(id) {
    var video = document.getElementById(id);
    if (video.requestFullscreen) {
        video.requestFullscreen();
    } else if (video.webkitRequestFullscreen) {
        video.webkitRequestFullscreen();
    } else if (video.msRequestFullscreen) {
        video.msRequestFullscreen();
    }
}

function toggleMute() {
    var video = document.getElementById('videoPlayer');
    var muteButton = document.getElementById('muteToggle');
    
    if (video.muted) {
        video.muted = false;
        muteButton.innerHTML = '<i class="fas fa-volume-up me-1"></i> Mute';
    } else {
        video.muted = true;
        muteButton.innerHTML = '<i class="fas fa-volume-mute me-1"></i> Unmute';
    }
}

function snapshotVideo(id) {
    var video = document.getElementById(id);
    var canvas = document.createElement('canvas');
    var modal = new bootstrap.Modal(document.getElementById('snapshotModal'));
    
    // Set canvas dimensions to match the video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw the current video frame to the canvas
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Convert the canvas to a data URL
    var dataUrl = canvas.toDataURL('image/jpeg');
    
    // Display the snapshot in the modal
    document.getElementById('snapshotImage').src = dataUrl;
    document.getElementById('downloadSnapshot').href = dataUrl;
    
    // Show the modal
    modal.show();
}

function retryStream() {
    var video = document.getElementById('videoPlayer');
    var overlay = document.querySelector('.stream-overlay');
    
    // Hide the overlay
    overlay.classList.add('d-none');
    
    // Reload the video
    video.load();
    video.play().catch(function(error) {
        // If still can't play, show the overlay again
        overlay.classList.remove('d-none');
        updateStreamStatus(false);
    });
}

function refreshStream() {
    var video = document.getElementById('videoPlayer');
    var source = video.querySelector('source');
    
    // Add timestamp to force reload of the source
    var currentSrc = source.src.split('?')[0];
    source.src = currentSrc + '?t=' + new Date().getTime();
    
    // Reload the video
    video.load();
    video.play().catch(function(error) {
        // If can't play, show the overlay
        document.querySelector('.stream-overlay').classList.remove('d-none');
        updateStreamStatus(false);
    });
}

function updateStreamStatus(isActive) {
    var statusBadge = document.getElementById('streamStatus');
    var statusBadge2 = document.getElementById('streamStatusBadge');
    
    if (isActive) {
        statusBadge.className = 'badge bg-success';
        statusBadge.innerHTML = '<i class="fas fa-broadcast-tower me-1"></i>Streaming';
        
        if (statusBadge2) {
            statusBadge2.className = 'badge bg-success';
            statusBadge2.textContent = 'Aktif';
        }
    } else {
        statusBadge.className = 'badge bg-danger';
        statusBadge.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Offline';
        
        if (statusBadge2) {
            statusBadge2.className = 'badge bg-danger';
            statusBadge2.textContent = 'Tidak Aktif';
        }
    }
}

// Stream duration timer
var streamStartTime = new Date();
function updateStreamDuration() {
    var now = new Date();
    var diff = (now - streamStartTime) / 1000; // in seconds
    
    var hours = Math.floor(diff / 3600);
    var minutes = Math.floor((diff % 3600) / 60);
    var seconds = Math.floor(diff % 60);
    
    // Format the time
    var formattedTime = 
        (hours < 10 ? '0' : '') + hours + ':' +
        (minutes < 10 ? '0' : '') + minutes + ':' +
        (seconds < 10 ? '0' : '') + seconds;
    
    document.getElementById('streamDuration').textContent = formattedTime;
}

// Add video event listeners
document.addEventListener('DOMContentLoaded', function() {
    var video = document.getElementById('videoPlayer');
    var overlay = document.querySelector('.stream-overlay');
    
    // Check if video can play
    video.addEventListener('error', function() {
        overlay.classList.remove('d-none');
        updateStreamStatus(false);
    });
    
    video.addEventListener('playing', function() {
        updateStreamStatus(true);
    });
    
    // Update stream duration every second
    setInterval(updateStreamDuration, 1000);
});
</script>
@endsection
