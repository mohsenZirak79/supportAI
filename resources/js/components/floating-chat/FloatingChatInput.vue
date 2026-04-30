<template>
    <div class="widget-input-wrap">
        <div v-if="isRecording" class="recording-strip">
            <span class="record-dot"></span>
            <span class="timer">{{ formatTimer(recordingTime) }}</span>
            <button type="button" class="mini-btn ghost" @click="$emit('cancel-recording')">✕</button>
            <button type="button" class="mini-btn primary" @click="$emit('send-recording')">✓</button>
        </div>

        <form v-else class="widget-input-form" @submit.prevent="$emit('send-text')">
            <input
                ref="inputEl"
                :value="modelValue"
                class="widget-input"
                type="text"
                :placeholder="placeholder"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <button
                type="button"
                class="mini-btn ghost"
                :aria-label="'ضبط صدا'"
                :disabled="disabled"
                @click="$emit('start-recording')"
            >
                🎤
            </button>
            <button
                type="submit"
                class="mini-btn primary"
                :aria-label="'ارسال پیام'"
                :disabled="disabled || !modelValue.trim()"
            >
                ➤
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
.widget-input-wrap { padding: 10px 12px; border-top: 1px solid rgba(148, 163, 184, 0.3); }
.widget-input-form { display: flex; gap: 8px; align-items: center; }
.widget-input {
    flex: 1;
    border: 1px solid rgba(148, 163, 184, 0.35);
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 13px;
    background: rgba(255, 255, 255, 0.86);
}
.widget-input:focus { outline: 2px solid rgba(14, 165, 233, 0.3); border-color: rgba(14, 165, 233, 0.45); }

.mini-btn {
    width: 34px; height: 34px; border-radius: 10px; border: 0; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
}
.mini-btn.primary { background: linear-gradient(135deg, #0f766e, #06b6d4); color: #fff; }
.mini-btn.ghost { background: rgba(226, 232, 240, 0.72); color: #0f172a; }
.mini-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.recording-strip { display: flex; align-items: center; gap: 8px; }
.record-dot { width: 8px; height: 8px; border-radius: 999px; background: #ef4444; animation: dot-pulse 1s infinite; }
.timer { font-size: 12px; color: #334155; margin-inline-end: auto; }
@keyframes dot-pulse { 0%,100%{opacity:.5;}50%{opacity:1;} }
</style>
