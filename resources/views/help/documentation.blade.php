@extends('layouts.cctv')

@section('title', 'Dokumentasi - CCTV Monitoring')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #1976d2, #1565c0); color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-book me-2"></i>Dokumentasi</h4>
                        <a href="{{ route('help.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach($docs as $doc)
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <i class="fas {{ $doc['icon'] }} fa-2x text-primary"></i>
                                        </div>
                                        <h5 class="mb-0">{{ $doc['title'] }}</h5>
                                    </div>
                                    <p class="text-muted">{{ $doc['description'] }}</p>
                                    <button class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-read me-1"></i> Baca Selengkapnya
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <h5>API Reference</h5>
                        <div class="alert alert-info">
                            <h6><i class="fas fa-code me-2"></i>Endpoint API</h6>
                            <hr>
                            <p class="mb-1"><code>POST /api/cctv/{id}/start-stream</code> - Memulai streaming CCTV</p>
                            <p class="mb-1"><code>POST /api/cctv/{id}/stop-stream</code> - Menghentikan streaming CCTV</p>
                            <p class="mb-0"><code>GET /api/cctv-data</code> - Mendapatkan data semua CCTV</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

