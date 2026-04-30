<template>
    <div class="widget-input-wrap">
        <div v-if="isRecording" class="recording-strip">
            <span class="record-dot"></span>
            <span class="timer">{{ formatTimer(recordingTime) }}</span>
            <button type="button" class="mini-btn ghost" @click="$emit('cancel-recording')">✕</button>
            <button type="button" class="mini-btn primary" @click="$emit('send-recording')">✓</button>
        </div>

        <form
            v-else
            class="widget-input-form"
            :dir="dir"
            @submit.prevent="$emit('send-text')"
        >
            <input
                :value="modelValue"
                class="widget-input"
                type="text"
                :placeholder="placeholder"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <button
                type="submit"
                class="mini-btn mini-btn--send primary"
                :aria-label="sendLabel"
                :disabled="disabled || !modelValue.trim()"
            >
                <svg viewBox="0 0 24 24" class="mini-ico" aria-hidden="true" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                </svg>
            </button>
            <button
                type="button"
                class="mini-btn mini-btn--mic ghost"
                :aria-label="micLabel"
                :disabled="disabled"
                @click="$emit('start-recording')"
            >
                <svg
                    class="mini-ico"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" />
                    <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                    <line x1="12" y1="19" x2="12" y2="23" />
                    <line x1="8" y1="23" x2="16" y2="23" />
                </svg>
            </button>
        </form>
    </div>
</template>

<script setup>
defineProps({
    modelValue: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    isRecording: { type: Boolean, default: false },
    recordingTime: { type: Number, default: 0 },
    placeholder: { type: String, default: 'پیام خود را بنویسید…' },
    dir: { type: String, default: 'rtl' },
    sendLabel: { type: String, default: 'ارسال' },
    micLabel: { type: String, default: 'ضبط صوت' },
});

defineEmits([
    'update:modelValue',
    'send-text',
    'start-recording',
    'cancel-recording',
    'send-recording',
]);

const formatTimer = (ms) => {
    const totalSeconds = Math.floor(ms / 1000);
    const m = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
    const s = String(totalSeconds % 60).padStart(2, '0');
    return `${m}:${s}`;
};
</script>

<style scoped>
.widget-input-wrap {
    padding: 10px 12px 12px;
}

.widget-input-form {
    display: flex;
    flex-direction: row;
    gap: 8px;
    align-items: center;
}

.widget-input {
    flex: 1;
    min-width: 0;
    border: 1px solid rgba(148, 163, 184, 0.35);
    border-radius: 14px;
    padding: 10px 12px;
    font-size: 13px;
    background: rgba(255, 255, 255, 0.92);
}

.widget-input:focus {
    outline: 2px solid rgba(14, 165, 233, 0.28);
    border-color: rgba(14, 165, 233, 0.45);
}

.mini-btn {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    border: 0;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform 0.12s ease, opacity 0.15s ease;
}

.mini-btn:active:not(:disabled) {
    transform: scale(0.96);
}

.mini-ico {
    width: 20px;
    height: 20px;
    display: block;
}

.mini-btn.primary {
    background: linear-gradient(135deg, #0f766e, #06b6d4);
    color: #fff;
    box-shadow: 0 2px 8px rgba(14, 116, 144, 0.25);
}

.mini-btn.ghost {
    background: rgba(226, 232, 240, 0.85);
    color: #0f172a;
    border: 1px solid rgba(148, 163, 184, 0.25);
}

.mini-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

[dir='rtl'] .mini-btn--send .mini-ico {
    transform: scaleX(-1);
}

.recording-strip {
    display: flex;
    align-items: center;
    gap: 8px;
}

.record-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #ef4444;
    animation: dot-pulse 1s infinite;
}

.timer {
    font-size: 12px;
    color: #334155;
    margin-inline-end: auto;
}

@keyframes dot-pulse {
    0%,
    100% {
        opacity: 0.5;
    }
    50% {
        opacity: 1;
    }
}
</style>
