<template>
    <transition name="greet-leave-shell">
        <div
            v-if="visible"
            ref="anchorRef"
            class="greeting-anchor"
            role="status"
            aria-live="polite"
            :style="anchorShiftStyle"
        >
            <div class="greeting-pulse-wrap" :class="{ 'greeting-pulse-wrap--idle': idleAttention }">
                <div
                    class="greeting-morph"
                    :dir="textDir"
                    @animationend="onMorphAnimationEnd"
                >
                    <span class="greeting-text">{{ text }}</span>
                    <span class="greeting-tail" aria-hidden="true" />
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    visible: { type: Boolean, default: false },
    text: { type: String, default: '' },
    textDir: { type: String, default: 'rtl' },
});

const anchorRef = ref(null);
/** جابه‌جایی افقی (px) تا حباب از لبهٔ چپ/راست صفحه بیرون نزند */
const shiftX = ref(0);
const idleAttention = ref(false);

const anchorShiftStyle = computed(() =>
    shiftX.value ? { transform: `translateX(${shiftX.value}px)` } : {}
);

function clampIntoViewport() {
    const root = anchorRef.value;
    if (!root || typeof window === 'undefined') return;
    const bubble = root.querySelector('.greeting-morph');
    if (!bubble) return;
    const br = bubble.getBoundingClientRect();
    const margin = 12;
    let dx = 0;
    if (br.left < margin) {
        dx = margin - br.left;
    } else if (br.right > window.innerWidth - margin) {
        dx = window.innerWidth - margin - br.right;
    }
    shiftX.value = dx;
}

function onMorphAnimationEnd(ev) {
    if (ev.animationName === 'greet-morph-in' || ev.animationName === 'greet-morph-reduced') {
        idleAttention.value = true;
    }
}

watch(
    () => props.visible,
    async (v) => {
        if (v) {
            idleAttention.value = false;
            shiftX.value = 0;
            await nextTick();
            requestAnimationFrame(() => {
                clampIntoViewport();
                requestAnimationFrame(clampIntoViewport);
            });
        } else {
            idleAttention.value = false;
        }
    }
);

onMounted(() => {
    window.addEventListener('resize', clampIntoViewport);
    window.addEventListener('orientationchange', clampIntoViewport);
});

onUnmounted(() => {
    window.removeEventListener('resize', clampIntoViewport);
    window.removeEventListener('orientationchange', clampIntoViewport);
});
</script>

<style scoped>
/* بالا + راست آیکون */
.greeting-anchor {
    position: absolute;
    right: 0;
    left: auto;
    bottom: calc(100% + 12px);
    z-index: 3;
    pointer-events: none;
    max-width: min(300px, calc(100vw - 20px));
    width: max-content;
    min-width: 0;
    box-sizing: border-box;
}

.greeting-pulse-wrap {
    transform-origin: 100% 100%;
}

.greeting-pulse-wrap--idle {
    animation: greet-attention 5.2s ease-in-out infinite;
}

@keyframes greet-attention {
    0%,
    86%,
    100% {
        transform: translateY(0) scale(1);
        filter: drop-shadow(0 4px 14px rgba(15, 23, 42, 0.08));
    }
    5% {
        transform: translateY(-4px) scale(1.045);
        filter: drop-shadow(0 10px 26px rgba(15, 23, 42, 0.14));
    }
    11% {
        transform: translateY(0) scale(1);
        filter: drop-shadow(0 4px 14px rgba(15, 23, 42, 0.08));
    }
}

.greeting-morph {
    --bubble-bg: #eceef2;
    --bubble-border: #d8dce3;
    --bubble-text: #1e293b;
    position: relative;
    box-sizing: border-box;
    max-width: 100%;
    padding: 10px 18px 15px;
    line-height: 1.55;
    overflow-wrap: anywhere;
    word-break: break-word;
    color: var(--bubble-text);
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.01em;
    transform-origin: 100% 100%;
    border-radius: 999px;
    background: var(--bubble-bg);
    border: 1px solid var(--bubble-border);
    box-shadow: 0 2px 0 rgba(255, 255, 255, 0.8) inset;
    filter: drop-shadow(0 4px 14px rgba(15, 23, 42, 0.1));
    animation: greet-morph-in 0.68s cubic-bezier(0.32, 1.12, 0.28, 1) both;
}

/* دُم پایین‌چپ (مثلث) رو به آیکون */
.greeting-tail {
    position: absolute;
    left: 20px;
    bottom: -7px;
    width: 0;
    height: 0;
    border-left: 7px solid transparent;
    border-right: 7px solid transparent;
    border-top: 8px solid var(--bubble-bg);
    pointer-events: none;
}

@keyframes greet-morph-in {
    0% {
        opacity: 0;
        transform: scale(0.36);
        border-radius: 999px;
        filter: blur(3px) drop-shadow(0 4px 12px rgba(15, 23, 42, 0.06));
    }
    40% {
        opacity: 1;
        filter: blur(0) drop-shadow(0 4px 14px rgba(15, 23, 42, 0.1));
    }
    70% {
        transform: scale(1.03);
        border-radius: 999px;
    }
    100% {
        opacity: 1;
        transform: scale(1);
        border-radius: 999px;
        filter: blur(0) drop-shadow(0 4px 14px rgba(15, 23, 42, 0.1));
    }
}

.greeting-text {
    position: relative;
    z-index: 1;
    display: block;
    opacity: 0;
    transform: translateY(3px);
    animation: greet-text-in 0.38s cubic-bezier(0.22, 1, 0.36, 1) 0.16s both;
}

@keyframes greet-text-in {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.greet-leave-shell-leave-active .greeting-morph {
    transition:
        transform 0.28s cubic-bezier(0.55, 0, 1, 0.45),
        opacity 0.24s ease,
        filter 0.22s ease,
        border-radius 0.26s ease;
}

.greet-leave-shell-leave-to .greeting-morph {
    transform: scale(0.4);
    opacity: 0;
    border-radius: 999px;
    filter: blur(4px);
}

.greet-leave-shell-leave-to .greeting-text {
    opacity: 0;
    transition: opacity 0.12s ease;
}

@media (prefers-reduced-motion: reduce) {
    .greeting-pulse-wrap--idle {
        animation: none;
    }

    .greeting-morph {
        animation: greet-morph-reduced 0.22s ease both;
    }

    @keyframes greet-morph-reduced {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .greeting-text {
        animation: none;
        opacity: 1;
        transform: none;
    }

    .greet-leave-shell-leave-active .greeting-morph {
        transition: opacity 0.18s ease;
    }

    .greet-leave-shell-leave-to .greeting-morph {
        transform: none;
        filter: none;
    }
}
</style>
