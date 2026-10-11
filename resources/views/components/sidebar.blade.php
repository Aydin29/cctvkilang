<!-- 
  ======================================================================
  KODE SIDEBAR LENGKAP ANDA DIMULAI DI SINI
  ======================================================================
-->
<div class="sidebar bg-slate-800 text-white h-screen w-64 fixed left-0 top-0 z-40 overflow-y-auto" id="sidebar">
    
    <!-- ====================================================== -->
    <!-- ===== BAGIAN LOGO YANG TELAH DIPERBARUI DIMULAI ===== -->
    <!-- ====================================================== -->

    <!-- Logo/Brand -->
    <div class="p-4 border-b border-slate-700 sidebar-logo">
        <div class="text-center relative">
            <!-- 
              Ini adalah div yang menciptakan efek background.
              - bg-slate-700/50: Warna background abu-abu gelap dengan transparansi 50%.
              - p-3: Memberi jarak antara logo dengan tepi background.
              - rounded-lg: Membuat sudut background menjadi membulat.
              - inline-block: Membuat lebar background pas dengan konten di dalamnya.
            -->
            <div class="bg-slate-100/50 p-3 rounded-lg inline-block">
                <!-- Tag gambar logo Anda dipertahankan sesuai kode asli -->
                <img src="{{ asset('images/PPN2.png') }}" alt="Pertamina Logo" style="max-width: 180px; display: inline-block;">
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-4 sidebar-nav">
        <ul class="space-y-1 px-3 nav-list">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-colors duration-200 nav-link {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                    </svg>
                    <span class="font-medium nav-text">DASHBOARD</span>
                </a>
            </li>

            <!-- Profile -->
            <li class="nav-item">
                <a href="{{ route('profile.show') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-colors duration-200 nav-link {{ request()->routeIs('profile.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="font-medium nav-text">PROFILE</span>
                </a>
            </li>

            <!-- Data CCTV -->
            <li class="nav-item">
                <a href="{{ route('cctvs.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-colors duration-200 nav-link {{ request()->routeIs('cctvs.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-medium nav-text">DATA CCTV</span>
                </a>
            </li>

            <!-- Maps -->
            <li class="nav-item submenu">
                <a href="{{ route('maps.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-colors duration-200 nav-link {{ request()->routeIs('maps.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7l6-3 5.447 2.724A1 1 0 0121 7.618v10.764a1 1 0 01-1.447.894L15 17l-6 3z"></path>
                    </svg>
                    <span class="font-medium nav-text">MAPS</span>
                </a>
            </li>

            <!-- CCTV Analysis -->
            <li class="nav-item">
                <a href="{{ route('cctvanalys.index') }}"
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-colors duration-200 nav-link {{ request()->routeIs('cctvanalys.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="font-medium nav-text">CCTV ANALYSIS</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- User Info & Logout -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-700 sidebar-footer">
        <div class="flex items-center space-x-3 mb-3">
            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-white"></i>
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
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); this.closest('form').submit();"
               class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-colors duration-200 text-slate-300 hover:bg-red-600 hover:text-white">
                <i class="fas fa-sign-out-alt w-5 h-5 nav-icon"></i>
                <span class="font-medium nav-text">Logout</span>
            </a>
        </form>
    </div>
</div>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 280px;
    background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar.collapsed {
    width: 70px;
}

.sidebar-content {
    padding: 20px 0;
    height: 100%;
    overflow-y: auto;
}

.sidebar-nav {
    padding: 0 10px;
}

.nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin-bottom: 5px;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: #ecf0f1;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 14px;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.nav-link:hover {
    background: rgba(52, 152, 219, 0.2);
    color: #3498db;
    transform: translateX(5px);
}

.nav-link.active {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: #ecf0f1;
}

.nav-icon {
    font-size: 18px;
    width: 20px;
    text-align: center;
    margin-right: 15px;
    transition: all 0.3s ease;
}

.nav-text {
    transition: all 0.3s ease;
    white-space: nowrap;
}

.submenu .nav-link {
    padding-left: 35px;
    font-size: 13px;
}

.submenu .nav-icon {
    font-size: 16px;
}

.sidebar.collapsed .nav-text {
    opacity: 0;
    width: 0;
}

.sidebar.collapsed .nav-icon {
    margin-right: 0;
}

.sidebar.collapsed .nav-link {
    justify-content: center;
    padding: 15px 10px;
}

.sidebar.collapsed .submenu .nav-link {
    padding-left: 10px;
}

/* Main content adjustment */
.main-content {
    margin-left: 280px;
    transition: all 0.3s ease;
    min-height: 100vh;
    background: #f8f9fa;
}

.sidebar.collapsed + .main-content {
    margin-left: 70px;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
    }
    
}

/* Scrollbar styling */
.sidebar-content::-webkit-scrollbar {
    width: 6px;
}

.sidebar-content::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar-content::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.sidebar-content::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

.sidebar-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateX(5px)';
            }
        });
        
        link.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateX(0)';
            }
        });
    });
});
</script>