<template>
    <form class="cg-composer" @submit.prevent="$emit('submit')">
        <div v-if="isRecording" class="cg-composer-recording">
            <div class="cg-composer-wave" aria-hidden="true">
                <div v-for="n in 20" :key="n" class="cg-composer-bar" :style="{ height: barHeight(n) + 'px' }" />
            </div>
            <div class="cg-composer-rec-actions">
                <button type="button" class="cg-btn cg-btn--ghost" :aria-label="cancelLabel" @click="$emit('cancel-recording')">
                    ✕
                </button>
                <span class="cg-composer-timer">{{ formattedTime }}</span>
                <button type="button" class="cg-btn cg-btn--primary" :aria-label="sendVoiceLabel" @click="$emit('send-recording')">
                    ✓
                </button>
            </div>
        </div>
        <div v-else class="cg-composer-inner">
            <textarea
                ref="taRef"
                :value="modelValue"
                class="cg-composer-input"
                rows="1"
                :placeholder="placeholder"
                :disabled="disabled"
                @input="onInput"
                @keydown="$emit('keydown', $event)"
            />
            <div class="cg-composer-actions">
                <button
                    type="button"
                    class="cg-btn cg-btn--icon"
                    :disabled="disabled"
                    :aria-label="micLabel"
                    :title="micLabel"
                    @click="$emit('start-recording')"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22" aria-hidden="true">
                        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" />
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                        <line x1="12" y1="19" x2="12" y2="23" />
                        <line x1="8" y1="23" x2="16" y2="23" />
                    </svg>
                </button>
                <button
                    type="submit"
                    class="cg-btn cg-btn--send"
                    :disabled="disabled || !modelValue.trim()"
                    :aria-label="sendLabel"
                    :title="sendLabel"
                >
                    <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20" aria-hidden="true">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                    </svg>
                </button>
            </div>
        </div>
    </form>
</template>

<script setup>
import { ref, watch, nextTick, computed } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    isRecording: { type: Boolean, default: false },
    recordingTime: { type: Number, default: 0 },
    placeholder: { type: String, default: '' },
    micLabel: { type: String, default: 'ضبط صدا' },
    sendLabel: { type: String, default: 'ارسال' },
    sendVoiceLabel: { type: String, default: 'ارسال صدا' },
    cancelLabel: { type: String, default: 'لغو ضبط' },
    barHeightFn: { type: Function, default: null },
});

const emit = defineEmits(['update:modelValue', 'submit', 'start-recording', 'cancel-recording', 'send-recording', 'keydown']);

const taRef = ref(null);

const formatTimer = (ms) => {
    const totalSeconds = Math.floor(ms / 1000);
    const m = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
    const s = String(totalSeconds % 60).padStart(2, '0');
    const cs = String(Math.floor((ms % 1000) / 10)).padStart(2, '0');
    return `${m}:${s}.${cs}`;
};

const formattedTime = computed(() => formatTimer(props.recordingTime));

const barHeight = (n) => {
    if (props.barHeightFn) return props.barHeightFn(n);
    if (!props.isRecording) return 4;
    return 8 + Math.random() * 22;
};

const onInput = (e) => {
    emit('update:modelValue', e.target.value);
    autoGrow(e.target);
};

const autoGrow = (el) => {
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 200) + 'px';
};

watch(
    () => props.modelValue,
    () => {
        nextTick(() => {
            if (taRef.value) autoGrow(taRef.value);
        });
    },
);

defineExpose({
    resetHeight: () => {
        if (taRef.value) taRef.value.style.height = 'auto';
    },
    focus: () => taRef.value?.focus(),
});
</script>

<style scoped>
.cg-composer {
    margin: 0 auto;
    max-width: 768px;
    width: 100%;
    padding: 12px 16px;
    padding-bottom: max(12px, env(safe-area-inset-bottom));
    background: linear-gradient(to top, var(--cg-surface-0, #fff) 70%, transparent);
}

.cg-composer-inner {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 24px;
    border: 1px solid var(--cg-border, rgba(148, 163, 184, 0.28));
    background: var(--cg-surface-elevated, #fff);
    box-shadow: var(--cg-shadow-soft, 0 8px 32px rgba(15, 23, 42, 0.06));
}

.cg-composer-input {
    flex: 1;
    border: 0;
    outline: none;
    resize: none;
    font: inherit;
    font-size: 0.95rem;
    line-height: 1.5;
    min-height: 24px;
    max-height: 200px;
    padding: 6px 4px;
    background: transparent;
    color: inherit;
}

.cg-composer-input::placeholder {
    color: var(--cg-ink-muted, #94a3b8);
}

.cg-composer-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.cg-composer-recording {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 20px;
    border: 1px solid var(--cg-border, rgba(148, 163, 184, 0.28));
    background: var(--cg-surface-elevated, #fff);
    box-shadow: var(--cg-shadow-soft, 0 8px 32px rgba(15, 23, 42, 0.06));
}

.cg-composer-wave {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 3px;
    height: 40px;
}

.cg-composer-bar {
    width: 3px;
    border-radius: 2px;
    background: var(--cg-brand, #0f766e);
    opacity: 0.75;
}

.cg-composer-rec-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.cg-composer-timer {
    font-size: 0.85rem;
    font-variant-numeric: tabular-nums;
    color: var(--cg-ink-muted, #64748b);
}

.cg-btn {
    border: 0;
    border-radius: 10px;
    cursor: pointer;
    font: inherit;
    transition: background 0.15s ease, transform 0.12s ease, opacity 0.15s ease;
}

.cg-btn:focus-visible {
    outline: 2px solid var(--cg-brand, #0f766e);
    outline-offset: 2px;
}

.cg-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.cg-btn--ghost {
    width: 40px;
    height: 40px;
    background: rgba(241, 245, 249, 0.95);
    color: var(--cg-ink, #0f172a);
}

.cg-btn--primary {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--cg-brand, #0f766e), var(--cg-brand-2, #0ea5e9));
    color: #fff;
}

.cg-btn--icon {
    width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    color: var(--cg-ink-muted, #64748b);
}

.cg-btn--icon:hover:not(:disabled) {
    background: rgba(241, 245, 249, 0.9);
    color: var(--cg-brand, #0f766e);
}

.cg-btn--send {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--cg-brand, #0f766e), var(--cg-brand-2, #0ea5e9));
    color: #fff;
}

.cg-btn--send:hover:not(:disabled) {
    filter: brightness(1.05);
}

.cg-btn--send:active:not(:disabled) {
    transform: scale(0.96);
}
</style>
