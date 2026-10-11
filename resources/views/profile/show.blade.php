@extends('layouts.cctv')

@section('title', 'Profile - CCTV Management')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        
        {{-- Notifikasi sukses --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif



        {{-- Page Header (match dashboard) --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Profile Information</h1>
            </div>
            <div>
                <form id="deleteAccountForm" action="{{ route('profile.destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                        <i class="fas fa-trash me-1"></i> Delete Account
                    </button>
                </form>
            </div>
        </div>

        {{-- Card Profil --}}
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-circle me-2"></i>
                    Profile Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center mb-4 mb-md-0">
                        <div class="mb-3">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" 
                                     alt="{{ $user->name }}" 
                                     class="rounded-circle img-fluid" 
                                     style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <i class="fas fa-user-circle fa-5x text-muted"></i>
                            @endif
                        </div>
                        <h5 class="mb-1">{{ $user->name }}</h5>
                        @if($user->bio)
                            <p class="text-muted mt-2 small">{{ $user->bio }}</p>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td style="width: 30%;" class="fw-bold">Full Name:</td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email Address:</td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Member Since:</td>
                                    <td>{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Last Login:</td>
                                    <td>{{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Not available' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" data-bs-backdrop="false" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Account Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p>Are you sure you want to delete your account?</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                {{-- Gunakan onclick langsung di button sebagai alternatif jika script eksternal macet --}}
                <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteAccountForm').submit();">
                    Yes, delete my account
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
        <script>
                document.addEventListener('DOMContentLoaded', function() {
                        var confirmBtn = document.getElementById('confirmDeleteButton');
                        if (confirmBtn) {
                                confirmBtn.addEventListener('click', function() {
                                        // Submit the delete form when user confirms
                                        var form = document.getElementById('deleteAccountForm');
                                        if (form) {
                                                form.submit();
                                        }
                                });
                        }
                });
        </script>
@endsection

