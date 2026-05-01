<template>
    <transition name="greeting-fade">
        <div v-if="visible" class="floating-chat-greeting-anchor" role="status" aria-live="polite">
            <div class="floating-chat-greeting" :dir="textDir">{{ text }}</div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    visible: { type: Boolean, default: false },
    text: { type: String, default: '' },
    /** جهت متن داخل حباب (rtl برای فارسی/عربی) */
    textDir: { type: String, default: 'rtl' },
});
</script>

<style scoped>
/* چسبیده به لبهٔ چپ لانچر تا حباب به سمت راست رشد کند و از viewport بیرون نزند */
.floating-chat-greeting-anchor {
    position: absolute;
    left: 0;
    right: auto;
    bottom: calc(100% + 10px);
    z-index: 3;
    pointer-events: none;
    max-width: min(280px, calc(100vw - 24px));
    width: max-content;
    min-width: 0;
    box-sizing: border-box;
}

.floating-chat-greeting {
    box-sizing: border-box;
    max-width: 100%;
    border-radius: 14px;
    background: rgba(15, 23, 42, 0.92);
    color: #fff;
    font-size: 13px;
    font-weight: 500;
    padding: 10px 14px;
    line-height: 1.5;
    overflow-wrap: anywhere;
    word-break: break-word;
    box-shadow:
        0 10px 28px rgba(2, 6, 23, 0.22),
        0 0 0 1px rgba(255, 255, 255, 0.06) inset;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.greeting-fade-enter-active,
.greeting-fade-leave-active {
    transition: opacity 0.2s ease;
}

.greeting-fade-enter-from,
.greeting-fade-leave-to {
    opacity: 0;
}
</style>
