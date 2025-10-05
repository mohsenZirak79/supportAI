import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

const roleName = 'support_technical'; // هر نقشی که کاربر فعلی دارد
window.Echo.private(`role.${roleName}`)
    .listen('.referral.created', (payload) => {
        // payload همان broadcastWith
        console.log('New referral for role', roleName, payload);
        // اینجا toast/Badge/etc نشان بده
    });
