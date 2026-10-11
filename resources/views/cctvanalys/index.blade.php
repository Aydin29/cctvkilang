@extends('layouts.cctv')

@section('title', 'CCTV Analysis Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">CCTV Analysis Dashboard</h4>
                    <small class="text-muted">Analyze CCTV streams by protocol type</small>
                </div>
                <div class="card-body">
                    @if($cctvs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>IP Address</th>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Protocol</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cctvs as $cctv)
                                        <tr>
                                            <td>{{ $cctv->ip_address }}</td>
                                            <td>{{ $cctv->name }}</td>
                                            <td>{{ $cctv->location }}</td>
                                            <td>
                                                <span class="badge bg-{{ $cctv->status === 'online' ? 'success' : ($cctv->status === 'offline' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($cctv->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($cctv->is_streaming && !empty($cctv->playlist_url))
                                                    <span class="badge bg-primary">HLS</span>
                                                @elseif(!empty($cctv->rtsp_url))
                                                    <span class="badge bg-info">RTSP</span>
                                                @else
                                                    <span class="badge bg-secondary">Non-Streaming</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('cctvanalys.analyze', $cctv) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-chart-line"></i> Analyze
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $cctvs->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-video-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No CCTV cameras found</h5>
                            <p class="text-muted">Add CCTV cameras first to perform analysis.</p>
                            <a href="{{ route('cctvs.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add CCTV Camera
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
