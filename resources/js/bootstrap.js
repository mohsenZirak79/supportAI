import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.defaults.withCredentials = true;

// Toast جهانی
export const useToast = () => {
    const toasts = Vue.ref([]);

    const add = (message, type = 'success') => {
        const id = Date.now();
        toasts.value.push({ id, message, type });
        setTimeout(() => {
            toasts.value = toasts.value.filter(t => t.id !== id);
        }, 5000);
    };

    return { toasts, add };
};

// اکسپورت برای استفاده در کامپوننت‌ها
window.useToast = useToast;
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
