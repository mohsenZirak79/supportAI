import { createApp } from 'vue';
import TicketDashboard from './components/TicketDashboard.vue';
import './bootstrap'; // برای axios و Toast
import { i18nPlugin } from './i18n'; // CSP-safe i18n plugin
import VueToast from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-bootstrap.css';

const app = createApp(TicketDashboard);

// فعال کردن i18n (CSP-safe)
app.use(i18nPlugin);

// فعال کردن Toast
app.use(VueToast, {
    position: 'bottom-left',
    duration: 4000,
    dismissible: true,
});

app.mount('#app');
