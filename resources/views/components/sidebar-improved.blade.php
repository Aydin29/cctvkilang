<!-- 
  ======================================================================
  IMPROVED SIDEBAR WITH ELEGANT LOGOUT SECTION
  ======================================================================
-->
<div class="sidebar bg-slate-800 text-white h-screen w-64 fixed left-0 top-0 z-40 overflow-y-auto" id="sidebar">
    
    <!-- Logo Section -->
    <div class="p-4 border-b border-slate-700">
        <div class="text-center">
            <div class="bg-slate-100/50 p-3 rounded-lg inline-block">
                <img src="{{ asset('images/PPN2.png') }}" alt="Pertamina Logo" style="max-width: 180px; display: inline-block;">
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-4 px-3">
        <ul class="space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>DASHBOARD</span>
                </a>
            </li>

            <!-- Profile -->
            <li>
                <a href="{{ route('profile.show') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-colors duration-200 {{ request()->routeIs('profile.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    <i class="fas fa-user w-5"></i>
                    <span>PROFILE</span>
                </a>
            </li>

            <!-- Data CCTV -->
            <li>
                <a href="{{ route('cctvs.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-colors duration-200 {{ request()->routeIs('cctvs.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    <i class="fas fa-video w-5"></i>
                    <span>DATA CCTV</span>
                </a>
            </li>

            <!-- Maps -->
            <li>
                <a href="{{ route('maps.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-colors duration-200 {{ request()->routeIs('maps.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    <i class="fas fa-map w-5"></i>
                    <span>MAPS</span>
                </a>
            </li>

            <!-- CCTV Analysis -->
            <li>
                <a href="{{ route('cctvanalys.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-colors duration-200 {{ request()->routeIs('cctvanalys.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span>CCTV ANALYSIS</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- User Info & Logout - ELEGANT SECTION -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-700">
        <!-- User Info Card -->
        <div class="bg-slate-700/50 backdrop-blur-sm rounded-lg p-3 mb-3">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-white text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-white truncate">
                        {{ Auth::user()->name ?? 'Admin User' }}
                    </div>
                    <div class="text-xs text-slate-400 truncate">
                        {{ Auth::user()->email ?? 'admin@pertamina.com' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Elegant Logout Button -->
        <button onclick="showLogoutModal()" 
                class="w-full flex items-center justify-center space-x-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium py-2.5 px-4 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg">
            <i class="fas fa-sign-out-alt text-sm"></i>
            <span>Logout</span>
        </button>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-slate-800 border border-slate-600 rounded-xl">
            <div class="modal-header border-b border-slate-600">
                <h5 class="modal-title text-white" id="logoutModalLabel">
                    <i class="fas fa-sign-out-alt mr-2"></i>Confirm Logout
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-slate-300">
                <div class="text-center mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-3xl mb-3"></i>
                    <p class="mb-2">Are you sure you want to logout?</p>
                    <p class="text-sm text-slate-400">You will need to login again to access the system.</p>
                </div>
                
                <div class="bg-slate-700/50 rounded-lg p-3 mb-4">
                    <div class="text-sm">
                        <strong>User:</strong> {{ Auth::user()->name ?? 'Admin User' }}<br>
                        <strong>Role:</strong> {{ ucfirst(Auth::user()->role ?? 'Admin') }}
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-600">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout Now
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
    document.getElementById('logout-form').submit();
}

// Add keyboard shortcut (Ctrl+Shift+L)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'L') {
        e.preventDefault();
        showLogoutModal();
    }
});
</script>

<style>
/* Sidebar Styles */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 256px;
    background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1rem;
    border-top: 1px solid #475569;
}

/* Responsive */
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
