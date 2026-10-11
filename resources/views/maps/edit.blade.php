@extends('layouts.cctv')

@section('title', 'Edit Lokasi CCTV - Kilang Pertamina RU VI Balongan')

@section('content')
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
            <form id="editCctvForm">
                <div class="modal-body p-4">
                    <div id="alertModalContainer"></div>
                    <input type="hidden" name="cctv_id" id="cctv_id">
                    
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
                                           placeholder="-6.305100">
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
                                           placeholder="108.332800">
                                    <div class="form-text">Contoh: 108.332800</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-between p-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" onclick="submitForm(event)">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // create a loading modal just like on the map index
        const loadingModalHtml = `
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
        </div>`;
        document.body.insertAdjacentHTML('beforeend', loadingModalHtml);
        window.loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    });

    function submitForm(event) {
        event.preventDefault();
        console.log('submitForm called');
        if (window.loadingModal) { window.loadingModal.show(); }
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        }
        
        const formData = new FormData(document.getElementById('editCctvForm'));
        const cctvId = formData.get('cctv_id');
        
        // also log csrf token from meta to ensure it's being read
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        console.log('csrfToken (edit page):', csrfToken);

        fetch(`/maps/update-location/${cctvId}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // after saving, go to the CCTV listing
                window.location.href = '{{ route("cctvs.index") }}';
            } else {
                // Handle validation errors
                const alertContainer = document.getElementById('alertModalContainer');
                alertContainer.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i> Simpan Perubahan';
                }
                if (window.loadingModal) { window.loadingModal.hide(); }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i> Simpan Perubahan';
            }
            if (window.loadingModal) { window.loadingModal.hide(); }
        });
    }
</script>
@endsection
