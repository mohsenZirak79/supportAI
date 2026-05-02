<template>
    <transition name="greet-shell">
        <div
            v-if="visible"
            class="floating-chat-greeting-anchor"
            role="status"
            aria-live="polite"
        >
            <div class="greeting-bubble" :dir="textDir">
                <span class="greeting-shine" aria-hidden="true" />
                <span class="greeting-border-glow" aria-hidden="true" />
                <span class="greeting-inner">
                    <template v-for="(word, i) in words" :key="i">
                        <span
                            class="greeting-word"
                            :style="{ animationDelay: `${0.22 + i * 0.07}s` }"
                        >{{ word }}</span><span v-if="i < words.length - 1" class="greeting-space"> </span>
                    </template>
                </span>
            </div>
            <div class="greeting-stem" aria-hidden="true">
                <span class="greeting-stem-line" />
                <span class="greeting-stem-pulse" />
            </div>
        </div>
    </transition>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    visible: { type: Boolean, default: false },
    text: { type: String, default: '' },
    /** جهت متن داخل حباب (rtl برای فارسی/عربی) */
    textDir: { type: String, default: 'rtl' },
});

const words = computed(() => {
    const t = (props.text || '').trim();
    if (!t) return [];
    return t.split(/\s+/);
});
</script>

<style scoped>
.floating-chat-greeting-anchor {
    position: absolute;
    left: 0;
    right: auto;
    bottom: calc(100% + 8px);
    z-index: 3;
    pointer-events: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 62px;
    width: max-content;
    max-width: min(280px, calc(100vw - 24px));
    box-sizing: border-box;
}

.greeting-bubble {
    position: relative;
    perspective: 420px;
    box-sizing: border-box;
    max-width: 100%;
    border-radius: 16px;
    padding: 11px 15px;
    line-height: 1.55;
    overflow: hidden;
    overflow-wrap: anywhere;
    word-break: break-word;
    background: linear-gradient(145deg, rgba(22, 36, 54, 0.96) 0%, rgba(15, 23, 42, 0.94) 48%, rgba(17, 32, 48, 0.96) 100%);
    color: #f8fafc;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.01em;
    box-shadow:
        0 14px 36px rgba(2, 6, 23, 0.35),
        0 0 0 1px rgba(255, 255, 255, 0.08) inset,
        0 0 40px -8px rgba(45, 212, 191, 0.25);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.greeting-border-glow {
    position: absolute;
    inset: 0;
    border-radius: inherit;
    padding: 1px;
    background: linear-gradient(
        135deg,
        rgba(45, 212, 191, 0.55),
        rgba(56, 189, 248, 0.35),
        rgba(167, 139, 250, 0.4),
        rgba(45, 212, 191, 0.45)
    );
    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0.85;
    pointer-events: none;
    animation: border-glow-drift 5s ease-in-out infinite;
}

@keyframes border-glow-drift {
    0%,
    100% {
        filter: hue-rotate(0deg);
    }
    50% {
        filter: hue-rotate(12deg);
    }
}

.greeting-shine {
    position: absolute;
    inset: -40% -60%;
    background: linear-gradient(
        105deg,
        transparent 0%,
        transparent 42%,
        rgba(255, 255, 255, 0.14) 50%,
        transparent 58%,
        transparent 100%
    );
    transform: translateX(-35%) skewX(-12deg);
    animation: shine-sweep 1.35s cubic-bezier(0.22, 1, 0.36, 1) 0.45s both;
    pointer-events: none;
}

@keyframes shine-sweep {
    0% {
        transform: translateX(-55%) skewX(-12deg);
        opacity: 0;
    }
    15% {
        opacity: 1;
    }
    100% {
        transform: translateX(120%) skewX(-12deg);
        opacity: 0;
    }
}

.greeting-inner {
    position: relative;
    z-index: 1;
}

.greeting-word {
    display: inline-block;
    opacity: 0;
    transform: translateY(0.55em) rotateX(-22deg);
    transform-origin: 50% 100%;
    animation: word-pop 0.58s cubic-bezier(0.22, 1.2, 0.36, 1) both;
}

@keyframes word-pop {
    0% {
        opacity: 0;
        transform: translateY(0.55em) rotateX(-22deg) scale(0.92);
        filter: blur(4px);
    }
    70% {
        opacity: 1;
        filter: blur(0);
    }
    100% {
        opacity: 1;
        transform: translateY(0) rotateX(0) scale(1);
        filter: blur(0);
    }
}

.greeting-space {
    white-space: pre;
}

/* ساقهٔ نورانی تا آیکون */
.greeting-stem {
    position: relative;
    width: 4px;
    height: 16px;
    margin-top: 2px;
    flex-shrink: 0;
}

.greeting-stem-line {
    position: absolute;
    left: 50%;
    top: 0;
    width: 3px;
    height: 100%;
    margin-left: -1.5px;
    border-radius: 99px;
    background: linear-gradient(
        180deg,
        rgba(45, 212, 191, 0.85) 0%,
        rgba(14, 165, 233, 0.45) 70%,
        rgba(14, 165, 233, 0.15) 100%
    );
    transform-origin: top center;
    transform: scaleY(0);
    animation: stem-draw 0.42s cubic-bezier(0.22, 1, 0.36, 1) 0.12s both;
    box-shadow: 0 0 12px rgba(45, 212, 191, 0.45);
}

.greeting-stem-pulse {
    position: absolute;
    left: 50%;
    top: 0;
    width: 10px;
    height: 10px;
    margin-left: -5px;
    margin-top: -2px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(45, 212, 191, 0.7), transparent 70%);
    opacity: 0;
    animation: stem-dot 0.55s cubic-bezier(0.22, 1, 0.36, 1) 0.38s both;
}

@keyframes stem-draw {
    to {
        transform: scaleY(1);
    }
}

@keyframes stem-dot {
    0% {
        opacity: 0;
        transform: scale(0.4);
    }
    40% {
        opacity: 1;
        transform: scale(1.15);
    }
    100% {
        opacity: 0.55;
        transform: scale(1);
    }
}

/* ورود کل بلوک: فنر + محو شدن تاری */
.greet-shell-enter-active {
    transition:
        opacity 0.48s cubic-bezier(0.22, 1, 0.36, 1),
        transform 0.58s cubic-bezier(0.22, 1.15, 0.36, 1),
        filter 0.5s ease-out;
}

.greet-shell-leave-active {
    transition:
        opacity 0.22s ease,
        transform 0.24s ease,
        filter 0.2s ease;
}

.greet-shell-enter-from {
    opacity: 0;
    transform: translateY(18px) scale(0.88);
    filter: blur(10px);
}

.greet-shell-enter-to {
    opacity: 1;
    transform: translateY(0) scale(1);
    filter: blur(0);
}

.greet-shell-leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
    filter: blur(0);
}

.greet-shell-leave-to {
    opacity: 0;
    transform: translateY(8px) scale(0.94);
    filter: blur(6px);
}

@media (prefers-reduced-motion: reduce) {
    .greet-shell-enter-active,
    .greet-shell-leave-active {
        transition: opacity 0.2s ease;
    }

    .greet-shell-enter-from,
    .greet-shell-leave-to {
        transform: none;
        filter: none;
    }

    .greeting-word {
        animation: none;
        opacity: 1;
        transform: none;
        filter: none;
    }

    .greeting-shine,
    .greeting-border-glow {
        animation: none;
    }

    .greeting-shine {
        display: none;
    }

    .greeting-stem-line {
        animation: none;
        transform: scaleY(1);
    }

    .greeting-stem-pulse {
        animation: none;
        opacity: 0.4;
        transform: scale(1);
    }
}
</style>
