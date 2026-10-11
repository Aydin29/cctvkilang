@extends('layouts.cctv')

@section('title', 'Pusat Bantuan - CCTV Monitoring')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #1976d2, #1565c0); color: white;">
                    <h4 class="mb-0"><i class="fas fa-question-circle me-2"></i>Pusat Bantuan</h4>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- FAQ Card -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-comments fa-3x text-primary"></i>
                                    </div>
                                    <h5>Pertanyaan Umum</h5>
                                    <p class="text-muted">Temukan jawaban untuk pertanyaan yang sering diajukan</p>
                                    <a href="{{ route('help.faq') }}" class="btn btn-primary">
                                        <i class="fas fa-arrow-right me-1"></i> Lihat FAQ
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Documentation Card -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-book fa-3x text-success"></i>
                                    </div>
                                    <h5>Dokumentasi</h5>
                                    <p class="text-muted">Baca panduan lengkap dan dokumentasi sistem</p>
                                    <a href="{{ route('help.documentation') }}" class="btn btn-success">
                                        <i class="fas fa-arrow-right me-1"></i> Lihat Docs
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Card -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-envelope fa-3x text-warning"></i>
                                    </div>
                                    <h5>Hubungi Kami</h5>
                                    <p class="text-muted">Kirim pesan untuk pertanyaan lebih lanjut</p>
                                    <a href="{{ route('help.contact') }}" class="btn btn-warning">
                                        <i class="fas fa-arrow-right me-1"></i> Hubungi
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Ticket Card -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-ticket-alt fa-3x text-info"></i>
                                    </div>
                                    <h5>Kirim Tiket</h5>
                                    <p class="text-muted">Ajukan pertanyaan teknis melalui sistem tikcet</p>
                                    <a href="{{ route('help.contact') }}" class="btn btn-info text-white">
                                        <i class="fas fa-plus me-1"></i> Buat Tiket
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

