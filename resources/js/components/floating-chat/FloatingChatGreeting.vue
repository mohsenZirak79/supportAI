<template>
    <!-- الگوی morph از FAB: از نقطهٔ اتصال به آیکون بزرگ می‌شود (شبیه Material / iOS sheet از دکمه) -->
    <transition name="greet-leave-shell">
        <div
            v-if="visible"
            class="greeting-anchor"
            role="status"
            aria-live="polite"
        >
            <div class="greeting-morph" :dir="textDir">
                <span class="greeting-aura" aria-hidden="true" />
                <span class="greeting-text">{{ text }}</span>
            </div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    visible: { type: Boolean, default: false },
    text: { type: String, default: '' },
    textDir: { type: String, default: 'rtl' },
});
</script>

<style scoped>
.greeting-anchor {
    position: absolute;
    left: 50%;
    bottom: calc(100% + 10px);
    transform: translateX(-50%);
    z-index: 3;
    pointer-events: none;
    max-width: min(280px, calc(100vw - 24px));
    width: max-content;
    min-width: 0;
    box-sizing: border-box;
}

.greeting-morph {
    position: relative;
    box-sizing: border-box;
    max-width: 100%;
    padding: 11px 15px;
    line-height: 1.55;
    overflow-wrap: anywhere;
    word-break: break-word;
    color: #f8fafc;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.01em;
    transform-origin: 50% 100%;
    border-radius: 16px;
    background: linear-gradient(152deg, #115e59 0%, #0f766e 38%, #0e7490 100%);
    box-shadow:
        0 16px 40px rgba(6, 78, 59, 0.38),
        0 0 0 1px rgba(255, 255, 255, 0.12) inset;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    /* ورود: از دایرهٔ کوچک (هم‌راستا با FAB) به حباب — الگوی expand / morph */
    animation: greet-morph-in 0.72s cubic-bezier(0.32, 1.15, 0.25, 1) both;
}

.greeting-aura {
    position: absolute;
    inset: -3px;
    border-radius: inherit;
    pointer-events: none;
    z-index: 0;
    animation: greet-aura-out 0.75s ease-out both;
}

@keyframes greet-morph-in {
    0% {
        opacity: 0;
        transform: scale(0.34);
        border-radius: 999px;
        filter: blur(4px);
        background: linear-gradient(135deg, #0f766e, #06b6d4);
        box-shadow:
            0 0 0 1px rgba(255, 255, 255, 0.35) inset,
            0 8px 28px rgba(14, 116, 144, 0.55);
    }
    42% {
        opacity: 1;
        filter: blur(0);
    }
    68% {
        transform: scale(1.04);
        border-radius: 18px;
        background: linear-gradient(152deg, #134e4a 0%, #0f766e 42%, #0d9488 100%);
        box-shadow:
            0 18px 44px rgba(6, 78, 59, 0.36),
            0 0 0 1px rgba(255, 255, 255, 0.1) inset;
    }
    100% {
        opacity: 1;
        transform: scale(1);
        border-radius: 16px;
        filter: blur(0);
        background: linear-gradient(152deg, #115e59 0%, #0f766e 38%, #0e7490 100%);
        box-shadow:
            0 16px 40px rgba(6, 78, 59, 0.38),
            0 0 0 1px rgba(255, 255, 255, 0.12) inset;
    }
}

@keyframes greet-aura-out {
    0% {
        box-shadow:
            0 0 0 0 rgba(45, 212, 191, 0.55),
            0 0 48px rgba(34, 211, 238, 0.45);
        opacity: 1;
    }
    100% {
        box-shadow:
            0 0 0 22px transparent,
            0 0 0 transparent;
        opacity: 0;
    }
}

.greeting-text {
    position: relative;
    z-index: 1;
    display: block;
    opacity: 0;
    transform: translateY(4px);
    animation: greet-text-in 0.42s cubic-bezier(0.22, 1, 0.36, 1) 0.18s both;
}

@keyframes greet-text-in {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* خروج: جمع‌شدن به سمت آیکون */
.greet-leave-shell-leave-active .greeting-morph {
    transition:
        transform 0.28s cubic-bezier(0.55, 0, 1, 0.45),
        opacity 0.26s ease,
        filter 0.24s ease,
        border-radius 0.28s ease;
}

.greet-leave-shell-leave-to .greeting-morph {
    transform: scale(0.38);
    opacity: 0;
    border-radius: 999px;
    filter: blur(5px);
}

.greet-leave-shell-leave-to .greeting-text {
    opacity: 0;
    transition: opacity 0.15s ease;
}

@media (prefers-reduced-motion: reduce) {
    .greeting-morph {
        animation: greet-morph-reduced 0.28s ease both;
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

    .greeting-aura {
        display: none;
    }

    .greet-leave-shell-leave-active .greeting-morph {
        transition: opacity 0.2s ease;
    }

    .greet-leave-shell-leave-to .greeting-morph {
        transform: none;
        filter: none;
    }
}
</style>
