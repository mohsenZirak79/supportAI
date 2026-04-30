<template>
    <aside class="cg-sidebar" :class="{ 'cg-sidebar--open': isOpen, 'cg-sidebar--mobile': isMobile }">
        <div class="cg-sidebar-top">
            <button type="button" class="cg-sidebar-new" :aria-label="newChatLabel" @click="$emit('new-chat')">
                <span class="cg-sidebar-new-icon" aria-hidden="true">+</span>
                {{ newChatText }}
            </button>
            <div class="cg-sidebar-search-wrap">
                <label class="cg-sr-only" for="cg-chat-search">{{ searchLabel }}</label>
                <input
                    id="cg-chat-search"
                    v-model="searchQuery"
                    class="cg-sidebar-search"
                    type="search"
                    autocomplete="off"
                    :placeholder="searchPlaceholder"
                />
            </div>
        </div>
        <div class="cg-sidebar-list" role="list">
            <div
                v-for="chat in chats"
                :key="chat.id"
                class="cg-sidebar-item"
                :class="{ 'cg-sidebar-item--active': chat.id === activeChatId }"
                role="listitem"
                tabindex="0"
                @click="$emit('select', chat.id)"
                @keydown.enter.prevent="$emit('select', chat.id)"
                @keydown.space.prevent="$emit('select', chat.id)"
            >
                <span class="cg-sidebar-item-title">{{ chat.title }}</span>
                <button
                    type="button"
                    class="cg-sidebar-item-more"
                    :aria-label="actionsLabel"
                    @click.stop="$emit('open-actions', chat)"
                >
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
                        <circle cx="12" cy="5" r="1.5" />
                        <circle cx="12" cy="12" r="1.5" />
                        <circle cx="12" cy="19" r="1.5" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="cg-sidebar-footer">
            <button type="button" class="cg-sidebar-user" :aria-label="accountLabel" @click="$emit('open-settings')">
                <span v-if="userAvatar" class="cg-sidebar-avatar">
                    <img :src="userAvatar" :alt="userName" />
                </span>
                <span v-else class="cg-sidebar-avatar cg-sidebar-avatar--ph" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </span>
                <span class="cg-sidebar-user-name">{{ userName }}</span>
                <span v-if="referralDot" class="cg-sidebar-dot" aria-hidden="true" />
            </button>
        </div>
    </aside>
</template>

<script setup>
const searchQuery = defineModel('searchQuery', { type: String, default: '' });

defineProps({
    chats: { type: Array, default: () => [] },
    activeChatId: { type: [String, Number], default: null },
    isOpen: { type: Boolean, default: true },
    isMobile: { type: Boolean, default: false },
    newChatText: { type: String, default: 'چت جدید' },
    newChatLabel: { type: String, default: 'شروع گفتگوی جدید' },
    searchPlaceholder: { type: String, default: 'جستجوی گفتگوها' },
    searchLabel: { type: String, default: 'جستجو' },
    actionsLabel: { type: String, default: 'گزینه‌های گفتگو' },
    userName: { type: String, default: '' },
    userAvatar: { type: String, default: null },
    accountLabel: { type: String, default: 'حساب و تنظیمات' },
    referralDot: { type: Boolean, default: false },
});

defineEmits(['new-chat', 'select', 'open-actions', 'open-settings']);
</script>

<style scoped>
.cg-sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.cg-sidebar {
    width: 280px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    background: var(--cg-sidebar-bg, #f8fafc);
    border-inline-end: 1px solid var(--cg-border, rgba(148, 163, 184, 0.22));
    min-height: 0;
}

.cg-sidebar--mobile {
    --cg-drawer-shift: -1;
    position: fixed;
    inset-block: var(--cg-header-total, 56px) 0;
    inset-inline-start: 0;
    width: min(300px, 88vw);
    z-index: 40;
    box-shadow: var(--cg-shadow-drawer, 8px 0 32px rgba(15, 23, 42, 0.12));
    transform: translateX(calc(-100% * var(--cg-drawer-shift)));
    transition: transform 0.22s ease;
}

[dir='rtl'] .cg-sidebar--mobile {
    --cg-drawer-shift: 1;
}

.cg-sidebar--mobile.cg-sidebar--open {
    transform: translateX(0);
}

.cg-sidebar-top {
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex-shrink: 0;
}

.cg-sidebar-new {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 11px 14px;
    border: 0;
    border-radius: 12px;
    font: inherit;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(135deg, var(--cg-brand, #0f766e), var(--cg-brand-2, #0ea5e9));
    box-shadow: 0 4px 14px rgba(15, 118, 110, 0.25);
    transition: transform 0.12s ease, filter 0.15s ease;
}

.cg-sidebar-new:hover {
    filter: brightness(1.03);
}

.cg-sidebar-new:active {
    transform: scale(0.98);
}

.cg-sidebar-new-icon {
    font-size: 1.15rem;
    line-height: 1;
}

.cg-sidebar-search {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid var(--cg-border, rgba(148, 163, 184, 0.28));
    background: var(--cg-surface-0, #fff);
    font: inherit;
    font-size: 0.875rem;
}

.cg-sidebar-search:focus {
    outline: none;
    border-color: var(--cg-brand, #0f766e);
    box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
}

.cg-sidebar-list {
    flex: 1;
    overflow-y: auto;
    padding: 4px 8px 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.cg-sidebar-item {
    display: flex;
    align-items: center;
    gap: 6px;
    width: 100%;
    text-align: inherit;
    padding: 10px 10px 10px 8px;
    border: 0;
    border-radius: 10px;
    background: transparent;
    font: inherit;
    font-size: 0.9rem;
    color: var(--cg-ink, #0f172a);
    cursor: pointer;
    transition: background 0.12s ease;
}

.cg-sidebar-item:hover {
    background: rgba(255, 255, 255, 0.7);
}

.cg-sidebar-item--active {
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
}

.cg-sidebar-item-title {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cg-sidebar-item-more {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: var(--cg-ink-muted, #64748b);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    opacity: 0.85;
}

.cg-sidebar-item-more:hover {
    background: rgba(241, 245, 249, 0.95);
    color: var(--cg-ink, #0f172a);
}

.cg-sidebar-footer {
    padding: 10px 12px;
    padding-bottom: max(10px, env(safe-area-inset-bottom));
    border-top: 1px solid var(--cg-border, rgba(148, 163, 184, 0.18));
    flex-shrink: 0;
}

.cg-sidebar-user {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 8px 10px;
    border: 0;
    border-radius: 12px;
    background: transparent;
    font: inherit;
    cursor: pointer;
    color: var(--cg-ink, #0f172a);
    text-align: inherit;
    position: relative;
}

.cg-sidebar-user:hover {
    background: rgba(255, 255, 255, 0.75);
}

.cg-sidebar-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
    background: rgba(15, 118, 110, 0.12);
}

.cg-sidebar-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cg-sidebar-avatar--ph {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--cg-brand, #0f766e);
}

.cg-sidebar-user-name {
    flex: 1;
    min-width: 0;
    font-size: 0.875rem;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cg-sidebar-dot {
    position: absolute;
    inset-inline-end: 8px;
    top: 8px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #0ea5e9;
}
</style>
