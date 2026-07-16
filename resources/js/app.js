import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Echo/Pusher diinisialisasi terpisah & dibungkus try/catch,
// supaya kalau config broadcasting error, Alpine & UI tetap jalan normal.
import('./echo').catch((err) => {
    console.error('Echo/Pusher failed to initialize:', err);
});