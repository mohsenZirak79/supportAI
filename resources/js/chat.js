// resources/js/chat.js
import './bootstrap'; // اگر داری از bootstrap.js استفاده می‌کنی
import { createApp } from 'vue';
import ChatInterface from './components/ChatInterface.vue';
import 'vue-toast-notification/dist/theme-bootstrap.css';
import VueToast from 'vue-toast-notification';
import { i18nPlugin } from './i18n'; // CSP-safe i18n plugin
// Reverb setup
// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';
//
// window.Pusher = Pusher;
// window.Echo = new Echo({
//     broadcaster: 'reverb',
//     key: import.meta.env.VITE_REVERB_APP_KEY,
//     wsHost: import.meta.env.VITE_REVERB_HOST,
//     wsPort: import.meta.env.VITE_REVERB_PORT,
//     wssPort: import.meta.env.VITE_REVERB_PORT,
//     forceTLS: false,
//     enabledTransports: ['ws', 'wss'],
//     authEndpoint: '/broadcasting/auth',
//     auth: {
//         withCredentials: true,
//         headers: {
//             'X-CSRF-TOKEN': document
//                 .querySelector('meta[name="csrf-token"]')
//                 .getAttribute('content'),
//         },
//     },
// });


// createApp(ChatInterface).mount('#app');

document.addEventListener('DOMContentLoaded', () => {
    const el = document.querySelector('#app');
    if (!el) return;

    const app = createApp(ChatInterface);

    // فعال کردن i18n (CSP-safe)
    app.use(i18nPlugin);

    // فعال کردن Toast
    app.use(VueToast, {
        position: 'bottom-left',
        duration: 4000,
        dismissible: true,
    });

    // دسترسی سراسری (برای ChatInterface و کنسول)
    window.__toast = app.config.globalProperties.$toast;

    app.mount(el);
});

