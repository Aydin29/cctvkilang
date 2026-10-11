<!-- 
  ======================================================================
  CLEAN & PROFESSIONAL SIDEBAR DESIGN
  ======================================================================
-->
<div class="sidebar bg-gradient-to-b from-slate-800 to-slate-900 text-white h-screen w-64 fixed left-0 top-0 z-40 overflow-y-auto shadow-2xl" id="sidebar">
    
    <!-- Logo Section - Clean & Centered -->
    <div class="p-6 border-b border-slate-700/50">
        <div class="text-center">
            <div class="bg-white/10 backdrop-blur-sm p-3 rounded-xl inline-block">
                <img src="{{ asset('images/PPN2.png') }}" alt="Pertamina Logo" class="w-32 h-auto">
            </div>
        </div>
    </div>

    <!-- Navigation Menu - Clean & Organized -->
    <nav class="flex-1 px-4 py-4">
        <ul class="space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <i class="fas fa-tachometer-alt w-5 text-center"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
            </li>

            <!-- Profile -->
            <li>
                <a href="{{ route('profile.show') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('profile.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <i class="fas fa-user w-5 text-center"></i>
                    <span class="font-medium">Profile</span>
                </a>
            </li>

            <!-- Data CCTV -->
            <li>
                <a href="{{ route('cctvs.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('cctvs.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <i class="fas fa-video w-5 text-center"></i>
                    <span class="font-medium">Data CCTV</span>
                </a>
            </li>

            <!-- Maps -->
            <li>
                <a href="{{ route('maps.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('maps.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <i class="fas fa-map w-5 text-center"></i>
                    <span class="font-medium">Maps</span>
                </a>
            </li>

            <!-- Help -->
            <li>
                <a href="{{ route('help.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('help.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <i class="fas fa-question-circle w-5 text-center"></i>
                    <span class="font-medium">Help</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- User Info & Logout - Clean & Professional -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-700/50 bg-slate-900/50 backdrop-blur-sm">
        <!-- User Info Card - Minimal & Clean -->
        <div class="bg-slate-700/30 rounded-lg p-3 mb-3">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user text-white text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-white truncate">
                        {{ Auth::user()->name ?? 'Admin User' }}
                    </div>
                    <div class="text-xs text-slate-400 truncate">
                        {{ Auth::user()->email ?? 'admin@pertamina.com' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Clean Logout Button -->
        <button onclick="showLogoutModal()" 
                class="w-full flex items-center justify-center space-x-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium py-2.5 px-4 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg hover:scale-[1.02]">
            <i class="fas fa-sign-out-alt text-sm"></i>
            <span class="text-sm font-medium">Logout</span>
        </button>
    </div>
</div>

<!-- Logout Confirmation Modal - Clean Design -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-slate-800 border border-slate-600 rounded-xl shadow-2xl">
            <div class="modal-header border-b border-slate-600/50">
                <h5 class="modal-title text-white font-semibold" id="logoutModalLabel">
                    <i class="fas fa-sign-out-alt mr-2"></i>Konfirmasi Logout
                </h5>
                <button type="button" class="btn-close btn-close-white opacity-70 hover:opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-slate-300 p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-2xl mb-3"></i>
                    <p class="mb-2 text-base">Apakah Anda yakin ingin logout?</p>
                    <p class="text-sm text-slate-400">Anda perlu login kembali untuk mengakses sistem.</p>
                </div>
                
                <div class="bg-slate-700/30 rounded-lg p-3">
                    <div class="text-sm space-y-1">
                        <div><strong>Pengguna:</strong> {{ Auth::user()->name ?? 'Admin User' }}</div>
                        <div><strong>Role:</strong> {{ ucfirst(Auth::user()->role ?? 'Admin') }}</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-600/50 p-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

<!-- Scripts -->
<script>
function showLogoutModal() {
    const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
    modal.show();
}

function confirmLogout() {
    const btn = document.querySelector('#logoutModal .btn-danger');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Proses...';
    btn.disabled = true;
    
    setTimeout(() => {
        document.getElementById('logout-form').submit();
    }, 1000);
}

// Keyboard shortcut (Ctrl+Shift+L)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'L') {
        e.preventDefault();
        showLogoutModal();
    }
});
</script>

<style>
/* Clean Sidebar Styles */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 256px;
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
}

.sidebar::-webkit-scrollbar {
    width: 4px;
}

.sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
}

/* Navigation hover effects */
nav a {
    position: relative;
    overflow: hidden;
}

nav a::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transition: left 0.5s;
}

nav a:hover::before {
    left: 100%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        transform: translateX(-100%);
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
}
</style>
