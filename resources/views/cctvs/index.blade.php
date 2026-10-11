@extends('layouts.cctv')

@section('title', 'Daftar Kamera CCTV')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center mb-2">
            <h1 class="mb-0">List CCTV</h1>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('cctvs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New CCTV
        </a>
        <button type="button" id="bulk-delete-btn" class="btn btn-danger" disabled>
            <i class="fas fa-trash"></i> Hapus Terpilih
        </button>
    </div>
</div>

<!-- Search and Filter -->
<div class="row mb-4">
    <div class="col-md-8">
        <form method="GET" action="{{ route('cctvs.index') }}" class="d-flex">
            <input type="text" name="search" class="form-control me-2" 
                   placeholder="Cari nama kamera, lokasi, atau IP..." 
                   value="{{ $currentSearch }}">
            <select name="location" class="form-select me-2" onchange="this.form.submit()">
                <option value="">Semua Lokasi</option>
                @foreach($locations as $location)
                    <option value="{{ $location }}" {{ $currentLocation == $location ? 'selected' : '' }}>
                        {{ $location }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="status" value="{{ $currentFilter }}">
            <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    <div class="col-md-4 text-end">
        @if($currentFilter || $currentSearch || $currentLocation)
            <a href="{{ route('cctvs.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Clear Filter
            </a>
        @endif
    </div>
</div>

<!-- Statistics Cards (Clickable) -->
<div class="row mb-4">
    <div class="col-md-4">
        <a href="{{ route('cctvs.index', ['status' => 'online']) }}" class="text-decoration-none">
            <div class="card bg-success text-white h-100 {{ $currentFilter === 'online' ? 'border border-light border-3' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $statistics['online'] }}</h4>
                            <p class="mb-0">ONLINE</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('cctvs.index', ['status' => 'offline']) }}" class="text-decoration-none">
            <div class="card bg-danger text-white h-100 {{ $currentFilter === 'offline' ? 'border border-light border-3' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $statistics['offline'] }}</h4>
                            <p class="mb-0">OFFLINE</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('cctvs.index') }}" class="text-decoration-none">
            <div class="card bg-primary text-white h-100 {{ $currentFilter === '' ? 'border border-light border-3' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $statistics['total'] }}</h4>
                            <p class="mb-0">TOTAL KAMERA</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-video fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Filter Status Info -->
@if($currentFilter)
    <div class="alert alert-info">
        <i class="fas fa-filter me-2"></i>
        Menampilkan kamera dengan status: <strong>{{ ucfirst($currentFilter) }}</strong>
        <span class="badge bg-primary ms-2">{{ $cctvs->count() }} kamera</span>
    </div>
@endif

@if($currentSearch)
    <div class="alert alert-info">
        <i class="fas fa-search me-2"></i>
        Hasil pencarian untuk: <strong>"{{ $currentSearch }}"</strong>
        <span class="badge bg-primary ms-2">{{ $cctvs->count() }} kamera</span>
    </div>
@endif

<!-- CCTV Table -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Kamera</h5>
            <button type="button" class="btn btn-warning btn-sm" id="ping-all-ip-btn">
                <i class="fas fa-plug me-2"></i>Refresh Status IP
            </button>
        </div>
    </div>
    <div class="card-body">

        @if($cctvs->count() > 0)
            <form action="{{ route('cctvs.bulkDestroy') }}" method="POST" id="bulk-delete-form">
                @csrf
                @method('DELETE')
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="10"><input type="checkbox" id="select-all"></th>
                                <th>Nama Kamera</th>
                                <th>Lokasi</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>Last Seen</th>
                                <th width="200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cctvs as $cctv)
                            <tr>
                                <td><input type="checkbox" name="cctv_ids[]" value="{{ $cctv->id }}" class="cctv-checkbox"></td>
                                <td>{{ $cctv->name }}</td>
                                <td>{{ $cctv->location }}</td>
                                <td><code>{{ $cctv->ip_address }}</code></td>
                                <td>
                                    <span id="cctv-status-{{ $cctv->id }}" class="badge"
                                        data-ip="{{ $cctv->ip_address }}"
                                        data-port="{{ $cctv->rtsp_port ?? 554 }}"
                                        data-rtsp-path="{{ $cctv->rtsp_path }}"
                                        data-rtsp-username="{{ $cctv->rtsp_username }}"
                                        data-rtsp-password="{{ $cctv->rtsp_password }}">
                                        {!! $cctv->status_badge_content !!}
                                    </span>

                                </td>

                                <td>{{ $cctv->last_seen_formatted }}</td>
                                <td>
                                    <div class="btn-group" role="group">
<a href="{{ route('cctvs.show', $cctv) }}" class="btn btn-sm btn-info cctv-view-btn" 
                                           data-cctv-id="{{ $cctv->id }}" 
                                           data-start-url="{{ route('cctvs.start-streaming', $cctv) }}" 
                                           title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('cctvs.edit', $cctv) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
            
            <div class="mt-4">
                {{ $cctvs->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-video fa-3x text-muted mb-3"></i>
                @if($currentFilter || $currentSearch)
                    <h5>Tidak ada kamera yang sesuai dengan filter</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="{{ route('cctvs.index') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> Lihat Semua Kamera
                    </a>
                @else
                    <h5>Belum ada data CCTV yang tersedia</h5>
                    <p class="text-muted">Silakan tambahkan kamera CCTV pertama Anda</p>
                    <a href="{{ route('cctvs.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Kamera
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllCheckbox = document.getElementById('select-all');

        const cctvCheckboxes = document.querySelectorAll('.cctv-checkbox');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const bulkDeleteForm = document.getElementById('bulk-delete-form');

        // ===== Ping IP semua (per IP card/button) =====
        // Logic: test TCP koneksi ke ip:port lalu update badge online/offline
        // POST /api/test-ip-connection { ip: "x.x.x.x", port: 554 }
        function pingIpCctv(ip, port, statusBadgeEl) {
            if (!ip || !port) return;
            if (!statusBadgeEl) return;

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




        // Ping semua IP dalam tabel
        const pingAllBtn = document.getElementById('ping-all-ip-btn');
        if (pingAllBtn) {
            pingAllBtn.addEventListener('click', async function () {
                const rows = document.querySelectorAll('[id^="cctv-status-"]');

                // Ambil data ip/port dari atribut di row (kita pasang dari render HTML)
                // Kalau badge belum punya atribut, kita gunakan dataset dari tombol (seharusnya tidak ada lagi).
                // Jadi untuk saat ini: kita gunakan query DOM: cari tombol ping row (jika ada) tidak dipakai.

                // Karena kita tidak lagi punya tombol per-IP, datanya harus diambil dari hidden dataset.
                // Maka: kita tampilkan fallback offline.
                // Untuk implementasi ini, kita butuh atribut ip/port di badge.
                // Jadi pastikan render badge punya data-ip dan data-port.
                let promises = [];

                rows.forEach(badgeEl => {
                    // data-attribute dari render HTML
                    const ip = badgeEl.getAttribute('data-ip');
                    const port = badgeEl.getAttribute('data-port');
                    const rtspPath = badgeEl.getAttribute('data-rtsp-path') || '';
                    const username = badgeEl.getAttribute('data-rtsp-username') || '';
                    const password = badgeEl.getAttribute('data-rtsp-password') || '';

                    if (!ip || !port) return;

                    promises.push(pingIpCctv(ip, port, badgeEl));



                });

                await Promise.all(promises);
            });
        }



        function toggleBulkDeleteButton() {
            const anyChecked = Array.from(cctvCheckboxes).some(checkbox => checkbox.checked);
            bulkDeleteBtn.disabled = !anyChecked;
        }

        selectAllCheckbox.addEventListener('change', function () {
            cctvCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkDeleteButton();
        });

        cctvCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                if (!this.checked) {
                    selectAllCheckbox.checked = false;
                }
                toggleBulkDeleteButton();
            });
        });

        bulkDeleteBtn.addEventListener('click', function () {
            const selectedCount = document.querySelectorAll('.cctv-checkbox:checked').length;
            if (selectedCount > 0 && confirm(`Anda yakin ingin menghapus ${selectedCount} kamera CCTV yang dipilih?`)) {
                bulkDeleteForm.submit();
            }
        });

        toggleBulkDeleteButton();


    });
</script>
@endpush
