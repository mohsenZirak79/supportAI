<template>
    <button
        class="floating-chat-launcher"
        :class="{ 'is-pulsing': pulse }"
        type="button"
        :aria-label="ariaLabel"
        @click="$emit('toggle')"
    >
        <span class="launcher-glow" aria-hidden="true"></span>
        <svg viewBox="0 0 24 24" class="launcher-icon" aria-hidden="true">
            <path d="M4 5.5A3.5 3.5 0 0 1 7.5 2h9A3.5 3.5 0 0 1 20 5.5v6A3.5 3.5 0 0 1 16.5 15H11l-4.25 3.45A1 1 0 0 1 5 17.67V15.9A3.5 3.5 0 0 1 4 13V5.5Z" />
            <path d="M15.6 6.7l.6 1.45 1.45.6-1.45.6-.6 1.45-.6-1.45-1.45-.6 1.45-.6.6-1.45Z" />
        </svg>
    </button>
</template>

<script setup>
defineProps({
    pulse: { type: Boolean, default: false },
    ariaLabel: { type: String, default: 'باز کردن چت شناور' },
});

defineEmits(['toggle']);
</script>

<style scoped>
.floating-chat-launcher {
    width: 62px;
    height: 62px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 999px;
    cursor: pointer;
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0f766e, #06b6d4);
    box-shadow:
        0 18px 42px rgba(14, 116, 144, 0.34),
        inset 0 1px 0 rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.floating-chat-launcher:hover {
    transform: translateY(-2px);
    box-shadow:
        0 22px 48px rgba(14, 116, 144, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

.floating-chat-launcher:active {
    transform: scale(0.97);
}

.floating-chat-launcher:focus-visible {
    outline: 3px solid rgba(14, 165, 233, 0.45);
    outline-offset: 2px;
}

.launcher-icon {
    width: 28px;
    height: 28px;
    fill: #fff;
}

.launcher-glow {
    position: absolute;
    inset: -7px;
    border-radius: inherit;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.42), transparent 70%);
    z-index: -1;
}

.is-pulsing {
    animation: launcher-pulse 2.2s ease-out 1;
}

@keyframes launcher-pulse {
    0% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.55); }
    70% { box-shadow: 0 0 0 16px rgba(56, 189, 248, 0); }
    100% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0); }
}
</style>
