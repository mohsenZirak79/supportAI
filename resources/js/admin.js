// /resources/js/admin.js

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

// ✅ DataTables
import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'datatables.net-buttons';
import 'datatables.net-buttons-dt/css/buttons.dataTables.css';
import 'datatables.net-buttons/js/buttons.html5.mjs';

import pdfMake from 'pdfmake/build/pdfmake';
import vfsFonts from 'pdfmake/build/vfs_fonts';
import JSZip from 'jszip';

pdfMake.addVirtualFileSystem(vfsFonts);
window.JSZip = JSZip;

// تنظیمات پایه دیتاتیبل
const baseOptions = {
    order: [],
    pageLength: 10,
    lengthMenu: [5, 10, 20, 50],
    layout: {
        topStart: ['pageLength', 'buttons'],
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
        paginate: { first: 'اول', previous: 'قبلی', next: 'بعدی', last: 'آخر' }
    },
    buttons: [
        { extend: 'excelHtml5', text: 'Excel', className: 'dt-btn-excel' }
        // ,{ extend: 'pdfHtml5', text: 'PDF', className: 'dt-btn-pdf' }
    ]
};

// تابع ساخت دیتا‌تیبل
function makeDataTable(el, extraOptions = {}) {
    const options = Object.assign({}, baseOptions, extraOptions);
    if (el._dtInstance) return el._dtInstance;
    const instance = new DataTable(el, options);
    el._dtInstance = instance;
    return instance;
}

// وقتی صفحه لود شد
document.addEventListener('DOMContentLoaded', () => {
    const tables = document.querySelectorAll('table.datatable');
    tables.forEach((el) => makeDataTable(el));
});

import Toastify from 'toastify-js'
import 'toastify-js/src/toastify.css'

// helper global
window.toast = {
    show(message, opts = {}) {
        Toastify(Object.assign({
            text: message,
            duration: 4000,
            gravity: 'bottom', // 'top' or 'bottom'
            position: 'left',  // 'left', 'center', 'right'
            close: true,
            stopOnFocus: true,
            style: {
                background: '#323232',
                color: '#fff',
                borderRadius: '8px',
                padding: '10px 16px',
                boxShadow: '0 3px 10px rgba(0,0,0,0.2)',
            }
        }, opts)).showToast()
    },
    success(msg) {
        this.show(msg, { style: { background: '#16a34a' } })
    },
    error(msg) {
        this.show(msg, { style: { background: '#dc2626' } })
    },
    info(msg) {
        this.show(msg, { style: { background: '#2563eb' } })
    },
    warning(msg) {
        this.show(msg, { style: { background: '#d97706' } })
    },
}

document.addEventListener('DOMContentLoaded', function () {

    const toPersianDigits = str =>
        str.replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);

    // همه متن‌های صفحه را پیمایش کن و اعداد را فارسی کن
    const walk = node => {
        if (node.nodeType === 3) {
            node.nodeValue = toPersianDigits(node.nodeValue);
        } else if (node.nodeType === 1 && node.tagName !== 'SCRIPT' && node.tagName !== 'STYLE') {
            node.childNodes.forEach(walk);
        }
    };

    walk(document.body);
});


// ✅ استفاده:
// window.toast.success('عملیات با موفقیت انجام شد')
// window.toast.error('خطایی رخ داد')
