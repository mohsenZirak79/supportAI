import "./admin/bootstrap.min.js";
import "./admin/jquery-3.7.1.min.js";
import "./admin/bootstrap-notify.js";
import "./admin/Chart.extension.js";
import "./admin/chartjs.min.js";
import "./admin/choices.min.js";
import "./admin/fullcalendar.min.js";
import "./admin/perfect-scrollbar.min.js";
import "./admin/popper.min.js";
import "./admin/smooth-scrollbar.min.js";
import "./admin/soft-ui-dashboard.js";
import "./admin/axios.min.js";

import './bootstrap'
import { createApp } from 'vue'
window.axios = axios;

// ⬇️ این خط حیاتی است
window.axios.defaults.withCredentials = true;
// ✅ چون App.vue نداری، کامپوننت اصلی‌ت همون ChatInterface هست
import ChatInterface from './components/ChatInterface.vue'

// ✅ Toast library
import VueToast from 'vue-toast-notification'
 // تم زیباتر از default

// ایجاد اپ Vue
const app = createApp(ChatInterface)

// ثبت Toast به‌صورت پلاگین
app.use(VueToast, {
    position: 'bottom-left',
    duration: 4000,
    dismissible: true,
})

// دسترسی سراسری (برای استفاده در کنسول یا فایل JS دیگر)
window.__toast = app.config.globalProperties.$toast

// mount به #app در layout اصلی
app.mount('#app')
