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
/** جابه‌جایی افقی (px) — لبهٔ چپ/راست داخل viewport + safe-area */
const shiftX = ref(0);
const idleAttention = ref(false);

let resizeObserver = null;
let clampTimerIds = [];

function clearClampTimers() {
    clampTimerIds.forEach((id) => clearTimeout(id));
    clampTimerIds = [];
}

function viewportXMargins() {
    if (typeof document === 'undefined') return { left: 12, right: 12 };
    const cs = getComputedStyle(document.documentElement);
    const sl = Number.parseFloat(cs.getPropertyValue('env(safe-area-inset-left)')) || 0;
    const sr = Number.parseFloat(cs.getPropertyValue('env(safe-area-inset-right)')) || 0;
    return {
        left: Math.max(10, sl + 8),
        right: Math.max(10, sr + 8),
    };
}

function clampIntoViewport() {
    const root = anchorRef.value;
    if (!root || typeof window === 'undefined') return;
    const bubble = root.querySelector('.greeting-morph');
    if (!bubble) return;
    const br = bubble.getBoundingClientRect();
    const { left: mLeft, right: mRight } = viewportXMargins();
    const vw = window.visualViewport?.width ?? window.innerWidth;
    let dx = 0;
    if (br.left < mLeft) {
        dx = mLeft - br.left;
    } else if (br.right > vw - mRight) {
        dx = vw - mRight - br.right;
    }
    shiftX.value = dx;
}

function scheduleClampPasses() {
    clearClampTimers();
    const delays = [0, 40, 120, 280, 520, 900];
    delays.forEach((ms) => {
        clampTimerIds.push(
            window.setTimeout(() => {
                clampIntoViewport();
            }, ms)
        );
    });
}

function bindResizeObserver() {
    unbindResizeObserver();
    const el = anchorRef.value;
    if (!el || typeof ResizeObserver === 'undefined') return;
    resizeObserver = new ResizeObserver(() => clampIntoViewport());
    resizeObserver.observe(el);
}

function unbindResizeObserver() {
    if (resizeObserver) {
        resizeObserver.disconnect();
        resizeObserver = null;
    }
}

const anchorShiftStyle = computed(() => ({
    '--greet-shift-x': `${shiftX.value}px`,
}));

function onMorphAnimationEnd(ev) {
    if (ev.animationName === 'greet-morph-in' || ev.animationName === 'greet-morph-reduced') {
        idleAttention.value = true;
        clampIntoViewport();
        scheduleClampPasses();
    }
}

watch(
    () => props.visible,
    async (v) => {
        if (v) {
            idleAttention.value = false;
            shiftX.value = 0;
            await nextTick();
            scheduleClampPasses();
            requestAnimationFrame(() => {
                clampIntoViewport();
                requestAnimationFrame(clampIntoViewport);
            });
            bindResizeObserver();
        } else {
            idleAttention.value = false;
            clearClampTimers();
            unbindResizeObserver();
        }
    }
);

function onWinResize() {
    if (props.visible) clampIntoViewport();
}

onMounted(() => {
    window.addEventListener('resize', onWinResize);
    window.addEventListener('orientationchange', onWinResize);
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', onWinResize);
        window.visualViewport.addEventListener('scroll', onWinResize);
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', onWinResize);
    window.removeEventListener('orientationchange', onWinResize);
    if (window.visualViewport) {
        window.visualViewport.removeEventListener('resize', onWinResize);
        window.visualViewport.removeEventListener('scroll', onWinResize);
    }
    clearClampTimers();
    unbindResizeObserver();
});

watch(
    () => props.text,
    () => {
        if (!props.visible) return;
        nextTick(() => {
            clampIntoViewport();
            scheduleClampPasses();
        });
    }
);
</script>

<style scoped>
.greeting-anchor {
    position: absolute;
    right: 0;
    left: auto;
    bottom: calc(100% + 12px);
    z-index: 3;
    pointer-events: none;
    box-sizing: border-box;
    width: min(300px, calc(100dvw - 16px), calc(100vw - 16px));
    max-width: min(300px, calc(100dvw - 16px), calc(100vw - 16px));
    min-width: 0;
    transform: translateX(var(--greet-shift-x, 0px));
}

.greeting-pulse-wrap {
    transform-origin: 100% 100%;
    width: 100%;
    max-width: 100%;
}

.greeting-pulse-wrap--idle {
    animation: greet-attention 2.35s ease-in-out infinite;
}

@keyframes greet-attention {
    0%,
    72%,
    100% {
        transform: translateY(0) scale(1);
        filter: drop-shadow(0 6px 18px rgba(15, 23, 42, 0.14));
    }
    7% {
        transform: translateY(-9px) scale(1.09);
        filter: drop-shadow(0 16px 36px rgba(15, 118, 110, 0.28));
    }
    15% {
        transform: translateY(0) scale(1);
        filter: drop-shadow(0 6px 18px rgba(15, 23, 42, 0.14));
    }
    22% {
        transform: translateY(-4px) scale(1.04);
        filter: drop-shadow(0 10px 26px rgba(15, 23, 42, 0.18));
    }
    28% {
        transform: translateY(0) scale(1);
        filter: drop-shadow(0 6px 18px rgba(15, 23, 42, 0.14));
    }
}

.greeting-morph {
    --bubble-bg: #d3dce8;
    --bubble-bg2: #c5d0e0;
    --bubble-border: #8b9cb3;
    --bubble-text: #0f172a;
    position: relative;
    box-sizing: border-box;
    width: fit-content;
    max-width: 100%;
    margin-inline-start: auto;
    padding: 10px 18px 15px;
    line-height: 1.55;
    overflow-wrap: anywhere;
    word-break: break-word;
    color: var(--bubble-text);
    font-size: clamp(12px, 2.9vw, 13px);
    font-weight: 600;
    letter-spacing: 0.01em;
    transform-origin: 100% 100%;
    border-radius: 999px;
    background: linear-gradient(165deg, var(--bubble-bg) 0%, var(--bubble-bg2) 100%);
    border: 1px solid var(--bubble-border);
    box-shadow:
        0 1px 0 rgba(255, 255, 255, 0.65) inset,
        0 6px 22px rgba(15, 23, 42, 0.14);
    filter: drop-shadow(0 3px 10px rgba(15, 23, 42, 0.12));
    animation: greet-morph-in 0.68s cubic-bezier(0.32, 1.12, 0.28, 1) both;
}

.greeting-tail {
    position: absolute;
    left: 20px;
    bottom: -7px;
    width: 0;
    height: 0;
    border-left: 7px solid transparent;
    border-right: 7px solid transparent;
    border-top: 8px solid var(--bubble-bg2);
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
        filter: blur(0) drop-shadow(0 3px 10px rgba(15, 23, 42, 0.12));
    }
    70% {
        transform: scale(1.03);
        border-radius: 999px;
    }
    100% {
        opacity: 1;
        transform: scale(1);
        border-radius: 999px;
        filter: blur(0) drop-shadow(0 3px 10px rgba(15, 23, 42, 0.12));
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

@media (max-width: 400px) {
    .greeting-anchor {
        width: min(calc(100dvw - 12px), calc(100vw - 12px));
        max-width: min(calc(100dvw - 12px), calc(100vw - 12px));
    }

    .greeting-morph {
        padding: 9px 14px 14px;
    }

    .greeting-tail {
        left: 16px;
    }
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
