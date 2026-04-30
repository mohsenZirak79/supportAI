<template>
    <div class="notification-bell" :class="`notification-bell--${tone}`" ref="container">
        <button
            type="button"
            class="bell-trigger"
            :aria-expanded="open.toString()"
            @click="toggleOpen"
        >
            <span class="sr-only">{{ t('notifications.ariaLabel') }}</span>
            <svg class="bell-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"
                />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>
            <span v-if="unreadCount > 0" class="badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
        </button>

        <Teleport to="body">
            <div
                v-if="open"
                ref="dropdownRef"
                class="dropdown"
                :style="dropdownPanelStyle"
                role="dialog"
                aria-modal="false"
                :aria-label="t('notifications.title')"
            >
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
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { apiFetch } from '../lib/http';
import { useToast } from 'vue-toast-notification';
import { useLanguage } from '../i18n';

defineProps({
    /** light: آیکن تیره روی پس روشن — dark: آیکن روشن روی هدر تیره/فیروزه‌ای */
    tone: { type: String, default: 'light' },
});

const emit = defineEmits(['select']);
const toast = useToast();
const { locale, t } = useLanguage();

const notifications = ref([]);
const open = ref(false);
const loading = ref(false);
const container = ref(null);
const dropdownRef = ref(null);
const dropdownPanelStyle = ref({});

const unreadCount = computed(() => notifications.value.filter((n) => !n.read_at).length);

const repositionDropdown = () => {
    if (!open.value) {
        dropdownPanelStyle.value = {};
        return;
    }
    const btn = container.value?.querySelector?.('.bell-trigger');
    if (!btn || typeof btn.getBoundingClientRect !== 'function') {
        return;
    }
    const br = btn.getBoundingClientRect();
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const margin = 12;
    const gap = 10;
    const maxPanelW = 380;
    const width = Math.min(maxPanelW, vw - margin * 2);

    let left = br.left;
    if (left + width > vw - margin) {
        left = vw - margin - width;
    }
    if (left < margin) {
        left = margin;
    }

    let top = br.bottom + gap;
    let maxH = Math.min(460, vh - top - margin);
    if (maxH < 200 && br.top > margin + gap + 200) {
        maxH = Math.min(460, br.top - margin - gap);
        top = br.top - gap - maxH;
        if (top < margin) {
            top = margin;
            maxH = Math.min(460, br.top - margin - gap);
        }
    }

    dropdownPanelStyle.value = {
        position: 'fixed',
        left: `${Math.round(left)}px`,
        top: `${Math.round(top)}px`,
        width: `${Math.round(width)}px`,
        maxHeight: `${Math.max(160, Math.round(maxH))}px`,
        zIndex: 9999,
    };
};

const scheduleReposition = () => {
    nextTick(() => {
        requestAnimationFrame(() => repositionDropdown());
    });
};

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
        if (open.value) scheduleReposition();
    }
};

const toggleOpen = async () => {
    open.value = !open.value;
    if (open.value) {
        await fetchNotifications();
        scheduleReposition();
    } else {
        dropdownPanelStyle.value = {};
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
        const res = await apiFetch(`/notifications/${notification.id}/read`, { method: 'PATCH' });
        if (res.ok) {
            notification.read_at = new Date().toISOString();
        }
    } catch (error) {
        console.error('mark notification read', error);
    }
};

const markAllNotificationsRead = async () => {
    try {
        const res = await apiFetch('/notifications/read-all', { method: 'PATCH' });
        if (res.ok) {
            notifications.value = notifications.value.map((n) => ({
                ...n,
                read_at: n.read_at || new Date().toISOString(),
            }));
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

const fixEmbeddedTicketPriorityKeys = (text) => {
    if (!text || typeof text !== 'string') return text;
    return text.replace(/\bticket\.priorities\.([a-z0-9_]+)\b/gi, (_, rawCode) => {
        const code = String(rawCode).toLowerCase();
        const key = `ticket.priorities.${code}`;
        const translated = t(key);
        return translated !== key ? translated : code;
    });
};

const notificationTitle = (notification) => {
    if (notification?.title_key) {
        const template = t(notification.title_key);
        if (template === notification.title_key) {
            return fixEmbeddedTicketPriorityKeys(notification?.title || '');
        }
        return fixEmbeddedTicketPriorityKeys(translateTemplate(template, notification?.params || {}));
    }
    return fixEmbeddedTicketPriorityKeys(notification?.title || notification?.type || '');
};

const notificationBody = (notification) => {
    if (notification?.body_key) {
        const template = t(notification.body_key);
        if (template === notification.body_key) {
            return fixEmbeddedTicketPriorityKeys(notification?.body || '');
        }
        return fixEmbeddedTicketPriorityKeys(translateTemplate(template, notification?.params || {}));
    }
    return fixEmbeddedTicketPriorityKeys(notification?.body || '');
};

const relativeTime = (iso) => {
    if (!iso) return '';
    const date = new Date(iso);
    const diffMinutes = Math.round((date - Date.now()) / 1000 / 60);
    try {
        const rtfLocale = localeMap[locale.value] || 'fa-IR';
        return new Intl.RelativeTimeFormat(rtfLocale, { numeric: 'auto' }).format(diffMinutes, 'minute');
    } catch {
        return date.toLocaleString();
    }
};

const handleClickOutside = (event) => {
    if (!open.value) return;
    const root = container.value;
    const panel = dropdownRef.value;
    const target = event.target;
    if (root?.contains(target) || panel?.contains(target)) return;
    open.value = false;
};

const onViewportChange = () => {
    if (open.value) repositionDropdown();
};

watch(open, (v) => {
    if (v) scheduleReposition();
    else dropdownPanelStyle.value = {};
});

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('resize', onViewportChange);
    window.addEventListener('scroll', onViewportChange, true);
    fetchNotifications();
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('resize', onViewportChange);
    window.removeEventListener('scroll', onViewportChange, true);
});
</script>

<style scoped>
.notification-bell {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.bell-trigger {
    position: relative;
    width: 48px;
    height: 48px;
    border: none;
    border-radius: 12px;
    background: transparent;
    color: #334155;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: color 0.15s ease, transform 0.12s ease;
}

.bell-trigger:hover {
    color: #0f172a;
}

.bell-trigger:focus-visible {
    outline: 2px solid #0e7490;
    outline-offset: 2px;
}

.notification-bell--dark .bell-trigger {
    color: rgba(255, 255, 255, 0.92);
}

.notification-bell--dark .bell-trigger:hover {
    color: #fff;
}

.notification-bell--dark .bell-trigger:focus-visible {
    outline-color: rgba(255, 255, 255, 0.85);
}

.bell-svg {
    width: 28px;
    height: 28px;
    flex-shrink: 0;
}

.badge {
    position: absolute;
    top: 2px;
    inset-inline-end: 2px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ef4444;
    color: white;
    font-size: 0.65rem;
    font-weight: 700;
    border-radius: 999px;
    line-height: 1;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.dropdown {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(15, 23, 42, 0.2);
    border: 1px solid rgba(15, 23, 42, 0.08);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.dropdown-header {
    padding: 12px 16px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    font-weight: 700;
    flex-shrink: 0;
}

.dropdown-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.ghost-btn {
    border: none;
    background: transparent;
    font-weight: 600;
    color: #2563eb;
    cursor: pointer;
    font-size: 0.8rem;
    white-space: nowrap;
}

.dropdown-body {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
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
    text-align: start;
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
