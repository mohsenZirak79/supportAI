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

import './bootstrap';

// DataTables + style
import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';

// Buttons + style
import 'datatables.net-buttons';
import 'datatables.net-buttons-dt/css/buttons.dataTables.css';
import 'datatables.net-buttons/js/buttons.html5.mjs';

// Export deps
import pdfMake from 'pdfmake/build/pdfmake';
import vfsFonts from 'pdfmake/build/vfs_fonts';
import JSZip from 'jszip';

// ✅ مقداردهی درست
pdfMake.addVirtualFileSystem(vfsFonts);
window.JSZip = JSZip;

const baseOptions = {
    order: [],
    pageLength: 10,
    lengthMenu: [5, 10, 20, 50],
    layout: {
        topStart: ['pageLength','buttons'],
        bottomStart: 'info',
        bottomEnd: 'paging'
    },
    language: {
        search: 'جستجو:',
        lengthMenu: 'نمایش _MENU_',
        info: 'نمایش _START_ تا _END_ از _TOTAL_ رکورد',
        infoEmpty: 'نمایش 0 تا 0 از 0 رکورد',
        zeroRecords: 'موردی یافت نشد',
        emptyTable: 'داده‌ای وجود ندارد',
        paginate: { first:'اول', previous:'قبلی', next:'بعدی', last:'آخر' }
    },
    buttons: [
        { extend: 'excelHtml5', text: 'Excel', className: 'dt-btn-excel' },
        { extend: 'pdfHtml5',   text: 'PDF',   className: 'dt-btn-pdf'   }
    ]
};

// تابع سازنده که هر جدول را جداگانه مقداردهی می‌کند
function makeDataTable(el, extraOptions = {}) {
    const options = Object.assign({}, baseOptions, extraOptions);
    // اگر قبلاً init شده، دوباره نساز
    if (el._dtInstance) return el._dtInstance;
    const instance = new DataTable(el, options);
    el._dtInstance = instance;
    return instance;
}

document.addEventListener('DOMContentLoaded', () => {
    // همه جدول‌هایی که می‌خواهی دیتاتیبل شوند کلاس بده
    const tables = document.querySelectorAll('table.datatable');
    tables.forEach((el) => {
        // مثال: اگر خواستی per-table سفارشی کنی
        // const perPage = parseInt(el.dataset.perPage || '10', 10);
        // makeDataTable(el, { pageLength: perPage });

        makeDataTable(el);
    });
});


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
