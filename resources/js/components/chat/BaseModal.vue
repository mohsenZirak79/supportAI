<template>
    <Teleport to="body">
        <Transition name="cg-modal-fade">
            <div
                v-if="open"
                class="cg-modal-backdrop"
                :class="{ 'cg-modal-backdrop--sheet': isMobile }"
                role="presentation"
                @click="onBackdrop"
            >
                <div
                    ref="panelRef"
                    class="cg-modal-panel"
                    :class="[
                        `cg-modal-panel--${size}`,
                        { 'cg-modal-panel--sheet': isMobile, 'cg-modal-panel--danger': variant === 'danger' },
                    ]"
                    role="dialog"
                    aria-modal="true"
                    :aria-labelledby="titleId"
                    @click.stop
                >
                    <header v-if="title || $slots.title || showClose" class="cg-modal-header">
                        <div :id="titleId" class="cg-modal-title">
                            <slot name="title">{{ title }}</slot>
                        </div>
                        <button
                            v-if="showClose"
                            type="button"
                            class="cg-modal-close"
                            :aria-label="closeLabel"
                            @click="close"
                        >
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M18 6L6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </header>
                    <div class="cg-modal-body">
                        <slot />
                    </div>
                    <footer v-if="$slots.footer" class="cg-modal-footer">
                        <slot name="footer" />
                    </footer>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, computed, nextTick } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: '' },
    size: { type: String, default: 'md' }, // sm | md | lg | xl
    closeOnBackdrop: { type: Boolean, default: true },
    showClose: { type: Boolean, default: true },
    closeLabel: { type: String, default: 'بستن' },
    variant: { type: String, default: 'default' }, // default | danger
    /** Breakpoint for bottom-sheet layout (px) */
    mobileBreakpoint: { type: Number, default: 768 },
});

const emit = defineEmits(['update:open', 'close']);

const panelRef = ref(null);
const titleId = `cg-modal-title-${Math.random().toString(36).slice(2, 9)}`;
const isMobile = ref(false);

const updateMobile = () => {
    if (typeof window === 'undefined') return;
    isMobile.value = window.innerWidth <= props.mobileBreakpoint;
};

const close = () => {
    emit('update:open', false);
    emit('close');
};

const onBackdrop = () => {
    if (props.closeOnBackdrop) close();
};

const focusables = () => {
    const el = panelRef.value;
    if (!el) return [];
    return Array.from(
        el.querySelectorAll(
            'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
        ),
    ).filter((n) => n.offsetParent !== null || n === document.activeElement);
};

let prevActive = null;

watch(
    () => props.open,
    async (v) => {
        if (typeof document === 'undefined') return;
        updateMobile();
        if (v) {
            prevActive = document.activeElement;
            document.body.style.overflow = 'hidden';
            await nextTick();
            const list = focusables();
            (list[0] || panelRef.value)?.focus?.();
        } else {
            document.body.style.overflow = '';
            if (prevActive && typeof prevActive.focus === 'function') {
                try {
                    prevActive.focus();
                } catch {
                    /* ignore */
                }
            }
            prevActive = null;
        }
    },
);

const onKeydown = (e) => {
    if (!props.open) return;
    if (e.key === 'Escape') {
        e.preventDefault();
        close();
        return;
    }
    if (e.key !== 'Tab' || !panelRef.value) return;
    const list = focusables();
    if (list.length === 0) return;
    const first = list[0];
    const last = list[list.length - 1];
    if (e.shiftKey) {
        if (document.activeElement === first) {
            e.preventDefault();
            last.focus();
        }
    } else if (document.activeElement === last) {
        e.preventDefault();
        first.focus();
    }
};

onMounted(() => {
    updateMobile();
    window.addEventListener('resize', updateMobile);
    document.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    window.removeEventListener('resize', updateMobile);
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<style scoped>
.cg-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 3000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    padding-inline: max(24px, env(safe-area-inset-inline));
    padding-block: max(24px, env(safe-area-inset-block));
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}

.cg-modal-backdrop--sheet {
    align-items: flex-end;
    padding: 0;
}

.cg-modal-panel {
    width: min(100%, 440px);
    max-height: min(88vh, 720px);
    display: flex;
    flex-direction: column;
    background: var(--cg-surface-elevated, #fff);
    color: var(--cg-ink, #0f172a);
    border-radius: 16px;
    box-shadow: var(--cg-shadow-modal, 0 24px 64px rgba(15, 23, 42, 0.18));
    border: 1px solid var(--cg-border, rgba(148, 163, 184, 0.22));
    overflow: hidden;
}

.cg-modal-panel--sm {
    width: min(100%, 360px);
}
.cg-modal-panel--md {
    width: min(100%, 440px);
}
.cg-modal-panel--lg {
    width: min(100%, 560px);
}
.cg-modal-panel--xl {
    width: min(100%, 720px);
}

.cg-modal-panel--sheet {
    width: 100%;
    max-width: 100%;
    max-height: 85vh;
    border-radius: 16px 16px 0 0;
    margin-inline: 0;
}

.cg-modal-panel--danger {
    border-color: rgba(220, 38, 38, 0.25);
}

.cg-modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--cg-border, rgba(148, 163, 184, 0.18));
    flex-shrink: 0;
}

.cg-modal-title {
    font-size: 1.05rem;
    font-weight: 600;
    line-height: 1.35;
    margin: 0;
}

.cg-modal-close {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border: 0;
    border-radius: 10px;
    background: var(--cg-surface-muted, rgba(241, 245, 249, 0.9));
    color: inherit;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s ease, transform 0.12s ease;
}

.cg-modal-close:hover {
    background: rgba(226, 232, 240, 1);
}

.cg-modal-close:active {
    transform: scale(0.96);
}

.cg-modal-close:focus-visible {
    outline: 2px solid var(--cg-brand, #0f766e);
    outline-offset: 2px;
}

.cg-modal-body {
    padding: 16px 20px;
    overflow: auto;
    flex: 1;
    min-height: 0;
}

.cg-modal-footer {
    padding: 12px 20px 16px;
    border-top: 1px solid var(--cg-border, rgba(148, 163, 184, 0.18));
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
    flex-shrink: 0;
}

.cg-modal-fade-enter-active,
.cg-modal-fade-leave-active {
    transition: opacity 0.2s ease;
}

.cg-modal-fade-enter-active .cg-modal-panel,
.cg-modal-fade-leave-active .cg-modal-panel {
    transition: transform 0.22s ease, opacity 0.2s ease;
}

.cg-modal-fade-enter-from,
.cg-modal-fade-leave-to {
    opacity: 0;
}

.cg-modal-fade-enter-from .cg-modal-panel,
.cg-modal-fade-leave-to .cg-modal-panel {
    transform: translateY(12px) scale(0.98);
    opacity: 0.85;
}

.cg-modal-backdrop:not(.cg-modal-backdrop--sheet) .cg-modal-fade-enter-from .cg-modal-panel,
.cg-modal-backdrop:not(.cg-modal-backdrop--sheet) .cg-modal-fade-leave-to .cg-modal-panel {
    transform: scale(0.98);
}
</style>
