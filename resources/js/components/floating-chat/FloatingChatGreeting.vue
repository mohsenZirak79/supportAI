<template>
    <transition name="greeting-fade">
        <div v-if="visible" class="floating-chat-greeting" role="status" aria-live="polite">
            {{ text }}
        </div>
    </transition>
</template>

<script setup>
defineProps({
    visible: { type: Boolean, default: false },
    text: { type: String, default: 'نیاز به کمک داری؟' },
});
</script>

<style scoped>
.floating-chat-greeting {
    max-width: 220px;
    border-radius: 14px;
    background: rgba(15, 23, 42, 0.9);
    color: #fff;
    font-size: 13px;
    padding: 10px 12px;
    box-shadow: 0 14px 28px rgba(2, 6, 23, 0.28);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}

.greeting-fade-enter-active,
.greeting-fade-leave-active {
    transition: all 0.25s ease;
}
.greeting-fade-enter-from,
.greeting-fade-leave-to {
    opacity: 0;
    transform: translateY(8px);
}
</style>
