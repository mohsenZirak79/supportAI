<template>
    <div class="cg-typing" role="status" aria-live="polite" :aria-label="label">
        <span class="cg-typing-dot" />
        <span class="cg-typing-dot" />
        <span class="cg-typing-dot" />
    </div>
</template>

<script setup>
defineProps({
    label: { type: String, default: 'در حال فکر کردن' },
});
</script>

<style scoped>
.cg-typing {
    display: inline-flex;
    gap: 6px;
    align-items: center;
    padding: 8px 4px;
}

.cg-typing-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--cg-brand-muted, rgba(15, 118, 110, 0.45));
    animation: cg-typing-bounce 1.1s ease-in-out infinite;
}

.cg-typing-dot:nth-child(2) {
    animation-delay: 0.15s;
}
.cg-typing-dot:nth-child(3) {
    animation-delay: 0.3s;
}

@keyframes cg-typing-bounce {
    0%,
    80%,
    100% {
        transform: translateY(0);
        opacity: 0.45;
    }
    40% {
        transform: translateY(-5px);
        opacity: 1;
    }
}
</style>
