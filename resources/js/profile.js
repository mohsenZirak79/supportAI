/**
 * User Profile Entry Point
 * Initializes the Vue profile application
 */

import { createApp } from 'vue';
import UserProfile from './components/UserProfile.vue';
import { i18nPlugin } from './i18n';
import VueToast from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-bootstrap.css';
import './bootstrap';

// Initialize Vue app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const el = document.querySelector('#app');
    if (!el) return;

    const app = createApp(UserProfile);

    // Use i18n plugin
    app.use(i18nPlugin);

    // Use toast notifications
    app.use(VueToast, {
        position: 'bottom-left',
        duration: 4000,
        dismissible: true,
    });

    // Mount the app
    app.mount(el);
});

