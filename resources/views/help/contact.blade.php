@extends('layouts.cctv')

@section('title', 'Hubungi Kami - CCTV Monitoring')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #1976d2, #1565c0); color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Hubungi Kami</h4>
                        <a href="{{ route('help.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row g-4">
                        <div class="col-md-6">
                            <h5 class="mb-3">Informasi Kontak</h5>
                            <div class="mb-3">
                                <i class="fas fa-building text-primary me-2"></i>
                                <strong>PT. PPN RU Balongan</strong>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                <span>Jl. Raya Balongan, Indramayu, Jawa Barat</span>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-phone text-success me-2"></i>
                                <span>+62 234 XXXX XXX</span>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-envelope text-warning me-2"></i>
                                <span>it-support@ppnru.co.id</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Jam Operasional</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td>Senin - Jumat</td>
                                    <td>: 08:00 - 17:00 WIB</td>
                                </tr>
                                <tr>
                                    <td>Sabtu</td>
                                    <td>: 08:00 - 12:00 WIB</td>
                                </tr>
                                <tr>
                                    <td>Minggu</td>
                                    <td>: Tutup</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Kirim Pesan</h5>
                    <form action="{{ route('help.ticket.submit') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subjek</label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Pesan</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Kirim Pesan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

