@extends('layouts.cctv')

@section('title', 'Konfirmasi Hapus CCTV')

@section('styles')
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Konfirmasi Hapus CCTV
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>

                    <div class="mb-4">
                        <h6>Detail CCTV yang akan dihapus:</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Nama Kamera:</th>
                                <td>{{ $cctv->name }}</td>
                            </tr>
                            <tr>
                                <th>Lokasi:</th>
                                <td>{{ $cctv->location }}</td>
                            </tr>
                            <tr>
                                <th>IP Address:</th>
                                <td>{{ $cctv->ip_address }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>{!! $cctv->status_badge !!}</td>
                            </tr>
                        </table>
                    </div>

                    <p class="text-danger">
                        <i class="fas fa-info-circle me-2"></i>
                        Semua data CCTV, termasuk stream yang sedang berjalan, akan dihapus secara permanen.
                    </p>

                    <form action="{{ route('cctvs.destroy', $cctv) }}" method="POST" id="deleteForm">
                        @csrf
                        @method('DELETE')
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Batal
                            </a>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash me-2"></i>Ya, Hapus CCTV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    function confirmDelete() {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "CCTV '{{ $cctv->name }}' akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm').submit();
            }
        });
    }
</script>
@endsection
