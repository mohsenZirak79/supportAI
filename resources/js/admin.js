// /resources/js/admin.js

import "./admin/bootstrap.min.js";
import "./admin/jquery-3.7.1.min.js";
import "./admin/bootstrap-notify.js";
import "./admin/Chart.extension.js";
import Chart from "./admin/chartjs.min.js";
window.Chart = Chart;
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

document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('[data-admin-notifications]');
    if (!container) return;

    const trigger = container.querySelector('[data-admin-notifications-trigger]');
    const dropdown = container.querySelector('[data-admin-notifications-dropdown]');
    const badge = container.querySelector('[data-admin-notifications-badge]');
    const list = container.querySelector('[data-admin-notifications-list]');
    const empty = container.querySelector('[data-admin-notifications-empty]');
    const loading = container.querySelector('[data-admin-notifications-loading]');
    const title = container.querySelector('[data-admin-notifications-title]');
    const refreshBtn = container.querySelector('[data-admin-notifications-refresh]');
    const markAllBtn = container.querySelector('[data-admin-notifications-mark-all]');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const storedLocale = localStorage.getItem('app_language') || document.documentElement.lang?.split('-')[0] || 'fa';
    const locale = ['fa', 'en', 'ar'].includes(storedLocale) ? storedLocale : 'fa';

    const translations = {
        fa: {
            'notifications.title': 'نوتیفیکیشن‌ها',
            'notifications.loading': 'در حال بارگذاری…',
            'notifications.empty': 'نوتیفیکیشنی ثبت نشده.',
            'notifications.refresh': 'بروزرسانی',
            'notifications.markAll': 'خوانده‌شده همه',
            'notifications.loadError': 'بارگذاری نوتیفیکیشن‌ها ممکن نشد.',
            'notifications.referralAssignedTitle': 'ارجاع جدید',
            'notifications.referralAssignedBody': 'ارجاع جدیدی برای «{conversationTitle}» به شما رسید.',
            'notifications.referralRespondedTitle': 'پاسخ ارجاع',
            'notifications.referralRespondedBody': 'پاسخ مربوط به ارجاع شما در «{conversationTitle}» ثبت شد.',
            'notifications.ticketAssignedTitle': 'تیکت جدید',
            'notifications.ticketAssignedBody': 'تیکت «{ticketTitle}» برای بخش شما ثبت شد.',
            'notifications.ticketRespondedTitle': 'پاسخ پشتیبان',
            'notifications.ticketRespondedBody': 'پشتیبان به تیکت «{ticketTitle}» پاسخ داد: «{excerpt}»',
        },
        en: {
            'notifications.title': 'Notifications',
            'notifications.loading': 'Loading…',
            'notifications.empty': 'No notifications yet.',
            'notifications.refresh': 'Refresh',
            'notifications.markAll': 'Mark all read',
            'notifications.loadError': 'Failed to load notifications.',
            'notifications.referralAssignedTitle': 'New referral',
            'notifications.referralAssignedBody': 'A new referral for “{conversationTitle}” is assigned to you.',
            'notifications.referralRespondedTitle': 'Referral response',
            'notifications.referralRespondedBody': 'A response for your referral in “{conversationTitle}” has been posted.',
            'notifications.ticketAssignedTitle': 'New ticket',
            'notifications.ticketAssignedBody': 'Ticket “{ticketTitle}” was assigned to your department.',
            'notifications.ticketRespondedTitle': 'Support reply',
            'notifications.ticketRespondedBody': 'Support replied to “{ticketTitle}”: “{excerpt}”',
        },
        ar: {
            'notifications.title': 'الإشعارات',
            'notifications.loading': 'جاري التحميل…',
            'notifications.empty': 'لا توجد إشعارات بعد.',
            'notifications.refresh': 'تحديث',
            'notifications.markAll': 'وضع الكل كمقروء',
            'notifications.loadError': 'تعذر تحميل الإشعارات.',
            'notifications.referralAssignedTitle': 'إحالة جديدة',
            'notifications.referralAssignedBody': 'تمت إحالة جديدة لـ \"{conversationTitle}\" إليك.',
            'notifications.referralRespondedTitle': 'رد الإحالة',
            'notifications.referralRespondedBody': 'تم نشر رد على إحالتك في \"{conversationTitle}\".',
            'notifications.ticketAssignedTitle': 'تذكرة جديدة',
            'notifications.ticketAssignedBody': 'تم إسناد التذكرة \"{ticketTitle}\" إلى قسمك.',
            'notifications.ticketRespondedTitle': 'رد الدعم',
            'notifications.ticketRespondedBody': 'رد الدعم على \"{ticketTitle}\": \"{excerpt}\"',
        }
    };

    const localeMap = { fa: 'fa-IR', en: 'en-US', ar: 'ar-SA' };

    const t = (key) => translations[locale]?.[key] || translations.fa?.[key] || key;

    const translateTemplate = (template, params = {}) => {
        if (!template) return '';
        return Object.keys(params || {}).reduce((result, key) => {
            return result.replace(`{${key}}`, params[key] ?? '');
        }, template);
    };

    const formatTitle = (notification) => {
        if (notification?.title_key) {
            const template = t(notification.title_key);
            if (template === notification.title_key) {
                return notification?.title || notification?.type || '';
            }
            return translateTemplate(template, notification?.params || {});
        }
        return notification?.title || notification?.type || '';
    };

    const formatBody = (notification) => {
        if (notification?.body_key) {
            const template = t(notification.body_key);
            if (template === notification.body_key) {
                return notification?.body || '';
            }
            return translateTemplate(template, notification?.params || {});
        }
        return notification?.body || '';
    };

    const relativeTime = (iso) => {
        if (!iso) return '';
        const date = new Date(iso);
        const diffMinutes = Math.round((date - Date.now()) / 1000 / 60);
        try {
            const rtfLocale = localeMap[locale] || 'fa-IR';
            return new Intl.RelativeTimeFormat(rtfLocale, { numeric: 'auto' }).format(diffMinutes, 'minute');
        } catch {
            return date.toLocaleString();
        }
    };

    const setLoading = (state) => {
        if (loading) loading.style.display = state ? 'block' : 'none';
    };

    const setEmpty = (state) => {
        if (empty) empty.style.display = state ? 'block' : 'none';
    };

    const updateBadge = (count) => {
        if (!badge) return;
        if (count > 0) {
            badge.textContent = String(count);
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }
    };

    let notifications = [];
    let loaded = false;

    const renderNotifications = () => {
        if (!list) return;
        list.innerHTML = '';
        const unreadCount = notifications.filter(n => !n.read_at).length;
        updateBadge(unreadCount);
        setEmpty(notifications.length === 0);

        notifications.forEach((notification) => {
            const item = document.createElement('li');
            item.className = `admin-notifications__item${notification.read_at ? '' : ' unread'}`;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'admin-notifications__link';
            button.addEventListener('click', async () => {
                await markNotificationRead(notification);
                handleNotificationSelect(notification);
                dropdown?.classList.remove('is-open');
                trigger?.setAttribute('aria-expanded', 'false');
            });

            const content = document.createElement('div');
            content.className = 'admin-notifications__content';
            const strong = document.createElement('strong');
            strong.textContent = formatTitle(notification);
            const body = document.createElement('p');
            body.textContent = formatBody(notification);
            content.appendChild(strong);
            content.appendChild(body);

            const time = document.createElement('span');
            time.className = 'admin-notifications__timestamp';
            time.textContent = relativeTime(notification.created_at);

            button.appendChild(content);
            button.appendChild(time);
            item.appendChild(button);
            list.appendChild(item);
        });
    };

    const fetchNotifications = async () => {
        setLoading(true);
        setEmpty(false);
        try {
            const res = await fetch('/admin/notifications', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) throw new Error('failed');
            const payload = await res.json();
            notifications = (payload.data || []).sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            loaded = true;
            renderNotifications();
        } catch (error) {
            console.error('admin notifications fetch failed', error);
            window.toast?.error(t('notifications.loadError'));
            setEmpty(true);
        } finally {
            setLoading(false);
        }
    };

    const markNotificationRead = async (notification) => {
        if (!notification || notification.read_at) return;
        try {
            const res = await fetch(`/admin/notifications/${notification.id}/read`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin'
            });
            if (res.ok) {
                notification.read_at = new Date().toISOString();
                renderNotifications();
            }
        } catch (error) {
            console.error('admin notification read failed', error);
        }
    };

    const markAllRead = async () => {
        try {
            const res = await fetch('/admin/notifications/read-all', {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin'
            });
            if (res.ok) {
                notifications = notifications.map(n => ({ ...n, read_at: n.read_at || new Date().toISOString() }));
                renderNotifications();
            }
        } catch (error) {
            console.error('admin notification read-all failed', error);
        }
    };

    const handleNotificationSelect = (notification) => {
        if (!notification) return;
        if (notification.category === 'ticket' && notification.ticket_id) {
            window.location.href = `/admin/tickets?ticket=${notification.ticket_id}`;
            return;
        }
        if (notification.category === 'referral' && notification.conversation_id) {
            window.location.href = '/admin/chats';
        }
    };

    const toggleDropdown = async () => {
        const isOpen = dropdown?.classList.toggle('is-open');
        trigger?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (isOpen && !loaded) {
            await fetchNotifications();
        }
    };

    title && (title.textContent = t('notifications.title'));
    if (loading) loading.textContent = t('notifications.loading');
    if (empty) empty.textContent = t('notifications.empty');
    if (refreshBtn) refreshBtn.textContent = t('notifications.refresh');
    if (markAllBtn) markAllBtn.textContent = t('notifications.markAll');

    trigger?.addEventListener('click', toggleDropdown);
    refreshBtn?.addEventListener('click', fetchNotifications);
    markAllBtn?.addEventListener('click', markAllRead);

    document.addEventListener('click', (event) => {
        if (!dropdown?.classList.contains('is-open')) return;
        if (!container.contains(event.target)) {
            dropdown.classList.remove('is-open');
            trigger?.setAttribute('aria-expanded', 'false');
        }
    });

    fetchNotifications();
});


// ✅ استفاده:
// window.toast.success('عملیات با موفقیت انجام شد')
// window.toast.error('خطایی رخ داد')
