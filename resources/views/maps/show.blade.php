@extends('layouts.app-with-sidebar')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <div>
                    <h1 class="mb-1">
                        <i class="fas fa-camera me-2 text-primary"></i>
                        Detail Kamera CCTV: {{ $cctv->name ?? 'N/A' }}
                    </h1>
                    <p class="text-muted mb-0">Informasi dan statistik terkini untuk kamera ini.</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('maps.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Peta
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- CCTV Information and Status -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-video fa-4x mb-3 text-primary"></i>
                    <h5 class="card-title mb-2">{{ $cctv->name ?? 'N/A' }}</h5>
                    <p class="card-text text-muted mb-3">{{ $cctv->location ?? 'N/A' }}</p>
                    <span class="badge rounded-pill fs-6 py-2 px-4 bg-{{ $cctv->status == 'online' ? 'success' : ($cctv->status == 'offline' ? 'danger' : 'warning') }}">
                        {{ ucfirst($cctv->status) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white border-0 rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Teknis</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>IP Address:</strong>
                            <span>{{ $cctv->ip_address ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Platform:</strong>
                            <span>{{ $cctv->platform ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Resolusi:</strong>
                            <span>{{ $cctv->resolution ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Terakhir Dilihat:</strong>
                            <span>{{ $cctv->last_seen ?? 'N/A' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Stream and Map -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white border-0 rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Tayangan Langsung (Live Stream)</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-0" style="min-height: 400px;">
                    <!-- Placeholder untuk Live Stream -->
                    <div class="text-center p-5 bg-light w-100 h-100 rounded-bottom-4 d-flex align-items-center justify-content-center">
                        <div class="text-muted">
                            <i class="fas fa-play-circle fa-4x mb-3"></i>
                            <p class="h5">Live Stream (Placeholder)</p>
                            <p class="mb-0">Aplikasi streaming Anda akan muncul di sini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 d-flex flex-column">
                <div class="card-header bg-primary text-white border-0 rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Lokasi Kamera</h5>
                </div>
                <div class="card-body p-0 rounded-bottom-4 flex-grow-1 d-flex" style="min-height: 400px;">
                    <div id="cctv-map" class="rounded-bottom-4 w-100"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- CCTV Statistics -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white border-0 rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Statistik dan Kinerja</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <!-- Uptime Statistics -->
                        <div class="col-md-4 mb-3">
                            <div class="bg-light p-4 rounded-4 border">
                                <h4 class="text-primary mb-2">99.9%</h4>
                                <small class="text-muted">Uptime (30 hari terakhir)</small>
                            </div>
                        </div>
                        <!-- Disruption Count -->
                        <div class="col-md-4 mb-3">
                            <div class="bg-light p-4 rounded-4 border">
                                <h4 class="text-primary mb-2">5</h4>
                                <small class="text-muted">Jumlah Disrupsi (bulan ini)</small>
                            </div>
                        </div>
                        <!-- Average Latency -->
                        <div class="col-md-4 mb-3">
                            <div class="bg-light p-4 rounded-4 border">
                                <h4 class="text-primary mb-2">45ms</h4>
                                <small class="text-muted">Rata-rata Latency</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom styles from index.blade.php for consistency */
    .card-header,
    .btn-primary,
    .bg-primary {
        background: linear-gradient(135deg, #2f7dcb, #1e5a96) !important;
        color: white !important;
    }
    .btn-secondary {
        background: #6c757d;
        border: none;
        color: white;
    }
    .btn-secondary:hover {
        background: #5a6268;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #1e5a96, #2f7dcb) !important;
    }
    .badge.bg-success { background-color: #28a745 !important; }
    .badge.bg-danger { background-color: #dc3545 !important; }
    .badge.bg-warning { background-color: #ffc107 !important; }

    .list-group-item {
        border-color: #e9ecef;
    }
    .list-group-item strong {
        color: #333;
    }
    
    /* Map container styling - critical for Leaflet */
    #cctv-map {
        height: 100%;
        width: 100%;
        flex: 1;
        position: relative;
        border-radius: 0 0 1rem 1rem;
        background-color: #f0f0f0;
    }
    
    /* Ensure card takes proper height */
    .card {
        border-radius: 1rem;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .card-body.flex-grow-1 {
        flex-grow: 1;
        min-height: 400px;
    }
    
    /* Leaflet map fixes */
    .leaflet-container {
        background: #f0f0f0;
        border-radius: 0 0 1rem 1rem;
    }
    
    /* Responsive map */
    @media (max-width: 768px) {
        .card-body.flex-grow-1 {
            min-height: 300px;
        }
    }
</style>

@endsection

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('DOM loaded, initializing Leaflet map.');
            
            // Small timeout to ensure DOM is fully rendered
            setTimeout(function() {
                const mapContainer = document.getElementById('cctv-map');
                
                if (!mapContainer) {
                    console.error('Map container tidak ditemukan');
                    return;
                }
                
                const latitude = parseFloat('{{ $cctv->latitude }}');
                const longitude = parseFloat('{{ $cctv->longitude }}');

                console.log('Latitude yang diterima:', latitude);
                console.log('Longitude yang diterima:', longitude);
                console.log('Map container dimensions:', {
                    width: mapContainer.offsetWidth,
                    height: mapContainer.offsetHeight
                });

                if (isNaN(latitude) || isNaN(longitude)) {
                    console.error('Data latitude atau longitude tidak valid. Peta tidak dapat ditampilkan.');
                    mapContainer.innerHTML = `
                        <div class="d-flex align-items-center justify-content-center h-100 bg-light" style="border-radius: 0 0 1rem 1rem;">
                            <p class="text-muted"><i class="fas fa-exclamation-triangle me-2"></i>Data lokasi tidak tersedia.</p>
                        </div>
                    `;
                    return;
                }

                try {
                    // Inisialisasi peta Leaflet
                    const map = L.map('cctv-map', {
                        zoom: 16,
                        zoomControl: true
                    }).setView([latitude, longitude], 16);

                    console.log('Peta Leaflet berhasil diinisialisasi.');

                    // Tambahkan tile layer (lapisan peta dasar) dari OpenStreetMap
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(map);

                    // Buat marker dengan ikon kustom
                    const customIcon = L.icon({
                        iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });
                    
                    // Tambahkan marker ke peta
                    const marker = L.marker([latitude, longitude], {icon: customIcon}).addTo(map)
                        .bindPopup(`
                            <div style="font-family: sans-serif;">
                                <h6>{{ $cctv->name ?? 'CCTV' }}</h6>
                                <p><strong>Lokasi:</strong> {{ $cctv->location ?? 'N/A' }}</p>
                                <p><strong>Status:</strong> <span style="font-weight: bold; color: {{ $cctv->status == 'online' ? '#28a745' : ($cctv->status == 'offline' ? '#dc3545' : '#ffc107') }}">{{ ucfirst($cctv->status ?? 'unknown') }}</span></p>
                                <p><strong>IP:</strong> {{ $cctv->ip_address ?? 'N/A' }}</p>
                            </div>
                        `);

                    // Force map to resize and re-center
                    map.invalidateSize();
                    map.setView([latitude, longitude], 16);

                    console.log('Marker Leaflet berhasil ditambahkan.');
                    
                } catch (error) {
                    console.error('Terjadi kesalahan saat menginisialisasi peta atau marker Leaflet:', error);
                    mapContainer.innerHTML = `
                        <div class="d-flex align-items-center justify-content-center h-100 bg-light" style="border-radius: 0 0 1rem 1rem;">
                            <p class="text-danger"><i class="fas fa-exclamation-circle me-2"></i>Gagal memuat peta. Silakan coba muat ulang halaman.</p>
                        </div>
                    `;
                }
            }, 100);
        });
    </script>
@endsection
