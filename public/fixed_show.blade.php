<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCTV Viewer</title>
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f9;
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Hls.js for robust HLS streaming -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.8"></script>
</head>
<body class="p-6">
    <!-- Mock data for demonstration. Replace with your Laravel Blade variables. -->
    <script>
        const cctv = {
            id: 123,
            name: "Lobby Kamera Utama",
            location: "Gedung A, Lantai 1",
            status: "active",
            ip_address: "192.168.1.100",
            camera_brand: "Hikvision",
            latitude: -6.1754,
            longitude: 106.8272,
            last_seen: new Date(Date.now() - 300000).toISOString(),
            created_at: new Date(Date.now() - 3600000).toISOString(),
            updated_at: new Date().toISOString(),
        };
    </script>
    
    <div class="container mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-2 md:mb-0">Detail Kamera CCTV</h3>
            <div class="flex space-x-2">
                <a href="#" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all duration-200">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <form action="#" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus CCTV ini?')">
                    <button class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg shadow-md transition-all duration-200">
                        <i class="fas fa-trash mr-2"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Informasi Dasar -->
            <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden">
                <div class="p-4 bg-blue-600 text-white flex justify-between items-center">
                    <span class="font-semibold text-lg"><i class="fas fa-info-circle mr-2"></i>Informasi Dasar</span>
                    <span id="cctvStatusBadge" class="badge bg-green-500 text-white font-medium rounded-full px-3 py-1">
                        <i class="fas fa-check-circle mr-1"></i>{{ ucfirst($cctv->status) }}
                    </span>
                </div>
                <div class="p-6">
                    <table class="w-full text-left">
                        <tbody>
                            <tr><th class="py-2 font-medium w-1/3 text-gray-600">Nama Kamera:</th><td class="py-2 text-gray-800">{{ $cctv->name }}</td></tr>
                            <tr><th class="py-2 font-medium text-gray-600">Lokasi:</th><td class="py-2 text-gray-800">{{ $cctv->location }}</td></tr>
                            <tr><th class="py-2 font-medium text-gray-600">IP Address:</th><td class="py-2 font-mono text-red-500">{{ $cctv->ip_address }}</td></tr>
                            <tr><th class="py-2 font-medium text-gray-600">Brand Kamera:</th><td class="py-2 text-gray-800">{{ $cctv->camera_brand ?? '-' }}</td></tr>
                            <tr><th class="py-2 font-medium text-gray-600">Koordinat:</th><td class="py-2 text-gray-800">{{ $cctv->latitude && $cctv->longitude ? $cctv->latitude . ', ' . $cctv->longitude : 'Belum diset' }}</td></tr>
                            <tr><th class="py-2 font-medium text-gray-600">Terakhir Dilihat:</th><td class="py-2 text-gray-800" id="lastSeen">{{ $cctv->last_seen ? (new DateTime($cctv->last_seen))->diff(new DateTime())->format('%h jam, %i menit yang lalu') : '-' }}</td></tr>
                            <tr><th class="py-2 font-medium text-gray-600">Dibuat:</th><td class="py-2 text-gray-800">{{ (new DateTime($cctv->created_at))->format('d M Y, H:i:s') }}</td></tr>
                            <tr><th class="py-2 font-medium text-gray-600">Diperbarui:</th><td class="py-2 text-gray-800">{{ (new DateTime($cctv->updated_at))->format('d M Y, H:i:s') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Live Streaming -->
            <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden">
                <div class="p-4 bg-green-600 text-white flex justify-between items-center">
                    <span class="font-semibold text-lg"><i class="fas fa-video mr-2"></i>Live Streaming</span>
                    <span id="streamStatus" class="inline-flex items-center rounded-full bg-gray-400 px-3 py-1 text-sm font-medium text-white transition-all">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Memuat...
                    </span>
                </div>
                <div class="p-6">
                    <div class="relative w-full aspect-video bg-black rounded-lg overflow-hidden mb-4">
                        <video id="videoPlayer" class="w-full h-full" controls autoplay muted playsinline></video>
                        <div id="streamOverlay" class="absolute top-0 left-0 w-full h-full bg-black bg-opacity-75 text-white flex flex-col justify-center items-center transition-opacity duration-300 opacity-0 invisible">
                            <div class="text-center">
                                <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                                <p class="text-lg font-medium mb-2">Stream tidak tersedia</p>
                                <button onclick="retryStream()" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 rounded-lg text-white font-semibold transition-colors">Coba Lagi</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center justify-between text-sm text-gray-500 mb-4">
                        <span>
                            <i class="fas fa-link mr-1"></i> URL Stream: 
                            <a href="#" target="_blank" class="truncate max-w-xs inline-block text-blue-500 hover:underline" id="streamUrl">
                                {{ url('stream/' . rawurlencode($cctv->slug) . '/playlist.m3u8') }}
                            </a>
                        </span>
                        <span id="streamDuration" class="font-medium">00:00:00</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button onclick="retryStream()" class="flex-1 min-w-[120px] inline-flex justify-center items-center px-4 py-2 text-blue-600 bg-blue-100 border border-blue-200 rounded-lg hover:bg-blue-200 transition-colors">
                            <i class="fas fa-sync mr-2"></i> Refresh
                        </button>
                        <button onclick="toggleFullscreen('videoPlayer')" class="flex-1 min-w-[120px] inline-flex justify-center items-center px-4 py-2 text-gray-600 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="fas fa-expand mr-2"></i> Fullscreen
                        </button>
                        <button onclick="snapshotVideo('videoPlayer')" class="flex-1 min-w-[120px] inline-flex justify-center items-center px-4 py-2 text-indigo-600 bg-indigo-100 border border-indigo-200 rounded-lg hover:bg-indigo-200 transition-colors">
                            <i class="fas fa-camera mr-2"></i> Snapshot
                        </button>
                        <button onclick="toggleMute()" id="muteToggle" class="flex-1 min-w-[120px] inline-flex justify-center items-center px-4 py-2 text-yellow-600 bg-yellow-100 border border-yellow-200 rounded-lg hover:bg-yellow-200 transition-colors">
                            <i class="fas fa-volume-mute mr-2"></i> Unmute
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Snapshot Modal -->
        <div id="snapshotModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full p-6 transform transition-all">
                    <div class="flex justify-between items-center pb-3">
                        <h3 class="text-lg font-bold text-gray-900">Snapshot CCTV</h3>
                        <button onclick="document.getElementById('snapshotModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Tutup</span>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="my-4 text-center">
                        <img id="snapshotImage" src="" class="max-h-[70vh] mx-auto rounded-lg" alt="CCTV Snapshot">
                    </div>
                    <div class="flex justify-center gap-4">
                        <a id="downloadSnapshot" href="#" download="cctv-snapshot.jpg" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors">
                            <i class="fas fa-download mr-2"></i> Download
                        </a>
                        <button onclick="document.getElementById('snapshotModal').classList.add('hidden')" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i> Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById('videoPlayer');
        const streamStatus = document.getElementById('streamStatus');
        const streamOverlay = document.getElementById('streamOverlay');
        const streamUrlElement = document.getElementById('streamUrl');

        const streamUrl = streamUrlElement.textContent.trim();

        // Function to update stream status badge
        const updateStreamStatus = (status, text, icon, color) => {
            streamStatus.className = `inline-flex items-center rounded-full px-3 py-1 text-sm font-medium text-white transition-all ${color}`;
            streamStatus.innerHTML = `<i class="fas ${icon} mr-1"></i> ${text}`;
            if (status === 'offline') {
                streamOverlay.classList.remove('opacity-0', 'invisible');
            } else {
                streamOverlay.classList.add('opacity-0', 'invisible');
            }
        };

        const loadStream = () => {
            if (Hls.isSupported()) {
                updateStreamStatus('loading', 'Memuat...', 'fa-spinner fa-spin', 'bg-gray-400');
                const hls = new Hls({
                    // Custom Hls.js configuration can go here
                });
                hls.loadSource(streamUrl);
                hls.attachMedia(video);
                hls.on(Hls.Events.MANIFEST_PARSED, () => {
                    video.play();
                    updateStreamStatus('online', 'Streaming', 'fa-broadcast-tower', 'bg-green-600');
                    video.muted = true; // Auto-play requires muted
                    streamStartTime = new Date(); // Start duration timer
                });
                hls.on(Hls.Events.ERROR, (event, data) => {
                    if (data.fatal) {
                        updateStreamStatus('offline', 'Offline', 'fa-exclamation-circle', 'bg-red-600');
                        console.error('Fatal HLS.js error:', data);
                    }
                });
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                // Fallback for Safari
                video.src = streamUrl;
                video.addEventListener('loadedmetadata', () => {
                    video.play();
                    updateStreamStatus('online', 'Streaming', 'fa-broadcast-tower', 'bg-green-600');
                    streamStartTime = new Date();
                });
                video.addEventListener('error', () => {
                    updateStreamStatus('offline', 'Offline', 'fa-exclamation-circle', 'bg-red-600');
                });
            } else {
                updateStreamStatus('unsupported', 'Browser tidak mendukung HLS', 'fa-times-circle', 'bg-red-600');
                streamOverlay.classList.remove('opacity-0', 'invisible');
            }
        };

        // Function to retry the stream
        const retryStream = () => {
            video.src = ''; // Clear source to trigger new load
            loadStream();
        };

        let streamStartTime;
        const updateStreamDuration = () => {
            if (streamStartTime) {
                const now = new Date();
                const diff = (now - streamStartTime) / 1000;
                const hours = Math.floor(diff / 3600);
                const minutes = Math.floor((diff % 3600) / 60);
                const seconds = Math.floor(diff % 60);
                document.getElementById('streamDuration').textContent = 
                    `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
        };
        setInterval(updateStreamDuration, 1000);
        
        // Load the stream on page load
        document.addEventListener('DOMContentLoaded', loadStream);

        // General UI functions
        const toggleFullscreen = (id) => {
            const element = document.getElementById(id);
            if (element.requestFullscreen) {
                element.requestFullscreen();
            } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen();
            } else if (element.msRequestFullscreen) {
                element.msRequestFullscreen();
            }
        };

        const toggleMute = () => {
            const muteButton = document.getElementById('muteToggle');
            if (video.muted) {
                video.muted = false;
                muteButton.innerHTML = `<i class="fas fa-volume-up mr-2"></i> Mute`;
            } else {
                video.muted = true;
                muteButton.innerHTML = `<i class="fas fa-volume-mute mr-2"></i> Unmute`;
            }
        };

        const snapshotVideo = (id) => {
            const video = document.getElementById(id);
            const canvas = document.createElement('canvas');
            const modal = document.getElementById('snapshotModal');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const dataUrl = canvas.toDataURL('image/jpeg');
            
            document.getElementById('snapshotImage').src = dataUrl;
            document.getElementById('downloadSnapshot').href = dataUrl;
            
            modal.classList.remove('hidden');
        };
    </script>
</body>
</html>
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Peta GIS CCTV
        </h2>
    </x-slot>

    <div class="w-full h-[calc(100vh-4rem)] relative">
        <div id="map" class="w-full h-full z-0"></div>
    </div>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const cctvs = @json($cctvs);

        const map = L.map('map').setView([-6.325, 108.35], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        cctvs.forEach(cctv => {
            const marker = L.marker([cctv.latitude, cctv.longitude]).addTo(map);
            marker.bindPopup(`
                <strong>${cctv.name}</strong><br>
                <iframe src="${cctv.stream_url}" width="300" height="200" frameborder="0" allowfullscreen></iframe>
            `);
        });
    </script>
</x-app-layout>
