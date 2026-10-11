@extends('layouts.cctv')

@section('title', 'Dashboard Overview - CCTV Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Dashboard Overview</h1>
    </div>
    <div>
        <a href="{{ route('cctvs.index') }}" class="btn btn-primary me-2">
            <i class="fas fa-list"></i> Kelola CCTV
        </a>
        <a href="{{ route('cctvs.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Tambah Kamera
        </a>
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
                            <h3>{{ $statistics['online'] }}</h3>
                            <p class="mb-0">Total CCTV Online</p>
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
                            <h3>{{ $statistics['offline'] }}</h3>
                            <p class="mb-0">Total CCTV Offline</p>
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
                            <h3>{{ $statistics['total'] }}</h3>
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

<!-- Recent Activity -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Live Feed CCTV Pilihan</h5>
            </div>
            <div class="card-body">
                <div class="bg-dark text-white text-center py-5 rounded">
                    <i class="fas fa-video fa-4x mb-3 text-muted"></i>
                    <h4>CCTV Live Feed Tidak Tersedia</h4>
                    <p class="text-muted">Fitur live streaming akan segera hadir</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Aktivitas Terbaru</h6>
            </div>
            <div class="card-body">
                @if($recentActivity->count() > 0)
                    @foreach($recentActivity as $activity)
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                @if($activity->status === 'online')
                                    <i class="fas fa-check-circle text-success"></i>
                                @elseif($activity->status === 'offline')
                                    <i class="fas fa-times-circle text-danger"></i>
                                @else
                                    <i class="fas fa-tools text-warning"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted">{{ $cctv->created_at ? $cctv->created_at->diffForHumans() : '-' }}</small>
                                <div>{{ $activity->name }}</div>
                                <small class="text-muted">{{ $activity->location }}</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <p>Belum ada aktivitas</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- System Status -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">System Status</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="mb-2">
                            <i class="fas fa-server fa-2x text-success"></i>
                        </div>
                        <h6>Server Status</h6>
                        <span class="badge bg-success">Online</span>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="mb-2">
                            <i class="fas fa-database fa-2x text-success"></i>
                        </div>
                        <h6>Database</h6>
                        <span class="badge bg-success">Connected</span>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="mb-2">
                            <i class="fas fa-wifi fa-2x text-success"></i>
                        </div>
                        <h6>Network</h6>
                        <span class="badge bg-success">Stable</span>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="mb-2">
                            <i class="fas fa-shield-alt fa-2x text-success"></i>
                        </div>
                        <h6>Security</h6>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto refresh setiap 20 detik
    setInterval(function() {
        // Refresh statistik via AJAX jika diperlukan
        console.log('Auto refresh dashboard...');
    }, 20000);

    // Add hover effects to clickable cards
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
@endsection
