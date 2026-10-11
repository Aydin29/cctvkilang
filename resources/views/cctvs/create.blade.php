@extends('layouts.cctv')

@section('title', 'Tambah Kamera CCTV - Statistik System Control CCTV RU VI BALONGAN')

@section('content')
<div class="container-fluid py-4">

    <div class="row justify-content-center mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Tambah Kamera CCTV Baru
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('cctvs.store') }}" method="POST" id="cctvForm">
                        @csrf
                        
                        <div class="row">
                            <!-- Informasi Dasar -->
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3 text-primary">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Informasi Dasar Kamera
                                        </h6>

                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nama Kamera <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name') }}" required
                                                   placeholder="Contoh: Kamera Pintu Masuk Utama">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="location" class="form-label">Lokasi <span class="text-danger">*</span></label>
                                            <select class="form-select @error('location') is-invalid @enderror" 
                                                    id="location" name="location" required>
                                                <option value="">-- Pilih Lokasi --</option>
                                                <option value="Kilang" {{ old('location') == 'Kilang' ? 'selected' : '' }}>Kilang</option>
                                                <option value="Bumi Patra" {{ old('location') == 'Bumi Patra' ? 'selected' : '' }}>Bumi Patra</option>
                                            </select>
                                            @error('location')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="ip_address" class="form-label">IP Address <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('ip_address') is-invalid @enderror" 
                                                   id="ip_address" name="ip_address" value="{{ old('ip_address') }}" required
                                                   placeholder="Contoh: 192.168.1.100">
                                            @error('ip_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Status Awal <span class="text-danger">*</span></label>
                                            <div class="d-flex align-items-center gap-2">
                                                @php
                                                    $initialStatus = old('status');
                                                    $badgeClass = 'bg-secondary';
                                                    $badgeLabel = 'Belum dites';
                                                    if ($initialStatus === 'online') {
                                                        $badgeClass = 'bg-success';
                                                        $badgeLabel = 'Online';
                                                    } elseif ($initialStatus === 'offline') {
                                                        $badgeClass = 'bg-danger';
                                                        $badgeLabel = 'Offline';
                                                    }
                                                @endphp
                                                <span id="status_display" class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                                <small class="text-muted">Status akan ditetapkan setelah test koneksi.</small>
                                            </div>
                                            <input type="hidden" id="status" name="status" value="{{ old('status') }}">
                                            @error('status')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Konfigurasi RTSP -->
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3 text-primary">
                                            <i class="fas fa-cog me-2"></i>
                                            Konfigurasi RTSP Stream
                                        </h6>

                                        <div class="mb-3">
                                            <label for="camera_brand" class="form-label">Brand Kamera <span class="text-danger">*</span></label>
                                            <select class="form-select @error('camera_brand') is-invalid @enderror" 
                                                    id="camera_brand" name="camera_brand" required onchange="updateRtspPath()">
                                                <option value="">-- Pilih Brand --</option>
                                                @foreach($cameraBrands as $brand => $paths)
                                                    <option value="{{ $brand }}" {{ old('camera_brand') == $brand ? 'selected' : '' }}>
                                                        {{ ucfirst($brand) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('camera_brand')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div id="brandInfo" class="alert alert-info" style="display: none;">
                                            <div id="brandDetails"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="rtsp_port" class="form-label">Port RTSP <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('rtsp_port') is-invalid @enderror" 
                                                       id="rtsp_port" name="rtsp_port" value="{{ old('rtsp_port', 554) }}" required>
                                                @error('rtsp_port')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="rtsp_path" class="form-label">Path RTSP <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('rtsp_path') is-invalid @enderror" 
                                                       id="rtsp_path" name="rtsp_path" value="{{ old('rtsp_path') }}" required>
                                                @error('rtsp_path')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="rtsp_username" class="form-label">Username RTSP</label>
                                                <input type="text" class="form-control @error('rtsp_username') is-invalid @enderror" 
                                                       id="rtsp_username" name="rtsp_username" value="{{ old('rtsp_username') }}">
                                                @error('rtsp_username')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="rtsp_password" class="form-label">Password RTSP</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control @error('rtsp_password') is-invalid @enderror" 
                                                           id="rtsp_password" name="rtsp_password" value="{{ old('rtsp_password') }}">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                                        <i id="toggleIcon" class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                @error('rtsp_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="alert alert-secondary mt-3">
                                            <small class="d-block mb-2"><strong>Preview RTSP URL:</strong></small>
                                            <code id="rtsp_preview" class="d-block">rtsp://[username]:[password]@[ip]:[port][path]</code>
                                            <div class="mt-2">
                                                <span id="rtsp_status" class="badge bg-secondary">Status: Belum dites</span>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-info mb-3" onclick="testConnection()" id="testBtn">
                                            <i class="fas fa-plug me-2"></i>
                                            Test Koneksi
                                        </button>
                                    </div>
                                </div>

                                <!-- Koordinat Lokasi -->
                                <div class="card bg-light mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3 text-primary">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            Koordinat Lokasi
                                        </h6>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="latitude" class="form-label">Latitude</label>
                                                <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror" 
                                                       id="latitude" name="latitude" value="{{ old('latitude') }}"
                                                       placeholder="-6.3051">
                                                @error('latitude')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="longitude" class="form-label">Longitude</label>
                                                <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror" 
                                                       id="longitude" name="longitude" value="{{ old('longitude') }}"
                                                       placeholder="108.3328">
                                                @error('longitude')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="btn-group w-100">
                                            <button type="button" class="btn btn-outline-success" onclick="getCurrentLocationCreate()">
                                                <i class="fas fa-crosshairs me-2"></i>
                                                Gunakan Lokasi Saat Ini
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="openMapPicker()">
                                                <i class="fas fa-map-marked-alt me-2"></i>
                                                Pilih di Peta
                                            </button>
                                        </div>

                                        <div id="coordinatePreview" class="alert alert-success mt-3" style="display: none;">
                                            <span id="coordinate_preview"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('cctvs.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-arrow-left me-2"></i>
                                Kembali ke Daftar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" onclick="submitForm(event)">
                                <i class="fas fa-save me-2"></i>
                                Simpan & Auto Convert ke M3U8
                            </button>
                        </div>
                    </form>
                </div>
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
                    <h5>Menyimpan & Mengkonversi Stream...</h5>
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

        // Validasi IP Address
        const ipField = form.querySelector('#ip_address');
        if (ipField.value) {
            const ipPattern = /^(\d{1,3}\.){3}\d{1,3}$/;
            if (!ipPattern.test(ipField.value)) {
                ipField.classList.add('is-invalid');
                isValid = false;
            }
        }

        return isValid;
    }

    function submitForm(e) {
        e.preventDefault();
        
        const form = document.getElementById('cctvForm');
        const submitBtn = document.getElementById('submitBtn');
        
        // Validate form
        if (!validateForm()) {
            alert('Mohon lengkapi semua field yang wajib diisi dengan benar');
            return;
        }
        
        // Log form data untuk debugging
        console.log('Form data:', new FormData(form));
        
        // Disable tombol submit
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        }
        
        // Tampilkan loading modal
        if (window.loadingModal) {
            window.loadingModal.show();
        }
        
        // Submit form
        form.submit();
    }

    </script>
</div>
@endsection

@section('scripts')
<script>
const cameraBrands = @json($cameraBrands);

// Brand-specific information
const brandInfo = {
    'hikvision': {
        description: 'Hikvision adalah brand kamera CCTV terkemuka dengan kualitas tinggi.',
        defaultCredentials: 'Username: admin, Password: [sesuai setting kamera]',
        commonPorts: '554 (RTSP), 80 (HTTP), 8000 (Web Interface)',
        tips: 'Pastikan RTSP service sudah diaktifkan di pengaturan kamera.'
    },
    'dahua': {
        description: 'Dahua Technology menyediakan solusi keamanan video yang komprehensif.',
        defaultCredentials: 'Username: admin, Password: [sesuai setting kamera]',
        commonPorts: '554 (RTSP), 80 (HTTP), 37777 (TCP)',
        tips: 'Gunakan Dahua Config Tool untuk konfigurasi awal kamera.'
    },
    'axis': {
        description: 'Axis Communications adalah pioneer dalam network video solutions.',
        defaultCredentials: 'Username: root, Password: [sesuai setting kamera]',
        commonPorts: '554 (RTSP), 80 (HTTP), 443 (HTTPS)',
        tips: 'Axis kamera biasanya menggunakan username "root" sebagai default.'
    },
    'foscam': {
        description: 'Foscam menyediakan kamera IP yang mudah digunakan dan terjangkau.',
        defaultCredentials: 'Username: admin, Password: [kosong atau sesuai setting]',
        commonPorts: '554 (RTSP), 80 (HTTP), 88 (Alternative HTTP)',
        tips: 'Beberapa model Foscam menggunakan port 88 untuk web interface.'
    },
    'honeywell': {
        description: 'Honeywell Security menyediakan solusi keamanan enterprise-grade.',
        defaultCredentials: 'Username: admin, Password: [sesuai setting kamera]',
        commonPorts: '554 (RTSP), 80 (HTTP), 8080 (Alternative HTTP)',
        tips: 'Honeywell kamera sering menggunakan path /live1s1.sdp untuk RTSP.'
    },
    'generic': {
        description: 'Konfigurasi umum untuk berbagai brand kamera IP.',
        defaultCredentials: 'Username: admin, Password: [sesuai setting kamera]',
        commonPorts: '554 (RTSP), 80 (HTTP)',
        tips: 'Coba berbagai kombinasi path seperti /stream1, /live, atau /video.'
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cctvForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form) {
        form.addEventListener('submit', function(e) {
            // Debug: Log form data
            console.log('Form data:', new FormData(form));
            
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            if (loadingModal) {
                loadingModal.show();
            }
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            }
        });
    }

    // Setup event listeners for RTSP preview
    const inputs = ['ip_address', 'rtsp_port', 'rtsp_path', 'rtsp_username', 'rtsp_password'];
    inputs.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updateRtspPreview);
        }
    });

    // Initial update
    updateRtspPreview();
});

// Update RTSP URL preview
function updateRtspPreview() {
    const preview = document.getElementById('rtsp_preview');
    if (!preview) return;

    const ip = document.getElementById('ip_address')?.value || '[ip]';
    const port = document.getElementById('rtsp_port')?.value || '[port]';
    const path = document.getElementById('rtsp_path')?.value || '[path]';
    const username = document.getElementById('rtsp_username')?.value;
    const password = document.getElementById('rtsp_password')?.value;

    let rtspUrl = 'rtsp://';
    if (username && password) {
        rtspUrl += `${username}:${password}@`;
    }
    rtspUrl += `${ip}:${port}${path}`;

    preview.textContent = rtspUrl;
}

function setRtspStatus(label, style) {
    const statusBadge = document.getElementById('rtsp_status');
    if (!statusBadge) return;
    statusBadge.className = `badge bg-${style}`;
    statusBadge.textContent = `Status: ${label}`;
}

function setStatusAwal(label, style) {
    const statusDisplay = document.getElementById('status_display');
    const statusInput = document.getElementById('status');
    if (statusDisplay) {
        statusDisplay.className = `badge ${style}`;
        statusDisplay.textContent = label;
    }
    if (statusInput) {
        const normalized = label.toLowerCase();
        if (normalized === 'online' || normalized === 'offline') {
            statusInput.value = normalized;
        }
    }
}

function resetTestButton(button, originalText) {
    if (!button) return;
    button.disabled = false;
    button.classList.remove('btn-danger', 'btn-success', 'btn-warning');
    button.classList.add('btn-info');
    button.innerHTML = originalText;
}

function testConnection() {
    const ip = document.getElementById('ip_address')?.value;
    const port = document.getElementById('rtsp_port')?.value;
    const testBtn = document.getElementById('testBtn');

    if (!ip || !port) {
        alert('Mohon lengkapi IP Address dan Port terlebih dahulu');
        setRtspStatus('Offline', 'danger');
        setStatusAwal('Offline', 'bg-danger');
        return;
    }

    if (!testBtn) return;

    const originalText = testBtn.innerHTML;
    testBtn.disabled = true;
    testBtn.classList.remove('btn-danger', 'btn-success');
    testBtn.classList.add('btn-warning');
    testBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testing...';
    setRtspStatus('Menghubungkan...', 'secondary');
    setStatusAwal('Menghubungkan...', 'bg-secondary');

    fetch('/api/test-ip-connection', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            ip: ip,
            port: port,
            timeout: 5
        })
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
            alert('Koneksi berhasil! Host terjangkau.');
            setRtspStatus('Online', 'success');
            setStatusAwal('Online', 'bg-success');
            testBtn.classList.remove('btn-warning');
            testBtn.classList.add('btn-success');
            testBtn.innerHTML = '<i class="fas fa-check me-2"></i>Online';
        } else {
            setRtspStatus('Offline', 'danger');
            setStatusAwal('Offline', 'bg-danger');
            testBtn.classList.remove('btn-warning');
            testBtn.classList.add('btn-danger');
            testBtn.innerHTML = '<i class="fas fa-times me-2"></i>Offline';
        }

    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menguji koneksi');
        setRtspStatus('Offline', 'danger');
        setStatusAwal('Offline', 'bg-danger');
        testBtn.classList.remove('btn-warning');
        testBtn.classList.add('btn-danger');
        testBtn.innerHTML = '<i class="fas fa-times me-2"></i>Offline';
    })
    .finally(() => {
        setTimeout(() => resetTestButton(testBtn, originalText), 3000);
    });
}


// Update RTSP path based on camera brand
function updateRtspPath() {
    const brand = document.getElementById('camera_brand')?.value;
    if (!brand) return;

    // Auto-fill common settings
    if (cameraBrands[brand]) {
        document.getElementById('rtsp_path').value = cameraBrands[brand].main_stream;
        document.getElementById('rtsp_port').value = '554';
        if (!document.getElementById('rtsp_username').value) {
            document.getElementById('rtsp_username').value = 'admin';
        }
    }

    // Show brand info
    const brandInfoDiv = document.getElementById('brandInfo');
    const brandDetailsDiv = document.getElementById('brandDetails');
    
    if (brandInfoDiv && brandDetailsDiv && brandInfo[brand]) {
        const info = brandInfo[brand];
        brandDetailsDiv.innerHTML = `
            <div class="row">
                <div class="col-12 mb-2">
                    <small><strong>Deskripsi:</strong> ${info.description}</small>
                </div>
                <div class="col-md-6 mb-2">
                    <small><strong>Default Login:</strong><br>${info.defaultCredentials}</small>
                </div>
                <div class="col-md-6 mb-2">
                    <small><strong>Port Umum:</strong><br>${info.commonPorts}</small>
                </div>
                <div class="col-12">
                    <small><strong>Tips:</strong> ${info.tips}</small>
                </div>
            </div>
        `;
        brandInfoDiv.style.display = 'block';
    } else if (brandInfoDiv) {
        brandInfoDiv.style.display = 'none';
    }

    updateRtspPreview();
}

// Toggle password visibility
function togglePassword() {
    const input = document.getElementById('rtsp_password');
    const icon = document.getElementById('toggleIcon');
    
    if (input && icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
}

// Get current location
function getCurrentLocationCreate() {
    if (!navigator.geolocation) {
        alert('Geolocation tidak didukung oleh browser ini.');
        return;
    }

    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mendapatkan lokasi...';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
        function(position) {
            document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
            document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
            updateCoordinatePreview();
            
            btn.innerHTML = '<i class="fas fa-check me-2"></i>Lokasi didapat!';
            btn.className = 'btn btn-success';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.className = 'btn btn-outline-success';
                btn.disabled = false;
            }, 2000);
        },
        function(error) {
            alert('Error mendapatkan lokasi: ' + error.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// Open map picker
function openMapPicker() {
    const mapUrl = 'https://www.google.com/maps/@-6.3051,108.3328,15z';
    window.open(mapUrl, '_blank', 'width=800,height=600');
    alert('Silakan pilih lokasi di Google Maps, lalu salin koordinatnya ke form ini.\n\nTips: Klik kanan pada lokasi → pilih koordinat untuk menyalin.');
}

// Update coordinate preview
function updateCoordinatePreview() {
    const preview = document.getElementById('coordinatePreview');
    const previewText = document.getElementById('coordinate_preview');
    if (!preview || !previewText) return;

    const lat = document.getElementById('latitude')?.value;
    const lng = document.getElementById('longitude')?.value;

    if (lat && lng) {
        previewText.textContent = `Latitude: ${lat}, Longitude: ${lng}`;
        preview.style.display = 'block';

        const latNum = parseFloat(lat);
        const lngNum = parseFloat(lng);

        if (latNum >= -90 && latNum <= 90 && lngNum >= -180 && lngNum <= 180) {
            preview.className = 'alert alert-success mt-3';
        } else {
            preview.className = 'alert alert-danger mt-3';
        }
    } else {
        preview.style.display = 'none';
    }
}

</script>

<style>
.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    background: linear-gradient(135deg, #4CAF50, #2E7D32);
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #2196F3, #1976D2);
    border: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1976D2, #1565C0);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.form-control:focus, .form-select:focus {
    border-color: #2196F3;
    box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
}

.alert {
    border-radius: 10px;
}

.modal-content {
    border-radius: 15px;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 1rem;
    }
}
</style>
@endsection