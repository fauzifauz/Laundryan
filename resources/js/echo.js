import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

try {
    if (!import.meta.env.VITE_REVERB_APP_KEY) {
        throw new Error('VITE_REVERB_APP_KEY is not set — check your .env file.');
    }

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
} catch (err) {
    console.error('Echo/Pusher failed to initialize:', err);
    // window.Echo sengaja dibiarkan undefined; kode yang cek `if (window.Echo)` di berbagai blade tetap aman.
}