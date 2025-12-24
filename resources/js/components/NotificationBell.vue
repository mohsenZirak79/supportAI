<template>
    <div class="notification-bell" ref="container">
        <button
            type="button"
            class="bell-trigger"
            :aria-expanded="open.toString()"
            @click="toggleOpen"
        >
            <span class="sr-only">{{ t('notifications.ariaLabel') }}</span>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path
                    d="M12 2a5 5 0 0 0-5 5v3.1c0 .58-.2 1.14-.57 1.58L5 14.4h14l-.43-2.72A2.5 2.5 0 0 1 18 10.1V7a5 5 0 0 0-5-5zm0 18a3 3 0 0 0 2.83-2H9.17A3 3 0 0 0 12 20z"
                />
            </svg>
            <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
        </button>

        <div v-if="open" class="dropdown">
            <div class="dropdown-header">
                <span>{{ t('notifications.title') }}</span>
                <div class="dropdown-actions">
                    <button type="button" class="ghost-btn" @click="refreshNotifications" :disabled="loading">
                        {{ t('notifications.refresh') }}
                    </button>
                    <button type="button" class="ghost-btn" @click="markAllNotificationsRead" :disabled="!unreadCount">
                        {{ t('notifications.markAll') }}
                    </button>
                </div>
            </div>
            <div class="dropdown-body">
                <div v-if="loading" class="empty-state">
                    {{ t('notifications.loading') }}
                </div>
                <div v-else-if="!notifications.length" class="empty-state">
                    {{ t('notifications.empty') }}
                </div>
                <ul v-else>
                    <li
                        v-for="notification in notifications"
                        :key="notification.id"
                        :class="['notification-item', { unread: !notification.read_at }]"
                    >
                        <button type="button" class="notification-link" @click="selectNotification(notification)">
                            <div class="notification-content">
                                <strong>{{ notificationTitle(notification) }}</strong>
                                <p>{{ notificationBody(notification) }}</p>
                            </div>
                            <span class="timestamp">{{ relativeTime(notification.created_at) }}</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import {ref, computed, onMounted, onBeforeUnmount} from 'vue';
import {apiFetch} from '../lib/http';
import {useToast} from 'vue-toast-notification';
import {useLanguage} from '../i18n';

const emit = defineEmits(['select']);
const toast = useToast();
const { locale, t } = useLanguage();

const notifications = ref([]);
const open = ref(false);
const loading = ref(false);
const container = ref(null);

const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length);

const fetchNotifications = async () => {
    loading.value = true;
    try {
        const res = await apiFetch('/notifications');
        if (!res.ok) throw new Error('failed');
        const payload = await res.json();
        notifications.value = (payload.data || []).sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    } catch (error) {
        console.error('notif fetch error', error);
        toast.error(t('notifications.loadError'));
    } finally {
        loading.value = false;
    }
};

const toggleOpen = async () => {
    open.value = !open.value;
    if (open.value) {
        await fetchNotifications();
    }
};

const selectNotification = async (notification) => {
    emit('select', notification);
    await markNotificationRead(notification);
    open.value = false;
};

const markNotificationRead = async (notification) => {
    if (notification.read_at) return;
    try {
        const res = await apiFetch(`/notifications/${notification.id}/read`, {method: 'PATCH'});
        if (res.ok) {
            notification.read_at = new Date().toISOString();
        }
    } catch (error) {
        console.error('mark notification read', error);
    }
};

const markAllNotificationsRead = async () => {
    try {
        const res = await apiFetch('/notifications/read-all', {method: 'PATCH'});
        if (res.ok) {
            notifications.value = notifications.value.map(n => ({...n, read_at: n.read_at || new Date().toISOString()}));
        }
    } catch (error) {
        console.error('mark all read', error);
    }
};

const refreshNotifications = async () => {
    await fetchNotifications();
};

const localeMap = { fa: 'fa-IR', en: 'en-US', ar: 'ar-SA' };

const translateTemplate = (template, params = {}) => {
    if (!template) return '';
    return Object.keys(params || {}).reduce((result, key) => {
        return result.replace(`{${key}}`, params[key] ?? '');
    }, template);
};

const notificationTitle = (notification) => {
    if (notification?.title_key) {
        const template = t(notification.title_key);
        if (template === notification.title_key) {
            return notification?.title || '';
        }
        return translateTemplate(template, notification?.params || {});
    }
    return notification?.title || notification?.type || '';
};

const notificationBody = (notification) => {
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
        const rtfLocale = localeMap[locale.value] || 'fa-IR';
        return new Intl.RelativeTimeFormat(rtfLocale, {numeric: 'auto'}).format(diffMinutes, 'minute');
    } catch {
        return date.toLocaleString();
    }
};

const handleClickOutside = (event) => {
    if (!open.value) return;
    if (!container.value?.contains(event.target)) {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    fetchNotifications();
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.notification-bell {
    position: relative;
}
.bell-trigger {
    position: relative;
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform .2s ease;
}
.bell-trigger:hover {
    transform: translateY(-1px);
}
.bell-trigger svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
}
.badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #ef4444;
    color: white;
    font-size: 0.65rem;
    padding: 2px 6px;
    border-radius: 999px;
}
.dropdown {
    position: absolute;
    top: 48px;
    right: 0;
    width: 320px;
    max-height: 420px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 25px 45px rgba(15, 23, 42, 0.25);
    overflow: hidden;
    z-index: 200;
    display: flex;
    flex-direction: column;
}
.dropdown-header {
    padding: 12px 16px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 700;
}
.dropdown-actions {
    display: flex;
    gap: 8px;
}
.ghost-btn {
    border: none;
    background: transparent;
    font-weight: 600;
    color: #2563eb;
    cursor: pointer;
    font-size: 0.8rem;
}
.dropdown-body {
    flex: 1;
    overflow-y: auto;
    padding: 8px;
}
.notification-item {
    list-style: none;
    margin-bottom: 6px;
}
.notification-link {
    width: 100%;
    border: none;
    background: transparent;
    padding: 10px 12px;
    border-radius: 12px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-direction: column;
    text-align: right;
    cursor: pointer;
}
.notification-item.unread .notification-link {
    background: rgba(59, 130, 246, 0.08);
}
.notification-content strong {
    display: block;
    font-size: 0.9rem;
}
.notification-content p {
    margin-top: 4px;
    font-size: 0.78rem;
    color: #374151;
}
.timestamp {
    font-size: 0.7rem;
    color: #94a3b8;
    margin-top: 6px;
    align-self: flex-end;
}
.empty-state {
    font-size: 0.85rem;
    color: #64748b;
    text-align: center;
    padding: 32px 0;
}
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}
</style>
