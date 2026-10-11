<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Peta GIS CCTV
        </h2>
    </x-slot>

    <div class="w-full h-[calc(100vh-4rem)] relative">
        <div id="map" class="w-full h-full z-0"></div>
    </div>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const cctvs = @json($cctvs);

        const map = L.map('map').setView([-6.325, 108.35], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        cctvs.forEach(cctv => {
            const marker = L.marker([cctv.latitude, cctv.longitude]).addTo(map);
            marker.bindPopup(`
                <strong>${cctv.name}</strong><br>
                <iframe src="${cctv.stream_url}" width="300" height="200" frameborder="0" allowfullscreen></iframe>
            `);
        });
    </script>
</x-app-layout>
