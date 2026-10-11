<nav class="flex items-center justify-between bg-white dark:bg-gray-800 shadow-lg px-8 py-3 w-full relative z-30">
    <div class="flex items-center space-x-3">
        <img src="{{ asset('images/PPN2.png') }}" alt="Logo Pertamina"
             class="h-16 w-auto object-contain transition-transform duration-200 hover:scale-110"
        />
    </div>

    <div class="flex items-center space-x-4 flex-grow justify-center"> <span class="text-2xl font-bold text-gray-800 dark:text-white transition duration-200 hover:scale-105 hover:drop-shadow-lg tracking-wider elegant-title">
            CCTV KILANG PERTAMINA INTERNASIONAL RU VI <br> & BUMI PATRA AYU
        </span>
        <div class="flex items-center space-x-2">
            <input type="text" x-model="mapSearchQuery" placeholder="Cari CCTV di peta..."
                   class="px-3 py-2 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md">
            <button x-show="mapSearchQuery.length > 0" @click="clearMapSearch"
                    class="p-2 rounded-full bg-red-500 text-white hover:bg-red-600 transition duration-200 shadow-md">
                ✖
            </button>
        </div>
    </div>

    <div class="relative ml-auto mr-4" x-data="{ showNotificationsDropdown: false }" @click.away="showNotificationsDropdown = false">
        <button @click="showNotificationsDropdown = !showNotificationsDropdown; markNotificationsAsRead()"
                class="p-2 rounded-full text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-700 transition duration-200 relative">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0v1m-6-1v1"></path>
            </svg>
            <span x-show="unreadNotificationsCount > 0"
                  class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                <span x-text="unreadNotificationsCount"></span>
            </span>
        </button>

        <div x-show="showNotificationsDropdown" x-cloak
             class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-md shadow-lg z-50 overflow-hidden">
            <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifikasi</h3>
                <button @click="clearNotifications()" class="text-sm text-blue-600 hover:underline dark:text-blue-400">Bersihkan Semua</button>
            </div>
            <div class="max-h-60 overflow-y-auto">
                <template x-for="notification in notifications" :key="notification.id">
                    <div :class="{ 'bg-blue-50 dark:bg-blue-900': !notification.read, 'bg-white dark:bg-gray-800': notification.read }"
                         class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="notification.message"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="notification.timestamp"></p>
                    </div>
                </template>
                <div x-show="notifications.length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                    Tidak ada notifikasi.
                </div>
            </div>
        </div>
    </div>

    <div class="relative" x-data="{ openUserMenu: false }">
        <button @click="openUserMenu = !openUserMenu"
            class="flex items-center space-x-2 px-3 py-1 text-sm text-white bg-gray-700 rounded hover:bg-gray-600 transition duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5.121 17.804A4 4 0 015 16V8a4 4 0 014-4h6a4 4 0 014 4v8a4 4 0 01-.121 1.804M8 12h.01M16 12h.01" />
            </svg>
            <span>Username</span> <svg class="w-4 h-4 transform transition-transform duration-200"
                :class="{ 'rotate-180': openUserMenu }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="openUserMenu" @click.away="openUserMenu = false" x-cloak
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 overflow-hidden dark:bg-gray-800">
            <a href="{{ url('/profile') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                Profile
            </a>
            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                @csrf
                <button type="submit"
                    class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-100 dark:hover:bg-red-600">
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>