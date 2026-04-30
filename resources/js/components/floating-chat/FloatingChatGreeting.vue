<template>
    <transition name="greeting-fade">
        <div v-if="visible" class="floating-chat-greeting-anchor" role="status" aria-live="polite">
            <div class="floating-chat-greeting">{{ text }}</div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    visible: { type: Boolean, default: false },
    text: { type: String, default: '' },
});
</script>

<style scoped>
.floating-chat-greeting-anchor {
    position: absolute;
    right: 0;
    bottom: calc(100% + 10px);
    left: auto;
    width: max-content;
    max-width: min(220px, calc(100vw - 48px));
    z-index: 3;
    pointer-events: none;
}

.floating-chat-greeting {
    border-radius: 14px;
    background: rgba(15, 23, 42, 0.9);
    color: #fff;
    font-size: 13px;
    padding: 10px 12px;
    line-height: 1.45;
    box-shadow: 0 14px 28px rgba(2, 6, 23, 0.28);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}

.greeting-fade-enter-active,
.greeting-fade-leave-active {
    transition: opacity 0.22s ease;
}

.greeting-fade-enter-from,
.greeting-fade-leave-to {
    opacity: 0;
}
</style>
