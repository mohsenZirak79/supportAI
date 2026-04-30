<template>
    <header class="cg-header">
        <div class="cg-header-start">
            <button
                v-if="showMenuToggle"
                type="button"
                class="cg-header-icon"
                :aria-label="menuLabel"
                :aria-expanded="sidebarOpen ? 'true' : 'false'"
                @click="$emit('toggle-sidebar')"
            >
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="4" y1="6" x2="20" y2="6" />
                    <line x1="4" y1="12" x2="20" y2="12" />
                    <line x1="4" y1="18" x2="20" y2="18" />
                </svg>
            </button>
            <h1 class="cg-header-title">{{ title }}</h1>
        </div>
        <div class="cg-header-end">
            <slot name="notifications" />
            <button type="button" class="cg-header-icon" :aria-label="settingsLabel" :title="settingsLabel" @click="$emit('open-settings')">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="3" />
                    <path
                        d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"
                    />
                </svg>
            </button>
        </div>
    </header>
</template>

<script setup>
defineProps({
    title: { type: String, default: '' },
    showMenuToggle: { type: Boolean, default: true },
    sidebarOpen: { type: Boolean, default: false },
    menuLabel: { type: String, default: 'منو' },
    settingsLabel: { type: String, default: 'تنظیمات' },
});

defineEmits(['toggle-sidebar', 'open-settings']);
</script>

<style scoped>
.cg-header {
    height: var(--cg-header-h, 56px);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-inline: max(16px, env(safe-area-inset-inline));
    padding-block: 0;
    padding-top: env(safe-area-inset-top, 0px);
    border-bottom: 1px solid var(--cg-border, rgba(148, 163, 184, 0.2));
    background: var(--cg-surface-0, #fff);
    z-index: 5;
}

.cg-header-start,
.cg-header-end {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.cg-header-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--cg-ink, #0f172a);
}

.cg-header-icon {
    width: 40px;
    height: 40px;
    border: 0;
    border-radius: 10px;
    background: transparent;
    color: var(--cg-ink-muted, #475569);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background 0.15s ease, color 0.15s ease;
}

.cg-header-icon:hover {
    background: rgba(241, 245, 249, 0.95);
    color: var(--cg-brand, #0f766e);
}

.cg-header-icon:focus-visible {
    outline: 2px solid var(--cg-brand, #0f766e);
    outline-offset: 2px;
}
</style>
