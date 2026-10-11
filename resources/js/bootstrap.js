import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

// Initialize Echo/Pusher only if environment variables are available
if (import.meta.env.VITE_PUSHER_APP_KEY && import.meta.env.VITE_PUSHER_APP_CLUSTER) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        forceTLS: import.meta.env.VITE_PUSHER_SCHEME === 'https',
        wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
        wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
        wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
        enabledTransports: ['ws', 'wss'],
    });
} else {
    console.warn('Pusher environment variables not found. Real-time features will be disabled.');

    // Mock Echo supaya tidak error kalau variabel env kosong
    window.Echo = {
        channel: () => ({
            listen: () => {},
            stopListening: () => {}
        }),
        private: () => ({
            listen: () => {},
            stopListening: () => {}
        }),
        join: () => ({
            here: () => {},
            joining: () => {},
            leaving: () => {}
        }),
        leave: () => {},
        socketId: () => null
    };
}
