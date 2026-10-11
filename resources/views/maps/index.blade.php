@extends('layouts.cctv')

@section('title', 'Peta CCTV - Kilang Pertamina RU VI Balongan')

@section('content')
<style>
    /* Styling for the map container */
    #map {
        height: calc(100vh - 200px);
        width: 100%;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: all 0.3s ease-in-out;
    }

    /* Stream loading overlay styling */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
        z-index: 10;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 10px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .stream-status-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 5;
        padding: 5px 10px;
        font-size: 12px;
    }

    /* Fullscreen map styling */
    #map.fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 100vw;
        z-index: 1050; /* A bit higher than modals */
        border-radius: 0;
        box-shadow: none;
    }

    /* Map control buttons */
    .map-controls {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 12px;
        padding: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .map-controls .btn-group-vertical .btn {
        border-radius: 8px;
        font-size: 0.85rem;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
    }

    .map-controls hr {
        border-top: 1px solid #e0e0e0;
        margin: 8px 0;
    }

    /* Custom marker icons */
    .custom-div-icon {
        background: transparent !important;
        border: none !important;
        cursor: grab;
    }

    .custom-div-icon i {
        font-size: 24px;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        transition: all 0.2s ease-in-out;
    }

    .custom-div-icon i:hover {
        transform: scale(1.2);
    }

    /* Colors for different statuses */
    .marker-online { color: #28a745; }
    .marker-offline { color: #dc3545; }

    /* Popup styling */
    .leaflet-popup-content-wrapper {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        padding: 0;
    }

    .leaflet-popup-content {
        margin: 0;
        padding: 15px;
        line-height: 1.6;
        font-size: 0.9rem;
    }

    .popup-header {
        font-weight: bold;
        color: #2f7dcb;
        margin-bottom: 8px;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .popup-info {
        font-size: 0.85rem;
        color: #555;
        margin-bottom: 10px;
    }

    .popup-actions {
        margin-top: 15px;
        text-align: center;
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    /* Marker cluster styling */
    .marker-cluster-small { background-color: rgba(47, 125, 203, 0.6); }
    .marker-cluster-small div { background-color: rgba(47, 125, 203, 0.95); }

    .marker-cluster-medium { background-color: rgba(255, 165, 0, 0.6); }
    .marker-cluster-medium div { background-color: rgba(255, 165, 0, 0.95); }

    .marker-cluster-large { background-color: rgba(220, 53, 69, 0.6); }
    .marker-cluster-large div { background-color: rgba(220, 53, 69, 0.95); }

    .marker-cluster-large,
    .marker-cluster-medium,
    .marker-cluster-small {
        background-clip: padding-box;
        border-radius: 20px;
    }

    .marker-cluster div {
        width: 40px;
        height: 40px;
        margin-left: 5px;
        margin-top: 5px;
        text-align: center;
        border-radius: 50%;
        font: 16px "Helvetica Neue", Arial, Helvetica, sans-serif;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(0,0,0,0.4);
        border: 2px solid white;
    }

    .marker-cluster span {
        font-weight: 700;
    }

    /* Legend styling */
    .legend {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        font-size: 14px;
        z-index: 999;
    }

    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-right: 10px;
        border: 2px solid white;
        box-shadow: 0 1px 5px rgba(0,0,0,0.3);
    }
    
    /* Stats cards styling */
    .map-stats {
        background: linear-gradient(135deg, #2f7dcb, #1e5a96);
        color: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        text-align: center;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: transform 0.2s ease-in-out;
    }

    .map-stats:hover {
        transform: translateY(-5px);
    }

    .map-stats h3 {
        font-size: 2.2rem;
        margin-bottom: 5px;
        font-weight: 700;
    }

    .map-stats small {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    /* Other custom card/list styling */
    .card-header-custom {
        background: linear-gradient(135deg, #2f7dcb, #1e5a96);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .list-group-item {
        border-left: 5px solid transparent;
        transition: all 0.2s ease-in-out;
        padding: 12px 15px;
    }

    .list-group-item.active {
        background-color: #e9f2fa;
        border-left-color: #2f7dcb;
    }

    .list-group-item:hover {
        background-color: #f5f9fc;
    }

    .list-group-item h6 {
        font-weight: 600;
        color: #333;
    }

    .list-group-item .text-muted {
        font-size: 0.8rem;
    }

    .list-group-item .badge {
        font-size: 0.75rem;
        padding: 0.4em 0.6em;
        border-radius: 5px;
    }


    .alert-custom {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .alert-success-custom {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .alert-danger-custom {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert-warning-custom {
        background-color: #fff3cd;
        border-color: #ffeeba;
        color: #856404;
    }

    .alert-info-custom {
        background: linear-gradient(135deg, rgba(209, 236, 241, 0.95) 0%, rgba(190, 229, 235, 0.95) 100%);
        border: 1px solid rgba(190, 229, 235, 0.3);
        color: #0c5460;
        backdrop-filter: blur(10px);
    }

    .form-label.fw-bold {
        color: #2f7dcb;
    }

    .form-control[readonly] {
        background-color: rgba(233, 236, 239, 0.8);
        opacity: 1;
        backdrop-filter: blur(5px);
    }

    #editCctvMap {
        height: 300px;
        width: 100%;
        border-radius: 12px;
        margin-bottom: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: 2px solid rgba(47, 125, 203, 0.3);
    }

    #editCctvMap:hover {
        box-shadow: 0 6px 20px rgba(47, 125, 203, 0.3);
        border-color: rgba(47, 125, 203, 0.6);
        transform: translateY(-2px);
    }

    /* Enhanced modal styling */
    .modal-content {
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(245, 247, 250, 0.98) 100%);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .modal-header-custom {
        background: linear-gradient(135deg, #2f7dcb, #1e5a96);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .modal-header-custom .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-header-custom .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }

    .modal-header-custom .btn-close-white:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        border-top: 1px solid rgba(47, 125, 203, 0.1);
        padding: 20px 25px;
    }

    /* Enhanced form controls */
    .form-control-lg {
        border-radius: 12px;
        border: 2px solid rgba(47, 125, 203, 0.2);
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        transition: all 0.3s ease;
        padding: 12px 16px;
        font-size: 1rem;
    }

    .form-control-lg:focus {
        border-color: #2f7dcb;
        box-shadow: 0 0 0 0.2rem rgba(47, 125, 203, 0.25);
        background: rgba(255, 255, 255, 0.95);
        transform: translateY(-2px);
    }

    .form-control-lg::placeholder {
        color: #6c757d;
        opacity: 0.7;
    }

    /* Enhanced buttons */
    .btn {
        border-radius: 12px;
        transition: all 0.3s ease;
        font-weight: 500;
        border: 2px solid transparent;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .btn-primary {
        background: linear-gradient(135deg, #2f7dcb, #1e5a96);
        border-color: #2f7dcb;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #1e5a96, #2f7dcb);
        border-color: #1e5a96;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        border-color: #28a745;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #1e7e34, #28a745);
        border-color: #1e7e34;
    }

    .btn-outline-primary {
        border: 2px solid #2f7dcb;
        color: #2f7dcb;
        background: transparent;
    }

    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #2f7dcb, #1e5a96);
        color: white;
        border-color: transparent;
    }

    .btn-outline-secondary {
        border: 2px solid #6c757d;
        color: #6c757d;
        background: transparent;
    }

    .btn-outline-secondary:hover {
        background: linear-gradient(135deg, #6c757d, #495057);
        color: white;
        border-color: transparent;
    }


    /* Loading animation */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .loading {
        animation: pulse 1.5s ease-in-out infinite;
    }

    /* Success/Error states */
    .is-valid {
        border-color: #28a745 !important;
        background: rgba(40, 167, 69, 0.05) !important;
    }

    .is-invalid {
        border-color: #dc3545 !important;
        background: rgba(220, 53, 69, 0.05) !important;
    }

    /* Responsive adjustments for modal */
    @media (max-width: 768px) {
        .modal-dialog.modal-lg {
            margin: 10px;
            width: auto;
        }
        
        .form-control-lg {
            padding: 10px 14px;
            font-size: 0.9rem;
        }
        
        #editCctvMap {
            height: 250px;
        }
        
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        #map {
            height: 50vh;
        }
        .map-controls {
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
            right: 50%;
            transform: translateX(50%);
            width: 90%;
            top: auto;
            bottom: 15px;
        }
        .map-controls .btn-group-vertical {
            flex-direction: row;
            width: 100%;
            justify-content: center;
        }
        .map-controls .btn-group-vertical .btn {
            flex-grow: 1;
            text-align: center;
            justify-content: center;
        }
        .map-stats {
            margin-bottom: 15px;
        }
        .col-md-3, .col-md-9 {
            width: 100%;
        }
    }
</style>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet Cluster CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" xintegrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <div>
        <h1 class="mb-1">
            <i class="fas fa-map-marked-alt me-2 text-primary"></i>
            Map System CCTV
        </h1>
        <p class="text-muted mb-0">Pertamina Patra Niaga Refinery Unit Balongan, Indramayu, Jawa Barat</p>
    </div>
    <div class="mt-3 mt-md-0">
        <a href="{{ route('cctvs.index') }}" class="btn btn-outline-primary me-2">
            <i class="fas fa-list"></i> Daftar CCTV
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </div>
</div>

<!-- Alert Messages Container -->
<div id="alertContainer" class="mb-4"></div>

<!-- Map Statistics -->
<div class="row mb-4">
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="map-stats">
            <h3>{{ $statistics['total'] }}</h3>
            <small>Total CCTV</small>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="map-stats" style="background: linear-gradient(135deg, #28a745, #1e7e34);">
            <h3>{{ $statistics['online'] }}</h3>
            <small>Online</small>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="map-stats" style="background: linear-gradient(135deg, #dc3545, #c82333);">
            <h3>{{ $statistics['offline'] }}</h3>
            <small>Offline</small>
        </div>
    </div>
</div>

<!-- Main Map Container -->
<div class="row">
    <div class="col-md-9">
        <div class="card border-0">
            <div class="card-header" style="background: linear-gradient(135deg, #2f7dcb, #1e5a96); color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-globe-asia me-2"></i> Interactive Map - Pertamina Patra Niaga Refinery Unit Balongan
                </h5>
            </div>
            <div class="card-body p-0 position-relative">
                <!-- Map Controls -->
                <div class="map-controls">
                    <div class="btn-group-vertical" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary active" onclick="toggleLayer('osm')" id="osmBtn">
                            <i class="fas fa-map"></i> OSM
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleLayer('satellite')" id="satelliteBtn">
                            <i class="fas fa-satellite"></i> Satelit
                        </button>
                    </div>
                    <hr class="my-2">
                    <div class="btn-group-vertical" role="group">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="zoomToFit()">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="refreshMarkers()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="debugMarkers()" title="Debug">
                            <i class="fas fa-bug"></i>
                        </button>
                    </div>
                    <!-- Fullscreen button -->
                    <hr class="my-2">
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="toggleFullscreen()">
                        <i class="fas fa-expand" id="fullscreenIcon"></i> Fullscreen
                    </button>
                </div>
                <!-- Map Element -->
                <div id="map"></div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-3">
        <!-- Legend -->
        <div class="legend mb-3">
            <h6 class="mb-3">
                <i class="fas fa-info-circle me-2"></i> Legend
            </h6>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #28a745;"></div>
                <span>Online ({{ $statistics['online'] }})</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #dc3545;"></div>
                <span>Offline ({{ $statistics['offline'] }})</span>
            </div>
            <hr>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #2f7dcb;"></div>
                <span>Cluster < 10</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ffa500;"></div>
                <span>Cluster 10-20</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #dc3545;"></div>
                <span>Cluster > 20</span>
            </div>
        </div>

        <!-- CCTV List -->
        <div class="card border-0">
            <div class="card-header-custom">
                <i class="fas fa-video me-2"></i>
                Daftar CCTV
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                    @foreach ($cctvs as $cctv)
                        <a href="{{ route('cctvs.show', $cctv->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $cctv->name }}</h6>
                                <p class="mb-1 text-muted">{{ $cctv->location }}</p>
                            </div>
                            <span class="badge rounded-pill bg-{{ $cctv->status == 'online' ? 'success' : ($cctv->status == 'offline' ? 'danger' : 'warning') }}">
                                {{ ucfirst($cctv->status) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for editing CCTV location -->
<div class="modal fade" id="editCctvModal" tabindex="-1" aria-labelledby="editCctvModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(245, 247, 250, 0.95) 100%); backdrop-filter: blur(20px);">
            <div class="modal-header-custom">
                <h5 class="modal-title" id="editCctvModalLabel">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    Edit Lokasi CCTV
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCctvForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div id="alertModalContainer"></div>
                    <input type="hidden" name="cctv_id" id="cctv_id">
                    
                    <!-- CCTV Info Section -->
                    <div class="cctv-info-card mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, rgba(47, 125, 203, 0.1) 0%, rgba(30, 90, 150, 0.1) 100%); border: 1px solid rgba(47, 125, 203, 0.2);">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-video text-primary me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-1 text-primary fw-bold" id="cctvNameDisplay">Nama CCTV</h6>
                                <small class="text-muted">ID: <span id="cctvIdDisplay">-</span></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Interactive Map Section -->
                    <div class="map-section mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="fas fa-map me-2"></i>Pilih Lokasi Baru
                            </h6>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="getCurrentLocation()" title="Gunakan lokasi GPS saat ini">
                                    <i class="fas fa-crosshairs"></i> GPS
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetToOriginalLocation()" title="Reset ke lokasi awal">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                        <div id="editCctvMap" class="rounded-3" style="height: 300px; border: 2px solid rgba(47, 125, 203, 0.3);"></div>
                        <div class="mt-2 text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Klik di peta untuk memilih lokasi atau gunakan tombol GPS/Reset
                            </small>
                        </div>
                    </div>
                    
                    <!-- Coordinates Input Section -->
                    <div class="coordinates-section mb-4">
                        <h6 class="mb-3 fw-bold text-primary">
                            <i class="fas fa-coordinates me-2"></i>Koordinat
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="modalLatitude" class="form-label fw-semibold">
                                        <i class="fas fa-latitude me-1 text-info"></i>Latitude
                                    </label>
                                    <input type="text" class="form-control form-control-lg" 
                                           id="modalLatitude" name="latitude" required
                                           placeholder="-6.305100" 
                                           oninput="updateMarkerFromInput()">
                                    <div class="form-text">Contoh: -6.305100</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="modalLongitude" class="form-label fw-semibold">
                                        <i class="fas fa-longitude me-1 text-info"></i>Longitude
                                    </label>
                                    <input type="text" class="form-control form-control-lg" 
                                           id="modalLongitude" name="longitude" required
                                           placeholder="108.332800"
                                           oninput="updateMarkerFromInput()">
                                    <div class="form-text">Contoh: 108.332800</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instructions -->
                    <div class="alert alert-info-custom rounded-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-lightbulb me-3 fs-4 text-warning"></i>
                            <div>
                                <h6 class="mb-1 fw-bold">Tips Penggunaan</h6>
                                <p class="mb-0 small">Anda dapat mengubah lokasi dengan: (1) Klik di peta, (2) Gunakan tombol GPS, (3) Input manual koordinat, atau (4) Reset ke lokasi awal</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-between p-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg" id="saveLocationBtn">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>Menyimpan Perubahan...</h5>
                <p class="text-muted mb-0">Mohon tunggu sebentar</p>
            </div>
        </div>
    </div>
</div>

<script>
// Inisialisasi Bootstrap Modal saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    window.loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
});

// previously a separate function; submission handled by form listener below
// keep window.loadingModal available for showing/hiding in the listener

function validateForm() {
    const form = document.getElementById('cctvForm');
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
}
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet Cluster JS -->
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<script>
    // Variable untuk menyimpan data CCTV dari backend
    let cctvData = @json($cctvs);
    let map;
    let osmLayer, satelliteLayer;
    let markers, editCctvMap, editMarker, mapClickListener;
    let fullscreenButton;

    // Fungsi untuk inisialisasi peta utama
    function initMap() {
        console.log('Inisialisasi peta utama...');

        // Inisialisasi peta dengan koordinat pusat Balongan
        map = L.map('map', {
            center: [-6.3051, 108.3328], // Koordinat Balongan, Indramayu
            zoom: 13,
            zoomControl: false // Hapus kontrol zoom default
        });

        // Tambahkan kontrol zoom kustom di sudut kanan bawah
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        // Tambahkan layer OpenStreetMap
        osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Tambahkan layer satelit dari Google
        satelliteLayer = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3'],
            attribution: 'Map data &copy; <a href="https://www.google.com/maps">Google Maps</a>'
        });

        // Inisialisasi grup marker dan cluster
        markers = L.markerClusterGroup();
        map.addLayer(markers);

        // Tambahkan marker ke peta
        addMarkers();
    }

    // Fungsi untuk menambahkan marker ke peta dari data CCTV
    function addMarkers() {
        // Hapus semua marker yang ada di cluster sebelum menambah yang baru
        markers.clearLayers();

        cctvData.forEach(cctv => {
            if (cctv.latitude && cctv.longitude) {
                const markerHtml = `
                    <div class="custom-div-icon">
                        <i class="fas fa-video marker-${cctv.status}"></i>
                    </div>
                `;

                const customIcon = L.divIcon({
                    html: markerHtml,
                    className: '',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30],
                    popupAnchor: [0, -30]
                });

                const marker = L.marker([cctv.latitude, cctv.longitude], { icon: customIcon });

                // Tentukan warna status untuk badge di popup
                const statusColor = cctv.status === 'online' ? 'success' : (cctv.status === 'offline' ? 'danger' : 'warning');
                
                // Konten popup yang telah dimodifikasi
                const popupContent = `
                    <div class="popup-container" style="min-width: 280px; max-width: 320px;">
                        <!-- Header dengan gradient background -->
                        <div class="popup-header" style="background: linear-gradient(135deg, #2f7dcb, #1e5a96); color: white; padding: 12px 15px; border-radius: 8px 8px 0 0; margin: -15px -15px 10px -15px;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-video me-2"></i>
                                <span class="fw-bold">${cctv.name}</span>
                            </div>
                        </div>
                        
                        <!-- Konten utama -->
                        <div class="popup-content">
                            <!-- Info card dengan icon -->
                            <div class="info-card mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    <span class="fw-semibold">Lokasi</span>
                                </div>
                                <p class="mb-1 text-muted small">${cctv.location || 'Lokasi belum ditentukan'}</p>
                            </div>
                            
                            <!-- Status dengan badge yang lebih menarik -->
                            <div class="info-card mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-signal text-success me-2"></i>
                                    <span class="fw-semibold">Status</span>
                                </div>
                                <span class="badge rounded-pill bg-${statusColor}" style="font-size: 0.75rem; padding: 0.4em 0.8em;">
                                    <i class="fas fa-circle me-1"></i>
                                    ${cctv.status.charAt(0).toUpperCase() + cctv.status.slice(1)}
                                </span>
                            </div>
                            
                            <!-- IP Address dengan icon -->
                            <div class="info-card mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-network-wired text-info me-2"></i>
                                    <span class="fw-semibold">IP Address</span>
                                </div>
                                <code class="text-muted small">${cctv.ip_address || 'N/A'}</code>
                            </div>
                            
                            <!-- Quick stats -->
                            <div class="stats-row mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="stat-item text-center p-2 bg-light rounded">
                                            <i class="fas fa-calendar text-primary"></i>
                                            <div class="small text-muted">Terdaftar</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-item text-center p-2 bg-light rounded">
                                            <i class="fas fa-clock text-warning"></i>
                                            <div class="small text-muted">Aktif</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action buttons dengan styling yang lebih baik -->
                    <div class="popup-actions" style="border-top: 1px solid #eee; padding-top: 12px; margin: 0 -15px -15px -15px; padding: 15px;">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-sm live-stream-btn" data-cctv-id="${cctv.id}" data-cctv-name="${cctv.name}">
                                <i class="fas fa-play-circle me-1"></i> 
                                <strong>LIVE STREAM</strong>
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="openEditModal(${cctv.id}, '${cctv.name}', ${cctv.latitude}, ${cctv.longitude})">
                                <i class="fas fa-edit me-1"></i> 
                                Edit Lokasi
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="window.location.href='/cctvs/${cctv.id}'">
                                <i class="fas fa-info-circle me-1"></i> 
                                Detail Info
                            </button>
                        </div>
                    </div>
                    </div>
                `;

                marker.bindPopup(popupContent, {
                    closeButton: false,
                    offset: L.point(0, -10)
                });

                markers.addLayer(marker);
            }
        });
    }

    // Fungsi untuk mengganti layer peta
    function toggleLayer(layerName) {
        if (layerName === 'osm') {
            if (map.hasLayer(satelliteLayer)) {
                map.removeLayer(satelliteLayer);
            }
            map.addLayer(osmLayer);
            document.getElementById('osmBtn').classList.add('active');
            document.getElementById('satelliteBtn').classList.remove('active');
        } else if (layerName === 'satellite') {
            if (map.hasLayer(osmLayer)) {
                map.removeLayer(osmLayer);
            }
            map.addLayer(satelliteLayer);
            document.getElementById('satelliteBtn').classList.add('active');
            document.getElementById('osmBtn').classList.remove('active');
        }
    }

    // Fungsi untuk memperbesar peta agar semua marker terlihat
    function zoomToFit() {
        if (cctvData.length > 0) {
            const group = new L.featureGroup(cctvData.filter(c => c.latitude && c.longitude).map(c => L.marker([c.latitude, c.longitude])));
            map.fitBounds(group.getBounds(), { padding: [50, 50] });
        }
    }

    // Fungsi untuk menyegarkan marker
    function refreshMarkers() {
        addMarkers();
        zoomToFit();
        showAlert('Marker berhasil diperbarui!', 'success');
    }

    // Fungsi untuk menampilkan alert
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        const alertHtml = `
            <div class="alert alert-${type}-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        alertContainer.innerHTML = alertHtml;
    }

    // Fungsi untuk membuka modal edit lokasi
    function openEditModal(cctvId, cctvName, lat, lng) {
        const modal = new bootstrap.Modal(document.getElementById('editCctvModal'));
        
        // Set values for the form
        const form = document.getElementById('editCctvForm');
        form.action = `/maps/update-location/${cctvId}`;
        console.log('openEditModal setting form action to', form.action);
        document.getElementById('cctv_id').value = cctvId;
        document.getElementById('cctvNameDisplay').textContent = cctvName;
        document.getElementById('cctvIdDisplay').textContent = cctvId;
        document.getElementById('modalLatitude').value = lat;
        document.getElementById('modalLongitude').value = lng;
        
        // Simpan nilai default untuk reset
        document.getElementById('modalLatitude').defaultValue = lat;
        document.getElementById('modalLongitude').defaultValue = lng;
        
        // Reset alert container
        document.getElementById('alertModalContainer').innerHTML = '';
        
        modal.show();

        // Tunggu modal muncul sepenuhnya sebelum inisialisasi peta
        document.getElementById('editCctvModal').addEventListener('shown.bs.modal', function() {
            initEditMap(lat, lng);
        }, { once: true });

        // Bersihkan event listener saat modal ditutup
        document.getElementById('editCctvModal').addEventListener('hidden.bs.modal', function() {
            if (editCctvMap) {
                editCctvMap.remove();
                editCctvMap = null;
            }
            editMarker = null;
            mapClickListener = null;
            
            // Remove input event listeners
            const latInput = document.getElementById('modalLatitude');
            const lngInput = document.getElementById('modalLongitude');
            if (latInput && lngInput) {
                latInput.removeEventListener('input', updateMarkerFromInput);
                lngInput.removeEventListener('input', updateMarkerFromInput);
            }
        }, { once: true });
    }

    // Inisialisasi peta di dalam modal edit
    function initEditMap(lat, lng) {
        // Hapus peta sebelumnya jika ada
        if (editCctvMap) {
            editCctvMap.remove();
        }

        editCctvMap = L.map('editCctvMap').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(editCctvMap);

        editMarker = L.marker([lat, lng], { draggable: true }).addTo(editCctvMap);
        editMarker.on('dragend', function(e) {
            const newLat = e.target.getLatLng().lat.toFixed(6);
            const newLng = e.target.getLatLng().lng.toFixed(6);
            document.getElementById('modalLatitude').value = newLat;
            document.getElementById('modalLongitude').value = newLng;
        });

        // Tambahkan event listener klik di peta
        mapClickListener = editCctvMap.on('click', function(e) {
            const newLat = e.latlng.lat.toFixed(6);
            const newLng = e.latlng.lng.toFixed(6);
            document.getElementById('modalLatitude').value = newLat;
            document.getElementById('modalLongitude').value = newLng;
            editMarker.setLatLng(e.latlng); // Pindahkan marker ke lokasi klik
        });

        // Event listener untuk input manual
        document.getElementById('modalLatitude').addEventListener('input', updateMarkerFromInput);
        document.getElementById('modalLongitude').addEventListener('input', updateMarkerFromInput);

        // Invalidasi ukuran peta untuk memastikan tampil dengan benar di modal
        setTimeout(() => {
            editCctvMap.invalidateSize();
        }, 100);
    }

    // Fungsi untuk update marker dari input manual
    function updateMarkerFromInput() {
        const lat = parseFloat(document.getElementById('modalLatitude').value);
        const lng = parseFloat(document.getElementById('modalLongitude').value);
        
        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            const newLatLng = [lat, lng];
            if (editMarker) {
                editMarker.setLatLng(newLatLng);
            }
            if (editCctvMap) {
                editCctvMap.setView(newLatLng, 16);
            }
        }
    }

    // Fungsi untuk mendapatkan lokasi GPS saat ini
    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude.toFixed(6);
                const lng = position.coords.longitude.toFixed(6);
                document.getElementById('modalLatitude').value = lat;
                document.getElementById('modalLongitude').value = lng;
                updateMarkerFromInput();
            }, function(error) {
                alert('Tidak bisa mendapatkan lokasi GPS: ' + error.message);
            });
        } else {
            alert('Browser tidak mendukung geolocation');
        }
    }

    // Fungsi untuk reset ke lokasi awal
    function resetToOriginalLocation() {
        const originalLat = parseFloat(document.getElementById('modalLatitude').defaultValue || -6.3051);
        const originalLng = parseFloat(document.getElementById('modalLongitude').defaultValue || 108.3328);
        
        document.getElementById('modalLatitude').value = originalLat;
        document.getElementById('modalLongitude').value = originalLng;
        updateMarkerFromInput();
    }

    // Fungsi untuk menangani fullscreen
    function toggleFullscreen() {
        const mapElement = document.getElementById('map');
        const fullscreenIcon = document.getElementById('fullscreenIcon');
        if (!document.fullscreenElement) {
            mapElement.requestFullscreen().catch(err => {
                alert(`Error saat mencoba mode fullscreen: ${err.message}`);
            });
            fullscreenIcon.className = 'fas fa-compress';
        } else {
            document.exitFullscreen();
            fullscreenIcon.className = 'fas fa-expand';
        }

        // Memperbarui ukuran peta setelah mode fullscreen
        setTimeout(() => {
            map.invalidateSize();
        }, 300);
    }
    
    // Listen for fullscreen change events to update icon and size
    document.addEventListener('fullscreenchange', function () {
        const fullscreenIcon = document.getElementById('fullscreenIcon');
        if (document.fullscreenElement) {
            fullscreenIcon.className = 'fas fa-compress';
            document.getElementById('map').classList.add('fullscreen');
        } else {
            fullscreenIcon.className = 'fas fa-expand';
            document.getElementById('map').classList.remove('fullscreen');
        }
        setTimeout(() => {
            map.invalidateSize();
        }, 300);
    });

    // ==============================================
    // MODAL ALERT FUNCTIONS
    // ==============================================
    
    /**
     * Menampilkan alert di dalam modal edit lokasi
     * @param {string} message - Pesan yang akan ditampilkan
     * @param {string} type - Tipe alert ('info', 'success', 'warning', 'error')
     */
    function showModalAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertModalContainer');
        const alertClass = type === 'error' ? 'alert-danger-custom' : 
                          type === 'success' ? 'alert-success-custom' : 
                          type === 'warning' ? 'alert-warning-custom' : 'alert-info-custom';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show mb-3" role="alert">
                <i class="fas ${type === 'error' ? 'fa-exclamation-triangle' : 
                               type === 'success' ? 'fa-check-circle' : 
                               type === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        alertContainer.innerHTML = alertHtml;
        
        // Auto-dismiss after 5 seconds for success/info messages
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }
            }, 5000);
        }
    }

    // ==============================================
    // VALIDATION FUNCTIONS
    // ==============================================
    
    /**
     * Validasi format dan range koordinat latitude/longitude
     * @param {string} lat - Nilai latitude
     * @param {string} lng - Nilai longitude
     * @returns {Object} Objek hasil validasi
     */
    function validateCoordinates(lat, lng) {
        const errors = [];
        
        // Validasi input tidak kosong
        if (!lat || !lng) {
            errors.push('Latitude dan longitude harus diisi');
            return { isValid: false, errors };
        }
        
        // Validasi format angka
        const numLat = parseFloat(lat);
        const numLng = parseFloat(lng);
        
        if (isNaN(numLat) || isNaN(numLng)) {
            errors.push('Latitude dan longitude harus berupa angka');
            return { isValid: false, errors };
        }
        
        // Validasi range latitude (-90 to 90)
        if (numLat < -90 || numLat > 90) {
            errors.push('Latitude harus antara -90 dan 90');
        }
        
        // Validasi range longitude (-180 to 180)
        if (numLng < -180 || numLng > 180) {
            errors.push('Longitude harus antara -180 dan 180');
        }
        
        return {
            isValid: errors.length === 0,
            errors: errors.length > 0 ? errors : null,
            latitude: numLat,
            longitude: numLng
        };
    }

    /**
     * Update tampilan visual validasi pada input fields
     * @param {HTMLElement} latInput - Input field latitude
     * @param {HTMLElement} lngInput - Input field longitude
     * @param {boolean} isValid - Status validasi
     */
    function updateInputValidation(latInput, lngInput, isValid) {
        // Reset kelas validasi sebelumnya
        latInput.classList.remove('is-valid', 'is-invalid');
        lngInput.classList.remove('is-valid', 'is-invalid');
        
        // Terapkan kelas validasi sesuai status
        if (isValid) {
            latInput.classList.add('is-valid');
            lngInput.classList.add('is-valid');
        } else {
            latInput.classList.add('is-invalid');
            lngInput.classList.add('is-invalid');
        }
    }

    // Menangani pengiriman form edit lokasi via AJAX
    document.getElementById('editCctvForm').addEventListener('submit', function(event) {
        event.preventDefault();
        if(window.loadingModal){ window.loadingModal.show(); }
        const saveLocationBtn = document.getElementById('saveLocationBtn');
        if(saveLocationBtn){
            saveLocationBtn.disabled = true;
            saveLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';
        }

        const cctvId = document.getElementById('cctv_id').value;
        const newLatitude = document.getElementById('modalLatitude').value.trim();
        const newLongitude = document.getElementById('modalLongitude').value.trim();

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        console.log('csrfToken for map submission:', csrfToken);

        fetch(`/maps/update-location/${cctvId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ latitude: newLatitude, longitude: newLongitude })
        })
        .then(resp => {
            console.log('map update status', resp.status);
            return resp.json();
        })
        .then(result => {
            console.log('map update response', result);
            if(result.success) {
                showModalAlert('✅ Lokasi CCTV berhasil diperbarui!', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("cctvs.index") }}';
                }, 1000);
            } else {
                const errorMessage = result.message || (result.errors ? JSON.stringify(result.errors) : 'Gagal memperbarui lokasi CCTV.');
                showModalAlert(`❌ ${errorMessage}`, 'error');
                if(saveLocationBtn){
                    saveLocationBtn.disabled = false;
                    saveLocationBtn.innerHTML = 'Simpan Perubahan';
                }
                if(window.loadingModal){ window.loadingModal.hide(); }
            }
        })
        .catch(err => {
            console.error('map update error', err);
            if(saveLocationBtn){
                saveLocationBtn.disabled = false;
                saveLocationBtn.innerHTML = 'Simpan Perubahan';
            }
            if(window.loadingModal){ window.loadingModal.hide(); }
        });
    });

    // Initialize map when page loads
    // Initialize map when page loads, only one DOMContentLoaded listener is needed
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing map...');
        
        // Handle Pusher error if Pusher is loaded but not configured
        if (typeof Pusher !== 'undefined' && !window.PusherConfig) {
            console.warn('Pusher loaded but not configured. Disabling Pusher functionality.');
            // You can optionally disable Pusher-related code here
        }
        
        initMap();
        
        // Add event listener for live stream buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.live-stream-btn')) {
                const button = e.target.closest('.live-stream-btn');
                const cctvId = button.getAttribute('data-cctv-id');
                const cctvName = button.getAttribute('data-cctv-name');
                openLiveStream(cctvId, cctvName);
            }
        });
    });

    // Global HLS instance
    let streamHlsInstance = null;

    // Function to open live stream for a specific CCTV
    function openLiveStream(cctvId, cctvName) {
        // Set CCTV ID and name for the modal
        document.getElementById('streamCctvId').value = cctvId;
        document.getElementById('streamCctvName').textContent = cctvName;
        
        // Bersihkan alert error sebelumnya
        const modalBody = document.querySelector('#streamModal .modal-body');
        const existingAlert = modalBody.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Show the stream modal (no backdrop so user can still interact with map)
        const streamModalEl = document.getElementById('streamModal');
        const streamModal = bootstrap.Modal.getOrCreateInstance(streamModalEl, { backdrop: false });
        
        // After modal opens, remove Bootstrap's scroll lock so page remains scrollable
        streamModalEl.addEventListener('shown.bs.modal', function onModalShown() {
            streamModalEl.removeEventListener('shown.bs.modal', onModalShown);
            document.body.classList.remove('modal-open');
            document.body.style.overflow = 'auto';
            initializeStreamPlayer(cctvId);
        });

        streamModalEl.addEventListener('hidden.bs.modal', function onModalHidden() {
            streamModalEl.removeEventListener('hidden.bs.modal', onModalHidden);
            // restore any stylistic changes when modal closes
            document.body.style.overflow = '';
        });

        streamModal.show();
    }

    // Function to initialize the stream player
    function initializeStreamPlayer(cctvId) {
        const videoPlayer = document.getElementById('streamVideoPlayer');
        const loadingOverlay = document.getElementById('streamLoadingOverlay');
        const streamLiveBadge = document.getElementById('streamLiveBadge');
        
        // Destroy existing HLS instance if any
        if (streamHlsInstance) {
            streamHlsInstance.destroy();
            streamHlsInstance = null;
        }

        // Show loading state
        loadingOverlay.style.display = 'flex';
        streamLiveBadge.style.display = 'none';
        
        // Cari CCTV berdasarkan ID untuk mendapatkan slug
        const cctv = cctvData.find(c => c.id == cctvId);
        const streamUrl = cctv ? `/stream/${cctv.slug || cctvId}/playlist.m3u8` : `/stream/${cctvId}/playlist.m3u8`;
        
        // Initialize HLS.js for the video player
        if (Hls.isSupported()) {
            try {
                streamHlsInstance = new Hls({
                    debug: false,
                    enableWorker: true,
                    lowLatencyMode: true,
                    backBufferLength: 90
                });
                
                streamHlsInstance.loadSource(streamUrl);
                streamHlsInstance.attachMedia(videoPlayer);

                streamHlsInstance.on(Hls.Events.MANIFEST_PARSED, function() {
                    videoPlayer.play().catch(e => {
                        console.log('Autoplay prevented:', e);
                        // User interaction might be required for autoplay
                    });
                    loadingOverlay.style.display = 'none';
                    streamLiveBadge.style.display = 'block';
                });

                streamHlsInstance.on(Hls.Events.ERROR, function(event, data) {
                    console.error('HLS Error:', data);
                    loadingOverlay.style.display = 'none';
                    streamLiveBadge.style.display = 'none';
                    
                    // Tampilkan pesan error yang lebih informatif
                    let errorMessage = 'Gagal memuat stream. ';
                    
                    if (data.details === 'manifestLoadError') {
                        errorMessage += 'File playlist tidak ditemukan. Pastikan CCTV sedang online dan streaming server berjalan.';
                    } else if (data.details === 'manifestParsingError') {
                        errorMessage += 'Format playlist tidak valid.';
                    } else if (data.details === 'levelLoadError') {
                        errorMessage += 'Gagal memuat level stream.';
                    } else if (data.details === 'fragLoadError') {
                        errorMessage += 'Gagal memuat segment video.';
                    } else if (data.type === 'networkError') {
                        errorMessage += 'Koneksi jaringan bermasalah. Periksa koneksi internet Anda.';
                    } else {
                        errorMessage += 'Pastikan CCTV sedang online dan streaming server berjalan.';
                    }
                    
                    // Tampilkan alert dengan pesan error
                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 10px;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${errorMessage}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    
                    // Tambahkan alert di dalam modal
                    const modalBody = document.querySelector('#streamModal .modal-body');
                    const existingAlert = modalBody.querySelector('.alert');
                    if (existingAlert) {
                        existingAlert.remove();
                    }
                    modalBody.insertAdjacentHTML('afterbegin', alertHtml);
                });
            } catch (error) {
                console.error('HLS initialization error:', error);
                fallbackToNativePlayback(streamUrl, videoPlayer, loadingOverlay, streamLiveBadge);
            }
        } else {
            // Fallback to native HTML5 video playback
            fallbackToNativePlayback(streamUrl, videoPlayer, loadingOverlay, streamLiveBadge);
        }
    }

    // Fallback function for native HTML5 video playback
    function fallbackToNativePlayback(streamUrl, videoPlayer, loadingOverlay, streamLiveBadge) {
        console.log('Using native HTML5 video playback fallback');
        
        // Try to play directly with the stream URL
        videoPlayer.src = streamUrl;
        
        videoPlayer.addEventListener('loadeddata', function() {
            videoPlayer.play().catch(e => {
                console.log('Native playback autoplay prevented:', e);
            });
            loadingOverlay.style.display = 'none';
            streamLiveBadge.style.display = 'block';
        });
        
        videoPlayer.addEventListener('error', function(e) {
            console.error('Native video playback error:', e);
            loadingOverlay.style.display = 'none';
            streamLiveBadge.style.display = 'none';
            
            const errorMessage = 'Gagal memuat stream menggunakan native playback. Pastikan CCTV sedang online dan format video didukung.';
            
            const alertHtml = `
                <div class="alert alert-warning alert-dismissible fade show" role="alert" style="margin: 10px;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${errorMessage}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const modalBody = document.querySelector('#streamModal .modal-body');
            const existingAlert = modalBody.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            modalBody.insertAdjacentHTML('afterbegin', alertHtml);
        });
        
        // Set timeout in case the video doesn't load
        setTimeout(() => {
            if (loadingOverlay.style.display !== 'none') {
                loadingOverlay.style.display = 'none';
                const alertHtml = `
                    <div class="alert alert-info alert-dismissible fade show" role="alert" style="margin: 10px;">
                        <i class="fas fa-info-circle me-2"></i>
                        Stream sedang dimuat. Jika tidak muncul, pastikan CCTV online.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                const modalBody = document.querySelector('#streamModal .modal-body');
                const existingAlert = modalBody.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.remove();
                }
                modalBody.insertAdjacentHTML('afterbegin', alertHtml);
            }
        }, 10000); // 10 second timeout
    }

    // Debug function to log all CCTV data
    function debugMarkers() {
        console.log('Data CCTV:', cctvData);
        alert('Data CCTV telah dicetak ke konsol browser (F12 > Console).');
    }

    // Fungsi untuk menampilkan detail CCTV dalam modal
    function showCctvDetails(cctvId) {
        const cctv = cctvData.find(c => c.id == cctvId);
        if (!cctv) return;

        // Buat konten detail yang lebih lengkap
        const detailContent = `
            <div class="detail-container">
                <h5 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Detail Informasi CCTV</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><td><strong>Nama:</strong></td><td>${cctv.name}</td></tr>
                            <tr><td><strong>Lokasi:</strong></td><td>${cctv.location || 'N/A'}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge bg-${cctv.status === 'online' ? 'success' : (cctv.status === 'offline' ? 'danger' : 'warning')}">${cctv.status}</span></td></tr>
                            <tr><td><strong>IP:</strong></td><td>${cctv.ip_address || 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><td><strong>Latitude:</strong></td><td>${cctv.latitude || 'N/A'}</td></tr>
                            <tr><td><strong>Longitude:</strong></td><td>${cctv.longitude || 'N/A'}</td></tr>
                            <tr><td><strong>Terdaftar:</strong></td><td>${cctv.created_at ? new Date(cctv.created_at).toLocaleDateString('id-ID') : 'N/A'}</td></tr>
                            <tr><td><strong>Update:</strong></td><td>${cctv.updated_at ? new Date(cctv.updated_at).toLocaleDateString('id-ID') : 'N/A'}</td></tr>
                        </table>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="/cctvs/${cctv.id}/show" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Lihat Live Stream
                    </a>
                </div>
            </div>
        `;

        // Tampilkan dalam modal sederhana
        const modal = document.createElement('div');
        modal.innerHTML = `
            <div class="modal fade" id="cctvDetailModal" tabindex="-1" data-bs-backdrop="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #2f7dcb, #1e5a96); color: white;">
                            <h5 class="modal-title">
                                <i class="fas fa-video me-2"></i>Detail CCTV: ${cctv.name}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${detailContent}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal.querySelector('.modal'));
        bsModal.show();

        // Hapus modal dari DOM setelah ditutup
        modal.querySelector('.modal').addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }


</script>

<!-- Stream Modal -->
<div class="modal fade" id="streamModal" tabindex="-1" aria-labelledby="streamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="streamModalLabel">
                    <i class="fas fa-video me-2"></i>
                    Live Stream - <span id="streamCctvName">CCTV</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="stream-video-container" style="position: relative; width: 100%; height: 500px;">
                    <video id="streamVideoPlayer" class="w-100 h-100" controls playsinline style="background: #000;"></video>
                    <div class="loading-overlay" id="streamLoadingOverlay">
                        <div class="loading-spinner"></div>
                        <div>Memuat stream...</div>
                    </div>
                    <div id="streamLiveBadge" class="stream-status-badge badge bg-success shadow-sm">
                        <i class="fas fa-broadcast-tower me-1"></i> Live
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="streamCctvId">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i> Tutup
                </button>
                <button type="button" class="btn btn-primary" onclick="refreshStream()">
                    <i class="fas fa-sync-alt me-2"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include HLS.js library -->
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<script>
    // Function to refresh the stream
    function refreshStream() {
        const cctvId = document.getElementById('streamCctvId').value;
        if (cctvId) {
            initializeStreamPlayer(cctvId);
        }
    }

    // Clean up when modal is closed
    document.getElementById('streamModal').addEventListener('hidden.bs.modal', function() {
        const videoPlayer = document.getElementById('streamVideoPlayer');
        if (videoPlayer) {
            videoPlayer.pause();
            videoPlayer.src = '';
        }
        
        // Destroy HLS instance if exists
        if (typeof streamHlsInstance !== 'undefined' && streamHlsInstance) {
            streamHlsInstance.destroy();
            streamHlsInstance = null;
        }

        // Bersihkan alert error
        const modalBody = document.querySelector('#streamModal .modal-body');
        const existingAlert = modalBody.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Reset loading state
        const loadingOverlay = document.getElementById('streamLoadingOverlay');
        const streamLiveBadge = document.getElementById('streamLiveBadge');
        if (loadingOverlay) loadingOverlay.style.display = 'flex';
        if (streamLiveBadge) streamLiveBadge.style.display = 'none';
    });
</script>
@endsection
