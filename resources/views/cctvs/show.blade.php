@extends('layouts.cctv')

@section('styles')
<style>
    .video-shell {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        background: #0f172a;
        min-height: 300px;
    }

    .video-shell video {
        display: block;
        width: 100%;
        height: 300px;
        background: #000;
        object-fit: cover;
    }

    .loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.82);
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 30;
        text-align: center;
        padding: 24px;
        backdrop-filter: blur(3px);
    }

    .loading-card {
        max-width: 420px;
        width: 100%;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.08);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        padding: 22px 20px;
    }

    .loading-spinner {
        width: 48px;
        height: 48px;
        margin: 0 auto 14px;
        border: 5px solid rgba(255, 255, 255, 0.22);
        border-top-color: #60a5fa;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .loading-title {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .loading-message {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.86);
        margin-bottom: 8px;
    }

    .loading-meta {
        font-size: 0.82rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .stream-status-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 10;
        font-size: 0.8rem;
        padding: 0.55rem 0.7rem;
        border-radius: 999px;
    }

    .inactive-card {
        border: 1px dashed #cbd5e1;
        background: #f8fafc;
        border-radius: 16px;
        padding: 18px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3 class="mb-0 fw-bold">Detail Kamera CCTV</h3>
            <div>
                <a href="{{ route('cctvs.edit', $cctv->id) }}" class="btn btn-warning me-2 shadow-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('cctvs.delete', $cctv) }}" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Hapus
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center" style="background:linear-gradient(135deg, #1976d2, #1565c0); color:white;">
                    <span><i class="fas fa-cogs me-2"></i>Kontrol & Informasi</span>
                </div>

                <div class="card-body">
                    <div id="modal-video-container" class="mb-4 video-shell">
                        <video id="modalVideoPlayer" class="w-100" style="height: 300px;" controls playsinline></video>

                        <div class="loading-overlay" id="modalLoadingOverlay" aria-live="polite">
                            <div class="loading-card">
                                <div class="loading-spinner"></div>
                                <div class="loading-title" id="streamLoadingTitle">Menunggu stream FFmpeg</div>
                                <div class="loading-message" id="streamLoadingMessage">Memeriksa playlist HLS...</div>
                                <div class="loading-meta" id="streamLoadingMeta">Percobaan 0/100</div>
                            </div>
                        </div>

                        <div id="modalStreamLiveBadge" class="stream-status-badge badge bg-success shadow-sm" style="display:none;">
                            <i class="fas fa-broadcast-tower me-1"></i> Live
                        </div>
                    </div>

                    <div id="stream-inactive-container" class="mb-4 text-center inactive-card" style="display:none;">
                        <i class="fas fa-video-slash fa-4x text-secondary mb-3"></i>
                        <h5 class="mb-2" id="inactiveStreamTitle">Stream Belum Aktif</h5>
                        <p class="text-muted mb-0">
                            FFmpeg belum menghasilkan playlist HLS. Sistem akan berhenti polling setelah batas percobaan tercapai.
                        </p>
                    </div>

                    <h6 class="mb-3">Informasi Dasar</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="fw-bold">Nama</td>
                            <td>{{ $cctv->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Lokasi</td>
                            <td>{{ $cctv->location }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">IP Address</td>
                            <td>{{ $cctv->ip_address }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Status</td>
                            <td>
                                <span id="cctv-status-{{ $cctv->id }}" class="badge bg-secondary"
                                    data-ip="{{ $cctv->ip_address }}"
                                    data-port="{{ $cctv->rtsp_port ?? 554 }}">
                                    Testing...
                                </span>
                            </td>
                        </tr>
                        @if($cctv->stream_url)
                        <tr>
                            <td class="fw-bold">Stream URL</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="streamUrl" value="{{ $cctv->stream_url }}" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyStreamUrl()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @if($cctv->playlist_url)
                        <tr>
                            <td class="fw-bold">Playlist URL</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="playlistUrl" value="{{ $cctv->playlist_url }}" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyPlaylistUrl()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endif
                    </table>

                    <h6 class="mb-3">Kontrol Streaming</h6>

                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-wrench me-1"></i> Debug URL playlist:
                            <span id="playlistDebugText" class="ms-1">-</span>
                        </small>
                    </div>

                    <div class="btn-group w-100 mb-3">
                        <button class="btn btn-outline-primary" onclick="refreshStream()">
                            <i class="fas fa-sync-alt"></i> Refresh Stream
                        </button>
                        <button class="btn btn-outline-secondary" onclick="toggleFullscreen()">
                            <i class="fas fa-expand"></i> Fullscreen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cctv = @json($cctv);
        const slug = @json($cctv->slug);
        const currentPort = window.location.port || '8000';
        const playlistUrl = `http://127.0.0.1:${currentPort}/stream/${encodeURIComponent(slug)}/playlist.m3u8`;

        // Ping Status Logic
        const statusBadge = document.getElementById(`cctv-status-${cctv.id}`);

        function pingIpCctv(ip, port, statusBadgeEl) {
            if (!ip || !port || !statusBadgeEl) return;

            statusBadgeEl.classList.remove('bg-success', 'bg-danger', 'bg-secondary');
            statusBadgeEl.classList.add('bg-secondary');
            statusBadgeEl.textContent = 'Testing...';

            return fetch('/api/test-ip-connection', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ ip: ip, port: port, timeout: 5 })
            })
            .then(async (response) => {
                if (!response.ok) {
                    let text = '';
                    try { text = await response.text(); } catch (e) {}
                    throw new Error(`HTTP ${response.status}: ${text.slice(0, 200)}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    statusBadgeEl.classList.remove('bg-danger', 'bg-secondary');
                    statusBadgeEl.classList.add('bg-success');
                    statusBadgeEl.textContent = 'Online';
                } else {
                    statusBadgeEl.classList.remove('bg-success', 'bg-secondary');
                    statusBadgeEl.classList.add('bg-danger');
                    statusBadgeEl.textContent = 'Offline';
                }
            })
            .catch(err => {
                console.error('pingIpCctv error:', err);
                statusBadgeEl.classList.remove('bg-success', 'bg-secondary');
                statusBadgeEl.classList.add('bg-danger');
                statusBadgeEl.textContent = 'Offline';
            });
        }

        if (statusBadge) {
            const ip = statusBadge.getAttribute('data-ip');
            const port = statusBadge.getAttribute('data-port');
            
            // Auto ping on load
            pingIpCctv(ip, port, statusBadge);
        }

        const videoPlayer = document.getElementById('modalVideoPlayer');
        const loadingOverlay = document.getElementById('modalLoadingOverlay');
        const loadingTitle = document.getElementById('streamLoadingTitle');
        const loadingMessage = document.getElementById('streamLoadingMessage');
        const loadingMeta = document.getElementById('streamLoadingMeta');
        const streamLiveBadge = document.getElementById('modalStreamLiveBadge');
        const inactiveContainer = document.getElementById('stream-inactive-container');
        const inactiveStreamTitle = document.getElementById('inactiveStreamTitle');
        const playlistDebugText = document.getElementById('playlistDebugText');
        const playlistUrlInput = document.getElementById('playlistUrl');

        const state = {
            hls: null,
            attempts: 0,
            maxAttempts: 100, // Increased to support staggered 20s starts
            finished: false,
            pollingTimer: null,
            liveSynced: false
        };

        if (playlistDebugText) {
            playlistDebugText.textContent = playlistUrl;
        }

        if (playlistUrlInput) {
            playlistUrlInput.value = playlistUrl;
        }

        function setOverlay(visible, title, message, meta) {
            if (loadingOverlay) {
                loadingOverlay.style.display = visible ? 'flex' : 'none';
            }

            if (loadingTitle && title !== undefined) {
                loadingTitle.textContent = title;
            }

            if (loadingMessage && message !== undefined) {
                loadingMessage.textContent = message;
            }

            if (loadingMeta && meta !== undefined) {
                loadingMeta.textContent = meta;
            }
        }

        function showLiveState() {
            if (inactiveContainer) {
                inactiveContainer.style.display = 'none';
            }

            if (streamLiveBadge) {
                streamLiveBadge.style.display = 'inline-flex';
            }

            setOverlay(false);
        }

        function showInactiveState(message) {
            if (inactiveContainer) {
                inactiveContainer.style.display = 'block';
            }

            if (inactiveStreamTitle && message) {
                inactiveStreamTitle.textContent = message;
            }

            if (streamLiveBadge) {
                streamLiveBadge.style.display = 'none';
            }

            setOverlay(false);
        }

        function destroyHls() {
            if (state.hls) {
                state.hls.destroy();
                state.hls = null;
            }

            state.liveSynced = false;
        }

        function initializePlayer() {
            destroyHls();

            if (window.Hls && Hls.isSupported()) {
                state.hls = new Hls({
                    enableWorker: true,
                    lowLatencyMode: true,
                    backBufferLength: 30,
                    maxBufferLength: 15,
                    liveSyncDurationCount: 1,
                    liveMaxLatencyDurationCount: 3,
                    liveDurationInfinity: true,
                    startPosition: -1
                });

                const syncToLiveEdge = function () {
                    if (state.liveSynced || !state.hls || !state.hls.liveSyncPosition) {
                        return;
                    }

                    const livePosition = state.hls.liveSyncPosition;
                    if (Number.isFinite(livePosition) && livePosition > 0) {
                        state.liveSynced = true;
                        videoPlayer.currentTime = livePosition;
                    }
                };

                state.hls.loadSource(playlistUrl);
                state.hls.attachMedia(videoPlayer);

                state.hls.on(Hls.Events.MEDIA_ATTACHED, function () {
                    state.hls.startLoad(-1);
                });

                state.hls.on(Hls.Events.MANIFEST_PARSED, function () {
                    videoPlayer.muted = true;
                    videoPlayer.playsInline = true;
                    videoPlayer.play().catch(() => {});
                    showLiveState();
                });

                state.hls.on(Hls.Events.LEVEL_LOADED, function () {
                    syncToLiveEdge();
                });

                state.hls.on(Hls.Events.BUFFER_APPENDED, function () {
                    if (!state.liveSynced) {
                        syncToLiveEdge();
                        videoPlayer.play().catch(() => {});
                    }
                });

                state.hls.on(Hls.Events.ERROR, function (event, data) {
                    console.error('HLS Error:', data);

                    if (data && data.fatal) {
                        destroyHls();
                        showInactiveState('Stream tidak aktif');
                    } else if (data && data.type === Hls.ErrorTypes.NETWORK_ERROR) {
                        state.hls.startLoad(-1);
                    }
                });

                return;
            }

            if (videoPlayer.canPlayType('application/vnd.apple.mpegurl')) {
                videoPlayer.src = playlistUrl;
                videoPlayer.muted = true;
                videoPlayer.playsInline = true;
                videoPlayer.addEventListener('loadedmetadata', function () {
                    videoPlayer.play().catch(() => {});
                    showLiveState();
                }, { once: true });
                return;
            }

            showInactiveState('Browser tidak mendukung HLS');
        }

        async function isPlaylistReady() {
            try {
                const response = await fetch(playlistUrl, {
                    method: 'GET',
                    cache: 'no-store'
                });

                return response.ok;
            } catch (error) {
                return false;
            }
        }

        async function triggerStartStreaming() {
            try {
                const res = await fetch(`{{ url('cctvs') }}/${cctv.id}/start-streaming`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                // res can be 200/500; we ignore and keep polling
                await res.text().catch(() => null);
            } catch (e) {
                // ignore network errors; polling will retry
            }
        }

        async function pollPlaylist() {
            if (state.finished) {
                return;
            }

            state.attempts += 1;

            const isFirstPhase = state.attempts <= 10;
            const nextDelay = isFirstPhase ? 500 : 1000;



            setOverlay(
                true,
                'Menunggu stream FFmpeg',
                isFirstPhase
                    ? 'Memastikan FFmpeg berjalan & menyiapkan playlist HLS...'
                    : 'Polling tetap jalan: FFmpeg masih memproses stream...',
                `Percobaan ${state.attempts}/${state.maxAttempts}`
            );

            // Jalankan start-streaming hanya jika overlay/loading sudah terlihat di halaman.
            // (hasShown di-set setelah bootLoadingFlow jalan)
            if (hasShown) {
                await triggerStartStreaming();
            }


            const ready = await isPlaylistReady();

            if (ready) {
                state.finished = true;
                initializePlayer();
                return;
            }

            if (state.attempts >= state.maxAttempts) {
                state.finished = true;
                showInactiveState('Stream belum tersedia dari FFmpeg');
                return;
            }

            state.pollingTimer = window.setTimeout(pollPlaylist, nextDelay);
        }


        let hasShown = false;

        function bootLoadingFlow() {
            // Jangan jalankan start-streaming saat halaman baru dibuka.
            // Jalankan hanya setelah overlay/loading sudah tampil (DOM sudah ter-render).
            setOverlay(
                true,
                'Menunggu stream FFmpeg',
                cctv.is_streaming
                    ? 'Status backend sudah aktif, sekarang memverifikasi playlist HLS...'
                    : 'Stream belum aktif, sistem akan memulai konversi saat loading siap...',
                'Percobaan 0/100'
            );

            // Mark bahwa halaman sudah tampil.
            hasShown = true;

            pollPlaylist();
        }


        bootLoadingFlow();
    });

    function refreshStream() {
        location.reload();
    }

    function toggleFullscreen() {
        const videoPlayer = document.getElementById('modalVideoPlayer');

        if (!videoPlayer) {
            alert('Video player tidak ditemukan.');
            return;
        }

        if (!document.fullscreenElement) {
            videoPlayer.requestFullscreen().catch(err => {
                alert(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
            });
        } else {
            document.exitFullscreen();
        }
    }

    function copyStreamUrl() {
        const streamUrlInput = document.getElementById('streamUrl');
        navigator.clipboard.writeText(streamUrlInput.value).then(() => {
            showToast('Stream URL disalin ke clipboard', 'success');
        });
    }

    function copyPlaylistUrl() {
        const playlistUrlInput = document.getElementById('playlistUrl');
        navigator.clipboard.writeText(playlistUrlInput.value).then(() => {
            showToast('Playlist URL disalin ke clipboard', 'success');
        });
    }

    function showToast(message, type = 'info') {
        const toastContainer = document.body;
        const toast = document.createElement('div');
        toast.className = `toast show position-fixed top-0 end-0 p-3 bg-${type} text-white`;
        toast.style.zIndex = '1056';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', function () {
            toast.remove();
        });
    }
</script>
@endsection
